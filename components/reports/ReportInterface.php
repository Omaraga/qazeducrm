<?php

namespace app\components\reports;

/**
 * Интерфейс отчета
 *
 * Все отчеты должны реализовывать этот интерфейс для обеспечения
 * единообразного API и возможности использования в универсальном view
 */
interface ReportInterface
{
    /**
     * Уникальный идентификатор отчета
     * Формат: категория-название (например: finance-income, leads-funnel)
     */
    public function getId(): string;

    /**
     * Название отчета для отображения
     */
    public function getTitle(): string;

    /**
     * Описание отчета
     */
    public function getDescription(): string;

    /**
     * Категория отчета (finance, leads, pupils, teachers, operations)
     */
    public function getCategory(): string;

    /**
     * Иконка отчета (из Icon widget)
     */
    public function getIcon(): string;

    /**
     * Получить основные данные отчета
     *
     * @param ReportFilterDTO $filter Фильтры
     * @return array Массив данных для таблицы
     */
    public function getData(ReportFilterDTO $filter): array;

    /**
     * Получить сводную статистику для карточек
     *
     * @param ReportFilterDTO $filter Фильтры
     * @return array Ассоциативный массив метрик ['total' => 100, 'count' => 10, ...]
     */
    public function getSummary(ReportFilterDTO $filter): array;

    /**
     * Получить данные для графика (опционально)
     *
     * @param ReportFilterDTO $filter Фильтры
     * @return array|null Данные в формате Chart.js или null если график не нужен
     */
    public function getChartData(ReportFilterDTO $filter): ?array;

    /**
     * Получить описание колонок таблицы
     *
     * @return array Массив колонок [['field' => 'date', 'label' => 'Дата', 'format' => 'date'], ...]
     */
    public function getColumns(): array;

    /**
     * Получить описание метрик для карточек
     *
     * @return array Массив метрик [['key' => 'total', 'label' => 'Всего', 'icon' => 'chart', 'color' => 'primary'], ...]
     */
    public function getSummaryConfig(): array;

    /**
     * Получить список доступных фильтров
     *
     * @return array Массив фильтров ['date_range', 'group', 'teacher', ...]
     */
    public function getAvailableFilters(): array;

    /**
     * Поддерживает ли отчет экспорт
     */
    public function supportsExport(): bool;

    /**
     * Получить роли, имеющие доступ к отчету
     *
     * @return array Массив ролей из OrganizationRoles
     */
    public function getAllowedRoles(): array;
}
