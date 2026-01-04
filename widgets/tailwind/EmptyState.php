<?php

namespace app\widgets\tailwind;

use yii\base\Widget;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * EmptyState Widget - компонент для пустых состояний
 *
 * Использование:
 * ```php
 * // Базовый
 * <?= EmptyState::widget([
 *     'icon' => 'users',
 *     'title' => 'Нет учеников',
 *     'description' => 'Добавьте первого ученика',
 * ]) ?>
 *
 * // С кнопкой действия
 * <?= EmptyState::widget([
 *     'icon' => 'calendar',
 *     'title' => 'Расписание пусто',
 *     'description' => 'Создайте первое занятие',
 *     'actionLabel' => 'Добавить занятие',
 *     'actionUrl' => ['schedule/create'],
 * ]) ?>
 *
 * // Компактный (для таблиц)
 * <?= EmptyState::compact('Данные не найдены', 'search') ?>
 *
 * // Для ячейки таблицы
 * <?= EmptyState::tableRow(7, 'users', 'Ученики не найдены', 'Добавьте первого ученика', ['pupil/create'], 'Добавить') ?>
 * ```
 */
class EmptyState extends Widget
{
    /**
     * @var string иконка
     */
    public $icon = 'folder';

    /**
     * @var string заголовок
     */
    public $title = 'Нет данных';

    /**
     * @var string|null описание
     */
    public $description = null;

    /**
     * @var string|null текст кнопки действия
     */
    public $actionLabel = null;

    /**
     * @var string|array|null URL кнопки действия
     */
    public $actionUrl = null;

    /**
     * @var string иконка кнопки действия
     */
    public $actionIcon = 'plus';

    /**
     * @var string CSS класс кнопки
     */
    public $actionClass = 'btn btn-primary';

    /**
     * @var string размер: sm, md, lg
     */
    public $size = 'md';

    /**
     * @var array дополнительные опции контейнера
     */
    public $options = [];

    /**
     * Размеры
     */
    const SIZES = [
        'sm' => [
            'container' => 'py-6',
            'icon' => 'lg',
            'title' => 'text-sm font-medium',
            'description' => 'text-xs',
            'button' => 'btn-sm',
        ],
        'md' => [
            'container' => 'py-12',
            'icon' => '2xl',
            'title' => 'text-lg font-medium',
            'description' => 'text-sm',
            'button' => '',
        ],
        'lg' => [
            'container' => 'py-16',
            'icon' => '2xl',
            'title' => 'text-xl font-semibold',
            'description' => 'text-base',
            'button' => 'btn-lg',
        ],
    ];

    /**
     * {@inheritdoc}
     */
    public function run()
    {
        return $this->renderEmptyState();
    }

    /**
     * Рендер пустого состояния
     */
    protected function renderEmptyState(): string
    {
        $sizes = self::SIZES[$this->size] ?? self::SIZES['md'];

        $containerClass = 'text-center text-gray-500 ' . $sizes['container'];
        if (isset($this->options['class'])) {
            $containerClass .= ' ' . $this->options['class'];
            unset($this->options['class']);
        }

        $html = Html::beginTag('div', array_merge(['class' => $containerClass], $this->options));

        // Icon
        $html .= '<div class="text-gray-400 mx-auto mb-4">';
        $html .= Icon::show($this->icon, $sizes['icon']);
        $html .= '</div>';

        // Title
        $html .= '<p class="' . $sizes['title'] . ' text-gray-900">' . Html::encode($this->title) . '</p>';

        // Description
        if ($this->description) {
            $html .= '<p class="mt-1 ' . $sizes['description'] . ' text-gray-500">' . Html::encode($this->description) . '</p>';
        }

        // Action button
        if ($this->actionLabel && $this->actionUrl) {
            $btnClass = $this->actionClass;
            if (!empty($sizes['button'])) {
                $btnClass .= ' ' . $sizes['button'];
            }

            $html .= '<div class="mt-4">';
            $html .= '<a href="' . Url::to($this->actionUrl) . '" class="' . $btnClass . '">';
            if ($this->actionIcon) {
                $html .= Icon::show($this->actionIcon, 'sm') . ' ';
            }
            $html .= Html::encode($this->actionLabel);
            $html .= '</a>';
            $html .= '</div>';
        }

        $html .= Html::endTag('div');

        return $html;
    }

    /**
     * Компактный вариант (для маленьких областей)
     */
    public static function compact(string $message, string $icon = 'info'): string
    {
        return self::widget([
            'icon' => $icon,
            'title' => $message,
            'size' => 'sm',
        ]);
    }

    /**
     * Для использования внутри таблицы (полная строка)
     */
    public static function tableRow(
        int $colspan,
        string $icon,
        string $title,
        ?string $description = null,
        $actionUrl = null,
        ?string $actionLabel = null
    ): string {
        $content = self::widget([
            'icon' => $icon,
            'title' => $title,
            'description' => $description,
            'actionUrl' => $actionUrl,
            'actionLabel' => $actionLabel,
        ]);

        return '<tr><td colspan="' . $colspan . '" class="px-6">' . $content . '</td></tr>';
    }

    /**
     * Для использования внутри карточки
     */
    public static function card(
        string $icon,
        string $title,
        ?string $description = null,
        $actionUrl = null,
        ?string $actionLabel = null
    ): string {
        $html = '<div class="card"><div class="card-body">';
        $html .= self::widget([
            'icon' => $icon,
            'title' => $title,
            'description' => $description,
            'actionUrl' => $actionUrl,
            'actionLabel' => $actionLabel,
        ]);
        $html .= '</div></div>';

        return $html;
    }

    /**
     * Пресеты для частых случаев
     */
    public static function noData(?string $actionUrl = null, ?string $actionLabel = null): string
    {
        return self::widget([
            'icon' => 'folder',
            'title' => 'Нет данных',
            'description' => 'Данные пока не добавлены',
            'actionUrl' => $actionUrl,
            'actionLabel' => $actionLabel,
        ]);
    }

    public static function noResults(): string
    {
        return self::widget([
            'icon' => 'search',
            'title' => 'Ничего не найдено',
            'description' => 'Попробуйте изменить параметры поиска',
        ]);
    }

    public static function noAccess(): string
    {
        return self::widget([
            'icon' => 'x',
            'title' => 'Доступ запрещён',
            'description' => 'У вас нет прав для просмотра этой страницы',
        ]);
    }

    public static function error(?string $message = null): string
    {
        return self::widget([
            'icon' => 'error',
            'title' => 'Произошла ошибка',
            'description' => $message ?? 'Попробуйте обновить страницу',
        ]);
    }
}
