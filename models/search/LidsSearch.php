<?php

namespace app\models\search;

use app\models\Lids;
use app\models\Organizations;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Pupil;

/**
 * PupilSearch represents the model behind the search form of `app\models\Pupil`.
 */
class LidsSearch extends Lids
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['class_id', 'total_point', 'total_sum', 'sale'], 'integer'],
            ['date', 'date', 'format' => 'php:d.m.Y'],
            [['fio', 'phone'], 'string'],
            [['fio', 'class_id'], 'safe'],
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
        $query = Lids::find()->andWhere(['organization_id' => Organizations::getCurrentOrganizationId()]);

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

        // grid filtering conditions
//        $query->andFilterWhere([
//            'id' => $this->id,
//            'fio' => $this->fio,
//            'class_id' => $this->class_id,
//        ]);
        if ($this->fio && strlen($this->fio) > 0){
            $query->andFilterWhere(['like', "LOWER(fio)", mb_strtolower(trim($this->fio), "UTF-8")]);
        }

        if ($this->phone && strlen($this->phone) > 0){
            $query->andFilterWhere(['like', 'phone', $this->phone]);
        }



        return $dataProvider;
    }
}
