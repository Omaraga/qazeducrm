<?php
/**
 * Скрипт для тестирования отчетов с реальными данными
 */

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/web/index.php';

use app\models\Payment;
use app\models\Pupil;
use app\models\Lids;
use app\models\Group;
use app\models\User;
use app\models\LessonAttendance;
use app\models\Lesson;
use app\models\EducationGroup;
use app\models\PupilEducation;
use app\components\reports\ReportRegistry;
use app\components\reports\ReportFilterDTO;

echo "=== АНАЛИЗ ДАННЫХ ДЛЯ ОТЧЕТОВ ===\n\n";

// Получаем organization_id
$orgId = 1; // Тестовая организация

echo "Organization ID: $orgId\n\n";

// 1. Платежи (для finance-income, finance-expenses)
echo "--- ПЛАТЕЖИ ---\n";
$payments = Payment::find()->where(['organization_id' => $orgId])->all();
$payCount = count($payments);
$incomeCount = Payment::find()->where(['organization_id' => $orgId, 'type' => Payment::TYPE_PAY])->count();
$expenseCount = Payment::find()->where(['organization_id' => $orgId, 'type' => Payment::TYPE_SPENDING])->count();
echo "Всего платежей: $payCount\n";
echo "Доходы (TYPE_PAY): $incomeCount\n";
echo "Расходы (TYPE_SPENDING): $expenseCount\n\n";

// 2. Ученики с долгами (для finance-debts)
echo "--- УЧЕНИКИ С ДОЛГАМИ ---\n";
$debtors = Pupil::find()->where(['organization_id' => $orgId])->andWhere(['<', 'balance', 0])->count();
$totalPupils = Pupil::find()->where(['organization_id' => $orgId])->count();
echo "Всего учеников: $totalPupils\n";
echo "С долгами (balance < 0): $debtors\n\n";

// 3. Лиды (для leads-funnel, leads-sources, leads-managers)
echo "--- ЛИДЫ ---\n";
$lids = Lids::find()->where(['organization_id' => $orgId])->count();
echo "Всего лидов: $lids\n";

// По статусам
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
    echo "  $label: $count\n";
}

// По источникам
echo "\nПо источникам:\n";
$sources = Lids::find()
    ->select(['source', 'COUNT(*) as cnt'])
    ->where(['organization_id' => $orgId])
    ->andWhere(['is not', 'source', null])
    ->groupBy('source')
    ->asArray()
    ->all();
foreach ($sources as $s) {
    $sourceLabel = Lids::getSourceList()[$s['source']] ?? $s['source'];
    echo "  $sourceLabel: {$s['cnt']}\n";
}

// По менеджерам
echo "\nПо менеджерам:\n";
$managers = Lids::find()
    ->select(['manager_id', 'COUNT(*) as cnt'])
    ->where(['organization_id' => $orgId])
    ->andWhere(['is not', 'manager_id', null])
    ->groupBy('manager_id')
    ->asArray()
    ->all();
foreach ($managers as $m) {
    $user = User::findOne($m['manager_id']);
    $name = $user ? $user->name : "ID: {$m['manager_id']}";
    echo "  $name: {$m['cnt']}\n";
}
echo "\n";

// 4. Группы (для operations-groups)
echo "--- ГРУППЫ ---\n";
$groups = Group::find()->where(['organization_id' => $orgId])->count();
echo "Всего групп: $groups\n";

// Ученики в группах
$eduGroups = EducationGroup::find()
    ->alias('eg')
    ->innerJoin('pupil_education pe', 'pe.id = eg.education_id')
    ->innerJoin('pupil p', 'p.id = pe.pupil_id')
    ->where(['p.organization_id' => $orgId])
    ->count();
echo "Записей учеников в группах: $eduGroups\n\n";

// 5. Посещаемость (для pupils-attendance, teachers-salary)
echo "--- ПОСЕЩАЕМОСТЬ ---\n";
$attendance = LessonAttendance::find()
    ->alias('la')
    ->innerJoin('lesson l', 'l.id = la.lesson_id')
    ->innerJoin('`group` g', 'g.id = l.group_id')
    ->where(['g.organization_id' => $orgId])
    ->count();
echo "Записей посещаемости: $attendance\n";

$lessons = Lesson::find()
    ->alias('l')
    ->innerJoin('`group` g', 'g.id = l.group_id')
    ->where(['g.organization_id' => $orgId])
    ->count();
echo "Уроков: $lessons\n\n";

// 6. Учителя
echo "--- УЧИТЕЛЯ ---\n";
$teachers = User::find()
    ->innerJoin('user_organization uo', 'uo.user_id = user.id')
    ->where(['uo.organization_id' => $orgId, 'uo.role' => 'teacher'])
    ->count();
echo "Учителей: $teachers\n\n";

echo "=== ТЕСТИРОВАНИЕ ОТЧЕТОВ ===\n\n";

// Тестируем каждый отчет
$filter = new ReportFilterDTO();
$filter->dateFrom = date('Y-m-01'); // Начало месяца
$filter->dateTo = date('Y-m-d'); // Сегодня

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

        // Получаем данные
        $data = $report->getData($filter);
        $summary = $report->getSummary($filter);

        echo "  Записей данных: " . count($data) . "\n";
        echo "  Summary: " . json_encode($summary, JSON_UNESCAPED_UNICODE) . "\n";

        // Показываем первые 3 записи
        if (!empty($data)) {
            echo "  Первые записи:\n";
            foreach (array_slice($data, 0, 3) as $i => $row) {
                $preview = array_slice($row, 0, 4);
                echo "    " . ($i+1) . ": " . json_encode($preview, JSON_UNESCAPED_UNICODE) . "\n";
            }
        }

        echo "  Статус: OK\n";
    } catch (Exception $e) {
        echo "  ОШИБКА: " . $e->getMessage() . "\n";
        echo "  Файл: " . $e->getFile() . ":" . $e->getLine() . "\n";
    }
    echo "\n";
}

echo "=== ЗАВЕРШЕНО ===\n";
