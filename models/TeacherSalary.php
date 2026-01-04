<?php

namespace app\models;

use app\components\ActiveRecord;
use app\traits\UpdateInsteadOfDeleteTrait;
use Yii;
use yii\db\Expression;

/**
 * Модель начисленных зарплат учителей
 *
 * @property int $id
 * @property int $organization_id
 * @property int $teacher_id
 * @property string $period_start
 * @property string $period_end
 * @property int $lessons_count
 * @property int $students_count
 * @property float $base_amount
 * @property float $bonus_amount
 * @property float $deduction_amount
 * @property float $total_amount
 * @property int $status
 * @property int|null $approved_by
 * @property string|null $approved_at
 * @property string|null $paid_at
 * @property string|null $notes
 * @property string|null $details
 * @property int $is_deleted
 * @property string $created_at
 * @property string $updated_at
 *
 * @property User $teacher
 * @property User $approver
 * @property TeacherSalaryDetail[] $salaryDetails
 */
class TeacherSalary extends ActiveRecord
{
    use UpdateInsteadOfDeleteTrait;

    // Статусы зарплаты
    const STATUS_DRAFT = 1;     // Черновик / Расчёт
    const STATUS_APPROVED = 2;  // Утверждена
    const STATUS_PAID = 3;      // Выплачена

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%teacher_salary}}';
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            [
                'class' => \yii\behaviors\TimestampBehavior::class,
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
                'value' => (new Expression('NOW()')),
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['teacher_id', 'period_start', 'period_end'], 'required'],
            [['organization_id', 'teacher_id', 'lessons_count', 'students_count', 'status', 'approved_by', 'is_deleted'], 'integer'],
            [['base_amount', 'bonus_amount', 'deduction_amount', 'total_amount'], 'number'],
            [['period_start', 'period_end', 'approved_at', 'paid_at'], 'safe'],
            [['notes', 'details'], 'string'],
            [['teacher_id'], 'exist', 'targetClass' => User::class, 'targetAttribute' => 'id'],
            ['status', 'in', 'range' => [self::STATUS_DRAFT, self::STATUS_APPROVED, self::STATUS_PAID]],
            ['status', 'default', 'value' => self::STATUS_DRAFT],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'organization_id' => 'Организация',
            'teacher_id' => 'Преподаватель',
            'period_start' => 'Начало периода',
            'period_end' => 'Конец периода',
            'lessons_count' => 'Уроков',
            'students_count' => 'Учеников',
            'base_amount' => 'Базовая сумма',
            'bonus_amount' => 'Бонусы',
            'deduction_amount' => 'Вычеты',
            'total_amount' => 'Итого',
            'status' => 'Статус',
            'approved_by' => 'Утвердил',
            'approved_at' => 'Дата утверждения',
            'paid_at' => 'Дата выплаты',
            'notes' => 'Примечания',
            'details' => 'Детали',
            'is_deleted' => 'Удалена',
            'created_at' => 'Создано',
            'updated_at' => 'Обновлено',
        ];
    }

    /**
     * Связь с преподавателем
     */
    public function getTeacher()
    {
        return $this->hasOne(User::class, ['id' => 'teacher_id']);
    }

    /**
     * Связь с утвердившим
     */
    public function getApprover()
    {
        return $this->hasOne(User::class, ['id' => 'approved_by']);
    }

    /**
     * Связь с деталями
     */
    public function getSalaryDetails()
    {
        return $this->hasMany(TeacherSalaryDetail::class, ['salary_id' => 'id']);
    }

    /**
     * Список статусов
     */
    public static function getStatusList()
    {
        return [
            self::STATUS_DRAFT => 'Расчёт',
            self::STATUS_APPROVED => 'Утверждена',
            self::STATUS_PAID => 'Выплачена',
        ];
    }

    /**
     * Название статуса
     */
    public function getStatusLabel()
    {
        $list = self::getStatusList();
        return $list[$this->status] ?? 'Неизвестно';
    }

    /**
     * CSS класс для бейджа статуса
     */
    public function getStatusBadgeClass()
    {
        $classes = [
            self::STATUS_DRAFT => 'bg-secondary',
            self::STATUS_APPROVED => 'bg-warning',
            self::STATUS_PAID => 'bg-success',
        ];
        return $classes[$this->status] ?? 'bg-secondary';
    }

    /**
     * Период в читаемом формате
     */
    public function getPeriodLabel()
    {
        return Yii::$app->formatter->asDate($this->period_start, 'php:d.m.Y') .
            ' - ' .
            Yii::$app->formatter->asDate($this->period_end, 'php:d.m.Y');
    }

    /**
     * Форматированная сумма
     */
    public function getFormattedTotal()
    {
        return number_format($this->total_amount, 0, ',', ' ') . ' ₸';
    }

    /**
     * Утвердить зарплату
     */
    public function approve()
    {
        if ($this->status !== self::STATUS_DRAFT) {
            return false;
        }

        $this->status = self::STATUS_APPROVED;
        $this->approved_by = Yii::$app->user->id;
        $this->approved_at = date('Y-m-d H:i:s');

        return $this->save(false);
    }

    /**
     * Отметить как выплаченную
     */
    public function markAsPaid()
    {
        if ($this->status !== self::STATUS_APPROVED) {
            return false;
        }

        $this->status = self::STATUS_PAID;
        $this->paid_at = date('Y-m-d H:i:s');

        return $this->save(false);
    }

    /**
     * Вернуть в черновик
     */
    public function revertToDraft()
    {
        if ($this->status === self::STATUS_PAID) {
            return false;
        }

        $this->status = self::STATUS_DRAFT;
        $this->approved_by = null;
        $this->approved_at = null;

        return $this->save(false);
    }

    /**
     * Рассчитать зарплату за период
     */
    public static function calculate($teacherId, $periodStart, $periodEnd)
    {
        $organizationId = Organizations::getCurrentOrganizationId();

        // Проверяем, нет ли уже зарплаты за этот период
        $existing = self::find()
            ->byOrganization()
            ->andWhere([
                'teacher_id' => $teacherId,
                'period_start' => $periodStart,
                'period_end' => $periodEnd,
            ])
            ->notDeleted()
            ->one();

        if ($existing) {
            return $existing;
        }

        // Получаем все уроки учителя за период
        $lessons = Lesson::find()
            ->byOrganization()
            ->andWhere(['teacher_id' => $teacherId])
            ->andWhere(['>=', 'date', $periodStart])
            ->andWhere(['<=', 'date', $periodEnd])
            ->andWhere(['status' => Lesson::STATUS_FINISHED])
            ->notDeleted()
            ->all();

        $baseAmount = 0;
        $lessonsCount = 0;
        $studentsCount = 0;
        $details = [];

        foreach ($lessons as $lesson) {
            // Получаем посещения с оплатой
            $paidAttendances = LessonAttendance::find()
                ->andWhere([
                    'lesson_id' => $lesson->id,
                    'teacher_id' => $teacherId,
                ])
                ->andWhere(['in', 'status', [
                    LessonAttendance::STATUS_VISIT,
                    LessonAttendance::STATUS_MISS_WITH_PAY
                ]])
                ->notDeleted()
                ->count();

            if ($paidAttendances == 0) {
                continue;
            }

            $lessonsCount++;
            $studentsCount += $paidAttendances;

            // Находим ставку для учителя
            $group = $lesson->group;
            $subjectId = $group ? $group->subject_id : null;
            $rate = TeacherRate::findRate($teacherId, $subjectId, $lesson->group_id);

            $lessonAmount = 0;
            $rateType = TeacherRate::RATE_PER_STUDENT;
            $rateValue = 0;

            if ($rate) {
                $rateType = $rate->rate_type;
                $rateValue = $rate->rate_value;

                switch ($rate->rate_type) {
                    case TeacherRate::RATE_PER_STUDENT:
                        $lessonAmount = $paidAttendances * $rate->rate_value;
                        break;
                    case TeacherRate::RATE_PER_LESSON:
                        $lessonAmount = $rate->rate_value;
                        break;
                    case TeacherRate::RATE_PERCENT:
                        // Для процентной ставки нужно знать сумму оплаты учеников
                        // Пока используем упрощённый расчёт
                        $lessonAmount = $paidAttendances * $rate->rate_value;
                        break;
                }
            }

            $baseAmount += $lessonAmount;

            $details[] = [
                'lesson_id' => $lesson->id,
                'lesson_date' => $lesson->date,
                'group_id' => $lesson->group_id,
                'group_name' => $group ? $group->name : '',
                'students_paid' => $paidAttendances,
                'rate_type' => $rateType,
                'rate_value' => $rateValue,
                'amount' => $lessonAmount,
            ];
        }

        // Создаём запись о зарплате
        $salary = new self();
        $salary->organization_id = $organizationId;
        $salary->teacher_id = $teacherId;
        $salary->period_start = $periodStart;
        $salary->period_end = $periodEnd;
        $salary->lessons_count = $lessonsCount;
        $salary->students_count = $studentsCount;
        $salary->base_amount = $baseAmount;
        $salary->bonus_amount = 0;
        $salary->deduction_amount = 0;
        $salary->total_amount = $baseAmount;
        $salary->status = self::STATUS_DRAFT;
        $salary->details = json_encode($details, JSON_UNESCAPED_UNICODE);

        if ($salary->save()) {
            // Сохраняем детали
            foreach ($details as $detail) {
                $salaryDetail = new TeacherSalaryDetail();
                $salaryDetail->salary_id = $salary->id;
                $salaryDetail->lesson_id = $detail['lesson_id'];
                $salaryDetail->group_id = $detail['group_id'];
                $salaryDetail->students_paid = $detail['students_paid'];
                $salaryDetail->rate_type = $detail['rate_type'];
                $salaryDetail->rate_value = $detail['rate_value'];
                $salaryDetail->amount = $detail['amount'];
                $salaryDetail->lesson_date = $detail['lesson_date'];
                $salaryDetail->save();
            }
        }

        return $salary;
    }

    /**
     * Пересчитать зарплату
     */
    public function recalculate()
    {
        if ($this->status !== self::STATUS_DRAFT) {
            return false;
        }

        // Удаляем старые детали
        TeacherSalaryDetail::deleteAll(['salary_id' => $this->id]);

        // Получаем все уроки учителя за период
        $lessons = Lesson::find()
            ->byOrganization()
            ->andWhere(['teacher_id' => $this->teacher_id])
            ->andWhere(['>=', 'date', $this->period_start])
            ->andWhere(['<=', 'date', $this->period_end])
            ->andWhere(['status' => Lesson::STATUS_FINISHED])
            ->notDeleted()
            ->all();

        $baseAmount = 0;
        $lessonsCount = 0;
        $studentsCount = 0;
        $details = [];

        foreach ($lessons as $lesson) {
            $paidAttendances = LessonAttendance::find()
                ->andWhere([
                    'lesson_id' => $lesson->id,
                    'teacher_id' => $this->teacher_id,
                ])
                ->andWhere(['in', 'status', [
                    LessonAttendance::STATUS_VISIT,
                    LessonAttendance::STATUS_MISS_WITH_PAY
                ]])
                ->notDeleted()
                ->count();

            if ($paidAttendances == 0) {
                continue;
            }

            $lessonsCount++;
            $studentsCount += $paidAttendances;

            $group = $lesson->group;
            $subjectId = $group ? $group->subject_id : null;
            $rate = TeacherRate::findRate($this->teacher_id, $subjectId, $lesson->group_id);

            $lessonAmount = 0;
            $rateType = TeacherRate::RATE_PER_STUDENT;
            $rateValue = 0;

            if ($rate) {
                $rateType = $rate->rate_type;
                $rateValue = $rate->rate_value;

                switch ($rate->rate_type) {
                    case TeacherRate::RATE_PER_STUDENT:
                        $lessonAmount = $paidAttendances * $rate->rate_value;
                        break;
                    case TeacherRate::RATE_PER_LESSON:
                        $lessonAmount = $rate->rate_value;
                        break;
                    case TeacherRate::RATE_PERCENT:
                        $lessonAmount = $paidAttendances * $rate->rate_value;
                        break;
                }
            }

            $baseAmount += $lessonAmount;

            // Сохраняем деталь
            $salaryDetail = new TeacherSalaryDetail();
            $salaryDetail->salary_id = $this->id;
            $salaryDetail->lesson_id = $lesson->id;
            $salaryDetail->group_id = $lesson->group_id;
            $salaryDetail->students_paid = $paidAttendances;
            $salaryDetail->rate_type = $rateType;
            $salaryDetail->rate_value = $rateValue;
            $salaryDetail->amount = $lessonAmount;
            $salaryDetail->lesson_date = $lesson->date;
            $salaryDetail->save();

            $details[] = [
                'lesson_id' => $lesson->id,
                'lesson_date' => $lesson->date,
                'group_id' => $lesson->group_id,
                'group_name' => $group ? $group->name : '',
                'students_paid' => $paidAttendances,
                'rate_type' => $rateType,
                'rate_value' => $rateValue,
                'amount' => $lessonAmount,
            ];
        }

        $this->lessons_count = $lessonsCount;
        $this->students_count = $studentsCount;
        $this->base_amount = $baseAmount;
        $this->total_amount = $baseAmount + $this->bonus_amount - $this->deduction_amount;
        $this->details = json_encode($details, JSON_UNESCAPED_UNICODE);

        return $this->save(false);
    }
}
