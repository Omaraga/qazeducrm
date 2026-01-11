<?php

namespace app\models;

use app\components\ActiveRecord;
use Yii;
use yii\db\Expression;
use yii\helpers\Json;

/**
 * Модель ответа на домашнее задание
 *
 * @property int $id
 * @property int $homework_id ID задания
 * @property int $pupil_id ID ученика
 * @property int $status Статус ответа
 * @property string|null $answer Текст ответа
 * @property string|null $files JSON массив файлов ученика
 * @property string|null $submitted_at Дата сдачи
 * @property int|null $grade Оценка
 * @property string|null $comment Комментарий учителя
 * @property int|null $checked_by ID проверившего
 * @property string|null $checked_at Дата проверки
 * @property int $organization_id
 * @property string $created_at
 * @property string $updated_at
 *
 * @property Homework $homework
 * @property Pupil $pupil
 * @property User $checker
 */
class HomeworkSubmission extends ActiveRecord
{
    // Статусы ответа
    const STATUS_PENDING = 0;    // Не сдано
    const STATUS_SUBMITTED = 1;  // Сдано, ожидает проверки
    const STATUS_CHECKED = 2;    // Проверено
    const STATUS_RETURNED = 3;   // Возвращено на доработку

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
        return 'homework_submission';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['homework_id', 'pupil_id'], 'required'],
            [['homework_id', 'pupil_id', 'status', 'grade', 'checked_by', 'organization_id'], 'integer'],
            [['answer', 'comment'], 'string'],
            [['files'], 'string'],
            [['submitted_at', 'checked_at', 'created_at', 'updated_at'], 'safe'],
            [['status'], 'default', 'value' => self::STATUS_PENDING],
            [['grade'], 'in', 'range' => range(1, 10)],
            [['homework_id'], 'exist', 'skipOnError' => true, 'targetClass' => Homework::class, 'targetAttribute' => ['homework_id' => 'id']],
            [['pupil_id'], 'exist', 'skipOnError' => true, 'targetClass' => Pupil::class, 'targetAttribute' => ['pupil_id' => 'id']],
            // Уникальность: один ученик - один ответ на задание
            [['pupil_id'], 'unique', 'targetAttribute' => ['homework_id', 'pupil_id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'homework_id' => Yii::t('app', 'Задание'),
            'pupil_id' => Yii::t('app', 'Ученик'),
            'status' => Yii::t('app', 'Статус'),
            'answer' => Yii::t('app', 'Ответ'),
            'files' => Yii::t('app', 'Файлы'),
            'submitted_at' => Yii::t('app', 'Дата сдачи'),
            'grade' => Yii::t('app', 'Оценка'),
            'comment' => Yii::t('app', 'Комментарий'),
            'checked_by' => Yii::t('app', 'Проверил'),
            'checked_at' => Yii::t('app', 'Дата проверки'),
        ];
    }

    /**
     * Связь с заданием
     */
    public function getHomework()
    {
        return $this->hasOne(Homework::class, ['id' => 'homework_id']);
    }

    /**
     * Связь с учеником
     */
    public function getPupil()
    {
        return $this->hasOne(Pupil::class, ['id' => 'pupil_id']);
    }

    /**
     * Связь с проверяющим
     */
    public function getChecker()
    {
        return $this->hasOne(User::class, ['id' => 'checked_by']);
    }

    /**
     * Список статусов
     */
    public static function getStatusList(): array
    {
        return [
            self::STATUS_PENDING => Yii::t('app', 'Не сдано'),
            self::STATUS_SUBMITTED => Yii::t('app', 'На проверке'),
            self::STATUS_CHECKED => Yii::t('app', 'Проверено'),
            self::STATUS_RETURNED => Yii::t('app', 'На доработку'),
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
            self::STATUS_PENDING => 'bg-gray-100 text-gray-700',
            self::STATUS_SUBMITTED => 'bg-blue-100 text-blue-700',
            self::STATUS_CHECKED => 'bg-green-100 text-green-700',
            self::STATUS_RETURNED => 'bg-orange-100 text-orange-700',
        ];
        return $classes[$this->status] ?? 'bg-gray-100 text-gray-700';
    }

    /**
     * Получить массив файлов
     */
    public function getFilesList(): array
    {
        if (empty($this->files)) {
            return [];
        }
        try {
            return Json::decode($this->files);
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Установить список файлов
     */
    public function setFilesList(array $files): void
    {
        $this->files = Json::encode($files);
    }

    /**
     * Добавить файл
     */
    public function addFile(string $filename, string $path): void
    {
        $files = $this->getFilesList();
        $files[] = [
            'name' => $filename,
            'path' => $path,
            'uploaded_at' => date('Y-m-d H:i:s'),
        ];
        $this->setFilesList($files);
    }

    /**
     * Сдать работу
     */
    public function submit(?string $answer = null): bool
    {
        $this->answer = $answer;
        $this->status = self::STATUS_SUBMITTED;
        $this->submitted_at = date('Y-m-d H:i:s');
        return $this->save();
    }

    /**
     * Проверить работу
     */
    public function check(int $grade, ?string $comment = null): bool
    {
        $this->grade = $grade;
        $this->comment = $comment;
        $this->status = self::STATUS_CHECKED;
        $this->checked_by = Yii::$app->user->id;
        $this->checked_at = date('Y-m-d H:i:s');
        return $this->save();
    }

    /**
     * Вернуть на доработку
     */
    public function returnForRevision(string $comment): bool
    {
        $this->comment = $comment;
        $this->status = self::STATUS_RETURNED;
        $this->checked_by = Yii::$app->user->id;
        $this->checked_at = date('Y-m-d H:i:s');
        return $this->save();
    }

    /**
     * Проверить, сдано ли задание
     */
    public function isSubmitted(): bool
    {
        return in_array($this->status, [self::STATUS_SUBMITTED, self::STATUS_CHECKED]);
    }

    /**
     * Проверить, проверено ли задание
     */
    public function isChecked(): bool
    {
        return $this->status === self::STATUS_CHECKED;
    }

    /**
     * Получить или создать ответ для ученика
     */
    public static function getOrCreate(int $homeworkId, int $pupilId): self
    {
        $submission = self::find()
            ->where(['homework_id' => $homeworkId, 'pupil_id' => $pupilId])
            ->one();

        if (!$submission) {
            $homework = Homework::findOne($homeworkId);
            $submission = new self();
            $submission->homework_id = $homeworkId;
            $submission->pupil_id = $pupilId;
            $submission->organization_id = $homework ? $homework->organization_id : null;
            $submission->save(false);
        }

        return $submission;
    }
}
