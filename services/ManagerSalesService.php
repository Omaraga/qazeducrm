<?php

namespace app\services;

use app\models\OrganizationPayment;
use app\models\User;
use Yii;

/**
 * Сервис для работы с продажами менеджеров.
 *
 * Предоставляет методы для:
 * - Получения статистики продаж менеджера
 * - Расчёта бонусов
 * - Выплаты бонусов
 * - Формирования отчётов
 */
class ManagerSalesService
{
    /**
     * Получить статистику продаж менеджера за период
     */
    public function getManagerStats(int $managerId, string $dateFrom, string $dateTo): array
    {
        $query = OrganizationPayment::find()
            ->where(['manager_id' => $managerId])
            ->andWhere(['status' => OrganizationPayment::STATUS_COMPLETED])
            ->andWhere(['>=', 'processed_at', $dateFrom])
            ->andWhere(['<=', 'processed_at', $dateTo]);

        $totalSales = (float)($query->sum('amount') ?? 0);
        $totalPayments = (int)$query->count();

        $bonusQuery = OrganizationPayment::find()
            ->where(['manager_id' => $managerId])
            ->andWhere(['status' => OrganizationPayment::STATUS_COMPLETED])
            ->andWhere(['>=', 'processed_at', $dateFrom])
            ->andWhere(['<=', 'processed_at', $dateTo]);

        $totalBonus = (float)($bonusQuery->sum('manager_bonus_amount') ?? 0);

        $pendingBonus = (float)(OrganizationPayment::find()
            ->where(['manager_id' => $managerId])
            ->andWhere(['status' => OrganizationPayment::STATUS_COMPLETED])
            ->andWhere(['manager_bonus_status' => OrganizationPayment::BONUS_PENDING])
            ->andWhere(['>=', 'processed_at', $dateFrom])
            ->andWhere(['<=', 'processed_at', $dateTo])
            ->sum('manager_bonus_amount') ?? 0);

        $paidBonus = (float)(OrganizationPayment::find()
            ->where(['manager_id' => $managerId])
            ->andWhere(['status' => OrganizationPayment::STATUS_COMPLETED])
            ->andWhere(['manager_bonus_status' => OrganizationPayment::BONUS_PAID])
            ->andWhere(['>=', 'processed_at', $dateFrom])
            ->andWhere(['<=', 'processed_at', $dateTo])
            ->sum('manager_bonus_amount') ?? 0);

        // Средний чек
        $avgCheck = $totalPayments > 0 ? $totalSales / $totalPayments : 0;

        return [
            'manager_id' => $managerId,
            'period' => ['from' => $dateFrom, 'to' => $dateTo],
            'total_sales' => $totalSales,
            'total_payments' => $totalPayments,
            'avg_check' => round($avgCheck, 2),
            'total_bonus' => $totalBonus,
            'pending_bonus' => $pendingBonus,
            'paid_bonus' => $paidBonus,
        ];
    }

    /**
     * Получить топ менеджеров по продажам за период
     */
    public function getTopManagers(string $dateFrom, string $dateTo, int $limit = 10): array
    {
        return OrganizationPayment::find()
            ->alias('p')
            ->select([
                'p.manager_id',
                'COALESCE(u.fio, u.username) as manager_name',
                'u.email as manager_email',
                'SUM(p.amount) as total_sales',
                'COUNT(*) as payments_count',
                'SUM(p.manager_bonus_amount) as total_bonus',
                'SUM(CASE WHEN p.manager_bonus_status = "pending" THEN p.manager_bonus_amount ELSE 0 END) as pending_bonus',
            ])
            ->innerJoin(['u' => User::tableName()], 'p.manager_id = u.id')
            ->where(['p.status' => OrganizationPayment::STATUS_COMPLETED])
            ->andWhere(['IS NOT', 'p.manager_id', null])
            ->andWhere(['>=', 'p.processed_at', $dateFrom])
            ->andWhere(['<=', 'p.processed_at', $dateTo])
            ->groupBy(['p.manager_id'])
            ->orderBy(['total_sales' => SORT_DESC])
            ->limit($limit)
            ->asArray()
            ->all();
    }

