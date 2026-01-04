<?php

namespace app\widgets\tailwind;

use Yii;
use yii\base\Widget;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * Breadcrumbs Widget - хлебные крошки
 *
 * Использование:
 *   <?= Breadcrumbs::widget([
 *       'links' => $this->params['breadcrumbs'] ?? [],
 *   ]) ?>
 */
class Breadcrumbs extends Widget
{
    /**
     * @var array список ссылок
     */
    public $links = [];

    /**
     * @var string метка для главной страницы
     */
    public $homeLabel = 'Главная';

    /**
     * @var string|array URL главной страницы
     */
    public $homeUrl = ['/'];

    /**
     * {@inheritdoc}
     */
    public function run()
    {
        if (empty($this->links)) {
            return '';
        }

        $items = [];

        // Добавляем главную
        $items[] = Html::a(
            Html::encode($this->homeLabel),
            Url::to($this->homeUrl),
            ['class' => 'breadcrumb-item']
        );

        // Добавляем ссылки
        foreach ($this->links as $link) {
            if (is_string($link)) {
                // Последний элемент (активный)
                $items[] = Html::tag('span', Html::encode($link), ['class' => 'breadcrumb-item active']);
            } elseif (is_array($link)) {
                if (isset($link['url'])) {
                    $items[] = Html::a(
                        Html::encode($link['label']),
                        Url::to($link['url']),
                        ['class' => 'breadcrumb-item']
                    );
                } else {
                    $items[] = Html::tag('span', Html::encode($link['label']), ['class' => 'breadcrumb-item active']);
                }
            }
        }

        // Собираем с разделителями
        $separator = '<span class="breadcrumb-separator">/</span>';
        $content = implode($separator, $items);

        return Html::tag('nav', $content, ['class' => 'breadcrumb', 'aria-label' => 'Breadcrumb']);
    }
}
