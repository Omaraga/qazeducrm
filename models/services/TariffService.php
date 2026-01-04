<?php

namespace app\models\services;

use app\models\Tariff;

/**
 * TariffService - сервис для расчёта стоимости тарифов
 *
 * Централизует бизнес-логику расчёта цен тарифов
 */
class TariffService
{
    /**
     * Рассчитать стоимость тарифа за период
     *
     * @param int $tariffId ID тарифа
     * @param string|null $dateStart Дата начала (формат d.m.Y)
     * @param string|null $dateEnd Дата окончания (формат d.m.Y)
     * @param int $sale Скидка в процентах (0-100)
     * @return array|null Массив с результатами расчёта или null если тариф не найден
     */
    public static function calculatePricing(int $tariffId, ?string $dateStart = null, ?string $dateEnd = null, int $sale = 0): ?array
    {
        $tariff = Tariff::findOne($tariffId);
        if (!$tariff) {
            return null;
        }

        // Значения по умолчанию
        $dateStart = $dateStart ?: date('d.m.Y');
        $dateEnd = $dateEnd ?: date('d.m.Y', time() + (30 * 24 * 60 * 60));
        $sale = max(0, min(100, $sale)); // Ограничиваем 0-100

        // Расчёт стоимости за период
        $periodPrice = self::calculatePeriodPrice($tariff, $dateStart, $dateEnd);

        // Расчёт скидки
        $salePrice = $sale > 0 ? intval($periodPrice * $sale / 100) : 0;
        $totalPrice = $periodPrice - $salePrice;

        // Формирование информационного текста
        $infoText = self::buildInfoText($tariff, $periodPrice, $sale, $salePrice, $totalPrice);

        // Получение предметов тарифа
        $subjects = [];
        foreach ($tariff->subjectsRelation as $subject) {
            $subjects[] = $subject->subject_id;
        }

        return [
            'id' => $tariff->id,
            'name' => $tariff->name,
            'info_text' => $infoText,
            'price' => $tariff->price,
            'sale' => $sale,
            'period_price' => $periodPrice,
            'sale_price' => $salePrice,
            'total_price' => $totalPrice,
            'type' => $tariff->type,
            'duration' => $tariff->duration,
            'subjects' => $subjects,
        ];
    }

    /**
     * Рассчитать стоимость за период
     *
     * @param Tariff $tariff
     * @param string $dateStart
     * @param string $dateEnd
     * @return int
     */
    protected static function calculatePeriodPrice(Tariff $tariff, string $dateStart, string $dateEnd): int
    {
        // Тип 2 - помесячный тариф, пропорционально дням
        if ($tariff->type == 2) {
            $pricePerDay = $tariff->price / 31;
            $days = (strtotime($dateEnd) - strtotime($dateStart)) / (24 * 60 * 60);
            return intval(($days + 1) * $pricePerDay);
        }

        // Другие типы - фиксированная цена
        return $tariff->price;
    }

    /**
     * Сформировать информационный текст о расчёте
     *
     * @param Tariff $tariff
     * @param int $periodPrice
     * @param int $sale
     * @param int $salePrice
     * @param int $totalPrice
     * @return string HTML текст
     */
    protected static function buildInfoText(Tariff $tariff, int $periodPrice, int $sale, int $salePrice, int $totalPrice): string
    {
        $infoText = "Стоимость по тарифу {$tariff->price}тг. ";

        if ($periodPrice != $tariff->price) {
            $infoText .= "Стоимость за выбранный период {$periodPrice}тг. ";
        }

        if ($sale > 0) {
            $infoText .= "Скидка {$sale}% составляет {$salePrice}тг.";
        }

        $infoText .= "<br><b>Итого к оплате {$totalPrice}тг. </b>";

        return $infoText;
    }

    /**
     * Получить список предметов тарифа
     *
     * @param int $tariffId
     * @return array Массив ID предметов
     */
    public static function getTariffSubjects(int $tariffId): array
    {
        $tariff = Tariff::findOne($tariffId);
        if (!$tariff) {
            return [];
        }

        $subjects = [];
        foreach ($tariff->subjectsRelation as $subject) {
            $subjects[] = $subject->subject_id;
        }
        return $subjects;
    }
}
