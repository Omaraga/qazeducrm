<?php

use yii\db\Migration;

/**
 * Создание таблицы пробных занятий
 */
class m260111_000001_create_trial_lesson_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%trial_lesson}}', [
            'id' => $this->primaryKey(),
            'lid_id' => $this->integer()->notNull()->comment('ID лида'),
            'group_id' => $this->integer()->comment('ID группы'),
            'date' => $this->date()->notNull()->comment('Дата пробного'),
            'time' => $this->time()->notNull()->comment('Время начала'),
            'status' => $this->tinyInteger()->notNull()->defaultValue(1)->comment('Статус: 1-план, 2-подтв, 3-провед, 4-не пришел, 5-отменено, 6-конверт'),
            'feedback' => $this->string(1000)->comment('Отзыв/комментарий'),
            'rating' => $this->tinyInteger()->comment('Оценка 1-5'),
            'reminder_sent' => $this->dateTime()->comment('Дата отправки напоминания'),
            'converted_pupil_id' => $this->integer()->comment('ID ученика после конвертации'),
            'organization_id' => $this->integer()->notNull(),
            'is_deleted' => $this->tinyInteger()->notNull()->defaultValue(0),
            'created_at' => $this->dateTime()->notNull()->defaultExpression('NOW()'),
            'updated_at' => $this->dateTime()->notNull()->defaultExpression('NOW()'),
        ]);

        // Индексы
        $this->createIndex('idx-trial_lesson-lid_id', '{{%trial_lesson}}', 'lid_id');
        $this->createIndex('idx-trial_lesson-group_id', '{{%trial_lesson}}', 'group_id');
        $this->createIndex('idx-trial_lesson-date', '{{%trial_lesson}}', 'date');
        $this->createIndex('idx-trial_lesson-status', '{{%trial_lesson}}', 'status');
        $this->createIndex('idx-trial_lesson-organization_id', '{{%trial_lesson}}', 'organization_id');
        $this->createIndex('idx-trial_lesson-is_deleted', '{{%trial_lesson}}', 'is_deleted');

        // Внешние ключи
        $this->addForeignKey(
            'fk-trial_lesson-lid_id',
            '{{%trial_lesson}}',
            'lid_id',
            '{{%lids}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-trial_lesson-group_id',
            '{{%trial_lesson}}',
            'group_id',
            '{{%group}}',
            'id',
            'SET NULL',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-trial_lesson-converted_pupil_id',
            '{{%trial_lesson}}',
            'converted_pupil_id',
            '{{%pupil}}',
            'id',
            'SET NULL',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-trial_lesson-organization_id',
            '{{%trial_lesson}}',
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
        $this->dropForeignKey('fk-trial_lesson-organization_id', '{{%trial_lesson}}');
        $this->dropForeignKey('fk-trial_lesson-converted_pupil_id', '{{%trial_lesson}}');
        $this->dropForeignKey('fk-trial_lesson-group_id', '{{%trial_lesson}}');
        $this->dropForeignKey('fk-trial_lesson-lid_id', '{{%trial_lesson}}');
        $this->dropTable('{{%trial_lesson}}');
    }
}
