<?php
/* @var \app\models\Lesson[] $lessons*/
/* @var array $attendances*/
?>

<table class="table">
    <tbody>
    <?foreach ($lessons as $lesson):?>
    <tr>
        <td style="width: 50%;">
            <span style="background-color: <?=$lesson->group->color;?>; font-weight: bold; color: white;" class="p-1">
                <?if($lesson->status == \app\models\Lesson::STATUS_FINISHED):?>
                    <i class="fa fa-calendar-check-o" aria-hidden="true" style="font-size:17px;"></i>
                <?else:?>
                    <i class="fa fa-calendar-times-o" aria-hidden="true" style="font-size:17px;"></i>
                <?endif;?>
                <?=$lesson->group->getNameFull();?>
            </span> <br>
            <p class="mb-0"><span class="font-weight-bold">Группа: </span><?=$lesson->group->getNameFull();?></p>
            <p class="mb-0"><span class="font-weight-bold">Дата: </span><?=$lesson->getDateTime();?></p>
            <p class="mb-0"><span class="font-weight-bold">Преподаватель: </span><?=$lesson->teacher->fio;?></p>
            <p class="mb-0"><span class="font-weight-bold">Предмет: </span><?=$lesson->group->subject->name;?></p>
            <br>
            <a class="badge badge-primary" href="<?=\app\helpers\OrganizationUrl::to(['attendance/lesson', 'id' => $lesson->id]);?>" target="_blank">Редактировать посещения</a>
        </td>
        <td style="width: 50%;">
            <table class="table table-borderless">
                <tbody>
                <?foreach ($lesson->getPupils() as $k => $pupil):?>
                <tr>
                    <td style="padding-right: 25px;">
                        <span class="npp"><?=$k+1;?></span>

                        <span style="color: green;"><a href="<?=\app\helpers\OrganizationUrl::to(['pupil/view', 'id' => $pupil->id]);?>" target="_blank"><?=$pupil->fio;?></a></span>
                    </td>
                    <td style="width: 40%;">
                        <?if(key_exists($lesson->id, $attendances) && key_exists($pupil->id, $attendances[$lesson->id])):?>
                        <span style="color: green;"><?=\app\models\LessonAttendance::getStatusList()[$attendances[$lesson->id][$pupil->id]];?></span>
                        <?else:?>
                            <span style="color: black;">Не выставлено</span>
                        <?endif;?>
                    </td>
                </tr>
                <?endforeach;?>

                </tbody>
            </table>
        </td>
    </tr>
    <?endforeach;?>
    </tbody>
</table>
