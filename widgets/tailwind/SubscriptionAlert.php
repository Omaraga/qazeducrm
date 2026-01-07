<?php

namespace app\widgets\tailwind;

use app\helpers\FeatureHelper;
use app\helpers\OrganizationUrl;
use app\models\Organizations;
use app\models\OrganizationSubscription;
use app\services\SubscriptionAccessService;
use Yii;
use yii\base\Widget;
use yii\helpers\Html;

/**
 * SubscriptionAlert Widget - алерты о статусе подписки
 *
 * Показывает предупреждения о:
 * - Истечении подписки
 * - Окончании trial периода
 * - Превышении лимитов
 *
 * Использование:
 * ```php
 * // В layout после основных алертов
 * <?= SubscriptionAlert::widget() ?>
 *
 * // Только критические алерты
 * <?= SubscriptionAlert::widget(['level' => 'critical']) ?>
 *
 * // Статический метод
 * <?= SubscriptionAlert::show() ?>
 * ```
 */
class SubscriptionAlert extends Widget
{
    /**
     * @var string уровень алертов: all, warning, critical
     */
    public $level = 'all';

    /**
     * @var bool можно ли закрыть алерт
     */
    public $dismissible = true;

    /**
     * @var array дополнительные HTML атрибуты
     */
    public $options = [];

    /**
     * {@inheritdoc}
     */
    public function run()
    {
        return self::show([
            'level' => $this->level,
            'dismissible' => $this->dismissible,
            'options' => $this->options,
        ]);
    }

