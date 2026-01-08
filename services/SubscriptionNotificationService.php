<?php

namespace app\services;

use Yii;
use app\models\Organizations;
use app\models\OrganizationSubscription;
use app\models\SubscriptionNotificationLog;
use app\models\User;

/**
 * Сервис уведомлений о подписках
 */
class SubscriptionNotificationService
{
    /**
     * Уведомить об истекающей подписке
     */
    public function notifySubscriptionExpiring(OrganizationSubscription $subscription, int $daysRemaining): bool
    {
        $organization = $subscription->organization;
        if (!$organization) {
            return false;
        }

        // Проверяем, не отправляли ли уже
        if (SubscriptionNotificationLog::wasRecentlySent(
            $organization->id,
            SubscriptionNotificationLog::TYPE_SUBSCRIPTION_EXPIRING,
            24
        )) {
            return false;
        }

        $admin = $this->getOrganizationAdmin($organization);
        if (!$admin || !$admin->email) {
            return false;
        }

        $subject = "Подписка истекает через {$daysRemaining} дней";
        $message = $this->renderMessage('subscription-expiring', [
            'organization' => $organization,
            'subscription' => $subscription,
            'daysRemaining' => $daysRemaining,
            'expiresAt' => $subscription->expires_at,
        ]);

        return $this->sendEmail(
            $organization,
            $subscription,
            SubscriptionNotificationLog::TYPE_SUBSCRIPTION_EXPIRING,
            $admin->email,
            $subject,
            $message,
            ['days_remaining' => $daysRemaining]
        );
    }

    /**
     * Уведомить об истёкшей подписке
     */
    public function notifySubscriptionExpired(OrganizationSubscription $subscription): bool
    {
        $organization = $subscription->organization;
        if (!$organization) {
            return false;
        }

        if (SubscriptionNotificationLog::wasRecentlySent(
            $organization->id,
            SubscriptionNotificationLog::TYPE_SUBSCRIPTION_EXPIRED,
            24
        )) {
            return false;
        }

        $admin = $this->getOrganizationAdmin($organization);
        if (!$admin || !$admin->email) {
            return false;
        }

        $subject = "Ваша подписка истекла";
        $message = $this->renderMessage('subscription-expired', [
            'organization' => $organization,
            'subscription' => $subscription,
        ]);

        return $this->sendEmail(
            $organization,
            $subscription,
            SubscriptionNotificationLog::TYPE_SUBSCRIPTION_EXPIRED,
            $admin->email,
            $subject,
            $message
        );
    }

    /**
     * Уведомить о заканчивающемся триале
     */
    public function notifyTrialEnding(OrganizationSubscription $subscription, int $daysRemaining): bool
    {
        $organization = $subscription->organization;
        if (!$organization) {
            return false;
        }

        if (SubscriptionNotificationLog::wasRecentlySent(
            $organization->id,
            SubscriptionNotificationLog::TYPE_TRIAL_ENDING,
            24
        )) {
            return false;
        }

        $admin = $this->getOrganizationAdmin($organization);
        if (!$admin || !$admin->email) {
            return false;
        }

        $subject = "Пробный период заканчивается через {$daysRemaining} дней";
        $message = $this->renderMessage('trial-ending', [
            'organization' => $organization,
            'subscription' => $subscription,
            'daysRemaining' => $daysRemaining,
        ]);

        return $this->sendEmail(
            $organization,
            $subscription,
            SubscriptionNotificationLog::TYPE_TRIAL_ENDING,
            $admin->email,
            $subject,
            $message,
            ['days_remaining' => $daysRemaining]
        );
    }

    /**
     * Уведомить о достижении лимита
     */
    public function notifyLimitWarning(Organizations $organization, string $limitType, int $current, int $limit): bool
    {
        if (SubscriptionNotificationLog::wasRecentlySent(
            $organization->id,
            SubscriptionNotificationLog::TYPE_LIMIT_WARNING,
            72 // 3 дня
        )) {
            return false;
        }

        $admin = $this->getOrganizationAdmin($organization);
        if (!$admin || !$admin->email) {
            return false;
        }

        $limitLabels = [
            'pupils' => 'учеников',
            'groups' => 'групп',
            'teachers' => 'учителей',
        ];

        $limitLabel = $limitLabels[$limitType] ?? $limitType;
        $percent = round(($current / $limit) * 100);

        $subject = "Внимание: использовано {$percent}% лимита {$limitLabel}";
        $message = $this->renderMessage('limit-warning', [
            'organization' => $organization,
            'limitType' => $limitType,
            'limitLabel' => $limitLabel,
            'current' => $current,
            'limit' => $limit,
            'percent' => $percent,
        ]);

        return $this->sendEmail(
            $organization,
            null,
            SubscriptionNotificationLog::TYPE_LIMIT_WARNING,
            $admin->email,
            $subject,
            $message,
            ['limit_type' => $limitType, 'current' => $current, 'limit' => $limit]
        );
    }

