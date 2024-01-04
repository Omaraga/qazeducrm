<?php
/** @var yii\web\View $this */
/** @var \app\models\User[] $teachers */
/** @var \app\models\search\DateSearch $searchModel */
/** @var array $dateTeacherSalary */
$this->title = Yii::t('main', 'Отчет. Заработная плата преподавателей');
setlocale(LC_ALL, 'russian');
?>
<h3><?=$this->title;?></h3>

<?php  echo $this->render('_search', ['model' => $searchModel]); ?>
<div>
<?foreach ($teachers as $teacher):?>
    <?
        $sum = 0;
    ?>
    <div class="card border-success mb-3">
        <div class="card-body">
            <h5 class="card-title">
                <a data-toggle="collapse" href="#collapse_<?=$teacher->id;?>" role="button" aria-expanded="false" aria-controls="collapse_<?=$teacher->id;?>">
                    <?=$teacher->fio;?>
                </a>
            </h5>
            <div class="collapse" id="collapse_<?=$teacher->id;?>">
                <table class="table">
                    <tbody>
                    <?foreach ($dateTeacherSalary as $date => $item):?>
                    <tr style="">
                        <td style="width: 300px;">
                            <a href="/Schedule/DayClose?date=01.01.2024" target="_blank"><?=date('d.m.Y', strtotime($date));?> г. <?=\app\helpers\Lists::getWeekDays()[date('w', strtotime($date)) == 0 ? 7 :date('w', strtotime($date))];?></a>
                        </td>
                        <td>
                            <?=$item[$teacher->id];?>тг
                        </td>
                    </tr>
                    <?
                        $sum += $item[$teacher->id];
                    ?>
                    <?endforeach;?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-transparent border-success font-weight-bold">
            Итого: <?=$sum > 0 ? number_format($sum, 0, '.', ' ') : $sum;?> тг
        </div>
    </div>
<?endforeach;?>
</div>