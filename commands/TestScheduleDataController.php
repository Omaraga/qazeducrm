<?php

namespace app\commands;

use app\models\Group;
use app\models\Lesson;
use app\models\LessonAttendance;
use app\models\Organizations;
use app\models\Payment;
use app\models\Pupil;
use app\models\PupilEducation;
use app\models\relations\EducationGroup;
use app\models\relations\TeacherGroup;
use app\models\Room;
use app\models\ScheduleTemplate;
use app\models\Subject;
use app\models\Tariff;
use app\models\TypicalSchedule;
use app\models\User;
use Yii;
use yii\console\Controller;
use yii\console\ExitCode;
use yii\helpers\Console;

/**
 * Контроллер для создания тестовых данных расписания
 *
 * Использование:
 *   php yii test-schedule-data/clean 2    - Очистить данные организации
 *   php yii test-schedule-data/seed 2     - Заполнить тестовыми данными
 *   php yii test-schedule-data/reset 2    - Очистить и заполнить
 */
class TestScheduleDataController extends Controller
{
    /**
     * @var int ID организации
     */
    private $orgId;

    /**
     * @var array Созданные предметы
     */
    private $subjects = [];

    /**
     * @var array Созданные тарифы
     */
    private $tariffs = [];

    /**
     * @var array Созданные кабинеты
     */
    private $rooms = [];

    /**
     * @var array Созданные преподаватели
     */
    private $teachers = [];

    /**
     * @var array Созданные группы
     */
    private $groups = [];

    /**
     * @var array Созданные ученики
     */
    private $pupils = [];

    /**
     * Очистить данные организации
     * @param int $orgId ID организации
     * @return int
     */
    public function actionClean($orgId)
    {
        $this->orgId = (int) $orgId;

        $this->stdout("Очистка данных для организации #{$this->orgId}...\n", Console::FG_YELLOW);

        $transaction = Yii::$app->db->beginTransaction();
        try {
            // Порядок удаления важен из-за foreign keys
            $this->deleteTable('lesson_attendance', 'Посещаемость');
            $this->deleteTable('lesson', 'Занятия');
            $this->deleteTable('typical_schedule', 'Типовое расписание');
            $this->deleteTable('schedule_template', 'Шаблоны расписания');
            $this->deleteTable('room', 'Кабинеты');
            $this->deleteTable('education_group', 'Связи обучение-группа');
            $this->deleteTable('pupil_education', 'Обучения');
            $this->deleteTable('payment', 'Платежи');
            $this->deleteTable('pupil', 'Ученики');
            $this->deleteTable('teacher_group', 'Связи учитель-группа');
            $this->deleteTable('`group`', 'Группы');

            // tariff_subject не имеет organization_id, удаляем через tariff_id
            $tariffIds = Yii::$app->db->createCommand("SELECT id FROM tariff WHERE organization_id = :org")
                ->bindValue(':org', $this->orgId)
                ->queryColumn();
            if (!empty($tariffIds)) {
                $count = Yii::$app->db->createCommand()
                    ->delete('tariff_subject', ['tariff_id' => $tariffIds])
                    ->execute();
                $this->stdout("  - Связи тариф-предмет: удалено {$count} записей\n");
            } else {
                $this->stdout("  - Связи тариф-предмет: удалено 0 записей\n");
            }

            $this->deleteTable('tariff', 'Тарифы');
            // subject - общая таблица, не удаляем (нет organization_id)
            $this->stdout("  - Предметы: пропущено (общая таблица)\n");

            // Удаляем преподавателей (пользователей с ролью TEACHER)
            // Не удаляем, так как они могут использоваться в других местах

            $transaction->commit();
            $this->stdout("\nДанные успешно очищены!\n", Console::FG_GREEN);
            return ExitCode::OK;
        } catch (\Exception $e) {
            $transaction->rollBack();
            $this->stderr("Ошибка: " . $e->getMessage() . "\n", Console::FG_RED);
            return ExitCode::UNSPECIFIED_ERROR;
        }
    }

