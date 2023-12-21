<?php

use yii\bootstrap4\Nav;
use yii\bootstrap4\NavBar;
use yii\bootstrap4\Html;

if (!Yii::$app->user->isGuest){
    $user = Yii::$app->user->identity;
    $roles = $user->rolesMap;
}else{
    $roles = [];
}
$menuRoles = [];
foreach ($roles as $roleId => $name){
    $menuRoles[] = ['label' => $name, 'url' => \app\helpers\OrganizationUrl::to(['site/change-role', 'id' => $roleId])];
}
?>

<header>
    <?php
    NavBar::begin([
        'brandLabel' =>'<img src="/images/logo-text.svg" style="width: 10rem;">',
        'brandUrl' => Yii::$app->homeUrl,
        'options' => [
            'class' => 'navbar navbar-expand-md navbar-dark bg-dark fixed-top',
        ],
    ]);
    echo Nav::widget([
        'options' => ['class' => 'navbar-nav'],
        'items' => [
            ['label' => 'Home', 'url' => \app\helpers\OrganizationUrl::to(['/site/index'])],
            ['label' => 'About', 'url' => \app\helpers\OrganizationUrl::to(['/site/about'])],
            ['label' => 'Contact', 'url' => \app\helpers\OrganizationUrl::to(['/site/contact'])],
            !Yii::$app->user->isGuest && $menuRoles && sizeof($menuRoles) > 0 ?(
            [
                'label' => 'Роли',
                'items' => $menuRoles,
            ]) :  ['label' => 'Contact', 'url' => \app\helpers\OrganizationUrl::to(['/site/contact'])],
            Yii::$app->user->isGuest ? (
            ['label' => 'Login', 'url' => \app\helpers\OrganizationUrl::to(['/site/login'])]
            ) : (
                '<li>'
                . Html::beginForm(['/site/logout'], 'post', ['class' => 'form-inline'])
                . Html::submitButton(
                    'Logout (' . Yii::$app->user->identity->username . ')',
                    ['class' => 'btn btn-link logout']
                )
                . Html::endForm()
                . '</li>'
            )
        ],
    ]);
    NavBar::end();
    ?>
</header>