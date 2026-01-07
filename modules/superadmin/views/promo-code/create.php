<?php

/** @var yii\web\View $this */
/** @var app\models\SaasPromoCode $model */
/** @var app\models\SaasPlan[] $plans */
/** @var app\models\SaasFeature[] $addons */

use yii\helpers\Url;

$this->title = 'Создание промокода';
$this->params['breadcrumbs'][] = ['label' => 'Промокоды', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="mb-3">
    <a href="<?= Url::to(['index']) ?>" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left"></i> К списку
    </a>
</div>

<?= $this->render('_form', [
    'model' => $model,
    'plans' => $plans,
    'addons' => $addons,
]) ?>
