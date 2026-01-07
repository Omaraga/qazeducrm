<?php

namespace app\components\reports;

use Yii;

/**
 * DTO для фильтров отчетов
 *
 * Поддерживает быстрые пресеты дат и произвольные периоды
 */
class ReportFilterDTO
{
    // Пресеты дат
    const PRESET_TODAY = 'today';
    const PRESET_YESTERDAY = 'yesterday';
    const PRESET_WEEK = 'week';
    const PRESET_MONTH = 'month';
    const PRESET_QUARTER = 'quarter';
    const PRESET_YEAR = 'year';
    const PRESET_CUSTOM = 'custom';

    /**
     * @var string|null Дата начала периода (Y-m-d)
     */
    public ?string $dateFrom = null;

    /**
     * @var string|null Дата окончания периода (Y-m-d)
     */
    public ?string $dateTo = null;

    /**
     * @var string Пресет периода
     */
    public string $preset = self::PRESET_MONTH;

    /**
     * @var int|null ID группы для фильтрации
     */
    public ?int $groupId = null;

    /**
     * @var int|null ID учителя для фильтрации
     */
    public ?int $teacherId = null;

    /**
     * @var int|null ID предмета для фильтрации
     */
    public ?int $subjectId = null;

    /**
     * @var int|null ID менеджера для фильтрации (лиды)
     */
    public ?int $managerId = null;

    /**
     * @var string|null Источник лида для фильтрации
     */
    public ?string $source = null;

    /**
     * @var int|null ID метода оплаты для фильтрации
     */
    public ?int $payMethodId = null;

    /**
     * @var array Дополнительные фильтры
     */
    public array $customFilters = [];

    /**
     * Создать DTO из параметров запроса
     */
    public static function fromRequest(array $params = null): self
    {
        if ($params === null) {
            $params = Yii::$app->request->get();
        }

        $dto = new self();

        // Обработка пресета
        $dto->preset = $params['preset'] ?? self::PRESET_MONTH;
        $dto->applyPreset($dto->preset, $params);

        // Фильтры сущностей
        $dto->groupId = isset($params['group_id']) ? (int)$params['group_id'] : null;
        $dto->teacherId = isset($params['teacher_id']) ? (int)$params['teacher_id'] : null;
        $dto->subjectId = isset($params['subject_id']) ? (int)$params['subject_id'] : null;
        $dto->managerId = isset($params['manager_id']) ? (int)$params['manager_id'] : null;
        $dto->source = $params['source'] ?? null;
        $dto->payMethodId = isset($params['pay_method_id']) ? (int)$params['pay_method_id'] : null;

        // Дополнительные фильтры
        $dto->customFilters = $params['filters'] ?? [];

        return $dto;
    }

    /**
     * Применить пресет дат
     */
    protected function applyPreset(string $preset, array $params): void
    {
        switch ($preset) {
            case self::PRESET_TODAY:
                $this->dateFrom = date('Y-m-d');
                $this->dateTo = date('Y-m-d');
                break;

            case self::PRESET_YESTERDAY:
                $this->dateFrom = date('Y-m-d', strtotime('-1 day'));
                $this->dateTo = date('Y-m-d', strtotime('-1 day'));
                break;

            case self::PRESET_WEEK:
                // Неделя с понедельника
                $this->dateFrom = date('Y-m-d', strtotime('monday this week'));
                $this->dateTo = date('Y-m-d', strtotime('sunday this week'));
                break;

            case self::PRESET_MONTH:
                $this->dateFrom = date('Y-m-01');
                $this->dateTo = date('Y-m-t');
                break;

            case self::PRESET_QUARTER:
                $quarter = ceil(date('n') / 3);
                $this->dateFrom = date('Y-m-d', mktime(0, 0, 0, ($quarter - 1) * 3 + 1, 1));
                $this->dateTo = date('Y-m-t', mktime(0, 0, 0, $quarter * 3, 1));
                break;

            case self::PRESET_YEAR:
                $this->dateFrom = date('Y-01-01');
                $this->dateTo = date('Y-12-31');
                break;

            case self::PRESET_CUSTOM:
                $this->dateFrom = $params['date_from'] ?? date('Y-m-01');
                $this->dateTo = $params['date_to'] ?? date('Y-m-t');
                break;

            default:
                // По умолчанию - текущий месяц
                $this->dateFrom = date('Y-m-01');
                $this->dateTo = date('Y-m-t');
        }
    }

    /**
     * Получить список пресетов с названиями
     */
    public static function getPresets(): array
    {
        return [
            self::PRESET_TODAY => [
                'label' => 'Сегодня',
                'icon' => 'clock',
            ],
            self::PRESET_YESTERDAY => [
                'label' => 'Вчера',
                'icon' => 'arrow-left',
            ],
            self::PRESET_WEEK => [
                'label' => 'Неделя',
                'icon' => 'calendar',
            ],
            self::PRESET_MONTH => [
                'label' => 'Месяц',
                'icon' => 'calendar',
            ],
            self::PRESET_QUARTER => [
                'label' => 'Квартал',
                'icon' => 'chart',
            ],
            self::PRESET_YEAR => [
                'label' => 'Год',
                'icon' => 'chart-bar',
            ],
            self::PRESET_CUSTOM => [
                'label' => 'Период',
                'icon' => 'adjustments',
            ],
        ];
    }

    /**
     * Получить человеко-читаемый период
     */
    public function getPeriodLabel(): string
    {
        if ($this->dateFrom === $this->dateTo) {
            return Yii::$app->formatter->asDate($this->dateFrom, 'long');
        }

        return Yii::$app->formatter->asDate($this->dateFrom, 'short') . ' - ' .
            Yii::$app->formatter->asDate($this->dateTo, 'short');
    }

    /**
     * Преобразовать в массив параметров для URL
     */
    public function toArray(): array
    {
        $result = [
            'preset' => $this->preset,
        ];

        if ($this->preset === self::PRESET_CUSTOM) {
            $result['date_from'] = $this->dateFrom;
            $result['date_to'] = $this->dateTo;
        }

        if ($this->groupId) {
            $result['group_id'] = $this->groupId;
        }
        if ($this->teacherId) {
            $result['teacher_id'] = $this->teacherId;
        }
        if ($this->subjectId) {
            $result['subject_id'] = $this->subjectId;
        }
        if ($this->managerId) {
            $result['manager_id'] = $this->managerId;
        }
        if ($this->source) {
            $result['source'] = $this->source;
        }
        if ($this->payMethodId) {
            $result['pay_method_id'] = $this->payMethodId;
        }

        return $result;
    }

    /**
     * Количество дней в периоде
     */
    public function getDaysCount(): int
    {
        $from = new \DateTime($this->dateFrom);
        $to = new \DateTime($this->dateTo);
        return $from->diff($to)->days + 1;
    }
}
