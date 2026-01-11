<?php

namespace app\modules\cabinet\controllers;

use app\models\Payment;
use app\models\PupilEducation;
use app\modules\cabinet\Module;
use Yii;
use yii\data\ActiveDataProvider;

/**
 * PaymentController - баланс и история платежей ученика
 */
class PaymentController extends CabinetBaseController
{
    /**
     * История платежей
     * @param int|null $pupil_id ID ученика
     */
    public function actionIndex($pupil_id = null)
    {
        $pupils = $this->getPupils() ?? [];

        // Если передан конкретный ученик - проверяем доступ
        $selectedPupil = null;
        if ($pupil_id) {
            $selectedPupil = $this->getPupilById($pupil_id);
        } elseif (count($pupils) === 1) {
            $selectedPupil = $pupils[0];
        }

        $pupilIds = $selectedPupil ? [$selectedPupil->id] : Module::getPupilIds();

        // DataProvider для платежей
        $dataProvider = new ActiveDataProvider([
            'query' => Payment::find()
                ->where(['pupil_id' => $pupilIds])
                ->andWhere(['is_deleted' => 0])
                ->orderBy(['date' => SORT_DESC, 'id' => SORT_DESC]),
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);

        // Общий баланс по всем ученикам
        $totalBalance = 0;
        foreach ($pupils as $pupil) {
            $totalBalance += $pupil->balance;
        }

        return $this->render('index', [
            'pupils' => $pupils,
            'selectedPupil' => $selectedPupil,
            'dataProvider' => $dataProvider,
            'totalBalance' => $totalBalance,
        ]);
    }

    /**
     * Детали платежа
     */
    public function actionView($id)
    {
        $pupilIds = Module::getPupilIds();

        $payment = Payment::find()
            ->where(['id' => $id])
            ->andWhere(['pupil_id' => $pupilIds])
            ->andWhere(['is_deleted' => 0])
            ->one();

        if (!$payment) {
            Yii::$app->session->setFlash('error', Yii::t('app', 'Платёж не найден'));
            return $this->redirect(['index']);
        }

        return $this->render('view', [
            'payment' => $payment,
        ]);
    }

    /**
     * Баланс ученика (подробно)
     */
    public function actionBalance($pupil_id = null)
    {
        $pupils = $this->getPupils() ?? [];

        // Если передан конкретный ученик - проверяем доступ
        $selectedPupil = null;
        if ($pupil_id) {
            $selectedPupil = $this->getPupilById($pupil_id);
        } elseif (count($pupils) === 1) {
            $selectedPupil = $pupils[0];
        }

        $balanceData = [];

        $targetPupils = $selectedPupil ? [$selectedPupil] : $pupils;

        foreach ($targetPupils as $pupil) {
            // Получаем обучения ученика
            $educations = PupilEducation::find()
                ->where(['pupil_id' => $pupil->id])
                ->andWhere(['is_deleted' => 0])
                ->with(['tariff', 'groups.group'])
                ->orderBy(['date_start' => SORT_DESC])
                ->all();

            // Сумма платежей
            $totalPaid = Payment::find()
                ->where(['pupil_id' => $pupil->id])
                ->andWhere(['is_deleted' => 0])
                ->andWhere(['type' => Payment::TYPE_PAY])
                ->sum('amount') ?: 0;

            // Сумма возвратов
            $totalRefund = Payment::find()
                ->where(['pupil_id' => $pupil->id])
                ->andWhere(['is_deleted' => 0])
                ->andWhere(['type' => Payment::TYPE_REFUND])
                ->sum('amount') ?: 0;

            // Сумма начислений (стоимость обучения)
            $totalCharged = PupilEducation::find()
                ->where(['pupil_id' => $pupil->id])
                ->andWhere(['is_deleted' => 0])
                ->sum('total_price') ?: 0;

            $balanceData[] = [
                'pupil' => $pupil,
                'educations' => $educations,
                'totalPaid' => $totalPaid,
                'totalRefund' => $totalRefund,
                'totalCharged' => $totalCharged,
                'balance' => $pupil->balance,
            ];
        }

        return $this->render('balance', [
            'pupils' => $pupils,
            'selectedPupil' => $selectedPupil,
            'balanceData' => $balanceData,
        ]);
    }

    /**
     * Квитанция платежа (PDF)
     */
    public function actionReceipt($id)
    {
        $pupilIds = Module::getPupilIds();
        $organizationId = $this->getOrganizationId();

        // Проверяем pupil_id И organization_id для безопасности
        $payment = Payment::find()
            ->where(['id' => $id])
            ->andWhere(['pupil_id' => $pupilIds])
            ->andWhere(['organization_id' => $organizationId])
            ->andWhere(['is_deleted' => 0])
            ->one();

        if (!$payment) {
            Yii::$app->session->setFlash('error', Yii::t('app', 'Платёж не найден'));
            return $this->redirect(['index']);
        }

        // Получаем данные организации
        $organization = \app\models\Organizations::findOne($organizationId);

        return $this->render('receipt', [
            'payment' => $payment,
            'organization' => $organization,
        ]);
    }
}
