<?php

namespace app\models\services;

use app\helpers\OrganizationUrl;
use app\models\Group;
use app\models\Lesson;
use app\models\Organizations;
use app\models\relations\TeacherGroup;
use app\models\Room;
use app\models\TypicalSchedule;
use app\models\User;
use yii\db\Query;

/**
 * ScheduleService - сервис для работы с расписанием
 *
 * Объединяет общую логику для ScheduleController и TypicalScheduleController
 */
class ScheduleService
{
    /**
     * Получить события расписания для календаря
     *
     * @param string $modelClass Класс модели (Lesson::class или TypicalSchedule::class)
     * @param string $urlPrefix Префикс URL для редактирования ('schedule' или 'typical-schedule')
     * @param bool $includeTimeFields Добавлять ли поля date, start_time, end_time в результат
     * @return array Массив событий для календаря
     */
    public static function getEvents(string $modelClass, string $urlPrefix, bool $includeTimeFields = false): array
    {
        $result = [];

        // Определяем алиас таблицы на основе модели
        $tableAlias = $modelClass === Lesson::class ? 'lesson' : 'typical_schedule';

        $query = new Query();
        $query->select([
            "{$tableAlias}.id",
            "{$tableAlias}.start_time",
            "{$tableAlias}.end_time",
            "{$tableAlias}.date",
            'group.code as code',
            'group.color as color',
            'group.name as name',
            'user.fio as fio',
        ])
            ->from($modelClass::tableName())
            ->innerJoin(Group::tableName(), "{$tableAlias}.group_id = group.id AND group.is_deleted != 1")
            ->innerJoin(User::tableName(), "{$tableAlias}.teacher_id = user.id")
            ->andWhere(["{$tableAlias}.organization_id" => Organizations::getCurrentOrganizationId()])
            ->andWhere("{$tableAlias}.is_deleted != 1")
            ->orderBy("{$tableAlias}.start_time ASC");

        $events = $query->all();

        foreach ($events as $i => $event) {
            $result[$i] = [
                'start' => strtotime($event['date'] . ' ' . $event['start_time']),
                'end' => strtotime($event['date'] . ' ' . $event['end_time']),
                'title' => $event['code'] . '-' . $event['name'],
                'color' => $event['color'],
                'content' => $event['fio'],
                'url' => OrganizationUrl::to(["{$urlPrefix}/update", 'id' => $event['id']]),
            ];

            if ($includeTimeFields) {
                $result[$i]['date'] = $event['date'];
                $result[$i]['start_time'] = $event['start_time'];
                $result[$i]['end_time'] = $event['end_time'];
            }
        }

        // Сортировка по длительности события
        usort($result, function ($a, $b) {
            $aDif = $a['end'] - $a['start'];
            $bDif = $b['end'] - $b['start'];
            return $aDif <=> $bDif;
        });

        return $result;
    }

    /**
     * Получить учителей для группы
     *
     * @param int $groupId ID группы
     * @return array Массив с id и fio учителей
     */
    public static function getTeachersForGroup(int $groupId): array
    {
        $result = [];

        // Проверяем существование группы с учетом организации
        $group = Group::find()
            ->where(['id' => $groupId])
            ->byOrganization()
            ->notDeleted()
            ->one();

        if (!$group) {
            return $result;
        }

        // Используем eager loading для предотвращения N+1 (исправление)
        $teacherGroups = TeacherGroup::find()
            ->with('teacher')
            ->where(['target_id' => $group->id])
            ->byOrganization()
            ->notDeleted()
            ->all();

        foreach ($teacherGroups as $i => $teacherGroup) {
            if ($teacherGroup->teacher) {
                $result[$i] = [
                    'id' => $teacherGroup->related_id,
                    'fio' => $teacherGroup->teacher->fio,
                ];
            }
        }

        return $result;
    }

    /**
     * Получить события уроков
     *
     * @return array
     */
    public static function getLessonEvents(): array
    {
        return self::getEvents(Lesson::class, 'schedule', false);
    }

    /**
     * Получить события типового расписания
     *
     * @return array
     */
    public static function getTypicalScheduleEvents(): array
    {
        return self::getEvents(TypicalSchedule::class, 'typical-schedule', true);
    }

