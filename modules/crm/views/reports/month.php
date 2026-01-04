<?php
/** @var yii\web\View $this */
/** @var \app\models\User[] $teachers */
/** @var \app\models\search\DateSearch $searchModel */
/** @var array $dataArray */
/** @var integer $type */
use app\models\search\DateSearch;
$this->title = Yii::t('main', 'Отчет за месяц');
$onlyMonth = true;
if ($type == DateSearch::TYPE_ATTENDANCE){
    $this->title = 'Статистика посещаемости занятий.';
    $onlyMonth = false;
}else if($type == DateSearch::TYPE_SALARY){
    $this->title = $this->title.'. Зарпалата преподавателей.';
}else if($type == DateSearch::TYPE_PAYMENT){
    $this->title = $this->title.'. Приход.';
}else if($type == DateSearch::TYPE_PUPIL_PAYMENT){
    $this->title = $this->title.'. Оплата и задолженность по ученикам.';
}
setlocale(LC_ALL, 'russian');
?>
<h3 id="report-title"><?=$this->title;?></h3>

<?php  echo $this->render('_search', ['model' => $searchModel, 'onlyMonth' => $onlyMonth]); ?>
<hr class="my-2">
<div class="tab-content" id="pills-tabContent">
    <div class="tab-pane fade show active" id="pills-home" role="tabpanel" aria-labelledby="pills-home-tab">
        <?if($type == DateSearch::TYPE_ATTENDANCE):?>
            <?if(sizeof($dataArray['lessons']) > 0):?>
            <?=$this->render('day/attendance', [
                'lessons' => $dataArray['lessons'],
                'attendances' => $dataArray['attendances']
            ]);?>
            <?else:?>
                <p style="font-weight: bold; font-size: 20px;"><?=$searchModel->date;?> г. нет ни одного занятия</p>
            <?endif;?>
        <?elseif($type == DateSearch::TYPE_SALARY):?>
            <?if(sizeof($dataArray['teachers']) > 0):?>
                <?=$this->render('day/teacher', [
                    'teachers' => $dataArray['teachers'],
                    'teacherLessons' => $dataArray['teacherLessons'],
                    'lessonPupilSalary' => $dataArray['lessonPupilSalary']
                ]);?>
            <?else:?>
                <p style="font-weight: bold; font-size: 20px;"><?=$searchModel->date;?> г. нет ни одного занятия</p>
            <?endif;?>
        <?elseif($type == DateSearch::TYPE_PAYMENT):?>
            <?if(sizeof($dataArray) > 0):?>
                <?=$this->render('day/payment', [
                    'payments' => $dataArray,
                ]);?>
            <?else:?>
                <p style="font-weight: bold; font-size: 20px;"><?=$searchModel->date;?> г. нет ни одной оплаты</p>
            <?endif;?>
        <?elseif($type == DateSearch::TYPE_PUPIL_PAYMENT):?>
            <?=$this->render('day/pupil_payment', [
                'pupils' => $dataArray['pupils'],
                'pupilPupilEducations' => $dataArray['pupilPupilEducations'],
                'pupilPayments' => $dataArray['pupilPayments']
            ]);?>

        <?endif;?>
    </div>
</div>
