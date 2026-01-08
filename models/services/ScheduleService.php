<?php

namespace app\models\services;

use app\helpers\OrganizationUrl;
use app\helpers\RoleChecker;
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

        // Для учителя - показываем только его занятия
        if (RoleChecker::isTeacherOnly()) {
            $teacherId = RoleChecker::getCurrentTeacherId();
            if ($teacherId) {
                $query->andWhere(['lesson.teacher_id' => $teacherId]);
            }
        } else {
            // Применяем фильтры (только для не-учителей или в дополнение)
            if (!empty($groupIds)) {
                $query->andWhere(['lesson.group_id' => $groupIds]);
            }
            if (!empty($teacherIds)) {
                $query->andWhere(['lesson.teacher_id' => $teacherIds]);
            }
        }

        $events = $query->all();

        // ОПТИМИЗИРОВАНО: минимальный набор полей для календаря
        // Детальная информация загружается отдельно через getLessonDetails()
        foreach ($events as $event) {
            $item = [
                'id' => (int)$event['id'],
                'group_id' => (int)$event['group_id'],      // нужен для фильтрации групп в UI
                'teacher_id' => (int)$event['teacher_id'],  // нужен для фильтрации преподавателей в UI
                'date' => $event['date'],
                'start_time' => substr($event['start_time'], 0, 5),
                'end_time' => substr($event['end_time'], 0, 5),
                'title' => $event['group_code'] . ' - ' . $event['group_name'],
                'color' => $event['color'] ?: '#3b82f6',
                'teacher' => $event['teacher_fio'],
            ];

            // Добавляем room_id и room только если есть значение (экономия трафика)
            if ($event['room_id']) {
                $item['room_id'] = (int)$event['room_id'];
                $item['room'] = $event['room_code']
                    ? $event['room_code'] . ' - ' . $event['room_name']
                    : $event['room_name'];
            }

            $result[] = $item;
        }

        return $result;
    }

    /**
     * Получить группы с занятиями для фильтра
     * ОПТИМИЗИРОВАНО: один запрос с EXISTS вместо двух отдельных
     * Для учителя - возвращает только его группы
     *
     * @return array [{id, code, name, color}]
     */
    public static function getGroupsForFilter(): array
    {
        $orgId = Organizations::getCurrentOrganizationId();

        // Для учителя - возвращаем только его группы
        if (RoleChecker::isTeacherOnly()) {
            $teacherId = RoleChecker::getCurrentTeacherId();
            if (!$teacherId) {
                return [];
            }

            // Получаем группы учителя
            $groups = Group::find()
                ->select(['group.id', 'group.code', 'group.name', 'group.color'])
                ->innerJoin('teacher_group', 'teacher_group.target_id = group.id AND teacher_group.is_deleted != 1')
                ->where(['group.organization_id' => $orgId])
                ->andWhere(['!=', 'group.is_deleted', 1])
                ->andWhere(['teacher_group.related_id' => $teacherId])
                ->orderBy('group.code ASC')
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

        // Один запрос с EXISTS subquery вместо двух отдельных запросов
        $groups = Group::find()
            ->select(['group.id', 'group.code', 'group.name', 'group.color'])
            ->where(['group.organization_id' => $orgId])
            ->andWhere(['!=', 'group.is_deleted', 1])
            ->andWhere([
                'exists',
                Lesson::find()
                    ->select([new \yii\db\Expression('1')])
                    ->where('lesson.group_id = group.id')
                    ->andWhere(['lesson.organization_id' => $orgId])
                    ->andWhere(['!=', 'lesson.is_deleted', 1])
                    ->limit(1)
            ])
            ->orderBy('group.code ASC')
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
     * ОПТИМИЗИРОВАНО: используем asArray() вместо загрузки полных моделей
     * Для учителя - возвращает только себя
     *
     * @return array [{id, fio}]
     */
    public static function getTeachersForFilter(): array
    {
        // Для учителя - возвращаем только его самого
        if (RoleChecker::isTeacherOnly()) {
            $teacherId = RoleChecker::getCurrentTeacherId();
            if (!$teacherId) {
                return [];
            }

            $teacher = User::find()
                ->select(['id', 'fio'])
                ->where(['id' => $teacherId])
                ->asArray()
                ->one();

            return $teacher ? [[
                'id' => (int)$teacher['id'],
                'fio' => $teacher['fio'],
            ]] : [];
        }

        $teachers = User::find()
            ->select(['user.id', 'user.fio'])
            ->innerJoinWith(['currentUserOrganizations' => function ($q) {
                $q->andWhere(['<>', 'user_organization.is_deleted', 1])
                    ->andWhere(['user_organization.role' => \app\helpers\OrganizationRoles::TEACHER]);
            }])
            ->orderBy('user.fio ASC')
            ->asArray()
            ->all();

        return array_map(function ($teacher) {
            return [
                'id' => (int)$teacher['id'],
                'fio' => $teacher['fio'],
            ];
        }, $teachers);
    }

    /**
     * Получить связи преподаватель-группа для зависимых фильтров
     *
     * @return array [{teacher_id, group_id}]
     */
    public static function getTeacherGroupRelations(): array
    {
        $relations = TeacherGroup::find()
            ->select(['related_id as teacher_id', 'target_id as group_id'])
            ->byOrganization()
            ->notDeleted()
            ->asArray()
            ->all();

        return array_map(function ($rel) {
            return [
                'teacher_id' => (int)$rel['teacher_id'],
                'group_id' => (int)$rel['group_id'],
            ];
        }, $relations);
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
     * @param int|null $roomId Новый кабинет (null = оставить без изменений, 0 или пустая строка = убрать кабинет)
     * @return bool
     */
    public static function moveLesson(int $id, string $newDate, string $newStartTime, $roomId = null): bool
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

        // Обновляем кабинет, если передан
        if ($roomId !== null) {
            $lesson->room_id = !empty($roomId) ? (int)$roomId : null;
        }

        return $lesson->save(false);
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
