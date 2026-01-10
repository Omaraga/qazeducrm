<?php

namespace app\commands;

use app\helpers\OrganizationRoles;
use app\models\Group;
use app\models\Lesson;
use app\models\LessonAttendance;
use app\models\Organizations;
use app\models\OrganizationSubscription;
use app\models\OrganizationAccessSettings;
use app\models\Payment;
use app\models\Pupil;
use app\models\PupilEducation;
use app\models\relations\EducationGroup;
use app\models\relations\TeacherGroup;
use app\models\relations\UserOrganization;
use app\models\Room;
use app\models\SaasPlan;
use app\models\Subject;
use app\models\Tariff;
use app\models\TeacherRate;
use app\models\TeacherSalary;
use app\models\User;
use Yii;
use yii\console\Controller;
use yii\console\ExitCode;
use yii\helpers\Console;

/**
 * Комплексное тестирование расчёта зарплаты учителей
 *
 * Использование:
 *   php yii salary-test/create       - Создать тестовую организацию со всеми данными
 *   php yii salary-test/calculate    - Рассчитать зарплату
 *   php yii salary-test/verify       - Проверить результаты
 *   php yii salary-test/clean        - Удалить тестовые данные
 */
class SalaryTestController extends Controller
{
    // IDs для тестовых данных
    private $orgId;
    private $planId;
    private $subscriptionId;
    private $adminId;
    private $teacher1Id; // per-student rate
    private $teacher2Id; // per-lesson rate
    private $teacher3Id; // percent rate
    private $subjects = [];
    private $rooms = [];
    private $groups = [];
    private $tariffs = [];
    private $pupils = [];

    // Константы для тестирования
    const ORG_NAME = 'Salary Test Academy';
    const PERIOD_START = '2026-01-01';
    const PERIOD_END = '2026-01-31';

    /**
     * Создать полный набор тестовых данных
     */
    public function actionCreate()
    {
        $this->stdout("\n╔══════════════════════════════════════════════════════════════╗\n", Console::FG_CYAN);
        $this->stdout("║  КОМПЛЕКСНОЕ ТЕСТИРОВАНИЕ РАСЧЁТА ЗАРПЛАТЫ УЧИТЕЛЕЙ         ║\n", Console::FG_CYAN);
        $this->stdout("╚══════════════════════════════════════════════════════════════╝\n\n", Console::FG_CYAN);

        $transaction = Yii::$app->db->beginTransaction();
        try {
            // Фаза 1: Организация и пользователи
            $this->createOrganization();
            $this->createSubscription();
            $this->createUsers();
            $this->createAccessSettings();

            // Фаза 2: Справочники
            $this->createSubjects();
            $this->createRooms();
            $this->createTariffs();

            // Фаза 3: Группы и ставки
            $this->createGroups();
            $this->linkTeachersToGroups();
            $this->createTeacherRates();

            // Фаза 4: Ученики
            $this->createPupils();
            $this->enrollPupils();

            // Фаза 5: Занятия
            $this->createLessons();

            // Фаза 6: Посещения
            $this->createAttendance();

            // Фаза 7: Оплаты
            $this->createPayments();

            $transaction->commit();

            $this->printSummary();

            return ExitCode::OK;
        } catch (\Exception $e) {
            $transaction->rollBack();
            $this->stderr("\n❌ Ошибка: " . $e->getMessage() . "\n", Console::FG_RED);
            $this->stderr($e->getTraceAsString() . "\n", Console::FG_RED);
            return ExitCode::UNSPECIFIED_ERROR;
        }
    }

