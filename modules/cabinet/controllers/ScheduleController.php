<?php

namespace app\modules\cabinet\controllers;

use app\models\Group;
use app\models\Lesson;
use app\models\Pupil;
use app\modules\cabinet\Module;
use Yii;
use yii\web\Response;

/**
 * ScheduleController - расписание занятий ученика
 */
class ScheduleController extends CabinetBaseController
{
    /**
     * Расписание ученика
     * @param int|null $pupil_id ID ученика (если несколько детей)
     */
    public function actionIndex($pupil_id = null)
    {
        $pupils = $this->getPupils() ?? [];

        // Если передан конкретный ученик - проверяем доступ
        $selectedPupil = null;
        if ($pupil_id) {
            $selectedPupil = $this->getPupilById($pupil_id);
        } elseif (count($pupils) === 1) {
            $selectedPupil = $pupils[0];
        }

        // Получаем группы ученика(ов)
        $pupilIds = $selectedPupil ? [$selectedPupil->id] : Module::getPupilIds();

        $groups = Group::findWithDeleted()
            ->alias('g')
            ->innerJoin('education_group eg', 'eg.group_id = g.id')
            ->innerJoin('pupil_education pe', 'pe.id = eg.education_id')
            ->where(['pe.pupil_id' => $pupilIds])
            ->andWhere(['g.is_deleted' => 0])
            ->andWhere(['pe.is_deleted' => 0])
            ->distinct()
            ->all();

        return $this->render('index', [
            'pupils' => $pupils,
            'selectedPupil' => $selectedPupil,
            'groups' => $groups,
        ]);
    }

    /**
     * AJAX: Получение событий расписания для календаря
     */
    public function actionEvents()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $start = Yii::$app->request->get('start');
        $end = Yii::$app->request->get('end');
        $pupilId = Yii::$app->request->get('pupil_id');

        // Проверяем доступ к ученику
        $pupilIds = Module::getPupilIds();
        if ($pupilId && !in_array($pupilId, $pupilIds)) {
            return ['error' => 'Access denied'];
        }

        $filterPupilIds = $pupilId ? [$pupilId] : $pupilIds;

        // Получаем занятия
        $lessons = Lesson::findWithDeleted()
            ->alias('l')
            ->innerJoin('`group` g', 'g.id = l.group_id')
            ->innerJoin('education_group eg', 'eg.group_id = g.id')
            ->innerJoin('pupil_education pe', 'pe.id = eg.education_id')
            ->where(['pe.pupil_id' => $filterPupilIds])
            ->andWhere(['l.is_deleted' => 0])
            ->andWhere(['>=', 'l.date', $start])
            ->andWhere(['<=', 'l.date', $end])
            ->with(['group', 'room', 'teacher'])
            ->distinct()
            ->all();

        $events = [];
        foreach ($lessons as $lesson) {
            $group = $lesson->group;
            $room = $lesson->room;
            $teacher = $lesson->teacher;

            // Определяем цвет по статусу
            $color = '#3788d8'; // Обычный
            if ($lesson->status == Lesson::STATUS_CANCELED) {
                $color = '#dc3545'; // Отменён
            } elseif ($lesson->status == Lesson::STATUS_FINISHED) {
                $color = '#28a745'; // Завершён
            }

            $events[] = [
                'id' => $lesson->id,
                'title' => $group ? $group->name : 'Занятие',
                'start' => $lesson->date . 'T' . $lesson->start_time,
                'end' => $lesson->date . 'T' . $lesson->end_time,
                'backgroundColor' => $color,
                'borderColor' => $color,
                'extendedProps' => [
                    'room' => $room ? $room->name : '',
                    'teacher' => $teacher ? $teacher->fio : '',
                    'status' => $lesson->status,
                    'subject' => $group && $group->subject ? $group->subject->name : '',
                ],
            ];
        }

        return $events;
    }

    /**
     * Просмотр деталей занятия
     */
    public function actionLesson($id)
    {
        $pupilIds = Module::getPupilIds();

        // Находим занятие и проверяем что ученик записан на него
        $lesson = Lesson::findWithDeleted()
            ->alias('l')
            ->innerJoin('`group` g', 'g.id = l.group_id')
            ->innerJoin('education_group eg', 'eg.group_id = g.id')
            ->innerJoin('pupil_education pe', 'pe.id = eg.education_id')
            ->where(['l.id' => $id])
            ->andWhere(['pe.pupil_id' => $pupilIds])
            ->andWhere(['l.is_deleted' => 0])
            ->one();

        if (!$lesson) {
            Yii::$app->session->setFlash('error', Yii::t('app', 'Занятие не найдено'));
            return $this->redirect(['index']);
        }

        return $this->render('lesson', [
            'lesson' => $lesson,
        ]);
    }

    /**
     * Расписание на неделю (таблица)
     */
    public function actionWeek($pupil_id = null)
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

        // Получаем даты текущей недели
        $weekStart = date('Y-m-d', strtotime('monday this week'));
        $weekEnd = date('Y-m-d', strtotime('sunday this week'));

        // Получаем занятия на неделю
        $lessons = Lesson::findWithDeleted()
            ->alias('l')
            ->innerJoin('`group` g', 'g.id = l.group_id')
            ->innerJoin('education_group eg', 'eg.group_id = g.id')
            ->innerJoin('pupil_education pe', 'pe.id = eg.education_id')
            ->where(['pe.pupil_id' => $pupilIds])
            ->andWhere(['l.is_deleted' => 0])
            ->andWhere(['>=', 'l.date', $weekStart])
            ->andWhere(['<=', 'l.date', $weekEnd])
            ->with(['group', 'room', 'teacher'])
            ->orderBy(['l.date' => SORT_ASC, 'l.start_time' => SORT_ASC])
            ->all();

        // Группируем по дням недели
        $weekDays = [];
        for ($i = 0; $i < 7; $i++) {
            $date = date('Y-m-d', strtotime($weekStart . " +{$i} days"));
            $weekDays[$date] = [
                'date' => $date,
                'dayName' => Yii::$app->formatter->asDate($date, 'EEEE'),
                'lessons' => [],
            ];
        }

        foreach ($lessons as $lesson) {
            if (isset($weekDays[$lesson->date])) {
                $weekDays[$lesson->date]['lessons'][] = $lesson;
            }
        }

        return $this->render('week', [
            'pupils' => $pupils,
            'selectedPupil' => $selectedPupil,
            'weekDays' => $weekDays,
            'weekStart' => $weekStart,
            'weekEnd' => $weekEnd,
        ]);
    }
}
