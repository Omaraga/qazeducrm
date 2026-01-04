<?php

use yii\db\Migration;

/**
 * Улучшение воронки лидов:
 * - Добавление статусов воронки
 * - Добавление источника лида
 * - Добавление ответственного менеджера
 */
class m260104_210000_improve_lids_funnel extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Статус лида в воронке
        $this->addColumn('{{%lids}}', 'status', $this->smallInteger()->notNull()->defaultValue(1)->after('comment'));

        // Источник лида
        $this->addColumn('{{%lids}}', 'source', $this->string(50)->null()->after('status'));

        // Ответственный менеджер (user_id)
        $this->addColumn('{{%lids}}', 'manager_id', $this->integer()->null()->after('manager_name'));

        // Дата следующего контакта
        $this->addColumn('{{%lids}}', 'next_contact_date', $this->date()->null()->after('source'));

        // Причина потери (для статуса LOST)
        $this->addColumn('{{%lids}}', 'lost_reason', $this->string(255)->null()->after('next_contact_date'));

        // Индексы
        $this->createIndex('idx-lids-status', '{{%lids}}', 'status');
        $this->createIndex('idx-lids-source', '{{%lids}}', 'source');
        $this->createIndex('idx-lids-manager_id', '{{%lids}}', 'manager_id');
        $this->createIndex('idx-lids-next_contact_date', '{{%lids}}', 'next_contact_date');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex('idx-lids-next_contact_date', '{{%lids}}');
        $this->dropIndex('idx-lids-manager_id', '{{%lids}}');
        $this->dropIndex('idx-lids-source', '{{%lids}}');
        $this->dropIndex('idx-lids-status', '{{%lids}}');

        $this->dropColumn('{{%lids}}', 'lost_reason');
        $this->dropColumn('{{%lids}}', 'next_contact_date');
        $this->dropColumn('{{%lids}}', 'manager_id');
        $this->dropColumn('{{%lids}}', 'source');
        $this->dropColumn('{{%lids}}', 'status');
    }
}
