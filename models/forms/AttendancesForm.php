<?php

namespace app\models\forms;

use app\models\Lesson;
use app\models\LessonAttendance;
use app\models\services\AttendanceService;
use Yii;
use yii\base\Model;

/**
 * Форма для редактирования посещаемости занятия
 */
class AttendancesForm extends Model
{
    /** @var array Ученики занятия */
    public $pupils;

    /** @var int ID занятия */
    public $lessonId;

    /** @var array [pupil_id => LessonAttendance] */
    public $attendances;

    /** @var array [pupil_id => ['status' => int]] */
    public $statuses;

    /** @var Lesson */
    private $lesson;

    /** @var AttendanceService */
    private $service;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            ['statuses', 'safe'],
        ];
    }

    public function __construct($config = [])
    {
        $this->lessonId = Yii::$app->request->get('id');
        $this->service = new AttendanceService();
        parent::__construct($config);
    }

    public function init()
    {
        $this->loadDefaultValues();
        parent::init();
    }

    /**
     * Загрузить данные занятия и посещаемости
     */
    public function loadDefaultValues(): void
    {
        $this->lesson = Lesson::findOne($this->lessonId);

        if (!$this->lesson) {
            return;
        }

        $this->pupils = $this->lesson->getPupils();
        $this->attendances = $this->service->getOrCreateAttendances($this->lesson);

        $this->statuses = [];
        foreach ($this->pupils as $pupil) {
            $attendance = $this->attendances[$pupil->id] ?? null;
            $this->statuses[$pupil->id]['status'] = $attendance ? $attendance->status : null;
        }
    }

    /**
     * Сохранить посещаемость
     *
     * @return bool
     */
    public function save(): bool
    {
        if (!$this->validate()) {
            return false;
        }

        if (!$this->lesson) {
            $this->lesson = Lesson::findOne($this->lessonId);
        }

        if (!$this->lesson) {
            Yii::error("AttendancesForm::save - Lesson not found: {$this->lessonId}", 'application');
            return false;
        }

        return $this->service->saveAttendances($this->lesson, $this->statuses);
    }
}
