<?php

use yii\db\Migration;

/**
 * Миграция для создания таблицы логов impersonate (вход под другим пользователем)
 */
class m260108_000001_create_impersonate_log_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Если таблица уже существует - удаляем
        $this->execute('DROP TABLE IF EXISTS {{%impersonate_log}}');

        $this->createTable('{{%impersonate_log}}', [
            'id' => $this->primaryKey(),
            'admin_user_id' => $this->integer()->notNull()->comment('ID администратора'),
            'target_user_id' => $this->integer()->notNull()->comment('ID целевого пользователя'),
            'organization_id' => $this->integer()->comment('ID организации'),
            'action' => $this->string(20)->notNull()->comment('start/end'),
            'ip_address' => $this->string(45)->comment('IP адрес'),
            'user_agent' => $this->text()->comment('User Agent браузера'),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
        ]);

        // Индексы для быстрого поиска
        $this->createIndex(
            'idx-impersonate_log-admin_user_id',
            '{{%impersonate_log}}',
            'admin_user_id'
        );

        $this->createIndex(
            'idx-impersonate_log-target_user_id',
            '{{%impersonate_log}}',
            'target_user_id'
        );

        $this->createIndex(
            'idx-impersonate_log-organization_id',
            '{{%impersonate_log}}',
            'organization_id'
        );

        $this->createIndex(
            'idx-impersonate_log-created_at',
            '{{%impersonate_log}}',
            'created_at'
        );

        // Примечание: внешние ключи не добавляются из-за различий в типах колонок id
        // Целостность данных обеспечивается на уровне приложения
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%impersonate_log}}');
    }
}
