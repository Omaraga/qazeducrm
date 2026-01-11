<?php

use yii\db\Migration;

/**
 * Создание таблиц для домашних заданий
 */
class m260111_000002_create_homework_tables extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Таблица домашних заданий
        $this->createTable('{{%homework}}', [
            'id' => $this->primaryKey(),
            'group_id' => $this->integer()->notNull()->comment('ID группы'),
            'lesson_id' => $this->integer()->comment('ID урока'),
            'title' => $this->string(255)->notNull()->comment('Название'),
            'description' => $this->text()->comment('Описание задания'),
            'due_date' => $this->date()->notNull()->comment('Срок сдачи'),
            'attachments' => $this->text()->comment('JSON прикреплённых файлов'),
            'status' => $this->tinyInteger()->notNull()->defaultValue(1)->comment('Статус: 0-черновик, 1-активно, 2-закрыто, 3-архив'),
            'created_by' => $this->integer()->comment('ID создателя'),
            'organization_id' => $this->integer()->notNull(),
            'is_deleted' => $this->tinyInteger()->notNull()->defaultValue(0),
            'created_at' => $this->dateTime()->notNull()->defaultExpression('NOW()'),
            'updated_at' => $this->dateTime()->notNull()->defaultExpression('NOW()'),
        ]);

        // Индексы для homework
        $this->createIndex('idx-homework-group_id', '{{%homework}}', 'group_id');
        $this->createIndex('idx-homework-lesson_id', '{{%homework}}', 'lesson_id');
        $this->createIndex('idx-homework-due_date', '{{%homework}}', 'due_date');
        $this->createIndex('idx-homework-status', '{{%homework}}', 'status');
        $this->createIndex('idx-homework-organization_id', '{{%homework}}', 'organization_id');
        $this->createIndex('idx-homework-is_deleted', '{{%homework}}', 'is_deleted');

        // Внешние ключи для homework
        $this->addForeignKey(
            'fk-homework-group_id',
            '{{%homework}}',
            'group_id',
            '{{%group}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-homework-lesson_id',
            '{{%homework}}',
            'lesson_id',
            '{{%lesson}}',
            'id',
            'SET NULL',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-homework-created_by',
            '{{%homework}}',
            'created_by',
            '{{%user}}',
            'id',
            'SET NULL',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-homework-organization_id',
            '{{%homework}}',
            'organization_id',
            '{{%organization}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        // Таблица ответов на домашние задания
        $this->createTable('{{%homework_submission}}', [
            'id' => $this->primaryKey(),
            'homework_id' => $this->integer()->notNull()->comment('ID задания'),
            'pupil_id' => $this->integer()->notNull()->comment('ID ученика'),
            'status' => $this->tinyInteger()->notNull()->defaultValue(0)->comment('Статус: 0-не сдано, 1-сдано, 2-проверено, 3-на доработку'),
            'answer' => $this->text()->comment('Текст ответа'),
            'files' => $this->text()->comment('JSON файлов ученика'),
            'submitted_at' => $this->dateTime()->comment('Дата сдачи'),
            'grade' => $this->tinyInteger()->comment('Оценка 1-10'),
            'comment' => $this->text()->comment('Комментарий учителя'),
            'checked_by' => $this->integer()->comment('ID проверившего'),
            'checked_at' => $this->dateTime()->comment('Дата проверки'),
            'organization_id' => $this->integer()->notNull(),
            'created_at' => $this->dateTime()->notNull()->defaultExpression('NOW()'),
            'updated_at' => $this->dateTime()->notNull()->defaultExpression('NOW()'),
        ]);

        // Индексы для homework_submission
        $this->createIndex('idx-homework_submission-homework_id', '{{%homework_submission}}', 'homework_id');
        $this->createIndex('idx-homework_submission-pupil_id', '{{%homework_submission}}', 'pupil_id');
        $this->createIndex('idx-homework_submission-status', '{{%homework_submission}}', 'status');
        $this->createIndex('idx-homework_submission-organization_id', '{{%homework_submission}}', 'organization_id');
        // Уникальный индекс: один ответ на задание от ученика
        $this->createIndex('idx-homework_submission-unique', '{{%homework_submission}}', ['homework_id', 'pupil_id'], true);

        // Внешние ключи для homework_submission
        $this->addForeignKey(
            'fk-homework_submission-homework_id',
            '{{%homework_submission}}',
            'homework_id',
            '{{%homework}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-homework_submission-pupil_id',
            '{{%homework_submission}}',
            'pupil_id',
            '{{%pupil}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-homework_submission-checked_by',
            '{{%homework_submission}}',
            'checked_by',
            '{{%user}}',
            'id',
            'SET NULL',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-homework_submission-organization_id',
            '{{%homework_submission}}',
            'organization_id',
            '{{%organization}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // Удаляем homework_submission
        $this->dropForeignKey('fk-homework_submission-organization_id', '{{%homework_submission}}');
        $this->dropForeignKey('fk-homework_submission-checked_by', '{{%homework_submission}}');
        $this->dropForeignKey('fk-homework_submission-pupil_id', '{{%homework_submission}}');
        $this->dropForeignKey('fk-homework_submission-homework_id', '{{%homework_submission}}');
        $this->dropTable('{{%homework_submission}}');

        // Удаляем homework
        $this->dropForeignKey('fk-homework-organization_id', '{{%homework}}');
        $this->dropForeignKey('fk-homework-created_by', '{{%homework}}');
        $this->dropForeignKey('fk-homework-lesson_id', '{{%homework}}');
        $this->dropForeignKey('fk-homework-group_id', '{{%homework}}');
        $this->dropTable('{{%homework}}');
    }
}
