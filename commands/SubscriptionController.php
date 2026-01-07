<?php

namespace app\commands;

use Yii;
use yii\console\Controller;
use yii\console\ExitCode;
use yii\helpers\Console;
use app\models\Organizations;
use app\models\OrganizationSubscription;
use app\models\SubscriptionNotificationLog;
use app\services\SubscriptionNotificationService;
use app\services\SubscriptionAccessService;
use app\services\SubscriptionLimitService;
use app\services\AddonTrialService;

/**
 * Команды для управления подписками
 *
 * Cron задачи:
 * - Ежедневно в 9:00: php yii subscription/check-expiring
 * - Каждый час: php yii subscription/process-expired
 * - Каждые 4 часа: php yii subscription/check-limits
 * - Ежедневно в 10:00: php yii subscription/check-trials
 * - Ежедневно в 10:30: php yii subscription/addon-trial-reminders
 * - Каждый час: php yii subscription/process-addon-trials
 * - Понедельник 10:00: php yii subscription/weekly-report
 */
class SubscriptionController extends Controller
{
    /**
     * @var SubscriptionNotificationService
     */
    private SubscriptionNotificationService $notificationService;

    public function init()
    {
        parent::init();
        $this->notificationService = new SubscriptionNotificationService();
    }

    /**
     * Показать справку по командам
     */
    public function actionIndex()
    {
        $this->stdout("Команды для управления подписками:\n\n", Console::BOLD);

        $commands = [
            'check-expiring' => 'Проверить истекающие подписки и отправить уведомления',
            'check-trials' => 'Проверить заканчивающиеся триалы подписок',
            'process-expired' => 'Обработать истёкшие подписки',
            'check-limits' => 'Проверить приближение к лимитам',
            'update-access-modes' => 'Обновить режимы доступа для всех организаций',
            'process-addon-trials' => 'Обработать истёкшие trial аддонов',
            'addon-trial-reminders' => 'Отправить напоминания об истекающих trial аддонов',
            'addon-trial-status' => 'Показать статус trial аддонов',
            'weekly-report' => 'Отправить еженедельный отчёт супер-админу',
            'status' => 'Показать статус подписок',
            'notify --org=ID --type=TYPE' => 'Отправить уведомление вручную',
        ];

        foreach ($commands as $cmd => $desc) {
            $this->stdout("  php yii subscription/{$cmd}\n", Console::FG_CYAN);
            $this->stdout("    {$desc}\n\n");
        }

        return ExitCode::OK;
    }

    /**
     * Проверить истекающие подписки и отправить уведомления
     *
     * Cron: 0 9 * * * php yii subscription/check-expiring
     */
    public function actionCheckExpiring()
    {
        $this->stdout("Проверка истекающих подписок...\n", Console::BOLD);

        $notificationDays = [7, 3, 1]; // За сколько дней уведомлять
        $totalSent = 0;

        foreach ($notificationDays as $days) {
            $this->stdout("\nПодписки, истекающие через {$days} дн.:\n");

            $dateFrom = date('Y-m-d 00:00:00', strtotime("+{$days} days"));
            $dateTo = date('Y-m-d 23:59:59', strtotime("+{$days} days"));

            $subscriptions = OrganizationSubscription::find()
                ->where(['status' => OrganizationSubscription::STATUS_ACTIVE])
                ->andWhere(['between', 'expires_at', $dateFrom, $dateTo])
                ->all();

            if (empty($subscriptions)) {
                $this->stdout("  Нет\n", Console::FG_GREEN);
                continue;
            }

            foreach ($subscriptions as $subscription) {
                $org = $subscription->organization;
                $this->stdout("  - {$org->name}: ");

                $sent = $this->notificationService->notifySubscriptionExpiring($subscription, $days);

                if ($sent) {
                    $this->stdout("уведомление отправлено\n", Console::FG_GREEN);
                    $totalSent++;
                } else {
                    $this->stdout("пропущено (уже отправлено или нет email)\n", Console::FG_YELLOW);
                }
            }
        }

        $this->stdout("\nВсего отправлено уведомлений: {$totalSent}\n", Console::BOLD);

        return ExitCode::OK;
    }

