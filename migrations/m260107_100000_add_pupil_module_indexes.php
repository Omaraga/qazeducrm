<?php

use yii\db\Migration;

/**
 * Добавление индексов для оптимизации модуля учеников
 */
class m260107_100000_add_pupil_module_indexes extends Migration
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
        // ========== pupil ==========
        // Индекс на organization_id для мультитенансности
        $this->safeCreateIndex(
            'idx_pupil_organization_id',
            'pupil',
            'organization_id'
        );

        // Индекс на is_deleted для фильтрации удаленных
        $this->safeCreateIndex(
            'idx_pupil_is_deleted',
            'pupil',
            'is_deleted'
        );

        // Уникальный составной индекс для IIN в пределах организации
        // Сначала удалим существующий уникальный индекс на iin если есть
        $this->safeDropIndex('iin', 'pupil');

        $this->safeCreateIndex(
            'idx_pupil_organization_iin',
            'pupil',
            ['organization_id', 'iin'],
            true // unique
        );

        // ========== pupil_education ==========
        // Индекс на pupil_id для быстрого поиска обучений ученика
        $this->safeCreateIndex(
            'idx_pupil_education_pupil_id',
            'pupil_education',
            'pupil_id'
        );

        // Индекс на organization_id
        $this->safeCreateIndex(
            'idx_pupil_education_organization_id',
            'pupil_education',
            'organization_id'
        );

        // Составной индекс для частых запросов
        $this->safeCreateIndex(
            'idx_pupil_education_org_deleted',
            'pupil_education',
            ['organization_id', 'is_deleted']
        );

        // ========== education_group ==========
        // Индекс на education_id
        $this->safeCreateIndex(
            'idx_education_group_education_id',
            'education_group',
            'education_id'
        );

        // Индекс на group_id
        $this->safeCreateIndex(
            'idx_education_group_group_id',
            'education_group',
            'group_id'
        );

        // Индекс на pupil_id
        $this->safeCreateIndex(
            'idx_education_group_pupil_id',
            'education_group',
            'pupil_id'
        );

        // Индекс на organization_id
        $this->safeCreateIndex(
            'idx_education_group_organization_id',
            'education_group',
            'organization_id'
        );

        // ========== payment ==========
        // Индекс на pupil_id для быстрого поиска платежей ученика
        $this->safeCreateIndex(
            'idx_payment_pupil_id',
            'payment',
            'pupil_id'
        );

        // Индекс на organization_id
        $this->safeCreateIndex(
            'idx_payment_organization_id',
            'payment',
            'organization_id'
        );

        // Индекс на type для фильтрации по типу платежа
        $this->safeCreateIndex(
            'idx_payment_type',
            'payment',
            'type'
        );

        // Индекс на date для диапазонных запросов
        $this->safeCreateIndex(
            'idx_payment_date',
            'payment',
            'date'
        );

        // Составной индекс для агрегаций в DashboardService
        $this->safeCreateIndex(
            'idx_payment_org_date_type',
            'payment',
            ['organization_id', 'date', 'type']
        );

        // ========== lesson ==========
        // Индекс на organization_id
        $this->safeCreateIndex(
            'idx_lesson_organization_id',
            'lesson',
            'organization_id'
        );

        // Индекс на group_id
        $this->safeCreateIndex(
            'idx_lesson_group_id',
            'lesson',
            'group_id'
        );

        // Индекс на teacher_id
        $this->safeCreateIndex(
            'idx_lesson_teacher_id',
            'lesson',
            'teacher_id'
        );

        // Индекс на date для поиска по дате
        $this->safeCreateIndex(
            'idx_lesson_date',
            'lesson',
            'date'
        );

        // Составной индекс для частых запросов
        $this->safeCreateIndex(
            'idx_lesson_org_date',
            'lesson',
            ['organization_id', 'date']
        );

        // ========== lesson_attendance ==========
        // Индекс на pupil_id
        $this->safeCreateIndex(
            'idx_lesson_attendance_pupil_id',
            'lesson_attendance',
            'pupil_id'
        );

        // Индекс на lesson_id
        $this->safeCreateIndex(
            'idx_lesson_attendance_lesson_id',
            'lesson_attendance',
            'lesson_id'
        );

        // Индекс на organization_id
        $this->safeCreateIndex(
            'idx_lesson_attendance_organization_id',
            'lesson_attendance',
            'organization_id'
        );

        // Уникальный индекс для предотвращения дублирования посещаемости
        $this->safeCreateIndex(
            'idx_lesson_attendance_lesson_pupil',
            'lesson_attendance',
            ['lesson_id', 'pupil_id'],
            true // unique
        );

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // pupil
        $this->safeDropIndex('idx_pupil_organization_id', 'pupil');
        $this->safeDropIndex('idx_pupil_is_deleted', 'pupil');
        $this->safeDropIndex('idx_pupil_organization_iin', 'pupil');

        // pupil_education
        $this->safeDropIndex('idx_pupil_education_pupil_id', 'pupil_education');
        $this->safeDropIndex('idx_pupil_education_organization_id', 'pupil_education');
        $this->safeDropIndex('idx_pupil_education_org_deleted', 'pupil_education');

        // education_group
        $this->safeDropIndex('idx_education_group_education_id', 'education_group');
        $this->safeDropIndex('idx_education_group_group_id', 'education_group');
        $this->safeDropIndex('idx_education_group_pupil_id', 'education_group');
        $this->safeDropIndex('idx_education_group_organization_id', 'education_group');

        // payment
        $this->safeDropIndex('idx_payment_pupil_id', 'payment');
        $this->safeDropIndex('idx_payment_organization_id', 'payment');
        $this->safeDropIndex('idx_payment_type', 'payment');
        $this->safeDropIndex('idx_payment_date', 'payment');
        $this->safeDropIndex('idx_payment_org_date_type', 'payment');

        // lesson
        $this->safeDropIndex('idx_lesson_organization_id', 'lesson');
        $this->safeDropIndex('idx_lesson_group_id', 'lesson');
        $this->safeDropIndex('idx_lesson_teacher_id', 'lesson');
        $this->safeDropIndex('idx_lesson_date', 'lesson');
        $this->safeDropIndex('idx_lesson_org_date', 'lesson');

        // lesson_attendance
        $this->safeDropIndex('idx_lesson_attendance_pupil_id', 'lesson_attendance');
        $this->safeDropIndex('idx_lesson_attendance_lesson_id', 'lesson_attendance');
        $this->safeDropIndex('idx_lesson_attendance_organization_id', 'lesson_attendance');
        $this->safeDropIndex('idx_lesson_attendance_lesson_pupil', 'lesson_attendance');

        // Восстановить оригинальный индекс на iin
        $this->safeCreateIndex('iin', 'pupil', 'iin', true);

        return true;
    }
}
