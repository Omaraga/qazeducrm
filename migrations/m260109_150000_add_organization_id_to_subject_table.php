<?php

use yii\db\Migration;

/**
 * Migration to add organization_id column to subject table.
 * This makes subjects organization-specific instead of global.
 */
class m260109_150000_add_organization_id_to_subject_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Add organization_id column
        $this->addColumn('{{%subject}}', 'organization_id', $this->integer()->null()->after('id'));

        // Add foreign key
        $this->addForeignKey(
            'fk-subject-organization_id',
            '{{%subject}}',
            'organization_id',
            '{{%organization}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        // Add index for faster queries
        $this->createIndex(
            'idx-subject-organization_id',
            '{{%subject}}',
            'organization_id'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // Remove foreign key first
        $this->dropForeignKey('fk-subject-organization_id', '{{%subject}}');

        // Remove index
        $this->dropIndex('idx-subject-organization_id', '{{%subject}}');

        // Remove column
        $this->dropColumn('{{%subject}}', 'organization_id');
    }
}
