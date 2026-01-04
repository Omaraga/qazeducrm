<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;

/** @var yii\web\View $this */
/** @var app\models\User[] $teachers */
/** @var string $defaultStart */
/** @var string $defaultEnd */

$this->title = 'Расчёт зарплаты';
$this->params['breadcrumbs'][] = ['label' => 'Зарплаты учителей', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="salary-calculate">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><?= Html::encode($this->title) ?></h1>
        <a href="<?= Url::to(['index']) ?>" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Назад
        </a>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Параметры расчёта</h5>
                </div>
                <div class="card-body">
                    <form method="post">
                        <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->csrfToken) ?>

                        <div class="mb-3">
                            <label class="form-label">Преподаватель <span class="text-danger">*</span></label>
                            <select name="teacher_id" class="form-control" required>
                                <option value="">Выберите преподавателя...</option>
                                <?php foreach ($teachers as $teacher): ?>
                                    <option value="<?= $teacher->id ?>"><?= Html::encode($teacher->fio) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Начало периода <span class="text-danger">*</span></label>
                                    <input type="date" name="period_start" class="form-control"
                                           value="<?= date('Y-m-d', strtotime($defaultStart)) ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Конец периода <span class="text-danger">*</span></label>
                                    <input type="date" name="period_end" class="form-control"
                                           value="<?= date('Y-m-d', strtotime($defaultEnd)) ?>" required>
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            Будут учтены только завершённые уроки с выставленной посещаемостью.
                            Ставка берётся из настроек для учителя.
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-calculator"></i> Рассчитать
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Быстрый расчёт</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted">Выберите период для быстрого расчёта:</p>

                    <div class="d-grid gap-2">
                        <?php
                        $periods = [
                            [
                                'label' => 'Текущий месяц',
                                'start' => date('Y-m-01'),
                                'end' => date('Y-m-d'),
                            ],
                            [
                                'label' => 'Прошлый месяц',
                                'start' => date('Y-m-01', strtotime('-1 month')),
                                'end' => date('Y-m-t', strtotime('-1 month')),
                            ],
                            [
                                'label' => 'Последние 2 недели',
                                'start' => date('Y-m-d', strtotime('-2 weeks')),
                                'end' => date('Y-m-d'),
                            ],
                        ];
                        ?>
                        <?php foreach ($periods as $period): ?>
                            <button type="button" class="btn btn-outline-primary quick-period"
                                    data-start="<?= $period['start'] ?>"
                                    data-end="<?= $period['end'] ?>">
                                <?= $period['label'] ?>
                                <small class="d-block text-muted">
                                    <?= date('d.m.Y', strtotime($period['start'])) ?> - <?= date('d.m.Y', strtotime($period['end'])) ?>
                                </small>
                            </button>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="card-title mb-0">Подсказка</h5>
                </div>
                <div class="card-body">
                    <p class="mb-2"><strong>Алгоритм расчёта:</strong></p>
                    <ol class="mb-0 ps-3">
                        <li>Система находит все завершённые уроки преподавателя за период</li>
                        <li>Для каждого урока считает учеников со статусом "Посещение" или "Пропуск с оплатой"</li>
                        <li>Применяет ставку преподавателя (за ученика, за урок или процент)</li>
                        <li>Суммирует все начисления</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.querySelectorAll('.quick-period').forEach(function(btn) {
    btn.addEventListener('click', function() {
        document.querySelector('input[name="period_start"]').value = this.dataset.start;
        document.querySelector('input[name="period_end"]').value = this.dataset.end;
    });
});
</script>
