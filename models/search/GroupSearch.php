<?php

namespace app\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Group;

/**
 * GroupSearch represents the model behind the search form of `app\models\Group`.
 */
class GroupSearch extends Group
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'subject_id', 'category_id', 'type',], 'integer'],
            [['code', 'name'], 'safe'],
        ];
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
        $query = Group::find()->byOrganization();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 20
            ],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }


        if ($this->category_id && $this->category_id > 0){
            $query->andFilterWhere(['category_id' => $this->category_id]);
        }

        if ($this->subject_id && $this->subject_id > 0){
            $query->andFilterWhere(['subject_id' => $this->subject_id]);
        }

        if ($this->name && strlen($this->name) > 0){
            $query->andFilterWhere(['like', 'name', $this->name]);
        }
        if ($this->code && strlen($this->code) > 0){
            $query->andFilterWhere(['like', 'code', $this->code]);
        }


        return $dataProvider;
    }
}
