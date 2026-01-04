<?php
/* @var \app\models\User[] $teachers*/
/* @var array $teacherLessons*/
/* @var array $lessonPupilSalary*/
?>

<?foreach ($teachers as $teacher):?>
<?
$teacherSalary = 0;
?>
<div class="card border-success mb-3">

    <div class="card-body">
        <h5 class="card-title"><?=$teacher['fio'];?></h5>

        <table class="table">
            <tbody>
            <?foreach ($teacherLessons[$teacher['id']] as $lesson):?>
            <?
                $lessonSalary = 0;
            ?>
            <tr>
                <td style="width: 50%;">
                    <p style="background-color: <?=$lesson->group->color;?>; font-weight: bold; color: white;" class="p-1 mb-0">
                    <?if($lesson->status == \app\models\Lesson::STATUS_FINISHED):?>
                        <i class="fa fa-calendar-check-o" aria-hidden="true" style="font-size:17px;"></i>
                    <?else:?>
                        <i class="fa fa-calendar-times-o" aria-hidden="true" style="font-size:17px;"></i>
                    <?endif;?>
                    <?=$lesson->group->getNameFull();?>
                    </p>
                    <p class="mb-0"><span class="font-weight-bold">Группа: </span><?=$lesson->group->getNameFull();?></p>
                    <p class="mb-0"><span class="font-weight-bold">Дата: </span><?=$lesson->getDateTime();?></p>
                    <p class="mb-0"><span class="font-weight-bold">Предмет: </span><?=$lesson->group->subject->name;?></p>
                </td>
                <td style="width: 50%;">
                    <table class="table table-borderless">
                        <tbody>
                        <?foreach ($lesson->getPupils() as $i => $pupil):?>
                        <tr>
                            <td style="padding-right: 25px;">
                                <span class="npp"><?=$i+1;?></span>
                                <span style="color: green;"><?=$pupil->fio;?></span>
                            </td>
                            <td style="width: 40%;">
                                <?=$lessonPupilSalary[$lesson['id']][$pupil['id']];?> тг
                            </td>
                        </tr>
                        <?
                            $teacherSalary += $lessonPupilSalary[$lesson['id']][$pupil['id']];
                            $lessonSalary += $lessonPupilSalary[$lesson['id']][$pupil['id']];
                        ?>
                        <?endforeach;?>
                        <tr>
                            <td style="padding-right: 25px;">
                                <span class="npp"></span>
                                <span style="font-weight: bold;">Итого:</span>
                            </td>
                            <td style="width: 40%;">
                                <span style="font-weight: bold;"><?=$lessonSalary;?> тг</span>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </td>
            </tr>
            <?endforeach;?>
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-transparent border-success font-weight-bold">
        Итого: <?=number_format($teacherSalary, 0, '.', ' ');?> тг
    </div>
</div>
<?endforeach;?>
