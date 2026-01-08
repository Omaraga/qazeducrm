<?php

namespace app\widgets\tailwind;

use yii\base\Widget;
use yii\helpers\Html;

/**
 * CollapsibleFilter Widget - сворачиваемая карточка для фильтров
 *
 * Использование:
 * ```php
 * <?php CollapsibleFilter::begin([
 *     'title' => 'Фильтры',
 *     'collapsed' => false,
 *     'badge' => 3, // количество активных фильтров
 * ]) ?>
 *     <form method="get">
 *         <!-- поля фильтра -->
 *     </form>
 * <?php CollapsibleFilter::end() ?>
 *
 * // Или как обёртка формы
 * <?= CollapsibleFilter::widget([
 *     'title' => 'Поиск',
 *     'content' => $this->render('_search', ['model' => $searchModel]),
 * ]) ?>
 * ```
 */
class CollapsibleFilter extends Widget
{
    /**
     * @var string заголовок панели
     */
    public $title = 'Фильтры';

    /**
     * @var bool свёрнута ли панель по умолчанию
     */
    public $collapsed = false;

    /**
     * @var int|null количество активных фильтров (показывается как badge)
     */
    public $badge = null;

    /**
     * @var string|null контент (если не используется begin/end)
     */
    public $content = null;

    /**
     * @var string иконка в заголовке
     */
    public $icon = 'filter';

    /**
     * @var bool сохранять состояние в localStorage
     */
    public $rememberState = true;

    /**
     * @var string ключ для localStorage
     */
    public $storageKey = null;

    /**
     * @var array дополнительные опции контейнера
     */
    public $options = [];

    /**
     * @var string CSS класс карточки
     */
    public $cardClass = 'card';

    /**
     * @var bool компактный режим (меньше отступы и шрифты)
     */
    public $compact = false;

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();

        if ($this->storageKey === null) {
            $this->storageKey = 'filter_' . \Yii::$app->controller->id . '_' . \Yii::$app->controller->action->id;
        }

        if ($this->content === null) {
            ob_start();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function run()
    {
        if ($this->content === null) {
            $this->content = ob_get_clean();
        }

        return $this->renderFilter();
    }

    /**
     * Рендер фильтра
     */
    protected function renderFilter(): string
    {
        $id = $this->getId();
        $collapsedState = $this->collapsed ? 'true' : 'false';

        $alpineData = $this->rememberState
            ? "{ open: localStorage.getItem('{$this->storageKey}') !== null ? localStorage.getItem('{$this->storageKey}') === 'true' : {$collapsedState} === false }"
            : "{ open: " . ($this->collapsed ? 'false' : 'true') . " }";

        $alpineWatch = $this->rememberState
            ? "@click=\"open = !open; localStorage.setItem('{$this->storageKey}', open)\""
            : "@click=\"open = !open\"";

        $html = '<div class="' . $this->cardClass . '" x-data="' . $alpineData . '">';

        // Header
        $html .= '<div class="card-header cursor-pointer select-none" ' . $alpineWatch . '>';
        $html .= '<div class="flex items-center justify-between">';

        // Left side: icon + title + badge
        $html .= '<div class="flex items-center gap-2">';
        $iconSize = $this->compact ? 'xs' : 'sm';
        $titleClass = $this->compact ? 'text-sm font-medium text-gray-900' : 'text-lg font-semibold text-gray-900';
        $html .= '<span class="text-gray-400">' . Icon::show($this->icon, $iconSize) . '</span>';
        $html .= '<h3 class="' . $titleClass . '">' . Html::encode($this->title) . '</h3>';

        if ($this->badge !== null && $this->badge > 0) {
            $html .= StatusBadge::count($this->badge, ['color' => 'primary']);
        }

        $html .= '</div>';

        // Right side: chevron
        $html .= '<div class="text-gray-400 transition-transform duration-200" :class="open ? \'rotate-180\' : \'\'">';
        $html .= Icon::show('chevron-down', 'sm');
        $html .= '</div>';

        $html .= '</div>';
        $html .= '</div>';

        // Body
        $html .= '<div x-show="open" x-collapse>';
        $bodyClass = $this->compact ? 'px-4 py-2 border-t border-gray-100' : 'card-body border-t border-gray-100';
        $html .= '<div class="' . $bodyClass . '">';
        $html .= $this->content;
        $html .= '</div>';
        $html .= '</div>';

        $html .= '</div>';

        return $html;
    }

    /**
     * Статический метод для быстрого создания фильтра
     */
    public static function wrap(string $content, array $options = []): string
    {
        $options['content'] = $content;
        return self::widget($options);
    }
}
