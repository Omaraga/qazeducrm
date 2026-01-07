<?php

namespace app\commands;

use app\models\Payment;
use app\models\Pupil;
use app\models\Lids;
use app\models\Group;
use app\models\User;
use app\models\Lesson;
use app\models\LessonAttendance;
use app\models\PupilEducation;
use app\models\EducationGroup;
use app\models\enum\StatusEnum;
use app\models\TeacherRate;
use app\models\Subject;
use yii\console\Controller;
use yii\console\ExitCode;

/**
 * Генератор тестовых данных для отчетов
 */
class TestDataController extends Controller
{
    public $orgId = 1;

    public function options($actionID)
    {
        return array_merge(parent::options($actionID), ['orgId']);
    }

    /**
     * Генерирует тестовые данные для всех отчетов
     */
    public function actionGenerate()
    {
        echo "Генерация тестовых данных для организации {$this->orgId}...\n\n";

        $this->generatePayments();
        $this->generateLids();
        $this->generateAttendance();

        echo "\nГотово!\n";
        return ExitCode::OK;
    }

    /**
     * Генерация платежей
     */
    protected function generatePayments()
    {
        echo "--- Платежи ---\n";

        // Проверяем наличие учеников
        $pupils = Pupil::find()->where(['organization_id' => $this->orgId])->limit(10)->all();
        if (empty($pupils)) {
            echo "Нет учеников для генерации платежей\n";
            return;
        }

        // Доходы
        $incomeCount = 0;
        foreach ($pupils as $pupil) {
            for ($i = 0; $i < rand(1, 3); $i++) {
                $payment = new Payment();
                $payment->organization_id = $this->orgId;
                $payment->pupil_id = $pupil->id;
                $payment->type = Payment::TYPE_PAY;
                $payment->amount = rand(5, 50) * 1000;
                $payment->method_id = 1;
                $payment->comment = 'Тестовый платёж';
                $payment->date = date('Y-m-d', strtotime("-" . rand(0, 60) . " days"));
                $payment->created_at = $payment->date . ' ' . rand(9, 18) . ':00:00';
                if ($payment->save(false)) {
                    $incomeCount++;
                }
            }
        }
        echo "Создано доходов: $incomeCount\n";

        // Расходы
        $expenseCount = 0;
        $expenseTypes = ['Аренда', 'Зарплата', 'Материалы', 'Коммунальные', 'Реклама'];
        for ($i = 0; $i < 15; $i++) {
            $payment = new Payment();
            $payment->organization_id = $this->orgId;
            $payment->type = Payment::TYPE_SPENDING;
            $payment->amount = rand(10, 100) * 1000;
            $payment->method_id = 1;
            $payment->comment = $expenseTypes[array_rand($expenseTypes)];
            $payment->date = date('Y-m-d', strtotime("-" . rand(0, 60) . " days"));
            $payment->created_at = $payment->date . ' ' . rand(9, 18) . ':00:00';
            if ($payment->save(false)) {
                $expenseCount++;
            }
        }
        echo "Создано расходов: $expenseCount\n";
    }

    /**
     * Генерация лидов
     */
    protected function generateLids()
    {
        echo "\n--- Лиды ---\n";

        $sources = [
            Lids::SOURCE_INSTAGRAM,
            Lids::SOURCE_WHATSAPP,
            Lids::SOURCE_2GIS,
            Lids::SOURCE_WEBSITE,
            Lids::SOURCE_REFERRAL,
            Lids::SOURCE_WALK_IN,
        ];

        $statuses = [
            Lids::STATUS_NEW,
            Lids::STATUS_CONTACTED,
            Lids::STATUS_TRIAL,
            Lids::STATUS_THINKING,
            Lids::STATUS_ENROLLED,
            Lids::STATUS_PAID,
            Lids::STATUS_LOST,
        ];

        // Получаем менеджеров (первые 3 пользователя)
        $managers = User::find()
            ->select('id')
            ->limit(3)
            ->column();

        if (empty($managers)) {
            $managers = [1];
        }

        $names = ['Айгерим', 'Нурлан', 'Асель', 'Дастан', 'Камила', 'Арман', 'Гульназ', 'Ержан', 'Динара', 'Бауыржан'];
        $surnames = ['Касымов', 'Нурланова', 'Ахметов', 'Сатыбалдиева', 'Жумабаев', 'Токтарова', 'Сагынбаев', 'Бейсенова'];

        $count = 0;
        for ($i = 0; $i < 50; $i++) {
            $lid = new Lids();
            $lid->organization_id = $this->orgId;
            $lid->fio = $names[array_rand($names)] . ' ' . $surnames[array_rand($surnames)];
            $lid->phone = '+7' . rand(700, 778) . rand(1000000, 9999999);
            $lid->source = $sources[array_rand($sources)];
            $lid->status = $statuses[array_rand($statuses)];
            $lid->manager_id = $managers[array_rand($managers)];
            $lid->created_at = date('Y-m-d H:i:s', strtotime("-" . rand(0, 90) . " days"));

            if ($lid->save(false)) {
                $count++;
            }
        }
        echo "Создано лидов: $count\n";
    }

