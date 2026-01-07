<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;

/**
 * Контроллер для управления impersonate сессией
 *
 * Позволяет завершить сессию impersonate и вернуться к оригинальному пользователю.
 */
class ImpersonateController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'], // Только авторизованные пользователи
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'stop' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Завершить impersonate сессию и вернуться к оригинальному пользователю
     *
     * @return \yii\web\Response
     */
    public function actionStop()
    {
        if (!Yii::$app->has('impersonate')) {
            Yii::$app->session->setFlash('error', 'Компонент impersonate не настроен.');
            return $this->redirect(['/crm']);
        }

        if (!Yii::$app->impersonate->isImpersonating()) {
            Yii::$app->session->setFlash('warning', 'Вы не находитесь в режиме имитации.');
            return $this->redirect(['/crm']);
        }

        if (Yii::$app->impersonate->stop()) {
            Yii::$app->session->setFlash('success', 'Вы вернулись к своему аккаунту.');
            return $this->redirect(['/superadmin']);
        }

        Yii::$app->session->setFlash('error', 'Не удалось завершить сессию имитации.');
        return $this->redirect(['/crm']);
    }
}
