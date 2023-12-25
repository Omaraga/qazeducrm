<?php
/** @var yii\web\View $this */
/** @var \app\models\PupilEducation $model */
?>
<div class="card" style="margin-bottom: 5px;">
    <div class="card-body" style="padding: 10px 20px 0px 20px;">
        <h5 class="card-title">
            <a data-toggle="collapse" href="#collapseTuition_<?=$model->id;?>" role="button" aria-expanded="false" aria-controls="collapseTuition_<?=$model->id;?>" class="collapsed">
                <?=date('d.m.Y', strtotime($model->date_start));?> - <?=date('d.m.Y', strtotime($model->date_end));?><br>
                <?foreach ($model->groups as $eduGroup):?>
                    <?=$eduGroup->group->nameFull;?>; <br>
                <?endforeach;?>
            </a>
        </h5>
        <div class="collapse" id="collapseTuition_<?=$model->id;?>" style="">
            <table>
                <tbody>
                <tr>
                    <td>
                        <b><?=Yii::t('main', 'Тариф');?></b>
                    </td>
                    <td>
                        <?=$model->tariff->nameFull;?>
                    </td>
                </tr>
                <tr>
                    <td><b><?=Yii::t('main', 'Выбранные группы');?></b></td>
                    <td>
                        <?foreach ($model->groups as $eduGroup):?>
                            <?=$eduGroup->group->nameFull;?>; <br>
                        <?endforeach;?>
                    </td>
                </tr>
                <tr>
                    <td>
                        <b><?=Yii::t('main', 'Скидка');?></b>
                    </td>
                    <td>
                        <?=$model->sale > 0 ? $model->sale.'%' : 'нет';?>
                    </td>
                </tr>
                <tr>
                    <td>
                        <b><?=Yii::t('main', 'Итого к оплате');?></b>
                    </td>
                    <td><?=number_format($model->total_price, 0, '.', ' ');?>тг</td>
                </tr>
                <tr><td><b><nobr><?=Yii::t('main', 'Период');?></nobr></b>
                    </td><td><?=date('d.m.Y', strtotime($model->date_start));?> - <?=date('d.m.Y', strtotime($model->date_end));?></td>
                </tr>
                <tr>
                    <td><b><span title="Кол-во переносов - кол-во дней когда ученик имел уважительную причину пропуска. Такие дни переносятся и будут использованы когда у ученика закончится оплата по тарифу.">Кол-во переносов</span></b>
                    </td>
                    <td>0</td>
                </tr>
                <tr>
                    <td><b><?=Yii::t('main', 'Примечание');?></b></td>
                    <td><?=$model->comment;?></td>
                </tr>
                </tbody>
            </table>
            <a href="<?=\app\helpers\OrganizationUrl::to(['pupil/update-edu', 'pupil_id' => $model->pupil_id, 'id' => $model->id]);?>" class="badge badge-secondary mr-2" style="color: #fff; cursor: pointer;">
                                    Редактировать обучение <i class="fa fa-edit"></i></a>
            <a href="<?=\app\helpers\OrganizationUrl::to(['pupil/copy-edu', 'pupil_id' => $model->pupil_id, 'id' => $model->id]);?>" class="badge badge-primary mr-2" style="color: #fff; cursor: pointer;">
                                    Дублировать обучение <i class="fa fa-copy"></i>
            </a>

            <?= \yii\bootstrap4\Html::a(' Удалить обучение <i class="fa fa-trash"></i>', \app\helpers\OrganizationUrl::to(['pupil/delete-edu', 'id' => $model->id]), [
                'class' => 'badge badge-danger',
                'style' => 'color: #fff; cursor: pointer;',
                'data' => [
                    'confirm' => 'Вы действительно хотите удалить обучение?',
                    'method' => 'post',
                ],
            ]) ?>
            <br><br>
        </div>
    </div>
</div>