    /**
     * Получить все платежи менеджера за период
     */
    public function getManagerPayments(int $managerId, string $dateFrom, string $dateTo): array
    {
        return OrganizationPayment::find()
            ->with(['organization', 'subscription'])
            ->where(['manager_id' => $managerId])
            ->andWhere(['status' => OrganizationPayment::STATUS_COMPLETED])
            ->andWhere(['>=', 'processed_at', $dateFrom])
            ->andWhere(['<=', 'processed_at', $dateTo])
            ->orderBy(['processed_at' => SORT_DESC])
            ->all();
    }

    /**
     * Выплатить бонус за конкретный платёж
     */
    public function payBonus(int $paymentId): bool
    {
        $payment = OrganizationPayment::findOne($paymentId);
        if (!$payment) {
            return false;
        }

        return $payment->payBonus();
    }

    /**
     * Выплатить все ожидающие бонусы менеджера
     */
    public function payAllPendingBonuses(int $managerId): array
    {
        $payments = OrganizationPayment::find()
            ->where(['manager_id' => $managerId])
            ->andWhere(['status' => OrganizationPayment::STATUS_COMPLETED])
            ->andWhere(['manager_bonus_status' => OrganizationPayment::BONUS_PENDING])
            ->andWhere(['>', 'manager_bonus_amount', 0])
            ->all();

        $success = 0;
        $failed = 0;
        $totalAmount = 0;

        foreach ($payments as $payment) {
            if ($payment->payBonus()) {
                $success++;
                $totalAmount += $payment->manager_bonus_amount;
            } else {
                $failed++;
            }
        }

        return [
            'success' => $success,
            'failed' => $failed,
            'total_amount' => $totalAmount,
        ];
    }

    /**
     * Получить все ожидающие бонусы (для выплаты)
     */
    public function getPendingBonuses(): array
    {
        return OrganizationPayment::find()
            ->alias('p')
            ->select([
                'p.*',
                'COALESCE(u.fio, u.username) as manager_name',
                'o.name as organization_name',
            ])
            ->innerJoin(['u' => User::tableName()], 'p.manager_id = u.id')
            ->innerJoin(['o' => 'organization'], 'p.organization_id = o.id')
            ->where(['p.status' => OrganizationPayment::STATUS_COMPLETED])
            ->andWhere(['p.manager_bonus_status' => OrganizationPayment::BONUS_PENDING])
            ->andWhere(['>', 'p.manager_bonus_amount', 0])
            ->orderBy(['p.processed_at' => SORT_ASC])
            ->asArray()
            ->all();
    }

    /**
     * Получить сумму ожидающих бонусов
     */
    public function getTotalPendingBonuses(): float
    {
        return (float)(OrganizationPayment::findPendingBonuses()->sum('manager_bonus_amount') ?? 0);
    }

    /**
     * Получить сумму ожидающих бонусов по менеджерам
     */
    public function getPendingBonusesByManager(): array
    {
        return OrganizationPayment::find()
            ->alias('p')
            ->select([
                'p.manager_id',
                'COALESCE(u.fio, u.username) as manager_name',
                'SUM(p.manager_bonus_amount) as pending_amount',
                'COUNT(*) as payments_count',
            ])
            ->innerJoin(['u' => User::tableName()], 'p.manager_id = u.id')
            ->where(['p.status' => OrganizationPayment::STATUS_COMPLETED])
            ->andWhere(['p.manager_bonus_status' => OrganizationPayment::BONUS_PENDING])
            ->andWhere(['>', 'p.manager_bonus_amount', 0])
            ->groupBy(['p.manager_id'])
            ->orderBy(['pending_amount' => SORT_DESC])
            ->asArray()
            ->all();
    }