    /**
     * Показать алерты подписки
     */
    public static function show(array $options = []): string
    {
        $level = $options['level'] ?? 'all';
        $dismissible = $options['dismissible'] ?? true;

        $org = Organizations::getCurrentOrganization();
        if (!$org) {
            return '';
        }

        $subscription = $org->getActiveSubscription();
        $alerts = [];

        // Проверка режима доступа (graceful degradation)
        $accessAlerts = self::getAccessModeAlerts($org, $subscription);
        if (!empty($accessAlerts)) {
            $alerts = array_merge($alerts, $accessAlerts);
        }

        // Проверка отсутствия подписки
        if (!$subscription && empty($accessAlerts)) {
            $alerts[] = [
                'type' => 'warning',
                'icon' => 'exclamation-triangle',
                'message' => Yii::t('main', 'У вас нет активной подписки. Функции системы ограничены.'),
                'action' => [
                    'label' => Yii::t('main', 'Выбрать план'),
                    'url' => OrganizationUrl::to(['subscription/plans']),
                ],
                'priority' => 'critical',
            ];
        } elseif ($subscription) {
            $daysRemaining = $subscription->getDaysRemaining();
            $isTrial = $subscription->isTrial();

            // Trial заканчивается
            if ($isTrial && $daysRemaining !== null) {
                if ($daysRemaining <= 0) {
                    $alerts[] = [
                        'type' => 'danger',
                        'icon' => 'exclamation-circle',
                        'message' => Yii::t('main', 'Пробный период закончился. Оформите подписку, чтобы продолжить работу.'),
                        'action' => [
                            'label' => Yii::t('main', 'Оформить подписку'),
                            'url' => OrganizationUrl::to(['subscription/plans']),
                        ],
                        'priority' => 'critical',
                    ];
                } elseif ($daysRemaining <= 3) {
                    $alerts[] = [
                        'type' => 'warning',
                        'icon' => 'clock',
                        'message' => Yii::t('main', 'Пробный период заканчивается через {n} дн. Оформите подписку.', ['n' => $daysRemaining]),
                        'action' => [
                            'label' => Yii::t('main', 'Оформить подписку'),
                            'url' => OrganizationUrl::to(['subscription/plans']),
                        ],
                        'priority' => 'warning',
                    ];
                } elseif ($daysRemaining <= 7 && $level === 'all') {
                    $alerts[] = [
                        'type' => 'info',
                        'icon' => 'information-circle',
                        'message' => Yii::t('main', 'Пробный период: осталось {n} дн.', ['n' => $daysRemaining]),
                        'action' => [
                            'label' => Yii::t('main', 'Посмотреть планы'),
                            'url' => OrganizationUrl::to(['subscription/plans']),
                        ],
                        'priority' => 'info',
                    ];
                }
            }
            // Подписка истекает
            elseif (!$isTrial && $daysRemaining !== null) {
                if ($daysRemaining <= 0) {
                    $alerts[] = [
                        'type' => 'danger',
                        'icon' => 'exclamation-circle',
                        'message' => Yii::t('main', 'Подписка истекла. Продлите подписку для продолжения работы.'),
                        'action' => [
                            'label' => Yii::t('main', 'Продлить'),
                            'url' => OrganizationUrl::to(['subscription/renew']),
                        ],
                        'priority' => 'critical',
                    ];
                } elseif ($daysRemaining <= 3) {
                    $alerts[] = [
                        'type' => 'warning',
                        'icon' => 'exclamation-triangle',
                        'message' => Yii::t('main', 'Подписка истекает через {n} дн. Продлите заранее.', ['n' => $daysRemaining]),
                        'action' => [
                            'label' => Yii::t('main', 'Продлить'),
                            'url' => OrganizationUrl::to(['subscription/renew']),
                        ],
                        'priority' => 'warning',
                    ];
                } elseif ($daysRemaining <= 7 && $level === 'all') {
                    $alerts[] = [
                        'type' => 'info',
                        'icon' => 'information-circle',
                        'message' => Yii::t('main', 'Подписка истекает через {n} дн.', ['n' => $daysRemaining]),
                        'action' => [
                            'label' => Yii::t('main', 'Продлить'),
                            'url' => OrganizationUrl::to(['subscription/renew']),
                        ],
                        'priority' => 'info',
                    ];
                }
            }
        }

        // Проверка лимитов
        if ($level !== 'critical') {
            $alerts = array_merge($alerts, self::getLimitAlerts($level));
        }

        // Фильтрация по уровню
        if ($level === 'critical') {
            $alerts = array_filter($alerts, fn($a) => $a['priority'] === 'critical');
        } elseif ($level === 'warning') {
            $alerts = array_filter($alerts, fn($a) => in_array($a['priority'], ['critical', 'warning']));
        }

        if (empty($alerts)) {
            return '';
        }

        $html = '';
        foreach ($alerts as $alert) {
            $html .= self::renderAlert($alert, $dismissible);
        }

        return $html;
    }

    /**
     * Получить алерты о режиме доступа (graceful degradation)
     */
    private static function getAccessModeAlerts(Organizations $org, ?OrganizationSubscription $subscription): array
    {
        $alerts = [];

        try {
            $accessService = SubscriptionAccessService::forOrganization($org);
            $mode = $accessService->getAccessMode();

            // Блокировка
            if ($mode === SubscriptionAccessService::MODE_BLOCKED) {
                $alerts[] = [
                    'type' => 'danger',
                    'icon' => 'lock-closed',
                    'message' => Yii::t('main', 'Доступ заблокирован. Для продолжения работы необходимо оплатить подписку.'),
                    'action' => [
                        'label' => Yii::t('main', 'Оплатить'),
                        'url' => OrganizationUrl::to(['subscription/renew']),
                    ],
                    'priority' => 'critical',
                ];
                return $alerts;
            }

            // Только чтение
            if ($mode === SubscriptionAccessService::MODE_READ_ONLY) {
                $daysUntilBlock = $accessService->getDaysUntilBlock();
                $alerts[] = [
                    'type' => 'warning',
                    'icon' => 'eye',
                    'message' => Yii::t('main', 'Режим только для чтения. Редактирование и создание записей недоступно. Через {n} дн. доступ будет заблокирован.', ['n' => $daysUntilBlock]),
                    'action' => [
                        'label' => Yii::t('main', 'Продлить подписку'),
                        'url' => OrganizationUrl::to(['subscription/renew']),
                    ],
                    'priority' => 'critical',
                ];
                return $alerts;
            }

            // Ограниченный доступ (grace period)
            if ($mode === SubscriptionAccessService::MODE_LIMITED) {
                $daysUntilReadOnly = $accessService->getDaysUntilReadOnly();
                $alerts[] = [
                    'type' => 'warning',
                    'icon' => 'exclamation-triangle',
                    'message' => Yii::t('main', 'Подписка истекла. Создание новых записей ограничено. Через {n} дн. режим сменится на «только чтение».', ['n' => $daysUntilReadOnly]),
                    'action' => [
                        'label' => Yii::t('main', 'Продлить подписку'),
                        'url' => OrganizationUrl::to(['subscription/renew']),
                    ],
                    'priority' => 'warning',
                ];
            }
        } catch (\Exception $e) {
            Yii::error('Error checking access mode: ' . $e->getMessage(), 'subscription');
        }

        return $alerts;
    }

