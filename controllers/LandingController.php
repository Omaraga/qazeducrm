<?php

namespace app\controllers;

use app\models\SaasPlan;
use Yii;
use yii\web\Controller;

/**
 * LandingController - публичные страницы сайта
 */
class LandingController extends Controller
{
    /**
     * Используем landing layout
     */
    public $layout = 'landing';

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            // Все страницы публичные
        ];
    }

    /**
     * Главная страница
     */
    public function actionIndex()
    {
        // Если пользователь авторизован, перенаправляем в CRM
        if (!Yii::$app->user->isGuest) {
            return $this->redirect(['/site/index']);
        }

        $plans = SaasPlan::find()
            ->where(['is_active' => 1])
            ->orderBy(['sort_order' => SORT_ASC])
            ->limit(3)
            ->all();

        return $this->render('index', [
            'plans' => $plans,
        ]);
    }

    /**
     * Страница тарифов
     */
    public function actionPricing()
    {
        $plans = SaasPlan::find()
            ->where(['is_active' => 1])
            ->orderBy(['sort_order' => SORT_ASC])
            ->all();

        return $this->render('pricing', [
            'plans' => $plans,
        ]);
    }

    /**
     * Страница возможностей
     */
    public function actionFeatures()
    {
        return $this->render('features');
    }

    /**
     * Страница контактов
     */
    public function actionContact()
    {
        return $this->render('contact');
    }
}
