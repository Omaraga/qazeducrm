<?php

namespace app\models;

use app\components\PhoneNumberValidator;
use app\helpers\Lists;
use app\traits\AttributesToInfoTrait;
use app\traits\UpdateInsteadOfDeleteTrait;
use Yii;
use app\components\ActiveRecord;

/**
 * This is the model class for table "pupil".
 *
 * @property int $id
 * @property string $iin
 * @property string|null $email
 * @property string|null $phone
 * @property string|null $home_phone
 * @property string|null $address
 * @property string|null $first_name
 * @property string|null $last_name
 * @property string|null $middle_name
 * @property string|null $parent_fio
 * @property string|null $parent_phone
 * @property int|null $sex
 * @property string|null $birth_date
 * @property string|null $school_name
 * @property string|null $info
 * @property int|null $class_id
 * @property int $status
 * @property int $created_at
 * @property int $updated_at
 * @property double $balance
 * @property string $fio
 * @property int $is_deleted [smallint(6)]
 * @property int $organization_id [int(11)]
 */
class Pupil extends ActiveRecord
{
    const STATUS_ACTIVE = 1;
    const STATUS_ARCHIVED = 0;

    use UpdateInsteadOfDeleteTrait, AttributesToInfoTrait;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'pupil';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['iin', 'sex',], 'required'],
            [['created_at'], 'default', 'value' => time()],
            [['balance'], 'default', 'value' => 0],
            [['status'], 'default', 'value' => self::STATUS_ACTIVE],
            [['birth_date'], 'date', 'format' => 'php:d.m.Y'],
            [['sex', 'class_id', 'status', 'created_at', 'updated_at'], 'integer'],
            [['iin', 'email', 'phone', 'home_phone', 'address', 'first_name', 'last_name', 'middle_name', 'parent_fio', 'parent_phone', 'birth_date', 'school_name'], 'string', 'max' => 255],
            [['iin'], 'unique'],
            [['phone', 'home_phone', 'parent_phone'], PhoneNumberValidator::class],
            ['email', 'email'],
        ];
    }

    public function save($runValidation = true, $attributeNames = null)
    {
        if (!$this->organization_id){
            $this->organization_id = Organizations::getCurrentOrganizationId();
        }
        $this->updated_at = time();
        $this->fio = $this->last_name.' '.$this->first_name.' '.$this->middle_name;
        return parent::save($runValidation, $attributeNames); // TODO: Change the autogenerated stub
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'iin' => 'ИИН',
            'email' => 'Email',
            'phone' => 'Мобильный телефон',
            'fio' => 'ФИО',
            'contacts' => 'Контакты',
            'parent_contacts' => 'Контакты родителя',
            'home_phone' => 'Телефон',
            'address' => 'Адрес',
            'first_name' => 'Имя',
            'last_name' => 'Фамилия',
            'middle_name' => 'Отчество',
            'parent_fio' => 'ФИО родителя',
            'parent_phone' => 'Номер родителя',
            'sex' => 'Пол',
            'birth_date' => 'Дата рождения',
            'school_name' => 'Название школы',
            'info' => 'Info',
            'class_id' => 'Класс',
            'status' => 'Статус',
            'balance' => 'На счету ученика',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    public function getGenderLabel(){
        return Lists::getGenders()[$this->sex];
    }
}
