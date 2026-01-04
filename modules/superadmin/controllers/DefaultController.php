<?php

namespace app\modules\superadmin\controllers;

use app\models\Organizations;
use app\models\OrganizationSubscription;
use app\models\OrganizationPayment;
use app\models\SaasPlan;
use yii\web\Controller;
use yii\db\Expression;

/**
 * Dashboard контроллер супер-админки.
 */
class DefaultController extends Controller
{
    /**
     * Главная страница Dashboard со статистикой.
     */
    public function actionIndex()
    {
        // Статистика организаций
        $totalOrganizations = Organizations::find()->andWhere(['is_deleted' => 0])->count();
        $headOrganizations = Organizations::find()
            ->andWhere(['is_deleted' => 0])
            ->andWhere(['or', ['parent_id' => null], ['type' => 'head']])
            ->count();
        $branchOrganizations = Organizations::find()
            ->andWhere(['is_deleted' => 0])
            ->andWhere(['is not', 'parent_id', null])
            ->count();

        $organizationsByStatus = [
            'active' => Organizations::find()->andWhere(['status' => 'active', 'is_deleted' => 0])->count(),
            'pending' => Organizations::find()->andWhere(['status' => 'pending', 'is_deleted' => 0])->count(),
            'suspended' => Organizations::find()->andWhere(['status' => 'suspended', 'is_deleted' => 0])->count(),
            'blocked' => Organizations::find()->andWhere(['status' => 'blocked', 'is_deleted' => 0])->count(),
        ];

        // Статистика подписок
        $subscriptionsByStatus = [
            'trial' => OrganizationSubscription::find()->andWhere(['status' => 'trial'])->count(),
            'active' => OrganizationSubscription::find()->andWhere(['status' => 'active'])->count(),
            'expired' => OrganizationSubscription::find()->andWhere(['status' => 'expired'])->count(),
            'suspended' => OrganizationSubscription::find()->andWhere(['status' => 'suspended'])->count(),
        ];

        // Истекающие подписки (в течение 7 дней)
        $expiringSoon = OrganizationSubscription::find()
            ->andWhere(['in', 'status', ['trial', 'active']])
            ->andWhere(['<=', 'expires_at', date('Y-m-d H:i:s', strtotime('+7 days'))])
            ->andWhere(['>=', 'expires_at', date('Y-m-d H:i:s')])
            ->count();

        // Статистика платежей
        $pendingPayments = OrganizationPayment::find()
            ->andWhere(['status' => 'pending'])
            ->count();

        $pendingPaymentsAmount = OrganizationPayment::find()
            ->andWhere(['status' => 'pending'])
            ->sum('amount') ?? 0;

        $thisMonthRevenue = OrganizationPayment::find()
            ->andWhere(['status' => 'completed'])
            ->andWhere(['>=', 'processed_at', date('Y-m-01')])
            ->sum('amount') ?? 0;

        $lastMonthRevenue = OrganizationPayment::find()
            ->andWhere(['status' => 'completed'])
            ->andWhere(['>=', 'processed_at', date('Y-m-01', strtotime('-1 month'))])
            ->andWhere(['<', 'processed_at', date('Y-m-01')])
            ->sum('amount') ?? 0;

        // Последние регистрации
        $recentOrganizations = Organizations::find()
            ->andWhere(['is_deleted' => 0])
            ->andWhere(['or', ['parent_id' => null], ['type' => 'head']])
            ->orderBy(['created_at' => SORT_DESC])
            ->limit(5)
            ->all();

        // Ожидающие платежи
        $recentPendingPayments = OrganizationPayment::find()
            ->with(['organization'])
            ->andWhere(['status' => 'pending'])
            ->orderBy(['created_at' => SORT_DESC])
            ->limit(5)
            ->all();

        // Статистика по тарифам
        $planStats = SaasPlan::find()
            ->select([
                'saas_plan.id',
                'saas_plan.name',
                'saas_plan.code',
                'COUNT(organization_subscription.id) as subscription_count'
            ])
            ->leftJoin(
                'organization_subscription',
                'organization_subscription.saas_plan_id = saas_plan.id AND organization_subscription.status IN ("trial", "active")'
            )
            ->groupBy('saas_plan.id')
            ->orderBy(['saas_plan.sort_order' => SORT_ASC])
            ->asArray()
            ->all();

        return $this->render('index', [
            'totalOrganizations' => $totalOrganizations,
            'headOrganizations' => $headOrganizations,
            'branchOrganizations' => $branchOrganizations,
            'organizationsByStatus' => $organizationsByStatus,
            'subscriptionsByStatus' => $subscriptionsByStatus,
            'expiringSoon' => $expiringSoon,
            'pendingPayments' => $pendingPayments,
            'pendingPaymentsAmount' => $pendingPaymentsAmount,
            'thisMonthRevenue' => $thisMonthRevenue,
            'lastMonthRevenue' => $lastMonthRevenue,
            'recentOrganizations' => $recentOrganizations,
            'recentPendingPayments' => $recentPendingPayments,
            'planStats' => $planStats,
        ]);
    }
}
