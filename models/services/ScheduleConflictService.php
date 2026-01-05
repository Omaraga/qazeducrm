<?php

namespace app\models\services;

use app\models\Lesson;
use app\models\Room;
use app\models\Organizations;

/**
 * ScheduleConflictService - сервис для проверки конфликтов в расписании
 *
 * Проверяет наложения:
 * - Преподаватель не может вести два занятия одновременно
 * - Группа не может быть на двух занятиях одновременно
 * - Кабинет не может использоваться для двух занятий одновременно
 */
class ScheduleConflictService
{
    const CONFLICT_TEACHER = 'teacher';
    const CONFLICT_GROUP = 'group';
    const CONFLICT_ROOM = 'room';

    /**
     * Проверить все возможные конфликты для занятия
     *
     * @param int|null $teacherId ID преподавателя
     * @param int|null $groupId ID группы
     * @param int|null $roomId ID кабинета (nullable)
     * @param string $date Дата в формате Y-m-d
     * @param string $startTime Время начала HH:MM
     * @param string $endTime Время окончания HH:MM
     * @param int|null $excludeLessonId ID занятия для исключения (при редактировании)
     * @return array Массив конфликтов
     */
    public static function checkAllConflicts(
        ?int $teacherId,
        ?int $groupId,
        ?int $roomId,
        string $date,
        string $startTime,
        string $endTime,
        ?int $excludeLessonId = null
    ): array {
        $conflicts = [];

        if ($teacherId) {
            $teacherConflict = self::checkTeacherConflict($teacherId, $date, $startTime, $endTime, $excludeLessonId);
            if ($teacherConflict) {
                $conflicts[] = $teacherConflict;
            }
        }

        if ($groupId) {
            $groupConflict = self::checkGroupConflict($groupId, $date, $startTime, $endTime, $excludeLessonId);
            if ($groupConflict) {
                $conflicts[] = $groupConflict;
            }
        }

        if ($roomId) {
            $roomConflict = self::checkRoomConflict($roomId, $date, $startTime, $endTime, $excludeLessonId);
            if ($roomConflict) {
                $conflicts[] = $roomConflict;
            }
        }

        return $conflicts;
    }

    /**
     * Проверить конфликт преподавателя
     *
     * @param int $teacherId
     * @param string $date
     * @param string $startTime
     * @param string $endTime
     * @param int|null $excludeLessonId
     * @return array|null
     */
    public static function checkTeacherConflict(
        int $teacherId,
        string $date,
        string $startTime,
        string $endTime,
        ?int $excludeLessonId = null
    ): ?array {
        $conflict = self::findOverlappingLesson([
            'teacher_id' => $teacherId,
        ], $date, $startTime, $endTime, $excludeLessonId);

        if ($conflict) {
            $teacher = $conflict->teacher;
            return [
                'type' => self::CONFLICT_TEACHER,
                'message' => sprintf(
                    '%s уже ведёт занятие в это время (%s, %s-%s)',
                    $teacher->fio ?? 'Преподаватель',
                    $conflict->group->code ?? 'группа',
                    $conflict->start_time,
                    $conflict->end_time
                ),
                'lesson' => self::formatLessonInfo($conflict),
            ];
        }

        return null;
    }

    /**
     * Проверить конфликт группы
     *
     * @param int $groupId
     * @param string $date
     * @param string $startTime
     * @param string $endTime
     * @param int|null $excludeLessonId
     * @return array|null
     */
    public static function checkGroupConflict(
        int $groupId,
        string $date,
        string $startTime,
        string $endTime,
        ?int $excludeLessonId = null
    ): ?array {
        $conflict = self::findOverlappingLesson([
            'group_id' => $groupId,
        ], $date, $startTime, $endTime, $excludeLessonId);

        if ($conflict) {
            $group = $conflict->group;
            return [
                'type' => self::CONFLICT_GROUP,
                'message' => sprintf(
                    'Группа %s уже имеет занятие в это время (%s, %s-%s)',
                    $group->code ?? 'группа',
                    $conflict->teacher->fio ?? 'преподаватель',
                    $conflict->start_time,
                    $conflict->end_time
                ),
                'lesson' => self::formatLessonInfo($conflict),
            ];
        }

        return null;
    }