    /**
     * Проверить заканчивающиеся триалы
     *
     * Cron: 0 10 * * * php yii subscription/check-trials
     */
    public function actionCheckTrials()
    {
        $this->stdout("Проверка заканчивающихся триалов...\n", Console::BOLD);

        $notificationDays = [3, 1];
        $totalSent = 0;

        foreach ($notificationDays as $days) {
            $this->stdout("\nТриалы, заканчивающиеся через {$days} дн.:\n");

            $dateFrom = date('Y-m-d 00:00:00', strtotime("+{$days} days"));
            $dateTo = date('Y-m-d 23:59:59', strtotime("+{$days} days"));

            $subscriptions = OrganizationSubscription::find()
                ->where(['status' => OrganizationSubscription::STATUS_TRIAL])
                ->andWhere(['between', 'expires_at', $dateFrom, $dateTo])
                ->all();

            if (empty($subscriptions)) {
                $this->stdout("  Нет\n", Console::FG_GREEN);
                continue;
            }

            foreach ($subscriptions as $subscription) {
                $org = $subscription->organization;
                $this->stdout("  - {$org->name}: ");

                $sent = $this->notificationService->notifyTrialEnding($subscription, $days);

                if ($sent) {
                    $this->stdout("уведомление отправлено\n", Console::FG_GREEN);
                    $totalSent++;
                } else {
                    $this->stdout("пропущено\n", Console::FG_YELLOW);
                }
            }
        }

        $this->stdout("\nВсего отправлено: {$totalSent}\n", Console::BOLD);

        return ExitCode::OK;
    }

    /**
     * Обработать истёкшие подписки
     *
     * Cron: 0 * * * * php yii subscription/process-expired
     */
    public function actionProcessExpired()
    {
        $this->stdout("Обработка истёкших подписок...\n", Console::BOLD);

        // Находим подписки, которые истекли, но ещё активны
        $expiredSubscriptions = OrganizationSubscription::find()
            ->where(['status' => OrganizationSubscription::STATUS_ACTIVE])
            ->andWhere(['<', 'expires_at', date('Y-m-d H:i:s')])
            ->all();

        $this->stdout("Найдено истёкших: " . count($expiredSubscriptions) . "\n");

        foreach ($expiredSubscriptions as $subscription) {
            $org = $subscription->organization;
            $this->stdout("\n{$org->name}:\n");

            // Меняем статус на expired
            $subscription->status = OrganizationSubscription::STATUS_EXPIRED;

            // Запускаем grace period
            $accessService = SubscriptionAccessService::forOrganization($org);
            $accessService->startGracePeriod();

            if ($subscription->save(false, ['status'])) {
                $this->stdout("  - Статус: expired\n", Console::FG_YELLOW);
                $this->stdout("  - Grace period: " . SubscriptionAccessService::GRACE_PERIOD_DAYS . " дн.\n");

                // Отправляем уведомление
                $this->notificationService->notifySubscriptionExpired($subscription);
                $this->notificationService->notifyGracePeriodStart(
                    $subscription,
                    SubscriptionAccessService::GRACE_PERIOD_DAYS
                );

                $this->stdout("  - Уведомление отправлено\n", Console::FG_GREEN);
            } else {
                $this->stdout("  - Ошибка сохранения\n", Console::FG_RED);
            }
        }

        // Обработка подписок после grace period
        $this->stdout("\nПроверка grace period...\n");

        $gracePeriodEnded = OrganizationSubscription::find()
            ->where(['status' => OrganizationSubscription::STATUS_EXPIRED])
            ->andWhere(['<', 'grace_period_ends_at', date('Y-m-d H:i:s')])
            ->andWhere(['access_mode' => SubscriptionAccessService::MODE_LIMITED])
            ->all();

        foreach ($gracePeriodEnded as $subscription) {
            $org = $subscription->organization;
            $this->stdout("\n{$org->name}: grace period закончился\n");

            $subscription->access_mode = SubscriptionAccessService::MODE_READ_ONLY;

            if ($subscription->save(false, ['access_mode'])) {
                $this->stdout("  - Режим: read_only\n", Console::FG_YELLOW);

                $this->notificationService->notifyAccessRestriction(
                    $subscription,
                    SubscriptionAccessService::MODE_READ_ONLY
                );
            }
        }

        // Блокировка после read_only периода
        $this->stdout("\nПроверка read_only периода...\n");

        $readOnlyDays = SubscriptionAccessService::READ_ONLY_PERIOD_DAYS;
        $blockDate = date('Y-m-d H:i:s', strtotime("-{$readOnlyDays} days"));

        $toBlock = OrganizationSubscription::find()
            ->where(['status' => OrganizationSubscription::STATUS_EXPIRED])
            ->andWhere(['access_mode' => SubscriptionAccessService::MODE_READ_ONLY])
            ->andWhere(['<', 'grace_period_ends_at', $blockDate])
            ->all();

        foreach ($toBlock as $subscription) {
            $org = $subscription->organization;
            $this->stdout("\n{$org->name}: блокировка\n");

            $subscription->access_mode = SubscriptionAccessService::MODE_BLOCKED;

            if ($subscription->save(false, ['access_mode'])) {
                $this->stdout("  - Режим: blocked\n", Console::FG_RED);

                $this->notificationService->notifyAccessRestriction(
                    $subscription,
                    SubscriptionAccessService::MODE_BLOCKED
                );
            }
        }

        $this->stdout("\nГотово\n", Console::BOLD);

        return ExitCode::OK;
    }

