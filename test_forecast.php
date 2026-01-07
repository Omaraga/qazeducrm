<?php
/**
 * Тест сервиса прогнозирования выручки
 */

defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'dev');

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/vendor/yiisoft/yii2/Yii.php';

$config = require __DIR__ . '/config/console.php';
$application = new yii\console\Application($config);

use app\services\RevenueForecastService;
use app\services\RevenueReportService;
use app\models\OrganizationSubscription;
use app\models\SaasRevenueMonthly;
use app\models\SaasPlan;

echo "=== ТЕСТ ПРОГНОЗИРОВАНИЯ ВЫРУЧКИ ===\n\n";

// Проверяем данные в БД
echo "1. ПРОВЕРКА ДАННЫХ В БД\n";
echo str_repeat("-", 50) . "\n";

$activeSubscriptions = OrganizationSubscription::find()
    ->where(['status' => OrganizationSubscription::STATUS_ACTIVE])
    ->count();
echo "Активных подписок: {$activeSubscriptions}\n";

$trialSubscriptions = OrganizationSubscription::find()
    ->where(['status' => OrganizationSubscription::STATUS_TRIAL])
    ->count();
echo "Триальных подписок: {$trialSubscriptions}\n";

$monthlyDataCount = SaasRevenueMonthly::find()->count();
echo "Записей в saas_revenue_monthly: {$monthlyDataCount}\n";

// Проверяем планы
$plans = SaasPlan::find()->where(['is_active' => 1, 'is_deleted' => 0])->all();
echo "\nТарифные планы:\n";
foreach ($plans as $plan) {
    echo "  - {$plan->name}: " . number_format($plan->price_monthly, 0, '', ' ') . " KZT/мес\n";
}

// Проверяем истекающие подписки
$expiringCount = OrganizationSubscription::find()
    ->where(['status' => OrganizationSubscription::STATUS_ACTIVE])
    ->andWhere(['between', 'expires_at', date('Y-m-d'), date('Y-m-d', strtotime('+30 days'))])
    ->count();
echo "\nИстекающих за 30 дней: {$expiringCount}\n";

// Показываем несколько подписок
echo "\nПримеры подписок:\n";
$subs = OrganizationSubscription::find()
    ->with(['organization', 'saasPlan'])
    ->where(['status' => OrganizationSubscription::STATUS_ACTIVE])
    ->limit(5)
    ->all();

foreach ($subs as $sub) {
    $orgName = $sub->organization ? $sub->organization->name : 'N/A';
    $planName = $sub->saasPlan ? $sub->saasPlan->name : 'N/A';
    $price = $sub->saasPlan ? $sub->saasPlan->price_monthly : 0;
    $expires = $sub->expires_at ? date('d.m.Y', strtotime($sub->expires_at)) : 'N/A';
    echo "  - {$orgName} | {$planName} | " . number_format($price, 0, '', ' ') . " KZT | до {$expires}\n";
}

echo "\n";

// Тестируем RevenueReportService
echo "2. ТЕСТ RevenueReportService\n";
echo str_repeat("-", 50) . "\n";

$reportService = new RevenueReportService();

