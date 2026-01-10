<?php

namespace app\models\search;

use app\models\Lids;
use app\models\Organizations;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * LidsSearch represents the model behind the search form of `app\models\Lids`.
 */
class LidsSearch extends Lids
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['class_id', 'total_point', 'total_sum', 'sale', 'status', 'manager_id'], 'integer'],
            ['date', 'date', 'format' => 'php:d.m.Y'],
            [['fio', 'phone', 'source'], 'string'],
            [['fio', 'class_id', 'status', 'source', 'next_contact_date'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
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
        $query = Lids::find()
            ->andWhere(['lids.organization_id' => Organizations::getCurrentOrganizationId()])
            ->andWhere(['!=', 'lids.is_deleted', 1]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 20
            ],
            'sort' => [
                'defaultOrder' => [
                    'created_at' => SORT_DESC,
                ],
            ],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        // Фильтр по ФИО (также ищем по телефону и ФИО родителя)
        if ($this->fio && strlen($this->fio) > 0) {
            $searchTerm = mb_strtolower(trim($this->fio), 'UTF-8');
            $query->andWhere([
                'or',
                ['like', 'LOWER(fio)', $searchTerm],
                ['like', 'LOWER(parent_fio)', $searchTerm],
                ['like', 'phone', $this->fio],
                ['like', 'parent_phone', $this->fio],
            ]);
        }

        // Фильтр по телефону (отдельный)
        if ($this->phone && strlen($this->phone) > 0) {
            $query->andFilterWhere(['like', 'phone', $this->phone]);
        }

        // Фильтр по статусу
        $query->andFilterWhere(['status' => $this->status]);

        // Фильтр по источнику
        $query->andFilterWhere(['source' => $this->source]);

        // Фильтр по менеджеру
        $query->andFilterWhere(['manager_id' => $this->manager_id]);

        // Фильтр по классу
        $query->andFilterWhere(['class_id' => $this->class_id]);

        return $dataProvider;
    }
}
