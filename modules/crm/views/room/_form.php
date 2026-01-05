<?php

use app\helpers\OrganizationUrl;
use app\models\Room;
use app\widgets\tailwind\Icon;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Room $model */
?>

<form action="" method="post" class="max-w-2xl">
    <input type="hidden" name="<?= Yii::$app->request->csrfParam ?>" value="<?= Yii::$app->request->csrfToken ?>">

    <div class="card">
        <div class="card-header">
            <h3 class="text-lg font-semibold text-gray-900">Данные кабинета</h3>
        </div>
        <div class="card-body space-y-4">
            <!-- Name -->
            <div>
                <label class="form-label" for="room-name">
                    Название <span class="text-danger-500">*</span>
                </label>
                <?= Html::activeTextInput($model, 'name', [
                    'class' => 'form-input',
                    'id' => 'room-name',
                    'maxlength' => true,
                    'placeholder' => 'Например: Аудитория 101',
                    'autofocus' => true
                ]) ?>
                <?php if ($model->hasErrors('name')): ?>
                    <p class="mt-1 text-sm text-danger-600"><?= $model->getFirstError('name') ?></p>
                <?php endif; ?>
            </div>

            <!-- Code and Capacity in row -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Code -->
                <div>
                    <label class="form-label" for="room-code">
                        Код/Номер
                    </label>
                    <?= Html::activeTextInput($model, 'code', [
                        'class' => 'form-input',
                        'id' => 'room-code',
                        'maxlength' => 20,
                        'placeholder' => 'Например: 101'
                    ]) ?>
                    <p class="mt-1 text-sm text-gray-500">Короткий код для быстрой идентификации</p>
                    <?php if ($model->hasErrors('code')): ?>
                        <p class="mt-1 text-sm text-danger-600"><?= $model->getFirstError('code') ?></p>
                    <?php endif; ?>
                </div>

                <!-- Capacity -->
                <div>
                    <label class="form-label" for="room-capacity">
                        Вместимость
                    </label>
                    <?= Html::activeInput('number', $model, 'capacity', [
                        'class' => 'form-input',
                        'id' => 'room-capacity',
                        'min' => 0,
                        'placeholder' => '0'
                    ]) ?>
                    <p class="mt-1 text-sm text-gray-500">Максимальное количество человек</p>
                    <?php if ($model->hasErrors('capacity')): ?>
                        <p class="mt-1 text-sm text-danger-600"><?= $model->getFirstError('capacity') ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Color -->
            <div>
                <label class="form-label">Цвет</label>
                <div class="flex flex-wrap gap-2">
                    <?php foreach (Room::DEFAULT_COLORS as $color): ?>
                        <label class="cursor-pointer">
                            <input type="radio" name="Room[color]" value="<?= $color ?>"
                                   class="sr-only peer" <?= $model->color === $color ? 'checked' : '' ?>>
                            <div class="w-8 h-8 rounded-lg border-2 border-transparent peer-checked:border-gray-900 peer-checked:ring-2 peer-checked:ring-offset-2 peer-checked:ring-gray-400 transition-all"
                                 style="background-color: <?= $color ?>">
                            </div>
                        </label>
                    <?php endforeach; ?>
                </div>
                <p class="mt-2 text-sm text-gray-500">Цвет для отображения в календаре</p>
            </div>
        </div>
        <div class="card-footer flex items-center gap-3">
            <button type="submit" class="btn btn-primary">
                <?= Icon::show('check') ?>
                <?= Yii::t('main', 'Сохранить') ?>
            </button>
            <a href="<?= OrganizationUrl::to(['room/index']) ?>" class="btn btn-secondary">
                Отмена
            </a>
        </div>
    </div>
</form>
