<?php

namespace app\widgets\tailwind;

use yii\base\Widget;
use yii\data\Pagination;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * LinkPager Widget - пагинация в стиле Tailwind
 *
 * Использование:
 *   <?= LinkPager::widget([
 *       'pagination' => $dataProvider->pagination,
 *   ]) ?>
 */
class LinkPager extends Widget
{
    /**
     * @var Pagination объект пагинации
     */
    public $pagination;

    /**
     * @var int максимум страниц для отображения
     */
    public $maxButtonCount = 7;

    /**
     * @var string текст первой страницы
     */
    public $firstPageLabel = '«';

    /**
     * @var string текст последней страницы
     */
    public $lastPageLabel = '»';

    /**
     * @var string текст предыдущей страницы
     */
    public $prevPageLabel = '‹';

    /**
     * @var string текст следующей страницы
     */
    public $nextPageLabel = '›';

    /**
     * {@inheritdoc}
     */
    public function run()
    {
        if ($this->pagination === null || $this->pagination->pageCount < 2) {
            return '';
        }

        $buttons = [];
        $currentPage = $this->pagination->page;
        $pageCount = $this->pagination->pageCount;

        // Первая страница
        if ($this->firstPageLabel !== false) {
            $buttons[] = $this->renderPageButton($this->firstPageLabel, 0, $currentPage <= 0);
        }

        // Предыдущая страница
        if ($this->prevPageLabel !== false) {
            $buttons[] = $this->renderPageButton($this->prevPageLabel, $currentPage - 1, $currentPage <= 0);
        }

        // Номера страниц
        list($beginPage, $endPage) = $this->getPageRange();
        for ($i = $beginPage; $i <= $endPage; $i++) {
            $buttons[] = $this->renderPageButton($i + 1, $i, false, $i === $currentPage);
        }

        // Следующая страница
        if ($this->nextPageLabel !== false) {
            $buttons[] = $this->renderPageButton($this->nextPageLabel, $currentPage + 1, $currentPage >= $pageCount - 1);
        }

        // Последняя страница
        if ($this->lastPageLabel !== false) {
            $buttons[] = $this->renderPageButton($this->lastPageLabel, $pageCount - 1, $currentPage >= $pageCount - 1);
        }

        return Html::tag('nav', implode('', $buttons), ['class' => 'pagination', 'aria-label' => 'Pagination']);
    }

    /**
     * Рендерит кнопку страницы
     */
    protected function renderPageButton($label, $page, $disabled = false, $active = false)
    {
        $class = 'pagination-link';

        if ($active) {
            $class .= ' active';
        }

        if ($disabled) {
            $class .= ' disabled';
            return Html::tag('span', $label, ['class' => $class]);
        }

        $url = $this->pagination->createUrl($page);
        return Html::a($label, $url, ['class' => $class]);
    }

    /**
     * Определяет диапазон страниц для отображения
     */
    protected function getPageRange()
    {
        $currentPage = $this->pagination->page;
        $pageCount = $this->pagination->pageCount;

        $beginPage = max(0, $currentPage - (int) ($this->maxButtonCount / 2));

        if (($endPage = $beginPage + $this->maxButtonCount - 1) >= $pageCount) {
            $endPage = $pageCount - 1;
            $beginPage = max(0, $endPage - $this->maxButtonCount + 1);
        }

        return [$beginPage, $endPage];
    }
}
