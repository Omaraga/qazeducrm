<?php

namespace app\models\search;

use app\models\TeacherRate;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * TeacherRateSearch - модель поиска для ставок учителей
 */
class TeacherRateSearch extends TeacherRate
{
    public $teacher_name;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'teacher_id', 'subject_id', 'group_id', 'rate_type'], 'integer'],
            [['rate_value'], 'number'],
            [['is_active'], 'boolean'],
            [['teacher_name'], 'safe'],
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
     * Поиск
     */
    public function search($params)
    {
        $query = TeacherRate::find()
            ->joinWith(['teacher', 'subject', 'group'])
            ->andWhere(['teacher_rate.organization_id' => \app\models\Organizations::getCurrentOrganizationId()])
            ->andWhere(['!=', 'teacher_rate.is_deleted', 1]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'created_at' => SORT_DESC,
                ],
            ],
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        $query->andFilterWhere([
            'teacher_rate.id' => $this->id,
            'teacher_rate.teacher_id' => $this->teacher_id,
            'teacher_rate.subject_id' => $this->subject_id,
            'teacher_rate.group_id' => $this->group_id,
            'teacher_rate.rate_type' => $this->rate_type,
            'teacher_rate.is_active' => $this->is_active,
        ]);

        if ($this->teacher_name) {
            $query->andFilterWhere(['or',
                ['like', 'user.first_name', $this->teacher_name],
                ['like', 'user.last_name', $this->teacher_name],
                ['like', 'user.fio', $this->teacher_name],
            ]);
        }

        return $dataProvider;
    }
}
