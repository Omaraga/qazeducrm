<?php

use yii\bootstrap4\Html;
use yii\bootstrap4\ActiveForm;
use app\models\relations\TariffSubject;

/** @var yii\web\View $this */
/** @var \app\models\forms\TariffForm $model */
/** @var yii\widgets\ActiveForm $form */

$js = <<<JS
    $('#tariff-duration').change(function (e){
        let val = $(this).find('option:selected').val();
        if (parseInt(val) === 3){
            $('#lesson_amount_block').removeClass('d-none');
        }else{
            $('#lesson_amount_block').addClass('d-none');
        }
    });
    $('#add-subject').click(function (e){
        e.preventDefault();
        let templateClone = $('#subject-table').find('.subject-block').last().clone();
        let i = parseInt($(templateClone).find('.td-number').text());
        $(templateClone).find('.td-number').text(i+1);
        $(templateClone).find('input[name],select[name]').each(function (e){
            let size = $('#subject-table').find('tbody').children('.subject-block ').length + 1;
            console.log('size', size);
            var prefix = "TariffForm[subjects][" + (size-1) + "]";
            this.name = this.name.replace(/TariffForm\[subjects\]\[\d+\]/, prefix);
        });
        $('#subject-table').find('tbody').append(templateClone);
    });
JS;
$this->registerJs($js);
?>

<div class="tariff-form">

    <?php $form = ActiveForm::begin(); ?>

    <div class="card mb-3">
        <div class="card-header">
            <?=Yii::t('main', 'Основные данные');?>
        </div>
        <div class="card-body">
            <div class="row">
                <?= $form->field($model, 'name', ['options' =>['class' => 'col-12 col-sm-4']])->textInput() ?>
                <?= $form->field($model, 'duration', ['options' =>['class' => 'col-12 col-sm-4']])->dropDownList(\app\helpers\Lists::getTariffDurations(),['id' => 'tariff-duration']) ?>
                <?= $form->field($model, 'lesson_amount', ['options' =>['class' => 'col-12 col-sm-4 d-none', 'id' => 'lesson_amount_block']])->textInput(['type' => 'number']) ?>
            </div>
            <div class="row">
                <?= $form->field($model, 'type', ['options' =>['class' => 'col-12 col-sm-4']])->dropDownList(\app\helpers\Lists::getTariffTypes()) ?>
                <?= $form->field($model, 'price', ['options' =>['class' => 'col-12 col-sm-4']])->textInput(['type' => 'number']) ?>
                <?= $form->field($model, 'status', ['options' =>['class' => 'col-12 col-sm-4']])->dropDownList(\app\models\Tariff::getStatusList(), []) ?>
            </div>
            <?= $form->field($model, 'description')->textarea(['rows' => 6]) ?>
        </div>
    </div>
    <div class="card mb-3">
        <div class="card-header">
            <?=Yii::t('main', 'Предметы');?>
        </div>
        <div class="card-body">
            <?=Html::a(Yii::t('main', 'Добавить предмет'), '#', [
                'class' => 'btn btn-primary my-2',
                'id' => 'add-subject'
            ]);?>
            <table class="table table-bordered" id="subject-table">
                <thead>
                <tr style="background: #e4e2ff;">
                    <th scope="col">#</th>
                    <th scope="col"><?=Yii::t('main', 'Предмет');?></th>
                    <th scope="col"><?=Yii::t('main', 'Количество занятии в неделю');?></th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($model->subjects ?: [new TariffSubject()] as $k => $subject) : ?>
                    <tr class="subject-block">
                        <th scope="row" class="td-number"><?=$k+1;?></th>
                        <td class="td-subject"><?=$form->field($model, "subjects[$k][subject_id]")->dropDownList(\app\models\Tariff::getSubjectsMap())->label(false)?></td>

                        <td class="td-lesson"><?= $form->field($model, "subjects[$k][lesson_amount]")->dropDownList(\app\models\Tariff::getAmounts())->label(false) ?></td>
                    </tr>
                <?endforeach;?>

                </tbody>
            </table>
        </div>
    </div>



    <div class="form-group">
        <?= Html::submitButton( Yii::t('main', 'Сохранить'), ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