    /**
     * Заполнить тестовыми данными
     * @param int $orgId ID организации
     * @return int
     */
    public function actionSeed($orgId)
    {
        $this->orgId = (int) $orgId;

        $this->stdout("Заполнение тестовыми данными для организации #{$this->orgId}...\n", Console::FG_YELLOW);

        $transaction = Yii::$app->db->beginTransaction();
        try {
            // Создаем данные в правильном порядке
            $this->createSubjects();
            $this->createTariffs();
            $this->createRooms();
            $this->createTeachers();
            $this->createGroups();
            $this->createPupils();
            $this->createEducations();
            $this->createPayments();
            $this->createScheduleTemplate();
            $this->createTypicalSchedule();
            $this->generateLessons();
            $this->createAttendance();

            $transaction->commit();
            $this->stdout("\nТестовые данные успешно созданы!\n", Console::FG_GREEN);
            return ExitCode::OK;
        } catch (\Exception $e) {
            $transaction->rollBack();
            $this->stderr("Ошибка: " . $e->getMessage() . "\n", Console::FG_RED);
            $this->stderr($e->getTraceAsString() . "\n", Console::FG_RED);
            return ExitCode::UNSPECIFIED_ERROR;
        }
    }

    /**
     * Очистить и заполнить
     * @param int $orgId ID организации
     * @return int
     */
    public function actionReset($orgId)
    {
        $result = $this->actionClean($orgId);
        if ($result !== ExitCode::OK) {
            return $result;
        }
        return $this->actionSeed($orgId);
    }

    /**
     * Удалить записи из таблицы
     */
    private function deleteTable($table, $label)
    {
        $count = Yii::$app->db->createCommand("DELETE FROM {$table} WHERE organization_id = :org")
            ->bindValue(':org', $this->orgId)
            ->execute();
        $this->stdout("  - {$label}: удалено {$count} записей\n");
    }

    /**
     * Найти или создать предметы
     */
    private function createSubjects()
    {
        $this->stdout("\nПоиск/создание предметов...\n", Console::FG_CYAN);

        $subjectNames = [
            'Математика',
            'Английский язык',
            'Программирование (Python)',
            'Русский язык',
            'Подготовка к ЕНТ',
            'Физика',
            'Химия',
            'Казахский язык',
        ];

        foreach ($subjectNames as $i => $name) {
            // Ищем существующий предмет
            $subject = Subject::find()
                ->where(['name' => $name])
                ->andWhere(['is_deleted' => 0])
                ->one();

            if (!$subject) {
                // Создаем новый
                $subject = new Subject();
                $subject->name = $name;
                $subject->order_col = $i + 1;
                if (!$subject->save(false)) {
                    throw new \Exception("Не удалось создать предмет: " . print_r($subject->errors, true));
                }
                $this->stdout("  + Создан: {$name}\n");
            } else {
                $this->stdout("  = Найден: {$name}\n");
            }

            $this->subjects[$name] = $subject;
        }
    }

    /**
     * Создать тарифы
     */
    private function createTariffs()
    {
        $this->stdout("\nСоздание тарифов...\n", Console::FG_CYAN);

        $tariffData = [
            ['name' => 'Групповые (8 занятий)', 'duration' => 3, 'lesson_amount' => 8, 'type' => 1, 'price' => 20000],
            ['name' => 'Групповые (12 занятий)', 'duration' => 3, 'lesson_amount' => 12, 'type' => 1, 'price' => 28000],
            ['name' => 'Индивидуальные (4 занятия)', 'duration' => 3, 'lesson_amount' => 4, 'type' => 1, 'price' => 24000],
            ['name' => 'Индивидуальные (8 занятий)', 'duration' => 3, 'lesson_amount' => 8, 'type' => 1, 'price' => 44000],
            ['name' => 'Интенсив ЕНТ (20 занятий)', 'duration' => 3, 'lesson_amount' => 20, 'type' => 1, 'price' => 45000],
            ['name' => 'VIP подготовка (10 занятий)', 'duration' => 3, 'lesson_amount' => 10, 'type' => 1, 'price' => 60000],
        ];

        foreach ($tariffData as $data) {
            $tariff = new Tariff();
            $tariff->organization_id = $this->orgId;
            $tariff->name = $data['name'];
            $tariff->duration = $data['duration'];
            $tariff->lesson_amount = $data['lesson_amount'];
            $tariff->type = $data['type'];
            $tariff->price = $data['price'];
            $tariff->status = Tariff::STATUS_ACTIVE;
            if (!$tariff->save(false)) {
                throw new \Exception("Не удалось создать тариф: " . print_r($tariff->errors, true));
            }
            $this->tariffs[$data['name']] = $tariff;
            $this->stdout("  + {$data['name']} ({$data['price']} тг)\n");
        }
    }

