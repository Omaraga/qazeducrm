<?php

namespace app\models\search;

use app\models\TeacherSalary;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * TeacherSalarySearch - модель поиска для зарплат учителей
 */
class TeacherSalarySearch extends TeacherSalary
{
    public $teacher_name;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'teacher_id', 'status', 'lessons_count', 'students_count'], 'integer'],
            [['period_start', 'period_end', 'teacher_name'], 'safe'],
            [['base_amount', 'bonus_amount', 'deduction_amount', 'total_amount'], 'number'],
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
        $query = TeacherSalary::find()
            ->joinWith(['teacher'])
            ->andWhere(['teacher_salary.organization_id' => \app\models\Organizations::getCurrentOrganizationId()])
            ->andWhere(['!=', 'teacher_salary.is_deleted', 1]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'period_start' => SORT_DESC,
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
            'teacher_salary.id' => $this->id,
            'teacher_salary.teacher_id' => $this->teacher_id,
            'teacher_salary.status' => $this->status,
        ]);

        $query->andFilterWhere(['>=', 'teacher_salary.period_start', $this->period_start]);
        $query->andFilterWhere(['<=', 'teacher_salary.period_end', $this->period_end]);

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
