<?php

use yii\db\Migration;

/**
 * Обновление тарифных планов с новой структурой цен и лимитов.
 *
 * Новая структура:
 * - FREE: 10 учеников, 2 учителя, 3 группы (бесплатно)
 * - СТАНДАРТ (basic): 50 учеников, 5 учителей, 15 групп (9,990 KZT/мес)
 * - БИЗНЕС (pro): 150 учеников, 15 учителей, 40 групп (29,990 KZT/мес)
 * - ПРЕМИУМ (enterprise): Безлимит (79,990 KZT/мес)
 */
class m250107_000003_update_saas_plan_data extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Обновляем FREE план
        $this->update('{{%saas_plan}}', [
            'name' => 'FREE',
            'description' => 'Бесплатный план для тестирования. Идеален для репетиторов и небольших групп.',
            'max_pupils' => 10,
            'max_teachers' => 2,
            'max_groups' => 3,
            'max_admins' => 1,
            'max_branches' => 0, // Нет филиалов
            'price_monthly' => 0,
            'price_yearly' => 0,
            'trial_days' => 14,
            'features' => json_encode([
                'pupils' => true,
                'groups' => true,
                'schedule' => true,
                'attendance' => true,
            ]),
        ], ['code' => 'free']);

        // Обновляем BASIC -> СТАНДАРТ
        $this->update('{{%saas_plan}}', [
            'name' => 'Стандарт',
            'description' => 'Для мини-центров и репетиторов. CRM, SMS, зарплата учителей.',
            'max_pupils' => 50,
            'max_teachers' => 5,
            'max_groups' => 15,
            'max_admins' => 2,
            'max_branches' => 0, // Нет филиалов
            'price_monthly' => 9990,
            'price_yearly' => 99900, // 10 месяцев
            'trial_days' => 14,
            'features' => json_encode([
                'pupils' => true,
                'groups' => true,
                'schedule' => true,
                'attendance' => true,
                'crm_lids' => true,
                'sms_notifications' => true,
                'sms_monthly_limit' => 50,
                'salary' => true,
                'reports_export' => false,
            ]),
        ], ['code' => 'basic']);

        // Обновляем PRO -> БИЗНЕС
        $this->update('{{%saas_plan}}', [
            'name' => 'Бизнес',
            'description' => 'Для учебных центров. 1 филиал, расширенные отчёты, API.',
            'max_pupils' => 150,
            'max_teachers' => 15,
            'max_groups' => 40,
            'max_admins' => 3,
            'max_branches' => 1,
            'price_monthly' => 29990,
            'price_yearly' => 299900, // 10 месяцев
            'trial_days' => 14,
            'features' => json_encode([
                'pupils' => true,
                'groups' => true,
                'schedule' => true,
                'attendance' => true,
                'crm_lids' => true,
                'crm_analytics' => true,
                'sms_notifications' => true,
                'sms_monthly_limit' => 200,
                'salary' => true,
                'reports_export' => true,
                'knowledge_base' => true,
                'api_access' => true,
                'branches' => 1,
            ]),
        ], ['code' => 'pro']);

        // Обновляем ENTERPRISE -> ПРЕМИУМ
        $this->update('{{%saas_plan}}', [
            'name' => 'Премиум',
            'description' => 'Для сетей центров. Безлимит на всё, приоритетная поддержка.',
            'max_pupils' => 0, // Безлимит
            'max_teachers' => 0, // Безлимит
            'max_groups' => 0, // Безлимит
            'max_admins' => 0, // Безлимит
            'max_branches' => 0, // Безлимит
            'price_monthly' => 79990,
            'price_yearly' => 799900, // 10 месяцев
            'trial_days' => 30,
            'features' => json_encode([
                'pupils' => true,
                'groups' => true,
                'schedule' => true,
                'attendance' => true,
                'crm_lids' => true,
                'crm_analytics' => true,
                'sms_notifications' => true,
                'sms_monthly_limit' => 0, // Безлимит
                'salary' => true,
                'reports_export' => true,
                'knowledge_base' => true,
                'api_access' => true,
                'branches' => 0, // Безлимит
                'priority_support' => true,
                'personal_manager' => true,
            ]),
        ], ['code' => 'enterprise']);

        echo "    > Обновлены данные тарифных планов\n";
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // Возвращаем старые значения
        $this->update('{{%saas_plan}}', [
            'name' => 'Free',
            'description' => 'Бесплатный план для небольших центров',
            'max_pupils' => 10,
            'max_teachers' => 2,
            'max_groups' => 3,
            'max_admins' => 1,
            'max_branches' => 0,
            'price_monthly' => 0,
            'price_yearly' => 0,
            'trial_days' => 14,
        ], ['code' => 'free']);

        $this->update('{{%saas_plan}}', [
            'name' => 'Basic',
            'description' => 'Базовый план с SMS и отчётами',
            'max_pupils' => 50,
            'max_teachers' => 5,
            'max_groups' => 10,
            'max_admins' => 2,
            'max_branches' => 1,
            'price_monthly' => 9990,
            'price_yearly' => 99900,
            'trial_days' => 14,
        ], ['code' => 'basic']);

        $this->update('{{%saas_plan}}', [
            'name' => 'Pro',
            'description' => 'Профессиональный план с API и лидами',
            'max_pupils' => 200,
            'max_teachers' => 20,
            'max_groups' => 50,
            'max_admins' => 5,
            'max_branches' => 3,
            'price_monthly' => 29990,
            'price_yearly' => 299900,
            'trial_days' => 14,
        ], ['code' => 'pro']);

        $this->update('{{%saas_plan}}', [
            'name' => 'Enterprise',
            'description' => 'Корпоративный план без ограничений',
            'max_pupils' => 0,
            'max_teachers' => 0,
            'max_groups' => 0,
            'max_admins' => 0,
            'max_branches' => 0,
            'price_monthly' => 99990,
            'price_yearly' => 999900,
            'trial_days' => 30,
        ], ['code' => 'enterprise']);

        echo "    > Восстановлены старые данные тарифных планов\n";
    }
}
