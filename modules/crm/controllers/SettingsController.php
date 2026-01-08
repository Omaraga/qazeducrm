<?php

namespace app\modules\crm\controllers;

use app\helpers\OrganizationRoles;
use app\helpers\OrganizationUrl;
use app\helpers\RoleChecker;
use app\helpers\SystemRoles;
use app\models\OrganizationAccessSettings;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;

/**
 * SettingsController - настройки организации
 */
class SettingsController extends Controller
{
    /**
     * @inheritDoc
     */
    public function behaviors()
    {
        return array_merge(
            parent::behaviors(),
            [
                'verbs' => [
                    'class' => VerbFilter::class,
                    'actions' => [
                        'access' => ['GET', 'POST'],
                    ],
                ],
                'access' => [
                    'class' => AccessControl::class,
                    'rules' => [
                        // Доступ к настройкам - только для директоров
                        [
                            'allow' => true,
                            'roles' => ['@'],
                            'matchCallback' => function ($rule, $action) {
                                return RoleChecker::isDirector();
                            }
                        ],
                    ],
                ],
            ]
        );
    }

    /**
     * Настройки прав доступа
     * @return string|\yii\web\Response
     */
    public function actionAccess()
    {
        $model = OrganizationAccessSettings::getForOrganization();
        $settings = $model->getSettingsArray();

        if ($this->request->isPost) {
            $postSettings = $this->request->post('settings', []);

            // Преобразуем checkbox значения в boolean
            $newSettings = [];
            foreach (OrganizationAccessSettings::DEFAULTS as $key => $default) {
                $newSettings[$key] = isset($postSettings[$key]) && $postSettings[$key];
            }

            $model->setSettingsArray($newSettings);

            if ($model->save()) {
                Yii::$app->session->setFlash('success', 'Настройки доступа сохранены');
                return $this->redirect(['access']);
            } else {
                Yii::$app->session->setFlash('error', 'Ошибка сохранения настроек');
            }

            $settings = $newSettings;
        }

        return $this->render('access', [
            'model' => $model,
            'settings' => $settings,
            'groups' => OrganizationAccessSettings::GROUPS,
            'labels' => OrganizationAccessSettings::LABELS,
            'hints' => OrganizationAccessSettings::HINTS,
        ]);
    }

    /**
     * AJAX сохранение одной настройки
     * @return array
     */
    public function actionAjaxSaveSetting()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        if (!$this->request->isAjax || !$this->request->isPost) {
            return ['success' => false, 'message' => 'Invalid request'];
        }

        $key = $this->request->post('key');
        $value = $this->request->post('value');

        if (!$key || !array_key_exists($key, OrganizationAccessSettings::DEFAULTS)) {
            return ['success' => false, 'message' => 'Invalid setting key'];
        }

        $model = OrganizationAccessSettings::getForOrganization();
        $model->setSetting($key, (bool)$value);

        if ($model->save()) {
            return [
                'success' => true,
                'message' => 'Сохранено',
                'key' => $key,
                'value' => (bool)$value
            ];
        }

        return ['success' => false, 'message' => 'Ошибка сохранения'];
    }

    /**
     * Главная страница настроек (редирект на доступы)
     * @return \yii\web\Response
     */
    public function actionIndex()
    {
        return $this->redirect(['access']);
    }
}
