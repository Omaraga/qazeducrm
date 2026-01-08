<?php

namespace app\widgets\tailwind;

use Yii;
use yii\base\Widget;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * SidebarMenu Widget - современное боковое меню с поддержкой секций
 *
 * Использование:
 * ```php
 * <?= SidebarMenu::widget([
 *     'items' => [
 *         [
 *             'label' => 'Главная',
 *             'section' => 'Меню',
 *             'items' => [
 *                 ['label' => 'Dashboard', 'icon' => 'dashboard', 'url' => ['/crm/default/index']],
 *                 ['label' => 'Ученики', 'icon' => 'users', 'url' => ['/crm/pupil/index']],
 *             ]
 *         ],
 *         [
 *             'label' => 'Настройки',
 *             'section' => 'Настройки',
 *             'collapsible' => true,
 *             'items' => [
 *                 ['label' => 'Предметы', 'icon' => 'book', 'url' => ['/crm/subject/index']],
 *             ]
 *         ],
 *     ]
 * ]) ?>
 * ```
 */
class SidebarMenu extends Widget
{
    /**
     * @var array элементы меню
     */
    public $items = [];

    /**
     * @var string текущий контроллер (определяется автоматически)
     */
    public $currentController;

    /**
     * @var string текущий action (определяется автоматически)
     */
    public $currentAction;

    /**
     * @var array опции контейнера
     */
    public $options = ['class' => 'flex-1 overflow-y-auto py-4 scrollbar-thin'];

    /**
     * @var string CSS класс для активного пункта
     */
    public $activeClass = 'bg-primary-50 text-primary-700 font-medium';

    /**
     * @var string CSS класс для неактивного пункта
     */
    public $inactiveClass = 'text-gray-600 hover:bg-gray-100 hover:text-gray-900';

    /**
     * @var string CSS класс для ссылки
     */
    public $linkClass = 'flex items-center gap-3 px-3 py-2.5 text-sm rounded-lg transition-colors mb-1';

    /**
     * @var string CSS класс для заголовка секции
     */
    public $sectionClass = 'px-3 mb-2 text-xs font-semibold uppercase tracking-wider text-gray-400';

    /**
     * @var string CSS класс для контейнера секции
     */
    public $sectionContainerClass = 'px-3 mb-6';

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();

        if ($this->currentController === null) {
            $this->currentController = Yii::$app->controller->id;
        }