    /**
     * Проверить конфликт кабинета
     *
     * @param int $roomId
     * @param string $date
     * @param string $startTime
     * @param string $endTime
     * @param int|null $excludeLessonId
     * @return array|null
     */
    public static function checkRoomConflict(
        int $roomId,
        string $date,
        string $startTime,
        string $endTime,
        ?int $excludeLessonId = null
    ): ?array {
        $conflict = self::findOverlappingLesson([
            'room_id' => $roomId,
        ], $date, $startTime, $endTime, $excludeLessonId);

        if ($conflict) {
            $room = $conflict->room;
            return [
                'type' => self::CONFLICT_ROOM,
                'message' => sprintf(
                    'Кабинет %s уже занят в это время (%s, %s-%s)',
                    $room->name ?? 'кабинет',
                    $conflict->group->code ?? 'группа',
                    $conflict->start_time,
                    $conflict->end_time
                ),
                'lesson' => self::formatLessonInfo($conflict),
            ];
        }

        return null;
    }

    /**
     * Найти пересекающееся занятие
     *
     * @param array $conditions Условия поиска (teacher_id, group_id или room_id)
     * @param string $date
     * @param string $startTime
     * @param string $endTime
     * @param int|null $excludeLessonId
     * @return Lesson|null
     */
    protected static function findOverlappingLesson(
        array $conditions,
        string $date,
        string $startTime,
        string $endTime,
        ?int $excludeLessonId = null
    ): ?Lesson {
        $query = Lesson::find()
            ->andWhere(['organization_id' => Organizations::getCurrentOrganizationId()])
            ->andWhere($conditions)
            ->andWhere(['date' => $date])
            ->andWhere(['is_deleted' => 0])
            // Проверка пересечения временных интервалов:
            // Новое занятие пересекается с существующим, если:
            // start_time < end_time_new AND end_time > start_time_new
            ->andWhere(['<', 'start_time', $endTime])
            ->andWhere(['>', 'end_time', $startTime]);

        if ($excludeLessonId) {
            $query->andWhere(['<>', 'id', $excludeLessonId]);
        }

        return $query->with(['group', 'teacher', 'room'])->one();
    }

    /**
     * Форматировать информацию о занятии для ответа
     *
     * @param Lesson $lesson
     * @return array
     */
    protected static function formatLessonInfo(Lesson $lesson): array
    {
        return [
            'id' => $lesson->id,
            'group' => $lesson->group->code ?? null,
            'teacher' => $lesson->teacher->fio ?? null,
            'room' => $lesson->room->name ?? null,
            'date' => $lesson->date,
            'start_time' => $lesson->start_time,
            'end_time' => $lesson->end_time,
        ];
    }

    /**
     * Проверить конфликты для массива занятий (для типового расписания)
     *
     * @param array $lessons Массив занятий [{teacher_id, group_id, room_id, date, start_time, end_time}, ...]
     * @return array Массив конфликтов
     */
    public static function checkBulkConflicts(array $lessons): array
    {
        $allConflicts = [];

        foreach ($lessons as $index => $lesson) {
            $conflicts = self::checkAllConflicts(
                $lesson['teacher_id'] ?? null,
                $lesson['group_id'] ?? null,
                $lesson['room_id'] ?? null,
                $lesson['date'],
                $lesson['start_time'],
                $lesson['end_time']
            );

            if (!empty($conflicts)) {
                $allConflicts[] = [
                    'index' => $index,
                    'lesson' => $lesson,
                    'conflicts' => $conflicts,
                ];
            }
        }

        return $allConflicts;
    }
}
