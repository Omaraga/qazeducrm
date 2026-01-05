<?php

use yii\db\Migration;

/**
 * Миграция существующих типовых расписаний в шаблоны
 * Для каждой организации создаётся шаблон "Основное расписание"
 */
class m260105_000003_migrate_typical_schedule_to_templates extends Migration
{
    public function safeUp()
    {
        // Получаем все организации с типовыми расписаниями
        $organizations = (new \yii\db\Query())
            ->select('organization_id')
            ->distinct()
            ->from('{{%typical_schedule}}')
            ->where(['is_deleted' => 0])
            ->andWhere(['IS NOT', 'organization_id', null])
            ->column();

        foreach ($organizations as $orgId) {
            // Создаем шаблон "Основное расписание" для каждой организации
            $this->insert('{{%schedule_template}}', [
                'organization_id' => $orgId,
                'name' => 'Основное расписание',
                'description' => 'Автоматически создан при миграции',
                'is_default' => 1,
                'is_active' => 1,
                'is_deleted' => 0,
            ]);

            $templateId = $this->db->getLastInsertID();

            // Привязываем все типовые занятия к этому шаблону
            $this->update(
                '{{%typical_schedule}}',
                ['template_id' => $templateId],
                ['organization_id' => $orgId, 'is_deleted' => 0]
            );
        }
    }

    public function safeDown()
    {
        // Отвязываем все занятия от шаблонов
        $this->update('{{%typical_schedule}}', ['template_id' => null], []);

        // Удаляем все автоматически созданные шаблоны
        $this->delete('{{%schedule_template}}', ['description' => 'Автоматически создан при миграции']);
    }
}