    /**
     * Уведомить о достижении 100% лимита
     */
    public function notifyLimitReached(Organizations $organization, string $limitType, int $current, int $limit): bool
    {
        if (SubscriptionNotificationLog::wasRecentlySent(
            $organization->id,
            SubscriptionNotificationLog::TYPE_LIMIT_REACHED,
            24
        )) {
            return false;
        }

        $admin = $this->getOrganizationAdmin($organization);
        if (!$admin || !$admin->email) {
            return false;
        }

        $limitLabels = [
            'pupils' => 'учеников',
            'groups' => 'групп',
            'teachers' => 'учителей',
        ];

        $limitLabel = $limitLabels[$limitType] ?? $limitType;

        $subject = "Лимит {$limitLabel} достигнут";
        $message = $this->renderMessage('limit-reached', [
            'organization' => $organization,
            'limitType' => $limitType,
            'limitLabel' => $limitLabel,
            'current' => $current,
            'limit' => $limit,
        ]);

        return $this->sendEmail(
            $organization,
            null,
            SubscriptionNotificationLog::TYPE_LIMIT_REACHED,
            $admin->email,
            $subject,
            $message,
            ['limit_type' => $limitType, 'current' => $current, 'limit' => $limit]
        );
    }

    /**
     * Уведомить о начале grace периода
     */
    public function notifyGracePeriodStart(OrganizationSubscription $subscription, int $graceDays): bool
    {
        $organization = $subscription->organization;
        if (!$organization) {
            return false;
        }

        $admin = $this->getOrganizationAdmin($organization);
        if (!$admin || !$admin->email) {
            return false;
        }

        $subject = "Подписка истекла - {$graceDays} дней на продление";
        $message = $this->renderMessage('grace-period-start', [
            'organization' => $organization,
            'subscription' => $subscription,
            'graceDays' => $graceDays,
        ]);

        return $this->sendEmail(
            $organization,
            $subscription,
            SubscriptionNotificationLog::TYPE_GRACE_PERIOD_START,
            $admin->email,
            $subject,
            $message,
            ['grace_days' => $graceDays]
        );
    }

    /**
     * Уведомить о скором блокировании доступа
     */
    public function notifyAccessRestriction(OrganizationSubscription $subscription, string $accessMode): bool
    {
        $organization = $subscription->organization;
        if (!$organization) {
            return false;
        }

        $admin = $this->getOrganizationAdmin($organization);
        if (!$admin || !$admin->email) {
            return false;
        }

        $modeLabels = [
            'limited' => 'ограничен',
            'read_only' => 'только чтение',
            'blocked' => 'заблокирован',
        ];

        $subject = "Доступ к системе " . ($modeLabels[$accessMode] ?? $accessMode);
        $message = $this->renderMessage('access-restricted', [
            'organization' => $organization,
            'subscription' => $subscription,
            'accessMode' => $accessMode,
            'modeLabel' => $modeLabels[$accessMode] ?? $accessMode,
        ]);

        return $this->sendEmail(
            $organization,
            $subscription,
            SubscriptionNotificationLog::TYPE_ACCESS_RESTRICTED,
            $admin->email,
            $subject,
            $message,
            ['access_mode' => $accessMode]
        );
    }

    /**
     * Уведомить о получении платежа
     */
    public function notifyPaymentReceived(Organizations $organization, float $amount): bool
    {
        $admin = $this->getOrganizationAdmin($organization);
        if (!$admin || !$admin->email) {
            return false;
        }

        $subject = "Платёж получен";
        $message = $this->renderMessage('payment-received', [
            'organization' => $organization,
            'amount' => $amount,
        ]);

        return $this->sendEmail(
            $organization,
            null,
            SubscriptionNotificationLog::TYPE_PAYMENT_RECEIVED,
            $admin->email,
            $subject,
            $message,
            ['amount' => $amount]
        );
    }

    /**
     * Создать in-app уведомление
     */
    public function createInAppNotification(
        Organizations $organization,
        string $type,
        string $title,
        string $message,
        ?array $metadata = null
    ): SubscriptionNotificationLog {
        $log = SubscriptionNotificationLog::log(
            $organization->id,
            $type,
            SubscriptionNotificationLog::CHANNEL_IN_APP,
            null,
            $title,
            $message,
            $metadata
        );

        $log->markAsSent();
        return $log;
    }

    /**
     * Отправить email
     */
    private function sendEmail(
        Organizations $organization,
        ?OrganizationSubscription $subscription,
        string $type,
        string $email,
        string $subject,
        string $message,
        ?array $metadata = null
    ): bool {
        // Создаём запись лога
        $log = SubscriptionNotificationLog::log(
            $organization->id,
            $type,
            SubscriptionNotificationLog::CHANNEL_EMAIL,
            $email,
            $subject,
            $message,
            $metadata,
            $subscription !== null ? $subscription->id : null
        );

        try {
            $sent = Yii::$app->mailer->compose()
                ->setFrom([Yii::$app->params['senderEmail'] ?? 'noreply@example.com' => Yii::$app->params['senderName'] ?? 'QazEduCRM'])
                ->setTo($email)
                ->setSubject($subject)
                ->setHtmlBody($message)
                ->send();

            if ($sent) {
                $log->markAsSent();
                return true;
            } else {
                $log->markAsFailed('Email not sent');
                return false;
            }
        } catch (\Exception $e) {
            $log->markAsFailed($e->getMessage());
            Yii::error("Failed to send subscription notification: " . $e->getMessage(), 'subscription');
            return false;
        }
    }

