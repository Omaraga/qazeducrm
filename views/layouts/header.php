<?php

use yii\bootstrap4\Nav;
use yii\bootstrap4\NavBar;


?>

<header>
    <?php
    NavBar::begin([
        'brandLabel' =>'<img src="/images/logo-text.svg" style="width: 9rem;">',
        'brandUrl' => Yii::$app->homeUrl,
        'options' => [
            'class' => 'navbar navbar-expand-md navbar-dark bg-dark fixed-top',
        ],
    ]);
    echo Nav::widget([
        'options' => ['class' => 'navbar-nav'],
        'items' => \app\helpers\MenuHelper::getMenuItems()
    ]);
    NavBar::end();
    ?>
</header>