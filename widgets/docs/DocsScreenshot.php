<?php

namespace app\widgets\docs;

use yii\base\Widget;
use yii\helpers\Html;

/**
 * DocsScreenshot - скриншот с подписью и lightbox для документации
 *
 * Использование:
 * <?= DocsScreenshot::widget([
 *     'src' => '/images/docs/pupils-list.png',
 *     'caption' => 'Список учеников',
 *     'alt' => 'Скриншот списка учеников'
 * ]) ?>
 */
class DocsScreenshot extends Widget
{
    /** @var string Путь к изображению */
    public $src;

    /** @var string Alt текст */
    public $alt = '';

    /** @var string Подпись под изображением */
    public $caption = '';

    /** @var bool Включить lightbox */
    public $lightbox = true;

    /** @var bool Показывать рамку */
    public $border = true;

    /** @var bool Показывать тень */
    public $shadow = true;

    /** @var string Размер: full, large, medium, small */
    public $size = 'full';

    public function run()
    {
        if (empty($this->src)) {
            return '';
        }

        $src = Html::encode($this->src);
        $alt = Html::encode($this->alt ?: $this->caption ?: 'Screenshot');
        $caption = $this->caption ? Html::encode($this->caption) : '';

        $imgClasses = ['rounded-lg', 'max-w-full', 'h-auto'];

        if ($this->border) {
            $imgClasses[] = 'border';
            $imgClasses[] = 'border-gray-200';
        }

        if ($this->shadow) {
            $imgClasses[] = 'shadow-md';
        }

        if ($this->lightbox) {
            $imgClasses[] = 'cursor-zoom-in';
        }

        // Size classes
        $sizeClasses = [
            'full' => 'w-full',
            'large' => 'max-w-3xl',
            'medium' => 'max-w-xl',
            'small' => 'max-w-sm',
        ];

        $containerClass = 'docs-screenshot my-6 ' . ($sizeClasses[$this->size] ?? '');
        $imgClass = implode(' ', $imgClasses);

        $onclick = $this->lightbox ? "onclick=\"openLightbox('{$src}')\"" : '';

        $html = "<figure class=\"{$containerClass}\">";
        $html .= "<img src=\"{$src}\" alt=\"{$alt}\" class=\"{$imgClass}\" loading=\"lazy\" {$onclick}>";

        if ($caption) {
            $html .= "<figcaption class=\"mt-2 text-sm text-gray-500 text-center\">{$caption}</figcaption>";
        }

        $html .= '</figure>';

        return $html;
    }
}
