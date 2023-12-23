<?php

namespace app\models\search;

use app\models\Organizations;
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
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['class_id'], 'integer'],
            [['fio', 'contacts', 'parent_contacts'], 'string'],
            [['iin', 'fio', 'class_id'], 'safe'],
        ];
    }
    public function safeAttributes()
    {
        return array_merge(['contacts', 'parent_contacts'], parent::safeAttributes());
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
        $query = Pupil::find()->andWhere(['organization_id' => Organizations::getCurrentOrganizationId()]);

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
        if ($this->iin && strlen($this->iin) > 0){
            $query->andFilterWhere(['like', 'iin', $this->iin]);
        }
        if ($this->contacts && strlen($this->contacts) > 0){
            $query->andFilterWhere(['or',['like', 'phone', $this->contacts], ['like', 'home_phone', $this->contacts]]);
        }
        if ($this->parent_contacts && strlen($this->parent_contacts) > 0){
            $query->andFilterWhere(['like', 'parent_phone', $this->parent_contacts]);
        }



        return $dataProvider;
    }
}
