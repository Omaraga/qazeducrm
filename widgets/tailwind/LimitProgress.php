<?php

namespace app\widgets\tailwind;

use app\helpers\FeatureHelper;
use app\helpers\OrganizationUrl;
use Yii;
use yii\base\Widget;
use yii\helpers\Html;

/**
 * LimitProgress Widget - прогресс-бар использования лимита тарифа
 *
 * Использование:
 * ```php
 * // Для учеников
 * <?= LimitProgress::widget(['type' => 'pupils']) ?>
 *
 * // Для групп
 * <?= LimitProgress::widget(['type' => 'groups']) ?>
 *
 * // Компактный вид (только текст)
 * <?= LimitProgress::widget(['type' => 'pupils', 'compact' => true]) ?>
 *
 * // Статический метод
 * <?= LimitProgress::show('teachers') ?>
 *
 * // Инлайн (для кнопок и заголовков)
 * <?= LimitProgress::inline('pupils') ?>
 * ```
 */
class LimitProgress extends Widget
{
    /**
     * @var string тип лимита: pupils, groups, teachers, admins, branches
     */
    public $type;

    /**
     * @var bool компактный режим (только текст без прогресс-бара)
     */
    public $compact = false;

    /**
     * @var bool показывать предупреждение при приближении к лимиту
     */
    public $showWarning = true;

    /**
     * @var bool показывать кнопку апгрейда
     */
    public $showUpgrade = true;

    /**
     * @var bool показывать кнопку действия (алиас для showUpgrade)
     */
    public $showAction = null;

    /**
     * @var array дополнительные HTML атрибуты
     */
    public $options = [];

    /**
     * Метки для типов
     */
    const TYPE_LABELS = [
        'pupils' => 'Ученики',
        'groups' => 'Группы',
        'teachers' => 'Учителя',
        'admins' => 'Администраторы',
        'branches' => 'Филиалы',
    ];

    /**
     * Поля лимитов
     */
    const TYPE_FIELDS = [
        'pupils' => 'max_pupils',
        'groups' => 'max_groups',
        'teachers' => 'max_teachers',
        'admins' => 'max_admins',
        'branches' => 'max_branches',
    ];

    /**
     * {@inheritdoc}
     */
    public function run()
    {
        // showAction - алиас для showUpgrade
        $showUpgrade = $this->showAction !== null ? $this->showAction : $this->showUpgrade;

        if ($this->compact) {
            return self::inline($this->type, $this->options);
        }

        return self::show($this->type, [
            'showWarning' => $this->showWarning,
            'showUpgrade' => $showUpgrade,
            'options' => $this->options,
        ]);
    }

    /**
     * Показать полный прогресс-бар
     */
    public static function show(string $type, array $options = []): string
    {
        $showWarning = $options['showWarning'] ?? true;
        $showUpgrade = $options['showUpgrade'] ?? true;
        $htmlOptions = $options['options'] ?? [];

        $usageInfo = FeatureHelper::getUsageInfo();
        if (empty($usageInfo) || !isset($usageInfo[$type])) {
            return '';
        }

        $info = $usageInfo[$type];
        $current = $info['current'] ?? 0;
        $limit = $info['limit'] ?? 0;
        $canAdd = $info['can_add'] ?? true;

        $field = self::TYPE_FIELDS[$type] ?? '';
        $percent = FeatureHelper::usagePercent($field);
        $label = Yii::t('main', self::TYPE_LABELS[$type] ?? $type);

        $isUnlimited = $limit === 0;
        $limitDisplay = $isUnlimited ? '∞' : $limit;

        $class = 'limit-progress';
        if (isset($htmlOptions['class'])) {
            $class .= ' ' . $htmlOptions['class'];
            unset($htmlOptions['class']);
        }

        $html = '<div class="' . $class . ' bg-white rounded-lg border border-gray-200 p-4">';

        // Заголовок с счётчиком
        $html .= '<div class="flex items-center justify-between mb-2">';
        $html .= '<span class="text-sm font-medium text-gray-700">' . Html::encode($label) . '</span>';
        $html .= '<span class="text-sm font-semibold text-gray-900">' . $current . ' / ' . $limitDisplay . '</span>';
        $html .= '</div>';

        // Прогресс-бар (если не безлимит)
        if (!$isUnlimited) {
            $progressColor = self::getProgressColor($percent);
            $html .= '<div class="w-full bg-gray-200 rounded-full h-2 mb-2">';
            $html .= '<div class="' . $progressColor . ' h-2 rounded-full transition-all duration-300" style="width: ' . min(100, $percent) . '%"></div>';
            $html .= '</div>';
        }

        // Предупреждение и кнопка
        $html .= '<div class="flex items-center justify-between">';

        if ($showWarning && !$isUnlimited) {
            if ($percent >= 100) {
                $html .= '<span class="text-xs text-danger-600 font-medium">';
                $html .= Icon::show('exclamation-circle', 'xs') . ' ';
                $html .= Yii::t('main', 'Лимит достигнут');
                $html .= '</span>';
            } elseif ($percent >= 90) {
                $remaining = $info['remaining'] ?? 0;
                $html .= '<span class="text-xs text-warning-600 font-medium">';
                $html .= Icon::show('exclamation-triangle', 'xs') . ' ';
                $html .= Yii::t('main', 'Осталось: {n}', ['n' => $remaining]);
                $html .= '</span>';
            } else {
                $html .= '<span></span>';
            }
        } else {
            $html .= '<span></span>';
        }

        if ($showUpgrade && !$canAdd) {
            $html .= '<a href="' . OrganizationUrl::to(['subscription/index']) . '" class="text-xs text-primary-600 hover:text-primary-700 font-medium">';
            $html .= Yii::t('main', 'Увеличить лимит') . ' →';
            $html .= '</a>';
        }

        $html .= '</div>';
        $html .= '</div>';

        return $html;
    }