    /**
     * Получить алерты о лимитах
     */
    private static function getLimitAlerts(string $level): array
    {
        $alerts = [];
        $usageInfo = FeatureHelper::getUsageInfo();

        if (empty($usageInfo)) {
            return $alerts;
        }

        $limitTypes = [
            'pupils' => Yii::t('main', 'учеников'),
            'groups' => Yii::t('main', 'групп'),
            'teachers' => Yii::t('main', 'учителей'),
        ];

        foreach ($limitTypes as $type => $label) {
            if (!isset($usageInfo[$type])) {
                continue;
            }

            $info = $usageInfo[$type];
            $limit = $info['limit'] ?? 0;

            if ($limit === 0) {
                continue; // Безлимит
            }

            $current = $info['current'] ?? 0;
            $percent = $limit > 0 ? round(($current / $limit) * 100) : 0;

            if ($percent >= 100) {
                $alerts[] = [
                    'type' => 'warning',
                    'icon' => 'exclamation-triangle',
                    'message' => Yii::t('main', 'Достигнут лимит {label}. Увеличьте лимит для добавления новых.', ['label' => $label]),
                    'action' => [
                        'label' => Yii::t('main', 'Увеличить'),
                        'url' => OrganizationUrl::to(['subscription/upgrade']),
                    ],
                    'priority' => 'warning',
                ];
            } elseif ($percent >= 90 && $level === 'all') {
                $remaining = $info['remaining'] ?? 0;
                $alerts[] = [
                    'type' => 'info',
                    'icon' => 'information-circle',
                    'message' => Yii::t('main', 'Использовано {percent}% лимита {label}. Осталось: {remaining}.', [
                        'percent' => $percent,
                        'label' => $label,
                        'remaining' => $remaining,
                    ]),
                    'priority' => 'info',
                ];
            }
        }

        return $alerts;
    }