    /**
     * Создать кабинеты
     */
    private function createRooms()
    {
        $this->stdout("\nСоздание кабинетов...\n", Console::FG_CYAN);

        $roomData = [
            ['name' => 'Главный зал', 'code' => '101', 'capacity' => 30, 'color' => '#6366f1'],
            ['name' => 'Кабинет математики', 'code' => '102', 'capacity' => 15, 'color' => '#22c55e'],
            ['name' => 'Компьютерный класс', 'code' => '103', 'capacity' => 12, 'color' => '#3b82f6'],
            ['name' => 'Кабинет языков', 'code' => '104', 'capacity' => 10, 'color' => '#f97316'],
            ['name' => 'Малый зал', 'code' => '105', 'capacity' => 8, 'color' => '#8b5cf6'],
            ['name' => 'Кабинет физики', 'code' => '106', 'capacity' => 20, 'color' => '#14b8a6'],
            ['name' => 'Лекционный зал', 'code' => '107', 'capacity' => 50, 'color' => '#ec4899'],
            ['name' => 'Кабинет химии', 'code' => '108', 'capacity' => 15, 'color' => '#eab308'],
        ];

        foreach ($roomData as $i => $data) {
            $room = new Room();
            $room->organization_id = $this->orgId;
            $room->name = $data['name'];
            $room->code = $data['code'];
            $room->capacity = $data['capacity'];
            $room->color = $data['color'];
            $room->sort_order = $i + 1;
            if (!$room->save(false)) {
                throw new \Exception("Не удалось создать кабинет: " . print_r($room->errors, true));
            }
            $this->rooms[$data['code']] = $room;
            $this->stdout("  + {$data['code']} - {$data['name']}\n");
        }
    }

    /**
     * Найти или создать преподавателей
     */
    private function createTeachers()
    {
        $this->stdout("\nПоиск/создание преподавателей...\n", Console::FG_CYAN);

        $teacherData = [
            ['fio' => 'Петрова Анна Сергеевна', 'email' => 'petrova@test.kz', 'username' => 'petrova'],
            ['fio' => 'Смирнов Иван Викторович', 'email' => 'smirnov@test.kz', 'username' => 'smirnov'],
            ['fio' => 'Козлова Мария Александровна', 'email' => 'kozlova@test.kz', 'username' => 'kozlova'],
            ['fio' => 'Нурланов Арман Кайратович', 'email' => 'nurlanov@test.kz', 'username' => 'nurlanov'],
            ['fio' => 'Жумабаева Айгуль Ерлановна', 'email' => 'zhumabaeva@test.kz', 'username' => 'zhumabaeva'],
        ];

        foreach ($teacherData as $data) {
            // Ищем существующего пользователя
            $user = User::find()
                ->where(['username' => $data['username']])
                ->one();

            if (!$user) {
                // Создаем нового
                $user = new User();
                $user->username = $data['username'];
                $user->email = $data['email'];
                $user->fio = $data['fio'];
                $user->setPassword('123456');
                $user->generateAuthKey();
                $user->status = User::STATUS_ACTIVE;
                // Используем магический setter из AttributesToInfoTrait
                $user->active_organization_id = $this->orgId;
                if (!$user->save(false)) {
                    throw new \Exception("Не удалось создать пользователя: " . print_r($user->errors, true));
                }
                $this->stdout("  + Создан: {$data['fio']}\n");
            } else {
                $this->stdout("  = Найден: {$data['fio']}\n");
            }

            $this->teachers[$data['fio']] = $user;
        }
    }

