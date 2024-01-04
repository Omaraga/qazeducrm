<?php

use yii\bootstrap4\Html;
use yii\bootstrap4\ActiveForm;
use app\models\relations\TariffSubject;
use yii\helpers\ArrayHelper;
use kartik\date\DatePicker;
/** @var yii\web\View $this */
/** @var \app\models\forms\EducationForm $model */
/** @var yii\widgets\ActiveForm $form */

$tariffUrl = \app\helpers\OrganizationUrl::to(['tariff/get-info']);
$js = <<<JS
    $('#date_start_input').mask('99.99.9999');
    $('#date_end_input').mask('99.99.9999');
    $('#edu-sale').change(function (e){
        e.preventDefault();
        let val = parseInt($(this).val());
        if (val < 0){
            val = 0;
        }else if(val > 100){
            val = 100
        }
        $(this).val(val);
    })
    let loadGroups = function (subjects){
        let scenario = $('#scenario').val()
        if (scenario !== 'edit' && subjects.length > 0){
            let i = 0;
            for (let i = 0; i < subjects.length; i++){
                let templateClone = $('#education-table').find('.group-block').last().clone();
                subjects[i] = {id : subjects[i], temp: templateClone};
            }
            
            $('#education-table').find('.group-block').each(function (){
                $(this).remove();
            })
            console.log(subjects);
            $(subjects).each(function (){
                console.log('inpu')
                $(this.temp).find('input[name],select[name]').each(function (e){
                    let size = $('#education-table').find('tbody').children('.group-block').length + 1;
                    var prefix = "EducationForm[groups][" + i + "]";
                    this.name = this.name.replace(/EducationForm\[groups\]\[\d+\]/, prefix);
                    $(this).find('option:selected').removeAttr('selected');
                });
                $(this.temp).find('.subject_input').find('option[value='+this.id+']').attr({'selected':true});
                $('#education-table').find('tbody').append(this.temp);
                i++;
            })
            
            
        }
    }
    let updateInfo = function (isLoadSubjects = false){
       let tariffId = $('#educationform-tariff_id').find('option:selected').val();
       let dateStart = $('#date_start_input').val();
       let dateEnd = $('#date_end_input').val();
       let sale = $('#edu-sale').val();
       $.ajax({
            'url': '/tariff/get-info',
            'type': 'post',
            'data': {id : tariffId, date_start : dateStart, date_end : dateEnd, sale : sale},
            success: function(data){
                data = JSON.parse(data);
                if (data.id){
                    $('#divPaymentDescription').html(data.info_text);
                    if (data.duration == 3){
                        $('.field-date_end_input').hide();
                    }else{
                        $('.field-date_end_input').show();
                    }
                    $('#group-card').removeClass('d-none');
                    if (isLoadSubjects){
                        loadGroups(data.subjects)
                    }
                }
                console.log(data);
            },
            error: function(data){
            
                alert('Error');
            }
        });
    }
    $('#educationform-tariff_id').change(function (e){
       updateInfo(true);
    });
    $('#date_start_input').change(function (e){
       updateInfo();
    });
    $('#date_end_input').change(function (e){
       updateInfo();
    });
    $('#edu-sale').change(function (e){
        updateInfo();
    })
    
    updateInfo();
    $('#submit-btn').click(function (e){
        e.preventDefault();
        $('input[disabled],select[disabled]').each(function (e){
            $(this).removeAttr('disabled')
        })
        $('#education-form').submit();
    })
    
    
JS;
$this->registerJs($js);

if ($model->getScenario() === \app\models\forms\EducationForm::TYPE_EDIT){
    $this->title = 'Редактировать обучение: ';
}else if($model->getScenario() === \app\models\forms\EducationForm::TYPE_COPY){
    $this->title = 'Дублировать обучение: ';
}else{
    $this->title = 'Добавить обучение';
}

$this->params['breadcrumbs'][] = ['label' => 'Обучение', 'url' => \app\helpers\OrganizationUrl::to(['pupil/edu', 'id' => $model->pupil_id])];
?>

