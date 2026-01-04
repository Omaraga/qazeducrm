<?php
/* @var \app\models\Pupil $model*/
?>
<div class="row my-3">
    <div class="input-group col-12 col-sm-5">
        <div class="input-group-text" id="btnGroupAddon2" style="background: lightgreen;"><b>
                <?=$model->balance > 0 ? Yii::t('main', 'На счету ученика') : Yii::t('main', 'Задолженость ученика');?> :

            </b></div>
        <input type="text" disabled class="form-control" style="background: <?=$model->balance > 0 ? 'lightblue' : 'pink';?>"  aria-describedby="btnGroupAddon2" value="<?=$model->balance ? : 0;?> тг.">
    </div>
</div>
