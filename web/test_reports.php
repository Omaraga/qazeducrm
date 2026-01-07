<?php
/**
 * Скрипт для тестирования отчетов
 */

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/index.php';

// Отключаем вывод HTML
if (php_sapi_name() !== 'cli') {
    header('Content-Type: text/plain; charset=utf-8');
}

use app\models\Payment;
use app\models\Pupil;
use app\models\Lids;
use app\models\Group;
use app\models\User;
use app\models\LessonAttendance;
use app\models\Lesson;
use app\models\EducationGroup;
use app\components\reports\ReportRegistry;
use app\components\reports\ReportFilterDTO;

echo "=== АНАЛИЗ ДАННЫХ ДЛЯ ОТЧЕТОВ ===\n\n";

$orgId = app\models\Organizations::getCurrentOrganizationId() ?: 1;
echo "Organization ID: $orgId\n\n";

// 1. Платежи
echo "--- ПЛАТЕЖИ ---\n";
$incomeCount = Payment::find()->where(['organization_id' => $orgId, 'type' => Payment::TYPE_PAY])->count();
$expenseCount = Payment::find()->where(['organization_id' => $orgId, 'type' => Payment::TYPE_SPENDING])->count();
$incomeSum = Payment::find()->where(['organization_id' => $orgId, 'type' => Payment::TYPE_PAY])->sum('amount') ?: 0;
$expenseSum = Payment::find()->where(['organization_id' => $orgId, 'type' => Payment::TYPE_SPENDING])->sum('amount') ?: 0;
echo "Доходы: $incomeCount записей, сумма: " . number_format($incomeSum, 0, '.', ' ') . " тг\n";
echo "Расходы: $expenseCount записей, сумма: " . number_format($expenseSum, 0, '.', ' ') . " тг\n\n";

// 2. Должники
echo "--- ДОЛЖНИКИ ---\n";
$debtors = Pupil::find()->where(['organization_id' => $orgId])->andWhere(['<', 'balance', 0])->count();
$totalDebt = Pupil::find()->where(['organization_id' => $orgId])->andWhere(['<', 'balance', 0])->sum('balance') ?: 0;
echo "Должников: $debtors, общий долг: " . number_format(abs($totalDebt), 0, '.', ' ') . " тг\n\n";

// 3. Лиды
echo "--- ЛИДЫ ---\n";
$totalLids = Lids::find()->where(['organization_id' => $orgId])->count();
echo "Всего лидов: $totalLids\n";

$statuses = [
    Lids::STATUS_NEW => 'Новый',
    Lids::STATUS_IN_PROGRESS => 'В работе',
    Lids::STATUS_THINKING => 'Думает',
    Lids::STATUS_CAME_FOR_TRIAL => 'Пришёл на пробный',
    Lids::STATUS_TRIAL_DONE => 'Прошёл пробный',
    Lids::STATUS_PAID => 'Оплатил',
    Lids::STATUS_LOST => 'Потерян',
];
foreach ($statuses as $status => $label) {
    $count = Lids::find()->where(['organization_id' => $orgId, 'status' => $status])->count();
    if ($count > 0) echo "  $label: $count\n";
}
echo "\n";

// 4. Группы
echo "--- ГРУППЫ ---\n";
$groups = Group::find()->where(['organization_id' => $orgId])->count();
echo "Групп: $groups\n\n";

// 5. Посещаемость
echo "--- ПОСЕЩАЕМОСТЬ ---\n";
$lessons = Lesson::find()
    ->alias('l')
    ->innerJoin('`group` g', 'g.id = l.group_id')
    ->where(['g.organization_id' => $orgId])
    ->count();
echo "Уроков: $lessons\n\n";

echo "=== ТЕСТИРОВАНИЕ ОТЧЕТОВ ===\n\n";

$filter = new ReportFilterDTO();
$filter->dateFrom = date('Y-01-01'); // С начала года
$filter->dateTo = date('Y-m-d');

$reportTypes = [
    'finance-income',
    'finance-expenses',
    'finance-debts',
    'leads-funnel',
    'leads-sources',
    'leads-managers',
    'pupils-attendance',
    'teachers-salary',
    'operations-groups',
];

foreach ($reportTypes as $type) {
    echo "--- $type ---\n";
    try {
        $report = ReportRegistry::getReport($type);
        if (!$report) {
            echo "  ОШИБКА: Отчет не найден\n\n";
            continue;
        }

        $data = $report->getData($filter);
        $summary = $report->getSummary($filter);

        echo "  Записей: " . count($data) . "\n";
        echo "  Summary: " . json_encode($summary, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n";
        echo "  Статус: OK\n";
    } catch (Exception $e) {
        echo "  ОШИБКА: " . $e->getMessage() . "\n";
        echo "  Строка: " . $e->getFile() . ":" . $e->getLine() . "\n";
    }
    echo "\n";
}

echo "=== ГОТОВО ===\n";
