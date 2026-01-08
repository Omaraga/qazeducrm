<?php

/** @var yii\web\View $this */
/** @var app\models\Settings $model */

use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = 'Настройки системы';
?>

<div class="row">
    <div class="col-lg-12">
        <?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]); ?>

        <!-- Основная информация -->
        <div class="card-custom mb-4">
            <div class="card-header">
                <i class="fas fa-building"></i> Основная информация
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <?= $form->field($model, 'name')->textInput(['maxlength' => 255, 'placeholder' => 'Qazaq Education CRM']) ?>
                    </div>
                    <div class="col-md-6">
                        <?= $form->field($model, 'url')->textInput(['maxlength' => 255, 'placeholder' => 'https://qazaqedu.kz']) ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <?= $form->field($model, 'image')->fileInput(['class' => 'form-control']) ?>
                        <?php if ($model->logo): ?>
                            <p class="mt-2"><img src="<?= Html::encode($model->logo) ?>" style="max-height: 50px;" alt="Logo"></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Контактная информация -->
        <div class="card-custom mb-4">
            <div class="card-header">
                <i class="fas fa-address-book"></i> Контактная информация
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <?= $form->field($model, 'email')->textInput(['type' => 'email', 'placeholder' => 'info@qazaqedu.kz']) ?>
                    </div>
                    <div class="col-md-6">
                        <?= $form->field($model, 'address')->textInput(['maxlength' => 255, 'placeholder' => 'г. Алматы, ул. Примерная 123']) ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <?= $form->field($model, 'working_hours')->textInput(['placeholder' => 'Пн-Пт: 9:00 - 18:00, Сб-Вс: выходной']) ?>
                    </div>
                </div>

                <!-- Телефоны -->
                <div class="mb-3">
                    <label class="form-label fw-bold">Телефоны</label>
                    <div id="phone-list">
                        <?php
                        $phones = $model->phoneList ?: [''];
                        foreach ($phones as $i => $phone):
                        ?>
                            <div class="input-group mb-2 phone-item">
                                <input type="text" name="Settings[phoneList][]"
                                       class="form-control"
                                       value="<?= Html::encode($phone) ?>"
                                       placeholder="+7 700 123 45 67">
                                <button type="button" class="btn btn-outline-danger" onclick="this.closest('.phone-item').remove()">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="addPhoneField()">
                        <i class="fas fa-plus"></i> Добавить телефон
                    </button>
                </div>
            </div>
        </div>

        <!-- Социальные сети -->
        <div class="card-custom mb-4">
            <div class="card-header">
                <i class="fas fa-share-alt"></i> Социальные сети
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <?= $form->field($model, 'telegram')->textInput(['placeholder' => '@qazaqedu или https://t.me/qazaqedu']) ?>
                    </div>
                    <div class="col-md-6">
                        <?= $form->field($model, 'instagram')->textInput(['placeholder' => 'https://instagram.com/qazaqedu']) ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <?= $form->field($model, 'whatsapp')->textInput(['placeholder' => 'https://wa.me/77001234567']) ?>
                    </div>
                    <div class="col-md-6">
                        <?= $form->field($model, 'youtube')->textInput(['placeholder' => 'https://youtube.com/@qazaqedu']) ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <?= $form->field($model, 'facebook')->textInput(['placeholder' => 'https://facebook.com/qazaqedu']) ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Статистика лендинга -->
        <div class="card-custom mb-4">
            <div class="card-header">
                <i class="fas fa-chart-bar"></i> Статистика на главной странице
            </div>
            <div class="card-body">
                <p class="text-muted mb-3">Эти данные отображаются в hero-секции лендинга</p>
                <div class="row">
                    <div class="col-md-3">
                        <?= $form->field($model, 'stat_centers_count')->textInput(['placeholder' => '200+']) ?>
                    </div>
                    <div class="col-md-3">
                        <?= $form->field($model, 'stat_centers_label')->textInput(['placeholder' => 'Учебных центров']) ?>
                    </div>
                    <div class="col-md-3">
                        <?= $form->field($model, 'stat_pupils_count')->textInput(['placeholder' => '15K+']) ?>
                    </div>
                    <div class="col-md-3">
                        <?= $form->field($model, 'stat_pupils_label')->textInput(['placeholder' => 'Учеников']) ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-3">
                        <?= $form->field($model, 'stat_satisfaction_count')->textInput(['placeholder' => '99%']) ?>
                    </div>
                    <div class="col-md-3">
                        <?= $form->field($model, 'stat_satisfaction_label')->textInput(['placeholder' => 'Довольных клиентов']) ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- SEO настройки -->
        <div class="card-custom mb-4">
            <div class="card-header">
                <i class="fas fa-search"></i> SEO настройки
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12">
                        <?= $form->field($model, 'meta_title')->textInput(['maxlength' => 70, 'placeholder' => 'Qazaq Education CRM - Система управления учебным центром']) ?>
                        <small class="text-muted">Рекомендуемая длина: до 70 символов</small>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-12">
                        <?= $form->field($model, 'meta_description')->textarea(['rows' => 2, 'maxlength' => 160, 'placeholder' => 'Современная CRM система для учебных центров...']) ?>
                        <small class="text-muted">Рекомендуемая длина: до 160 символов</small>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-12">
                        <?= $form->field($model, 'meta_keywords')->textInput(['maxlength' => 255, 'placeholder' => 'CRM, учебный центр, ученики, расписание']) ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Информация о системе (Features) -->
        <div class="card-custom mb-4">
            <div class="card-header">
                <i class="fas fa-star"></i> Страница возможностей (Features)
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <?= $form->field($model, 'features_hero_title')->textInput(['placeholder' => 'Все возможности системы']) ?>
                    </div>
                    <div class="col-md-6">
                        <?= $form->field($model, 'features_hero_subtitle')->textInput(['placeholder' => 'Полный набор инструментов...']) ?>
                    </div>
                </div>

                <hr class="my-4">

                <!-- Feature 1 -->
                <h6 class="mb-3"><i class="fas fa-user-graduate text-primary"></i> Функция 1: Ученики</h6>
                <div class="row">
                    <div class="col-md-4">
                        <?= $form->field($model, 'feature_1_title')->textInput(['placeholder' => 'Полный контроль над базой учеников']) ?>
                    </div>
                    <div class="col-md-8">
                        <?= $form->field($model, 'feature_1_description')->textarea(['rows' => 2, 'placeholder' => 'Описание функции...']) ?>
                    </div>
                </div>

                <!-- Feature 2 -->
                <h6 class="mb-3 mt-3"><i class="fas fa-users text-primary"></i> Функция 2: Группы</h6>
                <div class="row">
                    <div class="col-md-4">
                        <?= $form->field($model, 'feature_2_title')->textInput(['placeholder' => 'Гибкое управление группами']) ?>
                    </div>
                    <div class="col-md-8">
                        <?= $form->field($model, 'feature_2_description')->textarea(['rows' => 2, 'placeholder' => 'Описание функции...']) ?>
                    </div>
                </div>

                <!-- Feature 3 -->
                <h6 class="mb-3 mt-3"><i class="fas fa-wallet text-primary"></i> Функция 3: Финансы</h6>
                <div class="row">
                    <div class="col-md-4">
                        <?= $form->field($model, 'feature_3_title')->textInput(['placeholder' => 'Прозрачный учёт финансов']) ?>
                    </div>
                    <div class="col-md-8">
                        <?= $form->field($model, 'feature_3_description')->textarea(['rows' => 2, 'placeholder' => 'Описание функции...']) ?>
                    </div>
                </div>

                <!-- Feature 4 -->
                <h6 class="mb-3 mt-3"><i class="fas fa-chart-line text-primary"></i> Функция 4: Аналитика</h6>
                <div class="row">
                    <div class="col-md-4">
                        <?= $form->field($model, 'feature_4_title')->textInput(['placeholder' => 'Принимайте решения на основе данных']) ?>
                    </div>
                    <div class="col-md-8">
                        <?= $form->field($model, 'feature_4_description')->textarea(['rows' => 2, 'placeholder' => 'Описание функции...']) ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Карта (2GIS) -->
        <div class="card-custom mb-4">
            <div class="card-header">
                <i class="fas fa-map-marker-alt"></i> Карта (2GIS)
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <?= $form->field($model, 'two_gis')->textInput(['placeholder' => 'Ссылка на 2GIS']) ?>
                    </div>
                    <div class="col-md-4">
                        <?= $form->field($model, 'map_x')->textInput(['placeholder' => 'Широта (lat)']) ?>
                    </div>
                    <div class="col-md-4">
                        <?= $form->field($model, 'map_y')->textInput(['placeholder' => 'Долгота (lng)']) ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Кнопка сохранения -->
        <div class="card-custom mb-4">
            <div class="card-body">
                <?= Html::submitButton('<i class="fas fa-save"></i> Сохранить настройки', ['class' => 'btn btn-primary btn-lg']) ?>
            </div>
        </div>

        <?php ActiveForm::end(); ?>
    </div>
</div>

<script>
function addPhoneField() {
    const html = `
        <div class="input-group mb-2 phone-item">
            <input type="text" name="Settings[phoneList][]"
                   class="form-control"
                   placeholder="+7 700 123 45 67">
            <button type="button" class="btn btn-outline-danger" onclick="this.closest('.phone-item').remove()">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `;
    document.getElementById('phone-list').insertAdjacentHTML('beforeend', html);
}
</script>
