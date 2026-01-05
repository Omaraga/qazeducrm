<?php

namespace app\modules\superadmin\models\search;

use app\models\KnowledgeArticle;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * KnowledgeArticleSearch - поиск статей базы знаний в супер-админке.
 */
class KnowledgeArticleSearch extends KnowledgeArticle
{
    public $query;

    public function rules()
    {
        return [
            [['id', 'category_id', 'is_active', 'is_featured'], 'integer'],
            [['title', 'slug', 'query'], 'safe'],
        ];
    }

    public function scenarios()
    {
        return Model::scenarios();
    }

    public function search($params)
    {
        $query = KnowledgeArticle::find()
            ->andWhere(['is_deleted' => 0]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['sort_order' => SORT_ASC, 'id' => SORT_ASC],
            ],
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        // Фильтр по ID
        $query->andFilterWhere(['id' => $this->id]);

        // Фильтр по категории
        $query->andFilterWhere(['category_id' => $this->category_id]);

        // Фильтр по статусу активности
        $query->andFilterWhere(['is_active' => $this->is_active]);

        // Фильтр по избранному
        $query->andFilterWhere(['is_featured' => $this->is_featured]);

        // Поиск по заголовку и slug
        if ($this->query) {
            $query->andWhere(['or',
                ['like', 'title', $this->query],
                ['like', 'slug', $this->query],
                ['like', 'excerpt', $this->query],
            ]);
        }

        // Фильтр по заголовку
        $query->andFilterWhere(['like', 'title', $this->title]);

        // Фильтр по slug
        $query->andFilterWhere(['like', 'slug', $this->slug]);

        return $dataProvider;
    }
}
