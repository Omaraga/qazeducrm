<?php

namespace app\widgets\tailwind;

use app\helpers\StatusHelper;
use yii\base\Widget;
use yii\helpers\Html;

/**
 * StatusBadge Widget - универсальный бейдж для отображения статусов
 *
 * Использование:
 * ```php
 * // По типу и статусу
 * <?= StatusBadge::widget(['type' => 'lids', 'status' => $model->status]) ?>
 *
 * // Статический метод
 * <?= StatusBadge::show('salary', $model->status) ?>
 *
 * // С иконкой
 * <?= StatusBadge::show('lesson', $model->status, ['showIcon' => true]) ?>
 *
 * // Только точка
 * <?= StatusBadge::dot('sms', $model->status) ?>
 *
 * // Произвольный бейдж
 * <?= StatusBadge::custom('Новый', 'success') ?>
 * ```
 */
class StatusBadge extends Widget
{
    /**
     * @var string тип статуса (lids, salary, lesson, etc.)
     */
    public $type;

    /**
     * @var mixed значение статуса
     */
    public $status;

    /**
     * @var bool показывать иконку
     */
    public $showIcon = false;

    /**
     * @var bool показывать только точку
     */
    public $dotOnly = false;

    /**
     * @var string размер бейджа: sm, md, lg
     */
    public $size = 'md';

    /**
     * @var bool pill стиль (полностью скруглённый)
     */
    public $pill = true;

    /**
     * @var array дополнительные HTML атрибуты
     */
    public $options = [];

    /**
     * Размеры бейджей
     */
    const SIZES = [
        'xs' => 'px-1.5 py-0.5 text-[10px]',
        'sm' => 'px-2 py-0.5 text-xs',
        'md' => 'px-2.5 py-1 text-xs',
        'lg' => 'px-3 py-1.5 text-sm',
    ];

    /**
     * {@inheritdoc}
     */
    public function run()
    {
        if ($this->dotOnly) {
            return self::dot($this->type, $this->status);
        }

        return self::show($this->type, $this->status, [
            'showIcon' => $this->showIcon,
            'size' => $this->size,
            'pill' => $this->pill,
            'options' => $this->options,
        ]);
    }

    /**
     * Показать бейдж статуса
     */
    public static function show(string $type, $status, array $options = []): string
    {
        $showIcon = $options['showIcon'] ?? false;
        $size = $options['size'] ?? 'md';
        $pill = $options['pill'] ?? true;
        $htmlOptions = $options['options'] ?? [];

        $label = StatusHelper::getLabel($type, $status);
        $badgeClass = StatusHelper::getBadgeClass($type, $status);
        $icon = $showIcon ? StatusHelper::getIcon($type, $status) : null;

        $sizeClass = self::SIZES[$size] ?? self::SIZES['md'];
        $roundedClass = $pill ? 'rounded-full' : 'rounded-md';

        $class = "inline-flex items-center gap-1 font-medium {$sizeClass} {$roundedClass} {$badgeClass}";

        if (isset($htmlOptions['class'])) {
            $class .= ' ' . $htmlOptions['class'];
            unset($htmlOptions['class']);
        }

        $content = '';

        if ($icon) {
            $content .= Icon::show($icon, 'xs');
        }

        $content .= Html::encode($label);

        return Html::tag('span', $content, array_merge(['class' => $class], $htmlOptions));
    }

    /**
     * Показать только точку статуса
     */
    public static function dot(string $type, $status, array $options = []): string
    {
        $size = $options['size'] ?? 'md';
        $withLabel = $options['withLabel'] ?? false;
        $htmlOptions = $options['options'] ?? [];

        $label = StatusHelper::getLabel($type, $status);
        $dotClass = StatusHelper::getDotClass($type, $status);

        $dotSizes = [
            'xs' => 'w-1.5 h-1.5',
            'sm' => 'w-2 h-2',
            'md' => 'w-2.5 h-2.5',
            'lg' => 'w-3 h-3',
        ];

        $dotSizeClass = $dotSizes[$size] ?? $dotSizes['md'];

        if ($withLabel) {
            $content = Html::tag('span', '', ['class' => "inline-block rounded-full {$dotSizeClass} {$dotClass}"]);
            $content .= ' ' . Html::encode($label);
            return Html::tag('span', $content, array_merge(['class' => 'inline-flex items-center gap-2 text-sm text-gray-700'], $htmlOptions));
        }

        return Html::tag('span', '', array_merge([
            'class' => "inline-block rounded-full {$dotSizeClass} {$dotClass}",
            'title' => $label,
        ], $htmlOptions));
    }

