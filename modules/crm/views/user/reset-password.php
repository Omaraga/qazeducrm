<?php

use app\helpers\OrganizationUrl;
use app\widgets\tailwind\Icon;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\User $model */

$this->title = 'Сброс пароля';
$this->params['breadcrumbs'][] = ['label' => 'Сотрудники', 'url' => OrganizationUrl::to(['index'])];
$this->params['breadcrumbs'][] = ['label' => $model->fio, 'url' => OrganizationUrl::to(['view', 'id' => $model->id])];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="space-y-6 max-w-md mx-auto">
    <!-- Header -->
    <div class="text-center">
        <div class="w-16 h-16 rounded-full bg-amber-100 flex items-center justify-center mx-auto">
            <?= Icon::show('key', 'xl', 'text-amber-600') ?>
        </div>
        <h1 class="text-2xl font-bold text-gray-900 mt-4"><?= Html::encode($this->title) ?></h1>
        <p class="text-gray-500 mt-1">Сотрудник: <strong><?= Html::encode($model->fio) ?></strong></p>
    </div>

    <!-- Form -->
    <div class="card">
        <div class="card-body">
            <form method="post">
                <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->csrfToken) ?>

                <div class="space-y-4">
                    <div>
                        <label class="form-label required">Новый пароль</label>
                        <input type="password" name="new_password" class="form-input"
                               placeholder="Минимум 6 символов" required minlength="6">
                        <p class="text-xs text-gray-500 mt-1">Пароль должен содержать минимум 6 символов</p>
                    </div>

                    <div>
                        <label class="form-label required">Подтвердите пароль</label>
                        <input type="password" name="confirm_password" class="form-input"
                               placeholder="Повторите пароль" required minlength="6">
                    </div>

                    <div class="bg-amber-50 border border-amber-200 rounded-lg p-4">
                        <div class="flex gap-3">
                            <?= Icon::show('exclamation-triangle', 'md', 'text-amber-600 flex-shrink-0') ?>
                            <div class="text-sm text-amber-700">
                                <p class="font-medium">Внимание!</p>
                                <p class="mt-1">После смены пароля сотрудник сможет войти только с новым паролем. Убедитесь, что вы передадите ему новый пароль безопасным способом.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex gap-3 mt-6">
                    <button type="submit" class="btn btn-warning flex-1">
                        <?= Icon::show('key', 'sm') ?>
                        Сбросить пароль
                    </button>
                    <a href="<?= OrganizationUrl::to(['view', 'id' => $model->id]) ?>" class="btn btn-secondary">
                        Отмена
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