    /**
     * Рендерить сообщение из шаблона
     */
    private function renderMessage(string $template, array $params): string
    {
        $viewPath = "@app/mail/subscription/{$template}";

        try {
            return Yii::$app->view->render($viewPath, $params);
        } catch (\Exception $e) {
            // Если шаблон не найден, создаём простое сообщение
            return $this->generateSimpleMessage($template, $params);
        }
    }

    /**
     * Сгенерировать простое сообщение
     */
    private function generateSimpleMessage(string $template, array $params): string
    {
        $organization = $params['organization'] ?? null;
        $orgName = $organization ? $organization->name : 'Ваша организация';

        return match ($template) {
            'subscription-expiring' => "
                <h2>Подписка истекает</h2>
                <p>Уважаемый клиент,</p>
                <p>Подписка организации <strong>{$orgName}</strong> истекает через {$params['daysRemaining']} дней.</p>
                <p>Пожалуйста, продлите подписку, чтобы продолжить использование системы.</p>
            ",
            'subscription-expired' => "
                <h2>Подписка истекла</h2>
                <p>Уважаемый клиент,</p>
                <p>Подписка организации <strong>{$orgName}</strong> истекла.</p>
                <p>Для продолжения работы необходимо оплатить подписку.</p>
            ",
            'trial-ending' => "
                <h2>Пробный период заканчивается</h2>
                <p>Уважаемый клиент,</p>
                <p>Пробный период организации <strong>{$orgName}</strong> заканчивается через {$params['daysRemaining']} дней.</p>
                <p>Оформите подписку, чтобы продолжить использование всех функций системы.</p>
            ",
            'limit-warning' => "
                <h2>Приближение к лимиту</h2>
                <p>Уважаемый клиент,</p>
                <p>Организация <strong>{$orgName}</strong> использовала {$params['percent']}% лимита {$params['limitLabel']}.</p>
                <p>Текущее использование: {$params['current']} из {$params['limit']}</p>
                <p>Рассмотрите возможность перехода на более высокий тариф.</p>
            ",
            'limit-reached' => "
                <h2>Лимит достигнут</h2>
                <p>Уважаемый клиент,</p>
                <p>Организация <strong>{$orgName}</strong> достигла лимита {$params['limitLabel']}.</p>
                <p>Для добавления новых записей необходимо перейти на более высокий тариф.</p>
            ",
            'grace-period-start' => "
                <h2>Grace период</h2>
                <p>Уважаемый клиент,</p>
                <p>Подписка организации <strong>{$orgName}</strong> истекла.</p>
                <p>У вас есть {$params['graceDays']} дней для продления подписки без потери данных.</p>
                <p>В течение этого периода создание новых записей будет ограничено.</p>
            ",
            'access-restricted' => "
                <h2>Доступ ограничен</h2>
                <p>Уважаемый клиент,</p>
                <p>Доступ к системе для организации <strong>{$orgName}</strong> был {$params['modeLabel']}.</p>
                <p>Для восстановления полного доступа необходимо продлить подписку.</p>
            ",
            'payment-received' => "
                <h2>Платёж получен</h2>
                <p>Уважаемый клиент,</p>
                <p>Мы получили ваш платёж на сумму " . number_format($params['amount'], 0, '', ' ') . " KZT.</p>
                <p>Спасибо за использование нашего сервиса!</p>
            ",
            default => "<p>Уведомление для организации {$orgName}</p>",
        };
    }

    /**
     * Получить администратора организации
     */
    private function getOrganizationAdmin(Organizations $organization): ?User
    {
        // Ищем owner или первого админа организации
        return User::find()
            ->innerJoin('user_organization', 'user_organization.user_id = user.id')
            ->where(['user_organization.organization_id' => $organization->id])
            ->andWhere(['user_organization.is_deleted' => 0])
            ->andWhere(['user.is_deleted' => 0])
            ->orderBy(['user_organization.id' => SORT_ASC])
            ->one();
    }

    /**
     * Получить непрочитанные in-app уведомления
     */
    public function getUnreadInAppNotifications(int $organizationId, int $limit = 10): array
    {
        return SubscriptionNotificationLog::find()
            ->where(['organization_id' => $organizationId])
            ->andWhere(['channel' => SubscriptionNotificationLog::CHANNEL_IN_APP])
            ->orderBy(['sent_at' => SORT_DESC])
            ->limit($limit)
            ->all();
    }
}
