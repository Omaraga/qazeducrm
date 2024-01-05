<?php
/** @var yii\web\View $this */
/** @var \app\models\User[] $teachers */
/** @var \app\models\search\DateSearch $searchModel */
/** @var array $dataArray */
/** @var integer $type */
$this->title = Yii::t('main', 'Дневной отчет');
setlocale(LC_ALL, 'russian');
?>
<h3><?=$this->title;?></h3>

<?php  echo $this->render('_search', ['model' => $searchModel, 'onlyMonth' => false]); ?>
<hr class="my-2">
<ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
    <li class="nav-item" role="presentation">
        <a href="<?=\app\helpers\OrganizationUrl::to(['reports/day', 'type' => 1, 'DateSearch[date]' => $searchModel->date]);?>" class="nav-link <?=$type == 1 ?'active':'';?>" id="pills-home-tab" type="button" role="tab" aria-controls="pills-home" aria-selected="true"><?=Yii::t('main', 'Посещаемость по группам');?></a>
    </li>
    <li class="nav-item" role="presentation">
        <a href="<?=\app\helpers\OrganizationUrl::to(['reports/day', 'type' => 2, 'DateSearch[date]' => $searchModel->date]);?>" class="nav-link <?=$type == 2 ?'active':'';?>" id="pills-profile-tab" type="button" role="tab" aria-controls="pills-profile" aria-selected="false"><?=Yii::t('main', 'Оплата преподавателям');?></a>
    </li>
    <li class="nav-item" role="presentation">
        <a href="<?=\app\helpers\OrganizationUrl::to(['reports/day', 'type' => 3, 'DateSearch[date]' => $searchModel->date]);?>" class="nav-link <?=$type == 3 ?'active':'';?>" id="pills-contact-tab" type="button" role="tab" aria-controls="pills-contact" aria-selected="false"><?=Yii::t('main', 'Принятые платежы');?></a>
    </li>
</ul>
<div class="tab-content" id="pills-tabContent">
    <div class="tab-pane fade <?=$type == 1 ?'show active':'';?>" id="pills-home" role="tabpanel" aria-labelledby="pills-home-tab">
        <?if($type == 1):?>
            <?if(sizeof($dataArray['lessons']) > 0):?>
            <?=$this->render('day/attendance', [
                'lessons' => $dataArray['lessons'],
                'attendances' => $dataArray['attendances']
            ]);?>
            <?else:?>
                <p style="font-weight: bold; font-size: 20px;"><?=$searchModel->date;?> г. нет ни одного занятия</p>
            <?endif;?>
        <?endif;?>
    </div>
    <div class="tab-pane fade <?=$type == 2 ?'show active':'';?>" id="pills-profile" role="tabpanel" aria-labelledby="pills-profile-tab">
        <?if($type == 2):?>
            <?if(sizeof($dataArray['teachers']) > 0):?>
                <?=$this->render('day/teacher', [
                    'teachers' => $dataArray['teachers'],
                    'teacherLessons' => $dataArray['teacherLessons'],
                    'lessonPupilSalary' => $dataArray['lessonPupilSalary']
                ]);?>
            <?else:?>
                <p style="font-weight: bold; font-size: 20px;"><?=$searchModel->date;?> г. нет ни одного занятия</p>
            <?endif;?>
        <?endif;?>
    </div>
    <div class="tab-pane fade <?=$type == 3 ?'show active':'';?>" id="pills-contact" role="tabpanel" aria-labelledby="pills-contact-tab">
        <?if($type == 3):?>
            <?if(sizeof($dataArray) > 0):?>
                <?=$this->render('day/payment', [
                    'payments' => $dataArray,
                ]);?>
            <?else:?>
                <p style="font-weight: bold; font-size: 20px;"><?=$searchModel->date;?> г. нет ни одной оплаты</p>
            <?endif;?>
        <?endif;?>
    </div>
</div>
