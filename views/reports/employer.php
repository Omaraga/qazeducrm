<?php
/** @var yii\web\View $this */
/** @var \app\models\User[] $teachers */
/** @var \app\models\search\DateSearch $searchModel */
?>
<h1>Отчет. Заработная плата преподавателей</h1>

<?php  echo $this->render('_search', ['model' => $searchModel]); ?>
<p>
    You may change the content of this page by modifying
    the file <code><?= __FILE__; ?></code>.
</p>
