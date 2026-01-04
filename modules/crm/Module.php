<?php

namespace app\modules\crm;

use Yii;
use yii\web\ForbiddenHttpException;

/**
 * CRM module - основной функционал CRM системы
 * Доступен только для авторизованных пользователей
 */
class Module extends \yii\base\Module
{
    /**
     * {@inheritdoc}
     */
    public $controllerNamespace = 'app\modules\crm\controllers';

    /**
     * {@inheritdoc}
     */
    public $defaultRoute = 'default';

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();

        // Проверка авторизации для всего модуля
        if (Yii::$app->user->isGuest) {
            Yii::$app->response->redirect(['/login'])->send();
            exit;
        }

        // Устанавливаем layout для модуля CRM
        $this->layout = 'main';
    }
}