    /**
     * Получить события уроков с фильтрацией
     *
     * @param string $start Начальная дата (Y-m-d)
     * @param string $end Конечная дата (Y-m-d)
     * @param array $groupIds Фильтр по группам (опционально)
     * @param array $teacherIds Фильтр по учителям (опционально)
     * @return array Массив событий для календаря
     */
    public static function getLessonEventsFiltered(
        string $start,
        string $end,
        array $groupIds = [],
        array $teacherIds = []
    ): array {
        $result = [];

        $query = new Query();
        $query->select([
            'lesson.id',
            'lesson.start_time',
            'lesson.end_time',
            'lesson.date',
            'lesson.group_id',
            'lesson.teacher_id',
            'lesson.room_id',
            'lesson.status',
            'group.code as group_code',
            'group.color as color',
            'group.name as group_name',
            'user.fio as teacher_fio',
            'room.name as room_name',
            'room.code as room_code',
        ])
            ->from(Lesson::tableName())
            ->innerJoin(Group::tableName(), 'lesson.group_id = group.id AND group.is_deleted != 1')
            ->innerJoin(User::tableName(), 'lesson.teacher_id = user.id')
            ->leftJoin('room', 'lesson.room_id = room.id AND room.is_deleted != 1')
            ->andWhere(['lesson.organization_id' => Organizations::getCurrentOrganizationId()])
            ->andWhere('lesson.is_deleted != 1')
            ->andWhere(['>=', 'lesson.date', $start])
            ->andWhere(['<=', 'lesson.date', $end])
            ->orderBy('lesson.date ASC, lesson.start_time ASC');

        // Применяем фильтры
        if (!empty($groupIds)) {
            $query->andWhere(['lesson.group_id' => $groupIds]);
        }
        if (!empty($teacherIds)) {
            $query->andWhere(['lesson.teacher_id' => $teacherIds]);
        }

        $events = $query->all();

        foreach ($events as $event) {
            $result[] = [
                'id' => (int)$event['id'],
                'start' => strtotime($event['date'] . ' ' . $event['start_time']),
                'end' => strtotime($event['date'] . ' ' . $event['end_time']),
                'date' => $event['date'],
                'date_raw' => $event['date'], // для формы редактирования (input type="date")
                'start_time' => substr($event['start_time'], 0, 5),
                'end_time' => substr($event['end_time'], 0, 5),
                'title' => $event['group_code'] . ' - ' . $event['group_name'],
                'color' => $event['color'] ?: '#3b82f6',
                'teacher' => $event['teacher_fio'],
                'group_id' => (int)$event['group_id'],
                'teacher_id' => (int)$event['teacher_id'],
                'room_id' => $event['room_id'] ? (int)$event['room_id'] : null,
                'room' => $event['room_name'] ? ($event['room_code'] ? $event['room_code'] . ' - ' . $event['room_name'] : $event['room_name']) : null,
                'status' => (int)$event['status'],
            ];
        }

        return $result;
    }

    /**
     * Получить группы с занятиями для фильтра
     *
     * @return array [{id, code, name, color}]
     */
    public static function getGroupsForFilter(): array
    {
        // Получаем только группы, у которых есть занятия
        $groupIdsWithLessons = Lesson::find()
            ->select(['group_id'])
            ->byOrganization()
            ->notDeleted()
            ->distinct()
            ->column();

        if (empty($groupIdsWithLessons)) {
            return [];
        }

        $groups = Group::find()
            ->select(['id', 'code', 'name', 'color'])
            ->where(['id' => $groupIdsWithLessons])
            ->byOrganization()
            ->notDeleted()
            ->orderBy('code ASC')
            ->asArray()
            ->all();

        return array_map(function ($group) {
            return [
                'id' => (int)$group['id'],
                'code' => $group['code'],
                'name' => $group['name'],
                'color' => $group['color'] ?: '#3b82f6',
            ];
        }, $groups);
    }

