<?php

namespace app\widgets\docs;

use yii\base\Widget;
use yii\helpers\Html;

/**
 * DocsSteps - пошаговые инструкции для документации
 *
 * Использование:
 * <?= DocsSteps::widget([
 *     'steps' => [
 *         ['title' => 'Откройте меню', 'content' => 'Нажмите на кнопку меню...', 'screenshot' => '/images/docs/step1.png'],
 *         ['title' => 'Выберите пункт', 'content' => 'В выпадающем списке...'],
 *     ]
 * ]) ?>
 */
class DocsSteps extends Widget
{
    /** @var array Массив шагов: [['title' => '...', 'content' => '...', 'screenshot' => '...']] */
    public $steps = [];

    /** @var bool Компактный вид без скриншотов */
    public $compact = false;

    public function run()
    {
        if (empty($this->steps)) {
            return '';
        }

        $html = '<div class="docs-steps my-6 space-y-6">';

        foreach ($this->steps as $index => $step) {
            $number = $index + 1;
            $title = Html::encode($step['title'] ?? '');
            $content = $step['content'] ?? '';
            $screenshot = $step['screenshot'] ?? null;

            $html .= '<div class="flex gap-4">';

            // Number circle
            $html .= <<<HTML
<div class="flex-shrink-0">
    <div class="w-8 h-8 bg-orange-100 text-orange-600 rounded-full flex items-center justify-center font-bold text-sm">
        {$number}
    </div>
</div>
HTML;

            // Content
            $html .= '<div class="flex-1 min-w-0">';
            $html .= "<h4 class=\"font-semibold text-gray-900 mb-2\">{$title}</h4>";

            if ($content) {
                $html .= "<div class=\"text-gray-600 mb-3\">{$content}</div>";
            }

            if ($screenshot && !$this->compact) {
                $screenshotHtml = Html::encode($screenshot);
                $html .= <<<HTML
<div class="mt-3">
    <img src="{$screenshotHtml}"
         alt="{$title}"
         class="rounded-lg border border-gray-200 shadow-sm cursor-zoom-in max-w-full"
         loading="lazy">
</div>
HTML;
            }

            $html .= '</div>';
            $html .= '</div>';
        }

        $html .= '</div>';

        return $html;
    }
}
