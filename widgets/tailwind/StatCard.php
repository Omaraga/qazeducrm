<?php

namespace app\widgets\tailwind;

use yii\base\Widget;
use yii\helpers\Html;

/**
 * StatCard Widget - карточка статистики для дашборда
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
     * @var string имя иконки (без префикса)
     */
    public $icon;

    /**
     * @var string цвет (primary, success, warning, danger)
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
     * @var array иконки SVG
     */
    protected $icons = [
        'users' => '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>',
        'user-group' => '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>',
        'currency' => '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
        'calendar' => '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>',
        'chart' => '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>',
        'clock' => '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
        'check' => '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
        'warning' => '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>',
    ];

    /**
     * {@inheritdoc}
     */
    public function run()
    {
        $scheme = $this->colorSchemes[$this->color] ?? $this->colorSchemes['primary'];
        $iconSvg = $this->icons[$this->icon] ?? $this->icons['chart'];

        $trendHtml = '';
        if ($this->trend !== null) {
            $trendClass = $this->trendUp ? 'stat-card-trend-up' : 'stat-card-trend-down';
            $trendIcon = $this->trendUp
                ? '<svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 9.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 7.414V15a1 1 0 11-2 0V7.414L6.707 9.707a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>'
                : '<svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M14.707 10.293a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 111.414-1.414L9 12.586V5a1 1 0 012 0v7.586l2.293-2.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>';

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
