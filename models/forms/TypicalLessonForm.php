<?php


namespace app\models\forms;


use app\models\Lesson;
use app\models\TypicalSchedule;
use yii\base\BaseObject;
use yii\base\Model;

class TypicalLessonForm extends Model
{

    public $date_start;
    public $date_end;
    public $weeks;

    public function loadDefault(){
        $this->date_start = date("d.m.Y", strtotime('monday this week'));
        $this->date_end = date("d.m.Y", strtotime('sunday this week'));

        $this->weeks = [
            1 => ['is_copy' => true, 'week' => 1],
            2 => ['is_copy' => true, 'week' => 2],
            3 => ['is_copy' => true, 'week' => 3],
            4 => ['is_copy' => true, 'week' => 4],
            5 => ['is_copy' => true, 'week' => 5],
            6 => ['is_copy' => true, 'week' => 6],
            7 => ['is_copy' => true, 'week' => 7],
        ];
    }

    public function rules()
    {
        return [
            [['weeks', 'date_start', 'date_end'], 'safe']
        ];
    }

    public function save(){
        if (!$this->validate()){
            return false;
        }
        $transaction = \Yii::$app->db->beginTransaction();
        foreach ($this->weeks as $k => $week){
            if ($week['is_copy'] == 1){
                $typicalSchedules = TypicalSchedule::find()->where(['week' => $week['week']])->byOrganization()->all();
                $date = date('Y-m-d', strtotime($this->date_start) + (($k - 1) * 24 * 60 * 60));
                foreach ($typicalSchedules as $typicalSchedule){
                    $lesson = new Lesson();
                    $lesson->date = $date;
                    $lesson->group_id = $typicalSchedule->group_id;
                    $lesson->teacher_id = $typicalSchedule->teacher_id;
                    $lesson->week = $week['week'];
                    $lesson->start_time = $typicalSchedule->start_time;
                    $lesson->end_time = $typicalSchedule->end_time;
                    if(!$lesson->save(false)){
                       $transaction->rollBack();
                       return false;
                    }
                }

            }
        }
        $transaction->commit();

        return true;
    }

    public function attributeLabels()
    {
        return [
            'weeks[1]' => 'Понедельник',
        ];
    }

}