    /**
     * Создать группы
     */
    private function createGroups()
    {
        $this->stdout("\nСоздание групп...\n", Console::FG_CYAN);

        $groupData = [
            ['code' => 'MATH-9', 'name' => 'Математика 9 класс', 'subject' => 'Математика', 'type' => Group::TYPE_GROUP, 'category' => 3, 'teacher' => 'Петрова Анна Сергеевна'],
            ['code' => 'MATH-11', 'name' => 'Математика 11 класс', 'subject' => 'Математика', 'type' => Group::TYPE_GROUP, 'category' => 4, 'teacher' => 'Петрова Анна Сергеевна'],
            ['code' => 'ENG-A1', 'name' => 'Английский А1', 'subject' => 'Английский язык', 'type' => Group::TYPE_GROUP, 'category' => null, 'teacher' => 'Смирнов Иван Викторович'],
            ['code' => 'ENG-B1', 'name' => 'Английский B1', 'subject' => 'Английский язык', 'type' => Group::TYPE_GROUP, 'category' => null, 'teacher' => 'Смирнов Иван Викторович'],
            ['code' => 'PY-JR', 'name' => 'Python Junior', 'subject' => 'Программирование (Python)', 'type' => Group::TYPE_GROUP, 'category' => 2, 'teacher' => 'Козлова Мария Александровна'],
            ['code' => 'RUS-10', 'name' => 'Русский язык 10-11', 'subject' => 'Русский язык', 'type' => Group::TYPE_GROUP, 'category' => 4, 'teacher' => 'Смирнов Иван Викторович'],
            ['code' => 'ENT-MF', 'name' => 'ЕНТ Математика+Физика', 'subject' => 'Подготовка к ЕНТ', 'type' => Group::TYPE_GROUP, 'category' => 4, 'teacher' => 'Нурланов Арман Кайратович'],
            ['code' => 'ENT-CH', 'name' => 'ЕНТ Химия+Биология', 'subject' => 'Подготовка к ЕНТ', 'type' => Group::TYPE_GROUP, 'category' => 4, 'teacher' => 'Жумабаева Айгуль Ерлановна'],
            ['code' => 'IND-1', 'name' => 'Индивид. Иванов А.', 'subject' => 'Английский язык', 'type' => Group::TYPE_INDIVIDUAL, 'category' => null, 'teacher' => 'Смирнов Иван Викторович'],
            ['code' => 'IND-2', 'name' => 'Индивид. Петрова М.', 'subject' => 'Математика', 'type' => Group::TYPE_INDIVIDUAL, 'category' => null, 'teacher' => 'Петрова Анна Сергеевна'],
        ];

        foreach ($groupData as $data) {
            $group = new Group();
            $group->organization_id = $this->orgId;
            $group->code = $data['code'];
            $group->name = $data['name'];
            $group->subject_id = $this->subjects[$data['subject']]->id;
            $group->type = $data['type'];
            $group->category_id = $data['category'];
            $group->status = 1;
            $group->color = $this->getRandomColor();
            if (!$group->save(false)) {
                throw new \Exception("Не удалось создать группу: " . print_r($group->errors, true));
            }

            // Создаем связь с учителем
            $teacherGroup = new TeacherGroup();
            $teacherGroup->organization_id = $this->orgId;
            $teacherGroup->related_id = $this->teachers[$data['teacher']]->id;
            $teacherGroup->target_id = $group->id;
            $teacherGroup->type = TeacherGroup::PRICE_TYPE_FIX;
            $teacherGroup->price = 2000;
            if (!$teacherGroup->save(false)) {
                throw new \Exception("Не удалось создать связь учитель-группа: " . print_r($teacherGroup->errors, true));
            }

            $this->groups[$data['code']] = $group;
            $this->stdout("  + {$data['code']} - {$data['name']}\n");
        }
    }

