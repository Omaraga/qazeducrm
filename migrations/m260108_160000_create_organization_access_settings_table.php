<?php

use yii\db\Migration;

/**
 * Таблица настроек доступа для организации.
 * Хранит гибкие настройки прав доступа по ролям.
 */
class m260108_160000_create_organization_access_settings_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%organization_access_settings}}', [
            'id' => $this->primaryKey(),
            'organization_id' => $this->integer(11)->notNull()->unique(),
            'settings' => $this->json()->notNull()->comment('JSON с настройками доступа'),
            'updated_by' => $this->integer(11),
            'created_at' => $this->timestamp()->notNull()->defaultValue(new \yii\db\Expression('NOW()')),
            'updated_at' => $this->timestamp()->notNull()->defaultValue(new \yii\db\Expression('NOW()')),
        ]);

        $this->addForeignKey(
            'fk-organization_access_settings-organization_id',
            'organization_access_settings',
            'organization_id',
            'organization',
            'id',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-organization_access_settings-updated_by',
            'organization_access_settings',
            'updated_by',
            'user',
            'id',
            'SET NULL'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-organization_access_settings-updated_by', 'organization_access_settings');
        $this->dropForeignKey('fk-organization_access_settings-organization_id', 'organization_access_settings');
        $this->dropTable('{{%organization_access_settings}}');
    }
}
