<?php

namespace app\widgets\tailwind;

use app\models\Organizations;
use app\models\OrganizationSubscription;
use Yii;
use yii\base\Widget;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * SubscriptionBadge Widget - бейдж текущего тарифа организации
 *
 * Использование:
 * ```php
 * // Полный вид
 * <?= SubscriptionBadge::widget() ?>
 *
 * // Компактный вид (только название плана)
 * <?= SubscriptionBadge::widget(['compact' => true]) ?>
 *
 * // С информацией о trial
 * <?= SubscriptionBadge::widget(['showTrial' => true]) ?>
 *
 * // Статический метод
 * <?= SubscriptionBadge::show() ?>
 * ```
 */
class SubscriptionBadge extends Widget
{
    /**
     * @var bool компактный режим (только название)
     */
    public $compact = false;

    /**
     * @var bool показывать информацию о trial
     */
    public $showTrial = true;

    /**
     * @var bool показывать дни до истечения
     */
    public $showExpiry = true;

    /**
     * @var bool делать бейдж кликабельным (ссылка на страницу подписки)
     */
    public $clickable = true;

    /**
     * @var array дополнительные HTML атрибуты
     */
    public $options = [];

    /**
     * Цвета для тарифов
     */
    const PLAN_COLORS = [
        'free' => 'gray',
        'basic' => 'primary',
        'pro' => 'purple',
        'enterprise' => 'warning',
    ];

    /**
     * {@inheritdoc}
     */
    public function run()
    {
        return self::show([
            'compact' => $this->compact,
            'showTrial' => $this->showTrial,
            'showExpiry' => $this->showExpiry,
            'clickable' => $this->clickable,
            'options' => $this->options,
        ]);
    }

    /**
     * Показать бейдж подписки
     */
    public static function show(array $options = []): string
    {
        $compact = $options['compact'] ?? false;
        $showTrial = $options['showTrial'] ?? true;
        $showExpiry = $options['showExpiry'] ?? true;
        $clickable = $options['clickable'] ?? true;
        $htmlOptions = $options['options'] ?? [];

        $org = Organizations::getCurrentOrganization();
        if (!$org) {
            return '';
        }

        $subscription = $org->getActiveSubscription();
        if (!$subscription) {
            $badge = self::renderNoPlan($htmlOptions);
            return $clickable ? self::wrapInLink($badge) : $badge;
        }

        $plan = $subscription->saasPlan;
        if (!$plan) {
            $badge = self::renderNoPlan($htmlOptions);
            return $clickable ? self::wrapInLink($badge) : $badge;
        }

        $planCode = $plan->code ?? 'free';
        $planName = $plan->name ?? 'Free';
        $color = self::PLAN_COLORS[$planCode] ?? 'gray';
        $isTrial = $subscription->isTrial();
        $daysRemaining = $subscription->getDaysRemaining();

        if ($compact) {
            $badge = self::renderCompact($planName, $color, $isTrial, $htmlOptions);
        } else {
            $badge = self::renderFull($planName, $color, $isTrial, $daysRemaining, $showTrial, $showExpiry, $htmlOptions);
        }

        return $clickable ? self::wrapInLink($badge) : $badge;
    }

    /**
     * Обернуть бейдж в ссылку на страницу подписки
     */
    private static function wrapInLink(string $badge): string
    {
        return Html::a($badge, ['/crm/subscription/index'], [
            'class' => 'hover:opacity-80 transition-opacity',
            'title' => Yii::t('main', 'Управление подпиской'),
        ]);
    }

    /**
     * Рендер при отсутствии плана
     */
    private static function renderNoPlan(array $htmlOptions = []): string
    {
        $class = 'inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-600';

        if (isset($htmlOptions['class'])) {
            $class .= ' ' . $htmlOptions['class'];
            unset($htmlOptions['class']);
        }

        return Html::tag('span',
            Icon::show('exclamation-circle', 'xs') . ' ' . Yii::t('main', 'Нет плана'),
            array_merge(['class' => $class], $htmlOptions)
        );
    }

    /**
     * Компактный рендер
     */
    private static function renderCompact(string $planName, string $color, bool $isTrial, array $htmlOptions = []): string
    {
        $colorClasses = self::getColorClasses($color);
        $class = "inline-flex items-center gap-1 px-2 py-0.5 text-xs font-semibold rounded-full {$colorClasses}";

        if (isset($htmlOptions['class'])) {
            $class .= ' ' . $htmlOptions['class'];
            unset($htmlOptions['class']);
        }

        $content = Html::encode($planName);

        if ($isTrial) {
            $content .= ' <span class="text-[10px] opacity-75">(trial)</span>';
        }

        return Html::tag('span', $content, array_merge(['class' => $class], $htmlOptions));
    }

    /**
     * Полный рендер
     */
    private static function renderFull(
        string $planName,
        string $color,
        bool $isTrial,
        ?int $daysRemaining,
        bool $showTrial,
        bool $showExpiry,
        array $htmlOptions = []
    ): string {
        $colorClasses = self::getColorClasses($color);

        $class = 'inline-flex items-center gap-2';
        if (isset($htmlOptions['class'])) {
            $class .= ' ' . $htmlOptions['class'];
            unset($htmlOptions['class']);
        }

        $html = '<span class="' . $class . '">';

        // Бейдж плана
        $html .= '<span class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-semibold rounded-full ' . $colorClasses . '">';
        $html .= Html::encode($planName);
        $html .= '</span>';

        // Trial информация
        if ($isTrial && $showTrial && $daysRemaining !== null) {
            $html .= '<span class="text-xs text-warning-600 font-medium">';
            $html .= Icon::show('clock', 'xs') . ' ';
            $html .= Yii::t('main', 'Trial: {n} дн.', ['n' => $daysRemaining]);
            $html .= '</span>';
        }
        // Информация об истечении (не trial)
        elseif (!$isTrial && $showExpiry && $daysRemaining !== null && $daysRemaining <= 14) {
            $expiryClass = $daysRemaining <= 3 ? 'text-danger-600' : 'text-warning-600';
            $html .= '<span class="text-xs ' . $expiryClass . ' font-medium">';
            $html .= Icon::show('exclamation-triangle', 'xs') . ' ';
            if ($daysRemaining <= 0) {
                $html .= Yii::t('main', 'Истекает сегодня');
            } else {
                $html .= Yii::t('main', 'Истекает через {n} дн.', ['n' => $daysRemaining]);
            }
            $html .= '</span>';
        }

        $html .= '</span>';

        return $html;
    }

    /**
     * Получить CSS классы для цвета
     */
    private static function getColorClasses(string $color): string
    {
        return match ($color) {
            'primary' => 'bg-primary-100 text-primary-800',
            'success' => 'bg-success-100 text-success-800',
            'warning' => 'bg-warning-100 text-warning-800',
            'danger' => 'bg-danger-100 text-danger-800',
            'purple' => 'bg-purple-100 text-purple-800',
            'indigo' => 'bg-indigo-100 text-indigo-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    /**
     * Бейдж "PRO" для меню
     */
    public static function proBadge(): string
    {
        return '<span class="ml-1 px-1.5 py-0.5 text-[10px] font-bold rounded bg-gradient-to-r from-purple-500 to-indigo-500 text-white">PRO</span>';
    }

    /**
     * Иконка замка для недоступных функций
     */
    public static function lockIcon(string $size = 'sm'): string
    {
        return Icon::show('lock-closed', $size, ['class' => 'text-gray-400']);
    }
}
