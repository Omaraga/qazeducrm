<?php

namespace app\models\forms;

use app\models\relations\EducationGroup;
use app\models\Lids;
use app\models\LidHistory;
use app\models\Organizations;
use app\models\Payment;
use app\models\PayMethod;
use app\models\Pupil;
use app\models\PupilEducation;
use app\models\Tariff;
use app\models\Group;
use app\helpers\ActivityLogger;
use Yii;
use yii\base\Model;

/**
 * Форма конверсии лида в ученика
 * Позволяет создать ученика, записать в группу и принять оплату за одну операцию
 */
class LidConversionForm extends Model
{
    // Данные ученика
    public $iin;
    public $sex;
    public $birth_date;

    // Данные из лида (только для отображения, заполняются автоматически)
    public $first_name;
    public $last_name;
    public $middle_name;
    public $phone;
    public $parent_fio;
    public $parent_phone;
    public $school_name;
    public $class_id;

    // Обучение (опционально)
    public $add_to_group = false;
    public $tariff_id;
    public $group_id;
    public $education_date_start;
    public $sale = 0; // Скидка в %

    // Оплата (опционально)
    public $create_payment = false;
    public $payment_amount;
    public $payment_method_id;
    public $payment_date;
    public $payment_number;

    // ID лида
    public $lid_id;

    /**
     * @var Lids
     */
    private $_lid;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            // Обязательные поля ученика
            [['iin', 'sex'], 'required'],
            [['iin'], 'string', 'max' => 12],
            [['iin'], 'match', 'pattern' => '/^\d{12}$/', 'message' => 'ИИН должен содержать 12 цифр'],
            [['iin'], 'unique', 'targetClass' => Pupil::class, 'targetAttribute' => 'iin', 'message' => 'Ученик с таким ИИН уже существует'],
            [['sex'], 'integer'],
            [['sex'], 'in', 'range' => [1, 2]],

            // Опциональные поля ученика
            [['birth_date'], 'date', 'format' => 'php:d.m.Y'],
            [['first_name', 'last_name', 'middle_name', 'phone', 'parent_fio', 'parent_phone', 'school_name'], 'string', 'max' => 255],
            [['class_id'], 'integer'],
            // lid_id устанавливается программно через loadFromLid(), не требует валидации

            // Обучение
            [['add_to_group'], 'boolean'],
            [['tariff_id'], 'integer'],
            [['tariff_id'], 'required', 'when' => function($model) {
                return $model->add_to_group;
            }, 'whenClient' => "function(attribute, value) { return $('#lidconversionform-add_to_group').is(':checked'); }"],
            [['group_id'], 'integer'],
            [['education_date_start'], 'date', 'format' => 'php:d.m.Y'],
            [['sale'], 'integer', 'min' => 0, 'max' => 100],

            // Оплата
            [['create_payment'], 'boolean'],
            [['payment_amount'], 'number', 'min' => 0],
            [['payment_amount'], 'required', 'when' => function($model) {
                return $model->create_payment;
            }, 'whenClient' => "function(attribute, value) { return $('#lidconversionform-create_payment').is(':checked'); }"],
            [['payment_method_id'], 'integer'],
            [['payment_method_id'], 'required', 'when' => function($model) {
                return $model->create_payment;
            }, 'whenClient' => "function(attribute, value) { return $('#lidconversionform-create_payment').is(':checked'); }"],
            [['payment_date'], 'date', 'format' => 'php:d.m.Y'],
            [['payment_number'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'iin' => 'ИИН',
            'sex' => 'Пол',
            'birth_date' => 'Дата рождения',
            'first_name' => 'Имя',
            'last_name' => 'Фамилия',
            'middle_name' => 'Отчество',
            'phone' => 'Телефон',
            'parent_fio' => 'ФИО родителя',
            'parent_phone' => 'Телефон родителя',
            'school_name' => 'Школа',
            'class_id' => 'Класс',
            'add_to_group' => 'Добавить в группу',
            'tariff_id' => 'Тариф',
            'group_id' => 'Группа',
            'education_date_start' => 'Дата начала',
            'sale' => 'Скидка (%)',
            'create_payment' => 'Принять оплату',
            'payment_amount' => 'Сумма оплаты',
            'payment_method_id' => 'Метод оплаты',
            'payment_date' => 'Дата оплаты',
            'payment_number' => '№ квитанции',
        ];
    }

    /**
     * Загрузить данные из лида
     * @param Lids $lid
     */
    public function loadFromLid(Lids $lid): void
    {
        $this->_lid = $lid;
        $this->lid_id = $lid->id;

        // Разбираем ФИО
        $fioParts = preg_split('/\s+/', trim($lid->fio), 3);
        $this->last_name = $fioParts[0] ?? '';
        $this->first_name = $fioParts[1] ?? '';
        $this->middle_name = $fioParts[2] ?? '';

        // Копируем контактные данные
        $this->phone = $lid->phone;
        $this->parent_fio = $lid->parent_fio;
        $this->parent_phone = $lid->parent_phone;
        $this->school_name = $lid->school;
        $this->class_id = $lid->class_id;

        // Устанавливаем значения по умолчанию
        $this->payment_date = date('d.m.Y');
        $this->education_date_start = date('d.m.Y');

        // Если в лиде есть сумма - используем её
        if ($lid->total_sum) {
            $this->payment_amount = $lid->total_sum;
        }

        // Если в лиде есть скидка - используем её
        if ($lid->sale) {
            $this->sale = $lid->sale;
        }
    }

    /**
     * Получить связанный лид
     * @return Lids|null
     */
    public function getLid(): ?Lids
    {
        if ($this->_lid === null && $this->lid_id) {
            $this->_lid = Lids::findOne($this->lid_id);
        }
        return $this->_lid;
    }