    /**
     * Генерация посещаемости
     */
    protected function generateAttendance()
    {
        echo "\n--- Посещаемость ---\n";

        // Находим активные группы
        $groups = Group::find()
            ->where(['organization_id' => $this->orgId, 'status' => StatusEnum::STATUS_ACTIVE])
            ->all();

        if (empty($groups)) {
            echo "Нет активных групп\n";
            return;
        }

        $lessonCount = 0;
        $attendanceCount = 0;

        // Получаем учеников
        $pupils = Pupil::find()
            ->where(['organization_id' => $this->orgId])
            ->limit(20)
            ->all();

        if (empty($pupils)) {
            echo "Нет учеников для посещаемости\n";
            return;
        }

        foreach ($groups as $group) {
            // Создаём уроки за последние 30 дней
            for ($day = 0; $day < 30; $day++) {
                $date = date('Y-m-d', strtotime("-$day days"));

                // 3 урока в неделю
                if (in_array(date('N', strtotime($date)), [1, 3, 5])) {
                    $lesson = new Lesson();
                    $lesson->organization_id = $this->orgId;
                    $lesson->group_id = $group->id;
                    $lesson->date = $date;
                    $lesson->start_time = '10:00:00';
                    $lesson->end_time = '11:00:00';
                    $lesson->status = Lesson::STATUS_FINISHED;
                    $lesson->teacher_id = 1;

                    if ($lesson->save(false)) {
                        $lessonCount++;

                        // Добавляем посещаемость (случайные 5 учеников)
                        $randomPupils = array_rand(array_flip(array_column($pupils, 'id')), min(5, count($pupils)));
                        if (!is_array($randomPupils)) {
                            $randomPupils = [$randomPupils];
                        }

                        foreach ($randomPupils as $pupilId) {
                            $att = new LessonAttendance();
                            $att->organization_id = $this->orgId;
                            $att->lesson_id = $lesson->id;
                            $att->pupil_id = $pupilId;
                            $att->teacher_id = 1;
                            $att->status = rand(1, 10) > 2 ? LessonAttendance::STATUS_VISIT : LessonAttendance::STATUS_MISS_WITHOUT_PAY;

                            if ($att->save(false)) {
                                $attendanceCount++;
                            }
                        }
                    }
                }
            }
        }

        echo "Создано уроков: $lessonCount\n";
        echo "Создано записей посещаемости: $attendanceCount\n";
    }

    /**
     * Создание должников
     */
    public function actionDebtors()
    {
        echo "Создание должников...\n";

        $pupils = Pupil::find()
            ->where(['organization_id' => $this->orgId])
            ->andWhere(['>=', 'balance', 0])
            ->limit(10)
            ->all();

        $count = 0;
        foreach ($pupils as $pupil) {
            $pupil->balance = -1 * rand(5, 50) * 1000;
            if ($pupil->save(false)) {
                $count++;
                echo "  ID {$pupil->id}: {$pupil->balance} тг\n";
            }
        }

        echo "Создано должников: $count\n";
        return ExitCode::OK;
    }