        if ($this->currentAction === null) {
            $this->currentAction = Yii::$app->controller->action->id;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function run()
    {
        $html = Html::beginTag('nav', $this->options);

        foreach ($this->items as $section) {
            $html .= $this->renderSection($section);
        }

        $html .= Html::endTag('nav');

        return $html;
    }

    /**
     * Рендер секции меню
     */
    protected function renderSection(array $section): string
    {
        // Проверка видимости секции
        $visible = $section['visible'] ?? true;
        if (!$visible) {
            return '';
        }

        $items = $section['items'] ?? [];
        $sectionLabel = $section['section'] ?? '';
        $collapsible = $section['collapsible'] ?? false;
        $collapsed = $section['collapsed'] ?? false;
        $icon = $section['icon'] ?? null;

        // Проверяем, есть ли активный элемент в этой секции
        $hasActiveItem = $this->sectionHasActiveItem($items);

        // Если секция сворачиваемая и есть активный элемент - разворачиваем
        if ($hasActiveItem) {
            $collapsed = false;
        }

        $sectionId = 'section-' . md5($sectionLabel);

        $html = '';

        if ($collapsible) {
            // Сворачиваемая секция
            $html .= '<div class="' . $this->sectionContainerClass . '" x-data="{ open: ' . ($collapsed ? 'false' : 'true') . ' }">';

            // Заголовок с кнопкой сворачивания
            $html .= '<button @click="open = !open" class="w-full flex items-center justify-between px-3 mb-2 text-xs font-semibold uppercase tracking-wider text-gray-400 hover:text-gray-600 transition-colors">';
            $html .= '<span>' . Html::encode($sectionLabel) . '</span>';
            $html .= '<svg class="w-4 h-4 transition-transform duration-200" :class="open ? \'rotate-180\' : \'\'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>';
            $html .= '</button>';

            // Элементы секции
            $html .= '<div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">';
            foreach ($items as $item) {
                $html .= $this->renderItem($item);
            }
            $html .= '</div>';

            $html .= '</div>';
        } else {
            // Обычная секция
            $html .= '<div class="' . $this->sectionContainerClass . '">';

            if ($sectionLabel) {
                $html .= '<div class="' . $this->sectionClass . '">' . Html::encode($sectionLabel) . '</div>';
            }

            foreach ($items as $item) {
                $html .= $this->renderItem($item);
            }

            $html .= '</div>';
        }

        return $html;
    }

    /**
     * Рендер элемента меню
     */
    protected function renderItem(array $item): string
    {
        $label = $item['label'] ?? '';
        $icon = $item['icon'] ?? null;
        $url = $item['url'] ?? '#';
        $controller = $item['controller'] ?? null;
        $badge = $item['badge'] ?? null;
        $badgeClass = $item['badgeClass'] ?? 'bg-primary-500 text-white';
        $visible = $item['visible'] ?? true;
        $items = $item['items'] ?? []; // Вложенные элементы
        $isHeader = $item['header'] ?? false; // Заголовок группы

        if (!$visible) {
            return '';
        }

        // Если это заголовок группы - просто текст
        if ($isHeader) {
            return '<div class="px-3 py-1.5 text-xs font-semibold text-gray-400 uppercase tracking-wider">' . Html::encode($label) . '</div>';
        }

        // Определяем активность
        $isActive = $this->isItemActive($item);

        $linkClass = $this->linkClass . ' ' . ($isActive ? $this->activeClass : $this->inactiveClass);

        // Если есть вложенные элементы
        if (!empty($items)) {
            return $this->renderSubmenu($item, $isActive);
        }

        $html = Html::beginTag('a', [
            'href' => Url::to($url),
            'class' => $linkClass,
        ]);

        // Иконка
        if ($icon) {
            $html .= Icon::show($icon);
        }

        // Текст
        $html .= '<span class="flex-1">' . Html::encode($label) . '</span>';

        // Бейдж
        if ($badge !== null) {
            $html .= '<span class="px-2 py-0.5 text-xs rounded-full ' . $badgeClass . '">' . Html::encode($badge) . '</span>';
        }

        $html .= Html::endTag('a');

        return $html;
    }

    /**
     * Рендер подменю (вложенные элементы)
     */
    protected function renderSubmenu(array $item, bool $parentActive): string
    {
        $label = $item['label'] ?? '';
        $icon = $item['icon'] ?? null;
        $items = $item['items'] ?? [];

        // Проверяем, есть ли активный элемент в подменю
        $hasActiveChild = false;
        foreach ($items as $child) {
            if ($this->isItemActive($child)) {
                $hasActiveChild = true;
                break;
            }
        }

        $html = '<div x-data="{ open: ' . ($hasActiveChild ? 'true' : 'false') . ' }">';

        // Кнопка раскрытия
        $buttonClass = $this->linkClass . ' ' . ($hasActiveChild ? 'text-primary-700 font-medium' : $this->inactiveClass);
        $html .= '<button @click="open = !open" type="button" class="w-full ' . $buttonClass . '">';

        if ($icon) {
            $html .= Icon::show($icon);
        }

        $html .= '<span class="flex-1 text-left">' . Html::encode($label) . '</span>';
        $html .= '<svg class="w-4 h-4 transition-transform duration-200" :class="open ? \'rotate-180\' : \'\'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>';
        $html .= '</button>';

        // Вложенные элементы с анимацией
        $html .= '<div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="ml-4 mt-1 border-l border-gray-200 pl-3 overflow-hidden">';
        foreach ($items as $child) {
            $html .= $this->renderItem($child);
        }
        $html .= '</div>';

        $html .= '</div>';

        return $html;
    }

    /**
     * Проверка активности элемента
     */
    protected function isItemActive(array $item): bool
    {
        // По контроллеру (только если нет вложенных элементов)
        if (isset($item['controller']) && empty($item['items'])) {
            // Поддержка массива контроллеров
            if (is_array($item['controller'])) {
                return in_array($this->currentController, $item['controller']);
            }
            return $this->currentController === $item['controller'];
        }

        // По контроллеру для элементов с вложенными items
        if (isset($item['controller']) && !empty($item['items'])) {
            if (is_array($item['controller'])) {
                return in_array($this->currentController, $item['controller']);
            }
            return $this->currentController === $item['controller'];
        }

        // По URL - точное совпадение controller + action + параметры
        if (isset($item['url']) && is_array($item['url'])) {
            $route = $item['url'][0] ?? '';
            // Извлекаем контроллер и action из роута
            if (preg_match('/\/([^\/]+)\/([^\/]+)$/', $route, $matches)) {
                $controllerMatch = $this->currentController === $matches[1];
                $actionMatch = $this->currentAction === $matches[2];

                if (!$controllerMatch || !$actionMatch) {
                    return false;
                }

                // Проверяем параметры URL (например, type=finance-income)
                $urlParams = array_slice($item['url'], 1);
                if (!empty($urlParams)) {
                    $requestParams = Yii::$app->request->queryParams;
                    foreach ($urlParams as $key => $value) {
                        if (!isset($requestParams[$key]) || $requestParams[$key] !== $value) {
                            return false;
                        }
                    }
                }

                return true;
            }
        }

        return false;
    }

    /**
     * Проверка наличия активного элемента в секции
     */
    protected function sectionHasActiveItem(array $items): bool
    {
        foreach ($items as $item) {
            if ($this->isItemActive($item)) {
                return true;
            }
            // Проверяем вложенные элементы
            if (!empty($item['items'])) {
                if ($this->sectionHasActiveItem($item['items'])) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Быстрый хелпер для создания элемента меню
     */
    public static function item(string $label, string $icon, array $url, array $options = []): array
    {
        return array_merge([
            'label' => $label,
            'icon' => $icon,
            'url' => $url,
        ], $options);
    }

    /**
     * Быстрый хелпер для создания секции
     */
    public static function section(string $label, array $items, array $options = []): array
    {
        return array_merge([
            'section' => $label,
            'items' => $items,
        ], $options);
    }
}
