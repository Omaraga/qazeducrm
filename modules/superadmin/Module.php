<?php

namespace app\modules\superadmin;

use Yii;
use yii\web\ForbiddenHttpException;

/**
 * Модуль супер-админки для управления SaaS платформой.
 *
 * Доступен только пользователям с ролью SUPER.
 * Позволяет управлять организациями, подписками, платежами и тарифами.
 */
class Module extends \yii\base\Module
{
    public $controllerNamespace = 'app\modules\superadmin\controllers';

    public $layout = 'main';

    public function init()
    {
        parent::init();

        // Устанавливаем путь к views модуля
        $this->setViewPath('@app/modules/superadmin/views');
        $this->setLayoutPath('@app/modules/superadmin/views/layouts');
    }

    public function beforeAction($action)
    {
        if (!parent::beforeAction($action)) {
            return false;
        }

        // Проверяем авторизацию
        if (Yii::$app->user->isGuest) {
            Yii::$app->user->loginRequired();
            return false;
        }

        // Проверяем роль SUPER
        if (!Yii::$app->user->can('SUPER')) {
            throw new ForbiddenHttpException(Yii::t('main', 'У вас нет доступа к этому разделу.'));
        }

        return true;
    }
}