    /**
     * Создать учеников
     */
    private function createPupils()
    {
        $this->stdout("\nСоздание учеников...\n", Console::FG_CYAN);

        // Казахстанские имена
        $lastNames = ['Нурсултанов', 'Касымов', 'Жумабеков', 'Сагынбаев', 'Тулеуов', 'Ахметов', 'Байжанов', 'Сериков', 'Оразов', 'Мухамедов',
            'Кенжебаев', 'Абдрахманов', 'Токаев', 'Бекетов', 'Искаков', 'Садыков', 'Умаров', 'Рахимов', 'Калиев', 'Досанов',
            'Иванова', 'Петрова', 'Сидорова', 'Козлова', 'Смирнова', 'Федорова', 'Волкова', 'Кузнецова', 'Попова', 'Морозова',
            'Соколова', 'Павлова', 'Новикова', 'Орлова', 'Андреева'];

        $firstNamesMale = ['Арман', 'Нурлан', 'Даулет', 'Ерлан', 'Асет', 'Канат', 'Мурат', 'Сергей', 'Алексей', 'Дмитрий', 'Андрей', 'Максим', 'Иван', 'Николай', 'Александр'];
        $firstNamesFemale = ['Айгуль', 'Динара', 'Гульнар', 'Асель', 'Жанар', 'Мадина', 'Анна', 'Мария', 'Елена', 'Ольга', 'Наталья', 'Екатерина', 'Светлана', 'Татьяна', 'Юлия'];

        $middleNamesMale = ['Ерланович', 'Нурланович', 'Кайратович', 'Асетович', 'Маратович', 'Сергеевич', 'Александрович', 'Дмитриевич', 'Андреевич', 'Николаевич'];
        $middleNamesFemale = ['Ерлановна', 'Нурлановна', 'Кайратовна', 'Асетовна', 'Маратовна', 'Сергеевна', 'Александровна', 'Дмитриевна', 'Андреевна', 'Николаевна'];

        for ($i = 1; $i <= 35; $i++) {
            $sex = $i <= 18 ? 1 : 2; // 1 - муж, 2 - жен
            $firstName = $sex == 1 ? $firstNamesMale[array_rand($firstNamesMale)] : $firstNamesFemale[array_rand($firstNamesFemale)];
            $lastName = $lastNames[array_rand($lastNames)];
            $middleName = $sex == 1 ? $middleNamesMale[array_rand($middleNamesMale)] : $middleNamesFemale[array_rand($middleNamesFemale)];

            // Генерируем случайный ИИН (12 цифр)
            $birthYear = rand(2005, 2015);
            $birthMonth = str_pad(rand(1, 12), 2, '0', STR_PAD_LEFT);
            $birthDay = str_pad(rand(1, 28), 2, '0', STR_PAD_LEFT);
            $iin = substr($birthYear, 2) . $birthMonth . $birthDay . str_pad(rand(100000, 999999), 6, '0', STR_PAD_LEFT);

            $pupil = new Pupil();
            $pupil->organization_id = $this->orgId;
            $pupil->iin = $iin;
            $pupil->last_name = $lastName;
            $pupil->first_name = $firstName;
            $pupil->middle_name = $middleName;
            $pupil->sex = $sex;
            $pupil->birth_date = "{$birthDay}.{$birthMonth}.{$birthYear}";
            $pupil->phone = '+7' . rand(700, 799) . rand(1000000, 9999999);
            $pupil->status = Pupil::STATUS_ACTIVE;
            $pupil->balance = rand(-5000, 50000);

            if (!$pupil->save(false)) {
                throw new \Exception("Не удалось создать ученика: " . print_r($pupil->errors, true));
            }

            $this->pupils[] = $pupil;
            $this->stdout("  + {$lastName} {$firstName} (баланс: {$pupil->balance} тг)\n");
        }
    }

