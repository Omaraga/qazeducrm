<?php

namespace app\models\services;

use app\models\Lesson;
use app\models\LessonAttendance;
use Yii;

/**
 * Сервис для работы с посещаемостью занятий
 */
class AttendanceService
{
    /**
     * Сохранить посещаемость для занятия
     *
     * @param Lesson $lesson Занятие
     * @param array $statuses Массив статусов [pupil_id => ['status' => int]]
     * @param bool $finishLesson Завершить занятие после сохранения
     * @return bool
     */
    public function saveAttendances(Lesson $lesson, array $statuses, bool $finishLesson = true): bool
    {
        $transaction = Yii::$app->db->beginTransaction();

        try {
            foreach ($statuses as $pupilId => $item) {
                if (!$this->saveAttendance($lesson, (int)$pupilId, $item['status'])) {
                    throw new \Exception("Failed to save attendance for pupil {$pupilId}");
                }
            }

            if ($finishLesson) {
                $lesson->status = Lesson::STATUS_FINISHED;
                if (!$lesson->save()) {
                    throw new \Exception('Failed to finish lesson: ' . json_encode($lesson->errors));
                }
            }

            $transaction->commit();
            return true;

        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::error([
                'message' => 'AttendanceService::saveAttendances failed',
                'lesson_id' => $lesson->id,
                'error' => $e->getMessage(),
            ], 'application');
            return false;
        }
    }

    /**
     * Сохранить одну запись посещаемости
     *
     * @param Lesson $lesson
     * @param int $pupilId
     * @param int $status
     * @return bool
     */
    private function saveAttendance(Lesson $lesson, int $pupilId, int $status): bool
    {
        $attendance = LessonAttendance::find()
            ->where([
                'pupil_id' => $pupilId,
                'lesson_id' => $lesson->id,
            ])
            ->byOrganization($lesson->organization_id)
            ->notDeleted()
            ->one();

        if (!$attendance) {
            $attendance = new LessonAttendance();
            $attendance->lesson_id = $lesson->id;
            $attendance->pupil_id = $pupilId;
            $attendance->teacher_id = $lesson->teacher_id;
        }

        $attendance->status = $status;

        return $attendance->save();
    }

    /**
     * Найти или создать записи посещаемости для всех учеников занятия
     *
     * @param Lesson $lesson
     * @return array [pupil_id => LessonAttendance]
     */
    public function getOrCreateAttendances(Lesson $lesson): array
    {
        $pupils = $lesson->getPupils();
        $attendances = [];

        foreach ($pupils as $pupil) {
            $attendance = LessonAttendance::find()
                ->where([
                    'pupil_id' => $pupil->id,
                    'lesson_id' => $lesson->id,
                ])
                ->byOrganization($lesson->organization_id)
                ->notDeleted()
                ->one();

            if (!$attendance) {
                $attendance = new LessonAttendance();
                $attendance->lesson_id = $lesson->id;
                $attendance->pupil_id = $pupil->id;
                $attendance->teacher_id = $lesson->teacher_id;
                $attendance->save();
            }

            $attendances[$pupil->id] = $attendance;
        }

        return $attendances;
    }
}
