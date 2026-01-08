<?php

namespace app\modules\superadmin\controllers;

use app\models\Settings;
use app\helpers\SettingsHelper;
use Yii;
use yii\web\Controller;

/**
 * SettingsController - управление глобальными настройками системы
 */
class SettingsController extends Controller
{
    /**
     * Редактирование настроек лендинга и системы
     */
    public function actionIndex()
    {
        $model = Settings::find()->one();
        if (!$model) {
            $model = new Settings();
        }
        $model->loadDefaultValues();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            // Сброс кэша настроек
            SettingsHelper::clearCache();

            Yii::$app->session->setFlash('success', 'Настройки успешно сохранены.');
            return $this->redirect(['index']);
        }

        return $this->render('index', [
            'model' => $model,
        ]);
    }
}