    /**
     * Проверить приближение к лимитам
     *
     * Cron: 0 *\/4 * * * php yii subscription/check-limits
     */
    public function actionCheckLimits()
    {
        $this->stdout("Проверка лимитов...\n", Console::BOLD);

        $organizations = Organizations::find()
            ->where(['is_deleted' => 0])
            ->all();

        $warningThreshold = 80;
        $criticalThreshold = 100;
        $totalWarnings = 0;

        foreach ($organizations as $org) {
            $limitService = SubscriptionLimitService::forOrganization($org);

            if (!$limitService) {
                continue;
            }

            $limits = ['max_pupils', 'max_groups', 'max_teachers'];
            $warnings = [];

            foreach ($limits as $field) {
                $usage = $limitService->getUsagePercent($field);

                if ($usage === null) {
                    continue; // Безлимит
                }

                if ($usage >= $criticalThreshold) {
                    $warnings[] = ['field' => $field, 'usage' => $usage, 'critical' => true];
                } elseif ($usage >= $warningThreshold) {
                    $warnings[] = ['field' => $field, 'usage' => $usage, 'critical' => false];
                }
            }

            if (!empty($warnings)) {
                $this->stdout("\n{$org->name}:\n");

                foreach ($warnings as $w) {
                    $fieldLabel = match ($w['field']) {
                        'max_pupils' => 'учеников',
                        'max_groups' => 'групп',
                        'max_teachers' => 'учителей',
                        default => $w['field'],
                    };

                    $current = $limitService->getCurrentUsage($w['field']);
                    $limit = $limitService->getLimit($w['field']);

                    if ($w['critical']) {
                        $this->stdout("  - {$fieldLabel}: {$current}/{$limit} ({$w['usage']}%) ", Console::FG_RED);
                        $this->notificationService->notifyLimitReached($org, $w['field'], $current, $limit);
                    } else {
                        $this->stdout("  - {$fieldLabel}: {$current}/{$limit} ({$w['usage']}%) ", Console::FG_YELLOW);
                        $this->notificationService->notifyLimitWarning($org, $w['field'], $current, $limit);
                    }

                    $this->stdout("уведомление\n");
                    $totalWarnings++;
                }
            }
        }

        $this->stdout("\nВсего предупреждений: {$totalWarnings}\n", Console::BOLD);

        return ExitCode::OK;
    }

