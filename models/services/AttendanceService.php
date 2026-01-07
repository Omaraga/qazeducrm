<?php

namespace app\models\services;

use app\models\Lesson;
use app\models\LessonAttendance;
use app\models\Organizations;
use Yii;

/**
 * Сервис для работы с посещаемостью занятий
 */
class AttendanceService
{
    /**
     * Сохранить посещаемость для занятия
     * Оптимизировано: загружает все записи одним запросом
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
            // Загружаем все существующие записи посещаемости одним запросом
            $existingAttendances = LessonAttendance::find()
                ->where(['lesson_id' => $lesson->id])
                ->byOrganization($lesson->organization_id)
                ->notDeleted()
                ->indexBy('pupil_id')
                ->all();

            foreach ($statuses as $pupilId => $item) {
                $pupilId = (int)$pupilId;
                $status = $item['status'];

                if (isset($existingAttendances[$pupilId])) {
                    // Обновляем существующую запись
                    $attendance = $existingAttendances[$pupilId];
                    $attendance->status = $status;
                    if (!$attendance->save()) {
                        throw new \Exception("Failed to update attendance for pupil {$pupilId}");
                    }
                } else {
                    // Создаем новую запись
                    $attendance = new LessonAttendance();
                    $attendance->lesson_id = $lesson->id;
                    $attendance->pupil_id = $pupilId;
                    $attendance->teacher_id = $lesson->teacher_id;
                    $attendance->status = $status;
                    if (!$attendance->save()) {
                        throw new \Exception("Failed to create attendance for pupil {$pupilId}");
                    }
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
     * Найти или создать записи посещаемости для всех учеников занятия
     * Оптимизировано: один запрос + batch insert
     *
     * @param Lesson $lesson
     * @return array [pupil_id => LessonAttendance]
     */
    public function getOrCreateAttendances(Lesson $lesson): array
    {
        $pupils = $lesson->getPupils();
        $pupilIds = array_map(function($pupil) {
            return $pupil->id;
        }, $pupils);

        if (empty($pupilIds)) {
            return [];
        }

        // Загружаем все существующие записи одним запросом
        $existingAttendances = LessonAttendance::find()
            ->where(['lesson_id' => $lesson->id])
            ->andWhere(['in', 'pupil_id', $pupilIds])
            ->byOrganization($lesson->organization_id)
            ->notDeleted()
            ->indexBy('pupil_id')
            ->all();

        // Определяем, для каких учеников нужно создать записи
        $toCreate = [];
        foreach ($pupils as $pupil) {
            if (!isset($existingAttendances[$pupil->id])) {
                $toCreate[] = [
                    'lesson_id' => $lesson->id,
                    'pupil_id' => $pupil->id,
                    'teacher_id' => $lesson->teacher_id,
                    'organization_id' => $lesson->organization_id,
                    'status' => LessonAttendance::STATUS_PRESENT,
                    'is_deleted' => 0,
                ];
            }
        }

        // Batch insert для новых записей
        if (!empty($toCreate)) {
            Yii::$app->db->createCommand()
                ->batchInsert(
                    LessonAttendance::tableName(),
                    ['lesson_id', 'pupil_id', 'teacher_id', 'organization_id', 'status', 'is_deleted'],
                    $toCreate
                )
                ->execute();

            // Перезагружаем все записи после insert
            $existingAttendances = LessonAttendance::find()
                ->where(['lesson_id' => $lesson->id])
                ->andWhere(['in', 'pupil_id', $pupilIds])
                ->byOrganization($lesson->organization_id)
                ->notDeleted()
                ->indexBy('pupil_id')
                ->all();
        }

        return $existingAttendances;
    }
}