    /**
     * Генерирует тестовые данные для модуля зарплат
     * Использование: php yii test-data/salary --orgId=1
     */
    public function actionSalary()
    {
        echo "=== Генерация тестовых данных для зарплат ===\n";
        echo "Организация ID: {$this->orgId}\n\n";

        // 1. Находим или создаем преподавателя
        $teacher = $this->findOrCreateTeacher();
        if (!$teacher) {
            echo "Ошибка: не удалось найти или создать преподавателя\n";
            return ExitCode::UNSPECIFIED_ERROR;
        }
        echo "Преподаватель: {$teacher->fio} (ID: {$teacher->id})\n\n";

        // 2. Находим или создаем предмет
        $subject = $this->findOrCreateSubject();
        echo "Предмет: {$subject->name} (ID: {$subject->id})\n";

        // 3. Находим или создаем группу
        $group = $this->findOrCreateGroup($subject->id, $teacher->id);
        echo "Группа: {$group->name} (ID: {$group->id})\n\n";

        // 4. Создаем ставки разных типов
        $this->createTeacherRates($teacher->id, $subject->id, $group->id);

        // 5. Создаем уроки и посещаемость
        $this->createLessonsWithAttendance($teacher->id, $group->id);

        echo "\n=== Готово! ===\n";
        echo "Теперь перейдите на страницу /crm/salary/calculate\n";
        echo "Выберите преподавателя '{$teacher->fio}' и период 'Прошлый месяц'\n";

        return ExitCode::OK;
    }

    /**
     * Поиск или создание преподавателя
     */
    protected function findOrCreateTeacher()
    {
        // Ищем существующего преподавателя
        $teacher = User::find()
            ->where(['like', 'fio', 'Тестовый Преподаватель'])
            ->one();

        if (!$teacher) {
            // Ищем любого пользователя
            $teacher = User::find()->one();
        }

        return $teacher;
    }

    /**
     * Поиск или создание предмета
     */
    protected function findOrCreateSubject()
    {
        // Subject не имеет organization_id - общий для всех
        $subject = Subject::find()
            ->andWhere(['is_deleted' => 0])
            ->one();

        if (!$subject) {
            $subject = new Subject();
            $subject->name = 'Тест-Математика';
            $subject->save(false);
            echo "Создан предмет: {$subject->name}\n";
        }

        return $subject;
    }

    /**
     * Поиск или создание группы
     */
    protected function findOrCreateGroup($subjectId, $teacherId)
    {
        $group = Group::find()
            ->where(['organization_id' => $this->orgId])
            ->andWhere(['status' => StatusEnum::STATUS_ACTIVE])
            ->one();

        if (!$group) {
            $group = new Group();
            $group->organization_id = $this->orgId;
            $group->name = 'Тест-Группа-Зарплаты';
            $group->subject_id = $subjectId;
            $group->status = StatusEnum::STATUS_ACTIVE;
            $group->save(false);
            echo "Создана группа: {$group->name}\n";
        }

        return $group;
    }

    /**
     * Создание ставок преподавателя для тестирования приоритета
     */
    protected function createTeacherRates($teacherId, $subjectId, $groupId)
    {
        echo "--- Создание ставок ---\n";

        // Удаляем старые тестовые ставки
        TeacherRate::deleteAll([
            'organization_id' => $this->orgId,
            'teacher_id' => $teacherId,
        ]);

        // 1. Общая ставка (за ученика) - 500 ₸
        $rate1 = new TeacherRate();
        $rate1->organization_id = $this->orgId;
        $rate1->teacher_id = $teacherId;
        $rate1->rate_type = TeacherRate::RATE_PER_STUDENT;
        $rate1->rate_value = 500;
        $rate1->is_active = 1;
        $rate1->save(false);
        echo "✓ Общая ставка: 500 ₸/ученик\n";

        // 2. Ставка для предмета (за урок) - 3000 ₸
        $rate2 = new TeacherRate();
        $rate2->organization_id = $this->orgId;
        $rate2->teacher_id = $teacherId;
        $rate2->subject_id = $subjectId;
        $rate2->rate_type = TeacherRate::RATE_PER_LESSON;
        $rate2->rate_value = 3000;
        $rate2->is_active = 1;
        $rate2->save(false);
        echo "✓ Ставка для предмета: 3000 ₸/урок\n";

        // 3. Ставка для группы (процент) - 30%
        // НЕ создаем, чтобы проверить приоритет предмета
        // Можно раскомментировать для проверки приоритета группы
        /*
        $rate3 = new TeacherRate();
        $rate3->organization_id = $this->orgId;
        $rate3->teacher_id = $teacherId;
        $rate3->group_id = $groupId;
        $rate3->rate_type = TeacherRate::RATE_PERCENT;
        $rate3->rate_value = 30;
        $rate3->is_active = 1;
        $rate3->save(false);
        echo "✓ Ставка для группы: 30%\n";
        */

        echo "Ставки созданы!\n\n";
    }

