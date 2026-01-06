<?php
/**
 * @var yii\web\View $this
 * @var app\models\SalesScript $model
 * @var array $statuses
 */

use app\helpers\OrganizationUrl;
use app\widgets\tailwind\Icon;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

$isNew = $model->isNewRecord;
$this->title = $isNew ? 'Новый скрипт' : 'Редактировать: ' . $model->title;
$this->params['breadcrumbs'][] = ['label' => 'Скрипты продаж', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$objections = $model->getObjectionsArray();
$tips = $model->getTipsArray();
?>

<div class="max-w-4xl mx-auto" x-data="{
    objections: <?= json_encode(!empty($objections) ? $objections : [['objection' => '', 'response' => '']]) ?>,
    tips: <?= json_encode(!empty($tips) ? $tips : ['']) ?>,

    addObjection() {
        this.objections.push({ objection: '', response: '' });
    },
    removeObjection(index) {
        this.objections.splice(index, 1);
    },
    addTip() {
        this.tips.push('');
    },
    removeTip(index) {
        this.tips.splice(index, 1);
    }
}">
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900"><?= Html::encode($this->title) ?></h1>
        </div>
        <a href="<?= OrganizationUrl::to(['sales-script/index']) ?>" class="btn btn-secondary">
            <?= Icon::show('arrow-left', 'sm') ?>
            Назад
        </a>
    </div>

    <?php $form = ActiveForm::begin([
        'options' => ['class' => 'space-y-6'],
    ]); ?>

    <!-- Basic info -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-4">
        <h2 class="text-lg font-semibold text-gray-900 border-b border-gray-200 pb-3">Основная информация</h2>

        <div class="grid grid-cols-2 gap-4">
            <?= $form->field($model, 'status')->dropDownList($statuses, [
                'class' => 'form-select',
                'prompt' => 'Выберите статус...',
            ]) ?>

            <?= $form->field($model, 'title')->textInput([
                'class' => 'form-input',
                'placeholder' => 'Например: Первый контакт',
            ]) ?>
        </div>

        <?= $form->field($model, 'content')->textarea([
            'class' => 'form-input',
            'rows' => 8,
            'placeholder' => "Текст скрипта...\n\nИспользуйте переменные:\n[ИМЯ] - имя менеджера\n[ЦЕНТР] - название организации\n[ДАТА], [ВРЕМЯ], [АДРЕС], [СУММА]",
        ]) ?>

        <?= $form->field($model, 'is_active')->checkbox([
            'class' => 'form-checkbox',
        ]) ?>
    </div>

    <!-- Tips -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-4">
        <div class="flex items-center justify-between border-b border-gray-200 pb-3">
            <h2 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                <?= Icon::show('light-bulb', 'md', 'text-amber-500') ?>
                Советы
            </h2>
            <button type="button" @click="addTip()" class="btn btn-sm btn-secondary">
                <?= Icon::show('plus', 'sm') ?>
                Добавить
            </button>
        </div>

        <div class="space-y-3">
            <template x-for="(tip, index) in tips" :key="index">
                <div class="flex items-center gap-2">
                    <input type="text"
                           :name="'tips[' + index + ']'"
                           x-model="tips[index]"
                           class="form-input flex-1"
                           placeholder="Совет для менеджера...">
                    <button type="button" @click="removeTip(index)"
                            class="btn btn-sm btn-outline-danger"
                            x-show="tips.length > 1">
                        <?= Icon::show('trash', 'sm') ?>
                    </button>
                </div>
            </template>
        </div>
    </div>

    <!-- Objections -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-4">
        <div class="flex items-center justify-between border-b border-gray-200 pb-3">
            <h2 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                <?= Icon::show('chat-bubble-left-right', 'md', 'text-red-500') ?>
                Работа с возражениями
            </h2>
            <button type="button" @click="addObjection()" class="btn btn-sm btn-secondary">
                <?= Icon::show('plus', 'sm') ?>
                Добавить
            </button>
        </div>

        <div class="space-y-4">
            <template x-for="(obj, index) in objections" :key="index">
                <div class="bg-gray-50 rounded-lg p-4 space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-gray-700">Возражение #<span x-text="index + 1"></span></span>
                        <button type="button" @click="removeObjection(index)"
                                class="text-red-500 hover:text-red-700"
                                x-show="objections.length > 1">
                            <?= Icon::show('trash', 'sm') ?>
                        </button>
                    </div>
                    <input type="text"
                           :name="'objections[' + index + '][objection]'"
                           x-model="obj.objection"
                           class="form-input w-full"
                           placeholder="Что говорит клиент...">
                    <textarea :name="'objections[' + index + '][response]'"
                              x-model="obj.response"
                              class="form-input w-full"
                              rows="2"
                              placeholder="Как отвечать..."></textarea>
                </div>
            </template>
        </div>
    </div>

    <!-- Submit -->
    <div class="flex items-center justify-end gap-3">
        <a href="<?= OrganizationUrl::to(['sales-script/index']) ?>" class="btn btn-secondary">
            Отмена
        </a>
        <button type="submit" class="btn btn-primary">
            <?= Icon::show('check', 'sm') ?>
            <?= $isNew ? 'Создать' : 'Сохранить' ?>
        </button>
    </div>

    <?php ActiveForm::end(); ?>
</div>
