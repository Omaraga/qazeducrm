<?php

namespace app\modules\superadmin\models\search;

use app\models\Organizations;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * OrganizationSearch для фильтрации организаций в супер-админке.
 */
class OrganizationSearch extends Organizations
{
    public $query;
    public $showBranches = false;

    public function rules()
    {
        return [
            [['id', 'parent_id'], 'integer'],
            [['name', 'email', 'status', 'type', 'bin', 'query'], 'safe'],
            [['showBranches'], 'boolean'],
        ];
    }

    public function scenarios()
    {
        return Model::scenarios();
    }

    public function search($params)
    {
        $query = Organizations::find()
            ->andWhere(['is_deleted' => 0]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['created_at' => SORT_DESC],
            ],
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        // По умолчанию показываем только головные организации
        if (!$this->showBranches) {
            $query->andWhere(['or',
                ['parent_id' => null],
                ['type' => Organizations::TYPE_HEAD]
            ]);
        }

        // Фильтр по ID
        $query->andFilterWhere(['id' => $this->id]);

        // Фильтр по parent_id
        $query->andFilterWhere(['parent_id' => $this->parent_id]);

        // Фильтр по статусу
        $query->andFilterWhere(['status' => $this->status]);

        // Фильтр по типу
        $query->andFilterWhere(['type' => $this->type]);

        // Поиск по названию, email, БИН
        if ($this->query) {
            $query->andWhere(['or',
                ['like', 'name', $this->query],
                ['like', 'email', $this->query],
                ['like', 'bin', $this->query],
                ['like', 'legal_name', $this->query],
            ]);
        }

        // Фильтр по названию
        $query->andFilterWhere(['like', 'name', $this->name]);

        // Фильтр по email
        $query->andFilterWhere(['like', 'email', $this->email]);

        // Фильтр по БИН
        $query->andFilterWhere(['like', 'bin', $this->bin]);

        return $dataProvider;
    }
}
