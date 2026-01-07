<?php

use yii\db\Migration;

/**
 * Добавление индексов для оптимизации модуля групп
 */
class m260107_100001_add_group_module_indexes extends Migration
{
    /**
     * Безопасное создание индекса (игнорирует ошибку если индекс существует или есть дубликаты)
     */
    private function safeCreateIndex($name, $table, $columns, $unique = false)
    {
        try {
            $this->createIndex($name, $table, $columns, $unique);
            echo "    > created index {$name} on {$table}\n";
        } catch (\Exception $e) {
            $msg = $e->getMessage();
            if (strpos($msg, 'Duplicate key name') !== false) {
                echo "    > index {$name} already exists, skipping\n";
            } elseif (strpos($msg, 'Duplicate entry') !== false && $unique) {
                // Если есть дубликаты данных, создаем обычный индекс вместо уникального
                echo "    > WARNING: duplicate data found, creating non-unique index instead\n";
                try {
                    $this->createIndex($name, $table, $columns, false);
                    echo "    > created non-unique index {$name} on {$table}\n";
                } catch (\Exception $e2) {
                    echo "    > failed to create index: " . $e2->getMessage() . "\n";
                }
            } else {
                throw $e;
            }
        }
    }

    /**
     * Безопасное удаление индекса (игнорирует ошибку если индекс не существует)
     */
    private function safeDropIndex($name, $table)
    {
        try {
            $this->dropIndex($name, $table);
            echo "    > dropped index {$name} from {$table}\n";
        } catch (\Exception $e) {
            echo "    > index {$name} does not exist, skipping\n";
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // ========== group ==========
        // Индекс на organization_id для мультитенансности
        $this->safeCreateIndex(
            'idx_group_organization_id',
            'group',
            'organization_id'
        );

        // Индекс на subject_id для JOIN с предметами
        $this->safeCreateIndex(
            'idx_group_subject_id',
            'group',
            'subject_id'
        );

        // Индекс на is_deleted для фильтрации удаленных
        $this->safeCreateIndex(
            'idx_group_is_deleted',
            'group',
            'is_deleted'
        );

        // Индекс на status для фильтрации по статусу
        $this->safeCreateIndex(
            'idx_group_status',
            'group',
            'status'
        );

        // Составной индекс для частых запросов
        $this->safeCreateIndex(
            'idx_group_org_status',
            'group',
            ['organization_id', 'status']
        );

        // ========== teacher_group ==========
        // Индекс на group_id (target_id)
        $this->safeCreateIndex(
            'idx_teacher_group_target_id',
            'teacher_group',
            'target_id'
        );

        // Индекс на related_id (teacher_id)
        $this->safeCreateIndex(
            'idx_teacher_group_related_id',
            'teacher_group',
            'related_id'
        );

        // Индекс на organization_id
        $this->safeCreateIndex(
            'idx_teacher_group_organization_id',
            'teacher_group',
            'organization_id'
        );

        // Уникальный индекс для предотвращения дублирования связей
        $this->safeCreateIndex(
            'idx_teacher_group_unique',
            'teacher_group',
            ['target_id', 'related_id'],
            true // unique
        );

        // ========== typical_schedule ==========
        // Индекс на group_id для быстрого поиска расписания группы
        $this->safeCreateIndex(
            'idx_typical_schedule_group_id',
            'typical_schedule',
            'group_id'
        );

        // Индекс на organization_id
        $this->safeCreateIndex(
            'idx_typical_schedule_organization_id',
            'typical_schedule',
            'organization_id'
        );

        // Индекс на template_id для связи с шаблонами
        $this->safeCreateIndex(
            'idx_typical_schedule_template_id',
            'typical_schedule',
            'template_id'
        );

        // Составной индекс для типовых запросов
        $this->safeCreateIndex(
            'idx_typical_schedule_org_group',
            'typical_schedule',
            ['organization_id', 'group_id']
        );

        // ========== lesson (дополнительные индексы) ==========
        // Составной индекс для запросов по группе и статусу
        $this->safeCreateIndex(
            'idx_lesson_group_status',
            'lesson',
            ['group_id', 'status']
        );

        // Составной индекс для организации и группы
        $this->safeCreateIndex(
            'idx_lesson_org_group',
            'lesson',
            ['organization_id', 'group_id']
        );

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // group
        $this->safeDropIndex('idx_group_organization_id', 'group');
        $this->safeDropIndex('idx_group_subject_id', 'group');
        $this->safeDropIndex('idx_group_is_deleted', 'group');
        $this->safeDropIndex('idx_group_status', 'group');
        $this->safeDropIndex('idx_group_org_status', 'group');

        // teacher_group
        $this->safeDropIndex('idx_teacher_group_target_id', 'teacher_group');
        $this->safeDropIndex('idx_teacher_group_related_id', 'teacher_group');
        $this->safeDropIndex('idx_teacher_group_organization_id', 'teacher_group');
        $this->safeDropIndex('idx_teacher_group_unique', 'teacher_group');

        // typical_schedule
        $this->safeDropIndex('idx_typical_schedule_group_id', 'typical_schedule');
        $this->safeDropIndex('idx_typical_schedule_organization_id', 'typical_schedule');
        $this->safeDropIndex('idx_typical_schedule_template_id', 'typical_schedule');
        $this->safeDropIndex('idx_typical_schedule_org_group', 'typical_schedule');

        // lesson
        $this->safeDropIndex('idx_lesson_group_status', 'lesson');
        $this->safeDropIndex('idx_lesson_org_group', 'lesson');

        return true;
    }
}
