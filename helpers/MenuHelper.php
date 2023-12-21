<?php

namespace app\helpers;
use app\models\Settings;
use yii\base\Model;
use yii\bootstrap4\Html;
class MenuHelper extends Model
{
    /**
     * @return mixed|string|null
     */
    public static function getUrl(){
        $settings = self::getSetting();
        if ($settings && $settings->url){
            return $settings->url;
        }else{
            return \Yii::$app->params['url'];
        }
    }

    /**
     * @return array|Settings|\yii\db\ActiveRecord
     */
    public static function getSetting(){
       return Settings::find()->one() ? : new Settings();
    }

    public static function getLogo($mini = false){
        $settings = self::getSetting();
        if ($mini){
            $link = ($settings->logo) ? self::getUrl().$settings->logo :'/images/logo_star_mini.jpg';
            return '<img src="'.$link.'" style = "max-width:50px"/>';
        }else{
            $link = ($settings->logo) ? self::getUrl().$settings->logo :'/images/logo_star_black.png';
            return '<img src="'.$link.'" style = "max-width:100px;float: left;margin-left: 10px;"/>';
        }
    }

    public static function getMenuItems(){
        $items = [];
        if (\Yii::$app->user->isGuest){
            $items[] = ['label' => 'Login', 'url' => \app\helpers\OrganizationUrl::to(['/site/login'])];
        }else{
            $user = \Yii::$app->user->identity;
            $roles = $user->rolesMap;
            $menuRoles = [];
            foreach ($roles as $roleId => $name){
                $menuRoles[] = ['label' => $name, 'url' => \app\helpers\OrganizationUrl::to(['site/change-role', 'id' => $roleId])];
            }
            if (\Yii::$app->user->can(OrganizationRoles::ADMIN)){
                $items[] = ['label' => 'Тарифы', 'url' => \app\helpers\OrganizationUrl::to(['/tariff/index'])];
            }

            $items[] = ['label' => 'Роли', 'items' => $menuRoles, 'options' => ['class' => 'ml-4']];
            $items[] = '<li>'
                . Html::beginForm(['/site/logout'], 'post', ['class' => 'form-inline ml-4'])
                . Html::submitButton(
                    'Logout (' . \Yii::$app->user->identity->username . ')',
                    ['class' => 'btn btn-link logout']
                )
                . Html::endForm()
                . '</li>';
        }

        return $items;
    }

    public static function isMenuActive($url){
        $route =  \Yii::$app->controller->getRoute(); //TODO написать route
        $routeArray = explode('/',$route);
        $urlArray = explode('/', $url);
        if ($routeArray[0] == $urlArray[0]){
            return true;
        }else{
            return false;
        }
    }

    public static function getName(){
        $setting = self::getSetting();
        if ($setting->name){
            return $setting->name;
        }else{
            return 'Сайт';
        }
    }

    public static function normalizeUrl($url){
        if ($url && strlen($url) > 0){
            return ($url[0] == '/' || str_contains($url, 'http')) ? $url : '/'.$url;
        }else{
            return '#';
        }
    }


}