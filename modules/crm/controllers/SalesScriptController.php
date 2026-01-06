<?php

namespace app\modules\crm\controllers;

use app\helpers\OrganizationRoles;
use app\helpers\SystemRoles;
use app\models\Lids;
use app\models\Organizations;
use app\models\SalesScript;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * SalesScriptController - управление скриптами продаж
 */
class SalesScriptController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return array_merge(
            parent::behaviors(),
            [
                'verbs' => [
                    'class' => VerbFilter::class,
                    'actions' => [
                        'delete' => ['POST'],
                        'save' => ['POST'],
                        'toggle' => ['POST'],
                    ],
                ],
                'access' => [
                    'class' => AccessControl::class,
                    'rules' => [
                        [
                            'allow' => true,
                            'roles' => [
                                SystemRoles::SUPER,
                                OrganizationRoles::ADMIN,
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
     * Список скриптов
     */
    public function actionIndex()
    {
        $scripts = SalesScript::find()
            ->byOrganization()
            ->orderBy(['status' => SORT_ASC, 'sort_order' => SORT_ASC])
            ->all();

        // Если скриптов нет - создаём дефолтные
        if (empty($scripts)) {
            SalesScript::createDefaults(Organizations::getCurrentOrganizationId());
            $scripts = SalesScript::find()
                ->byOrganization()
                ->orderBy(['status' => SORT_ASC, 'sort_order' => SORT_ASC])
                ->all();
        }

        // Группируем по статусам
        $grouped = [];
        foreach ($scripts as $script) {
            $statusLabel = Lids::getStatusList()[$script->status] ?? $script->status;
            if (!isset($grouped[$script->status])) {
                $grouped[$script->status] = [
                    'label' => $statusLabel,
                    'scripts' => [],
                ];
            }
            $grouped[$script->status]['scripts'][] = $script;
        }

        return $this->render('index', [
            'grouped' => $grouped,
            'statuses' => Lids::getStatusList(),
        ]);
    }

    /**
     * Создать новый скрипт
     */
    public function actionCreate()
    {
        $model = new SalesScript();
        $model->organization_id = Organizations::getCurrentOrganizationId();
        $model->is_active = true;

        if ($model->load(Yii::$app->request->post())) {
            // Обработка возражений и советов
            $objections = Yii::$app->request->post('objections', []);
            $tips = Yii::$app->request->post('tips', []);

            $model->setObjectionsArray(array_filter($objections, fn($o) => !empty($o['objection'])));
            $model->setTipsArray(array_filter($tips));

            if ($model->save()) {
                Yii::$app->session->setFlash('success', 'Скрипт создан');
                return $this->redirect(['index']);
            }
        }

        return $this->render('form', [
            'model' => $model,
            'statuses' => Lids::getStatusList(),
        ]);
    }

    /**
     * Редактировать скрипт
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post())) {
            // Обработка возражений и советов
            $objections = Yii::$app->request->post('objections', []);
            $tips = Yii::$app->request->post('tips', []);

            $model->setObjectionsArray(array_filter($objections, fn($o) => !empty($o['objection'])));
            $model->setTipsArray(array_filter($tips));

            if ($model->save()) {
                Yii::$app->session->setFlash('success', 'Скрипт обновлён');
                return $this->redirect(['index']);
            }
        }

        return $this->render('form', [
            'model' => $model,
            'statuses' => Lids::getStatusList(),
        ]);
    }

    /**
     * Удалить скрипт
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        $model->delete();

        Yii::$app->session->setFlash('success', 'Скрипт удалён');
        return $this->redirect(['index']);
    }

    /**
     * Переключить активность
     */
    public function actionToggle($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $model = $this->findModel($id);
        $model->is_active = !$model->is_active;

        if ($model->save(false)) {
            return [
                'success' => true,
                'is_active' => $model->is_active,
                'message' => $model->is_active ? 'Скрипт активирован' : 'Скрипт деактивирован',
            ];
        }

        return ['success' => false, 'message' => 'Ошибка'];
    }

    /**
     * Поиск модели
     */
    protected function findModel($id)
    {
        $model = SalesScript::find()
            ->byOrganization()
            ->andWhere(['id' => $id])
            ->one();

        if ($model === null) {
            throw new NotFoundHttpException('Скрипт не найден');
        }

        return $model;
    }
}
