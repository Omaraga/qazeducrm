<?php

namespace app\widgets\tailwind;

use yii\base\Widget;
use yii\helpers\Html;

/**
 * StatCard Widget - карточка статистики для дашборда
 *
 * Использует централизованный Icon виджет для иконок.
 *
 * Использование:
 *   <?= StatCard::widget([
 *       'label' => 'Ученики',
 *       'value' => 156,
 *       'icon' => 'users',
 *       'color' => 'primary',
 *       'trend' => '+12%',
 *       'trendUp' => true,
 *   ]) ?>
 */
class StatCard extends Widget
{
    /**
     * @var string название метрики
     */
    public $label;

    /**
     * @var string|int значение метрики
     */
    public $value;

    /**
     * @var string имя иконки (из Icon виджета)
     */
    public $icon = 'chart';

    /**
     * @var string цвет (primary, success, warning, danger, gray)
     */
    public $color = 'primary';

    /**
     * @var string|null текст тренда
     */
    public $trend;

    /**
     * @var bool|null направление тренда (true = вверх, false = вниз)
     */
    public $trendUp;

    /**
     * @var string|null URL для ссылки
     */
    public $url;

    /**
     * @var array цветовые схемы
     */
    protected $colorSchemes = [
        'primary' => [
            'bg' => 'bg-primary-100',
            'text' => 'text-primary-600',
        ],
        'success' => [
            'bg' => 'bg-success-100',
            'text' => 'text-success-600',
        ],
        'warning' => [
            'bg' => 'bg-warning-100',
            'text' => 'text-warning-600',
        ],
        'danger' => [
            'bg' => 'bg-danger-100',
            'text' => 'text-danger-600',
        ],
        'gray' => [
            'bg' => 'bg-gray-100',
            'text' => 'text-gray-600',
        ],
    ];

    /**
     * {@inheritdoc}
     */
    public function run()
    {
        $scheme = $this->colorSchemes[$this->color] ?? $this->colorSchemes['primary'];
        $iconSvg = Icon::show($this->icon, 'lg');

        $trendHtml = '';
        if ($this->trend !== null) {
            $trendClass = $this->trendUp ? 'stat-card-trend-up' : 'stat-card-trend-down';
            $trendIconName = $this->trendUp ? 'arrow-up' : 'arrow-down';
            $trendIcon = Icon::show($trendIconName, 'xs');

            $trendHtml = <<<HTML
<div class="stat-card-trend {$trendClass}">
    {$trendIcon}
    <span>{$this->trend}</span>
</div>
HTML;
        }

        $content = <<<HTML
<div class="stat-card">
    <div class="stat-card-icon {$scheme['bg']} {$scheme['text']}">
        {$iconSvg}
    </div>
    <div class="stat-card-value">{$this->value}</div>
    <div class="stat-card-label">{$this->label}</div>
    {$trendHtml}
</div>
HTML;

        if ($this->url) {
            return Html::a($content, $this->url, ['class' => 'block hover:shadow-card-hover transition-shadow']);
        }

        return $content;
    }
}