try {
    $currentMRR = $reportService->getCurrentMRR();
    echo "Текущий MRR: " . number_format($currentMRR, 0, '', ' ') . " KZT\n";

    $dashboard = $reportService->getDashboard();
    echo "ARR: " . number_format($dashboard['current_arr'], 0, '', ' ') . " KZT\n";
    echo "Активных подписок: {$dashboard['active_subscriptions']}\n";
    echo "Триальных: {$dashboard['trial_subscriptions']}\n";
    echo "Churn Rate: {$dashboard['churn_rate']}%\n";
    echo "ARPU: " . number_format($dashboard['avg_revenue_per_org'], 0, '', ' ') . " KZT\n";
    echo "Trial Conversion: {$dashboard['trial_conversion_rate']}%\n";
} catch (Exception $e) {
    echo "ОШИБКА: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}

echo "\n";

// Тестируем RevenueForecastService
echo "3. ТЕСТ RevenueForecastService\n";
echo str_repeat("-", 50) . "\n";

$forecastService = new RevenueForecastService();

try {
    // Прогноз MRR
    echo "\n--- Прогноз MRR на 6 месяцев ---\n";
    $mrrForecast = $forecastService->forecastMRR(6);

    echo "Текущий MRR: " . number_format($mrrForecast['current_mrr'], 0, '', ' ') . " KZT\n";
    echo "Предположения:\n";
    echo "  - Рост: {$mrrForecast['assumptions']['growth_rate']}\n";
    echo "  - Отток: {$mrrForecast['assumptions']['churn_rate']}\n";
    echo "\nПрогноз по месяцам:\n";

    foreach ($mrrForecast['forecast'] as $month) {
        $change = $month['net_change'] >= 0 ? '+' : '';
        echo sprintf(
            "  %s: %s KZT (%s%s)\n",
            $month['month_label'],
            number_format($month['mrr'], 0, '', ' '),
            $change,
            number_format($month['net_change'], 0, '', ' ')
        );
    }

    echo "\nИтого:\n";
    echo "  Финальный MRR: " . number_format($mrrForecast['summary']['final_mrr'], 0, '', ' ') . " KZT\n";
    echo "  Рост: " . number_format($mrrForecast['summary']['total_growth'], 0, '', ' ') . " KZT ";
    echo "({$mrrForecast['summary']['total_growth_percent']}%)\n";

} catch (Exception $e) {
    echo "ОШИБКА прогноза MRR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}

try {
    // MRR под риском
    echo "\n--- MRR под риском (30 дней) ---\n";
    $mrrAtRisk = $forecastService->getMRRAtRisk(30);

    echo "MRR под риском: " . number_format($mrrAtRisk['total_mrr_at_risk'], 0, '', ' ') . " KZT\n";
    echo "Подписок истекает: {$mrrAtRisk['subscriptions_count']}\n";
    echo "Уровень риска: {$mrrAtRisk['risk_level']}\n";

    if (!empty($mrrAtRisk['by_week'])) {
        echo "\nПо неделям:\n";
        foreach ($mrrAtRisk['by_week'] as $week) {
            echo "  {$week['label']}: " . number_format($week['mrr'], 0, '', ' ') . " KZT ({$week['count']} подп.)\n";
        }
    }

    if (!empty($mrrAtRisk['subscriptions'])) {
        echo "\nИстекающие подписки (первые 5):\n";
        $count = 0;
        foreach ($mrrAtRisk['subscriptions'] as $sub) {
            if ($count++ >= 5) break;
            echo sprintf(
                "  - %s | %s | %d дн. | %s KZT\n",
                $sub['organization_name'],
                $sub['plan_name'],
                $sub['days_remaining'],
                number_format($sub['mrr'], 0, '', ' ')
            );
        }
    }

} catch (Exception $e) {
    echo "ОШИБКА MRR at Risk: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}

try {
    // Анализ сценариев
    echo "\n--- Анализ сценариев (6 мес) ---\n";
    $scenarios = $forecastService->getScenarioAnalysis(6);

    foreach ($scenarios['scenarios'] as $key => $scenario) {
        echo sprintf(
            "  %s: %s KZT (%s%s%%)\n",
            $scenario['label'],
            number_format($scenario['final_mrr'], 0, '', ' '),
            $scenario['growth_percent'] >= 0 ? '+' : '',
            $scenario['growth_percent']
        );
    }

} catch (Exception $e) {
    echo "ОШИБКА сценариев: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}

try {
    // Индикаторы здоровья
    echo "\n--- Индикаторы здоровья ---\n";
    $health = $forecastService->getHealthIndicators();

    echo "Общий статус: {$health['overall_status']}\n";
    echo "{$health['summary']}\n\n";

    foreach ($health['indicators'] as $ind) {
        echo sprintf(
            "  %s: %s (цель: %s) [%s]\n",
            $ind['name'],
            $ind['value'],
            $ind['target'],
            $ind['status']
        );
    }

} catch (Exception $e) {
    echo "ОШИБКА индикаторов: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}

try {
    // Гарантированная выручка
    echo "\n--- Гарантированная выручка (3 мес) ---\n";
    $guaranteed = $forecastService->getGuaranteedRevenue(3);

    foreach ($guaranteed['forecast'] as $month) {
        echo sprintf(
            "  %s: %s KZT (%d подп.)\n",
            $month['month_label'],
            number_format($month['guaranteed_revenue'], 0, '', ' '),
            $month['subscription_count']
        );
    }
    echo "Итого: " . number_format($guaranteed['total_guaranteed'], 0, '', ' ') . " KZT\n";

} catch (Exception $e) {
    echo "ОШИБКА гарантированной выручки: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}

try {
    // Триал воронка
    echo "\n--- Воронка триалов ---\n";
    $funnel = $forecastService->getTrialFunnelForecast();

    echo "Активных триалов: {$funnel['active_trials']}\n";
    echo "Конверсия: {$funnel['conversion_rate']}%\n";
    echo "Ожидаемых конверсий: {$funnel['expected_conversions']}\n";
    echo "Средний MRR: " . number_format($funnel['avg_mrr'], 0, '', ' ') . " KZT\n";
    echo "Ожидаемый MRR: " . number_format($funnel['expected_mrr'], 0, '', ' ') . " KZT\n";

} catch (Exception $e) {
    echo "ОШИБКА воронки: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}

echo "\n" . str_repeat("=", 50) . "\n";
echo "ТЕСТ ЗАВЕРШЁН\n";