    /**
     * Рассчитать зарплату для всех учителей
     */
    public function actionCalculate()
    {
        $this->stdout("\n=== Расчёт зарплаты ===\n\n", Console::FG_CYAN);

        // Находим организацию
        $org = Organizations::find()
            ->where(['name' => self::ORG_NAME])
            ->andWhere(['is_deleted' => 0])
            ->one();

        if (!$org) {
            $this->stderr("Организация не найдена. Сначала выполните: php yii salary-test/create\n", Console::FG_RED);
            return ExitCode::UNSPECIFIED_ERROR;
        }

        $this->orgId = $org->id;
        Organizations::setCurrentOrganization($org, $org->id);

        // Находим учителей
        $teachers = User::find()
            ->innerJoin('user_organization uo', 'uo.related_id = user.id')
            ->where(['uo.target_id' => $this->orgId])
            ->andWhere(['uo.role' => OrganizationRoles::TEACHER])
            ->andWhere(['uo.is_deleted' => 0])
            ->all();

        if (empty($teachers)) {
            $this->stderr("Учителя не найдены\n", Console::FG_RED);
            return ExitCode::UNSPECIFIED_ERROR;
        }

        $this->stdout("Найдено учителей: " . count($teachers) . "\n\n");

        foreach ($teachers as $teacher) {
            $this->stdout("Расчёт для: {$teacher->fio}...\n");

            try {
                // Удаляем старый расчёт если есть
                TeacherSalary::deleteAll([
                    'organization_id' => $this->orgId,
                    'teacher_id' => $teacher->id,
                    'period_start' => self::PERIOD_START,
                    'period_end' => self::PERIOD_END,
                ]);

                $salary = TeacherSalary::calculate($teacher->id, self::PERIOD_START, self::PERIOD_END);

                if ($salary) {
                    $this->stdout("  ✓ Занятий: {$salary->lessons_count}\n", Console::FG_GREEN);
                    $this->stdout("  ✓ Учеников (оплачено): {$salary->students_count}\n", Console::FG_GREEN);
                    $this->stdout("  ✓ Базовая сумма: " . number_format($salary->base_amount, 0, ',', ' ') . " ₸\n", Console::FG_GREEN);
                    $this->stdout("  ✓ Итого: " . number_format($salary->total_amount, 0, ',', ' ') . " ₸\n\n", Console::FG_GREEN);
                } else {
                    $this->stdout("  ⚠ Нет данных для расчёта\n\n", Console::FG_YELLOW);
                }
            } catch (\Exception $e) {
                $this->stderr("  ✗ Ошибка: " . $e->getMessage() . "\n\n", Console::FG_RED);
            }
        }

        return ExitCode::OK;
    }