    /**
     * Произвольный бейдж с заданным цветом
     */
    public static function custom(string $label, string $color = 'gray', array $options = []): string
    {
        $size = $options['size'] ?? 'md';
        $pill = $options['pill'] ?? true;
        $icon = $options['icon'] ?? null;
        $htmlOptions = $options['options'] ?? [];

        $badgeClasses = [
            'primary' => 'bg-primary-100 text-primary-800',
            'success' => 'bg-success-100 text-success-800',
            'warning' => 'bg-warning-100 text-warning-800',
            'danger' => 'bg-danger-100 text-danger-800',
            'info' => 'bg-blue-100 text-blue-800',
            'gray' => 'bg-gray-100 text-gray-800',
            'purple' => 'bg-purple-100 text-purple-800',
            'indigo' => 'bg-indigo-100 text-indigo-800',
        ];

        $badgeClass = $badgeClasses[$color] ?? $badgeClasses['gray'];
        $sizeClass = self::SIZES[$size] ?? self::SIZES['md'];
        $roundedClass = $pill ? 'rounded-full' : 'rounded-md';

        $class = "inline-flex items-center gap-1 font-medium {$sizeClass} {$roundedClass} {$badgeClass}";

        if (isset($htmlOptions['class'])) {
            $class .= ' ' . $htmlOptions['class'];
            unset($htmlOptions['class']);
        }

        $content = '';

        if ($icon) {
            $content .= Icon::show($icon, 'xs');
        }

        $content .= Html::encode($label);

        return Html::tag('span', $content, array_merge(['class' => $class], $htmlOptions));
    }

    /**
     * Бейдж для boolean значений
     */
    public static function boolean($value, array $options = []): string
    {
        $trueLabel = $options['trueLabel'] ?? 'Да';
        $falseLabel = $options['falseLabel'] ?? 'Нет';
        $trueColor = $options['trueColor'] ?? 'success';
        $falseColor = $options['falseColor'] ?? 'gray';

        $label = $value ? $trueLabel : $falseLabel;
        $color = $value ? $trueColor : $falseColor;

        return self::custom($label, $color, $options);
    }

    /**
     * Бейдж счётчик
     */
    public static function count(int $count, array $options = []): string
    {
        $color = $options['color'] ?? 'primary';
        $zeroColor = $options['zeroColor'] ?? 'gray';
        $maxDisplay = $options['maxDisplay'] ?? 99;

        $displayCount = $count > $maxDisplay ? "{$maxDisplay}+" : (string)$count;
        $actualColor = $count === 0 ? $zeroColor : $color;

        return self::custom($displayCount, $actualColor, array_merge($options, ['size' => 'xs']));
    }

    /**
     * Группа кнопок-статусов (для форм)
     */
    public static function buttons(string $type, $currentStatus, array $options = []): string
    {
        $name = $options['name'] ?? 'status';
        $statuses = StatusHelper::getStatuses($type);
        $excludeStatuses = $options['exclude'] ?? [];

        $html = '<div class="flex flex-wrap gap-2">';

        foreach ($statuses as $value => $config) {
            if (in_array($value, $excludeStatuses)) {
                continue;
            }

            $isActive = $currentStatus == $value;
            $color = $config['color'];
            $label = $config['label'];

            $activeClasses = [
                'primary' => 'bg-primary-600 text-white ring-2 ring-primary-600 ring-offset-2',
                'success' => 'bg-success-600 text-white ring-2 ring-success-600 ring-offset-2',
                'warning' => 'bg-warning-500 text-white ring-2 ring-warning-500 ring-offset-2',
                'danger' => 'bg-danger-600 text-white ring-2 ring-danger-600 ring-offset-2',
                'info' => 'bg-blue-600 text-white ring-2 ring-blue-600 ring-offset-2',
                'gray' => 'bg-gray-600 text-white ring-2 ring-gray-600 ring-offset-2',
                'purple' => 'bg-purple-600 text-white ring-2 ring-purple-600 ring-offset-2',
                'indigo' => 'bg-indigo-600 text-white ring-2 ring-indigo-600 ring-offset-2',
            ];

            $inactiveClasses = [
                'primary' => 'bg-primary-50 text-primary-700 hover:bg-primary-100',
                'success' => 'bg-success-50 text-success-700 hover:bg-success-100',
                'warning' => 'bg-warning-50 text-warning-700 hover:bg-warning-100',
                'danger' => 'bg-danger-50 text-danger-700 hover:bg-danger-100',
                'info' => 'bg-blue-50 text-blue-700 hover:bg-blue-100',
                'gray' => 'bg-gray-50 text-gray-700 hover:bg-gray-100',
                'purple' => 'bg-purple-50 text-purple-700 hover:bg-purple-100',
                'indigo' => 'bg-indigo-50 text-indigo-700 hover:bg-indigo-100',
            ];

            $buttonClass = $isActive
                ? $activeClasses[$color] ?? $activeClasses['gray']
                : $inactiveClasses[$color] ?? $inactiveClasses['gray'];

            $html .= '<label class="cursor-pointer">';
            $html .= '<input type="radio" name="' . Html::encode($name) . '" value="' . Html::encode($value) . '" class="sr-only peer"' . ($isActive ? ' checked' : '') . '>';
            $html .= '<span class="inline-flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-all ' . $buttonClass . '">';
            $html .= Html::encode($label);
            $html .= '</span>';
            $html .= '</label>';
        }

        $html .= '</div>';

        return $html;
    }
}