    /**
     * Создать обучения (зачисления в группы)
     */
    private function createEducations()
    {
        $this->stdout("\nСоздание обучений (зачислений)...\n", Console::FG_CYAN);

        // Распределяем учеников по группам
        $groupAssignments = [
            'MATH-9' => [0, 1, 2, 3, 4],           // 5 учеников
            'MATH-11' => [5, 6, 7, 8],              // 4 ученика
            'ENG-A1' => [9, 10, 11, 12, 13, 14],   // 6 учеников
            'ENG-B1' => [15, 16, 17, 18],          // 4 ученика
            'PY-JR' => [19, 20, 21, 22, 23],       // 5 учеников
            'RUS-10' => [24, 25, 26],              // 3 ученика
            'ENT-MF' => [27, 28, 29, 30],          // 4 ученика
            'ENT-CH' => [31, 32],                   // 2 ученика
            'IND-1' => [33],                        // 1 ученик
            'IND-2' => [34],                        // 1 ученик
        ];

        $tariffNames = array_keys($this->tariffs);

        foreach ($groupAssignments as $groupCode => $pupilIndexes) {
            $group = $this->groups[$groupCode];
            $isIndividual = $group->type == Group::TYPE_INDIVIDUAL;

            foreach ($pupilIndexes as $index) {
                if (!isset($this->pupils[$index])) continue;

                $pupil = $this->pupils[$index];

                // Выбираем подходящий тариф
                $tariffName = $isIndividual ? 'Индивидуальные (4 занятия)' : 'Групповые (8 занятий)';
                $tariff = $this->tariffs[$tariffName];

                // Создаем обучение
                $education = new PupilEducation();
                $education->organization_id = $this->orgId;
                $education->pupil_id = $pupil->id;
                $education->tariff_id = $tariff->id;
                $education->date_start = '2026-01-01';
                $education->date_end = '2026-01-31';
                $education->tariff_price = $tariff->price;
                $education->sale = rand(0, 1) ? rand(5, 20) : 0;
                $education->total_price = $education->tariff_price * (100 - $education->sale) / 100;

                if (!$education->save(false)) {
                    throw new \Exception("Не удалось создать обучение: " . print_r($education->errors, true));
                }

                // Связь обучение-группа
                $educationGroup = new EducationGroup();
                $educationGroup->organization_id = $this->orgId;
                $educationGroup->education_id = $education->id;
                $educationGroup->group_id = $group->id;
                $educationGroup->pupil_id = $pupil->id;
                $educationGroup->subject_id = $group->subject_id;

                if (!$educationGroup->save(false)) {
                    throw new \Exception("Не удалось создать связь обучение-группа: " . print_r($educationGroup->errors, true));
                }
            }

            $this->stdout("  + {$groupCode}: " . count($pupilIndexes) . " учеников\n");
        }
    }

    /**
     * Создать платежи
     */
    private function createPayments()
    {
        $this->stdout("\nСоздание платежей...\n", Console::FG_CYAN);

        $paymentCount = 0;

        foreach ($this->pupils as $pupil) {
            // 1-3 платежа на ученика
            $numPayments = rand(1, 3);

            for ($i = 0; $i < $numPayments; $i++) {
                $payment = new Payment();
                $payment->organization_id = $this->orgId;
                $payment->pupil_id = $pupil->id;
                $payment->type = Payment::TYPE_PAY;
                $payment->purpose_id = Payment::PURPOSE_EDUCATION;
                $payment->amount = rand(10000, 50000);
                $payment->date = '2026-01-' . str_pad(rand(1, 7), 2, '0', STR_PAD_LEFT);
                $payment->comment = 'Тестовый платеж';

                if (!$payment->save(false)) {
                    throw new \Exception("Не удалось создать платеж: " . print_r($payment->errors, true));
                }
                $paymentCount++;
            }
        }

        $this->stdout("  + Создано {$paymentCount} платежей\n");
    }

    /**
     * Создать шаблон расписания
     */
    private function createScheduleTemplate()
    {
        $this->stdout("\nСоздание шаблона расписания...\n", Console::FG_CYAN);

        $template = new ScheduleTemplate();
        $template->organization_id = $this->orgId;
        $template->name = 'Основное расписание январь';
        $template->description = 'Расписание на январь 2026';
        $template->color = '#6366f1';
        $template->is_default = 1;
        $template->is_active = 1;

        if (!$template->save(false)) {
            throw new \Exception("Не удалось создать шаблон расписания: " . print_r($template->errors, true));
        }

        $this->scheduleTemplate = $template;
        $this->stdout("  + {$template->name}\n");
    }

