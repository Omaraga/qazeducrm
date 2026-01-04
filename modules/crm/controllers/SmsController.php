<?php

namespace app\modules\crm\controllers;

use app\models\Organizations;
use app\models\SmsLog;
use app\models\SmsTemplate;
use app\models\search\SmsLogSearch;
use app\services\SmsService;
use Yii;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * SmsController - управление SMS уведомлениями
 */
class SmsController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete-template' => ['POST'],
                    'send' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Лог отправленных SMS
     */
    public function actionIndex()
    {
        $query = SmsLog::find()
            ->andWhere(['sms_log.organization_id' => Organizations::getCurrentOrganizationId()])
            ->orderBy(['created_at' => SORT_DESC]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => ['pageSize' => 50],
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Список шаблонов
     */
    public function actionTemplates()
    {
        $query = SmsTemplate::find()
            ->andWhere(['sms_template.organization_id' => Organizations::getCurrentOrganizationId()])
            ->andWhere(['!=', 'sms_template.is_deleted', 1])
            ->orderBy(['code' => SORT_ASC]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => false,
        ]);

        return $this->render('templates', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Создание шаблона
     */
    public function actionCreateTemplate()
    {
        $model = new SmsTemplate();
        $model->organization_id = Organizations::getCurrentOrganizationId();
        $model->is_active = true;

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Шаблон создан');
            return $this->redirect(['templates']);
        }

        return $this->render('template-form', [
            'model' => $model,
        ]);
    }

    /**
     * Редактирование шаблона
     */
    public function actionUpdateTemplate($id)
    {
        $model = $this->findTemplate($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Шаблон обновлён');
            return $this->redirect(['templates']);
        }

        return $this->render('template-form', [
            'model' => $model,
        ]);
    }

    /**
     * Удаление шаблона
     */
    public function actionDeleteTemplate($id)
    {
        $model = $this->findTemplate($id);
        $model->delete();

        Yii::$app->session->setFlash('success', 'Шаблон удалён');
        return $this->redirect(['templates']);
    }

    /**
     * Создать стандартные шаблоны
     */
    public function actionCreateDefaults()
    {
        $orgId = Organizations::getCurrentOrganizationId();

        // Проверяем, есть ли уже шаблоны
        $existing = SmsTemplate::find()
            ->andWhere(['organization_id' => $orgId])
            ->andWhere(['!=', 'is_deleted', 1])
            ->count();

        if ($existing > 0) {
            Yii::$app->session->setFlash('info', 'Шаблоны уже существуют');
        } else {
            SmsTemplate::createDefaults($orgId);
            Yii::$app->session->setFlash('success', 'Созданы стандартные шаблоны');
        }

        return $this->redirect(['templates']);
    }

    /**
     * Настройки SMS
     */
    public function actionSettings()
    {
        $org = Organizations::findOne(Organizations::getCurrentOrganizationId());

        if (Yii::$app->request->isPost) {
            $org->sms_provider = Yii::$app->request->post('sms_provider');
            $org->sms_api_key = Yii::$app->request->post('sms_api_key');
            $org->sms_sender = Yii::$app->request->post('sms_sender');

            if ($org->save(false)) {
                Yii::$app->session->setFlash('success', 'Настройки сохранены');
                return $this->redirect(['settings']);
            }
        }

        return $this->render('settings', [
            'org' => $org,
            'providers' => SmsService::getProviderList(),
        ]);
    }

    /**
     * Отправить тестовое SMS
     */
    public function actionTestSend()
    {
        if (Yii::$app->request->isPost) {
            $phone = Yii::$app->request->post('phone');
            $message = Yii::$app->request->post('message');

            $smsService = new SmsService();
            $log = $smsService->send($phone, $message);

            if ($log->status === SmsLog::STATUS_SENT) {
                Yii::$app->session->setFlash('success', 'SMS отправлено');
            } else {
                Yii::$app->session->setFlash('error', 'Ошибка: ' . $log->error_message);
            }
        }

        return $this->redirect(['settings']);
    }

    /**
     * Найти шаблон по ID
     */
    protected function findTemplate($id)
    {
        $model = SmsTemplate::find()
            ->andWhere(['id' => $id])
            ->andWhere(['organization_id' => Organizations::getCurrentOrganizationId()])
            ->andWhere(['!=', 'is_deleted', 1])
            ->one();

        if ($model === null) {
            throw new NotFoundHttpException('Шаблон не найден');
        }

        return $model;
    }
}