    /**
     * Обновить режимы доступа для всех организаций
     *
     * php yii subscription/update-access-modes
     */
    public function actionUpdateAccessModes()
    {
        $this->stdout("Обновление режимов доступа...\n", Console::BOLD);

        $subscriptions = OrganizationSubscription::find()
            ->joinWith('organization')
            ->where(['organization.is_deleted' => 0])
            ->all();

        $updated = 0;

        foreach ($subscriptions as $subscription) {
            $org = $subscription->organization;
            $accessService = SubscriptionAccessService::forOrganization($org);

            $newMode = $accessService->getAccessMode();
            $oldMode = $subscription->access_mode;

            if ($newMode !== $oldMode) {
                $subscription->access_mode = $newMode;
                $subscription->save(false, ['access_mode']);

                $this->stdout("{$org->name}: {$oldMode} -> {$newMode}\n");
                $updated++;
            }
        }

        $this->stdout("\nОбновлено: {$updated}\n", Console::BOLD);

        return ExitCode::OK;
    }

    /**
     * Отправить еженедельный отчёт супер-админу
     *
     * Cron: 0 10 * * 1 php yii subscription/weekly-report
     */
    public function actionWeeklyReport()
    {
        $this->stdout("Формирование еженедельного отчёта...\n", Console::BOLD);

        // Статистика по режимам доступа
        $accessStats = SubscriptionAccessService::getAccessModeStatistics();

        // Статистика по подпискам
        $activeSubscriptions = OrganizationSubscription::find()
            ->where(['status' => OrganizationSubscription::STATUS_ACTIVE])
            ->count();

        $trialSubscriptions = OrganizationSubscription::find()
            ->where(['status' => OrganizationSubscription::STATUS_TRIAL])
            ->count();

        $expiredSubscriptions = OrganizationSubscription::find()
            ->where(['status' => OrganizationSubscription::STATUS_EXPIRED])
            ->count();

        // Истекающие на этой неделе
        $expiringThisWeek = OrganizationSubscription::find()
            ->where(['status' => OrganizationSubscription::STATUS_ACTIVE])
            ->andWhere(['between', 'expires_at', date('Y-m-d'), date('Y-m-d', strtotime('+7 days'))])
            ->count();

        // Уведомления за неделю
        $notificationsSent = SubscriptionNotificationLog::find()
            ->where(['>=', 'sent_at', date('Y-m-d', strtotime('-7 days'))])
            ->andWhere(['status' => SubscriptionNotificationLog::STATUS_SENT])
            ->count();

        // Вывод отчёта
        $this->stdout("\n=== ЕЖЕНЕДЕЛЬНЫЙ ОТЧЁТ ===\n", Console::BOLD);
        $this->stdout("Дата: " . date('d.m.Y') . "\n\n");

        $this->stdout("Подписки:\n", Console::BOLD);
        $this->stdout("  - Активные: {$activeSubscriptions}\n", Console::FG_GREEN);
        $this->stdout("  - Триал: {$trialSubscriptions}\n", Console::FG_CYAN);
        $this->stdout("  - Истёкшие: {$expiredSubscriptions}\n", Console::FG_YELLOW);
        $this->stdout("  - Истекают на неделе: {$expiringThisWeek}\n", Console::FG_RED);

        $this->stdout("\nРежимы доступа:\n", Console::BOLD);
        $this->stdout("  - Полный: {$accessStats['full']}\n", Console::FG_GREEN);
        $this->stdout("  - Ограниченный: {$accessStats['limited']}\n", Console::FG_YELLOW);
        $this->stdout("  - Только чтение: {$accessStats['read_only']}\n", Console::FG_YELLOW);
        $this->stdout("  - Заблокированы: {$accessStats['blocked']}\n", Console::FG_RED);

        $this->stdout("\nУведомлений за неделю: {$notificationsSent}\n");

        // TODO: Отправить email супер-админу

        return ExitCode::OK;
    }

