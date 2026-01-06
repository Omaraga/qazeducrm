<?php

use yii\db\Migration;

/**
 * Миграция для оптимизации индексов таблицы lids
 * Критические индексы для multi-tenancy, фильтрации и производительности
 */
class m260107_000000_optimize_lids_indexes extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Проверяем существование таблицы lids
        if ($this->db->getTableSchema('{{%lids}}', true) === null) {
            echo "Таблица lids не существует, пропускаем миграцию\n";
            return true;
        }

        // === КРИТИЧЕСКИЕ ИНДЕКСЫ ДЛЯ lids ===

        // 1. Multi-tenancy индекс (если не существует)
        if (!$this->indexExists('lids', 'idx-lids-organization_id')) {
            $this->createIndex('idx-lids-organization_id', '{{%lids}}', 'organization_id');
        }

        // 2. Composite индекс для soft-delete запросов
        if (!$this->indexExists('lids', 'idx-lids-org-deleted')) {
            $this->createIndex('idx-lids-org-deleted', '{{%lids}}', ['organization_id', 'is_deleted']);
        }

        // 3. Индексы для дедупликации по телефону
        if (!$this->indexExists('lids', 'idx-lids-phone')) {
            $this->createIndex('idx-lids-phone', '{{%lids}}', 'phone');
        }
        if (!$this->indexExists('lids', 'idx-lids-parent_phone')) {
            $this->createIndex('idx-lids-parent_phone', '{{%lids}}', 'parent_phone');
        }

        // 4. Composite индекс для фильтра по статусу (Kanban)
        if (!$this->indexExists('lids', 'idx-lids-org-status-deleted')) {
            $this->createIndex('idx-lids-org-status-deleted', '{{%lids}}', ['organization_id', 'status', 'is_deleted']);
        }

        // 5. Composite индекс для CRM view менеджера
        if (!$this->indexExists('lids', 'idx-lids-org-manager-nextdate')) {
            $this->createIndex('idx-lids-org-manager-nextdate', '{{%lids}}', ['organization_id', 'manager_id', 'next_contact_date']);
        }

        // 6. Индекс для отчётов по дате создания
        if (!$this->indexExists('lids', 'idx-lids-created_at')) {
            $this->createIndex('idx-lids-created_at', '{{%lids}}', 'created_at');
        }

        // 7. Composite индекс для очереди контактов
        if (!$this->indexExists('lids', 'idx-lids-org-nextdate-deleted')) {
            $this->createIndex('idx-lids-org-nextdate-deleted', '{{%lids}}', ['organization_id', 'next_contact_date', 'is_deleted']);
        }

        // === ИНДЕКСЫ ДЛЯ lids_subject_point ===

        if ($this->db->getTableSchema('{{%lids_subject_point}}', true) !== null) {
            if (!$this->indexExists('lids_subject_point', 'idx-lids_subject_point-lid_id')) {
                $this->createIndex('idx-lids_subject_point-lid_id', '{{%lids_subject_point}}', 'lid_id');
            }
            if (!$this->indexExists('lids_subject_point', 'idx-lids_subject_point-subject_id')) {
                $this->createIndex('idx-lids_subject_point-subject_id', '{{%lids_subject_point}}', 'subject_id');
            }
            // Уникальный индекс для пары lid-subject
            if (!$this->indexExists('lids_subject_point', 'ux-lids_subject_point-lid_subject')) {
                try {
                    $this->createIndex('ux-lids_subject_point-lid_subject', '{{%lids_subject_point}}', ['lid_id', 'subject_id'], true);
                } catch (\Exception $e) {
                    echo "Не удалось создать уникальный индекс (возможны дубликаты): " . $e->getMessage() . "\n";
                }
            }
        }

        // === ИНДЕКСЫ ДЛЯ lid_history ===

        if ($this->db->getTableSchema('{{%lid_history}}', true) !== null) {
            if (!$this->indexExists('lid_history', 'idx-lid_history-lid_id')) {
                $this->createIndex('idx-lid_history-lid_id', '{{%lid_history}}', 'lid_id');
            }
            if (!$this->indexExists('lid_history', 'idx-lid_history-created_at')) {
                $this->createIndex('idx-lid_history-created_at', '{{%lid_history}}', 'created_at');
            }
        }

        // === FOREIGN KEYS (опционально, с try-catch) ===

        // FK на organization - пропускаем если уже существует или разные engine
        try {
            if (!$this->fkExists('lids', 'fk-lids-organization_id')) {
                $this->addForeignKey(
                    'fk-lids-organization_id',
                    '{{%lids}}',
                    'organization_id',
                    '{{%organizations}}',
                    'id',
                    'CASCADE',
                    'CASCADE'
                );
            }
        } catch (\Exception $e) {
            echo "FK organization_id: " . $e->getMessage() . " (пропускаем)\n";
        }

        // FK на manager (user) - SET NULL при удалении
        try {
            if (!$this->fkExists('lids', 'fk-lids-manager_id')) {
                $this->addForeignKey(
                    'fk-lids-manager_id',
                    '{{%lids}}',
                    'manager_id',
                    '{{%user}}',
                    'id',
                    'SET NULL',
                    'CASCADE'
                );
            }
        } catch (\Exception $e) {
            echo "FK manager_id: " . $e->getMessage() . " (пропускаем)\n";
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // Удаляем FK
        try {
            $this->dropForeignKey('fk-lids-manager_id', '{{%lids}}');
        } catch (\Exception $e) {}

        try {
            $this->dropForeignKey('fk-lids-organization_id', '{{%lids}}');
        } catch (\Exception $e) {}

        // Удаляем индексы lid_history
        if ($this->db->getTableSchema('{{%lid_history}}', true) !== null) {
            try { $this->dropIndex('idx-lid_history-created_at', '{{%lid_history}}'); } catch (\Exception $e) {}
            try { $this->dropIndex('idx-lid_history-lid_id', '{{%lid_history}}'); } catch (\Exception $e) {}
        }

        // Удаляем индексы lids_subject_point
        if ($this->db->getTableSchema('{{%lids_subject_point}}', true) !== null) {
            try { $this->dropIndex('ux-lids_subject_point-lid_subject', '{{%lids_subject_point}}'); } catch (\Exception $e) {}
            try { $this->dropIndex('idx-lids_subject_point-subject_id', '{{%lids_subject_point}}'); } catch (\Exception $e) {}
            try { $this->dropIndex('idx-lids_subject_point-lid_id', '{{%lids_subject_point}}'); } catch (\Exception $e) {}
        }

        // Удаляем индексы lids
        try { $this->dropIndex('idx-lids-org-nextdate-deleted', '{{%lids}}'); } catch (\Exception $e) {}
        try { $this->dropIndex('idx-lids-created_at', '{{%lids}}'); } catch (\Exception $e) {}
        try { $this->dropIndex('idx-lids-org-manager-nextdate', '{{%lids}}'); } catch (\Exception $e) {}
        try { $this->dropIndex('idx-lids-org-status-deleted', '{{%lids}}'); } catch (\Exception $e) {}
        try { $this->dropIndex('idx-lids-parent_phone', '{{%lids}}'); } catch (\Exception $e) {}
        try { $this->dropIndex('idx-lids-phone', '{{%lids}}'); } catch (\Exception $e) {}
        try { $this->dropIndex('idx-lids-org-deleted', '{{%lids}}'); } catch (\Exception $e) {}
        try { $this->dropIndex('idx-lids-organization_id', '{{%lids}}'); } catch (\Exception $e) {}

        return true;
    }

    /**
     * Проверяет существование индекса
     */
    private function indexExists($table, $indexName)
    {
        $tableSchema = $this->db->getTableSchema('{{%' . $table . '}}', true);
        if ($tableSchema === null) {
            return false;
        }

        try {
            $indexes = $this->db->createCommand("SHOW INDEX FROM {{%$table}} WHERE Key_name = :name")
                ->bindValue(':name', $indexName)
                ->queryAll();
            return !empty($indexes);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Проверяет существование FK
     */
    private function fkExists($table, $fkName)
    {
        try {
            $fks = $this->db->createCommand("
                SELECT CONSTRAINT_NAME
                FROM information_schema.TABLE_CONSTRAINTS
                WHERE TABLE_SCHEMA = DATABASE()
                AND TABLE_NAME = :table
                AND CONSTRAINT_TYPE = 'FOREIGN KEY'
                AND CONSTRAINT_NAME = :name
            ")
                ->bindValue(':table', $this->db->tablePrefix . $table)
                ->bindValue(':name', $fkName)
                ->queryAll();
            return !empty($fks);
        } catch (\Exception $e) {
            return false;
        }
    }
}
