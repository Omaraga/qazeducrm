<?php

namespace app\behaviors;

use Yii;
use yii\base\Behavior;
use yii\base\ActionEvent;
use yii\web\Controller;
use app\models\Organizations;
use app\services\SubscriptionLimitService;
use app\services\SubscriptionAccessService;

/**
 * Behavior для показа предупреждений о лимитах и подписке
 *
 * Использование в контроллере:
 * ```php
 * public function behaviors()
 * {
 *     return [
 *         'subscriptionWarning' => [
 *             'class' => SubscriptionWarningBehavior::class,
 *         ],
 *     ];
 * }
 * ```
 *
 * Или глобально в BaseController
 */
class SubscriptionWarningBehavior extends Behavior
{
    /**
     * @var int Порог предупреждения о лимите (процент)
     */
    public int $limitWarningThreshold = 80;

    /**
     * @var int Порог критического предупреждения о лимите (процент)
     */
    public int $limitCriticalThreshold = 95;

    /**
     * @var int Дней до истечения подписки для предупреждения
     */
    public int $subscriptionWarningDays = 7;

    /**
     * @var array Действия, исключённые из проверки
     */
    public array $except = ['login', 'logout', 'error', 'captcha'];

    /**
     * @var bool Включить проверку лимитов
     */
    public bool $checkLimits = true;

    /**
     * @var bool Включить проверку подписки
     */
    public bool $checkSubscription = true;

    /**
     * @var string Ключ сессии для хранения состояния dismiss
     */
    private string $dismissSessionKey = 'subscription_warning_dismissed';

    /**
     * {@inheritdoc}
     */
    public function events()
    {
        return [
            Controller::EVENT_BEFORE_ACTION => 'beforeAction',
        ];
    }

    /**
     * Проверка и показ предупреждений
     */
    public function beforeAction(ActionEvent $event): bool
    {
        // Пропускаем консольные команды
        if (Yii::$app instanceof \yii\console\Application) {
            return true;
        }

        // Пропускаем AJAX-запросы (кроме определённых)
        if (Yii::$app->request->isAjax && !$this->shouldCheckAjax($event)) {
            return true;
        }

        // Пропускаем гостей
        if (Yii::$app->user->isGuest) {
            return true;
        }

        $actionId = $event->action->id;

        // Проверяем исключения
        if (in_array($actionId, $this->except)) {
            return true;
        }

        // Не показываем предупреждения, если пользователь их закрыл
        if ($this->isWarningDismissed()) {
            return true;
        }

        $warnings = [];

        // Проверка подписки
        if ($this->checkSubscription) {
            $subscriptionWarnings = $this->getSubscriptionWarnings();
            $warnings = array_merge($warnings, $subscriptionWarnings);
        }

        // Проверка лимитов
        if ($this->checkLimits) {
            $limitWarnings = $this->getLimitWarnings();
            $warnings = array_merge($warnings, $limitWarnings);
        }

        // Показываем предупреждения
        if (!empty($warnings)) {
            $this->showWarnings($warnings);
        }

        return true;
    }

    /**
     * Получить предупреждения о подписке
     */
    protected function getSubscriptionWarnings(): array
    {
        $warnings = [];

        $organization = Organizations::getCurrentOrganization();
        if (!$organization) {
            return $warnings;
        }

        $subscription = $organization->getActiveSubscription();
        if (!$subscription) {
            $warnings[] = [
                'type' => 'warning',
                'category' => 'subscription',
                'message' => 'У вас нет активной подписки. Функционал ограничен.',
                'action' => [
                    'label' => 'Выбрать тариф',
                    'url' => '/subscription',
                ],
            ];
            return $warnings;
        }

        // Проверка режима доступа
        $accessService = SubscriptionAccessService::forCurrentOrganization();
        $bannerData = $accessService->getWarningBannerData();

        if ($bannerData) {
            $warnings[] = [
                'type' => $bannerData['type'],
                'category' => 'access',
                'message' => $bannerData['message'],
                'title' => $bannerData['title'],
                'action' => $bannerData['action'] ?? null,
                'dismissible' => $bannerData['dismissible'] ?? false,
            ];
        }

        // Проверка триала
        if ($subscription->status === 'trial') {
            $daysRemaining = $subscription->getDaysRemaining();

            if ($daysRemaining <= 3) {
                $warnings[] = [
                    'type' => 'warning',
                    'category' => 'trial',
                    'message' => "Пробный период заканчивается через {$daysRemaining} дн. " .
                                 "Выберите тариф для продолжения работы.",
                    'action' => [
                        'label' => 'Выбрать тариф',
                        'url' => '/subscription',
                    ],
                ];
            }
        }

        return $warnings;
    }

