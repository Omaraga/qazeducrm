<?php

use yii\db\Migration;

/**
 * Добавляет поле first_response_at в таблицу lids для отслеживания времени реакции
 */
class m260110_100000_add_first_response_at_to_lids extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Добавляем поле для времени первого ответа
        $this->addColumn('{{%lids}}', 'first_response_at', $this->dateTime()->null()->after('status_changed_at'));

        // Добавляем индекс для быстрого поиска по времени реакции
        $this->createIndex(
            'idx-lids-first_response_at',
            '{{%lids}}',
            'first_response_at'
        );

        // Добавляем составной индекс для статистики по менеджерам
        $this->createIndex(
            'idx-lids-manager_response',
            '{{%lids}}',
            ['manager_id', 'first_response_at', 'created_at']
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex('idx-lids-manager_response', '{{%lids}}');
        $this->dropIndex('idx-lids-first_response_at', '{{%lids}}');
        $this->dropColumn('{{%lids}}', 'first_response_at');
    }
}
