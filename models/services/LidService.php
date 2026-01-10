<?php

namespace app\models\services;

use app\helpers\ActivityLogger;
use app\helpers\DateHelper;
use app\models\Lids;
use app\models\LidHistory;
use app\models\Pupil;
use app\models\User;
use app\models\Organizations;
use Yii;

/**
 * Сервис для работы с лидами
 */
class LidService
{
    /**
     * Конверсия лида в ученика
     *
     * @param Lids $lid
     * @return Pupil|null
     */
    public static function convertToPupil(Lids $lid): ?Pupil
    {
        if (!$lid->canConvertToPupil()) {
            return null;
        }

        $transaction = Yii::$app->db->beginTransaction();

        try {
            // Создаём ученика
            $pupil = new Pupil();

            // Разбираем ФИО ребёнка
            $fioParts = preg_split('/\s+/', trim($lid->fio), 3);
            $pupil->last_name = $fioParts[0] ?? '';
            $pupil->first_name = $fioParts[1] ?? '';
            $pupil->middle_name = $fioParts[2] ?? '';

            // Контактные данные ребёнка
            $pupil->phone = $lid->phone;

            // Данные родителя
            $pupil->parent_fio = $lid->parent_fio;
            $pupil->parent_phone = $lid->parent_phone;

            // Дополнительные данные
            $pupil->school_name = $lid->school;
            $pupil->class_id = $lid->class_id;

            // Временный ИИН (нужно будет заполнить вручную)
            $pupil->iin = 'TEMP_' . time() . '_' . $lid->id;
            $pupil->sex = 1; // По умолчанию, нужно указать

            if (!$pupil->save(false)) {
                throw new \Exception('Ошибка создания ученика: ' . json_encode($pupil->errors));
            }

            // Обновляем лид
            $lid->pupil_id = $pupil->id;
            $lid->converted_at = DateHelper::now();
            $lid->save(false);

            // Запись в историю
            LidHistory::createConverted($lid, $pupil);
            ActivityLogger::logLidConverted($lid, $pupil);

            $transaction->commit();
            return $pupil;

        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::error('LidService::convertToPupil error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Смена статуса с записью в историю
     *
     * @param Lids $lid
     * @param int $newStatus
     * @param string|null $comment
     * @param bool $autoConvert Автоматически конвертировать при PAID (для обратной совместимости)
     * @return array Результат: ['success' => bool, 'needs_conversion' => bool, 'message' => string]
     */
    public static function changeStatus(Lids $lid, int $newStatus, ?string $comment = null, bool $autoConvert = false): array
    {
        $oldStatus = $lid->status;

        if ($oldStatus === $newStatus) {
            return ['success' => true, 'needs_conversion' => false, 'message' => 'Статус не изменился'];
        }

        if (!$lid->canMoveToStatus($newStatus)) {
            return ['success' => false, 'needs_conversion' => false, 'message' => 'Невозможно перейти в этот статус'];
        }

        $transaction = Yii::$app->db->beginTransaction();

        try {
            $lid->status = $newStatus;

            if (!$lid->save(false)) {
                throw new \Exception('Ошибка сохранения лида');
            }

            LidHistory::createStatusChange($lid, $oldStatus, $newStatus, $comment);

            $transaction->commit();

            // Определяем, нужна ли конверсия
            $needsConversion = in_array($newStatus, [Lids::STATUS_ENROLLED, Lids::STATUS_PAID])
                               && $lid->pupil_id === null;

            // Для обратной совместимости: автоконверсия при PAID если включена
            if ($autoConvert && $newStatus === Lids::STATUS_PAID && $lid->pupil_id === null) {
                self::convertToPupil($lid);
                $needsConversion = false;
            }

            return [
                'success' => true,
                'needs_conversion' => $needsConversion,
                'message' => 'Статус обновлён',
                'status' => $newStatus,
                'status_label' => $lid->getStatusLabel(),
            ];

        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::error('LidService::changeStatus error: ' . $e->getMessage());
            return ['success' => false, 'needs_conversion' => false, 'message' => 'Ошибка: ' . $e->getMessage()];
        }
    }

    /**
     * Добавить взаимодействие
     *
     * @param Lids $lid
     * @param string $type
     * @param string $comment
     * @param string|null $nextContactDate
     * @param int|null $callDuration
     * @return bool
     */
    public static function addInteraction(
        Lids $lid,
        string $type,
        string $comment,
        ?string $nextContactDate = null,
        ?int $callDuration = null
    ): bool {
        $transaction = Yii::$app->db->beginTransaction();

        try {
            $history = new LidHistory();
            $history->lid_id = $lid->id;
            $history->type = $type;
            $history->comment = $comment;
            $history->call_duration = $callDuration;
            $history->next_contact_date = $nextContactDate;

            if (!$history->save()) {
                throw new \Exception('Ошибка сохранения взаимодействия');
            }

            // Обновляем дату следующего контакта в лиде
            if ($nextContactDate) {
                $lid->next_contact_date = $nextContactDate;
                $lid->save(false);
            }

            $transaction->commit();
            return true;

        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::error('LidService::addInteraction error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Статистика воронки с конверсией
     *
     * @param string|null $dateFrom
     * @param string|null $dateTo
     * @param int|null $managerId
     * @return array
     */
    public static function getFunnelAnalytics(
        ?string $dateFrom = null,
        ?string $dateTo = null,
        ?int $managerId = null
    ): array {
        $query = Lids::find()
            ->byOrganization()
            ->notDeleted();

        if ($dateFrom) {
            $query->andWhere(['>=', 'created_at', $dateFrom . ' 00:00:00']);
        }
        if ($dateTo) {
            $query->andWhere(['<=', 'created_at', $dateTo . ' 23:59:59']);
        }
        if ($managerId) {
            $query->andWhere(['manager_id' => $managerId]);
        }

        $total = (clone $query)->count();
        $stats = [];

        foreach (Lids::getStatusList() as $status => $label) {
            $count = (clone $query)->andWhere(['status' => $status])->count();
            $totalConversion = $total > 0 ? round($count / $total * 100, 1) : 0;

            $stats[$status] = [
                'label' => $label,
                'count' => (int)$count,
                'total_conversion' => $totalConversion,
            ];
        }

        // Рассчитываем конверсию между этапами
        $prevCount = $total;
        foreach ($stats as $status => &$data) {
            if ($status == Lids::STATUS_LOST) {
                $data['step_conversion'] = 0;
            } else {
                $data['step_conversion'] = $prevCount > 0
                    ? round($data['count'] / $prevCount * 100, 1)
                    : 0;
                $prevCount = $data['count'] > 0 ? $data['count'] : $prevCount;
            }
        }

        return [
            'total' => (int)$total,
            'funnel' => $stats,
            'converted' => $stats[Lids::STATUS_PAID]['count'] ?? 0,
            'lost' => $stats[Lids::STATUS_LOST]['count'] ?? 0,
            'conversion_rate' => $total > 0
                ? round(($stats[Lids::STATUS_PAID]['count'] ?? 0) / $total * 100, 1)
                : 0,
        ];
    }

    /**
     * Статистика по менеджерам
     *
     * @param string|null $dateFrom
     * @param string|null $dateTo
     * @return array
     */
    public static function getManagerStats(?string $dateFrom = null, ?string $dateTo = null): array
    {
        $finalStatuses = implode(', ', Lids::getFinalStatusList());
        $query = Lids::find()
            ->select([
                'manager_id',
                'COUNT(*) as total',
                'SUM(CASE WHEN status = ' . Lids::STATUS_PAID . ' THEN 1 ELSE 0 END) as converted',
                'SUM(CASE WHEN status = ' . Lids::STATUS_LOST . ' THEN 1 ELSE 0 END) as lost',
                'SUM(CASE WHEN status = ' . Lids::STATUS_NOT_TARGET . ' THEN 1 ELSE 0 END) as not_target',
                'SUM(CASE WHEN status = ' . Lids::STATUS_IN_TRAINING . ' THEN 1 ELSE 0 END) as in_training',
                'SUM(CASE WHEN status NOT IN (' . $finalStatuses . ', ' . Lids::STATUS_IN_TRAINING . ') THEN 1 ELSE 0 END) as active',
            ])
            ->byOrganization()
            ->notDeleted()
            ->andWhere(['is not', 'manager_id', null])
            ->groupBy('manager_id');

        if ($dateFrom) {
            $query->andWhere(['>=', 'created_at', $dateFrom . ' 00:00:00']);
        }
        if ($dateTo) {
            $query->andWhere(['<=', 'created_at', $dateTo . ' 23:59:59']);
        }

        $results = $query->asArray()->all();

        // Добавляем данные менеджеров
        $managerIds = array_column($results, 'manager_id');
        $managers = User::find()
            ->where(['id' => $managerIds])
            ->indexBy('id')
            ->all();

        foreach ($results as &$result) {
            $manager = $managers[$result['manager_id']] ?? null;
            $result['manager_name'] = $manager ? $manager->fio : 'Неизвестный';
            $result['conversion_rate'] = $result['total'] > 0
                ? round($result['converted'] / $result['total'] * 100, 1)
                : 0;
        }

        // Сортировка по конверсии
        usort($results, function($a, $b) {
            return $b['conversion_rate'] <=> $a['conversion_rate'];
        });

        return $results;
    }

    /**
     * Топ причин потери
     *
     * @param int $limit
     * @return array
     */
    public static function getTopLostReasons(int $limit = 10): array
    {
        return Lids::find()
            ->select(['lost_reason', 'COUNT(*) as count'])
            ->byOrganization()
            ->notDeleted()
            ->andWhere(['status' => Lids::STATUS_LOST])
            ->andWhere(['is not', 'lost_reason', null])
            ->andWhere(['!=', 'lost_reason', ''])
            ->groupBy('lost_reason')
            ->orderBy(['count' => SORT_DESC])
            ->limit($limit)
            ->asArray()
            ->all();
    }

    /**
     * Статистика по источникам
     *
     * @return array
     */
    public static function getSourceStats(): array
    {
        $results = Lids::find()
            ->select([
                'source',
                'COUNT(*) as total',
                'SUM(CASE WHEN status = ' . Lids::STATUS_PAID . ' THEN 1 ELSE 0 END) as converted',
                'SUM(CASE WHEN status = ' . Lids::STATUS_LOST . ' THEN 1 ELSE 0 END) as lost',
            ])
            ->byOrganization()
            ->notDeleted()
            ->andWhere(['is not', 'source', null])
            ->groupBy('source')
            ->orderBy(['total' => SORT_DESC])
            ->asArray()
            ->all();

        $sourceLabels = Lids::getSourceList();

        foreach ($results as &$result) {
            $result['source_label'] = $sourceLabels[$result['source']] ?? $result['source'];
            $result['conversion_rate'] = $result['total'] > 0
                ? round($result['converted'] / $result['total'] * 100, 1)
                : 0;
        }

        return $results;
    }

    /**
     * Получить список менеджеров организации для dropdown
     *
     * @return array
     */
    public static function getManagersForDropdown(): array
    {
        $users = User::find()
            ->joinWith('userOrganizations')
            ->where(['user_organization.organization_id' => Organizations::getCurrentOrganizationId()])
            ->orderBy(['fio' => SORT_ASC])
            ->all();

        $result = [];
        foreach ($users as $user) {
            $result[$user->id] = $user->fio;
        }

        return $result;
    }

    /**
     * Получить лидов по статусу для Kanban
     *
     * @param array $filters Фильтры: search, source, manager_id, class_id, overdue_only, date_from, date_to,
     *                       my_leads_only, contact_today, stale_only, tags, show_not_target, show_in_training
     * @return array
     */
    public static function getKanbanData(array $filters = []): array
    {
        $showNotTarget = !empty($filters['show_not_target']);
        $showInTraining = $filters['show_in_training'] ?? true; // Показываем по умолчанию

        $query = Lids::find()
            ->byOrganization()
            ->notDeleted()
            ->with(['manager']);

        // По умолчанию исключаем NOT_TARGET (показываем только если фильтр включен)
        if (!$showNotTarget) {
            $query->andWhere(['!=', 'status', Lids::STATUS_NOT_TARGET]);
        }

        // Фильтр по поиску (ФИО или телефон)
        if (!empty($filters['search'])) {
            $search = trim($filters['search']);
            $query->andWhere(['or',
                ['like', 'LOWER(fio)', mb_strtolower($search, 'UTF-8')],
                ['like', 'phone', $search],
                ['like', 'parent_phone', $search],
                ['like', 'LOWER(parent_fio)', mb_strtolower($search, 'UTF-8')],
            ]);
        }

        // Фильтр по источнику
        if (!empty($filters['source'])) {
            $query->andWhere(['source' => $filters['source']]);
        }

        // Фильтр по менеджеру
        if (!empty($filters['manager_id'])) {
            $query->andWhere(['manager_id' => $filters['manager_id']]);
        }

        // Фильтр по классу
        if (!empty($filters['class_id'])) {
            $query->andWhere(['class_id' => $filters['class_id']]);
        }

        // Только просроченные
        if (!empty($filters['overdue_only'])) {
            $query->andWhere(['<', 'next_contact_date', DateHelper::today()]);
        }

        // Фильтр по дате создания (от)
        if (!empty($filters['date_from'])) {
            $query->andWhere(['>=', 'created_at', $filters['date_from'] . ' 00:00:00']);
        }

        // Фильтр по дате создания (до)
        if (!empty($filters['date_to'])) {
            $query->andWhere(['<=', 'created_at', $filters['date_to'] . ' 23:59:59']);
        }

        // НОВЫЕ ФИЛЬТРЫ

        // Только мои лиды
        if (!empty($filters['my_leads_only']) && !Yii::$app->user->isGuest) {
            $query->andWhere(['manager_id' => Yii::$app->user->id]);
        }

        // Контакт сегодня
        if (!empty($filters['contact_today'])) {
            $query->andWhere(['next_contact_date' => DateHelper::today()]);
        }

        // Долго в статусе (более 7 дней)
        if (!empty($filters['stale_only'])) {
            $query->andWhere(['<=', 'status_changed_at', DateHelper::relative('-7 days', true)]);
            // Исключаем финальные статусы
            $query->andWhere(['not in', 'status', Lids::getFinalStatusList()]);
        }

        // Фильтр по тегам (JSON contains)
        if (!empty($filters['tags'])) {
            $tags = (array)$filters['tags'];
            foreach ($tags as $tag) {
                $tag = trim($tag);
                if ($tag) {
                    $query->andWhere(['like', 'tags', '"' . $tag . '"']);
                }
            }
        }

        $lids = $query
            ->orderBy(['next_contact_date' => SORT_ASC, 'created_at' => SORT_DESC])
            ->all();

        $columns = [];
        foreach (Lids::getKanbanStatusList() as $status => $label) {
            $columns[$status] = [
                'status' => $status,
                'label' => $label,
                'color' => self::getStatusColor($status),
                'items' => [],
            ];
        }

        // Добавляем архивные колонки
        $columns[Lids::STATUS_PAID] = [
            'status' => Lids::STATUS_PAID,
            'label' => 'Оплатил',
            'color' => 'green',
            'items' => [],
            'archive' => true,
        ];
        $columns[Lids::STATUS_LOST] = [
            'status' => Lids::STATUS_LOST,
            'label' => 'Отказники',
            'color' => 'red',
            'items' => [],
            'archive' => true,
            'collapsible' => true,
        ];

        // Колонка "В обучении" (справа, всегда видна)
        if ($showInTraining) {
            $columns[Lids::STATUS_IN_TRAINING] = [
                'status' => Lids::STATUS_IN_TRAINING,
                'label' => 'В обучении',
                'color' => 'purple',
                'items' => [],
                'special' => true,
            ];
        }

        // Колонка "Не целевой" (только если фильтр включен)
        if ($showNotTarget) {
            $columns[Lids::STATUS_NOT_TARGET] = [
                'status' => Lids::STATUS_NOT_TARGET,
                'label' => 'Не целевой',
                'color' => 'slate',
                'items' => [],
                'archive' => true,
                'collapsible' => true,
            ];
        }

        foreach ($lids as $lid) {
            if (isset($columns[$lid->status])) {
                $columns[$lid->status]['items'][] = $lid;
            }
        }

        return $columns;
    }

    /**
     * Цвет статуса
     */
    private static function getStatusColor(int $status): string
    {
        $colors = [
            Lids::STATUS_NEW => 'sky',
            Lids::STATUS_CONTACTED => 'blue',
            Lids::STATUS_TRIAL => 'amber',
            Lids::STATUS_THINKING => 'gray',
            Lids::STATUS_ENROLLED => 'indigo',
            Lids::STATUS_PAID => 'green',
            Lids::STATUS_LOST => 'red',
            Lids::STATUS_NOT_TARGET => 'slate',
            Lids::STATUS_IN_TRAINING => 'purple',
        ];
        return $colors[$status] ?? 'gray';
    }

    /**
     * Личная статистика менеджера
     */
    public static function getManagerPersonalStats(int $managerId): array
    {
        $today = DateHelper::today();
        $weekStart = DateHelper::startOfWeek();
        $monthStart = DateHelper::startOfMonth();
        $prevMonthStart = DateHelper::relativeFrom(DateHelper::startOfMonth(), '-1 month');
        $prevMonthEnd = DateHelper::endOfMonth(DateHelper::relative('-1 month'));

        // Контакты на сегодня
        $todayContacts = Lids::find()
            ->byOrganization()
            ->notDeleted()
            ->andWhere(['manager_id' => $managerId])
            ->andWhere(['next_contact_date' => $today])
            ->andWhere(['not in', 'status', [Lids::STATUS_PAID, Lids::STATUS_LOST]])
            ->count();

        // Просроченные контакты
        $overdueContacts = Lids::find()
            ->byOrganization()
            ->notDeleted()
            ->andWhere(['manager_id' => $managerId])
            ->andWhere(['<', 'next_contact_date', $today])
            ->andWhere(['not in', 'status', [Lids::STATUS_PAID, Lids::STATUS_LOST]])
            ->count();

        // Активные лиды (в работе)
        $activeLeads = Lids::find()
            ->byOrganization()
            ->notDeleted()
            ->andWhere(['manager_id' => $managerId])
            ->andWhere(['not in', 'status', [Lids::STATUS_PAID, Lids::STATUS_LOST]])
            ->count();

        // Конверсии за неделю
        $weekConversions = Lids::find()
            ->byOrganization()
            ->notDeleted()
            ->andWhere(['manager_id' => $managerId])
            ->andWhere(['status' => Lids::STATUS_PAID])
            ->andWhere(['>=', 'status_changed_at', $weekStart])
            ->count();

        // Конверсии за месяц
        $monthConversions = Lids::find()
            ->byOrganization()
            ->notDeleted()
            ->andWhere(['manager_id' => $managerId])
            ->andWhere(['status' => Lids::STATUS_PAID])
            ->andWhere(['>=', 'status_changed_at', $monthStart])
            ->count();

        // Конверсии за прошлый месяц (для сравнения)
        $prevMonthConversions = Lids::find()
            ->byOrganization()
            ->notDeleted()
            ->andWhere(['manager_id' => $managerId])
            ->andWhere(['status' => Lids::STATUS_PAID])
            ->andWhere(['>=', 'status_changed_at', $prevMonthStart])
            ->andWhere(['<=', 'status_changed_at', $prevMonthEnd])
            ->count();

        // Новые лиды за месяц
        $monthNewLeads = Lids::find()
            ->byOrganization()
            ->notDeleted()
            ->andWhere(['manager_id' => $managerId])
            ->andWhere(['>=', 'created_at', $monthStart])
            ->count();

        // Потерянные за месяц
        $monthLost = Lids::find()
            ->byOrganization()
            ->notDeleted()
            ->andWhere(['manager_id' => $managerId])
            ->andWhere(['status' => Lids::STATUS_LOST])
            ->andWhere(['>=', 'status_changed_at', $monthStart])
            ->count();

        // Рассчёт конверсии
        $conversionRate = 0;
        if ($monthNewLeads > 0) {
            $conversionRate = round(($monthConversions / $monthNewLeads) * 100, 1);
        }

        // Изменение по сравнению с прошлым месяцем
        $conversionChange = $monthConversions - $prevMonthConversions;

        return [
            'today_contacts' => (int)$todayContacts,
            'overdue_contacts' => (int)$overdueContacts,
            'active_leads' => (int)$activeLeads,
            'week_conversions' => (int)$weekConversions,
            'month_conversions' => (int)$monthConversions,
            'prev_month_conversions' => (int)$prevMonthConversions,
            'conversion_change' => $conversionChange,
            'month_new_leads' => (int)$monthNewLeads,
            'month_lost' => (int)$monthLost,
            'conversion_rate' => $conversionRate,
        ];
    }

    /**
     * Лиды требующие внимания для менеджера
     */
    public static function getAttentionLeadsForManager(int $managerId, int $limit = 5): array
    {
        return Lids::find()
            ->byOrganization()
            ->notDeleted()
            ->andWhere(['manager_id' => $managerId])
            ->andWhere(['not in', 'status', [Lids::STATUS_PAID, Lids::STATUS_LOST]])
            ->andWhere([
                'or',
                ['<=', 'next_contact_date', DateHelper::today()],
                ['next_contact_date' => null],
            ])
            ->orderBy([
                new \yii\db\Expression('CASE WHEN next_contact_date < CURDATE() THEN 0 ELSE 1 END'),
                'next_contact_date' => SORT_ASC,
                'created_at' => SORT_ASC,
            ])
            ->limit($limit)
            ->all();
    }

    // =========================================================================
    // Inline-редактирование полей
    // =========================================================================

    /**
     * Разрешённые поля для inline-редактирования
     */
    private const ALLOWED_FIELDS = [
        'next_contact_date',
        'manager_id',
        'comment',
        'status',
        'source',
        'phone',
        'parent_phone',
        'lost_reason',
    ];

    /**
     * Обновление отдельного поля лида (inline-edit)
     *
     * @param Lids $lid Модель лида
     * @param string $field Название поля
     * @param mixed $value Новое значение
     * @return array Результат операции
     */
    public static function updateField(Lids $lid, string $field, $value): array
    {
        // Проверка разрешённого поля
        if (!in_array($field, self::ALLOWED_FIELDS)) {
            return [
                'success' => false,
                'message' => 'Недопустимое поле для редактирования',
            ];
        }

        // Используем обработчики для каждого поля
        $handlers = [
            'status' => [self::class, 'updateStatusField'],
            'next_contact_date' => [self::class, 'updateDateField'],
            'manager_id' => [self::class, 'updateManagerField'],
            'source' => [self::class, 'updateSourceField'],
            'comment' => [self::class, 'updateTextField'],
            'phone' => [self::class, 'updatePhoneField'],
            'parent_phone' => [self::class, 'updatePhoneField'],
            'lost_reason' => [self::class, 'updateTextField'],
        ];

        $handler = $handlers[$field] ?? [self::class, 'updateTextField'];

        try {
            return call_user_func($handler, $lid, $field, $value);
        } catch (\Exception $e) {
            Yii::error("LidService::updateField error: {$e->getMessage()}", 'application');
            return [
                'success' => false,
                'message' => 'Ошибка обновления: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Обновление поля статуса
     */
    private static function updateStatusField(Lids $lid, string $field, $value): array
    {
        $newStatus = (int)$value;

        $result = self::changeStatus($lid, $newStatus);

        if ($result['success']) {
            $lid->refresh();
            return [
                'success' => true,
                'message' => $result['message'],
                'value' => $lid->getStatusLabel(),
                'status_label' => $lid->getStatusLabel(),
                'status_color' => $lid->getStatusColor(),
                'pupil_id' => $lid->pupil_id,
                'needs_conversion' => $result['needs_conversion'] ?? false,
            ];
        }

        return [
            'success' => false,
            'message' => $result['message'] ?? 'Невозможно изменить статус',
        ];
    }

    /**
     * Обновление даты
     */
    private static function updateDateField(Lids $lid, string $field, $value): array
    {
        $oldValue = $lid->$field;
        $lid->$field = $value ?: null;

        if ($lid->save(false)) {
            // Записываем в историю, если дата изменилась
            if ($field === 'next_contact_date' && $oldValue !== $value) {
                LidHistory::createFieldChanged($lid, $field, $oldValue, $value);
            }

            return [
                'success' => true,
                'message' => 'Дата обновлена',
                'value' => $value ? DateHelper::format($value, 'd.m.Y') : '',
            ];
        }

        return ['success' => false, 'message' => 'Ошибка сохранения'];
    }

    /**
     * Обновление менеджера
     */
    private static function updateManagerField(Lids $lid, string $field, $value): array
    {
        $oldValue = $lid->manager_id;
        $lid->manager_id = $value ?: null;

        if ($lid->save(false)) {
            // Записываем в историю смены менеджера
            if ($oldValue != $value) {
                $oldManager = $oldValue ? User::findOne($oldValue) : null;
                $newManager = $value ? User::findOne($value) : null;
                LidHistory::createManagerChanged($lid, $oldManager, $newManager);
            }

            $lid->refresh();
            return [
                'success' => true,
                'message' => 'Менеджер назначен',
                'value' => $lid->manager ? $lid->manager->fio : '',
                'manager_name' => $lid->manager ? $lid->manager->fio : null,
            ];
        }

        return ['success' => false, 'message' => 'Ошибка сохранения'];
    }

    /**
     * Обновление источника
     */
    private static function updateSourceField(Lids $lid, string $field, $value): array
    {
        $lid->source = $value;

        if ($lid->save(false)) {
            return [
                'success' => true,
                'message' => 'Источник обновлён',
                'value' => $lid->getSourceLabel(),
                'source_label' => $lid->getSourceLabel(),
            ];
        }

        return ['success' => false, 'message' => 'Ошибка сохранения'];
    }

    /**
     * Обновление телефона
     */
    private static function updatePhoneField(Lids $lid, string $field, $value): array
    {
        // Очищаем телефон от лишних символов
        $cleanPhone = Lids::cleanPhone($value);
        $lid->$field = $cleanPhone;

        if ($lid->save(false)) {
            return [
                'success' => true,
                'message' => 'Телефон обновлён',
                'value' => $cleanPhone,
            ];
        }

        return ['success' => false, 'message' => 'Ошибка сохранения'];
    }

    /**
     * Обновление текстового поля (comment, lost_reason)
     */
    private static function updateTextField(Lids $lid, string $field, $value): array
    {
        $lid->$field = $value;

        if ($lid->save(false)) {
            return [
                'success' => true,
                'message' => 'Сохранено',
                'value' => $value,
            ];
        }

        return ['success' => false, 'message' => 'Ошибка сохранения'];
    }

    /**
     * Получить список разрешённых полей
     *
     * @return array
     */
    public static function getAllowedFields(): array
    {
        return self::ALLOWED_FIELDS;
    }

    // =========================================================================
    // Автоназначение менеджера
    // =========================================================================

    /**
     * Получить следующего менеджера для назначения (round-robin)
     * Выбирает менеджера с наименьшим количеством активных лидов
     *
     * @return int|null ID менеджера или null если нет доступных
     */
    public static function getNextManager(): ?int
    {
        // Проверяем настройку организации
        $org = Organizations::findOne(Organizations::getCurrentOrganizationId());
        if (!$org || !$org->getSetting('auto_assign_leads', true)) {
            return null;
        }

        // Получаем всех менеджеров организации
        $managers = User::find()
            ->joinWith('userOrganizations')
            ->where(['user_organization.organization_id' => Organizations::getCurrentOrganizationId()])
            ->andWhere(['user.is_deleted' => 0])
            ->select(['user.id'])
            ->asArray()
            ->all();

        if (empty($managers)) {
            return null;
        }

        $managerIds = array_column($managers, 'id');

        // Считаем активные лиды для каждого менеджера
        $finalStatuses = Lids::getFinalStatusList();
        $counts = Lids::find()
            ->select(['manager_id', 'COUNT(*) as cnt'])
            ->byOrganization()
            ->notDeleted()
            ->andWhere(['not in', 'status', $finalStatuses])
            ->andWhere(['!=', 'status', Lids::STATUS_IN_TRAINING])
            ->andWhere(['manager_id' => $managerIds])
            ->groupBy('manager_id')
            ->asArray()
            ->all();

        $countMap = [];
        foreach ($counts as $row) {
            $countMap[$row['manager_id']] = (int)$row['cnt'];
        }

        // Находим менеджера с минимальным количеством лидов
        $minCount = PHP_INT_MAX;
        $selectedManager = null;

        foreach ($managerIds as $managerId) {
            $count = $countMap[$managerId] ?? 0;
            if ($count < $minCount) {
                $minCount = $count;
                $selectedManager = $managerId;
            }
        }

        return $selectedManager;
    }

    /**
     * Автоназначение менеджера для лида
     *
     * @param Lids $lid
     * @return bool
     */
    public static function autoAssignManager(Lids $lid): bool
    {
        if ($lid->manager_id) {
            return true; // Уже назначен
        }

        $managerId = self::getNextManager();
        if (!$managerId) {
            return false;
        }

        $lid->manager_id = $managerId;
        return $lid->save(false, ['manager_id']);
    }

    // =========================================================================
    // Время реакции
    // =========================================================================

    /**
     * Записать время первого ответа
     * Вызывается при отправке сообщения менеджером
     *
     * @param Lids $lid
     * @return bool
     */
    public static function recordFirstResponse(Lids $lid): bool
    {
        // Если уже записано - не перезаписываем
        if (!empty($lid->first_response_at)) {
            return true;
        }

        $lid->first_response_at = DateHelper::now();
        return $lid->save(false, ['first_response_at']);
    }

    /**
     * Получить время реакции в минутах
     *
     * @param Lids $lid
     * @return int|null Время в минутах или null если нет данных
     */
    public static function getResponseTimeMinutes(Lids $lid): ?int
    {
        if (empty($lid->first_response_at) || empty($lid->created_at)) {
            return null;
        }

        $created = strtotime($lid->created_at);
        $response = strtotime($lid->first_response_at);

        if ($response <= $created) {
            return 0;
        }

        return (int)round(($response - $created) / 60);
    }

    /**
     * Получить среднее время реакции по менеджерам
     *
     * @param string|null $dateFrom
     * @param string|null $dateTo
     * @return array
     */
    public static function getResponseTimeStats(?string $dateFrom = null, ?string $dateTo = null): array
    {
        $query = Lids::find()
            ->select([
                'manager_id',
                'AVG(TIMESTAMPDIFF(MINUTE, created_at, first_response_at)) as avg_response_minutes',
                'COUNT(*) as total_with_response',
            ])
            ->byOrganization()
            ->notDeleted()
            ->andWhere(['is not', 'first_response_at', null])
            ->andWhere(['is not', 'manager_id', null])
            ->groupBy('manager_id');

        if ($dateFrom) {
            $query->andWhere(['>=', 'created_at', $dateFrom . ' 00:00:00']);
        }
        if ($dateTo) {
            $query->andWhere(['<=', 'created_at', $dateTo . ' 23:59:59']);
        }

        $results = $query->asArray()->all();

        // Добавляем имена менеджеров
        $managerIds = array_column($results, 'manager_id');
        $managers = User::find()
            ->where(['id' => $managerIds])
            ->indexBy('id')
            ->all();

        foreach ($results as &$result) {
            $manager = $managers[$result['manager_id']] ?? null;
            $result['manager_name'] = $manager ? $manager->fio : 'Неизвестный';
            $result['avg_response_minutes'] = (int)$result['avg_response_minutes'];

            // Форматируем в часы:минуты
            $minutes = $result['avg_response_minutes'];
            if ($minutes >= 60) {
                $hours = floor($minutes / 60);
                $mins = $minutes % 60;
                $result['avg_response_formatted'] = "{$hours}ч {$mins}мин";
            } else {
                $result['avg_response_formatted'] = "{$minutes} мин";
            }
        }

        // Сортировка по времени реакции (меньше = лучше)
        usort($results, function($a, $b) {
            return $a['avg_response_minutes'] <=> $b['avg_response_minutes'];
        });

        return $results;
    }
}