    /**
     * Получить список менеджеров (для dropdown)
     */
    public static function getManagersList(): array
    {
        // Получаем пользователей, которые были назначены менеджерами хотя бы в одном платеже
        // или имеют соответствующую роль
        $managers = User::find()
            ->alias('u')
            ->select(['u.id', 'COALESCE(u.fio, u.username) as name', 'u.email'])
            ->where(['u.status' => User::STATUS_ACTIVE])
            ->andWhere([
                'or',
                ['in', 'u.id', OrganizationPayment::find()->select('manager_id')->distinct()],
                ['u.system_role' => 'superadmin'], // Супер-админы могут быть менеджерами
            ])
            ->orderBy(['u.username' => SORT_ASC])
            ->asArray()
            ->all();

        $result = [];
        foreach ($managers as $manager) {
            $result[$manager['id']] = $manager['name'] . ' (' . $manager['email'] . ')';
        }

        return $result;
    }

    /**
     * Получить статистику по бонусам за месяц
     */
    public function getMonthlyBonusStats(int $year, int $month): array
    {
        $dateFrom = sprintf('%04d-%02d-01 00:00:00', $year, $month);
        $dateTo = date('Y-m-t 23:59:59', strtotime($dateFrom));

        $total = (float)(OrganizationPayment::find()
            ->where(['status' => OrganizationPayment::STATUS_COMPLETED])
            ->andWhere(['>=', 'processed_at', $dateFrom])
            ->andWhere(['<=', 'processed_at', $dateTo])
            ->sum('manager_bonus_amount') ?? 0);

        $pending = (float)(OrganizationPayment::find()
            ->where(['status' => OrganizationPayment::STATUS_COMPLETED])
            ->andWhere(['manager_bonus_status' => OrganizationPayment::BONUS_PENDING])
            ->andWhere(['>=', 'processed_at', $dateFrom])
            ->andWhere(['<=', 'processed_at', $dateTo])
            ->sum('manager_bonus_amount') ?? 0);

        $paid = (float)(OrganizationPayment::find()
            ->where(['status' => OrganizationPayment::STATUS_COMPLETED])
            ->andWhere(['manager_bonus_status' => OrganizationPayment::BONUS_PAID])
            ->andWhere(['>=', 'processed_at', $dateFrom])
            ->andWhere(['<=', 'processed_at', $dateTo])
            ->sum('manager_bonus_amount') ?? 0);

        return [
            'year' => $year,
            'month' => $month,
            'total' => $total,
            'pending' => $pending,
            'paid' => $paid,
        ];
    }

    /**
     * Получить ежемесячную статистику продаж за год
     */
    public function getYearlySalesStats(int $year): array
    {
        $stats = [];

        for ($month = 1; $month <= 12; $month++) {
            $dateFrom = sprintf('%04d-%02d-01 00:00:00', $year, $month);
            $dateTo = date('Y-m-t 23:59:59', strtotime($dateFrom));

            $sales = (float)(OrganizationPayment::find()
                ->where(['status' => OrganizationPayment::STATUS_COMPLETED])
                ->andWhere(['>=', 'processed_at', $dateFrom])
                ->andWhere(['<=', 'processed_at', $dateTo])
                ->sum('amount') ?? 0);

            $bonuses = (float)(OrganizationPayment::find()
                ->where(['status' => OrganizationPayment::STATUS_COMPLETED])
                ->andWhere(['>=', 'processed_at', $dateFrom])
                ->andWhere(['<=', 'processed_at', $dateTo])
                ->sum('manager_bonus_amount') ?? 0);

            $stats[] = [
                'month' => $month,
                'month_name' => Yii::$app->formatter->asDate($dateFrom, 'php:F'),
                'sales' => $sales,
                'bonuses' => $bonuses,
            ];
        }

        return $stats;
    }
}
