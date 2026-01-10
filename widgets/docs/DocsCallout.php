<?php

namespace app\widgets\docs;

use yii\base\Widget;
use yii\helpers\Html;

/**
 * DocsCallout - информационные блоки для документации
 *
 * Использование:
 * <?= DocsCallout::widget([
 *     'type' => 'tip',
 *     'title' => 'Совет',
 *     'content' => 'Текст совета...'
 * ]) ?>
 */
class DocsCallout extends Widget
{
    /** @var string Тип: info, tip, warning, danger */
    public $type = 'info';

    /** @var string Заголовок */
    public $title = '';

    /** @var string Содержимое */
    public $content = '';

    /** @var bool Сворачиваемый блок */
    public $collapsible = false;

    /** @var bool Изначально свёрнут */
    public $collapsed = false;

    const TYPES = [
        'info' => [
            'bg' => 'bg-blue-50',
            'border' => 'border-blue-200',
            'icon_bg' => 'bg-blue-100',
            'icon_color' => 'text-blue-600',
            'title_color' => 'text-blue-800',
            'content_color' => 'text-blue-700',
            'icon' => 'circle-info',
            'default_title' => 'Информация',
        ],
        'tip' => [
            'bg' => 'bg-green-50',
            'border' => 'border-green-200',
            'icon_bg' => 'bg-green-100',
            'icon_color' => 'text-green-600',
            'title_color' => 'text-green-800',
            'content_color' => 'text-green-700',
            'icon' => 'lightbulb',
            'default_title' => 'Совет',
        ],
        'warning' => [
            'bg' => 'bg-yellow-50',
            'border' => 'border-yellow-200',
            'icon_bg' => 'bg-yellow-100',
            'icon_color' => 'text-yellow-600',
            'title_color' => 'text-yellow-800',
            'content_color' => 'text-yellow-700',
            'icon' => 'triangle-exclamation',
            'default_title' => 'Внимание',
        ],
        'danger' => [
            'bg' => 'bg-red-50',
            'border' => 'border-red-200',
            'icon_bg' => 'bg-red-100',
            'icon_color' => 'text-red-600',
            'title_color' => 'text-red-800',
            'content_color' => 'text-red-700',
            'icon' => 'circle-exclamation',
            'default_title' => 'Важно',
        ],
    ];

    public function run()
    {
        $config = self::TYPES[$this->type] ?? self::TYPES['info'];
        $title = $this->title ?: $config['default_title'];

        $containerClass = "docs-callout my-6 rounded-lg border {$config['border']} {$config['bg']} overflow-hidden";

        if ($this->collapsible) {
            return $this->renderCollapsible($config, $title);
        }

        return <<<HTML
<div class="{$containerClass}">
    <div class="flex gap-3 p-4">
        <div class="flex-shrink-0">
            <div class="w-8 h-8 {$config['icon_bg']} {$config['icon_color']} rounded-lg flex items-center justify-center">
                <i class="fas fa-{$config['icon']}"></i>
            </div>
        </div>
        <div class="flex-1 min-w-0">
            <div class="font-semibold {$config['title_color']} mb-1">{$title}</div>
            <div class="{$config['content_color']} text-sm">{$this->content}</div>
        </div>
    </div>
</div>
HTML;
    }

    protected function renderCollapsible($config, $title)
    {
        $open = $this->collapsed ? 'false' : 'true';
        $containerClass = "docs-callout my-6 rounded-lg border {$config['border']} {$config['bg']} overflow-hidden";

        return <<<HTML
<div class="{$containerClass}" x-data="{ open: {$open} }">
    <button @click="open = !open" class="w-full flex items-center gap-3 p-4 text-left">
        <div class="flex-shrink-0">
            <div class="w-8 h-8 {$config['icon_bg']} {$config['icon_color']} rounded-lg flex items-center justify-center">
                <i class="fas fa-{$config['icon']}"></i>
            </div>
        </div>
        <div class="flex-1 font-semibold {$config['title_color']}">{$title}</div>
        <i class="fas fa-chevron-down {$config['icon_color']} transition-transform" :class="open && 'rotate-180'"></i>
    </button>
    <div x-show="open" x-collapse>
        <div class="px-4 pb-4 pl-[60px] {$config['content_color']} text-sm">{$this->content}</div>
    </div>
</div>
HTML;
    }
}