    /**
     * @var ScheduleTemplate
     */
    private $scheduleTemplate;

    /**
     * Создать типовое расписание
     */
    private function createTypicalSchedule()
    {
        $this->stdout("\nСоздание типового расписания...\n", Console::FG_CYAN);

        // week: 1=Пн, 2=Вт, 3=Ср, 4=Чт, 5=Пт, 6=Сб, 7=Вс
        $scheduleData = [
            // Понедельник
            ['week' => 1, 'start' => '15:00', 'end' => '16:30', 'group' => 'PY-JR', 'room' => '103'],
            ['week' => 1, 'start' => '16:00', 'end' => '17:30', 'group' => 'MATH-9', 'room' => '102'],
            ['week' => 1, 'start' => '18:00', 'end' => '19:30', 'group' => 'ENG-A1', 'room' => '104'],

            // Вторник
            ['week' => 2, 'start' => '15:00', 'end' => '16:30', 'group' => 'RUS-10', 'room' => '104'],
            ['week' => 2, 'start' => '17:00', 'end' => '19:00', 'group' => 'ENT-MF', 'room' => '101'],

            // Среда
            ['week' => 3, 'start' => '15:00', 'end' => '16:30', 'group' => 'PY-JR', 'room' => '103'],
            ['week' => 3, 'start' => '16:00', 'end' => '17:30', 'group' => 'MATH-9', 'room' => '102'],
            ['week' => 3, 'start' => '18:00', 'end' => '19:30', 'group' => 'ENG-B1', 'room' => '104'],

            // Четверг
            ['week' => 4, 'start' => '15:00', 'end' => '16:30', 'group' => 'ENT-CH', 'room' => '108'],
            ['week' => 4, 'start' => '17:00', 'end' => '19:00', 'group' => 'ENT-MF', 'room' => '101'],

            // Пятница
            ['week' => 5, 'start' => '10:00', 'end' => '11:00', 'group' => 'IND-1', 'room' => '105'],
            ['week' => 5, 'start' => '15:00', 'end' => '16:30', 'group' => 'MATH-11', 'room' => '102'],
            ['week' => 5, 'start' => '17:00', 'end' => '19:00', 'group' => 'ENT-CH', 'room' => '108'],

            // Суббота
            ['week' => 6, 'start' => '10:00', 'end' => '11:30', 'group' => 'MATH-9', 'room' => '102'],
            ['week' => 6, 'start' => '10:00', 'end' => '11:00', 'group' => 'IND-2', 'room' => '105'],
            ['week' => 6, 'start' => '12:00', 'end' => '13:30', 'group' => 'ENG-A1', 'room' => '104'],
            ['week' => 6, 'start' => '14:00', 'end' => '15:30', 'group' => 'PY-JR', 'room' => '103'],
        ];

        $daysRu = [1 => 'Пн', 2 => 'Вт', 3 => 'Ср', 4 => 'Чт', 5 => 'Пт', 6 => 'Сб', 7 => 'Вс'];

        foreach ($scheduleData as $data) {
            $group = $this->groups[$data['group']];
            $room = $this->rooms[$data['room']];

            // Находим учителя группы
            $teacherGroup = TeacherGroup::find()
                ->where(['target_id' => $group->id, 'organization_id' => $this->orgId])
                ->one();

            $ts = new TypicalSchedule();
            $ts->organization_id = $this->orgId;
            $ts->template_id = $this->scheduleTemplate->id;
            $ts->group_id = $group->id;
            $ts->teacher_id = $teacherGroup ? $teacherGroup->related_id : null;
            $ts->room_id = $room->id;
            $ts->week = $data['week'];
            $ts->start_time = $data['start'];
            $ts->end_time = $data['end'];
            // date используется как техническое поле для хранения даты недели
            $ts->date = '2024-01-0' . $data['week'];

            if (!$ts->save(false)) {
                throw new \Exception("Не удалось создать типовое расписание: " . print_r($ts->errors, true));
            }

            $this->stdout("  + {$daysRu[$data['week']]} {$data['start']}-{$data['end']}: {$data['group']}\n");
        }
    }

