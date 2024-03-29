<?php

namespace app\models\forms;

use app\helpers\OrganizationUrl;
use app\models\Pupil;
use app\models\PupilEducation;
use app\models\relations\EducationGroup;
use app\models\services\PupilService;
use app\models\Tariff;
use Yii;

class EducationForm extends \yii\base\Model
{
    public $id;
    public $pupil_id;
    public $tariff_id;
    public $sale;
    public $date_start;
    public $date_end;
    public $comment;
    public $groups;
    public $total_price;
    public $tariff_price;

    const TYPE_ADD = 'add';
    const TYPE_EDIT = 'edit';
    const TYPE_COPY = 'copy';

    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $attr = ['id','pupil_id', 'tariff_id', 'sale', 'date_start','date_end','comment','groups','total_price', 'tariff_price'];
        $scenarios[self::TYPE_ADD] = $attr;
        $scenarios[self::TYPE_EDIT] = $attr;
        $scenarios[self::TYPE_COPY] = $attr;
        return $scenarios;
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['pupil_id', 'tariff_id', 'date_start'], 'required'],
            [['date_start', 'date_end'], 'date', 'format' => 'php:d.m.Y'],
            [['pupil_id', 'tariff_id', 'sale'], 'integer'],
            [['date_start', 'date_end'], 'safe'],
            [['comment'], 'string'],
            [['tariff_price', 'total_price'], 'number'],
            [['pupil_id'], 'exist', 'skipOnError' => true, 'targetClass' => Pupil::class, 'targetAttribute' => ['pupil_id' => 'id']],
            [['tariff_id'], 'exist', 'skipOnError' => true, 'targetClass' => Tariff::class, 'targetAttribute' => ['tariff_id' => 'id']],
        ];
    }

    public function init()
    {
        $this->loadDefaultValues();
        parent::init(); // TODO: Change the autogenerated stub
    }

    public function loadDefaultValues(){
        if ($id = \Yii::$app->request->get('id')){
            $education = \app\models\PupilEducation::findOne($id);
            if ($this->getScenario() === self::TYPE_EDIT){
                $this->id = $education->id;
            }
            $this->pupil_id = $education->pupil_id;
            $this->tariff_id = $education->tariff_id;
            $this->sale = $education->sale;
            $this->date_start = date('d.m.Y', strtotime($education->date_start));
            $this->date_end = date('d.m.Y', strtotime($education->date_end));
            $this->groups = $education->groups ? : [];

        }
        if ($this->getScenario() === self::TYPE_ADD){
            $this->date_start = date('d.m.Y');
            $this->sale = 0;
            $this->date_end = date('d.m.Y', time()+30*24*60*60);
            $this->pupil_id = Yii::$app->request->get('pupil_id');

        }else if($this->getScenario() === self::TYPE_COPY){
            $this->pupil_id = Yii::$app->request->get('pupil_id');
        }

    }

    public function save(){
        if (!$this->validate()){
            return  false;
        }

        $transaction = \Yii::$app->db->beginTransaction();
        if ($this->id && $this->getScenario() === self::TYPE_EDIT){
            $model = PupilEducation::findOne($this->id);
        }else{
            $model = new PupilEducation();
        }
        $tariffInfo = $this->getTariffInfo();
        $model->tariff_id = $this->tariff_id;
        $model->pupil_id = $this->pupil_id;
        $model->sale = $this->sale;
        $model->date_start = date('Y-m-d', strtotime($this->date_start));
        $model->date_end = date('Y-m-d', strtotime($this->date_end));
        $model->comment = $this->comment;
        $model->tariff_price = $tariffInfo['tariff_price'];
        $model->total_price = $tariffInfo['total_price'];

        if (!$model->save()){
            $transaction->rollBack();
            return false;
        }
        $createdEduGroupIds = [];
        foreach ($this->groups as $item){
            $educationGroup = EducationGroup::find()->where(['education_id' => $model->id, 'group_id' => $item['group_id']])->one();
            if (!$educationGroup){
                $educationGroup = new EducationGroup();
                $educationGroup->education_id = $model->id;
            }
            $educationGroup->group_id = $item['group_id'];
            $educationGroup->pupil_id = $model->pupil_id;
            $educationGroup->subject_id = $item['subject_id'];
            if (!$educationGroup->save()){
                $transaction->rollBack();
                return false;
            }
            $createdEduGroupIds[] = $educationGroup->id;
        }
        $forDeleteEducationGroups = EducationGroup::find()->where(['not in', 'id', $createdEduGroupIds])->andWhere(['education_id' => $model->id])->all();
        foreach ($forDeleteEducationGroups as $deleteEducationGroup){
            $deleteEducationGroup->delete();
        }
        $this->id = $model->id;

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
            'tariff_id' => Yii::t('main', 'Выберит тариф'),
            'sale' => Yii::t('main', 'Скидка, %'),
            'date_start' => Yii::t('main', 'Действует с'),
            'date_end' => Yii::t('main', 'Действует по'),
            'comment' => Yii::t('main', 'Примечание'),
            'tariff_price' => Yii::t('main', 'Tariff Price'),
            'total_price' => Yii::t('main', 'Total Price'),
        ];
    }

    public function getActionUrl(){
        if ($this->getScenario() === self::TYPE_ADD){
            return OrganizationUrl::to(['pupil/create-edu', 'pupil_id' => $this->pupil_id]);
        }else if($this->getScenario() === self::TYPE_EDIT){
            return OrganizationUrl::to(['pupil/update-edu', 'pupil_id' => $this->pupil_id, 'id' => $this->id]);
        }else if($this->getScenario() === self::TYPE_COPY){
            return OrganizationUrl::to(['pupil/copy-edu', 'pupil_id' => $this->pupil_id, 'id' => $this->id]);
        }

        return OrganizationUrl::to(['pupil/create-edu', 'pupil_id' => $this->pupil_id]);
    }

    public function getTariffInfo(){
        $tariff = Tariff::findOne($this->tariff_id);
        if ($tariff->type == 2){
            $pricePerDay = $tariff->price / 31;
            $days = (strtotime($this->date_end) - strtotime($this->date_start)) / (24 * 60 * 60);
            $periodPrice = intval(($days + 1) * $pricePerDay);

        }else{
            $periodPrice = $tariff->price;
        }
        if ($this->sale > 0){
            $salePrice = intval($periodPrice * $this->sale / 100);
        }else{
            $salePrice = 0;
        }
        $totalPrice = $periodPrice - $salePrice;
        return [
            'period_price' => $periodPrice,
            'sale_price' => $salePrice,
            'total_price' => $totalPrice,
            'tariff_price' => $tariff->price,
        ];
    }



}