    /**
     * Рендер одного алерта
     */
    private static function renderAlert(array $alert, bool $dismissible): string
    {
        $type = $alert['type'] ?? 'info';
        $icon = $alert['icon'] ?? 'information-circle';
        $message = $alert['message'] ?? '';
        $action = $alert['action'] ?? null;

        $colorClasses = match ($type) {
            'danger' => 'bg-danger-50 border-danger-200 text-danger-800',
            'warning' => 'bg-warning-50 border-warning-200 text-warning-800',
            'success' => 'bg-success-50 border-success-200 text-success-800',
            default => 'bg-blue-50 border-blue-200 text-blue-800',
        };

        $iconColorClass = match ($type) {
            'danger' => 'text-danger-500',
            'warning' => 'text-warning-500',
            'success' => 'text-success-500',
            default => 'text-blue-500',
        };

        $buttonClasses = match ($type) {
            'danger' => 'bg-danger-600 hover:bg-danger-700 text-white',
            'warning' => 'bg-warning-600 hover:bg-warning-700 text-white',
            default => 'bg-primary-600 hover:bg-primary-700 text-white',
        };

        $html = '<div class="subscription-alert rounded-lg border p-4 mb-4 ' . $colorClasses . '">';
        $html .= '<div class="flex items-start gap-3">';

        // Иконка
        $html .= '<div class="flex-shrink-0 ' . $iconColorClass . '">';
        $html .= Icon::show($icon, 'md');
        $html .= '</div>';

        // Контент
        $html .= '<div class="flex-1 min-w-0">';
        $html .= '<p class="text-sm font-medium">' . Html::encode($message) . '</p>';
        $html .= '</div>';

        // Действие
        if ($action) {
            $html .= '<div class="flex-shrink-0">';
            $html .= '<a href="' . Html::encode($action['url']) . '" class="inline-flex items-center px-3 py-1.5 text-xs font-medium rounded-md ' . $buttonClasses . '">';
            $html .= Html::encode($action['label']);
            $html .= '</a>';
            $html .= '</div>';
        }

        // Кнопка закрытия
        if ($dismissible) {
            $html .= '<button type="button" class="flex-shrink-0 -mr-1 -mt-1 p-1 rounded hover:bg-black/5" onclick="this.closest(\'.subscription-alert\').remove()">';
            $html .= Icon::show('x-mark', 'sm');
            $html .= '</button>';
        }

        $html .= '</div>';
        $html .= '</div>';

        return $html;
    }

    /**
     * Баннер для верхней части страницы (полноширинный)
     */
    public static function banner(array $options = []): string
    {
        $org = Organizations::getCurrentOrganization();
        if (!$org) {
            return '';
        }

        $subscription = $org->getActiveSubscription();
        if (!$subscription) {
            return self::renderBanner(
                'warning',
                Yii::t('main', 'Нет активной подписки'),
                Yii::t('main', 'Выбрать план'),
                OrganizationUrl::to(['subscription/plans'])
            );
        }

        $daysRemaining = $subscription->getDaysRemaining();
        $isTrial = $subscription->isTrial();

        if ($isTrial && $daysRemaining !== null && $daysRemaining <= 3) {
            return self::renderBanner(
                'warning',
                Yii::t('main', 'Пробный период заканчивается через {n} дн.', ['n' => $daysRemaining]),
                Yii::t('main', 'Оформить подписку'),
                OrganizationUrl::to(['subscription/plans'])
            );
        }

        if (!$isTrial && $daysRemaining !== null && $daysRemaining <= 3) {
            return self::renderBanner(
                $daysRemaining <= 0 ? 'danger' : 'warning',
                $daysRemaining <= 0
                    ? Yii::t('main', 'Подписка истекла')
                    : Yii::t('main', 'Подписка истекает через {n} дн.', ['n' => $daysRemaining]),
                Yii::t('main', 'Продлить'),
                OrganizationUrl::to(['subscription/renew'])
            );
        }

        return '';
    }

    /**
     * Рендер баннера
     */
    private static function renderBanner(string $type, string $message, string $actionLabel, string $actionUrl): string
    {
        $bgClass = match ($type) {
            'danger' => 'bg-danger-600',
            'warning' => 'bg-warning-500',
            default => 'bg-primary-600',
        };

        $html = '<div class="' . $bgClass . ' text-white py-2 px-4">';
        $html .= '<div class="container mx-auto flex items-center justify-between">';
        $html .= '<p class="text-sm font-medium">' . Html::encode($message) . '</p>';
        $html .= '<a href="' . Html::encode($actionUrl) . '" class="text-sm font-semibold underline hover:no-underline">';
        $html .= Html::encode($actionLabel) . ' →';
        $html .= '</a>';
        $html .= '</div>';
        $html .= '</div>';

        return $html;
    }
}