    /**
     * Сгенерировать занятия на январь
     */
    private function generateLessons()
    {
        $this->stdout("\nГенерация занятий на январь 2026...\n", Console::FG_CYAN);

        // Получаем все типовые расписания
        $typicalSchedules = TypicalSchedule::find()
            ->where(['organization_id' => $this->orgId])
            ->andWhere(['is_deleted' => 0])
            ->all();

        $lessonCount = 0;
        $today = new \DateTime('2026-01-07'); // Текущая дата

        // Генерируем занятия с 1 по 31 января
        $startDate = new \DateTime('2026-01-01');
        $endDate = new \DateTime('2026-01-31');

        while ($startDate <= $endDate) {
            $dayOfWeek = (int) $startDate->format('N'); // 1=Пн, 7=Вс

            foreach ($typicalSchedules as $ts) {
                if ($ts->week != $dayOfWeek) continue;

                $lesson = new Lesson();
                $lesson->organization_id = $this->orgId;
                $lesson->group_id = $ts->group_id;
                $lesson->teacher_id = $ts->teacher_id;
                $lesson->room_id = $ts->room_id;
                $lesson->typical_schedule_id = $ts->id;
                $lesson->week = $dayOfWeek;
                $lesson->start_time = $ts->start_time;
                $lesson->end_time = $ts->end_time;
                $lesson->date = $startDate->format('Y-m-d');

                // Занятия до 7 января - завершены, остальные - запланированы
                $lesson->status = ($startDate < $today) ? Lesson::STATUS_FINISHED : Lesson::STATUS_PLANED;

                if (!$lesson->save(false)) {
                    throw new \Exception("Не удалось создать занятие: " . print_r($lesson->errors, true));
                }

                $lessonCount++;
            }

            $startDate->modify('+1 day');
        }

        $this->stdout("  + Создано {$lessonCount} занятий\n");
    }

    /**
     * Создать посещаемость для завершённых занятий
     */
    private function createAttendance()
    {
        $this->stdout("\nСоздание посещаемости...\n", Console::FG_CYAN);

        // Получаем все завершённые занятия
        $lessons = Lesson::find()
            ->where(['organization_id' => $this->orgId])
            ->andWhere(['status' => Lesson::STATUS_FINISHED])
            ->andWhere(['is_deleted' => 0])
            ->all();

        $attendanceCount = 0;

        foreach ($lessons as $lesson) {
            // Получаем учеников группы
            $educationGroups = EducationGroup::find()
                ->where(['group_id' => $lesson->group_id, 'organization_id' => $this->orgId])
                ->andWhere(['is_deleted' => 0])
                ->all();

            foreach ($educationGroups as $eg) {
                // Определяем статус посещения
                $rand = rand(1, 100);
                if ($rand <= 85) {
                    $status = LessonAttendance::STATUS_VISIT;
                } elseif ($rand <= 95) {
                    $status = LessonAttendance::STATUS_MISS_VALID_REASON;
                } else {
                    $status = LessonAttendance::STATUS_MISS_WITHOUT_PAY;
                }

                $attendance = new LessonAttendance();
                $attendance->organization_id = $this->orgId;
                $attendance->lesson_id = $lesson->id;
                $attendance->pupil_id = $eg->pupil_id;
                $attendance->teacher_id = $lesson->teacher_id;
                $attendance->status = $status;

                if (!$attendance->save(false)) {
                    // Пропускаем дубликаты
                    continue;
                }

                $attendanceCount++;
            }
        }

        $this->stdout("  + Создано {$attendanceCount} записей посещаемости\n");
    }

    /**
     * Получить случайный цвет
     */
    private function getRandomColor()
    {
        $colors = ['#6366f1', '#8b5cf6', '#ec4899', '#f43f5e', '#f97316', '#eab308', '#22c55e', '#14b8a6', '#06b6d4', '#3b82f6'];
        return $colors[array_rand($colors)];
    }
}
