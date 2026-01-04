<?php

use yii\db\Migration;

/**
 * Миграция для создания таблиц teacher_rate и teacher_salary
 *
 * teacher_rate - ставки учителей (за ученика или за урок)
 * teacher_salary - начисленные зарплаты учителям
 */
class m260104_200000_create_teacher_salary_tables extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Таблица ставок учителей
        $this->createTable('{{%teacher_rate}}', [
            'id' => $this->primaryKey(),
            'organization_id' => $this->integer()->notNull(),
            'teacher_id' => $this->integer()->notNull(),
            'subject_id' => $this->integer()->null()->comment('NULL = для всех предметов'),
            'group_id' => $this->integer()->null()->comment('NULL = для всех групп'),
            'rate_type' => $this->smallInteger()->notNull()->defaultValue(1)->comment('1=за ученика, 2=за урок, 3=процент'),
            'rate_value' => $this->decimal(10, 2)->notNull()->defaultValue(0)->comment('Сумма или процент'),
            'is_active' => $this->boolean()->notNull()->defaultValue(true),
            'is_deleted' => $this->smallInteger()->notNull()->defaultValue(0),
            'info' => $this->text()->null(),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ]);

        $this->createIndex('idx-teacher_rate-organization', '{{%teacher_rate}}', 'organization_id');
        $this->createIndex('idx-teacher_rate-teacher', '{{%teacher_rate}}', 'teacher_id');
        $this->createIndex('idx-teacher_rate-subject', '{{%teacher_rate}}', 'subject_id');
        $this->createIndex('idx-teacher_rate-group', '{{%teacher_rate}}', 'group_id');

        $this->addForeignKey(
            'fk-teacher_rate-organization',
            '{{%teacher_rate}}',
            'organization_id',
            '{{%organization}}',
            'id',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-teacher_rate-teacher',
            '{{%teacher_rate}}',
            'teacher_id',
            '{{%user}}',
            'id',
            'CASCADE'
        );

        // Таблица начисленных зарплат
        $this->createTable('{{%teacher_salary}}', [
            'id' => $this->primaryKey(),
            'organization_id' => $this->integer()->notNull(),
            'teacher_id' => $this->integer()->notNull(),
            'period_start' => $this->date()->notNull()->comment('Начало периода'),
            'period_end' => $this->date()->notNull()->comment('Конец периода'),
            'lessons_count' => $this->integer()->notNull()->defaultValue(0)->comment('Количество уроков'),
            'students_count' => $this->integer()->notNull()->defaultValue(0)->comment('Количество учеников с оплатой'),
            'base_amount' => $this->decimal(12, 2)->notNull()->defaultValue(0)->comment('Базовая сумма'),
            'bonus_amount' => $this->decimal(12, 2)->notNull()->defaultValue(0)->comment('Бонусы'),
            'deduction_amount' => $this->decimal(12, 2)->notNull()->defaultValue(0)->comment('Вычеты'),
            'total_amount' => $this->decimal(12, 2)->notNull()->defaultValue(0)->comment('Итого'),
            'status' => $this->smallInteger()->notNull()->defaultValue(1)->comment('1=draft, 2=approved, 3=paid'),
            'approved_by' => $this->integer()->null()->comment('Кто утвердил'),
            'approved_at' => $this->timestamp()->null(),
            'paid_at' => $this->timestamp()->null(),
            'notes' => $this->text()->null(),
            'details' => $this->text()->null()->comment('JSON с деталями расчёта'),
            'is_deleted' => $this->smallInteger()->notNull()->defaultValue(0),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ]);

        $this->createIndex('idx-teacher_salary-organization', '{{%teacher_salary}}', 'organization_id');
        $this->createIndex('idx-teacher_salary-teacher', '{{%teacher_salary}}', 'teacher_id');
        $this->createIndex('idx-teacher_salary-period', '{{%teacher_salary}}', ['period_start', 'period_end']);
        $this->createIndex('idx-teacher_salary-status', '{{%teacher_salary}}', 'status');

        $this->addForeignKey(
            'fk-teacher_salary-organization',
            '{{%teacher_salary}}',
            'organization_id',
            '{{%organization}}',
            'id',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-teacher_salary-teacher',
            '{{%teacher_salary}}',
            'teacher_id',
            '{{%user}}',
            'id',
            'CASCADE'
        );

        // Таблица для детализации - связь зарплаты с посещениями
        $this->createTable('{{%teacher_salary_detail}}', [
            'id' => $this->primaryKey(),
            'salary_id' => $this->integer()->notNull(),
            'lesson_id' => $this->integer()->notNull(),
            'attendance_id' => $this->integer()->null(),
            'group_id' => $this->integer()->null(),
            'subject_id' => $this->integer()->null(),
            'students_paid' => $this->integer()->notNull()->defaultValue(0),
            'rate_type' => $this->smallInteger()->notNull(),
            'rate_value' => $this->decimal(10, 2)->notNull(),
            'amount' => $this->decimal(10, 2)->notNull(),
            'lesson_date' => $this->date()->notNull(),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
        ]);

        $this->createIndex('idx-teacher_salary_detail-salary', '{{%teacher_salary_detail}}', 'salary_id');
        $this->createIndex('idx-teacher_salary_detail-lesson', '{{%teacher_salary_detail}}', 'lesson_id');

        $this->addForeignKey(
            'fk-teacher_salary_detail-salary',
            '{{%teacher_salary_detail}}',
            'salary_id',
            '{{%teacher_salary}}',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-teacher_salary_detail-salary', '{{%teacher_salary_detail}}');
        $this->dropTable('{{%teacher_salary_detail}}');

        $this->dropForeignKey('fk-teacher_salary-teacher', '{{%teacher_salary}}');
        $this->dropForeignKey('fk-teacher_salary-organization', '{{%teacher_salary}}');
        $this->dropTable('{{%teacher_salary}}');

        $this->dropForeignKey('fk-teacher_rate-teacher', '{{%teacher_rate}}');
        $this->dropForeignKey('fk-teacher_rate-organization', '{{%teacher_rate}}');
        $this->dropTable('{{%teacher_rate}}');
    }
}
