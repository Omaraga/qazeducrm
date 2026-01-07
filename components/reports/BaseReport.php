<?php

namespace app\components\reports;

use app\helpers\OrganizationRoles;
use app\helpers\SystemRoles;
use app\models\Organizations;
use Yii;

/**
 * Базовый класс для всех отчетов
 *
 * Предоставляет общую функциональность: кэширование, получение organization_id,
 * базовые реализации методов интерфейса
 */
abstract class BaseReport implements ReportInterface
{
    /**
     * @var int ID текущей организации
     */
    protected int $organizationId;

    /**
     * @var int Время жизни кэша в секундах (по умолчанию 5 минут)
     */
    protected int $cacheTtl = 300;

    /**
     * @var bool Включено ли кэширование
     */
    protected bool $cacheEnabled = true;

    public function __construct()
    {
        $this->organizationId = Organizations::getCurrentOrganizationId() ?? 0;
    }

    /**
     * Получить описание отчета (по умолчанию пустое)
     */
    public function getDescription(): string
    {
        return '';
    }

    /**
     * Поддерживает ли отчет экспорт (по умолчанию да)
     */
    public function supportsExport(): bool
    {
        return true;
    }

    /**
     * Роли по умолчанию - директора
     */
    public function getAllowedRoles(): array
    {
        return [
            OrganizationRoles::GENERAL_DIRECTOR,
            OrganizationRoles::DIRECTOR,
        ];
    }

    /**
     * Доступные фильтры по умолчанию
     */
    public function getAvailableFilters(): array
    {
        return ['date_range'];
    }

    /**
     * Данные для графика по умолчанию - не предоставляются
     */
    public function getChartData(ReportFilterDTO $filter): ?array
    {
        return null;
    }

    /**
     * Конфигурация метрик по умолчанию - пустая
     */
    public function getSummaryConfig(): array
    {
        return [];
    }

    /**
     * Получить данные с кэшированием
     */
    protected function cached(string $key, callable $callback, ?int $ttl = null): mixed
    {
        if (!$this->cacheEnabled) {
            return $callback();
        }

        $cache = Yii::$app->cache;
        $fullKey = $this->getCacheKey($key);
        $ttl = $ttl ?? $this->cacheTtl;

        return $cache->getOrSet($fullKey, $callback, $ttl);
    }

    /**
     * Сформировать ключ кэша
     */
    protected function getCacheKey(string $suffix): string
    {
        return sprintf(
            'report:%s:%d:%s',
            $this->getId(),
            $this->organizationId,
            $suffix
        );
    }

    /**
     * Инвалидировать кэш отчета
     */
    public function invalidateCache(): void
    {
        // Для простоты очищаем весь кэш с тегом отчета
        // В production стоит использовать tagged cache
        Yii::$app->cache->flush();
    }

    /**
     * Применить фильтр по организации к запросу
     * Для SUPER пользователей (org_id = 0) фильтр не применяется
     *
     * @param \yii\db\ActiveQuery $query Запрос
     * @param string $field Название поля (например 'payment.organization_id' или 'organization_id')
     * @return \yii\db\ActiveQuery
     */
    protected function applyOrganizationFilter($query, string $field = 'organization_id')
    {
        if ($this->organizationId > 0) {
            $query->andWhere([$field => $this->organizationId]);
        }
        return $query;
    }

    /**
     * Проверяет, нужно ли фильтровать по организации
     * @return bool true если нужно фильтровать (org_id > 0)
     */
    protected function shouldFilterByOrganization(): bool
    {
        return $this->organizationId > 0;
    }

    /**
     * Форматировать сумму как валюту
     */
    protected function formatCurrency(float $amount): string
    {
        return number_format($amount, 0, '.', ' ') . ' ₸';
    }

    /**
     * Форматировать процент
     */
    protected function formatPercent(float $value, int $decimals = 1): string
    {
        return number_format($value, $decimals) . '%';
    }