    /**
     * Получить всех учителей для фильтра
     *
     * @return array [{id, fio}]
     */
    public static function getTeachersForFilter(): array
    {
        $teachers = Organizations::getOrganizationTeachers();

        return array_map(function ($teacher) {
            return [
                'id' => (int)$teacher->id,
                'fio' => $teacher->fio,
            ];
        }, $teachers);
    }

    /**
     * Получить детали урока для модального окна
     *
     * @param int $id ID урока
     * @return array|null
     */
    public static function getLessonDetails(int $id): ?array
    {
        $lesson = Lesson::find()
            ->where(['id' => $id])
            ->byOrganization()
            ->notDeleted()
            ->one();

        if (!$lesson) {
            return null;
        }

        $group = $lesson->group;
        $teacher = $lesson->teacher;
        $room = $lesson->room;

        // Получаем учеников группы
        $pupils = $lesson->getPupils();
        $pupilIds = array_map(fn($pupil) => $pupil->id, $pupils);

        // Загружаем все посещения одним запросом (исправление N+1)
        $attendances = [];
        if (!empty($pupilIds)) {
            $attendanceRecords = \app\models\LessonAttendance::find()
                ->where(['lesson_id' => $lesson->id])
                ->andWhere(['in', 'pupil_id', $pupilIds])
                ->notDeleted()
                ->indexBy('pupil_id')
                ->all();
            $attendances = $attendanceRecords;
        }

        $pupilsData = [];
        foreach ($pupils as $pupil) {
            $attendance = $attendances[$pupil->id] ?? null;
            $pupilsData[] = [
                'id' => $pupil->id,
                'fio' => $pupil->fio,
                'status' => $attendance ? $attendance->status : null,
                'status_label' => $attendance ? $attendance->getStatusLabel() : 'Не задано',
            ];
        }

        return [
            'id' => $lesson->id,
            'date' => date('d.m.Y', strtotime($lesson->date)),
            'date_raw' => $lesson->date,
            'start_time' => substr($lesson->start_time, 0, 5),
            'end_time' => substr($lesson->end_time, 0, 5),
            'status' => $lesson->status,
            'group_id' => $lesson->group_id,
            'group' => $group ? [
                'id' => $group->id,
                'code' => $group->code,
                'name' => $group->name,
                'color' => $group->color ?: '#3b82f6',
            ] : null,
            'teacher' => $teacher ? [
                'id' => $teacher->id,
                'fio' => $teacher->fio,
            ] : null,
            'room_id' => $lesson->room_id,
            'room' => $room ? [
                'id' => $room->id,
                'name' => $room->name,
                'code' => $room->code,
            ] : null,
            'pupils' => $pupilsData,
            'pupils_count' => count($pupilsData),
        ];
    }

    /**
     * Переместить урок (drag & drop)
     *
     * @param int $id ID урока
     * @param string $newDate Новая дата (Y-m-d)
     * @param string $newStartTime Новое время начала (H:i)
     * @return bool
     */
    public static function moveLesson(int $id, string $newDate, string $newStartTime): bool
    {
        $lesson = Lesson::find()
            ->where(['id' => $id])
            ->byOrganization()
            ->notDeleted()
            ->one();

        if (!$lesson) {
            return false;
        }

        // Вычисляем новое время окончания (сохраняем длительность)
        $oldStart = strtotime($lesson->date . ' ' . $lesson->start_time);
        $oldEnd = strtotime($lesson->date . ' ' . $lesson->end_time);
        $duration = $oldEnd - $oldStart;

        $newStart = strtotime($newDate . ' ' . $newStartTime);
        $newEnd = $newStart + $duration;

        $lesson->date = $newDate;
        $lesson->start_time = date('H:i', $newStart);
        $lesson->end_time = date('H:i', $newEnd);
        $lesson->week = date('w', strtotime($newDate));

        return $lesson->save(false);
    }

