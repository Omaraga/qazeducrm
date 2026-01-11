<?php

namespace app\modules\cabinet\controllers;

use app\models\LessonAttendance;
use app\modules\cabinet\Module;
use Yii;
use yii\data\ActiveDataProvider;

/**
 * AttendanceController - посещаемость ученика
 */
class AttendanceController extends CabinetBaseController
{
    /**
     * История посещаемости
     * @param int|null $pupil_id ID ученика
     * @param string|null $month Месяц (Y-m)
     */
    public function actionIndex($pupil_id = null, $month = null)
    {
        $pupils = $this->getPupils() ?? [];

        // Если передан конкретный ученик - проверяем доступ
        $selectedPupil = null;
        if ($pupil_id) {
            $selectedPupil = $this->getPupilById($pupil_id);
        } elseif (count($pupils) === 1) {
            $selectedPupil = $pupils[0];
        }

        $pupilIds = $selectedPupil ? [$selectedPupil->id] : Module::getPupilIds();

        // Определяем период
        $currentMonth = $month ?: date('Y-m');
        $monthStart = $currentMonth . '-01';
        $monthEnd = date('Y-m-t', strtotime($monthStart));

        // DataProvider для посещаемости
        $query = LessonAttendance::findWithDeleted()
            ->alias('la')
            ->innerJoin('lesson l', 'l.id = la.lesson_id')
            ->where(['la.pupil_id' => $pupilIds])
            ->andWhere(['la.is_deleted' => 0])
            ->andWhere(['l.is_deleted' => 0])
            ->andWhere(['>=', 'l.date', $monthStart])
            ->andWhere(['<=', 'l.date', $monthEnd])
            ->with(['lesson.group', 'lesson.teacher', 'pupil'])
            ->orderBy(['l.date' => SORT_DESC, 'l.start_time' => SORT_DESC]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 50,
            ],
        ]);

        // Статистика за месяц
        $stats = $this->calculateStats($pupilIds, $monthStart, $monthEnd);

        // Список доступных месяцев (последние 12)
        $months = [];
        for ($i = 0; $i < 12; $i++) {
            $date = date('Y-m', strtotime("-{$i} months"));
            $months[$date] = Yii::$app->formatter->asDate($date . '-01', 'LLLL yyyy');
        }

        return $this->render('index', [
            'pupils' => $pupils,
            'selectedPupil' => $selectedPupil,
            'dataProvider' => $dataProvider,
            'stats' => $stats,
            'currentMonth' => $currentMonth,
            'months' => $months,
        ]);
    }

    /**
     * Статистика посещаемости
     */
    public function actionStats($pupil_id = null)
    {
        $pupils = $this->getPupils() ?? [];

        // Если передан конкретный ученик - проверяем доступ
        $selectedPupil = null;
        if ($pupil_id) {
            $selectedPupil = $this->getPupilById($pupil_id);
        } elseif (count($pupils) === 1) {
            $selectedPupil = $pupils[0];
        }

        $pupilIds = $selectedPupil ? [$selectedPupil->id] : Module::getPupilIds();

        // Статистика за последние 6 месяцев
        $monthlyStats = [];
        for ($i = 5; $i >= 0; $i--) {
            $monthStart = date('Y-m-01', strtotime("-{$i} months"));
            $monthEnd = date('Y-m-t', strtotime($monthStart));
            $monthName = Yii::$app->formatter->asDate($monthStart, 'LLLL');

            $stats = $this->calculateStats($pupilIds, $monthStart, $monthEnd);
            $monthlyStats[] = [
                'month' => $monthName,
                'monthKey' => date('Y-m', strtotime($monthStart)),
                'stats' => $stats,
            ];
        }

        // Общая статистика за всё время
        $allTimeStats = LessonAttendance::findWithDeleted()
            ->alias('la')
            ->innerJoin('lesson l', 'l.id = la.lesson_id')
            ->where(['la.pupil_id' => $pupilIds])
            ->andWhere(['la.is_deleted' => 0])
            ->andWhere(['l.is_deleted' => 0])
            ->select([
                'total' => 'COUNT(*)',
                'visited' => 'SUM(CASE WHEN la.status = ' . LessonAttendance::STATUS_VISIT . ' THEN 1 ELSE 0 END)',
                'missed_with_pay' => 'SUM(CASE WHEN la.status = ' . LessonAttendance::STATUS_MISS_WITH_PAY . ' THEN 1 ELSE 0 END)',
                'missed_without_pay' => 'SUM(CASE WHEN la.status = ' . LessonAttendance::STATUS_MISS_WITHOUT_PAY . ' THEN 1 ELSE 0 END)',
                'missed_valid' => 'SUM(CASE WHEN la.status = ' . LessonAttendance::STATUS_MISS_VALID_REASON . ' THEN 1 ELSE 0 END)',
            ])
            ->asArray()
            ->one();

        return $this->render('stats', [
            'pupils' => $pupils,
            'selectedPupil' => $selectedPupil,
            'monthlyStats' => $monthlyStats,
            'allTimeStats' => $allTimeStats,
        ]);
    }

    /**
     * Рассчитать статистику за период
     */
    private function calculateStats($pupilIds, $startDate, $endDate)
    {
        return LessonAttendance::findWithDeleted()
            ->alias('la')
            ->innerJoin('lesson l', 'l.id = la.lesson_id')
            ->where(['la.pupil_id' => $pupilIds])
            ->andWhere(['la.is_deleted' => 0])
            ->andWhere(['l.is_deleted' => 0])
            ->andWhere(['>=', 'l.date', $startDate])
            ->andWhere(['<=', 'l.date', $endDate])
            ->select([
                'total' => 'COUNT(*)',
                'visited' => 'SUM(CASE WHEN la.status = ' . LessonAttendance::STATUS_VISIT . ' THEN 1 ELSE 0 END)',
                'missed_with_pay' => 'SUM(CASE WHEN la.status = ' . LessonAttendance::STATUS_MISS_WITH_PAY . ' THEN 1 ELSE 0 END)',
                'missed_without_pay' => 'SUM(CASE WHEN la.status = ' . LessonAttendance::STATUS_MISS_WITHOUT_PAY . ' THEN 1 ELSE 0 END)',
                'missed_valid' => 'SUM(CASE WHEN la.status = ' . LessonAttendance::STATUS_MISS_VALID_REASON . ' THEN 1 ELSE 0 END)',
            ])
            ->asArray()
            ->one();
    }

    /**
     * Экспорт посещаемости (для печати)
     */
    public function actionExport($pupil_id = null, $month = null)
    {
        $pupils = $this->getPupils() ?? [];

        $selectedPupil = null;
        if ($pupil_id) {
            $selectedPupil = $this->getPupilById($pupil_id);
        } elseif (count($pupils) === 1) {
            $selectedPupil = $pupils[0];
        }

        $pupilIds = $selectedPupil ? [$selectedPupil->id] : Module::getPupilIds();

        $currentMonth = $month ?: date('Y-m');
        $monthStart = $currentMonth . '-01';
        $monthEnd = date('Y-m-t', strtotime($monthStart));

        $attendances = LessonAttendance::findWithDeleted()
            ->alias('la')
            ->innerJoin('lesson l', 'l.id = la.lesson_id')
            ->where(['la.pupil_id' => $pupilIds])
            ->andWhere(['la.is_deleted' => 0])
            ->andWhere(['l.is_deleted' => 0])
            ->andWhere(['>=', 'l.date', $monthStart])
            ->andWhere(['<=', 'l.date', $monthEnd])
            ->with(['lesson.group', 'pupil'])
            ->orderBy(['l.date' => SORT_ASC, 'l.start_time' => SORT_ASC])
            ->all();

        return $this->render('export', [
            'attendances' => $attendances,
            'selectedPupil' => $selectedPupil,
            'currentMonth' => $currentMonth,
        ]);
    }
}
