<?php

namespace app\modules\crm\controllers;

use app\helpers\OrganizationRoles;
use app\helpers\OrganizationUrl;
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
                            'roles' => [
                                SystemRoles::SUPER,
                                OrganizationRoles::DIRECTOR,
                                OrganizationRoles::GENERAL_DIRECTOR,
                            ]
                        ],
                        [
                            'allow' => false,
                            'roles' => ['?']
                        ]
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
     * Главная страница настроек (редирект на доступы)
     * @return \yii\web\Response
     */
    public function actionIndex()
    {
        return $this->redirect(['access']);
    }
}
