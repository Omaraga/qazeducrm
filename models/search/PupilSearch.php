<?php

namespace app\models\search;

use app\models\Group;
use app\models\Organizations;
use app\models\PupilEducation;
use app\models\Tariff;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Pupil;

/**
 * PupilSearch represents the model behind the search form of `app\models\Pupil`.
 */
class PupilSearch extends Pupil
{
    public $contacts;
    public $parent_contacts;

    // Расширенные фильтры
    public $balance_type;    // positive/negative/zero
    public $group_id;        // фильтр по группе
    public $tariff_id;       // фильтр по тарифу
    public $date_from;       // дата регистрации от
    public $date_to;         // дата регистрации до

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['class_id', 'status', 'group_id', 'tariff_id'], 'integer'],
            [['fio', 'contacts', 'parent_contacts', 'balance_type'], 'string'],
            [['iin', 'fio', 'class_id', 'status', 'balance_type', 'group_id', 'tariff_id', 'date_from', 'date_to'], 'safe'],
            [['date_from', 'date_to'], 'date', 'format' => 'php:d.m.Y'],
        ];
    }

    public function safeAttributes()
    {
        return array_merge(
            ['contacts', 'parent_contacts', 'balance_type', 'group_id', 'tariff_id', 'date_from', 'date_to'],
            parent::safeAttributes()
        );
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        // Используем таблицу напрямую без алиаса, т.к. notDeleted() уже применен в find()
        $query = Pupil::find()
            ->andWhere(['pupil.organization_id' => Organizations::getCurrentOrganizationId()]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 20
            ],
            'sort' => [
                'defaultOrder' => ['id' => SORT_DESC],
            ],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        // Базовые фильтры
        if ($this->fio && strlen($this->fio) > 0) {
            $query->andFilterWhere(['like', "LOWER(pupil.fio)", mb_strtolower(trim($this->fio), "UTF-8")]);
        }
        if ($this->iin && strlen($this->iin) > 0) {
            $query->andFilterWhere(['like', 'pupil.iin', $this->iin]);
        }
        if ($this->contacts && strlen($this->contacts) > 0) {
            $query->andFilterWhere(['or', ['like', 'pupil.phone', $this->contacts], ['like', 'pupil.home_phone', $this->contacts]]);
        }
        if ($this->parent_contacts && strlen($this->parent_contacts) > 0) {
            $query->andFilterWhere(['like', 'pupil.parent_phone', $this->parent_contacts]);
        }

        // Фильтр по классу
        if ($this->class_id) {
            $query->andFilterWhere(['pupil.class_id' => $this->class_id]);
        }

        // Фильтр по статусу
        if ($this->status !== null && $this->status !== '') {
            $query->andFilterWhere(['pupil.status' => $this->status]);
        }

        // Фильтр по балансу
        if ($this->balance_type) {
            switch ($this->balance_type) {
                case 'positive':
                    $query->andWhere(['>', 'pupil.balance', 0]);
                    break;
                case 'negative':
                    $query->andWhere(['<', 'pupil.balance', 0]);
                    break;
                case 'zero':
                    $query->andWhere(['pupil.balance' => 0]);
                    break;
            }
        }

        // Фильтр по дате регистрации
        if ($this->date_from) {
            $dateFrom = \DateTime::createFromFormat('d.m.Y', $this->date_from);
            if ($dateFrom) {
                $query->andWhere(['>=', 'pupil.created_at', $dateFrom->setTime(0, 0, 0)->getTimestamp()]);
            }
        }
        if ($this->date_to) {
            $dateTo = \DateTime::createFromFormat('d.m.Y', $this->date_to);
            if ($dateTo) {
                $query->andWhere(['<=', 'pupil.created_at', $dateTo->setTime(23, 59, 59)->getTimestamp()]);
            }
        }

        // Фильтр по группе (через pupil_education и education_group)
        if ($this->group_id) {
            $query->innerJoin('pupil_education pe_g', 'pe_g.pupil_id = pupil.id AND pe_g.is_deleted = 0')
                  ->innerJoin('education_group eg', 'eg.education_id = pe_g.id')
                  ->andWhere(['eg.group_id' => $this->group_id])
                  ->distinct();
        }

        // Фильтр по тарифу (через pupil_education)
        if ($this->tariff_id) {
            // Используем подзапрос чтобы избежать дублей если уже есть JOIN
            if (!$this->group_id) {
                $query->innerJoin('pupil_education pe_t', 'pe_t.pupil_id = pupil.id AND pe_t.is_deleted = 0')
                      ->andWhere(['pe_t.tariff_id' => $this->tariff_id])
                      ->distinct();
            } else {
                $query->andWhere(['pe_g.tariff_id' => $this->tariff_id]);
            }
        }

        return $dataProvider;
    }

    /**
     * Возвращает список групп для фильтра
     * @return array
     */
    public static function getGroupsList(): array
    {
        return Group::find()
            ->select(['name', 'id'])
            ->byOrganization()
            ->notDeleted()
            ->orderBy(['name' => SORT_ASC])
            ->indexBy('id')
            ->column();
    }

    /**
     * Возвращает список тарифов для фильтра
     * @return array
     */
    public static function getTariffsList(): array
    {
        return Tariff::find()
            ->select(['name', 'id'])
            ->byOrganization()
            ->notDeleted()
            ->orderBy(['name' => SORT_ASC])
            ->indexBy('id')
            ->column();
    }

    /**
     * Возвращает варианты фильтра по балансу
     * @return array
     */
    public static function getBalanceTypeOptions(): array
    {
        return [
            'positive' => 'Положительный (+)',
            'negative' => 'Отрицательный (-)',
            'zero' => 'Нулевой (0)',
        ];
    }

    /**
     * Возвращает варианты статуса
     * @return array
     */
    public static function getStatusOptions(): array
    {
        return [
            Pupil::STATUS_ACTIVE => 'Активные',
            Pupil::STATUS_ARCHIVED => 'Архивные',
        ];
    }
}