    /**
     * Проверить результаты расчёта
     */
    public function actionVerify()
    {
        $this->stdout("\n=== Верификация результатов ===\n\n", Console::FG_CYAN);

        // Находим организацию
        $org = Organizations::find()
            ->where(['name' => self::ORG_NAME])
            ->andWhere(['is_deleted' => 0])
            ->one();

        if (!$org) {
            $this->stderr("Организация не найдена\n", Console::FG_RED);
            return ExitCode::UNSPECIFIED_ERROR;
        }

        $this->orgId = $org->id;

        // Статистика по занятиям
        $this->stdout("📊 Статистика по занятиям:\n", Console::FG_YELLOW);
        $lessonStats = Yii::$app->db->createCommand("
            SELECT
                u.fio as teacher,
                COUNT(DISTINCT l.id) as lessons,
                COUNT(la.id) as total_attendance,
                SUM(CASE WHEN la.status IN (1, 2) THEN 1 ELSE 0 END) as paid_attendance
            FROM lesson l
            JOIN user u ON l.teacher_id = u.id
            LEFT JOIN lesson_attendance la ON l.id = la.lesson_id AND la.is_deleted = 0
            WHERE l.organization_id = :org AND l.status = 1 AND l.is_deleted = 0
            GROUP BY l.teacher_id
            ORDER BY u.fio
        ")->bindValue(':org', $this->orgId)->queryAll();

        foreach ($lessonStats as $stat) {
            $this->stdout("  {$stat['teacher']}:\n");
            $this->stdout("    Занятий: {$stat['lessons']}\n");
            $this->stdout("    Всего посещений: {$stat['total_attendance']}\n");
            $this->stdout("    Оплачиваемых: {$stat['paid_attendance']}\n\n");
        }

        // Результаты расчёта зарплаты
        $this->stdout("💰 Результаты расчёта зарплаты:\n", Console::FG_YELLOW);
        $salaries = TeacherSalary::find()
            ->where(['organization_id' => $this->orgId])
            ->andWhere(['is_deleted' => 0])
            ->with('teacher')
            ->all();

        if (empty($salaries)) {
            $this->stdout("  Зарплаты не рассчитаны. Выполните: php yii salary-test/calculate\n\n", Console::FG_RED);
        } else {
            foreach ($salaries as $salary) {
                $statusLabel = $this->getSalaryStatusLabel($salary->status);
                $this->stdout("  {$salary->teacher->fio}:\n");
                $this->stdout("    Период: {$salary->period_start} - {$salary->period_end}\n");
                $this->stdout("    Занятий: {$salary->lessons_count}\n");
                $this->stdout("    Учеников: {$salary->students_count}\n");
                $this->stdout("    Базовая: " . number_format($salary->base_amount, 0, ',', ' ') . " ₸\n");
                $this->stdout("    Бонус: " . number_format($salary->bonus_amount, 0, ',', ' ') . " ₸\n");
                $this->stdout("    Вычеты: " . number_format($salary->deduction_amount, 0, ',', ' ') . " ₸\n");
                $this->stdout("    ИТОГО: " . number_format($salary->total_amount, 0, ',', ' ') . " ₸\n");
                $this->stdout("    Статус: {$statusLabel}\n\n");
            }
        }

        // Детализация
        $this->stdout("📋 Детализация по занятиям (первые 10):\n", Console::FG_YELLOW);
        $details = Yii::$app->db->createCommand("
            SELECT
                tsd.lesson_date,
                g.name as group_name,
                tsd.students_paid,
                CASE tsd.rate_type
                    WHEN 1 THEN 'За ученика'
                    WHEN 2 THEN 'За занятие'
                    WHEN 3 THEN 'Процент'
                END as rate_type,
                tsd.rate_value,
                tsd.amount
            FROM teacher_salary_detail tsd
            JOIN teacher_salary ts ON ts.id = tsd.salary_id
            LEFT JOIN `group` g ON g.id = tsd.group_id
            WHERE ts.organization_id = :org
            ORDER BY tsd.lesson_date
            LIMIT 10
        ")->bindValue(':org', $this->orgId)->queryAll();

        if (empty($details)) {
            $this->stdout("  Детализация не найдена\n");
        } else {
            foreach ($details as $d) {
                $this->stdout("  {$d['lesson_date']} | {$d['group_name']} | ");
                $this->stdout("Учеников: {$d['students_paid']} | {$d['rate_type']} {$d['rate_value']} | ");
                $this->stdout(number_format($d['amount'], 0, ',', ' ') . " ₸\n");
            }
        }

        return ExitCode::OK;
    }

    /**
     * Удалить тестовые данные
     */
    public function actionClean()
    {
        $this->stdout("\n=== Удаление тестовых данных ===\n\n", Console::FG_YELLOW);

        $org = Organizations::find()
            ->where(['name' => self::ORG_NAME])
            ->one();

        if (!$org) {
            $this->stdout("Тестовая организация не найдена\n");
            return ExitCode::OK;
        }

        $this->orgId = $org->id;

        $transaction = Yii::$app->db->beginTransaction();
        try {
            // Удаляем в правильном порядке (foreign keys)
            $this->deleteTable('teacher_salary_detail', 'salary_id IN (SELECT id FROM teacher_salary WHERE organization_id = :org)');
            $this->deleteTable('teacher_salary');
            $this->deleteTable('teacher_rate');
            $this->deleteTable('lesson_attendance');
            $this->deleteTable('lesson');
            $this->deleteTable('education_group');
            $this->deleteTable('pupil_education');
            $this->deleteTable('payment');
            $this->deleteTable('pupil');
            $this->deleteTable('teacher_group');
            $this->deleteTable('`group`');
            $this->deleteTable('room');
            $this->deleteTable('tariff');
            $this->deleteTable('organization_access_settings');
            $this->deleteTable('user_organization', 'target_id = :org');

            // Удаляем пользователей
            $userIds = Yii::$app->db->createCommand("
                SELECT related_id FROM user_organization WHERE target_id = :org
            ")->bindValue(':org', $this->orgId)->queryColumn();

            if (!empty($userIds)) {
                Yii::$app->db->createCommand()
                    ->delete('user', ['id' => $userIds])
                    ->execute();
                $this->stdout("  - Пользователи: " . count($userIds) . " удалено\n");
            }

            // Удаляем подписку и организацию
            $this->deleteTable('organization_subscription');

            Yii::$app->db->createCommand()
                ->delete('organization', ['id' => $this->orgId])
                ->execute();
            $this->stdout("  - Организация удалена\n");

            $transaction->commit();
            $this->stdout("\n✓ Тестовые данные удалены!\n", Console::FG_GREEN);
            return ExitCode::OK;
        } catch (\Exception $e) {
            $transaction->rollBack();
            $this->stderr("Ошибка: " . $e->getMessage() . "\n", Console::FG_RED);
            return ExitCode::UNSPECIFIED_ERROR;
        }
    }

    /**
     * Полный цикл: создание + расчёт + верификация
     */
    public function actionFull()
    {
        $result = $this->actionClean();
        if ($result !== ExitCode::OK) return $result;

        $result = $this->actionCreate();
        if ($result !== ExitCode::OK) return $result;

        $result = $this->actionCalculate();
        if ($result !== ExitCode::OK) return $result;

        return $this->actionVerify();
    }

    // ==================== ВСПОМОГАТЕЛЬНЫЕ МЕТОДЫ ====================

    private function createOrganization()
    {
        $this->stdout("📦 Создание организации...\n", Console::FG_CYAN);

        // Проверяем существует ли уже
        $existing = Organizations::find()
            ->where(['name' => self::ORG_NAME])
            ->one();

        if ($existing) {
            throw new \Exception("Организация уже существует. Сначала выполните: php yii salary-test/clean");
        }

        $org = new Organizations();
        $org->name = self::ORG_NAME;
        $org->type = Organizations::TYPE_HEAD;
        $org->status = Organizations::STATUS_ACTIVE;
        $org->email = 'salary-test@example.com';
        $org->phone = '+77001234567';
        $org->address = 'Test Address, 123';
        $org->timezone = 'Asia/Almaty';
        $org->locale = 'ru';
        $org->billing_mode = 'pooled';
        $org->email_verified_at = date('Y-m-d H:i:s');

        if (!$org->save(false)) {
            throw new \Exception("Не удалось создать организацию: " . print_r($org->errors, true));
        }

        $this->orgId = $org->id;
        Organizations::setCurrentOrganization($org, $org->id);

        $this->stdout("  ✓ Организация создана (ID: {$this->orgId})\n\n");
    }

    private function createSubscription()
    {
        $this->stdout("💎 Создание премиум-подписки...\n", Console::FG_CYAN);

        // Ищем или создаём план
        $plan = SaasPlan::find()->where(['code' => 'premium_test'])->one();

        if (!$plan) {
            $plan = new SaasPlan();
            $plan->code = 'premium_test';
            $plan->name = 'Premium Test Plan';
            $plan->max_pupils = 1000;
            $plan->max_teachers = 100;
            $plan->max_groups = 200;
            $plan->max_admins = 20;
            $plan->price_monthly = 0;
            $plan->trial_days = 365;
            $plan->is_active = 1;
            $plan->save(false);
            $this->stdout("  ✓ SaaS план создан\n");
        }

        $this->planId = $plan->id;

        $subscription = new OrganizationSubscription();
        $subscription->organization_id = $this->orgId;
        $subscription->saas_plan_id = $this->planId;
        $subscription->status = OrganizationSubscription::STATUS_ACTIVE;
        $subscription->billing_period = OrganizationSubscription::PERIOD_YEARLY;
        $subscription->started_at = date('Y-m-d H:i:s');
        $subscription->expires_at = date('Y-m-d H:i:s', strtotime('+1 year'));
        $subscription->access_mode = OrganizationSubscription::ACCESS_FULL;

        if (!$subscription->save(false)) {
            throw new \Exception("Не удалось создать подписку");
        }

        $this->subscriptionId = $subscription->id;
        $this->stdout("  ✓ Подписка активирована (полный доступ)\n\n");
    }

    private function createUsers()
    {
        $this->stdout("👥 Создание пользователей...\n", Console::FG_CYAN);

        // Админ
        $admin = $this->createUser('admin_salary', 'Админов Админ Админович', 'admin-salary@test.com');
        $this->linkUserToOrg($admin->id, OrganizationRoles::GENERAL_DIRECTOR);
        $this->adminId = $admin->id;
        $this->stdout("  ✓ Админ: {$admin->fio}\n");

        // Учитель 1 - ставка за ученика
        $teacher1 = $this->createUser('teacher1_salary', 'Иванов Пётр Сергеевич', 'teacher1-salary@test.com');
        $this->linkUserToOrg($teacher1->id, OrganizationRoles::TEACHER);
        $this->teacher1Id = $teacher1->id;
        $this->stdout("  ✓ Учитель 1 (за ученика): {$teacher1->fio}\n");

        // Учитель 2 - ставка за занятие
        $teacher2 = $this->createUser('teacher2_salary', 'Сидорова Анна Михайловна', 'teacher2-salary@test.com');
        $this->linkUserToOrg($teacher2->id, OrganizationRoles::TEACHER);
        $this->teacher2Id = $teacher2->id;
        $this->stdout("  ✓ Учитель 2 (за занятие): {$teacher2->fio}\n");

        // Учитель 3 - процент
        $teacher3 = $this->createUser('teacher3_salary', 'Козлов Дмитрий Александрович', 'teacher3-salary@test.com');
        $this->linkUserToOrg($teacher3->id, OrganizationRoles::TEACHER);
        $this->teacher3Id = $teacher3->id;
        $this->stdout("  ✓ Учитель 3 (процент): {$teacher3->fio}\n\n");
    }

    private function createUser($username, $fio, $email)
    {
        // Проверяем существующего пользователя
        $user = User::find()->where(['username' => $username])->one();

        if (!$user) {
            $user = new User();
            $user->username = $username;
            $user->email = $email;
            $user->fio = $fio;
            $parts = explode(' ', $fio);
            $user->last_name = $parts[0] ?? '';
            $user->first_name = $parts[1] ?? '';
            $user->middle_name = $parts[2] ?? '';
            $user->setPassword('admin123');
            $user->generateAuthKey();
            $user->status = User::STATUS_ACTIVE;
            $user->active_organization_id = $this->orgId;

            if (!$user->save(false)) {
                throw new \Exception("Не удалось создать пользователя {$username}");
            }
        } else {
            // Обновляем активную организацию
            $user->active_organization_id = $this->orgId;
            $user->save(false);
        }

        return $user;
    }

    private function linkUserToOrg($userId, $role)
    {
        $link = new UserOrganization();
        $link->organization_id = $this->orgId;
        $link->related_id = $userId;
        $link->target_id = $this->orgId;
        $link->role = $role;
        $link->state = UserOrganization::STATE_ACTIVE;
        $link->save(false);
    }

    private function createAccessSettings()
    {
        $settings = new OrganizationAccessSettings();
        $settings->organization_id = $this->orgId;
        $settings->settings = json_encode([
            'teacher_view_own_salary' => true,
            'admin_view_salary' => true,
        ]);
        $settings->save(false);
    }

    private function createSubjects()
    {
        $this->stdout("📚 Создание предметов...\n", Console::FG_CYAN);

        $subjectNames = ['Математика', 'Английский язык', 'Физика'];

        foreach ($subjectNames as $name) {
            $subject = Subject::find()
                ->where(['name' => $name, 'is_deleted' => 0])
                ->one();

            if (!$subject) {
                $subject = new Subject();
                $subject->name = $name;
                $subject->organization_id = $this->orgId;
                $subject->save(false);
            }

            $this->subjects[$name] = $subject;
            $this->stdout("  ✓ {$name}\n");
        }
        $this->stdout("\n");
    }

    private function createRooms()
    {
        $this->stdout("🏫 Создание кабинетов...\n", Console::FG_CYAN);

        $roomData = [
            ['name' => 'Кабинет 101', 'code' => '101', 'capacity' => 20],
            ['name' => 'Кабинет 102', 'code' => '102', 'capacity' => 15],
        ];

        foreach ($roomData as $data) {
            $room = new Room();
            $room->organization_id = $this->orgId;
            $room->name = $data['name'];
            $room->code = $data['code'];
            $room->capacity = $data['capacity'];
            $room->color = '#6366f1';
            $room->save(false);
            $this->rooms[$data['code']] = $room;
            $this->stdout("  ✓ {$data['name']}\n");
        }
        $this->stdout("\n");
    }

    private function createTariffs()
    {
        $this->stdout("💰 Создание тарифов...\n", Console::FG_CYAN);

        $tariffData = [
            ['name' => 'Групповой 8 занятий', 'lesson_amount' => 8, 'price' => 20000],
            ['name' => 'Индивидуальный 4 занятия', 'lesson_amount' => 4, 'price' => 40000],
        ];

        foreach ($tariffData as $data) {
            $tariff = new Tariff();
            $tariff->organization_id = $this->orgId;
            $tariff->name = $data['name'];
            $tariff->duration = 3;
            $tariff->lesson_amount = $data['lesson_amount'];
            $tariff->type = 1;
            $tariff->price = $data['price'];
            $tariff->status = Tariff::STATUS_ACTIVE;
            $tariff->save(false);
            $this->tariffs[$data['name']] = $tariff;
            $this->stdout("  ✓ {$data['name']} ({$data['price']} ₸)\n");
        }
        $this->stdout("\n");
    }

    private function createGroups()
    {
        $this->stdout("👨‍👩‍👧‍👦 Создание групп...\n", Console::FG_CYAN);

        $groupData = [
            // Групповые занятия (TYPE_GROUP = 1)
            ['code' => 'MATH-G1', 'name' => 'Математика Группа 1', 'subject' => 'Математика', 'type' => Group::TYPE_GROUP],
            ['code' => 'MATH-G2', 'name' => 'Математика Группа 2', 'subject' => 'Математика', 'type' => Group::TYPE_GROUP],
            // Индивидуальные (TYPE_INDIVIDUAL = 2)
            ['code' => 'ENG-IND', 'name' => 'Английский Индивид', 'subject' => 'Английский язык', 'type' => Group::TYPE_INDIVIDUAL],
            ['code' => 'PHYS-IND', 'name' => 'Физика Индивид', 'subject' => 'Физика', 'type' => Group::TYPE_INDIVIDUAL],
        ];

        foreach ($groupData as $data) {
            $group = new Group();
            $group->organization_id = $this->orgId;
            $group->code = $data['code'];
            $group->name = $data['name'];
            $group->subject_id = $this->subjects[$data['subject']]->id;
            $group->type = $data['type'];
            $group->status = 1;
            $group->color = '#6366f1';
            $group->save(false);
            $this->groups[$data['code']] = $group;
            $typeLabel = $data['type'] == Group::TYPE_GROUP ? 'Группа' : 'Индивид';
            $this->stdout("  ✓ {$data['code']} - {$data['name']} ({$typeLabel})\n");
        }
        $this->stdout("\n");
    }

    private function linkTeachersToGroups()
    {
        $this->stdout("🔗 Привязка учителей к группам...\n", Console::FG_CYAN);

        $links = [
            // Учитель 1 -> Математика (per-student)
            ['teacher' => $this->teacher1Id, 'group' => 'MATH-G1', 'type' => TeacherGroup::PRICE_TYPE_FIX, 'price' => 500],
            ['teacher' => $this->teacher1Id, 'group' => 'MATH-G2', 'type' => TeacherGroup::PRICE_TYPE_FIX, 'price' => 500],
            // Учитель 2 -> Английский (per-lesson)
            ['teacher' => $this->teacher2Id, 'group' => 'ENG-IND', 'type' => TeacherGroup::PRICE_TYPE_FIX, 'price' => 3000],
            // Учитель 3 -> Физика (percent)
            ['teacher' => $this->teacher3Id, 'group' => 'PHYS-IND', 'type' => TeacherGroup::PRICE_TYPE_PERCENT, 'price' => 30],
        ];

        foreach ($links as $link) {
            $tg = new TeacherGroup();
            $tg->organization_id = $this->orgId;
            $tg->related_id = $link['teacher'];
            $tg->target_id = $this->groups[$link['group']]->id;
            $tg->type = $link['type'];
            $tg->price = $link['price'];
            $tg->save(false);
            $this->stdout("  ✓ Учитель {$link['teacher']} -> {$link['group']}\n");
        }
        $this->stdout("\n");
    }

    private function createTeacherRates()
    {
        $this->stdout("⚙️ Настройка ставок учителей...\n", Console::FG_CYAN);

        // Учитель 1: за ученика (500 ₸)
        $rate1 = new TeacherRate();
        $rate1->organization_id = $this->orgId;
        $rate1->teacher_id = $this->teacher1Id;
        $rate1->rate_type = TeacherRate::RATE_PER_STUDENT;
        $rate1->rate_value = 500;
        $rate1->is_active = 1;
        $rate1->save(false);
        $this->stdout("  ✓ Учитель 1: 500 ₸/ученик (RATE_PER_STUDENT)\n");

        // Учитель 2: за занятие (3000 ₸)
        $rate2 = new TeacherRate();
        $rate2->organization_id = $this->orgId;
        $rate2->teacher_id = $this->teacher2Id;
        $rate2->rate_type = TeacherRate::RATE_PER_LESSON;
        $rate2->rate_value = 3000;
        $rate2->is_active = 1;
        $rate2->save(false);
        $this->stdout("  ✓ Учитель 2: 3000 ₸/занятие (RATE_PER_LESSON)\n");

        // Учитель 3: процент (30%)
        $rate3 = new TeacherRate();
        $rate3->organization_id = $this->orgId;
        $rate3->teacher_id = $this->teacher3Id;
        $rate3->rate_type = TeacherRate::RATE_PERCENT;
        $rate3->rate_value = 30;
        $rate3->is_active = 1;
        $rate3->save(false);
        $this->stdout("  ✓ Учитель 3: 30% (RATE_PERCENT)\n\n");
    }

    private function createPupils()
    {
        $this->stdout("👦 Создание 32 учеников...\n", Console::FG_CYAN);

        $lastNames = ['Касымов', 'Ахметов', 'Жумабеков', 'Сагынбаев', 'Тулеуов', 'Байжанов', 'Сериков', 'Оразов',
            'Мухамедов', 'Кенжебаев', 'Абдрахманов', 'Токаев', 'Бекетов', 'Искаков', 'Садыков', 'Умаров',
            'Иванова', 'Петрова', 'Сидорова', 'Козлова', 'Смирнова', 'Федорова', 'Волкова', 'Кузнецова',
            'Попова', 'Морозова', 'Соколова', 'Павлова', 'Новикова', 'Орлова', 'Андреева', 'Калиева'];
        $firstNamesMale = ['Арман', 'Нурлан', 'Даулет', 'Ерлан', 'Асет', 'Канат', 'Мурат', 'Сергей'];
        $firstNamesFemale = ['Айгуль', 'Динара', 'Гульнар', 'Асель', 'Жанар', 'Мадина', 'Анна', 'Мария'];

        for ($i = 0; $i < 32; $i++) {
            $sex = $i < 16 ? 1 : 2;
            $firstName = $sex == 1 ? $firstNamesMale[$i % 8] : $firstNamesFemale[$i % 8];
            $lastName = $lastNames[$i];
            $iin = '05' . str_pad($i + 1, 2, '0', STR_PAD_LEFT) . str_pad(rand(10000000, 99999999), 8, '0', STR_PAD_LEFT);

            $pupil = new Pupil();
            $pupil->organization_id = $this->orgId;
            $pupil->iin = $iin;
            $pupil->first_name = $firstName;
            $pupil->last_name = $lastName;
            $pupil->sex = $sex;
            $pupil->phone = '+7' . rand(700, 799) . rand(1000000, 9999999);
            $pupil->status = Pupil::STATUS_ACTIVE;
            $pupil->balance = 20000;
            $pupil->save(false);
            $this->pupils[] = $pupil;
        }

        $this->stdout("  ✓ Создано 32 ученика\n\n");
    }

    private function enrollPupils()
    {
        $this->stdout("📝 Зачисление учеников в группы...\n", Console::FG_CYAN);

        // Распределение: 15 + 15 + 1 + 1 = 32
        $assignments = [
            'MATH-G1' => array_slice($this->pupils, 0, 15),
            'MATH-G2' => array_slice($this->pupils, 15, 15),
            'ENG-IND' => array_slice($this->pupils, 30, 1),
            'PHYS-IND' => array_slice($this->pupils, 31, 1),
        ];

        foreach ($assignments as $groupCode => $pupils) {
            $group = $this->groups[$groupCode];
            $isIndividual = $group->type == Group::TYPE_INDIVIDUAL;
            $tariff = $isIndividual ? $this->tariffs['Индивидуальный 4 занятия'] : $this->tariffs['Групповой 8 занятий'];

            foreach ($pupils as $pupil) {
                // PupilEducation
                $education = new PupilEducation();
                $education->organization_id = $this->orgId;
                $education->pupil_id = $pupil->id;
                $education->tariff_id = $tariff->id;
                $education->date_start = self::PERIOD_START;
                $education->date_end = self::PERIOD_END;
                $education->tariff_price = $tariff->price;
                $education->total_price = $tariff->price;
                $education->save(false);

                // EducationGroup
                $eg = new EducationGroup();
                $eg->organization_id = $this->orgId;
                $eg->education_id = $education->id;
                $eg->group_id = $group->id;
                $eg->pupil_id = $pupil->id;
                $eg->subject_id = $group->subject_id;
                $eg->save(false);
            }

            $this->stdout("  ✓ {$groupCode}: " . count($pupils) . " учеников\n");
        }
        $this->stdout("\n");
    }

    private function createLessons()
    {
        $this->stdout("📅 Генерация занятий на январь 2026...\n", Console::FG_CYAN);

        // Расписание: группа -> [дни недели]
        $schedule = [
            'MATH-G1' => ['teacher' => $this->teacher1Id, 'days' => [1, 3, 5], 'time' => ['10:00', '11:00']], // Пн, Ср, Пт
            'MATH-G2' => ['teacher' => $this->teacher1Id, 'days' => [1, 3, 5], 'time' => ['11:00', '12:00']],
            'ENG-IND' => ['teacher' => $this->teacher2Id, 'days' => [2, 4], 'time' => ['14:00', '15:00']], // Вт, Чт
            'PHYS-IND' => ['teacher' => $this->teacher3Id, 'days' => [6], 'time' => ['10:00', '11:00']], // Сб
        ];

        $lessonCount = 0;
        $startDate = new \DateTime(self::PERIOD_START);
        $endDate = new \DateTime(self::PERIOD_END);
        $room = reset($this->rooms);

        while ($startDate <= $endDate) {
            $dayOfWeek = (int)$startDate->format('N');

            foreach ($schedule as $groupCode => $config) {
                if (!in_array($dayOfWeek, $config['days'])) continue;

                $group = $this->groups[$groupCode];
                $lesson = new Lesson();
                $lesson->organization_id = $this->orgId;
                $lesson->group_id = $group->id;
                $lesson->teacher_id = $config['teacher'];
                $lesson->room_id = $room->id;
                $lesson->date = $startDate->format('Y-m-d');
                $lesson->start_time = $config['time'][0];
                $lesson->end_time = $config['time'][1];
                $lesson->week = $dayOfWeek;
                $lesson->status = Lesson::STATUS_FINISHED; // Все завершённые
                $lesson->save(false);
                $lessonCount++;
            }

            $startDate->modify('+1 day');
        }

        $this->stdout("  ✓ Создано {$lessonCount} занятий\n\n");
    }

    private function createAttendance()
    {
        $this->stdout("✅ Проставление посещений...\n", Console::FG_CYAN);

        $lessons = Lesson::find()
            ->where(['organization_id' => $this->orgId])
            ->andWhere(['status' => Lesson::STATUS_FINISHED])
            ->all();

        $attendanceCount = 0;
        $statuses = [
            LessonAttendance::STATUS_VISIT,
            LessonAttendance::STATUS_MISS_WITH_PAY,
            LessonAttendance::STATUS_MISS_WITHOUT_PAY,
            LessonAttendance::STATUS_MISS_VALID_REASON,
        ];

        foreach ($lessons as $lesson) {
            // Получаем учеников группы
            $educationGroups = EducationGroup::find()
                ->where(['group_id' => $lesson->group_id, 'organization_id' => $this->orgId, 'is_deleted' => 0])
                ->all();

            foreach ($educationGroups as $eg) {
                // Распределение: 70% visit, 10% miss_with_pay, 10% miss_without_pay, 10% valid_reason
                $rand = rand(1, 100);
                if ($rand <= 70) {
                    $status = LessonAttendance::STATUS_VISIT;
                } elseif ($rand <= 80) {
                    $status = LessonAttendance::STATUS_MISS_WITH_PAY;
                } elseif ($rand <= 90) {
                    $status = LessonAttendance::STATUS_MISS_WITHOUT_PAY;
                } else {
                    $status = LessonAttendance::STATUS_MISS_VALID_REASON;
                }

                $att = new LessonAttendance();
                $att->organization_id = $this->orgId;
                $att->lesson_id = $lesson->id;
                $att->pupil_id = $eg->pupil_id;
                $att->teacher_id = $lesson->teacher_id;
                $att->status = $status;
                $att->save(false);
                $attendanceCount++;
            }
        }

        $this->stdout("  ✓ Создано {$attendanceCount} записей посещаемости\n");
        $this->stdout("    - ~70% STATUS_VISIT (присутствие)\n");
        $this->stdout("    - ~10% STATUS_MISS_WITH_PAY (пропуск с оплатой)\n");
        $this->stdout("    - ~10% STATUS_MISS_WITHOUT_PAY (пропуск без оплаты)\n");
        $this->stdout("    - ~10% STATUS_MISS_VALID_REASON (уважительная причина)\n\n");
    }

    private function createPayments()
    {
        $this->stdout("💳 Добавление оплат ученикам...\n", Console::FG_CYAN);

        $paymentCount = 0;
        foreach ($this->pupils as $pupil) {
            $payment = new Payment();
            $payment->organization_id = $this->orgId;
            $payment->pupil_id = $pupil->id;
            $payment->type = Payment::TYPE_PAY;
            $payment->purpose_id = Payment::PURPOSE_EDUCATION;
            $payment->amount = 20000;
            $payment->date = '2026-01-05';
            $payment->comment = 'Тестовый платеж';
            $payment->save(false);
            $paymentCount++;
        }

        $this->stdout("  ✓ Создано {$paymentCount} платежей\n\n");
    }

    private function printSummary()
    {
        $this->stdout("╔══════════════════════════════════════════════════════════════╗\n", Console::FG_GREEN);
        $this->stdout("║                    ДАННЫЕ СОЗДАНЫ УСПЕШНО!                  ║\n", Console::FG_GREEN);
        $this->stdout("╚══════════════════════════════════════════════════════════════╝\n\n", Console::FG_GREEN);

        $this->stdout("📊 Сводка:\n", Console::FG_YELLOW);
        $this->stdout("  • Организация: {$this->orgId} (" . self::ORG_NAME . ")\n");
        $this->stdout("  • Учителей: 3\n");
        $this->stdout("  • Учеников: 32\n");
        $this->stdout("  • Групп: 4\n");
        $this->stdout("  • Период: " . self::PERIOD_START . " - " . self::PERIOD_END . "\n\n");

        $this->stdout("🔧 Настройки ставок:\n", Console::FG_YELLOW);
        $this->stdout("  • Учитель 1: 500 ₸/ученик (RATE_PER_STUDENT)\n");
        $this->stdout("  • Учитель 2: 3000 ₸/занятие (RATE_PER_LESSON)\n");
        $this->stdout("  • Учитель 3: 30% (RATE_PERCENT)\n\n");

        $this->stdout("📋 Следующие шаги:\n", Console::FG_CYAN);
        $this->stdout("  1. Рассчитать зарплату:  php yii salary-test/calculate\n");
        $this->stdout("  2. Проверить результаты: php yii salary-test/verify\n");
        $this->stdout("  3. Или всё сразу:        php yii salary-test/full\n\n");

        $this->stdout("🌐 Веб-интерфейс:\n", Console::FG_CYAN);
        $this->stdout("  • Расчёт ЗП: /{$this->orgId}/crm/salary/calculate\n");
        $this->stdout("  • Ставки:    /{$this->orgId}/crm/salary/rates\n");
        $this->stdout("  • Ведомость: /{$this->orgId}/crm/salary\n\n");
    }

    private function deleteTable($table, $customWhere = null)
    {
        if ($customWhere) {
            $count = Yii::$app->db->createCommand("DELETE FROM {$table} WHERE {$customWhere}")
                ->bindValue(':org', $this->orgId)
                ->execute();
        } else {
            $count = Yii::$app->db->createCommand("DELETE FROM {$table} WHERE organization_id = :org")
                ->bindValue(':org', $this->orgId)
                ->execute();
        }
        $this->stdout("  - {$table}: {$count} удалено\n");
    }

    private function getSalaryStatusLabel($status)
    {
        $labels = [
            TeacherSalary::STATUS_DRAFT => 'Черновик',
            TeacherSalary::STATUS_APPROVED => 'Утверждён',
            TeacherSalary::STATUS_PAID => 'Оплачен',
        ];
        return $labels[$status] ?? 'Неизвестно';
    }
}