    /**
     * Создание уроков и посещаемости за прошлый месяц
     */
    protected function createLessonsWithAttendance($teacherId, $groupId)
    {
        echo "--- Создание уроков и посещаемости ---\n";

        // Получаем учеников
        $pupils = Pupil::find()
            ->where(['organization_id' => $this->orgId])
            ->limit(5)
            ->all();

        if (empty($pupils)) {
            echo "Нет учеников! Создаю тестовых...\n";
            $pupils = $this->createTestPupils();
        }

        echo "Учеников для посещаемости: " . count($pupils) . "\n";

        // Определяем период (прошлый месяц)
        $lastMonth = strtotime('first day of last month');
        $lastDayOfMonth = strtotime('last day of last month');

        $lessonCount = 0;
        $attendanceCount = 0;

        // Создаем уроки каждые Пн, Ср, Пт прошлого месяца
        for ($date = $lastMonth; $date <= $lastDayOfMonth; $date = strtotime('+1 day', $date)) {
            $dayOfWeek = date('N', $date);

            // Пн=1, Ср=3, Пт=5
            if (in_array($dayOfWeek, [1, 3, 5])) {
                $dateStr = date('Y-m-d', $date);

                // Создаем урок
                $lesson = new Lesson();
                $lesson->organization_id = $this->orgId;
                $lesson->group_id = $groupId;
                $lesson->teacher_id = $teacherId;
                $lesson->date = date('d.m.Y', $date); // Формат для save()
                $lesson->start_time = '10:00';
                $lesson->end_time = '11:00';
                $lesson->status = Lesson::STATUS_FINISHED;

                if ($lesson->save(false)) {
                    $lessonCount++;

                    // Создаем посещаемость для каждого ученика
                    foreach ($pupils as $pupil) {
                        $att = new LessonAttendance();
                        $att->organization_id = $this->orgId;
                        $att->lesson_id = $lesson->id;
                        $att->pupil_id = $pupil->id;
                        $att->teacher_id = $teacherId;

                        // 80% - посещение, 10% - пропуск с оплатой, 10% - пропуск без оплаты
                        $rand = rand(1, 10);
                        if ($rand <= 8) {
                            $att->status = LessonAttendance::STATUS_VISIT;
                        } elseif ($rand == 9) {
                            $att->status = LessonAttendance::STATUS_MISS_WITH_PAY;
                        } else {
                            $att->status = LessonAttendance::STATUS_MISS_WITHOUT_PAY;
                        }

                        if ($att->save(false)) {
                            $attendanceCount++;
                        }
                    }
                }
            }
        }

        echo "Создано уроков: $lessonCount\n";
        echo "Создано записей посещаемости: $attendanceCount\n";

        // Расчет ожидаемой зарплаты
        $this->calculateExpectedSalary($teacherId, $lessonCount, count($pupils));
    }

    /**
     * Создание тестовых учеников
     */
    protected function createTestPupils()
    {
        $pupils = [];
        $names = ['Айгерим', 'Нурлан', 'Асель', 'Дастан', 'Камила'];
        $surnames = ['Касымова', 'Нурланов', 'Ахметова', 'Сатыбалдиев', 'Жумабаева'];

        for ($i = 0; $i < 5; $i++) {
            $pupil = new Pupil();
            $pupil->organization_id = $this->orgId;
            $pupil->fio = $names[$i] . ' ' . $surnames[$i];
            $pupil->phone = '+7' . rand(700, 778) . rand(1000000, 9999999);
            $pupil->balance = 0;
            if ($pupil->save(false)) {
                $pupils[] = $pupil;
            }
        }

        echo "Создано учеников: " . count($pupils) . "\n";
        return $pupils;
    }

    /**
     * Расчет ожидаемой зарплаты для проверки
     */
    protected function calculateExpectedSalary($teacherId, $lessonCount, $pupilCount)
    {
        echo "\n--- Ожидаемый расчет ---\n";
        echo "При ставке 3000 ₸/урок (приоритет предмета):\n";
        $expectedAmount = $lessonCount * 3000;
        echo "  Уроков: $lessonCount × 3000 ₸ = " . number_format($expectedAmount, 0, ',', ' ') . " ₸\n";
        echo "\nПри ставке 500 ₸/ученик (общая ставка):\n";
        $avgPaidStudents = round($pupilCount * 0.9); // ~90% с оплатой
        $expectedAmountPerStudent = $lessonCount * $avgPaidStudents * 500;
        echo "  Уроков: $lessonCount × ~$avgPaidStudents учеников × 500 ₸ = ~" . number_format($expectedAmountPerStudent, 0, ',', ' ') . " ₸\n";
    }
}
