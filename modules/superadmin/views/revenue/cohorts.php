<?php

/** @var yii\web\View $this */
/** @var array $cohorts */
/** @var int $months */

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Когортный анализ';
$this->params['breadcrumbs'][] = ['label' => 'Аналитика', 'url' => ['index']];
$this->params['breadcrumbs'][] = 'Когортный анализ';
?>

<div class="mb-4">
    <a href="<?= Url::to(['index']) ?>" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left"></i> К дашборду
    </a>
</div>

<div class="card card-custom mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="fas fa-users"></i> Retention когорт</span>
        <form method="get" class="form-inline">
            <label class="mr-2">Период:</label>
            <select name="months" class="form-control form-control-sm" onchange="this.form.submit()">
                <option value="6" <?= $months == 6 ? 'selected' : '' ?>>6 месяцев</option>
                <option value="12" <?= $months == 12 ? 'selected' : '' ?>>12 месяцев</option>
                <option value="18" <?= $months == 18 ? 'selected' : '' ?>>18 месяцев</option>
                <option value="24" <?= $months == 24 ? 'selected' : '' ?>>24 месяца</option>
            </select>
        </form>
    </div>
    <div class="card-body p-0">
        <?php if (empty($cohorts)): ?>
            <p class="text-muted text-center py-4">Нет данных для отображения</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-bordered table-sm mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th style="width: 120px;">Когорта</th>
                            <th style="width: 80px;">Размер</th>
                            <?php
                            // Собираем все уникальные месяцы
                            $allMonths = [];
                            foreach ($cohorts as $cohortMonth => $data) {
                                foreach (array_keys($data['retention']) as $m) {
                                    if (!in_array($m, $allMonths)) {
                                        $allMonths[] = $m;
                                    }
                                }
                            }
                            sort($allMonths);

                            foreach ($allMonths as $i => $m):
                            ?>
                                <th class="text-center" style="width: 70px;">M<?= $i ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cohorts as $cohortMonth => $data): ?>
                            <tr>
                                <td class="font-weight-bold"><?= $cohortMonth ?></td>
                                <td class="text-center"><?= $data['size'] ?></td>
                                <?php foreach ($allMonths as $m): ?>
                                    <td class="text-center">
                                        <?php if (isset($data['retention'][$m])): ?>
                                            <?php
                                            $retention = $data['retention'][$m];
                                            $bgColor = 'rgba(75, 192, 192, ' . ($retention / 100) . ')';
                                            $textColor = $retention > 50 ? 'white' : 'black';
                                            ?>
                                            <span class="badge p-2" style="background-color: <?= $bgColor ?>; color: <?= $textColor ?>; min-width: 45px;">
                                                <?= $retention ?>%
                                            </span>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="card card-custom">
    <div class="card-header">
        <i class="fas fa-info-circle"></i> Как читать когортный анализ
    </div>
    <div class="card-body">
        <ul class="mb-0">
            <li><strong>Когорта</strong> - месяц первого платежа организаций</li>
            <li><strong>Размер</strong> - количество организаций в когорте</li>
            <li><strong>M0, M1, M2...</strong> - процент организаций с активной подпиской через N месяцев</li>
            <li><strong>Цвет</strong> - чем темнее, тем выше retention</li>
            <li><strong>Норма</strong> - M1 > 80%, M12 > 60% считается хорошим показателем</li>
        </ul>
    </div>
</div>