<div class="edu-form">
    <input type="hidden" id="scenario" value="<?=$model->getScenario();?>">

    <?php $form = ActiveForm::begin(['action' => $model->getActionUrl(), 'method' => 'POST', 'id' => 'education-form']); ?>

    <div class="card mb-3">
        <div class="card-header font-weight-bold">
            <?=$this->title?>
        </div>
        <div class="card-body">
            <div class="row">
                <?= $form->field($model, 'tariff_id', ['options' =>['class' => 'col-12']])->widget(\kartik\select2\Select2::classname(), [
                    'data' => ArrayHelper::map(\app\models\Tariff::find()->all(), 'id', 'nameFull'),
                    'options' => [
                        'placeholder' => 'Выберите тариф',
                        'disabled' => $model->getScenario() == \app\models\forms\EducationForm::TYPE_EDIT,
                    ],
                    'pluginOptions' => [
                        'allowClear' => false
                    ],
                ]) ?>
            </div>
            <div class="card my-3 <?=$model->tariff_id ? '': '';?>" id="group-card">
                <div class="card-header">
                    <?=Yii::t('main', 'Выберите группы согласно тарифа');?>
                </div>
                <div class="card-body">
                    <table class="table table-bordered" id="education-table">
                        <thead>
                        <tr style="background: #e4e2ff;">
                            <th scope="col"><?=Yii::t('main', 'Предмет');?></th>
                            <th scope="col"><?=Yii::t('main', 'Группа');?></th>
                        </tr>
                        </thead>
                        <tbody>
                            <?foreach ($model->groups ? : [new \app\models\relations\EducationGroup()] as $k => $group):?>
                                <tr class="group-block">
                                    <td class="td-subject"><?=$form->field($model, "groups[$k][subject_id]")->dropDownList(\app\models\Tariff::getSubjectsMap(), [
                                            'disabled' => true,
                                            'class' => 'form-control subject_input disabled'
                                        ])->label(false)?></td>

                                    <td class="td-lesson"><?= $form->field($model, "groups[$k][group_id]")->dropDownList(ArrayHelper::map(\app\models\Group::find()->byOrganization()->all(), 'id', 'nameFull'), [
                                            'disabled' => $model->getScenario() == \app\models\forms\EducationForm::TYPE_EDIT,
                                            'prompt' => Yii::t('main', 'Выберите группу'),
                                        ])->label(false) ?></td>
                                </tr>
                            <?endforeach;?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="row">
                <?= $form->field($model, 'sale', ['options' =>['class' => 'col-12 col-sm-4']])->textInput([
                    'type' => 'number',
                    'id' => 'edu-sale',
                    'max' => '100'
                ]) ?>
                <?= $form->field($model, 'date_start', ['options' =>['class' => 'col-12 col-sm-4']])->widget(\kartik\date\DatePicker::className(), [
                    'type' => DatePicker::TYPE_INPUT,
                    'pluginOptions' => [
                        'autoclose' => true,
                        'format' => 'dd.mm.yyyy'
                    ],
                    'options' => ['autocomplete' => 'off', 'id' => 'date_start_input']
                ]) ?>
                <?= $form->field($model, 'date_end', ['options' =>['class' => 'col-12 col-sm-4']])->widget(\kartik\date\DatePicker::className(), [
                    'type' => DatePicker::TYPE_INPUT,
                    'pluginOptions' => [
                        'autoclose' => true,
                        'format' => 'dd.mm.yyyy'
                    ],
                    'options' => ['autocomplete' => 'off', 'id' => 'date_end_input']
                ]) ?>
            </div>
            <?= $form->field($model, 'comment')->textarea(['rows' => 6]) ?>
        </div>
    </div>

    <div id="divPaymentDescription" class="alert alert-warning mb-4 mt-2" role="alert" style="margin-bottom: 0;">Выберите тариф для расчета стоимости</div>



    <div class="form-group">
        <?= Html::submitButton( Yii::t('main', 'Сохранить'), ['class' => 'btn btn-success', 'id' => 'submit-btn']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>

