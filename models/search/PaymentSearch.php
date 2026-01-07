<?php

namespace app\models\search;

use app\models\PayMethod;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Payment;

/**
 * PaymentSearch represents the model behind the search form of `app\models\Payment`.
 */
class PaymentSearch extends Model
{
    public $date_start;
    public $date_end;
    public $type;
    public $method_id;
    public $sum = 0;

    // Аналитика по методам оплаты
    public $incomeByMethod = [];
    public $expenseByMethod = [];
    public $totalIncome = 0;
    public $totalExpense = 0;

    public function __construct($config = [])
    {
        $this->date_start = date('d.m.Y');
        $this->date_end = date('d.m.Y');
        parent::__construct($config);
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['date_start', 'date_end', 'type', 'method_id'], 'safe'],
        ];
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
        // Eager loading для оптимизации N+1 запросов
        $query = Payment::find()
            ->with(['method', 'pupil'])
            ->byOrganization()
            ->orderBy('date DESC');

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 30
            ],
        ]);


        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        if ($this->date_start){
            $query->andFilterWhere(['>=', 'date', date('Y-m-d H:i', strtotime($this->date_start))]);
        }
        if ($this->date_end){
            $query->andFilterWhere(['<', 'date', date('Y-m-d H:i', strtotime($this->date_end) + 24 * 60 * 60)]);
        }

        if ($this->type && $this->type > 0){
            $query->andFilterWhere(['type' => $this->type]);
        }

        // Фильтр по методу оплаты
        if ($this->method_id && $this->method_id > 0) {
            $query->andFilterWhere(['method_id' => $this->method_id]);
        }

        // Вычисляем статистику
        $this->calculateStatistics($query);

        return $dataProvider;
    }

    /**
     * Вычисляет статистику по платежам
     */
    protected function calculateStatistics($query)
    {
        $cloneQuery = clone $query;
        $payments = $cloneQuery->select('amount, type, method_id')->asArray()->all();

        // Сбрасываем счетчики
        $this->sum = 0;
        $this->totalIncome = 0;
        $this->totalExpense = 0;
        $incomeTotals = [];
        $expenseTotals = [];

        foreach ($payments as $payment) {
            $methodId = $payment['method_id'] ?? 0;

            if ($payment['type'] == Payment::TYPE_PAY) {
                $this->sum += $payment['amount'];
                $this->totalIncome += $payment['amount'];

                // Группируем приход по методу оплаты
                if (!isset($incomeTotals[$methodId])) {
                    $incomeTotals[$methodId] = 0;
                }
                $incomeTotals[$methodId] += $payment['amount'];
            } else {
                $this->sum -= $payment['amount'];
                $this->totalExpense += $payment['amount'];

                // Группируем расход по методу оплаты
                if (!isset($expenseTotals[$methodId])) {
                    $expenseTotals[$methodId] = 0;
                }
                $expenseTotals[$methodId] += $payment['amount'];
            }
        }

        // Загружаем названия методов оплаты
        $this->loadMethodNames($incomeTotals, $expenseTotals);
    }

    /**
     * Загружает названия методов оплаты и формирует массивы для отображения
     */
    protected function loadMethodNames($incomeTotals, $expenseTotals)
    {
        // Собираем все уникальные method_id
        $allMethodIds = array_unique(array_merge(
            array_keys($incomeTotals),
            array_keys($expenseTotals)
        ));

        if (empty($allMethodIds)) {
            return;
        }

        // Загружаем названия методов
        $methods = PayMethod::find()
            ->where(['id' => $allMethodIds])
            ->indexBy('id')
            ->all();

        // Формируем массив приходов
        foreach ($incomeTotals as $methodId => $total) {
            $this->incomeByMethod[] = [
                'id' => $methodId,
                'name' => $methodId && isset($methods[$methodId]) ? $methods[$methodId]->name : 'Без метода',
                'total' => (float)$total,
            ];
        }

        // Формируем массив расходов
        foreach ($expenseTotals as $methodId => $total) {
            $this->expenseByMethod[] = [
                'id' => $methodId,
                'name' => $methodId && isset($methods[$methodId]) ? $methods[$methodId]->name : 'Без метода',
                'total' => (float)$total,
            ];
        }

        // Сортируем по сумме (от большей к меньшей)
        usort($this->incomeByMethod, function($a, $b) {
            return $b['total'] <=> $a['total'];
        });
        usort($this->expenseByMethod, function($a, $b) {
            return $b['total'] <=> $a['total'];
        });
    }

    public function attributeLabels()
    {
        return [
            'date_end' => \Yii::t('main', 'Период по'),
            'date_start' => \Yii::t('main', 'Период с'),
            'type' => \Yii::t('main', 'Тип платежа'),
            'method_id' => \Yii::t('main', 'Способ оплаты'),
        ];
    }
}
