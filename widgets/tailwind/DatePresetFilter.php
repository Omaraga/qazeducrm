<?php

namespace app\widgets\tailwind;

use app\components\reports\ReportFilterDTO;
use yii\base\Widget;
use yii\helpers\Html;

/**
 * Виджет быстрых фильтров дат для отчетов
 *
 * Отображает кнопки пресетов (Сегодня, Неделя, Месяц, и т.д.)
 * и форму для произвольного периода
 *
 * Использование:
 * <?= DatePresetFilter::widget([
 *     'filter' => $filter, // ReportFilterDTO
 *     'action' => OrganizationUrl::to(['reports/view', 'type' => $reportId]),
 * ]) ?>
 */
class DatePresetFilter extends Widget
{
    /**
     * @var ReportFilterDTO Текущий фильтр
     */
    public ReportFilterDTO $filter;

    /**
     * @var string URL для формы (action)
     */
    public string $action = '';

    /**
     * @var array Дополнительные скрытые поля формы
     */
    public array $hiddenFields = [];

    /**
     * @var bool Показывать кнопку "Вчера"
     */
    public bool $showYesterday = false;

    /**
     * @var bool Показывать кнопку "Квартал"
     */
    public bool $showQuarter = true;

    /**
     * @var bool Показывать кнопку "Год"
     */
    public bool $showYear = true;

    /**
     * @var bool Компактный режим (меньше отступов)
     */
    public bool $compact = false;

    public function init()
    {
        parent::init();

        if (!isset($this->filter)) {
            $this->filter = ReportFilterDTO::fromRequest();
        }
    }

    public function run()
    {
        $presets = ReportFilterDTO::getPresets();
        $currentPreset = $this->filter->preset;

        // Фильтруем пресеты по настройкам
        if (!$this->showYesterday) {
            unset($presets[ReportFilterDTO::PRESET_YESTERDAY]);
        }
        if (!$this->showQuarter) {
            unset($presets[ReportFilterDTO::PRESET_QUARTER]);
        }
        if (!$this->showYear) {
            unset($presets[ReportFilterDTO::PRESET_YEAR]);
        }

        $html = Html::beginForm($this->action, 'get', [
            'class' => 'date-preset-filter',
            'x-data' => '{ showCustom: ' . ($currentPreset === ReportFilterDTO::PRESET_CUSTOM ? 'true' : 'false') . ' }',
        ]);

        // Скрытые поля
        foreach ($this->hiddenFields as $name => $value) {
            $html .= Html::hiddenInput($name, $value);
        }

        $containerClass = $this->compact
            ? 'flex flex-wrap items-center gap-2'
            : 'flex flex-wrap items-center gap-3';

        $html .= Html::beginTag('div', ['class' => $containerClass]);

        // Кнопки пресетов
        $html .= $this->renderPresetButtons($presets, $currentPreset);

        // Кастомный период
        $html .= $this->renderCustomDateInputs();

        $html .= Html::endTag('div');
        $html .= Html::endForm();

        return $html;
    }

    /**
     * Отрисовать кнопки пресетов
     */
    protected function renderPresetButtons(array $presets, string $currentPreset): string
    {
        $buttonClass = $this->compact
            ? 'px-2.5 py-1.5 text-xs'
            : 'px-3 py-2 text-sm';

        $html = Html::beginTag('div', [
            'class' => 'inline-flex rounded-lg shadow-sm bg-white border border-gray-200 p-1',
        ]);

        foreach ($presets as $presetId => $preset) {
            if ($presetId === ReportFilterDTO::PRESET_CUSTOM) {
                // Кнопка кастомного периода (toggle)
                $isActive = $currentPreset === $presetId;
                $activeClass = $isActive
                    ? 'bg-primary-600 text-white'
                    : 'text-gray-600 hover:bg-gray-100';

                $html .= Html::button(
                    Icon::show('adjustments', 'sm'),
                    [
                        'type' => 'button',
                        '@click' => 'showCustom = !showCustom',
                        'class' => "$buttonClass font-medium rounded-md transition-colors $activeClass",
                        'title' => $preset['label'],
                    ]
                );
            } else {
                // Обычная кнопка пресета
                $isActive = $currentPreset === $presetId;
                $activeClass = $isActive
                    ? 'bg-primary-600 text-white'
                    : 'text-gray-600 hover:bg-gray-100';

                $html .= Html::button(
                    $preset['label'],
                    [
                        'type' => 'submit',
                        'name' => 'preset',
                        'value' => $presetId,
                        'class' => "$buttonClass font-medium rounded-md transition-colors $activeClass",
                    ]
                );
            }
        }

        $html .= Html::endTag('div');

        return $html;
    }

    /**
     * Отрисовать поля кастомного периода
     */
    protected function renderCustomDateInputs(): string
    {
        $inputClass = $this->compact
            ? 'form-input form-input-sm text-sm'
            : 'form-input text-sm';

        $btnClass = $this->compact
            ? 'btn btn-primary btn-sm'
            : 'btn btn-primary';

        $html = Html::beginTag('div', [
            'x-show' => 'showCustom',
            'x-collapse' => '',
            'class' => 'flex items-center gap-2',
        ]);

        $html .= Html::input('date', 'date_from', $this->filter->dateFrom, [
            'class' => $inputClass,
            'x-bind:required' => 'showCustom',
        ]);

        $html .= Html::tag('span', '-', ['class' => 'text-gray-400']);

        $html .= Html::input('date', 'date_to', $this->filter->dateTo, [
            'class' => $inputClass,
            'x-bind:required' => 'showCustom',
        ]);

        $html .= Html::hiddenInput('preset', ReportFilterDTO::PRESET_CUSTOM, [
            'x-bind:disabled' => '!showCustom',
        ]);

        $html .= Html::submitButton(
            Icon::show('check', 'sm') . ' Применить',
            ['class' => $btnClass]
        );

        $html .= Html::endTag('div');

        return $html;
    }

    /**
     * Статический метод для быстрой отрисовки
     */
    public static function show(ReportFilterDTO $filter, string $action = '', array $options = []): string
    {
        return static::widget(array_merge([
            'filter' => $filter,
            'action' => $action,
        ], $options));
    }
}
