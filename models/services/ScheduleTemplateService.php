<?php

namespace app\models\services;

use app\models\Group;
use app\models\Lesson;
use app\models\Organizations;
use app\models\ScheduleTemplate;
use app\models\TypicalSchedule;

/**
 * ScheduleTemplateService - сервис для работы с шаблонами расписания
 */
class ScheduleTemplateService
{
    private static $daysOfWeek = [
        1 => 'Понедельник',
        2 => 'Вторник',
        3 => 'Среда',
        4 => 'Четверг',
        5 => 'Пятница',
        6 => 'Суббота',
        7 => 'Воскресенье',
    ];

    private static $shortDaysOfWeek = [
        1 => 'Пн',
        2 => 'Вт',
        3 => 'Ср',
        4 => 'Чт',
        5 => 'Пт',
        6 => 'Сб',
        7 => 'Вс',
    ];

    /**
     * Получить события шаблона для календаря
     *
     * @param int $templateId ID шаблона
     * @return array
     */
    public static function getTemplateEvents(int $templateId): array
    {
        $result = [];

        $schedules = TypicalSchedule::find()
            ->with(['group', 'teacher', 'room'])
            ->where(['template_id' => $templateId])
            ->byOrganization()
            ->notDeleted()
            ->orderBy('week ASC, start_time ASC')
            ->all();

        foreach ($schedules as $schedule) {
            $result[] = [
                'id' => $schedule->id,
                'week' => (int)$schedule->week,
                'day_name' => self::$daysOfWeek[$schedule->week] ?? '',
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
     * Получить предпросмотр генерируемого расписания из шаблона
     *
     * @param int $templateId ID шаблона
     * @param string $dateStart Начальная дата (d.m.Y или Y-m-d)
     * @param string $dateEnd Конечная дата (d.m.Y или Y-m-d)
     * @param array|null $dayMapping Маппинг дней {sourceDay => targetDay}, null = без маппинга
     * @return array
     */
    public static function getPreview(int $templateId, string $dateStart, string $dateEnd, ?array $dayMapping = null): array
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

        // Получаем занятия шаблона
        $schedules = TypicalSchedule::find()
            ->with(['group', 'teacher', 'room'])
            ->where(['template_id' => $templateId])
            ->byOrganization()
            ->notDeleted()
            ->orderBy('week ASC, start_time ASC')
            ->all();

        // Группируем по дням недели с учетом маппинга
        $byWeekDay = [];
        foreach ($schedules as $schedule) {
            $sourceWeek = (int)$schedule->week;

            // Если маппинг не задан или день включен в маппинг
            if ($dayMapping === null) {
                $targetWeek = $sourceWeek;
            } elseif (isset($dayMapping[$sourceWeek])) {
                $targetWeek = (int)$dayMapping[$sourceWeek];
            } else {
                // День выключен в маппинге - пропускаем
                continue;
            }

            if (!isset($byWeekDay[$targetWeek])) {
                $byWeekDay[$targetWeek] = [];
            }
            $byWeekDay[$targetWeek][] = $schedule;
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
                        'day_name' => self::$shortDaysOfWeek[$weekDay] ?? '',
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
                            'day_name' => self::$shortDaysOfWeek[$weekDay] ?? '',
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
     * Создать расписание из переданного списка занятий
     *
     * @param array $lessonsData Массив занятий [{date, start_time, end_time, group_id, teacher_id, room_id, typical_schedule_id}, ...]
     * @return array
     */
    public static function generateFromLessons(array $lessonsData): array
    {
        if (empty($lessonsData)) {
            return ['success' => false, 'message' => 'Нет занятий для создания'];
        }

        $created = 0;
        $errors = [];

        $transaction = \Yii::$app->db->beginTransaction();

        try {
            foreach ($lessonsData as $lessonData) {
                $lesson = new Lesson();
                $lesson->date = $lessonData['date'];
                $lesson->group_id = $lessonData['group_id'];
                $lesson->teacher_id = $lessonData['teacher_id'];
                $lesson->room_id = $lessonData['room_id'] ?? null;
                $lesson->start_time = $lessonData['start_time'];
                $lesson->end_time = $lessonData['end_time'];
                $lesson->typical_schedule_id = $lessonData['typical_schedule_id'] ?? null;

                if ($lesson->save(false)) {
                    $created++;
                } else {
                    $errors[] = [
                        'date' => $lessonData['date'],
                        'error' => implode(', ', $lesson->getFirstErrors()),
                    ];
                }
            }

            $transaction->commit();

            return [
                'success' => true,
                'message' => "Создано {$created} занятий",
                'created' => $created,
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
     * Создать расписание из шаблона
     *
     * @param int $templateId
     * @param string $dateStart
     * @param string $dateEnd
     * @param bool $skipConflicts
     * @return array
     */
    public static function generateFromTemplate(int $templateId, string $dateStart, string $dateEnd, bool $skipConflicts = false): array
    {
        $preview = self::getPreview($templateId, $dateStart, $dateEnd);

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
     * Получить все шаблоны с количеством занятий
     *
     * @return array
     */
    public static function getTemplatesWithCounts(): array
    {
        $templates = ScheduleTemplate::find()
            ->byOrganization()
            ->andWhere(['is_deleted' => 0])
            ->orderBy(['is_default' => SORT_DESC, 'name' => SORT_ASC])
            ->all();

        $result = [];
        foreach ($templates as $template) {
            $result[] = [
                'id' => $template->id,
                'name' => $template->name,
                'description' => $template->description,
                'color' => $template->color,
                'is_default' => (bool)$template->is_default,
                'is_active' => (bool)$template->is_active,
                'lessons_count' => $template->getLessonsCount(),
                'created_at' => $template->created_at,
                'updated_at' => $template->updated_at,
            ];
        }

        return $result;
    }

    /**
     * Добавить занятие в шаблон
     *
     * @param int $templateId
     * @param array $data
     * @return array
     */
    public static function addLesson(int $templateId, array $data): array
    {
        $template = ScheduleTemplate::find()
            ->where(['id' => $templateId])
            ->byOrganization()
            ->andWhere(['is_deleted' => 0])
            ->one();

        if (!$template) {
            return ['success' => false, 'message' => 'Шаблон не найден'];
        }

        $lesson = new TypicalSchedule();
        $lesson->template_id = $templateId;
        $lesson->week = $data['week'] ?? null;
        $lesson->group_id = $data['group_id'] ?? null;
        $lesson->teacher_id = $data['teacher_id'] ?? null;
        $lesson->room_id = $data['room_id'] ?? null;
        $lesson->start_time = $data['start_time'] ?? null;
        $lesson->end_time = $data['end_time'] ?? null;

        if ($lesson->save()) {
            return [
                'success' => true,
                'message' => 'Занятие добавлено',
                'id' => $lesson->id,
            ];
        }

        return [
            'success' => false,
            'message' => 'Ошибка сохранения',
            'errors' => $lesson->getErrors(),
        ];
    }

    /**
     * Обновить занятие в шаблоне
     *
     * @param int $lessonId
     * @param array $data
     * @return array
     */
    public static function updateLesson(int $lessonId, array $data): array
    {
        $lesson = TypicalSchedule::find()
            ->where(['id' => $lessonId])
            ->byOrganization()
            ->notDeleted()
            ->one();

        if (!$lesson) {
            return ['success' => false, 'message' => 'Занятие не найдено'];
        }

        if (isset($data['week'])) $lesson->week = $data['week'];
        if (isset($data['group_id'])) $lesson->group_id = $data['group_id'];
        if (isset($data['teacher_id'])) $lesson->teacher_id = $data['teacher_id'];
        if (isset($data['room_id'])) $lesson->room_id = $data['room_id'];
        if (isset($data['start_time'])) $lesson->start_time = $data['start_time'];
        if (isset($data['end_time'])) $lesson->end_time = $data['end_time'];

        if ($lesson->save()) {
            return [
                'success' => true,
                'message' => 'Занятие обновлено',
            ];
        }

        return [
            'success' => false,
            'message' => 'Ошибка сохранения',
            'errors' => $lesson->getErrors(),
        ];
    }

    /**
     * Удалить занятие из шаблона
     *
     * @param int $lessonId
     * @return array
     */
    public static function deleteLesson(int $lessonId): array
    {
        $lesson = TypicalSchedule::find()
            ->where(['id' => $lessonId])
            ->byOrganization()
            ->notDeleted()
            ->one();

        if (!$lesson) {
            return ['success' => false, 'message' => 'Занятие не найдено'];
        }

        if ($lesson->delete()) {
            return ['success' => true, 'message' => 'Занятие удалено'];
        }

        return ['success' => false, 'message' => 'Ошибка удаления'];
    }

    /**
     * Получить данные для формы добавления занятия
     *
     * @return array
     */
    public static function getFormData(): array
    {
        return [
            'groups' => ScheduleService::getGroupsForFilter(),
            'teachers' => ScheduleService::getTeachersForFilter(),
            'rooms' => ScheduleService::getRoomsForFilter(),
            'days' => self::$daysOfWeek,
        ];
    }
}
