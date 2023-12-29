<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use yii\widgets\Pjax;

/** @var yii\web\View $this */
/** @var app\models\Group $model */
/** @var \yii\data\ActiveDataProvider $dataProvider */

$this->title = $model->code.'-'.$model->name;
$this->params['breadcrumbs'][] = ['label' => 'Группы', 'url' => \app\helpers\OrganizationUrl::to(['index'])];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="group-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <nav>
        <div class="nav nav-tabs" id="nav-tab" role="tablist">
            <a class="nav-item nav-link" id="nav-home-tab"  href="<?=\app\helpers\OrganizationUrl::to(['group/view', 'id' => $model->id]);?>" role="tab" aria-controls="nav-home" aria-selected="true"><?=Yii::t('main', 'Основные данные');?></a>
            <a class="nav-item nav-link" id="nav-profile-tab"  href="<?=\app\helpers\OrganizationUrl::to(['group/teachers', 'id' => $model->id]);?>" role="tab" aria-controls="nav-profile" aria-selected="true"><?=Yii::t('main', 'Преподаватели');?></a>
            <a class="nav-item nav-link active" id="nav-contact-tab" href="<?=\app\helpers\OrganizationUrl::to(['group/pupils', 'id' => $model->id]);?>" role="tab" aria-controls="nav-contact" aria-selected="false"><?=Yii::t('main', 'Ученики');?></a>
        </div>
    </nav>
    <div class="tab-content" id="nav-tabContent">
        <div class="tab-pane fade show active" id="nav-home" role="tabpanel" aria-labelledby="nav-home-tab">
            <h4><?= Html::encode('Ученики группы: '.$this->title) ?></h4>

            <?= \yii\grid\GridView::widget([
                'dataProvider' => $dataProvider,
                'layout' => '{summary}{items}',
                'columns' => [
                    'id',
                    'iin',
                    'fio',
                    [
                        'attribute' => 'contacts',
                        'value' => function($model){
                            $phone = '';
                            if ($model->phone){
                                $phone .= $model->phone.'<br>';
                            }
                            if ($model->home_phone){
                                $phone .= $model->home_phone;
                            }
                            return $phone;
                        },
                        'format' => 'raw'
                    ],
                    [
                        'attribute' => 'parent_contacts',
                        'value' => function($model){
                            return $model->parent_fio.'<br>'.$model->parent_phone;
                        },
                        'format' => 'raw'
                    ],
                    [
                        'label' => Yii::t('main','Оплачено до'),
                        'value' => function($data) use ($model){
                            $pupilEducation = \app\models\PupilEducation::find()->innerJoinWith([
                                    'groups' => function($q){
                                        $q->andWhere(['<>', 'education_group.is_deleted', 1]);
                                    }
                            ])->where(['pupil_education.pupil_id' => $data->id, 'education_group.group_id' => $model->id])->orderBy('date_end DESC')->one();
                            if ($pupilEducation){
                                return date('d.m.Y', strtotime($pupilEducation->date_end));
                            }
                            return '';

                        }
                    ],
                ],
            ]); ?>

            <?= \yii\bootstrap4\LinkPager::widget([
                'pagination' => $dataProvider->pagination
            ]) ?>
        </div>
    </div>
    <? \yii\bootstrap4\Modal::begin([
        'title' => Yii::t('main', 'Добавить преподавателя'),
        'id' => 'modal-form',
        'size' => 'modal-lg'

    ]); ?>
    <div id="modalContent"></div>
    <? \yii\bootstrap4\Modal::end();?>

</div>


