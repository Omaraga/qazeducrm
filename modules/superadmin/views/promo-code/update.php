<?php

/** @var yii\web\View $this */
/** @var app\models\SaasPromoCode $model */
/** @var app\models\SaasPlan[] $plans */
/** @var app\models\SaasFeature[] $addons */

use yii\helpers\Url;

$this->title = 'Редактирование: ' . $model->code;
$this->params['breadcrumbs'][] = ['label' => 'Промокоды', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->code, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Редактирование';
?>

<div class="mb-3">
    <a href="<?= Url::to(['view', 'id' => $model->id]) ?>" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left"></i> Назад
    </a>
</div>

<?= $this->render('_form', [
    'model' => $model,
    'plans' => $plans,
    'addons' => $addons,
]) ?>