    /**
     * Форматировать дату
     */
    protected function formatDate(string $date): string
    {
        return Yii::$app->formatter->asDate($date, 'short');
    }

    /**
     * Проверить доступ пользователя к отчету
     */
    public function checkAccess(): bool
    {
        $user = Yii::$app->user;

        // SUPER пользователи имеют доступ ко всем отчетам
        if ($user->can(SystemRoles::SUPER)) {
            return true;
        }

        foreach ($this->getAllowedRoles() as $role) {
            if ($user->can($role)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Получить хэш фильтра для кэширования
     */
    protected function getFilterHash(ReportFilterDTO $filter): string
    {
        return md5(serialize($filter->toArray()));
    }

    /**
     * Построить линейный график
     *
     * @param array $data Данные [['date' => '2026-01-01', 'value' => 100], ...]
     * @param string $label Название линии
     * @param string $color Цвет (primary, success, danger, etc.)
     */
    protected function buildLineChart(array $data, string $label = 'Значение', string $color = 'primary'): array
    {
        $colors = [
            'primary' => 'rgb(59, 130, 246)',
            'success' => 'rgb(34, 197, 94)',
            'danger' => 'rgb(239, 68, 68)',
            'warning' => 'rgb(234, 179, 8)',
            'gray' => 'rgb(107, 114, 128)',
        ];

        $bgColors = [
            'primary' => 'rgba(59, 130, 246, 0.1)',
            'success' => 'rgba(34, 197, 94, 0.1)',
            'danger' => 'rgba(239, 68, 68, 0.1)',
            'warning' => 'rgba(234, 179, 8, 0.1)',
            'gray' => 'rgba(107, 114, 128, 0.1)',
        ];

        return [
            'type' => 'line',
            'labels' => array_column($data, 'date'),
            'datasets' => [
                [
                    'label' => $label,
                    'data' => array_column($data, 'value'),
                    'borderColor' => $colors[$color] ?? $colors['primary'],
                    'backgroundColor' => $bgColors[$color] ?? $bgColors['primary'],
                    'fill' => true,
                    'tension' => 0.4,
                ],
            ],
        ];
    }

    /**
     * Построить столбчатый график
     *
     * @param array $data Данные [['label' => 'Январь', 'value' => 100], ...]
     * @param string $label Название
     * @param string $color Цвет
     */
    protected function buildBarChart(array $data, string $label = 'Значение', string $color = 'primary'): array
    {
        $colors = [
            'primary' => 'rgb(59, 130, 246)',
            'success' => 'rgb(34, 197, 94)',
            'danger' => 'rgb(239, 68, 68)',
            'warning' => 'rgb(234, 179, 8)',
        ];

        return [
            'type' => 'bar',
            'labels' => array_column($data, 'label'),
            'datasets' => [
                [
                    'label' => $label,
                    'data' => array_column($data, 'value'),
                    'backgroundColor' => $colors[$color] ?? $colors['primary'],
                ],
            ],
        ];
    }

    /**
     * Построить круговую диаграмму
     *
     * @param array $data Данные [['label' => 'Категория', 'value' => 100], ...]
     */
    protected function buildPieChart(array $data): array
    {
        $colors = [
            'rgb(59, 130, 246)',   // primary
            'rgb(34, 197, 94)',    // success
            'rgb(239, 68, 68)',    // danger
            'rgb(234, 179, 8)',    // warning
            'rgb(107, 114, 128)',  // gray
            'rgb(168, 85, 247)',   // purple
            'rgb(20, 184, 166)',   // teal
            'rgb(249, 115, 22)',   // orange
        ];

        return [
            'type' => 'pie',
            'labels' => array_column($data, 'label'),
            'datasets' => [
                [
                    'data' => array_column($data, 'value'),
                    'backgroundColor' => array_slice($colors, 0, count($data)),
                ],
            ],
        ];
    }
}
