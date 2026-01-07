<?php

namespace app\modules\superadmin\controllers;

use app\models\Organizations;
use app\models\OrganizationAddon;
use app\models\OrganizationActivityLog;
use app\models\SaasFeature;
use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;

/**
 * AddonController - управление аддонами организаций.
 */
class AddonController extends Controller
{
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                    'activate' => ['POST'],
                    'cancel' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Список всех аддонов
     */
    public function actionIndex()
    {
        $query = OrganizationAddon::find()
            ->with(['organization', 'feature'])
            ->orderBy(['created_at' => SORT_DESC]);

        // Фильтры
        $status = Yii::$app->request->get('status');
        if ($status) {
            $query->andWhere(['status' => $status]);
        }

        $organizationId = Yii::$app->request->get('organization_id');
        if ($organizationId) {
            $query->andWhere(['organization_id' => $organizationId]);
        }

        $featureId = Yii::$app->request->get('feature_id');
        if ($featureId) {
            $query->andWhere(['feature_id' => $featureId]);
        }

        // Истекающие скоро
        $expiring = Yii::$app->request->get('expiring');
        if ($expiring) {
            $query->andWhere(['in', 'status', ['trial', 'active']])
                ->andWhere(['<=', 'expires_at', date('Y-m-d H:i:s', strtotime('+7 days'))])
                ->andWhere(['>=', 'expires_at', date('Y-m-d H:i:s')]);
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => ['pageSize' => 20],
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'organizations' => $this->getOrganizationsList(),
            'features' => $this->getFeaturesList(),
        ]);
    }

    /**
     * Просмотр аддона
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);

        return $this->render('view', [
            'model' => $model,
        ]);
    }

    /**
     * Создание аддона для организации
     */
    public function actionCreate($organization_id = null)
    {
        $model = new OrganizationAddon();
        $model->status = OrganizationAddon::STATUS_ACTIVE;
        $model->billing_period = OrganizationAddon::PERIOD_MONTHLY;
        $model->quantity = 1;

        if ($organization_id) {
            $model->organization_id = $organization_id;
        }

        if ($model->load(Yii::$app->request->post())) {
            $feature = SaasFeature::findOne($model->feature_id);

            // Устанавливаем цену из функции, если не указана
            if (!$model->price && $feature) {
                $model->price = $model->billing_period === OrganizationAddon::PERIOD_YEARLY
                    ? $feature->addon_price_yearly
                    : $feature->addon_price_monthly;
            }

            // Устанавливаем даты
            $model->started_at = date('Y-m-d H:i:s');
            $model->created_by = Yii::$app->user->id;

            if ($model->status === OrganizationAddon::STATUS_TRIAL) {
                $trialDays = $feature ? $feature->trial_days : 7;
                $model->trial_ends_at = date('Y-m-d H:i:s', strtotime("+{$trialDays} days"));
                $model->expires_at = $model->trial_ends_at;
            } else {
                $period = $model->billing_period === OrganizationAddon::PERIOD_YEARLY ? '+1 year' : '+1 month';
                $model->expires_at = date('Y-m-d H:i:s', strtotime($period));
            }

            // Обработка значений (для лимит-аддонов)
            $limitField = Yii::$app->request->post('limit_field');
            $limitValue = Yii::$app->request->post('limit_value');
            if ($limitField && $limitValue) {
                $model->value = [
                    'limit_field' => $limitField,
                    'limit_value' => (int)$limitValue,
                ];
            }

            if ($model->save()) {
                OrganizationActivityLog::log(
                    $model->organization_id,
                    'addon_created',
                    OrganizationActivityLog::CATEGORY_SUBSCRIPTION,
                    "Добавлен аддон: " . ($feature->name ?? 'ID ' . $model->feature_id)
                );

                Yii::$app->session->setFlash('success', 'Аддон добавлен.');
                return $this->redirect(['view', 'id' => $model->id]);
            }
        }

        return $this->render('create', [
            'model' => $model,
            'organizations' => $this->getOrganizationsList(),
            'features' => $this->getAddonFeatures(),
        ]);
    }

    /**
     * Редактирование аддона
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        // Загружаем значения для формы
        $limitField = $model->getValue('limit_field');
        $limitValue = $model->getValue('limit_value');

        if ($model->load(Yii::$app->request->post())) {
            // Обработка значений (для лимит-аддонов)
            $limitField = Yii::$app->request->post('limit_field');
            $limitValue = Yii::$app->request->post('limit_value');
            if ($limitField && $limitValue) {
                $model->value = [
                    'limit_field' => $limitField,
                    'limit_value' => (int)$limitValue,
                ];
            }

            if ($model->save()) {
                Yii::$app->session->setFlash('success', 'Аддон обновлён.');
                return $this->redirect(['view', 'id' => $model->id]);
            }
        }

        return $this->render('update', [
            'model' => $model,
            'organizations' => $this->getOrganizationsList(),
            'features' => $this->getAddonFeatures(),
            'limitField' => $limitField,
            'limitValue' => $limitValue,
        ]);
    }

    /**
     * Активация аддона
     */
    public function actionActivate($id)
    {
        $model = $this->findModel($id);
        $period = Yii::$app->request->post('period', $model->billing_period);
        $model->activate($period);

        OrganizationActivityLog::log(
            $model->organization_id,
            'addon_activated',
            OrganizationActivityLog::CATEGORY_SUBSCRIPTION,
            "Аддон активирован: " . $model->getFullName()
        );

        Yii::$app->session->setFlash('success', 'Аддон активирован.');
        return $this->redirect(['view', 'id' => $id]);
    }

    /**
     * Продление аддона
     */
    public function actionRenew($id)
    {
        $model = $this->findModel($id);

        if (Yii::$app->request->isPost) {
            $model->renew();

            OrganizationActivityLog::log(
                $model->organization_id,
                'addon_renewed',
                OrganizationActivityLog::CATEGORY_SUBSCRIPTION,
                "Аддон продлён: " . $model->getFullName()
            );

            Yii::$app->session->setFlash('success', 'Аддон продлён до ' . Yii::$app->formatter->asDate($model->expires_at, 'php:d.m.Y'));
            return $this->redirect(['view', 'id' => $id]);
        }

        return $this->render('renew', [
            'model' => $model,
        ]);
    }

    /**
     * Отмена аддона
     */
    public function actionCancel($id)
    {
        $model = $this->findModel($id);
        $model->cancel();

        OrganizationActivityLog::log(
            $model->organization_id,
            'addon_cancelled',
            OrganizationActivityLog::CATEGORY_SUBSCRIPTION,
            "Аддон отменён: " . $model->getFullName()
        );

        Yii::$app->session->setFlash('warning', 'Аддон отменён.');
        return $this->redirect(['view', 'id' => $id]);
    }

    /**
     * Удаление аддона
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        $organizationId = $model->organization_id;
        $name = $model->getFullName();

        $model->delete();

        OrganizationActivityLog::log(
            $organizationId,
            'addon_deleted',
            OrganizationActivityLog::CATEGORY_SUBSCRIPTION,
            "Аддон удалён: " . $name
        );

        Yii::$app->session->setFlash('danger', 'Аддон удалён.');
        return $this->redirect(['index']);
    }

    /**
     * Аддоны конкретной организации
     */
    public function actionOrganization($id)
    {
        $organization = Organizations::findOne($id);
        if (!$organization) {
            throw new NotFoundHttpException('Организация не найдена.');
        }

        $dataProvider = new ActiveDataProvider([
            'query' => OrganizationAddon::find()
                ->with('feature')
                ->where(['organization_id' => $id])
                ->orderBy(['created_at' => SORT_DESC]),
            'pagination' => ['pageSize' => 20],
        ]);

        return $this->render('organization', [
            'organization' => $organization,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Список доступных функций-аддонов
     */
    public function actionFeatures()
    {
        $features = SaasFeature::findAddons();

        return $this->render('features', [
            'features' => $features,
        ]);
    }

    /**
     * Статистика по аддонам
     */
    public function actionStats()
    {
        // Общая статистика
        $stats = [
            'total' => OrganizationAddon::find()->count(),
            'active' => OrganizationAddon::find()->where(['status' => OrganizationAddon::STATUS_ACTIVE])->count(),
            'trial' => OrganizationAddon::find()->where(['status' => OrganizationAddon::STATUS_TRIAL])->count(),
            'expired' => OrganizationAddon::find()->where(['status' => OrganizationAddon::STATUS_EXPIRED])->count(),
            'cancelled' => OrganizationAddon::find()->where(['status' => OrganizationAddon::STATUS_CANCELLED])->count(),
        ];

        // Выручка от аддонов (активных)
        $revenue = OrganizationAddon::find()
            ->where(['in', 'status', [OrganizationAddon::STATUS_ACTIVE, OrganizationAddon::STATUS_TRIAL]])
            ->sum('price') ?? 0;

        // По типам аддонов
        $byFeature = OrganizationAddon::find()
            ->alias('oa')
            ->select(['f.name', 'f.code', 'COUNT(*) as count', 'SUM(oa.price) as revenue'])
            ->innerJoin(['f' => SaasFeature::tableName()], 'oa.feature_id = f.id')
            ->where(['in', 'oa.status', [OrganizationAddon::STATUS_ACTIVE, OrganizationAddon::STATUS_TRIAL]])
            ->groupBy(['f.id'])
            ->orderBy(['count' => SORT_DESC])
            ->asArray()
            ->all();

        // Истекающие в ближайшие 7 дней
        $expiringSoon = OrganizationAddon::find()
            ->with(['organization', 'feature'])
            ->where(['in', 'status', [OrganizationAddon::STATUS_ACTIVE, OrganizationAddon::STATUS_TRIAL]])
            ->andWhere(['<=', 'expires_at', date('Y-m-d H:i:s', strtotime('+7 days'))])
            ->andWhere(['>=', 'expires_at', date('Y-m-d H:i:s')])
            ->orderBy(['expires_at' => SORT_ASC])
            ->limit(10)
            ->all();

        return $this->render('stats', [
            'stats' => $stats,
            'revenue' => $revenue,
            'byFeature' => $byFeature,
            'expiringSoon' => $expiringSoon,
        ]);
    }

    /**
     * Найти модель по ID
     */
    protected function findModel($id)
    {
        if (($model = OrganizationAddon::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('Аддон не найден.');
    }

    /**
     * Список организаций для dropdown
     */
    private function getOrganizationsList(): array
    {
        return ArrayHelper::map(
            Organizations::find()
                ->andWhere(['is_deleted' => 0])
                ->andWhere(['or', ['parent_id' => null], ['type' => 'head']])
                ->orderBy(['name' => SORT_ASC])
                ->all(),
            'id',
            'name'
        );
    }

    /**
     * Список функций для dropdown
     */
    private function getFeaturesList(): array
    {
        return ArrayHelper::map(
            SaasFeature::findAllActive(),
            'id',
            'name'
        );
    }

    /**
     * Список функций-аддонов для dropdown
     */
    private function getAddonFeatures(): array
    {
        return ArrayHelper::map(
            SaasFeature::findAddons(),
            'id',
            function ($feature) {
                $price = $feature->addon_price_monthly
                    ? ' (' . number_format($feature->addon_price_monthly, 0, '.', ' ') . ' KZT/мес)'
                    : '';
                return $feature->name . $price;
            }
        );
    }
}