    /**
     * Получить типовое расписание для календаря
     *
     * @return array
     */
    public static function getTypicalScheduleEventsForCalendar(): array
    {
        $result = [];

        $schedules = TypicalSchedule::find()
            ->with(['group', 'teacher', 'room'])
            ->byOrganization()
            ->notDeleted()
            ->orderBy('week ASC, start_time ASC')
            ->all();

        $daysOfWeek = [
            1 => 'Понедельник',
            2 => 'Вторник',
            3 => 'Среда',
            4 => 'Четверг',
            5 => 'Пятница',
            6 => 'Суббота',
            7 => 'Воскресенье',
        ];

        foreach ($schedules as $schedule) {
            $result[] = [
                'id' => $schedule->id,
                'week' => (int)$schedule->week,
                'day_name' => $daysOfWeek[$schedule->week] ?? '',
                'start_time' => substr($schedule->start_time, 0, 5),
                'end_time' => substr($schedule->end_time, 0, 5),
                'group_id' => $schedule->group_id,
                'group_code' => $schedule->group->code ?? '',
                'group_name' => $schedule->group->name ?? '',
                'color' => $schedule->group->color ?? '#3b82f6',
                'teacher_id' => $schedule->teacher_id,
                'teacher_fio' => $schedule->teacher->fio ?? '',
                'room_id' => $schedule->room_id,
                'room_name' => $schedule->room ? ($schedule->room->code ? $schedule->room->code . ' - ' . $schedule->room->name : $schedule->room->name) : null,
            ];
        }

        return $result;
    }

    /**
     * Получить предпросмотр генерируемого расписания из типового
     *
     * @param string $dateStart Начальная дата (d.m.Y или Y-m-d)
     * @param string $dateEnd Конечная дата (d.m.Y или Y-m-d)
     * @return array
     */
    public static function getTypicalSchedulePreview(string $dateStart, string $dateEnd): array
    {
        // Преобразуем даты
        if (strpos($dateStart, '.') !== false) {
            $start = \DateTime::createFromFormat('d.m.Y', $dateStart);
        } else {
            $start = new \DateTime($dateStart);
        }

        if (strpos($dateEnd, '.') !== false) {
            $end = \DateTime::createFromFormat('d.m.Y', $dateEnd);
        } else {
            $end = new \DateTime($dateEnd);
        }

        if (!$start || !$end) {
            return ['success' => false, 'message' => 'Неверный формат дат'];
        }

        // Получаем типовое расписание
        $schedules = TypicalSchedule::find()
            ->with(['group', 'teacher', 'room'])
            ->byOrganization()
            ->notDeleted()
            ->orderBy('week ASC, start_time ASC')
            ->all();

        // Группируем по дням недели
        $byWeekDay = [];
        foreach ($schedules as $schedule) {
            $week = (int)$schedule->week;
            if (!isset($byWeekDay[$week])) {
                $byWeekDay[$week] = [];
            }
            $byWeekDay[$week][] = $schedule;
        }

        $lessons = [];
        $conflicts = [];
        $byDay = [];

        // Итерируем по датам
        $current = clone $start;
        while ($current <= $end) {
            $weekDay = (int)$current->format('N'); // 1-7
            $dateStr = $current->format('Y-m-d');
            $dateFormatted = $current->format('d.m.Y');

            if (isset($byWeekDay[$weekDay])) {
                foreach ($byWeekDay[$weekDay] as $schedule) {
                    $lesson = [
                        'date' => $dateStr,
                        'date_formatted' => $dateFormatted,
                        'day_name' => self::getDayName($weekDay),
                        'start_time' => substr($schedule->start_time, 0, 5),
                        'end_time' => substr($schedule->end_time, 0, 5),
                        'group_id' => $schedule->group_id,
                        'group_code' => $schedule->group->code ?? '',
                        'group_name' => $schedule->group->name ?? '',
                        'color' => $schedule->group->color ?? '#3b82f6',
                        'teacher_id' => $schedule->teacher_id,
                        'teacher_fio' => $schedule->teacher->fio ?? '',
                        'room_id' => $schedule->room_id,
                        'room_name' => $schedule->room ? ($schedule->room->code ? $schedule->room->code . ' - ' . $schedule->room->name : $schedule->room->name) : null,
                        'typical_schedule_id' => $schedule->id,
                    ];

                    // Проверяем конфликты
                    $lessonConflicts = ScheduleConflictService::checkAllConflicts(
                        $schedule->teacher_id,
                        $schedule->group_id,
                        $schedule->room_id,
                        $dateStr,
                        $schedule->start_time,
                        $schedule->end_time
                    );

                    if (!empty($lessonConflicts)) {
                        $lesson['has_conflict'] = true;
                        $lesson['conflicts'] = $lessonConflicts;
                        $conflicts[] = [
                            'lesson' => $lesson,
                            'conflicts' => $lessonConflicts,
                        ];
                    } else {
                        $lesson['has_conflict'] = false;
                    }

                    $lessons[] = $lesson;

                    // Группируем по дням
                    if (!isset($byDay[$dateStr])) {
                        $byDay[$dateStr] = [
                            'date' => $dateStr,
                            'date_formatted' => $dateFormatted,
                            'day_name' => self::getDayName($weekDay),
                            'lessons' => [],
                        ];
                    }
                    $byDay[$dateStr]['lessons'][] = $lesson;
                }
            }

            $current->modify('+1 day');
        }

        return [
            'success' => true,
            'total' => count($lessons),
            'total_conflicts' => count($conflicts),
            'lessons' => $lessons,
            'by_day' => array_values($byDay),
            'conflicts' => $conflicts,
        ];
    }

