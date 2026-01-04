<?php

use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var app\models\TeacherSalary $model */

$this->title = 'Редактирование: ' . ($model->teacher ? $model->teacher->fio : 'ID ' . $model->teacher_id);
$this->params['breadcrumbs'][] = ['label' => 'Зарплаты учителей', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->teacher ? $model->teacher->fio : 'ID ' . $model->teacher_id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Редактирование';
?>

<div class="salary-update">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><?= Html::encode($this->title) ?></h1>
        <a href="<?= Url::to(['view', 'id' => $model->id]) ?>" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Назад
        </a>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Бонусы и вычеты</h5>
                </div>
                <div class="card-body">
                    <form method="post">
                        <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->csrfToken) ?>

                        <div class="alert alert-info">
                            <strong>Период:</strong> <?= $model->getPeriodLabel() ?><br>
                            <strong>Базовая сумма:</strong> <?= number_format($model->base_amount, 0, ',', ' ') ?> ₸
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Бонусы</label>
                            <div class="input-group">
                                <input type="number" name="bonus_amount" class="form-control"
                                       value="<?= $model->bonus_amount ?>" min="0" step="100">
                                <span class="input-group-text">₸</span>
                            </div>
                            <small class="text-muted">Дополнительные начисления (за качество, переработку и т.д.)</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Вычеты</label>
                            <div class="input-group">
                                <input type="number" name="deduction_amount" class="form-control"
                                       value="<?= $model->deduction_amount ?>" min="0" step="100">
                                <span class="input-group-text">₸</span>
                            </div>
                            <small class="text-muted">Удержания (штрафы, авансы и т.д.)</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Примечания</label>
                            <textarea name="notes" class="form-control" rows="3"><?= Html::encode($model->notes) ?></textarea>
                        </div>

                        <hr>

                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <strong>Итого:</strong>
                                <span class="fs-4 text-primary" id="total-preview">
                                    <?= number_format($model->total_amount, 0, ',', ' ') ?> ₸
                                </span>
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Сохранить
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Информация</h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tr>
                            <td class="text-muted">Преподаватель</td>
                            <td><?= $model->teacher ? Html::encode($model->teacher->fio) : 'Не указан' ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Уроков</td>
                            <td><?= $model->lessons_count ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Учеников с оплатой</td>
                            <td><?= $model->students_count ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Создано</td>
                            <td><?= Yii::$app->formatter->asDatetime($model->created_at, 'php:d.m.Y H:i') ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const baseAmount = <?= $model->base_amount ?>;
    const bonusInput = document.querySelector('input[name="bonus_amount"]');
    const deductionInput = document.querySelector('input[name="deduction_amount"]');
    const totalPreview = document.getElementById('total-preview');

    function updateTotal() {
        const bonus = parseFloat(bonusInput.value) || 0;
        const deduction = parseFloat(deductionInput.value) || 0;
        const total = baseAmount + bonus - deduction;
        totalPreview.textContent = new Intl.NumberFormat('ru-RU').format(total) + ' ₸';
    }

    bonusInput.addEventListener('input', updateTotal);
    deductionInput.addEventListener('input', updateTotal);
});
</script>
