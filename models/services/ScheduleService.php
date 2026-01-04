<?php

namespace app\models\services;

use app\helpers\OrganizationUrl;
use app\models\Group;
use app\models\Lesson;
use app\models\Organizations;
use app\models\relations\TeacherGroup;
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

        $group = Group::findOne($groupId);
        if (!$group) {
            return $result;
        }

        $teacherGroups = TeacherGroup::find()
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
}
