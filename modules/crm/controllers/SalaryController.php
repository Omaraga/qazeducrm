<?php

namespace app\modules\crm\controllers;

use app\helpers\OrganizationRoles;
use app\helpers\RoleChecker;
use app\helpers\SystemRoles;
use app\models\Organizations;
use app\models\services\TeacherService;
use app\models\TeacherRate;
use app\models\TeacherSalary;
use app\models\search\TeacherSalarySearch;
use app\models\search\TeacherRateSearch;
use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;

/**
 * SalaryController - управление зарплатами учителей
 */
class SalaryController extends Controller
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
                    'delete' => ['POST'],
                    'delete-rate' => ['POST'],
                    'approve' => ['POST'],
                    'pay' => ['POST'],
                ],
            ],
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    // Директора: утверждение, выплата, удаление
                    [
                        'allow' => true,
                        'actions' => ['approve', 'pay', 'delete'],
                        'roles' => [
                            SystemRoles::SUPER,
                            OrganizationRoles::DIRECTOR,
                            OrganizationRoles::GENERAL_DIRECTOR,
                        ]
                    ],
                    // Учителя: просмотр своих зарплат (по настройкам организации)
                    [
                        'allow' => true,
                        'actions' => ['index', 'view'],
                        'matchCallback' => function ($rule, $action) {
                            // Директора всегда имеют доступ
                            if (RoleChecker::isDirector()) {
                                return true;
                            }
                            // Админы - по настройке организации
                            if (RoleChecker::isAdminOnly()) {
                                return RoleChecker::canAdminViewSalary();
                            }
                            // Учителя - по настройке организации
                            if (RoleChecker::isTeacherOnly()) {
                                return RoleChecker::canTeacherViewOwnSalary();
                            }
                            return false;
                        }
                    ],
                    // Директора: полный доступ к остальным действиям
                    [
                        'allow' => true,
                        'roles' => [
                            SystemRoles::SUPER,
                            OrganizationRoles::DIRECTOR,
                            OrganizationRoles::GENERAL_DIRECTOR,
                        ]
                    ],
                    // Админы: доступ к управлению ставками (по настройке)
                    [
                        'allow' => true,
                        'actions' => ['rates', 'create-rate', 'update-rate', 'delete-rate'],
                        'matchCallback' => function ($rule, $action) {
                            return RoleChecker::canAdminViewSalary();
                        }
                    ],
                    // Гости: запрет
                    [
                        'allow' => false,
                        'roles' => ['?']
                    ]
                ],
            ],
        ];
    }

    /**
     * Список зарплат (ведомость)
     */
    public function actionIndex()
    {
        $searchModel = new TeacherSalarySearch();

        // Для учителей показываем только их зарплаты
        $params = Yii::$app->request->queryParams;
        if (RoleChecker::isTeacherOnly()) {
            $params['TeacherSalarySearch']['teacher_id'] = Yii::$app->user->id;
        }

        $dataProvider = $searchModel->search($params);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Просмотр зарплаты с детализацией
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);

        // Учитель может просматривать только свои зарплаты
        if (Yii::$app->user->can(OrganizationRoles::TEACHER)
            && !Yii::$app->user->can(OrganizationRoles::ADMIN)
            && !Yii::$app->user->can(OrganizationRoles::DIRECTOR)
            && !Yii::$app->user->can(OrganizationRoles::GENERAL_DIRECTOR)
        ) {
            if ($model->teacher_id != Yii::$app->user->id) {
                throw new \yii\web\ForbiddenHttpException('Вы можете просматривать только свои зарплаты');
            }
        }

        $details = $model->salaryDetails;

        return $this->render('view', [
            'model' => $model,
            'details' => $details,
        ]);
    }

    /**
     * Расчёт зарплаты за период
     */
    public function actionCalculate()
    {
        $teachers = TeacherService::getOrganizationTeachers();

        if (Yii::$app->request->isPost) {
            $teacherId = Yii::$app->request->post('teacher_id');
            $periodStart = Yii::$app->request->post('period_start');
            $periodEnd = Yii::$app->request->post('period_end');

            if ($teacherId && $periodStart && $periodEnd) {
                $periodStart = date('Y-m-d', strtotime($periodStart));
                $periodEnd = date('Y-m-d', strtotime($periodEnd));

                $salary = TeacherSalary::calculate($teacherId, $periodStart, $periodEnd);

                if ($salary && !$salary->hasErrors()) {
                    Yii::$app->session->setFlash('success', 'Зарплата рассчитана успешно');
                    return $this->redirect(['view', 'id' => $salary->id]);
                } else {
                    Yii::$app->session->setFlash('error', 'Ошибка при расчёте зарплаты');
                }
            }
        }

        // Период по умолчанию - прошлый месяц
        $defaultStart = date('Y-m-01', strtotime('-1 month'));
        $defaultEnd = date('Y-m-t', strtotime('-1 month'));

        return $this->render('calculate', [
            'teachers' => $teachers,
            'defaultStart' => $defaultStart,
            'defaultEnd' => $defaultEnd,
        ]);
    }

    /**
     * Редактирование зарплаты (бонусы, вычеты)
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->status !== TeacherSalary::STATUS_DRAFT) {
            Yii::$app->session->setFlash('error', 'Редактировать можно только зарплаты в статусе "Расчёт"');
            return $this->redirect(['view', 'id' => $id]);
        }

        if (Yii::$app->request->isPost) {
            $model->bonus_amount = Yii::$app->request->post('bonus_amount', 0);
            $model->deduction_amount = Yii::$app->request->post('deduction_amount', 0);
            $model->notes = Yii::$app->request->post('notes', '');
            $model->total_amount = $model->base_amount + $model->bonus_amount - $model->deduction_amount;

            if ($model->save()) {
                Yii::$app->session->setFlash('success', 'Зарплата обновлена');
                return $this->redirect(['view', 'id' => $id]);
            }
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Пересчитать зарплату
     */
    public function actionRecalculate($id)
    {
        $model = $this->findModel($id);

        if ($model->recalculate()) {
            Yii::$app->session->setFlash('success', 'Зарплата пересчитана');
        } else {
            Yii::$app->session->setFlash('error', 'Невозможно пересчитать зарплату');
        }

        return $this->redirect(['view', 'id' => $id]);
    }

    /**
     * Утвердить зарплату
     */
    public function actionApprove($id)
    {
        $model = $this->findModel($id);

        if ($model->approve()) {
            Yii::$app->session->setFlash('success', 'Зарплата утверждена');
        } else {
            Yii::$app->session->setFlash('error', 'Невозможно утвердить зарплату');
        }

        return $this->redirect(['view', 'id' => $id]);
    }

    /**
     * Отметить как выплаченную
     */
    public function actionPay($id)
    {
        $model = $this->findModel($id);

        if ($model->markAsPaid()) {
            Yii::$app->session->setFlash('success', 'Зарплата отмечена как выплаченная');
        } else {
            Yii::$app->session->setFlash('error', 'Невозможно отметить как выплаченную');
        }

        return $this->redirect(['view', 'id' => $id]);
    }

    /**
     * Удалить зарплату
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);

        if ($model->status === TeacherSalary::STATUS_PAID) {
            Yii::$app->session->setFlash('error', 'Невозможно удалить выплаченную зарплату');
            return $this->redirect(['index']);
        }

        $model->delete();
        Yii::$app->session->setFlash('success', 'Зарплата удалена');

        return $this->redirect(['index']);
    }

    // ==================== Ставки ====================

    /**
     * Список ставок учителей
     */
    public function actionRates()
    {
        $searchModel = new TeacherRateSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('rates', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Создание ставки
     */
    public function actionCreateRate()
    {
        $model = new TeacherRate();
        $model->organization_id = Organizations::getCurrentOrganizationId();
        $model->is_active = true;

        $teachers = TeacherService::getOrganizationTeachers();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Ставка создана');
            return $this->redirect(['rates']);
        }

        return $this->render('rate-form', [
            'model' => $model,
            'teachers' => $teachers,
        ]);
    }

    /**
     * Редактирование ставки
     */
    public function actionUpdateRate($id)
    {
        $model = $this->findRate($id);

        $teachers = TeacherService::getOrganizationTeachers();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Ставка обновлена');
            return $this->redirect(['rates']);
        }

        return $this->render('rate-form', [
            'model' => $model,
            'teachers' => $teachers,
        ]);
    }

    /**
     * Удаление ставки
     */
    public function actionDeleteRate($id)
    {
        $model = $this->findRate($id);
        $model->delete();

        Yii::$app->session->setFlash('success', 'Ставка удалена');
        return $this->redirect(['rates']);
    }

    /**
     * Найти модель зарплаты по ID
     */
    protected function findModel($id)
    {
        $model = TeacherSalary::find()
            ->byOrganization()
            ->andWhere(['id' => $id])
            ->notDeleted()
            ->one();

        if ($model === null) {
            throw new NotFoundHttpException('Зарплата не найдена');
        }

        return $model;
    }

    /**
     * Найти модель ставки по ID
     */
    protected function findRate($id)
    {
        $model = TeacherRate::find()
            ->byOrganization()
            ->andWhere(['id' => $id])
            ->notDeleted()
            ->one();

        if ($model === null) {
            throw new NotFoundHttpException('Ставка не найдена');
        }

        return $model;
    }
}
