<?php

namespace app\models;

use app\components\ActiveRecord;
use app\traits\UpdateInsteadOfDeleteTrait;
use Yii;
use yii\db\Expression;

/**
 * Модель пробного занятия
 *
 * @property int $id
 * @property int $lid_id ID лида
 * @property int|null $group_id ID группы (куда записан на пробное)
 * @property string $date Дата пробного занятия
 * @property string $time Время начала
 * @property int $status Статус пробного занятия
 * @property string|null $feedback Отзыв/комментарий после пробного
 * @property int|null $rating Оценка пробного (1-5)
 * @property string|null $reminder_sent Дата отправки напоминания
 * @property int|null $converted_pupil_id ID ученика (если конвертирован)
 * @property int $organization_id
 * @property int $is_deleted
 * @property string $created_at
 * @property string $updated_at
 *
 * @property Lids $lid
 * @property Group $group
 * @property Pupil $convertedPupil
 */
class TrialLesson extends ActiveRecord
{
    use UpdateInsteadOfDeleteTrait;

    // Статусы пробного занятия
    const STATUS_PLANNED = 1;      // Запланировано
    const STATUS_CONFIRMED = 2;    // Подтверждено (лид подтвердил участие)
    const STATUS_COMPLETED = 3;    // Проведено
    const STATUS_NO_SHOW = 4;      // Не пришёл
    const STATUS_CANCELLED = 5;    // Отменено
    const STATUS_CONVERTED = 6;    // Конвертирован в ученика

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
        return 'trial_lesson';
    }

    /**
     * Конвертация даты из dd.mm.yyyy в yyyy-mm-dd перед сохранением
     */
    public function beforeValidate(): bool
    {
        if ($this->date && preg_match('/^(\d{2})\.(\d{2})\.(\d{4})$/', $this->date, $m)) {
            $this->date = "{$m[3]}-{$m[2]}-{$m[1]}";
        }
        return parent::beforeValidate();
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['lid_id', 'date', 'time'], 'required'],
            [['lid_id', 'group_id', 'status', 'rating', 'converted_pupil_id', 'organization_id', 'is_deleted'], 'integer'],
            [['date', 'time', 'reminder_sent', 'created_at', 'updated_at'], 'safe'],
            [['feedback'], 'string', 'max' => 1000],
            [['rating'], 'in', 'range' => [1, 2, 3, 4, 5]],
            [['status'], 'default', 'value' => self::STATUS_PLANNED],
            [['is_deleted'], 'default', 'value' => 0],
            [['lid_id'], 'exist', 'skipOnError' => true, 'targetClass' => Lids::class, 'targetAttribute' => ['lid_id' => 'id']],
            [['group_id'], 'exist', 'skipOnError' => true, 'targetClass' => Group::class, 'targetAttribute' => ['group_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'lid_id' => Yii::t('app', 'Лид'),
            'group_id' => Yii::t('app', 'Группа'),
            'date' => Yii::t('app', 'Дата'),
            'time' => Yii::t('app', 'Время'),
            'status' => Yii::t('app', 'Статус'),
            'feedback' => Yii::t('app', 'Отзыв'),
            'rating' => Yii::t('app', 'Оценка'),
            'reminder_sent' => Yii::t('app', 'Напоминание отправлено'),
            'converted_pupil_id' => Yii::t('app', 'Конвертирован в ученика'),
            'created_at' => Yii::t('app', 'Создано'),
            'updated_at' => Yii::t('app', 'Обновлено'),
        ];
    }

    /**
     * Связь с лидом
     * @return \yii\db\ActiveQuery
     */
    public function getLid()
    {
        return $this->hasOne(Lids::class, ['id' => 'lid_id']);
    }

    /**
     * Связь с группой
     * @return \yii\db\ActiveQuery
     */
    public function getGroup()
    {
        return $this->hasOne(Group::class, ['id' => 'group_id']);
    }

    /**
     * Связь с учеником (после конвертации)
     * @return \yii\db\ActiveQuery
     */
    public function getConvertedPupil()
    {
        return $this->hasOne(Pupil::class, ['id' => 'converted_pupil_id']);
    }

    /**
     * Список статусов
     * @return array
     */
    public static function getStatusList(): array
    {
        return [
            self::STATUS_PLANNED => Yii::t('app', 'Запланировано'),
            self::STATUS_CONFIRMED => Yii::t('app', 'Подтверждено'),
            self::STATUS_COMPLETED => Yii::t('app', 'Проведено'),
            self::STATUS_NO_SHOW => Yii::t('app', 'Не пришёл'),
            self::STATUS_CANCELLED => Yii::t('app', 'Отменено'),
            self::STATUS_CONVERTED => Yii::t('app', 'Конвертирован'),
        ];
    }

    /**
     * Получить название статуса
     * @return string
     */
    public function getStatusLabel(): string
    {
        return self::getStatusList()[$this->status] ?? Yii::t('app', 'Неизвестно');
    }

    /**
     * Получить CSS-класс для статуса
     * @return string
     */
    public function getStatusClass(): string
    {
        $classes = [
            self::STATUS_PLANNED => 'bg-blue-100 text-blue-700',
            self::STATUS_CONFIRMED => 'bg-yellow-100 text-yellow-700',
            self::STATUS_COMPLETED => 'bg-green-100 text-green-700',
            self::STATUS_NO_SHOW => 'bg-red-100 text-red-700',
            self::STATUS_CANCELLED => 'bg-gray-100 text-gray-700',
            self::STATUS_CONVERTED => 'bg-purple-100 text-purple-700',
        ];

        return $classes[$this->status] ?? 'bg-gray-100 text-gray-700';
    }

    /**
     * Получить ФИО лида
     * @return string
     */
    public function getLidName(): string
    {
        if ($this->lid) {
            return $this->lid->fio ?: ($this->lid->parent_fio ?: Yii::t('app', 'Без имени'));
        }
        return '—';
    }

    /**
     * Получить телефон лида
     * @return string
     */
    public function getLidPhone(): string
    {
        if ($this->lid) {
            return $this->lid->phone ?: $this->lid->parent_phone ?: '';
        }
        return '';
    }

    /**
     * Проверить, можно ли редактировать пробное
     * @return bool
     */
    public function canEdit(): bool
    {
        return in_array($this->status, [self::STATUS_PLANNED, self::STATUS_CONFIRMED]);
    }

    /**
     * Проверить, завершено ли пробное
     * @return bool
     */
    public function isFinished(): bool
    {
        return in_array($this->status, [
            self::STATUS_COMPLETED,
            self::STATUS_NO_SHOW,
            self::STATUS_CANCELLED,
            self::STATUS_CONVERTED,
        ]);
    }

    /**
     * Пометить как проведённое
     * @param string|null $feedback
     * @param int|null $rating
     * @return bool
     */
    public function markAsCompleted(?string $feedback = null, ?int $rating = null): bool
    {
        $this->status = self::STATUS_COMPLETED;
        $this->feedback = $feedback;
        $this->rating = $rating;
        return $this->save();
    }

    /**
     * Пометить как "не пришёл"
     * @return bool
     */
    public function markAsNoShow(): bool
    {
        $this->status = self::STATUS_NO_SHOW;
        return $this->save();
    }

    /**
     * Пометить как отменённое
     * @param string|null $reason
     * @return bool
     */
    public function markAsCancelled(?string $reason = null): bool
    {
        $this->status = self::STATUS_CANCELLED;
        if ($reason) {
            $this->feedback = $reason;
        }
        return $this->save();
    }

    /**
     * Пометить как конвертирован
     * @param int $pupilId
     * @return bool
     */
    public function markAsConverted(int $pupilId): bool
    {
        $this->status = self::STATUS_CONVERTED;
        $this->converted_pupil_id = $pupilId;
        return $this->save();
    }

    /**
     * Получить пробные на сегодня
     * @return array
     */
    public static function getTodayTrials(): array
    {
        return self::find()
            ->with(['lid', 'group'])
            ->where(['date' => date('Y-m-d')])
            ->andWhere(['in', 'status', [self::STATUS_PLANNED, self::STATUS_CONFIRMED]])
            ->orderBy(['time' => SORT_ASC])
            ->all();
    }

    /**
     * Получить предстоящие пробные
     * @param int $days Количество дней вперёд
     * @return array
     */
    public static function getUpcomingTrials(int $days = 7): array
    {
        return self::find()
            ->with(['lid', 'group'])
            ->where(['>=', 'date', date('Y-m-d')])
            ->andWhere(['<=', 'date', date('Y-m-d', strtotime("+{$days} days"))])
            ->andWhere(['in', 'status', [self::STATUS_PLANNED, self::STATUS_CONFIRMED]])
            ->orderBy(['date' => SORT_ASC, 'time' => SORT_ASC])
            ->all();
    }

    /**
     * Получить статистику пробных занятий
     * @param string|null $dateFrom
     * @param string|null $dateTo
     * @return array
     */
    public static function getStatistics(?string $dateFrom = null, ?string $dateTo = null): array
    {
        $query = self::find()
            ->where(['is_deleted' => 0]);

        if ($dateFrom) {
            $query->andWhere(['>=', 'date', $dateFrom]);
        }
        if ($dateTo) {
            $query->andWhere(['<=', 'date', $dateTo]);
        }

        $total = (int) (clone $query)->count();
        $completed = (int) (clone $query)->andWhere(['status' => self::STATUS_COMPLETED])->count();
        $converted = (int) (clone $query)->andWhere(['status' => self::STATUS_CONVERTED])->count();
        $noShow = (int) (clone $query)->andWhere(['status' => self::STATUS_NO_SHOW])->count();
        $cancelled = (int) (clone $query)->andWhere(['status' => self::STATUS_CANCELLED])->count();

        $conversionRate = $completed > 0 ? round($converted / $completed * 100, 1) : 0;
        $noShowRate = $total > 0 ? round($noShow / $total * 100, 1) : 0;

        return [
            'total' => $total,
            'completed' => $completed,
            'converted' => $converted,
            'no_show' => $noShow,
            'cancelled' => $cancelled,
            'conversion_rate' => $conversionRate,
            'no_show_rate' => $noShowRate,
        ];
    }
}
