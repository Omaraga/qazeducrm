<?php

namespace app\models\search;

use app\models\TrialLesson;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * TrialLessonSearch - поиск пробных занятий
 */
class TrialLessonSearch extends TrialLesson
{
    /** @var string Поиск по имени/телефону лида */
    public $lid_name;

    /** @var string Дата с (формат dd.mm.yyyy) */
    public $date_from;

    /** @var string Дата по (формат dd.mm.yyyy) */
    public $date_to;

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['id', 'lid_id', 'group_id', 'status', 'rating'], 'integer'],
            [['date', 'time', 'feedback', 'lid_name', 'date_from', 'date_to'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios(): array
    {
        return Model::scenarios();
    }

    /**
     * Поиск с фильтрацией
     */
    public function search(array $params): ActiveDataProvider
    {
        $query = TrialLesson::find()->with(['lid', 'group']);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['date' => SORT_DESC, 'time' => SORT_DESC],
            ],
            'pagination' => ['pageSize' => 20],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        $query->andFilterWhere([
            'trial_lesson.id' => $this->id,
            'trial_lesson.lid_id' => $this->lid_id,
            'trial_lesson.group_id' => $this->group_id,
            'trial_lesson.status' => $this->status,
            'trial_lesson.rating' => $this->rating,
        ]);

        if ($this->date) {
            $query->andWhere(['trial_lesson.date' => $this->date]);
        }

        // Фильтр по периоду дат (конвертация dd.mm.yyyy -> yyyy-mm-dd)
        if ($this->date_from) {
            $dateFrom = $this->convertDate($this->date_from);
            if ($dateFrom) {
                $query->andWhere(['>=', 'trial_lesson.date', $dateFrom]);
            }
        }

        if ($this->date_to) {
            $dateTo = $this->convertDate($this->date_to);
            if ($dateTo) {
                $query->andWhere(['<=', 'trial_lesson.date', $dateTo]);
            }
        }

        // Поиск по имени/телефону лида
        if ($this->lid_name) {
            $query->joinWith('lid');
            $query->andWhere([
                'or',
                ['like', 'lids.fio', $this->lid_name],
                ['like', 'lids.parent_fio', $this->lid_name],
                ['like', 'lids.phone', $this->lid_name],
            ]);
        }

        $query->andFilterWhere(['like', 'trial_lesson.feedback', $this->feedback]);

        return $dataProvider;
    }

    /**
     * Конвертирует дату из dd.mm.yyyy в yyyy-mm-dd
     */
    private function convertDate(string $date): ?string
    {
        // Поддержка обоих форматов
        if (preg_match('/^(\d{2})\.(\d{2})\.(\d{4})$/', $date, $m)) {
            return "{$m[3]}-{$m[2]}-{$m[1]}";
        }

        // Если уже в формате yyyy-mm-dd
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return $date;
        }

        return null;
    }
}
