<?php

namespace app\models\forms;

use app\helpers\OrganizationUrl;
use app\models\Payment;
use app\models\PayMethod;
use app\models\Pupil;
use app\models\services\PupilService;
use Yii;

class PaymentForm extends \yii\base\Model
{
    public $id;
    public $pupil_id;
    public $purpose_id;
    public $method_id;
    public $type;
    public $number;
    public $amount;
    public $date;
    public $comment;



    const TYPE_ADD_PAY = 'add_pay';
    const TYPE_ADD_REFUND = 'add_refund';
    const TYPE_UPDATE_REFUND = 'update_refund';
    const TYPE_UPDATE_PAY = 'update_pay';
    const TYPE_CONSUMPTION = 'consumption';

    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios[self::TYPE_ADD_PAY] = ['id', 'pupil_id', 'purpose_id', 'method_id', 'type', 'number', 'amount', 'date', 'comment'];
        $scenarios[self::TYPE_UPDATE_PAY] = ['id', 'pupil_id', 'purpose_id', 'method_id', 'type', 'number', 'amount', 'date', 'comment'];
        $scenarios[self::TYPE_ADD_REFUND] = ['id', 'pupil_id', 'type', 'number', 'amount', 'date', 'comment'];
        $scenarios[self::TYPE_UPDATE_REFUND] = ['id', 'pupil_id', 'type', 'number', 'amount', 'date', 'comment'];
        $scenarios[self::TYPE_CONSUMPTION] = ['id', 'type', 'number', 'amount', 'date', 'comment'];
        return $scenarios;
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['pupil_id', 'purpose_id', 'method_id', 'type'], 'integer'],
            [['pupil_id', 'type', 'date'], 'required'],
            [['amount'], 'required'],
            [['amount'], 'number', 'min' => 0.01],
            [['date'], 'safe'],
            [['date'], 'date', 'format' => 'php:d.m.Y H:i'],
            [['comment'], 'string'],
            [['number'], 'string', 'max' => 255],
            [['method_id'], 'exist', 'skipOnError' => true, 'targetClass' => PayMethod::class, 'targetAttribute' => ['method_id' => 'id']],
            [['pupil_id'], 'exist', 'skipOnError' => true, 'targetClass' => Pupil::class, 'targetAttribute' => ['pupil_id' => 'id']],
        ];
    }

    public function init()
    {
        // Строгая типизация параметров
        $type = (int)Yii::$app->request->get('type', 0);
        $id = (int)Yii::$app->request->get('id', 0);

        if ($id > 0) {
            $this->id = $id;
            if ($type === Payment::TYPE_REFUND) {
                $this->setScenario(self::TYPE_UPDATE_REFUND);
            } else {
                $this->setScenario(self::TYPE_UPDATE_PAY);
            }
        } else {
            if ($type === Payment::TYPE_REFUND) {
                $this->setScenario(self::TYPE_ADD_REFUND);
            } else {
                $this->setScenario(self::TYPE_ADD_PAY);
            }
        }
        $this->loadDefaultValues();
        parent::init();
    }

    public function loadDefaultValues()
    {
        if ($this->getScenario() === self::TYPE_ADD_PAY) {
            $this->pupil_id = intval(Yii::$app->request->get('pupil_id'));
            $this->type = Payment::TYPE_PAY;
        } elseif ($this->getScenario() === self::TYPE_ADD_REFUND) {
            $this->pupil_id = intval(Yii::$app->request->get('pupil_id'));
            $this->type = Payment::TYPE_REFUND;
        } elseif ($this->getScenario() === self::TYPE_UPDATE_PAY || $this->getScenario() === self::TYPE_UPDATE_REFUND) {
            // Security: проверяем organization_id
            $model = Payment::find()
                ->where(['id' => Yii::$app->request->get('id')])
                ->byOrganization()
                ->one();

            if ($model === null) {
                return;
            }

            $this->id = $model->id;
            $this->pupil_id = $model->pupil_id;
            $this->type = $model->type;
            $this->purpose_id = $model->purpose_id;
            $this->method_id = $model->method_id;
            $this->number = $model->number;
            $this->amount = $model->amount;
            $this->date = date('d.m.Y H:i', strtotime($model->date));
            $this->comment = $model->comment;
        }
    }

    public function save()
    {
        if (!$this->validate()) {
            return false;
        }

        $transaction = \Yii::$app->db->beginTransaction();

        if ($this->id) {
            // Security: проверяем organization_id при обновлении
            $model = Payment::find()
                ->where(['id' => $this->id])
                ->byOrganization()
                ->one();

            if ($model === null) {
                $transaction->rollBack();
                return false;
            }
        } else {
            $model = new Payment();
            $model->pupil_id = $this->pupil_id;
            $model->type = $this->type;
        }
        $model->amount = $this->amount;
        $model->date = date('Y-m-d H:i', strtotime($this->date));
        $model->comment = $this->comment;
        if ($this->scenario == self::TYPE_UPDATE_PAY || $this->scenario == self::TYPE_ADD_PAY){
            $model->purpose_id = $this->purpose_id;
            $model->method_id = $this->method_id;
            $model->number = $this->number;
        }
        if (!$model->save()){
            $transaction->rollBack();
            return false;
        }

        PupilService::updateBalance($model->pupil_id);

        $transaction->commit();
        return true;
    }
    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('main', 'ID'),
            'pupil_id' => Yii::t('main', 'Pupil ID'),
            'purpose_id' => Yii::t('main', 'Назначение'),
            'method_id' => Yii::t('main', 'Метод оплаты'),
            'number' => Yii::t('main', '№ кватанции'),
            'date' => $this->type == Payment::TYPE_PAY ? Yii::t('main', 'Дата оплаты') :  Yii::t('main', 'Дата возврата'),
            'amount' => $this->type == Payment::TYPE_PAY ? Yii::t('main', 'Оплаченная сумма') : Yii::t('main', 'Сумма возврата'),
            'comment' => Yii::t('main', 'Примечание'),
            'tariff_price' => Yii::t('main', 'Tariff Price'),
            'total_price' => Yii::t('main', 'Total Price'),
        ];
    }

    public function getActionUrl(){
        if ($this->getScenario() === self::TYPE_ADD_PAY){
            return OrganizationUrl::to(['pupil/create-payment', 'pupil_id' => $this->pupil_id, 'type' => Payment::TYPE_PAY]);
        }else if($this->getScenario() === self::TYPE_ADD_REFUND){
            return OrganizationUrl::to(['pupil/create-payment', 'pupil_id' => $this->pupil_id, 'type' => Payment::TYPE_REFUND]);
        }

        return OrganizationUrl::to(['pupil/create-payment', 'pupil_id' => $this->pupil_id]);
    }




}