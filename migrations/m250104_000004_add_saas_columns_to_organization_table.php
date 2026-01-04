<?php

use yii\db\Migration;

/**
 * Adds SaaS-related columns to `{{%organization}}` table.
 *
 * Расширяет таблицу организаций для SaaS:
 * - Статус организации (pending, active, suspended, blocked)
 * - Контактные данные (email, верификация)
 * - Юридические данные (БИН, юр. название)
 * - Иерархия филиалов (parent_id, type)
 * - Настройки (timezone, locale, logo)
 */
class m250104_000004_add_saas_columns_to_organization_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Статус организации
        $this->addColumn('{{%organization}}', 'status', $this->string(20)->defaultValue('pending')->comment('pending, active, suspended, blocked')->after('name'));

        // Контактные данные
        $this->addColumn('{{%organization}}', 'email', $this->string(255)->null()->after('phone'));
        $this->addColumn('{{%organization}}', 'email_verified_at', $this->dateTime()->null()->after('email'));
        $this->addColumn('{{%organization}}', 'verification_token', $this->string(64)->null()->after('email_verified_at'));

        // Юридические данные
        $this->addColumn('{{%organization}}', 'bin', $this->string(12)->null()->comment('БИН организации')->after('verification_token'));
        $this->addColumn('{{%organization}}', 'legal_name', $this->string(255)->null()->comment('Юридическое название')->after('bin'));

        // Иерархия филиалов
        $this->addColumn('{{%organization}}', 'parent_id', $this->integer()->null()->comment('ID головной организации (NULL = головная)')->after('id'));
        $this->addColumn('{{%organization}}', 'type', $this->string(10)->defaultValue('head')->comment('head = головная, branch = филиал')->after('parent_id'));

        // Настройки
        $this->addColumn('{{%organization}}', 'logo', $this->string(255)->null()->comment('Путь к логотипу')->after('legal_name'));
        $this->addColumn('{{%organization}}', 'timezone', $this->string(50)->defaultValue('Asia/Almaty')->after('logo'));
        $this->addColumn('{{%organization}}', 'locale', $this->string(10)->defaultValue('ru')->comment('ru, kk')->after('timezone'));

        // Timestamps
        $this->addColumn('{{%organization}}', 'created_at', $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP')->after('is_deleted'));
        $this->addColumn('{{%organization}}', 'updated_at', $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP')->after('created_at'));

        // Внешний ключ для филиалов (self-reference)
        $this->addForeignKey(
            'fk-organization-parent_id',
            '{{%organization}}',
            'parent_id',
            '{{%organization}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        // Индексы
        $this->createIndex('idx-organization-parent_id', '{{%organization}}', 'parent_id');
        $this->createIndex('idx-organization-type', '{{%organization}}', 'type');
        $this->createIndex('idx-organization-status', '{{%organization}}', 'status');
        $this->createIndex('idx-organization-email', '{{%organization}}', 'email');
        $this->createIndex('idx-organization-bin', '{{%organization}}', 'bin');

        // Обновляем существующие организации
        $this->update('{{%organization}}', [
            'status' => 'active',
            'type' => 'head',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // Удаляем индексы
        $this->dropIndex('idx-organization-parent_id', '{{%organization}}');
        $this->dropIndex('idx-organization-type', '{{%organization}}');
        $this->dropIndex('idx-organization-status', '{{%organization}}');
        $this->dropIndex('idx-organization-email', '{{%organization}}');
        $this->dropIndex('idx-organization-bin', '{{%organization}}');

        // Удаляем внешний ключ
        $this->dropForeignKey('fk-organization-parent_id', '{{%organization}}');

        // Удаляем колонки
        $this->dropColumn('{{%organization}}', 'updated_at');
        $this->dropColumn('{{%organization}}', 'created_at');
        $this->dropColumn('{{%organization}}', 'locale');
        $this->dropColumn('{{%organization}}', 'timezone');
        $this->dropColumn('{{%organization}}', 'logo');
        $this->dropColumn('{{%organization}}', 'type');
        $this->dropColumn('{{%organization}}', 'parent_id');
        $this->dropColumn('{{%organization}}', 'legal_name');
        $this->dropColumn('{{%organization}}', 'bin');
        $this->dropColumn('{{%organization}}', 'verification_token');
        $this->dropColumn('{{%organization}}', 'email_verified_at');
        $this->dropColumn('{{%organization}}', 'email');
        $this->dropColumn('{{%organization}}', 'status');
    }
}