    /**
     * Создать расписание из типового
     *
     * @param string $dateStart
     * @param string $dateEnd
     * @param bool $skipConflicts
     * @return array
     */
    public static function generateFromTypicalSchedule(string $dateStart, string $dateEnd, bool $skipConflicts = false): array
    {
        $preview = self::getTypicalSchedulePreview($dateStart, $dateEnd);

        if (!$preview['success']) {
            return $preview;
        }

        $created = 0;
        $skipped = 0;
        $errors = [];

        $transaction = \Yii::$app->db->beginTransaction();

        try {
            foreach ($preview['lessons'] as $lessonData) {
                // Пропускаем занятия с конфликтами если нужно
                if ($skipConflicts && !empty($lessonData['has_conflict'])) {
                    $skipped++;
                    continue;
                }

                $lesson = new Lesson();
                $lesson->date = $lessonData['date'];
                $lesson->group_id = $lessonData['group_id'];
                $lesson->teacher_id = $lessonData['teacher_id'];
                $lesson->room_id = $lessonData['room_id'] ?? null;
                $lesson->start_time = $lessonData['start_time'];
                $lesson->end_time = $lessonData['end_time'];
                $lesson->typical_schedule_id = $lessonData['typical_schedule_id'];

                if ($lesson->save(false)) {
                    $created++;
                } else {
                    $errors[] = [
                        'date' => $lessonData['date_formatted'],
                        'group' => $lessonData['group_code'],
                        'error' => implode(', ', $lesson->getFirstErrors()),
                    ];
                }
            }

            $transaction->commit();

            return [
                'success' => true,
                'message' => "Создано {$created} занятий" . ($skipped > 0 ? ", пропущено {$skipped}" : ''),
                'created' => $created,
                'skipped' => $skipped,
                'errors' => $errors,
            ];
        } catch (\Exception $e) {
            $transaction->rollBack();
            return [
                'success' => false,
                'message' => 'Ошибка при создании расписания: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Получить название дня недели
     *
     * @param int $weekDay 1-7
     * @return string
     */
    private static function getDayName(int $weekDay): string
    {
        $days = [
            1 => 'Пн',
            2 => 'Вт',
            3 => 'Ср',
            4 => 'Чт',
            5 => 'Пт',
            6 => 'Сб',
            7 => 'Вс',
        ];
        return $days[$weekDay] ?? '';
    }

    /**
     * Получить список кабинетов для фильтра
     *
     * @return array [{id, name, code, color}]
     */
    public static function getRoomsForFilter(): array
    {
        $rooms = Room::find()
            ->select(['id', 'name', 'code', 'color'])
            ->byOrganization()
            ->notDeleted()
            ->orderBy(['sort_order' => SORT_ASC, 'name' => SORT_ASC])
            ->asArray()
            ->all();

        return array_map(function ($room) {
            return [
                'id' => (int)$room['id'],
                'name' => $room['name'],
                'code' => $room['code'],
                'color' => $room['color'] ?: '#6366f1',
            ];
        }, $rooms);
    }
}
