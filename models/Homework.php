<?php

namespace app\models;

use app\components\ActiveRecord;
use app\traits\UpdateInsteadOfDeleteTrait;
use Yii;
use yii\db\Expression;
use yii\helpers\Json;

/**
 * Модель домашнего задания
 *
 * @property int $id
 * @property int $group_id ID группы
 * @property int|null $lesson_id ID урока (опционально)
 * @property string $title Название задания
 * @property string|null $description Описание задания
 * @property string $due_date Срок сдачи
 * @property string|null $attachments JSON массив прикреплённых файлов
 * @property int $status Статус задания
 * @property int $created_by ID создателя (учителя)
 * @property int $organization_id
 * @property int $is_deleted
 * @property string $created_at
 * @property string $updated_at
 *
 * @property Group $group
 * @property Lesson $lesson
 * @property User $creator
 * @property HomeworkSubmission[] $submissions
 */
class Homework extends ActiveRecord
{
    use UpdateInsteadOfDeleteTrait;

    // Статусы задания
    const STATUS_DRAFT = 0;     // Черновик
    const STATUS_ACTIVE = 1;    // Активное
    const STATUS_CLOSED = 2;    // Закрыто (приём закончен)
    const STATUS_ARCHIVED = 3;  // В архиве

    /**
     * @return array[]
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
    public static function tableName()
    {
        return 'homework';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['group_id', 'title', 'due_date'], 'required'],
            [['group_id', 'lesson_id', 'status', 'created_by', 'organization_id', 'is_deleted'], 'integer'],
            [['description'], 'string'],
            [['due_date', 'created_at', 'updated_at'], 'safe'],
            [['title'], 'string', 'max' => 255],
            [['attachments'], 'string'],
            [['status'], 'default', 'value' => self::STATUS_ACTIVE],
            [['is_deleted'], 'default', 'value' => 0],
            [['group_id'], 'exist', 'skipOnError' => true, 'targetClass' => Group::class, 'targetAttribute' => ['group_id' => 'id']],
            [['lesson_id'], 'exist', 'skipOnError' => true, 'targetClass' => Lesson::class, 'targetAttribute' => ['lesson_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'group_id' => Yii::t('app', 'Группа'),
            'lesson_id' => Yii::t('app', 'Урок'),
            'title' => Yii::t('app', 'Название'),
            'description' => Yii::t('app', 'Описание'),
            'due_date' => Yii::t('app', 'Срок сдачи'),
            'attachments' => Yii::t('app', 'Файлы'),
            'status' => Yii::t('app', 'Статус'),
            'created_by' => Yii::t('app', 'Создал'),
            'created_at' => Yii::t('app', 'Создано'),
            'updated_at' => Yii::t('app', 'Обновлено'),
        ];
    }

    /**
     * Связь с группой
     */
    public function getGroup()
    {
        return $this->hasOne(Group::class, ['id' => 'group_id']);
    }

    /**
     * Связь с уроком
     */
    public function getLesson()
    {
        return $this->hasOne(Lesson::class, ['id' => 'lesson_id']);
    }

    /**
     * Связь с создателем
     */
    public function getCreator()
    {
        return $this->hasOne(User::class, ['id' => 'created_by']);
    }

    /**
     * Связь с ответами
     */
    public function getSubmissions()
    {
        return $this->hasMany(HomeworkSubmission::class, ['homework_id' => 'id']);
    }

    /**
     * Список статусов
     */
    public static function getStatusList(): array
    {
        return [
            self::STATUS_DRAFT => Yii::t('app', 'Черновик'),
            self::STATUS_ACTIVE => Yii::t('app', 'Активное'),
            self::STATUS_CLOSED => Yii::t('app', 'Закрыто'),
            self::STATUS_ARCHIVED => Yii::t('app', 'В архиве'),
        ];
    }

    /**
     * Получить название статуса
     */
    public function getStatusLabel(): string
    {
        return self::getStatusList()[$this->status] ?? Yii::t('app', 'Неизвестно');
    }

    /**
     * Получить CSS-класс для статуса
     */
    public function getStatusClass(): string
    {
        $classes = [
            self::STATUS_DRAFT => 'bg-gray-100 text-gray-700',
            self::STATUS_ACTIVE => 'bg-green-100 text-green-700',
            self::STATUS_CLOSED => 'bg-yellow-100 text-yellow-700',
            self::STATUS_ARCHIVED => 'bg-gray-100 text-gray-500',
        ];
        return $classes[$this->status] ?? 'bg-gray-100 text-gray-700';
    }

    /**
     * Получить массив прикреплённых файлов
     */
    public function getAttachmentsList(): array
    {
        if (empty($this->attachments)) {
            return [];
        }
        try {
            return Json::decode($this->attachments);
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Установить список прикреплённых файлов
     */
    public function setAttachmentsList(array $files): void
    {
        $this->attachments = Json::encode($files);
    }

    /**
     * Добавить файл к вложениям
     */
    public function addAttachment(string $filename, string $path): void
    {
        $files = $this->getAttachmentsList();
        $files[] = [
            'name' => $filename,
            'path' => $path,
            'uploaded_at' => date('Y-m-d H:i:s'),
        ];
        $this->setAttachmentsList($files);
    }

    /**
     * Проверить, просрочено ли задание
     */
    public function isOverdue(): bool
    {
        return strtotime($this->due_date) < strtotime('today');
    }

    /**
     * Проверить, можно ли сдать задание
     */
    public function canSubmit(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * Получить количество сданных работ
     */
    public function getSubmittedCount(): int
    {
        return HomeworkSubmission::find()
            ->where(['homework_id' => $this->id])
            ->andWhere(['in', 'status', [
                HomeworkSubmission::STATUS_SUBMITTED,
                HomeworkSubmission::STATUS_CHECKED,
            ]])
            ->count();
    }

    /**
     * Получить количество проверенных работ
     */
    public function getCheckedCount(): int
    {
        return HomeworkSubmission::find()
            ->where(['homework_id' => $this->id])
            ->andWhere(['status' => HomeworkSubmission::STATUS_CHECKED])
            ->count();
    }

    /**
     * Получить количество учеников в группе
     */
    public function getStudentsCount(): int
    {
        if (!$this->group) {
            return 0;
        }
        // Используем Query вместо ActiveRecord, т.к. education_group не имеет is_deleted
        return (new \yii\db\Query())
            ->from('education_group eg')
            ->innerJoin('pupil_education pe', 'pe.id = eg.education_id')
            ->where(['eg.group_id' => $this->group_id])
            ->andWhere(['eg.is_deleted' => 0])
            ->andWhere(['pe.is_deleted' => 0])
            ->count();
    }

    /**
     * Получить ответ ученика
     */
    public function getSubmissionByPupil(int $pupilId): ?HomeworkSubmission
    {
        return HomeworkSubmission::find()
            ->where(['homework_id' => $this->id, 'pupil_id' => $pupilId])
            ->one();
    }

    /**
     * Перед сохранением
     */
    public function beforeSave($insert)
    {
        if ($insert && !$this->created_by) {
            $this->created_by = Yii::$app->user->id;
        }
        return parent::beforeSave($insert);
    }
}
