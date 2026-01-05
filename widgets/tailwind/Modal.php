<?php

namespace app\widgets\tailwind;

use yii\base\Widget;
use yii\helpers\Html;

/**
 * Modal Widget для Tailwind CSS с Alpine.js
 *
 * Использование:
 *   <?php Modal::begin([
 *       'id' => 'my-modal',
 *       'title' => 'Заголовок модального окна',
 *       'size' => 'md', // sm, md, lg, xl, full
 *   ]); ?>
 *       <p>Содержимое модального окна</p>
 *   <?php Modal::end(); ?>
 *
 * Для открытия модального окна используйте Alpine.js:
 *   <button @click="$dispatch('open-modal', 'my-modal')">Открыть</button>
 *
 * Или через JavaScript:
 *   document.dispatchEvent(new CustomEvent('open-modal', { detail: 'my-modal' }));
 */
class Modal extends Widget
{
    /**
     * @var string уникальный ID модального окна
     */
    public $id;

    /**
     * @var string заголовок
     */
    public $title = '';

    /**
     * @var string размер (sm, md, lg, xl, full)
     */
    public $size = 'md';

    /**
     * @var bool показывать кнопку закрытия
     */
    public $closeButton = true;

    /**
     * @var string|null содержимое footer
     */
    public $footer;

    /**
     * @var array дополнительные классы для контента
     */
    public $contentOptions = [];

    /**
     * @var array размеры модального окна
     */
    protected $sizes = [
        'sm' => 'max-w-sm',
        'md' => 'max-w-lg',
        'lg' => 'max-w-2xl',
        'xl' => 'max-w-4xl',
        'full' => 'max-w-full mx-4',
    ];

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();

        if ($this->id === null) {
            $this->id = $this->getId();
        }

        ob_start();
    }

    /**
     * {@inheritdoc}
     */
    public function run()
    {
        $content = ob_get_clean();
        return $this->renderModal($content);
    }

    /**
     * Рендерит модальное окно
     */
    protected function renderModal($content)
    {
        $sizeClass = $this->sizes[$this->size] ?? $this->sizes['md'];
        $contentClass = isset($this->contentOptions['class']) ? ' ' . $this->contentOptions['class'] : '';

        $closeButton = '';
        if ($this->closeButton) {
            $closeButton = <<<HTML
<button type="button" @click="open = false" class="text-gray-400 hover:text-gray-500">
    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
    </svg>
</button>
HTML;
        }

        $header = '';
        if ($this->title || $this->closeButton) {
            $titleHtml = $this->title ? '<h3 class="modal-title">' . Html::encode($this->title) . '</h3>' : '<div></div>';
            $header = <<<HTML
<div class="modal-header">
    {$titleHtml}
    {$closeButton}
</div>
HTML;
        }

        $footer = '';
        if ($this->footer !== null) {
            $footer = <<<HTML
<div class="modal-footer">
    {$this->footer}
</div>
HTML;
        }

        return <<<HTML
<div x-data="{ open: false }"
     x-on:open-modal.window="if (\$event.detail === '{$this->id}') open = true"
     x-on:close-modal.window="if (\$event.detail === '{$this->id}') open = false"
     x-on:keydown.escape.window="open = false">

    <!-- Backdrop -->
    <div x-show="open"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-gray-900/50 z-[60]"
         @click="open = false"
         style="display: none;"></div>

    <!-- Modal -->
    <div x-show="open"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="fixed inset-0 z-[70] flex items-center justify-center p-4"
         style="display: none;">
        <div class="modal-content {$sizeClass}{$contentClass}" @click.stop>
            {$header}
            <div class="modal-body">
                {$content}
            </div>
            {$footer}
        </div>
    </div>
</div>
HTML;
    }

    /**
     * Генерирует кнопку для открытия модального окна
     */
    public static function openButton($modalId, $label, $options = [])
    {
        $options['@click'] = "\$dispatch('open-modal', '{$modalId}')";
        $options['type'] = 'button';

        if (!isset($options['class'])) {
            $options['class'] = 'btn btn-primary';
        }

        return Html::button($label, $options);
    }

    /**
     * Генерирует кнопку закрытия для footer
     */
    public static function closeButton($label = 'Закрыть', $options = [])
    {
        $options['@click'] = 'open = false';
        $options['type'] = 'button';

        if (!isset($options['class'])) {
            $options['class'] = 'btn btn-secondary';
        }

        return Html::button($label, $options);
    }

    /**
     * Генерирует footer с кнопками
     */
    public static function footer($buttons)
    {
        return implode("\n", $buttons);
    }
}
