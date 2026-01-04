<?php

use app\models\TeacherSalary;
use app\models\TeacherRate;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var app\models\TeacherSalary $model */
/** @var app\models\TeacherSalaryDetail[] $details */

$this->title = 'Зарплата: ' . ($model->teacher ? $model->teacher->fio : 'ID ' . $model->teacher_id);
$this->params['breadcrumbs'][] = ['label' => 'Зарплаты учителей', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="salary-view">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><?= Html::encode($this->title) ?></h1>
        <div>
            <a href="<?= Url::to(['index']) ?>" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Назад
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Информация</h5>
                    <span class="badge <?= $model->getStatusBadgeClass() ?>"><?= $model->getStatusLabel() ?></span>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tr>
                            <td class="text-muted">Преподаватель</td>
                            <td class="fw-bold"><?= $model->teacher ? Html::encode($model->teacher->fio) : 'Не указан' ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Период</td>
                            <td><?= $model->getPeriodLabel() ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Уроков</td>
                            <td><?= $model->lessons_count ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Учеников</td>
                            <td><?= $model->students_count ?></td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Суммы</h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tr>
                            <td class="text-muted">Базовая сумма</td>
                            <td class="text-end"><?= number_format($model->base_amount, 0, ',', ' ') ?> ₸</td>
                        </tr>
                        <?php if ($model->bonus_amount > 0): ?>
                            <tr class="text-success">
                                <td>Бонусы</td>
                                <td class="text-end">+<?= number_format($model->bonus_amount, 0, ',', ' ') ?> ₸</td>
                            </tr>
                        <?php endif; ?>
                        <?php if ($model->deduction_amount > 0): ?>
                            <tr class="text-danger">
                                <td>Вычеты</td>
                                <td class="text-end">-<?= number_format($model->deduction_amount, 0, ',', ' ') ?> ₸</td>
                            </tr>
                        <?php endif; ?>
                        <tr class="table-primary">
                            <td class="fw-bold">ИТОГО</td>
                            <td class="text-end fw-bold fs-5"><?= $model->getFormattedTotal() ?></td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Действия</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <?php if ($model->status == TeacherSalary::STATUS_DRAFT): ?>
                            <a href="<?= Url::to(['update', 'id' => $model->id]) ?>" class="btn btn-outline-primary">
                                <i class="fas fa-edit"></i> Редактировать
                            </a>
                            <a href="<?= Url::to(['recalculate', 'id' => $model->id]) ?>" class="btn btn-outline-secondary">
                                <i class="fas fa-sync"></i> Пересчитать
                            </a>
                            <?= Html::beginForm(['approve', 'id' => $model->id], 'post') ?>
                            <button type="submit" class="btn btn-success w-100">
                                <i class="fas fa-check"></i> Утвердить
                            </button>
                            <?= Html::endForm() ?>
                        <?php endif; ?>

                        <?php if ($model->status == TeacherSalary::STATUS_APPROVED): ?>
                            <?= Html::beginForm(['pay', 'id' => $model->id], 'post') ?>
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-money-bill"></i> Отметить как выплаченную
                            </button>
                            <?= Html::endForm() ?>
                        <?php endif; ?>

                        <?php if ($model->status == TeacherSalary::STATUS_PAID): ?>
                            <div class="alert alert-success mb-0">
                                <i class="fas fa-check-circle"></i>
                                Выплачено: <?= Yii::$app->formatter->asDatetime($model->paid_at, 'php:d.m.Y H:i') ?>
                            </div>
                        <?php endif; ?>

                        <?php if ($model->status != TeacherSalary::STATUS_PAID): ?>
                            <?= Html::beginForm(['delete', 'id' => $model->id], 'post', [
                                'onsubmit' => 'return confirm("Удалить эту зарплату?")'
                            ]) ?>
                            <button type="submit" class="btn btn-outline-danger w-100">
                                <i class="fas fa-trash"></i> Удалить
                            </button>
                            <?= Html::endForm() ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <?php if ($model->notes): ?>
                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Примечания</h5>
                    </div>
                    <div class="card-body">
                        <?= nl2br(Html::encode($model->notes)) ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Детализация</h5>
                </div>
                <div class="card-body p-0">
                    <table class="table table-striped mb-0">
                        <thead class="table-light">
                        <tr>
                            <th>Дата</th>
                            <th>Группа</th>
                            <th class="text-center">Учеников</th>
                            <th>Ставка</th>
                            <th class="text-end">Сумма</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($details)): ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">
                                    Нет данных
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($details as $detail): ?>
                                <tr>
                                    <td><?= Yii::$app->formatter->asDate($detail->lesson_date, 'php:d.m.Y') ?></td>
                                    <td>
                                        <?php if ($detail->group): ?>
                                            <?= Html::encode($detail->group->name) ?>
                                        <?php else: ?>
                                            <span class="text-muted">Группа #<?= $detail->group_id ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center"><?= $detail->students_paid ?></td>
                                    <td>
                                        <small class="text-muted"><?= $detail->getRateTypeLabel() ?>:</small>
                                        <?= $detail->getFormattedRate() ?>
                                    </td>
                                    <td class="text-end fw-bold"><?= $detail->getFormattedAmount() ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        </tbody>
                        <tfoot class="table-primary">
                        <tr>
                            <th colspan="4">Итого</th>
                            <th class="text-end"><?= number_format($model->base_amount, 0, ',', ' ') ?> ₸</th>
                        </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
