<?php

namespace app\modules\superadmin\controllers;

use app\models\SaasPromoCode;
use app\models\SaasPromoCodeUsage;
use app\models\SaasPlan;
use app\models\SaasFeature;
use app\services\DiscountService;
use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\filters\VerbFilter;
use yii\data\ActiveDataProvider;

/**
 * PromoCodeController - управление промокодами.
 */
class PromoCodeController extends Controller
{
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                    'toggle' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Список промокодов
     */
    public function actionIndex()
    {
        $query = SaasPromoCode::find()->orderBy(['created_at' => SORT_DESC]);

        // Фильтры
        $status = Yii::$app->request->get('status');
        if ($status === 'active') {
            $now = date('Y-m-d H:i:s');
            $query->andWhere(['is_active' => true])
                ->andWhere(['or', ['valid_from' => null], ['<=', 'valid_from', $now]])
                ->andWhere(['or', ['valid_until' => null], ['>=', 'valid_until', $now]]);
        } elseif ($status === 'inactive') {
            $query->andWhere(['is_active' => false]);
        } elseif ($status === 'expired') {
            $query->andWhere(['<', 'valid_until', date('Y-m-d H:i:s')]);
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => ['pageSize' => 20],
        ]);

        // Статистика
        $stats = [
            'total' => SaasPromoCode::find()->count(),
            'active' => SaasPromoCode::findActive()->count(),
            'total_usage' => SaasPromoCodeUsage::find()->count(),
            'total_discount' => (float)SaasPromoCodeUsage::find()->sum('discount_amount'),
        ];

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'stats' => $stats,
        ]);
    }

    /**
     * Просмотр промокода
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);

        $usageDataProvider = new ActiveDataProvider([
            'query' => SaasPromoCodeUsage::find()
                ->with(['organization', 'payment'])
                ->where(['promo_code_id' => $id])
                ->orderBy(['used_at' => SORT_DESC]),
            'pagination' => ['pageSize' => 20],
        ]);

        $stats = SaasPromoCodeUsage::getPromoCodeStats($id);

        return $this->render('view', [
            'model' => $model,
            'usageDataProvider' => $usageDataProvider,
            'stats' => $stats,
        ]);
    }

    /**
     * Создание промокода
     */
    public function actionCreate()
    {
        $model = new SaasPromoCode();
        $model->is_active = true;
        $model->usage_per_org = 1;
        $model->discount_type = SaasPromoCode::TYPE_PERCENT;
        $model->applies_to = SaasPromoCode::APPLIES_SUBSCRIPTION;

        if ($model->load(Yii::$app->request->post())) {
            $model->created_by = Yii::$app->user->id;

            // Обработка JSON полей
            $applicablePlans = Yii::$app->request->post('applicable_plans');
            $model->applicable_plans = !empty($applicablePlans) ? json_encode($applicablePlans) : null;

            $applicableAddons = Yii::$app->request->post('applicable_addons');
            $model->applicable_addons = !empty($applicableAddons) ? json_encode($applicableAddons) : null;

            if ($model->save()) {
                Yii::$app->session->setFlash('success', 'Промокод создан: ' . $model->code);
                return $this->redirect(['view', 'id' => $model->id]);
            }
        }

        // Генерируем код если не задан
        if (empty($model->code)) {
            $model->code = SaasPromoCode::generateCode();
        }

        return $this->render('create', [
            'model' => $model,
            'plans' => SaasPlan::find()->where(['is_active' => true])->all(),
            'addons' => SaasFeature::find()->where(['is_addon' => true, 'is_active' => true])->all(),
        ]);
    }

    /**
     * Редактирование промокода
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post())) {
            // Обработка JSON полей
            $applicablePlans = Yii::$app->request->post('applicable_plans');
            $model->applicable_plans = !empty($applicablePlans) ? json_encode($applicablePlans) : null;

            $applicableAddons = Yii::$app->request->post('applicable_addons');
            $model->applicable_addons = !empty($applicableAddons) ? json_encode($applicableAddons) : null;

            if ($model->save()) {
                Yii::$app->session->setFlash('success', 'Промокод обновлён');
                return $this->redirect(['view', 'id' => $model->id]);
            }
        }

        return $this->render('update', [
            'model' => $model,
            'plans' => SaasPlan::find()->where(['is_active' => true])->all(),
            'addons' => SaasFeature::find()->where(['is_addon' => true, 'is_active' => true])->all(),
        ]);
    }

    /**
     * Удаление промокода
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);

        // Проверяем использования
        if ($model->getUsageCount() > 0) {
            Yii::$app->session->setFlash('error', 'Нельзя удалить промокод с историей использования. Деактивируйте его.');
            return $this->redirect(['view', 'id' => $id]);
        }

        $model->delete();
        Yii::$app->session->setFlash('success', 'Промокод удалён');
        return $this->redirect(['index']);
    }

    /**
     * Включить/выключить промокод
     */
    public function actionToggle($id)
    {
        $model = $this->findModel($id);
        $model->is_active = !$model->is_active;
        $model->save(false, ['is_active', 'updated_at']);

        $status = $model->is_active ? 'активирован' : 'деактивирован';
        Yii::$app->session->setFlash('success', "Промокод {$status}");

        return $this->redirect(Yii::$app->request->referrer ?: ['index']);
    }

    /**
     * Генерация нового кода (AJAX)
     */
    public function actionGenerateCode()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        return ['code' => SaasPromoCode::generateCode()];
    }

    /**
     * Проверка промокода (AJAX)
     */
    public function actionCheck($code, $organization_id = null)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $service = new DiscountService();
        return $service->checkPromoCode($code, $organization_id);
    }

    /**
     * Статистика по промокодам
     */
    public function actionStats()
    {
        // Топ промокодов по использованию
        $topByUsage = SaasPromoCode::find()
            ->alias('p')
            ->select([
                'p.*',
                'COUNT(u.id) as usage_count',
                'SUM(u.discount_amount) as total_discount',
            ])
            ->leftJoin(['u' => SaasPromoCodeUsage::tableName()], 'p.id = u.promo_code_id')
            ->groupBy('p.id')
            ->orderBy(['usage_count' => SORT_DESC])
            ->limit(10)
            ->asArray()
            ->all();

        // Использование по месяцам
        $usageByMonth = SaasPromoCodeUsage::find()
            ->select([
                'DATE_FORMAT(used_at, "%Y-%m") as month',
                'COUNT(*) as count',
                'SUM(discount_amount) as total_discount',
            ])
            ->groupBy('month')
            ->orderBy(['month' => SORT_DESC])
            ->limit(12)
            ->asArray()
            ->all();

        return $this->render('stats', [
            'topByUsage' => $topByUsage,
            'usageByMonth' => array_reverse($usageByMonth),
        ]);
    }

    protected function findModel($id)
    {
        if (($model = SaasPromoCode::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('Промокод не найден.');
    }
}