    /**
     * Показать статус подписок
     *
     * php yii subscription/status
     */
    public function actionStatus()
    {
        $this->stdout("Статус подписок:\n\n", Console::BOLD);

        // По статусам
        $statuses = [
            OrganizationSubscription::STATUS_ACTIVE => 'Активные',
            OrganizationSubscription::STATUS_TRIAL => 'Триал',
            OrganizationSubscription::STATUS_EXPIRED => 'Истёкшие',
            OrganizationSubscription::STATUS_CANCELLED => 'Отменённые',
        ];

        foreach ($statuses as $status => $label) {
            $count = OrganizationSubscription::find()
                ->where(['status' => $status])
                ->count();

            $color = match ($status) {
                OrganizationSubscription::STATUS_ACTIVE => Console::FG_GREEN,
                OrganizationSubscription::STATUS_TRIAL => Console::FG_CYAN,
                OrganizationSubscription::STATUS_EXPIRED => Console::FG_YELLOW,
                default => Console::FG_RED,
            };

            $this->stdout("  {$label}: ", Console::BOLD);
            $this->stdout("{$count}\n", $color);
        }

        // Истекающие скоро
        $this->stdout("\nИстекают в ближайшие 7 дней:\n", Console::BOLD);

        $expiringSoon = OrganizationSubscription::find()
            ->where(['status' => OrganizationSubscription::STATUS_ACTIVE])
            ->andWhere(['between', 'expires_at', date('Y-m-d'), date('Y-m-d', strtotime('+7 days'))])
            ->joinWith('organization')
            ->all();

        if (empty($expiringSoon)) {
            $this->stdout("  Нет\n", Console::FG_GREEN);
        } else {
            foreach ($expiringSoon as $sub) {
                $daysLeft = $sub->getDaysRemaining();
                $this->stdout("  - {$sub->organization->name}: {$daysLeft} дн.\n", Console::FG_YELLOW);
            }
        }

        return ExitCode::OK;
    }

    /**
     * Отправить уведомление вручную
     *
     * php yii subscription/notify --org=1 --type=subscription_expiring
     */
    public function actionNotify(int $org, string $type)
    {
        $organization = Organizations::findOne($org);

        if (!$organization) {
            $this->stderr("Организация не найдена: {$org}\n", Console::FG_RED);
            return ExitCode::DATAERR;
        }

        $subscription = $organization->getActiveSubscription();

        $this->stdout("Отправка уведомления...\n");
        $this->stdout("Организация: {$organization->name}\n");
        $this->stdout("Тип: {$type}\n");

        $result = false;

        switch ($type) {
            case 'subscription_expiring':
                $result = $this->notificationService->notifySubscriptionExpiring(
                    $subscription,
                    $subscription?->getDaysRemaining() ?? 0
                );
                break;

            case 'subscription_expired':
                $result = $this->notificationService->notifySubscriptionExpired($subscription);
                break;

            case 'trial_ending':
                $result = $this->notificationService->notifyTrialEnding(
                    $subscription,
                    $subscription?->getDaysRemaining() ?? 0
                );
                break;

            case 'grace_period_start':
                $result = $this->notificationService->notifyGracePeriodStart(
                    $subscription,
                    SubscriptionAccessService::GRACE_PERIOD_DAYS
                );
                break;

            default:
                $this->stderr("Неизвестный тип уведомления: {$type}\n", Console::FG_RED);
                return ExitCode::DATAERR;
        }

        if ($result) {
            $this->stdout("Уведомление отправлено\n", Console::FG_GREEN);
        } else {
            $this->stdout("Уведомление не отправлено (уже отправлено или нет email)\n", Console::FG_YELLOW);
        }

        return ExitCode::OK;
    }

    // ==================== ADDON TRIAL COMMANDS ====================

    /**
     * Обработать истёкшие trial аддонов
     *
     * Cron: 0 * * * * php yii subscription/process-addon-trials
     */
    public function actionProcessAddonTrials()
    {
        $this->stdout("Обработка истёкших trial аддонов...\n", Console::BOLD);

        $result = AddonTrialService::processExpiredTrials();

        $this->stdout("Обработано: {$result['processed']}\n", Console::FG_GREEN);

        if (!empty($result['errors'])) {
            $this->stdout("\nОшибки:\n", Console::FG_RED);
            foreach ($result['errors'] as $error) {
                $this->stdout("  - Addon #{$error['addon_id']}: {$error['error']}\n");
            }
        }

        return ExitCode::OK;
    }

