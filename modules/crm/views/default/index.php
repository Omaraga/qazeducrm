<?php

/** @var yii\web\View $this */
/** @var array $data */
/** @var string $week */

use yii\helpers\Url;

$chartData = '[' . implode(',', $data) . ']';
$js = <<<JS
const ctx = document.getElementById('weekChart');
const labels = $week;
const data = {
  labels: labels,
  datasets: [{
    label: 'Сумма платежей',
    data: $chartData,
    backgroundColor: 'rgba(254, 141, 0, 0.2)',
    borderColor: 'rgb(254, 141, 0)',
    borderWidth: 2,
    borderRadius: 4,
  }]
};
const config = {
  type: 'bar',
  data: data,
  options: {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      legend: { display: false }
    },
    scales: {
      y: { beginAtZero: true }
    }
  },
};
new Chart(ctx, config);
JS;
$this->registerJs($js);
?>

<style>
.stat-card {
    background: var(--bg-white);
    border-radius: var(--radius-lg);
    padding: 1.5rem;
    border: 1px solid var(--border);
    transition: all var(--transition);
}
.stat-card:hover {
    box-shadow: var(--shadow-md);
}
.stat-icon {
    width: 48px;
    height: 48px;
    border-radius: var(--radius-md);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
    color: var(--text-white);
}
.stat-value {
    font-size: 1.75rem;
    font-weight: 700;
    color: var(--text-primary);
}
.stat-label {
    color: var(--text-muted);
    font-size: 0.875rem;
}
.chart-card {
    background: var(--bg-white);
    border-radius: var(--radius-lg);
    padding: 1.5rem;
    border: 1px solid var(--border);
}
.chart-title {
    font-weight: 600;
    color: var(--text-primary);
    margin-bottom: 1rem;
}
.quick-action {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 1rem;
    background: var(--bg-white);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    color: var(--text-primary);
    text-decoration: none;
    transition: all var(--transition);
}
.quick-action:hover {
    border-color: var(--primary);
    box-shadow: var(--shadow);
    text-decoration: none;
    color: var(--primary);
}
.quick-action i {
    width: 36px;
    height: 36px;
    background: var(--primary-light);
    border-radius: var(--radius);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--primary);
}
</style>

<!-- Stats -->
<div class="row g-4 mb-4">
    <div class="col-sm-6 col-lg-3">
        <div class="stat-card">
            <div class="d-flex align-items-center gap-3">
                <div class="stat-icon" style="background: var(--primary);">
                    <i class="fas fa-user-graduate"></i>
                </div>
                <div>
                    <div class="stat-value">—</div>
                    <div class="stat-label">Учеников</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="stat-card">
            <div class="d-flex align-items-center gap-3">
                <div class="stat-icon" style="background: var(--info);">
                    <i class="fas fa-users"></i>
                </div>
                <div>
                    <div class="stat-value">—</div>
                    <div class="stat-label">Групп</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="stat-card">
            <div class="d-flex align-items-center gap-3">
                <div class="stat-icon" style="background: var(--success);">
                    <i class="fas fa-money-bill-wave"></i>
                </div>
                <div>
                    <div class="stat-value">—</div>
                    <div class="stat-label">Доход (мес)</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="stat-card">
            <div class="d-flex align-items-center gap-3">
                <div class="stat-icon" style="background: var(--warning);">
                    <i class="fas fa-exclamation-circle"></i>
                </div>
                <div>
                    <div class="stat-value">—</div>
                    <div class="stat-label">Должников</div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Chart -->
    <div class="col-lg-8">
        <div class="chart-card">
            <h5 class="chart-title">Платежи за неделю</h5>
            <div style="height: 300px;">
                <canvas id="weekChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="col-lg-4">
        <div class="chart-card">
            <h5 class="chart-title">Быстрые действия</h5>
            <div class="d-flex flex-column gap-3">
                <a href="<?= Url::to(['/crm/pupil/create']) ?>" class="quick-action">
                    <i class="fas fa-user-plus"></i>
                    <span>Добавить ученика</span>
                </a>
                <a href="<?= Url::to(['/crm/group/create']) ?>" class="quick-action">
                    <i class="fas fa-users"></i>
                    <span>Создать группу</span>
                </a>
                <a href="<?= Url::to(['/crm/payment/create']) ?>" class="quick-action">
                    <i class="fas fa-plus-circle"></i>
                    <span>Принять платёж</span>
                </a>
                <a href="<?= Url::to(['/crm/schedule/index']) ?>" class="quick-action">
                    <i class="fas fa-calendar-alt"></i>
                    <span>Расписание</span>
                </a>
            </div>
        </div>
    </div>
</div>
