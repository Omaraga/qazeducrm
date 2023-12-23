<?php

namespace app\models\search;

use app\components\ActiveQuery;
use app\components\ActiveRecord;
use app\helpers\OrganizationRoles;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\User;

/**
 * UserSearch represents the model behind the search form of `app\models\User`.
 */
class UserSearch extends User
{
    public $contacts;
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['contacts', 'username', 'fio'], 'string'],
            [['id'], 'integer'],
            [['username', 'fio',  'email', 'contacts'], 'safe'],
        ];
    }

    public function safeAttributes()
    {
        return array_merge(['contacts'], parent::safeAttributes());
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
        $query = User::find()->innerJoinWith(['currentUserOrganizations' => function($q){
            $q->andWhere(['<>','user_organization.is_deleted', ActiveRecord::DELETED])->andWhere(['in', 'user_organization.role', [OrganizationRoles::TEACHER]]);
        }]);

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

        if ($this->fio && strlen($this->fio) > 0){
            $query->andFilterWhere(['like', "LOWER(user.fio)", mb_strtolower(trim($this->fio), "UTF-8")]);
        }
        if ($this->username && strlen($this->username) > 0){
            $query->andFilterWhere(['like', 'user.username', $this->username]);
        }
        if ($this->contacts && strlen($this->contacts) > 0){
            $query->andFilterWhere(['or',['like', 'user.phone', $this->contacts], ['like', 'user.home_phone', $this->contacts]]);
        }

        return $dataProvider;
    }
}