    /**
     * Получить предупреждения о лимитах
     */
    protected function getLimitWarnings(): array
    {
        $warnings = [];

        $limitService = SubscriptionLimitService::forCurrentOrganization();
        if (!$limitService) {
            return $warnings;
        }

        $limits = [
            'max_pupils' => ['name' => 'учеников', 'url' => '/pupil'],
            'max_groups' => ['name' => 'групп', 'url' => '/group'],
            'max_teachers' => ['name' => 'учителей', 'url' => '/user?role=teacher'],
        ];

        foreach ($limits as $field => $info) {
            $usagePercent = $limitService->getUsagePercent($field);

            if ($usagePercent === null) {
                continue; // Безлимит
            }

            if ($usagePercent >= 100) {
                $warnings[] = [
                    'type' => 'danger',
                    'category' => 'limit',
                    'field' => $field,
                    'message' => "Лимит {$info['name']} исчерпан. " .
                                 "Удалите записи или перейдите на более высокий тариф.",
                    'action' => [
                        'label' => 'Увеличить лимит',
                        'url' => '/subscription/upgrade',
                    ],
                ];
            } elseif ($usagePercent >= $this->limitCriticalThreshold) {
                $warnings[] = [
                    'type' => 'warning',
                    'category' => 'limit',
                    'field' => $field,
                    'message' => "Использовано {$usagePercent}% лимита {$info['name']}. " .
                                 "Скоро создание новых записей будет ограничено.",
                    'action' => [
                        'label' => 'Увеличить лимит',
                        'url' => '/subscription/upgrade',
                    ],
                    'dismissible' => true,
                ];
            } elseif ($usagePercent >= $this->limitWarningThreshold) {
                $warnings[] = [
                    'type' => 'info',
                    'category' => 'limit',
                    'field' => $field,
                    'message' => "Использовано {$usagePercent}% лимита {$info['name']}.",
                    'dismissible' => true,
                ];
            }
        }

        return $warnings;
    }

    /**
     * Показать предупреждения через flash-сообщения
     */
    protected function showWarnings(array $warnings): void
    {
        // Группируем предупреждения по типу
        $grouped = [
            'danger' => [],
            'warning' => [],
            'info' => [],
        ];

        foreach ($warnings as $warning) {
            $type = $warning['type'];
            if (!isset($grouped[$type])) {
                $type = 'warning';
            }
            $grouped[$type][] = $warning;
        }

        // Показываем только самые важные (не более 2)
        $shown = 0;
        $maxWarnings = 2;

        foreach (['danger', 'warning', 'info'] as $type) {
            if ($shown >= $maxWarnings) {
                break;
            }

            foreach ($grouped[$type] as $warning) {
                if ($shown >= $maxWarnings) {
                    break;
                }

                $message = $warning['message'];

                // Добавляем ссылку на действие
                if (!empty($warning['action'])) {
                    $url = $warning['action']['url'];
                    $label = $warning['action']['label'];
                    $message .= " <a href=\"{$url}\" class=\"alert-link\">{$label} →</a>";
                }

                // Используем разные ключи для разных предупреждений
                $flashKey = $type . '-' . ($warning['category'] ?? 'general');
                Yii::$app->session->setFlash($flashKey, $message);

                $shown++;
            }
        }
    }

    /**
     * Проверить, закрыл ли пользователь предупреждения
     */
    protected function isWarningDismissed(): bool
    {
        $dismissed = Yii::$app->session->get($this->dismissSessionKey, []);
        $today = date('Y-m-d');

        // Предупреждения сбрасываются каждый день
        return isset($dismissed[$today]);
    }

    /**
     * Закрыть предупреждения на сегодня
     */
    public function dismissWarnings(): void
    {
        $today = date('Y-m-d');
        Yii::$app->session->set($this->dismissSessionKey, [$today => true]);
    }

    /**
     * Проверять ли AJAX-запросы
     */
    protected function shouldCheckAjax(ActionEvent $event): bool
    {
        // Проверяем только определённые AJAX-действия
        $ajaxActionsToCheck = ['index', 'dashboard'];
        return in_array($event->action->id, $ajaxActionsToCheck);
    }

    /**
     * Получить данные для виджета предупреждений (для использования в layout)
     */
    public function getWarningsData(): array
    {
        $warnings = [];

        if ($this->checkSubscription) {
            $warnings = array_merge($warnings, $this->getSubscriptionWarnings());
        }

        if ($this->checkLimits) {
            $warnings = array_merge($warnings, $this->getLimitWarnings());
        }

        return $warnings;
    }

    /**
     * Получить самое критичное предупреждение (для баннера)
     */
    public function getMostCriticalWarning(): ?array
    {
        $warnings = $this->getWarningsData();

        if (empty($warnings)) {
            return null;
        }

        // Сортируем по критичности
        $priority = ['danger' => 0, 'warning' => 1, 'info' => 2];

        usort($warnings, function ($a, $b) use ($priority) {
            $pa = $priority[$a['type']] ?? 3;
            $pb = $priority[$b['type']] ?? 3;
            return $pa - $pb;
        });

        return $warnings[0];
    }
}
