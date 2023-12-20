<?php

namespace app\models;

use Yii;

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
 */
class Pupil extends \yii\db\ActiveRecord
{
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
            [['iin', 'created_at', 'updated_at'], 'required'],
            [['sex', 'class_id', 'status', 'created_at', 'updated_at'], 'integer'],
            [['info'], 'string'],
            [['iin', 'email', 'phone', 'home_phone', 'address', 'first_name', 'last_name', 'middle_name', 'parent_fio', 'parent_phone', 'birth_date', 'school_name'], 'string', 'max' => 255],
            [['iin'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'iin' => 'Iin',
            'email' => 'Email',
            'phone' => 'Phone',
            'home_phone' => 'Home Phone',
            'address' => 'Address',
            'first_name' => 'First Name',
            'last_name' => 'Last Name',
            'middle_name' => 'Middle Name',
            'parent_fio' => 'Parent Fio',
            'parent_phone' => 'Parent Phone',
            'sex' => 'Sex',
            'birth_date' => 'Birth Date',
            'school_name' => 'School Name',
            'info' => 'Info',
            'class_id' => 'Class ID',
            'status' => 'Status',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }
}
