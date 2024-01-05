<?php

/** @var yii\web\View $this */
/** @var array $data */
/** @var string $wek */
$this->title = 'Qazaq Education';
$data = '['.implode(',', $data).']';
$js = <<<JS
const ctx = document.getElementById('myChart');
const labels = $week;
const data = {
  labels: labels,
  datasets: [{
    label: 'Статистика прихода за неделю',
    data: $data,
    backgroundColor: [
      'rgba(255, 99, 132, 0.2)',
      'rgba(255, 159, 64, 0.2)',
      'rgba(255, 205, 86, 0.2)',
      'rgba(75, 192, 192, 0.2)',
      'rgba(54, 162, 235, 0.2)',
      'rgba(153, 102, 255, 0.2)',
      'rgba(201, 203, 207, 0.2)'
    ],
    borderColor: [
      'rgb(255, 99, 132)',
      'rgb(255, 159, 64)',
      'rgb(255, 205, 86)',
      'rgb(75, 192, 192)',
      'rgb(54, 162, 235)',
      'rgb(153, 102, 255)',
      'rgb(201, 203, 207)'
    ],
    borderWidth: 1
  }]
};
const config = {
  type: 'bar',
  data: data,
  options: {
    scales: {
      y: {
        beginAtZero: true
      }
    }
  },
};
new Chart(ctx, config);
JS;
$this->registerJs($js);
?>
<div class="site-index">
    <div class="text-center bg-transparent">
        <h1 class="display-4">Добро пожаловать!</h1>
        <p><a class="btn btn-lg btn-success" href="<?=\app\helpers\OrganizationUrl::to(['pupil/index']);?>">Начать</a></p>
    </div>
    <div>
        <canvas id="myChart"></canvas>
    </div>
</div>

