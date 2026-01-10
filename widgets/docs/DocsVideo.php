<?php

namespace app\widgets\docs;

use yii\base\Widget;
use yii\helpers\Html;

/**
 * DocsVideo - встраивание видео для документации
 *
 * Использование:
 * <?= DocsVideo::widget([
 *     'src' => 'https://www.youtube.com/embed/VIDEO_ID',
 *     'caption' => 'Видео-инструкция'
 * ]) ?>
 */
class DocsVideo extends Widget
{
    /** @var string URL видео (YouTube embed, Vimeo и т.д.) */
    public $src;

    /** @var string Подпись под видео */
    public $caption = '';

    /** @var string Соотношение сторон: 16:9, 4:3 */
    public $ratio = '16:9';

    public function run()
    {
        if (empty($this->src)) {
            return '';
        }

        $src = Html::encode($this->src);
        $caption = $this->caption ? Html::encode($this->caption) : '';

        // Aspect ratio padding
        $paddingClass = $this->ratio === '4:3' ? 'pb-[75%]' : 'pb-[56.25%]';

        $html = '<figure class="docs-video my-6">';
        $html .= "<div class=\"relative {$paddingClass} rounded-lg overflow-hidden bg-gray-100 shadow-md\">";
        $html .= "<iframe src=\"{$src}\" class=\"absolute inset-0 w-full h-full\" frameborder=\"0\" allow=\"accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture\" allowfullscreen></iframe>";
        $html .= '</div>';

        if ($caption) {
            $html .= "<figcaption class=\"mt-2 text-sm text-gray-500 text-center\">{$caption}</figcaption>";
        }

        $html .= '</figure>';

        return $html;
    }
}