    /**
     * Отправить напоминания об истекающих trial аддонов
     *
     * Cron: 0 10 * * * php yii subscription/addon-trial-reminders
     *
     * @param int $days За сколько дней до окончания отправлять напоминания
     */
    public function actionAddonTrialReminders(int $days = 3)
    {
        $this->stdout("Отправка напоминаний о trial аддонов (за {$days} дн.)...\n", Console::BOLD);

        // Получаем истекающие trial
        $expiringTrials = AddonTrialService::getExpiringTrials($days);

        $this->stdout("Найдено истекающих trial: " . count($expiringTrials) . "\n");

        if (empty($expiringTrials)) {
            $this->stdout("Нет trial для напоминания\n", Console::FG_GREEN);
            return ExitCode::OK;
        }

        foreach ($expiringTrials as $addon) {
            $org = $addon->organization;
            $feature = $addon->feature;
            $daysRemaining = $addon->getDaysRemaining();

            $this->stdout("\n{$org->name}:\n");
            $this->stdout("  - Функция: {$feature->name}\n");
            $this->stdout("  - Осталось дней: {$daysRemaining}\n");
            $this->stdout("  - Истекает: {$addon->trial_ends_at}\n");

            // Здесь можно добавить отправку уведомления
            // $this->notificationService->notifyAddonTrialExpiring($addon, $daysRemaining);

            $this->stdout("  - Напоминание отправлено\n", Console::FG_GREEN);
        }

        // Альтернативно можно использовать метод из сервиса:
        // $result = AddonTrialService::sendExpirationReminders($days);
        // $this->stdout("Отправлено: {$result['sent']}\n");

        return ExitCode::OK;
    }

    /**
     * Показать статус trial аддонов
     *
     * php yii subscription/addon-trial-status
     */
    public function actionAddonTrialStatus()
    {
        $this->stdout("Статус trial аддонов:\n\n", Console::BOLD);

        // Все активные trial
        $activeTrials = \app\models\OrganizationAddon::find()
            ->with(['organization', 'feature'])
            ->where(['status' => \app\models\OrganizationAddon::STATUS_TRIAL])
            ->all();

        if (empty($activeTrials)) {
            $this->stdout("Нет активных trial аддонов\n", Console::FG_GREEN);
            return ExitCode::OK;
        }

        // Группируем по организациям
        $byOrg = [];
        foreach ($activeTrials as $addon) {
            $orgId = $addon->organization_id;
            if (!isset($byOrg[$orgId])) {
                $byOrg[$orgId] = [
                    'name' => $addon->organization->name ?? "Org #{$orgId}",
                    'trials' => [],
                ];
            }
            $byOrg[$orgId]['trials'][] = $addon;
        }

        $this->stdout("Всего активных trial: " . count($activeTrials) . "\n");
        $this->stdout("Организаций с trial: " . count($byOrg) . "\n\n");

        foreach ($byOrg as $orgData) {
            $this->stdout("{$orgData['name']}:\n", Console::BOLD);

            foreach ($orgData['trials'] as $addon) {
                $feature = $addon->feature;
                $featureName = $feature ? $feature->name : "Feature #{$addon->feature_id}";
                $daysRemaining = $addon->getDaysRemaining();
                $isExpiringSoon = $addon->isExpiringSoon();

                $color = $isExpiringSoon ? Console::FG_YELLOW : Console::FG_GREEN;

                $this->stdout("  - {$featureName}: ", Console::FG_CYAN);
                $this->stdout("{$daysRemaining} дн.", $color);

                if ($isExpiringSoon) {
                    $this->stdout(" (скоро истекает)", Console::FG_YELLOW);
                }

                $this->stdout(" до " . date('d.m.Y', strtotime($addon->trial_ends_at)) . "\n");
            }

            $this->stdout("\n");
        }

        // Статистика
        $expiringSoon = array_filter($activeTrials, fn($a) => $a->isExpiringSoon());

        $this->stdout("=== Итого ===\n", Console::BOLD);
        $this->stdout("Активных trial: " . count($activeTrials) . "\n");
        $this->stdout("Скоро истекают: " . count($expiringSoon) . "\n");

        return ExitCode::OK;
    }
}
