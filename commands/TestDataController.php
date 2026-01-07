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
}