    /**
     * Конвертировать лида в ученика
     * @return Pupil|null Созданный ученик или null при ошибке
     */
    public function convert(): ?Pupil
    {
        if (!$this->validate()) {
            return null;
        }

        $lid = $this->getLid();
        if (!$lid) {
            $this->addError('lid_id', 'Лид не найден');
            return null;
        }

        if (!$lid->canConvertToPupil()) {
            $this->addError('lid_id', 'Невозможно конвертировать лида в ученика');
            return null;
        }

        $transaction = Yii::$app->db->beginTransaction();

        try {
            // 1. Создаём ученика
            $pupil = new Pupil();
            $pupil->iin = $this->iin;
            $pupil->sex = $this->sex;
            $pupil->first_name = $this->first_name;
            $pupil->last_name = $this->last_name;
            $pupil->middle_name = $this->middle_name;
            $pupil->phone = $this->phone;
            $pupil->parent_fio = $this->parent_fio;
            $pupil->parent_phone = $this->parent_phone;
            $pupil->school_name = $this->school_name;
            $pupil->class_id = $this->class_id;

            if ($this->birth_date) {
                $pupil->birth_date = $this->birth_date;
            }

            if (!$pupil->save()) {
                throw new \Exception('Ошибка создания ученика: ' . json_encode($pupil->errors));
            }

            // 2. Создаём обучение (если выбран тариф)
            if ($this->add_to_group && $this->tariff_id) {
                $tariff = Tariff::findOne($this->tariff_id);
                if ($tariff) {
                    $education = new PupilEducation();
                    $education->pupil_id = $pupil->id;
                    $education->tariff_id = $this->tariff_id;
                    $education->tariff_price = $tariff->price;

                    // Применяем скидку
                    if ($this->sale > 0) {
                        $education->sale = $this->sale;
                        $education->total_price = $tariff->price * (100 - $this->sale) / 100;
                    } else {
                        $education->sale = 0;
                        $education->total_price = $tariff->price;
                    }

                    if ($this->education_date_start) {
                        $education->date_start = date('Y-m-d', strtotime($this->education_date_start));
                    }

                    if (!$education->save()) {
                        throw new \Exception('Ошибка создания обучения: ' . json_encode($education->errors));
                    }

                    // Записываем в группу (если выбрана)
                    if ($this->group_id) {
                        $educationGroup = new EducationGroup();
                        $educationGroup->education_id = $education->id;
                        $educationGroup->group_id = $this->group_id;

                        if (!$educationGroup->save()) {
                            throw new \Exception('Ошибка записи в группу: ' . json_encode($educationGroup->errors));
                        }
                    }
                }
            }

            // 3. Создаём платёж (если нужен)
            if ($this->create_payment && $this->payment_amount > 0) {
                $payment = new Payment();
                $payment->pupil_id = $pupil->id;
                $payment->amount = $this->payment_amount;
                $payment->method_id = $this->payment_method_id;
                $payment->type = Payment::TYPE_PAY;
                $payment->purpose_id = Payment::PURPOSE_EDUCATION;
                $payment->number = $this->payment_number;

                if ($this->payment_date) {
                    $payment->date = date('Y-m-d H:i:s', strtotime($this->payment_date));
                } else {
                    $payment->date = date('Y-m-d H:i:s');
                }

                if (!$payment->save()) {
                    throw new \Exception('Ошибка создания платежа: ' . json_encode($payment->errors));
                }

                // Обновляем баланс ученика
                \app\models\services\PupilService::updateBalance($pupil->id);
            }

            // 4. Обновляем лида
            $lid->pupil_id = $pupil->id;
            $lid->converted_at = date('Y-m-d H:i:s');

            // Если создаём платёж - переводим в PAID
            if ($this->create_payment && $this->payment_amount > 0) {
                $lid->status = Lids::STATUS_PAID;
            }

            if (!$lid->save(false)) {
                throw new \Exception('Ошибка обновления лида');
            }

            // 5. Записываем в историю
            LidHistory::createConverted($lid, $pupil);

            // 6. Логируем
            ActivityLogger::logLidConverted($lid, $pupil);

            $transaction->commit();
            return $pupil;

        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::error('Ошибка конверсии лида: ' . $e->getMessage(), __METHOD__);
            $this->addError('lid_id', $e->getMessage());
            return null;
        }
    }

    /**
     * Получить список методов оплаты
     * @return array
     */
    public static function getPayMethodList(): array
    {
        return PayMethod::find()
            ->byOrganization()
            ->notDeleted()
            ->select(['name', 'id'])
            ->indexBy('id')
            ->column();
    }

    /**
     * Получить список тарифов
     * @return array
     */
    public static function getTariffList(): array
    {
        return Tariff::find()
            ->byOrganization()
            ->notDeleted()
            ->andWhere(['status' => Tariff::STATUS_ACTIVE])
            ->select(['name', 'id'])
            ->indexBy('id')
            ->column();
    }

    /**
     * Получить список групп по тарифу
     * @param int|null $tariffId
     * @return array
     */
    public static function getGroupListByTariff(?int $tariffId = null): array
    {
        $query = Group::find()
            ->byOrganization()
            ->notDeleted()
            ->andWhere(['status' => Group::STATUS_ACTIVE]);

        // TODO: Фильтровать группы по предметам тарифа если нужно

        return $query
            ->select(['name', 'id'])
            ->indexBy('id')
            ->column();
    }

    /**
     * Получить список полов
     * @return array
     */
    public static function getSexList(): array
    {
        return [
            1 => 'Мужской',
            2 => 'Женский',
        ];
    }
}
