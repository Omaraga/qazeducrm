<?php

namespace app\models\search;

use app\models\Homework;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * HomeworkSearch - поиск домашних заданий
 */
class HomeworkSearch extends Homework
{
    public $date_from;
    public $date_to;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'group_id', 'lesson_id', 'status', 'created_by'], 'integer'],
            [['title', 'description', 'due_date', 'date_from', 'date_to'], 'safe'],
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
     * Поиск с фильтрацией
     */
    public function search($params)
    {
        $query = Homework::find()
            ->with(['group', 'creator']);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['due_date' => SORT_DESC],
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
            'homework.id' => $this->id,
            'homework.group_id' => $this->group_id,
            'homework.lesson_id' => $this->lesson_id,
            'homework.status' => $this->status,
            'homework.created_by' => $this->created_by,
        ]);

        if ($this->due_date) {
            $query->andWhere(['homework.due_date' => $this->due_date]);
        }

        if ($this->date_from) {
            $query->andWhere(['>=', 'homework.due_date', $this->date_from]);
        }
        if ($this->date_to) {
            $query->andWhere(['<=', 'homework.due_date', $this->date_to]);
        }

        $query->andFilterWhere(['like', 'homework.title', $this->title]);
        $query->andFilterWhere(['like', 'homework.description', $this->description]);

        return $dataProvider;
    }
}
