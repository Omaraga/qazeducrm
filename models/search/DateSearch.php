<?php

namespace app\models\search;

use app\components\ActiveRecord;
use app\helpers\OrganizationRoles;
use app\models\Lesson;
use app\models\LessonAttendance;
use app\models\Organizations;
use app\models\Pupil;
use app\models\PupilEducation;
use app\models\relations\TeacherGroup;
use app\models\Tariff;
use app\models\User;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Payment;
use yii\helpers\ArrayHelper;

/**
 * PaymentSearch represents the model behind the search form of `app\models\Payment`.
 */
class DateSearch extends Model
{
    const TYPE_ATTENDANCE = 1;
    const TYPE_SALARY = 2;
    const TYPE_PAYMENT = 3;
    const TYPE_PUPIL_PAYMENT = 4;
    public $date;
    public $query;
    public $date_start;
    public $date_end;

    public $type;
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['date'], 'safe'],
        ];
    }

    public function __construct($query = null, $config = [])
    {
        $this->query = $query;
        $this->date = date('d.m.Y');
        parent::__construct($config);
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = $this->query;

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 20
            ],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        $this->date_start = '01'.substr($this->date, 2);
        $this->date_end = date('t',strtotime($this->date)).'.'.substr($this->date, 3);
        if ($this->date_start){
            $query->andFilterWhere(['>=', 'date', date('Y-m-d H:i', strtotime($this->date_start))]);
        }


        return $dataProvider;
    }

    public function searchEmployer($params){
        $this->load($params);

        $dateTeacherSalary = [];


        if (!$this->validate()) {
            return $dateTeacherSalary;
        }

        $this->date_start = '01'.substr($this->date, 2);
        $this->date_end = date('t',strtotime($this->date)).'.'.substr($this->date, 3);
        $teachers = User::find()->innerJoinWith(['currentUserOrganizations' => function($q){
            $q->andWhere(['<>','user_organization.is_deleted', ActiveRecord::DELETED])->andWhere(['in', 'user_organization.role', [OrganizationRoles::TEACHER]]);
        }])->all();
        for ($iDateTime = strtotime($this->date_start); $iDateTime <= strtotime($this->date_end); $iDateTime += 24 * 60 * 60){
            $date = date('d.m.Y', $iDateTime);
            foreach ($teachers as $teacher){
                $dateTeacherSalary[$date][$teacher->id] = 0;

            }

        }
        $pupilEducations = PupilEducation::find()->innerJoinWith(['groups' => function($q){
            $q->andWhere(['<>','education_group.is_deleted', 1]);
        }])->andWhere(['pupil_education.organization_id' => Organizations::getCurrentOrganizationId()])
            ->andWhere(['<=', 'pupil_education.date_start', date('Y-m-d', strtotime($this->date_start))])
            ->andWhere(['>=', 'pupil_education.date_end', date('Y-m-d', strtotime($this->date_start))])
            ->notDeleted(PupilEducation::tableName())->orderBy('pupil_education.date_start ASC')->asArray()->all();
        $pupilEducationArr = [];
        foreach ($pupilEducations as $pupilEducation){
            $pupilEducationArr[$pupilEducation['pupil_id']][] = [
                'sale' => $pupilEducation['sale'],
                'total_price' => $pupilEducation['total_price'],
                'tariff_price' => $pupilEducation['tariff_price'],
                'date_start' => $pupilEducation['date_start'],
                'date_end' => $pupilEducation['date_end'],
                'tariff_id' => $pupilEducation['tariff_id']
            ];
        }


        $lessonAttendances = LessonAttendance::find()->innerJoinWith(['lesson' => function($q){
            $q->andWhere(['<>', 'lesson.is_deleted', 1]);
        }])->andWhere(['>=','lesson.date', date('Y-m-d', strtotime($this->date_start))])
            ->andWhere(['<=','lesson.date', date('Y-m-d', strtotime($this->date_end))])
            ->andWhere(['lesson.status' => Lesson::STATUS_FINISHED])->notDeleted(LessonAttendance::tableName())->orderBy('lesson.date ASC')->all();

        foreach ($lessonAttendances as $lessonAttendance){
            if (in_array($lessonAttendance->status, [LessonAttendance::STATUS_VISIT,LessonAttendance::STATUS_MISS_WITH_PAY ])){
                $teacherGroup = TeacherGroup::find()->where(['related_id' => $lessonAttendance->lesson->teacher_id, 'target_id' => $lessonAttendance->lesson->group_id])
                    ->notDeleted()->asArray()->one();
                if (!$teacherGroup){
                    continue;
                }
                $lessonTime = strtotime($lessonAttendance->lesson->date);
                $pupil = $lessonAttendance->pupil;
                $totalSum = 0;
                $education = null;
                if (key_exists($pupil->id, $pupilEducationArr) && sizeof($pupilEducationArr[$pupil->id]) > 1){
                    for ($i = 1; $i < sizeof($pupilEducationArr[$pupil->id]); $i++){
                        $startTime = strtotime($pupilEducationArr[$pupil->id][$i]['date_start']);
                        $endTime = strtotime($pupilEducationArr[$pupil->id][$i]['date_end']);
                        if ($lessonTime >= $startTime && $lessonTime <= $endTime){
                            $education = $pupilEducationArr[$pupil->id][$i];
                            break;
                        }
                    }
                }else if (key_exists($pupil->id, $pupilEducationArr) && sizeof($pupilEducationArr[$pupil->id]) == 1){
                    $education = $pupilEducationArr[$pupil->id][0];
                }else{
                    continue;
                }
                if (!$education){
                    continue;
                }
                if ($teacherGroup['type'] == TeacherGroup::PRICE_TYPE_FIX){
                    $sum = $teacherGroup['price'];
                    $sale = $education['sale'];
                    if ($sale > 0){
                        $sum = $sum * (100-$sale) / 100;
                    }
                    $totalSum += $sum;

                }else if($teacherGroup['type'] == TeacherGroup::PRICE_TYPE_PERCENT){
                    $tariff = Tariff::findOne($education['tariff_id']);
                    $lessonCount = 0;
                    foreach ($tariff->subjectsRelation as $subject){
                        $lessonCount += $subject->lesson_amount;
                    }
                    // Защита от деления на ноль
                    if ($lessonCount > 0) {
                        $sum = (($education['total_price'] / ($lessonCount*4.33)) * 50)/100;
                        $totalSum += intval($sum);
                    }

                }
                $dateTeacherSalary[date('d.m.Y', strtotime($lessonAttendance->lesson->date))][$lessonAttendance->lesson->teacher_id] += $totalSum;


            }

        }
        return $dateTeacherSalary;


    }
    public function searchDay($params){
        $this->load($params);

        $dataArray = [];

        if (!$this->validate()) {
            return $dataArray;
        }

        if ($this->type == self::TYPE_ATTENDANCE){
            return $this->getAttendance();
        }else if($this->type == self::TYPE_SALARY){
            $pupilEducations = PupilEducation::find()->innerJoinWith(['groups' => function($q){
                $q->andWhere(['<>','education_group.is_deleted', 1]);
            }])->andWhere(['pupil_education.organization_id' => Organizations::getCurrentOrganizationId()])
                ->andWhere(['<=', 'pupil_education.date_start', date('Y-m-d', strtotime($this->date))])
                ->andWhere(['>=', 'pupil_education.date_end', date('Y-m-d', strtotime($this->date))])
                ->notDeleted(PupilEducation::tableName())->orderBy('pupil_education.date_start ASC')->asArray()->all();
            $pupilEducationArr = [];
            foreach ($pupilEducations as $pupilEducation){
                $pupilEducationArr[$pupilEducation['pupil_id']] = [
                    'sale' => $pupilEducation['sale'],
                    'total_price' => $pupilEducation['total_price'],
                    'tariff_price' => $pupilEducation['tariff_price'],
                    'tariff_id' => $pupilEducation['tariff_id']
                ];
            }

            $lessonPupilSalary = [];
            $teacherLessons = [];
            $lessons = Lesson::find()->andWhere(['lesson.date' => date('Y-m-d', strtotime($this->date))])
                ->notDeleted(Lesson::tableName())->orderBy('lesson.start_time ASC')->all();
            foreach ($lessons as $lesson){
                $teacherLessons[$lesson->teacher_id][] = $lesson;
                $pupils = $lesson->getPupils();
                foreach ($pupils as $pupil){
                    $lessonPupilSalary[$lesson->id][$pupil->id] = 0;
                }
            }

            $lessonAttendances = LessonAttendance::find()->innerJoinWith(['lesson' => function($q){
                $q->andWhere(['<>', 'lesson.is_deleted', 1]);
            }])->andWhere(['=','lesson.date', date('Y-m-d', strtotime($this->date))])
                ->andWhere(['lesson.status' => Lesson::STATUS_FINISHED])->notDeleted(LessonAttendance::tableName())->orderBy('lesson.date ASC')->all();

            foreach ($lessonAttendances as $lessonAttendance){
                if (in_array($lessonAttendance->status, [LessonAttendance::STATUS_VISIT,LessonAttendance::STATUS_MISS_WITH_PAY ])){
                    $teacherGroup = TeacherGroup::find()->where(['related_id' => $lessonAttendance->lesson->teacher_id, 'target_id' => $lessonAttendance->lesson->group_id])
                        ->notDeleted()->asArray()->one();
                    if (!$teacherGroup){
                        continue;
                    }
                    $pupil = $lessonAttendance->pupil;
                    if (!$pupil || !isset($pupilEducationArr[$pupil->id])){
                        continue;
                    }
                    $education = $pupilEducationArr[$pupil->id];
                    $totalSum = 0;
                    if ($teacherGroup['type'] == TeacherGroup::PRICE_TYPE_FIX){
                        $sum = $teacherGroup['price'];
                        $sale = $education['sale'];
                        if ($sale > 0){
                            $sum = $sum * (100-$sale) / 100;
                        }
                        $totalSum += $sum;

                    }else if($teacherGroup['type'] == TeacherGroup::PRICE_TYPE_PERCENT){
                        $tariff = Tariff::findOne($education['tariff_id']);
                        $lessonCount = 0;
                        foreach ($tariff->subjectsRelation as $subject){
                            $lessonCount += $subject->lesson_amount;
                        }
                        // Защита от деления на ноль
                        if ($lessonCount > 0) {
                            $sum = (($education['total_price'] / ($lessonCount*4.33)) * 50)/100;
                            $totalSum += intval($sum);
                        }
                    }
                    $lessonPupilSalary[$lessonAttendance->lesson_id][$pupil->id] = $totalSum;

                }
            }
            $dataArray['teachers'] = User::find()->select('user.id, user.fio')->innerJoinWith(['currentUserOrganizations' => function($q){
                $q->andWhere(['<>','user_organization.is_deleted', ActiveRecord::DELETED])
                    ->andWhere(['in', 'user_organization.role', [OrganizationRoles::TEACHER]]);
            }])->andWhere(['in', 'user.id', array_keys($teacherLessons)])->asArray()->all();
            $dataArray['lessonPupilSalary'] = $lessonPupilSalary;
            $dataArray['teacherLessons'] = $teacherLessons;
            return  $dataArray;

        }else if ($type = self::TYPE_PAYMENT){
            return $this->getPayments($this->date, $this->date);
        }
        return [];

    }

    public function searchMonth($params){
        $this->load($params);

        $dataArray = [];

        if (!$this->validate()) {
            return $dataArray;
        }
        $this->date_start = '01'.substr($this->date, 2);
        $this->date_end = date('t',strtotime($this->date)).'.'.substr($this->date, 3);

        if ($this->type == self::TYPE_ATTENDANCE){
            return $this->getAttendance();
        }else if($this->type == self::TYPE_SALARY){
            return  $this->searchEmployer($params);

        }else if($this->type == self::TYPE_PAYMENT){
            return $this->getPayments($this->date_start, $this->date_end);
        }else if($this->type == self::TYPE_PUPIL_PAYMENT){
            $pupilEducations = PupilEducation::find()
                ->andWhere(['pupil_education.organization_id' => Organizations::getCurrentOrganizationId()])
                ->andWhere(['>=', 'pupil_education.date_start', date('Y-m-d', strtotime($this->date_start))])
                ->andWhere(['<=', 'pupil_education.date_start', date('Y-m-d', strtotime($this->date_end) + 24 * 60 * 60)])
                ->notDeleted(PupilEducation::tableName())->orderBy('pupil_education.date_start ASC')->asArray()->all();
            $pupilPupilEducations = [];
            foreach ($pupilEducations as $pupilEducation){
                $pupilPupilEducations[$pupilEducation['pupil_id']][] = $pupilEducation;
            }
            $payments = $this->getPayments($this->date_start, $this->date_end);
            $pupilPayments = [];
            foreach ($payments as $payment){
                $pupilPayments[$payment->pupil_id][] = $payment;
            }
            $dataArray['pupilPayments'] = $pupilPayments;
            $dataArray['pupilPupilEducations'] = $pupilPupilEducations;
            $dataArray['pupils'] = Pupil::find()->byOrganization()->notDeleted()->orderBy('class_id ASC, fio ASC')->all();
            return $dataArray;
        }
        return [];

    }

    /**
     * @param $start_date
     * @param $endDate
     * @return Payment[]|array|\yii\db\ActiveRecord[]
     */
    public function getPayments($startDate, $endDate){
        return Payment::find()->byOrganization()
            ->andWhere(['is not', 'pupil_id', null])->andWhere(['type' => Payment::TYPE_PAY])
            ->andWhere(['>=', 'date', date('Y-m-d H:i:s', strtotime($startDate))])
            ->andWhere(['<', 'date', date('Y-m-d H:i:s', strtotime($endDate) + 24 * 60 * 60)])->orderBy('date DESC')->all();
    }

    public function getWeekPayments(){
        $week_start = date("Y-m-d", strtotime('monday this week'));
        $week_end = date("Y-m-d", strtotime('sunday this week'));
        $data = [0, 0, 0, 0, 0, 0, 0];
        $j = 0;
        for ($i = strtotime($week_start); $i <= strtotime($week_end); $i += 24 * 60 * 60){
            $date = date('d.m.Y', $i);
            $payments = $this->getPayments($date, $date);

            foreach ($payments as $payment){
                $data[$j] += $payment['amount'];
            }
            $j++;
        }
        return $data;
    }

    public function getWeeks($isString = false){
        $week_start = date("Y-m-d", strtotime('monday this week'));
        $week_end = date("Y-m-d", strtotime('sunday this week'));
        $week = ['Пн','Вт','Ср', 'Чт','Пт','Сб','Вс'];

        $j = 0;
        for ($i = strtotime($week_start); $i <= strtotime($week_end); $i += 24 * 60 * 60){
            $date = date('d.m', $i);
            $week[$j] .= ' '.$date;
            $j++;
        }
        if ($isString){
            $weekStr = "[";
            foreach ($week as $i =>$item){
                if($i == 6){
                    $weekStr.= "'".$item."'";
                }else{
                    $weekStr.= "'".$item."',";
                }
            }
            $weekStr.="]";
            return $weekStr;
        }
        return $week;
    }

    public function getAttendance(){
        $lessonAttendances = LessonAttendance::find()->innerJoinWith(['lesson' => function($q){
            $q->andWhere(['<>', 'lesson.is_deleted', 1]);
        }])->andWhere(['=','lesson.date', date('Y-m-d', strtotime($this->date))])
            ->andWhere(['lesson.status' => Lesson::STATUS_FINISHED])->notDeleted(LessonAttendance::tableName())->orderBy('lesson.date ASC')->andWhere(['lesson_attendance.organization_id' => Organizations::getCurrentOrganizationId()])->all();
        $attendanceArray = [];
        foreach ($lessonAttendances as $lessonAttendance){
            $attendanceArray[$lessonAttendance->lesson_id][$lessonAttendance->pupil_id] = $lessonAttendance->status;
        }
        $dataArray['attendances'] = $attendanceArray;
        $dataArray['lessons'] = Lesson::find()->innerJoinWith(['group' => function($q){
            $q->andWhere(['<>', 'group.is_deleted', 1]);
        }])->andWhere(['lesson.date' => date('Y-m-d', strtotime($this->date))])
            ->notDeleted(Lesson::tableName())->andWhere(['lesson.organization_id' => Organizations::getCurrentOrganizationId()])->orderBy('lesson.start_time ASC')->all();
        return $dataArray;
    }

    public function attributeLabels()
    {
        return [
            'date' => \Yii::t('main', 'Дата'),
        ]; // TODO: Change the autogenerated stub
    }
}