    /**
     * Инлайн отображение (для заголовков)
     */
    public static function inline(string $type, array $options = []): string
    {
        $showPercent = $options['showPercent'] ?? false;
        $htmlOptions = $options['options'] ?? [];

        $usageInfo = FeatureHelper::getUsageInfo();
        if (empty($usageInfo) || !isset($usageInfo[$type])) {
            return '';
        }

        $info = $usageInfo[$type];
        $current = $info['current'] ?? 0;
        $limit = $info['limit'] ?? 0;

        $field = self::TYPE_FIELDS[$type] ?? '';
        $percent = FeatureHelper::usagePercent($field);

        $isUnlimited = $limit === 0;
        $limitDisplay = $isUnlimited ? '∞' : $limit;

        $colorClass = 'text-gray-600';
        if (!$isUnlimited) {
            if ($percent >= 100) {
                $colorClass = 'text-danger-600';
            } elseif ($percent >= 90) {
                $colorClass = 'text-warning-600';
            }
        }

        $class = "inline-flex items-center gap-1 text-sm font-medium {$colorClass}";
        if (isset($htmlOptions['class'])) {
            $class .= ' ' . $htmlOptions['class'];
            unset($htmlOptions['class']);
        }

        $content = $current . '/' . $limitDisplay;

        if ($showPercent && !$isUnlimited) {
            $content .= ' (' . $percent . '%)';
        }

        return Html::tag('span', $content, array_merge(['class' => $class], $htmlOptions));
    }

    /**
     * Мини-бар для таблиц и карточек
     */
    public static function mini(string $type, array $options = []): string
    {
        $usageInfo = FeatureHelper::getUsageInfo();
        if (empty($usageInfo) || !isset($usageInfo[$type])) {
            return '';
        }

        $info = $usageInfo[$type];
        $limit = $info['limit'] ?? 0;

        if ($limit === 0) {
            return ''; // Не показываем для безлимита
        }

        $field = self::TYPE_FIELDS[$type] ?? '';
        $percent = FeatureHelper::usagePercent($field);
        $progressColor = self::getProgressColor($percent);

        $html = '<div class="w-16 bg-gray-200 rounded-full h-1.5" title="' . $percent . '%">';
        $html .= '<div class="' . $progressColor . ' h-1.5 rounded-full" style="width: ' . min(100, $percent) . '%"></div>';
        $html .= '</div>';

        return $html;
    }

    /**
     * Панель со всеми лимитами
     */
    public static function panel(array $options = []): string
    {
        $types = $options['types'] ?? ['pupils', 'groups', 'teachers'];
        $columns = $options['columns'] ?? 3;
        $htmlOptions = $options['options'] ?? [];

        $class = "grid grid-cols-1 md:grid-cols-{$columns} gap-4";
        if (isset($htmlOptions['class'])) {
            $class .= ' ' . $htmlOptions['class'];
            unset($htmlOptions['class']);
        }

        $html = '<div class="' . $class . '">';

        foreach ($types as $type) {
            $html .= self::show($type, ['showUpgrade' => false]);
        }

        $html .= '</div>';

        return $html;
    }

    /**
     * Получить CSS класс цвета прогресс-бара
     */
    private static function getProgressColor(int $percent): string
    {
        if ($percent >= 100) {
            return 'bg-danger-500';
        }
        if ($percent >= 90) {
            return 'bg-warning-500';
        }
        if ($percent >= 70) {
            return 'bg-primary-500';
        }
        return 'bg-success-500';
    }

    /**
     * Статус для кнопки добавления
     */
    public static function addButtonStatus(string $type): array
    {
        $usageInfo = FeatureHelper::getUsageInfo();
        if (empty($usageInfo) || !isset($usageInfo[$type])) {
            return ['canAdd' => false, 'message' => Yii::t('main', 'Нет активной подписки')];
        }

        $info = $usageInfo[$type];
        $canAdd = $info['can_add'] ?? false;
        $remaining = $info['remaining'] ?? null;

        if (!$canAdd) {
            return [
                'canAdd' => false,
                'message' => Yii::t('main', 'Достигнут лимит тарифа'),
            ];
        }

        if ($remaining !== null && $remaining <= 5) {
            return [
                'canAdd' => true,
                'message' => Yii::t('main', 'Осталось: {n}', ['n' => $remaining]),
                'warning' => true,
            ];
        }

        return ['canAdd' => true, 'message' => null];
    }
}
