<?php

use yii\db\Migration;

/**
 * Расширение таблицы лидов:
 * - Добавление данных родителя
 * - Связь с учеником
 * - Отслеживание конверсии
 */
class m260106_100000_extend_lids_parent_data extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Данные родителя
        $this->addColumn('{{%lids}}', 'parent_fio', $this->string(255)->null()->after('school'));
        $this->addColumn('{{%lids}}', 'parent_phone', $this->string(50)->null()->after('parent_fio'));

        // Контактное лицо (родитель или ребёнок)
        $this->addColumn('{{%lids}}', 'contact_person', $this->string(10)->notNull()->defaultValue('parent')->after('parent_phone'));

        // Связь с учеником (после конверсии)
        $this->addColumn('{{%lids}}', 'pupil_id', $this->integer()->null()->after('contact_person'));

        // Дата конверсии в ученика
        $this->addColumn('{{%lids}}', 'converted_at', $this->timestamp()->null()->after('pupil_id'));

        // Дата последней смены статуса
        $this->addColumn('{{%lids}}', 'status_changed_at', $this->timestamp()->null()->after('converted_at'));

        // Индексы
        $this->createIndex('idx-lids-pupil_id', '{{%lids}}', 'pupil_id');
        $this->createIndex('idx-lids-contact_person', '{{%lids}}', 'contact_person');
        $this->createIndex('idx-lids-converted_at', '{{%lids}}', 'converted_at');
        $this->createIndex('idx-lids-status_changed_at', '{{%lids}}', 'status_changed_at');

        // Внешний ключ на таблицу учеников
        $this->addForeignKey(
            'fk-lids-pupil_id',
            '{{%lids}}',
            'pupil_id',
            '{{%pupil}}',
            'id',
            'SET NULL',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-lids-pupil_id', '{{%lids}}');

        $this->dropIndex('idx-lids-status_changed_at', '{{%lids}}');
        $this->dropIndex('idx-lids-converted_at', '{{%lids}}');
        $this->dropIndex('idx-lids-contact_person', '{{%lids}}');
        $this->dropIndex('idx-lids-pupil_id', '{{%lids}}');

        $this->dropColumn('{{%lids}}', 'status_changed_at');
        $this->dropColumn('{{%lids}}', 'converted_at');
        $this->dropColumn('{{%lids}}', 'pupil_id');
        $this->dropColumn('{{%lids}}', 'contact_person');
        $this->dropColumn('{{%lids}}', 'parent_phone');
        $this->dropColumn('{{%lids}}', 'parent_fio');
    }
}
