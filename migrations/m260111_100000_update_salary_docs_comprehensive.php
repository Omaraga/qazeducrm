<?php

use yii\db\Migration;

/**
 * Обновляет документацию по расчёту зарплаты с подробными примерами и расчётами
 */
class m260111_100000_update_salary_docs_comprehensive extends Migration
{
    public function safeUp()
    {
        // Глава 8: setup-rates - Полное описание типов ставок
        $this->update('{{%docs_section}}', [
            'content' => $this->getSetupRatesContent(),
            'excerpt' => 'Полное руководство по настройке ставок преподавателей: за ученика, за занятие и процент от оплаты. Подробные формулы и примеры расчёта.',
        ], ['chapter_id' => 8, 'slug' => 'setup-rates']);

        // Глава 8: pay-salary - Расчёт и выплата
        $this->update('{{%docs_section}}', [
            'content' => $this->getPaySalaryContent(),
            'excerpt' => 'Пошаговый процесс расчёта зарплаты: от проставления посещений до выплаты. Статусы, бонусы, удержания.',
        ], ['chapter_id' => 8, 'slug' => 'pay-salary']);

        // Глава 8: salary-history - Детализация
        $this->update('{{%docs_section}}', [
            'content' => $this->getSalaryHistoryContent(),
            'excerpt' => 'Просмотр истории выплат, детализация по занятиям, экспорт отчётов.',
        ], ['chapter_id' => 8, 'slug' => 'salary-history']);

        // Добавляем новый раздел с примерами расчётов
        $chapterId = (new \yii\db\Query())
            ->select('id')
            ->from('{{%docs_chapter}}')
            ->where(['slug' => 'salary'])
            ->scalar();

        if ($chapterId) {
            $maxOrder = (new \yii\db\Query())
                ->select('MAX(sort_order)')
                ->from('{{%docs_section}}')
                ->where(['chapter_id' => $chapterId])
                ->scalar() ?: 0;

            $this->insert('{{%docs_section}}', [
                'chapter_id' => $chapterId,
                'slug' => 'calculation-examples',
                'title' => 'Примеры расчётов',
                'content' => $this->getCalculationExamplesContent(),
                'excerpt' => 'Реальные примеры расчёта зарплаты для всех типов ставок с пошаговыми вычислениями.',
                'sort_order' => $maxOrder + 1,
                'is_active' => 1,
                'is_deleted' => 0,
                'created_at' => new \yii\db\Expression('NOW()'),
                'updated_at' => new \yii\db\Expression('NOW()'),
            ]);
        }
    }

    public function safeDown()
    {
        // Удаляем новый раздел
        $this->delete('{{%docs_section}}', ['slug' => 'calculation-examples']);

        // Возвращаем старый контент (опционально можно оставить новый)
    }

    private function getSetupRatesContent(): string
    {
        return <<<'HTML'
<h2 id="about-rates">О системе ставок</h2>
<p>Система ставок определяет, сколько преподаватель получает за проведённые занятия. Правильная настройка ставок — основа корректного расчёта зарплаты.</p>

<div class="bg-orange-50 border border-orange-200 rounded-lg p-4 my-6">
    <div class="flex gap-3">
        <div class="text-orange-500"><i class="fas fa-star text-xl"></i></div>
        <div>
            <div class="font-medium text-orange-900">Важно понимать</div>
            <div class="text-orange-700 text-sm mt-1">Зарплата начисляется только за занятия со статусом <strong>«Проведено»</strong> и учеников со статусом посещения <strong>«Присутствовал»</strong> или <strong>«Пропуск с оплатой»</strong>. Пропуски без оплаты и неуважительные причины в расчёт не входят.</div>
        </div>
    </div>
</div>

<h2 id="rate-types">Типы ставок</h2>
<p>В системе доступны три типа ставок для гибкой настройки оплаты труда:</p>

<div class="space-y-6 my-6">
    <!-- Ставка за ученика -->
    <div class="bg-white border-2 border-indigo-200 rounded-xl p-6">
        <div class="flex items-center gap-3 mb-4">
            <div class="w-12 h-12 bg-indigo-100 text-indigo-600 rounded-lg flex items-center justify-center">
                <i class="fas fa-user text-xl"></i>
            </div>
            <div>
                <h3 class="text-lg font-bold text-gray-900 mb-0">Ставка за ученика</h3>
                <span class="text-sm text-indigo-600 font-medium">RATE_PER_STUDENT</span>
            </div>
        </div>
        <p class="text-gray-600 mb-4">Преподаватель получает фиксированную сумму за каждого оплачиваемого ученика на занятии.</p>

        <div class="bg-gray-50 rounded-lg p-4 font-mono text-sm mb-4">
            <div class="text-gray-500 mb-1">Формула:</div>
            <div class="text-indigo-700 font-bold">Зарплата = Количество_оплачиваемых_учеников × Ставка_за_ученика</div>
        </div>

        <div class="bg-green-50 border border-green-200 rounded-lg p-4">
            <div class="font-medium text-green-800 mb-2"><i class="fas fa-calculator mr-2"></i>Пример:</div>
            <ul class="text-green-700 text-sm space-y-1">
                <li>Ставка: <strong>500 ₸</strong> за ученика</li>
                <li>На занятии: 12 учеников, из них 10 с оплачиваемым статусом</li>
                <li>Расчёт: 10 × 500 = <strong>5 000 ₸</strong> за это занятие</li>
            </ul>
        </div>
    </div>

    <!-- Ставка за занятие -->
    <div class="bg-white border-2 border-emerald-200 rounded-xl p-6">
        <div class="flex items-center gap-3 mb-4">
            <div class="w-12 h-12 bg-emerald-100 text-emerald-600 rounded-lg flex items-center justify-center">
                <i class="fas fa-chalkboard-teacher text-xl"></i>
            </div>
            <div>
                <h3 class="text-lg font-bold text-gray-900 mb-0">Ставка за занятие</h3>
                <span class="text-sm text-emerald-600 font-medium">RATE_PER_LESSON</span>
            </div>
        </div>
        <p class="text-gray-600 mb-4">Преподаватель получает фиксированную сумму за проведённое занятие независимо от количества учеников.</p>

        <div class="bg-gray-50 rounded-lg p-4 font-mono text-sm mb-4">
            <div class="text-gray-500 mb-1">Формула:</div>
            <div class="text-emerald-700 font-bold">Зарплата = Количество_занятий × Ставка_за_занятие</div>
        </div>

        <div class="bg-green-50 border border-green-200 rounded-lg p-4">
            <div class="font-medium text-green-800 mb-2"><i class="fas fa-calculator mr-2"></i>Пример:</div>
            <ul class="text-green-700 text-sm space-y-1">
                <li>Ставка: <strong>3 000 ₸</strong> за занятие</li>
                <li>Проведено занятий за месяц: 20</li>
                <li>Расчёт: 20 × 3 000 = <strong>60 000 ₸</strong> за месяц</li>
            </ul>
        </div>
    </div>

    <!-- Процентная ставка -->
    <div class="bg-white border-2 border-amber-200 rounded-xl p-6">
        <div class="flex items-center gap-3 mb-4">
            <div class="w-12 h-12 bg-amber-100 text-amber-600 rounded-lg flex items-center justify-center">
                <i class="fas fa-percentage text-xl"></i>
            </div>
            <div>
                <h3 class="text-lg font-bold text-gray-900 mb-0">Процент от оплаты</h3>
                <span class="text-sm text-amber-600 font-medium">RATE_PERCENT</span>
            </div>
        </div>
        <p class="text-gray-600 mb-4">Преподаватель получает процент от стоимости занятия ученика (рассчитывается от тарифа).</p>

        <div class="bg-gray-50 rounded-lg p-4 font-mono text-sm mb-4">
            <div class="text-gray-500 mb-1">Формула:</div>
            <div class="text-amber-700 font-bold">Зарплата = Σ (Цена_занятия_ученика × Процент / 100)</div>
            <div class="text-gray-500 text-xs mt-2">где Цена_занятия = Стоимость_тарифа / Количество_занятий_в_тарифе</div>
        </div>

        <div class="bg-green-50 border border-green-200 rounded-lg p-4">
            <div class="font-medium text-green-800 mb-2"><i class="fas fa-calculator mr-2"></i>Пример:</div>
            <ul class="text-green-700 text-sm space-y-1">
                <li>Ставка: <strong>30%</strong> от оплаты</li>
                <li>Тариф ученика: 40 000 ₸ за 8 занятий</li>
                <li>Цена одного занятия: 40 000 / 8 = 5 000 ₸</li>
                <li>За одного ученика: 5 000 × 30% = <strong>1 500 ₸</strong></li>
                <li>Если на занятии 4 ученика: 1 500 × 4 = <strong>6 000 ₸</strong></li>
            </ul>
        </div>
    </div>
</div>

<h2 id="rate-priority">Приоритет ставок</h2>
<p>Если для преподавателя настроено несколько ставок, система выбирает наиболее специфичную:</p>

<ol class="list-decimal pl-6 space-y-2 my-4">
    <li><strong>Ставка для конкретной группы</strong> — высший приоритет</li>
    <li><strong>Ставка для конкретного предмета</strong> — средний приоритет</li>
    <li><strong>Общая ставка преподавателя</strong> — базовый приоритет</li>
</ol>

<div class="bg-blue-50 border border-blue-200 rounded-lg p-4 my-6">
    <div class="flex gap-3">
        <div class="text-blue-500"><i class="fas fa-lightbulb text-xl"></i></div>
        <div>
            <div class="font-medium text-blue-900">Пример приоритета</div>
            <div class="text-blue-700 text-sm mt-1">
                У преподавателя общая ставка 500 ₸ за ученика. Для VIP-группы установлена отдельная ставка 800 ₸.
                При расчёте зарплаты за VIP-группу будет использована ставка 800 ₸, для остальных — 500 ₸.
            </div>
        </div>
    </div>
</div>

<h2 id="create-rate">Создание ставки</h2>
<ol class="list-decimal pl-6 space-y-3 my-4">
    <li>Перейдите в <strong>Зарплаты → Ставки</strong></li>
    <li>Нажмите кнопку <strong>«Добавить ставку»</strong></li>
    <li>Выберите преподавателя из списка</li>
    <li>Выберите тип ставки:
        <ul class="list-disc pl-6 mt-2 space-y-1">
            <li>За ученика — если оплата зависит от количества учеников</li>
            <li>За занятие — если фиксированная сумма за урок</li>
            <li>Процент — для партнёрских схем оплаты</li>
        </ul>
    </li>
    <li>Введите размер ставки (сумму в тенге или процент)</li>
    <li>При необходимости укажите группу или предмет для индивидуальной ставки</li>
    <li>Нажмите <strong>«Сохранить»</strong></li>
</ol>

<h2 id="attendance-statuses">Статусы посещения</h2>
<p>Не все посещения учитываются при расчёте зарплаты:</p>

<div class="overflow-x-auto my-6">
    <table class="min-w-full bg-white border border-gray-200 rounded-lg overflow-hidden">
        <thead class="bg-gray-100">
            <tr>
                <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Статус</th>
                <th class="px-4 py-3 text-center text-sm font-semibold text-gray-700">Учёт в ЗП</th>
                <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Описание</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            <tr class="hover:bg-gray-50">
                <td class="px-4 py-3"><span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800"><i class="fas fa-check mr-1"></i> Присутствовал</span></td>
                <td class="px-4 py-3 text-center"><span class="text-green-600 font-bold">✓ Да</span></td>
                <td class="px-4 py-3 text-sm text-gray-600">Ученик был на занятии</td>
            </tr>
            <tr class="hover:bg-gray-50">
                <td class="px-4 py-3"><span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800"><i class="fas fa-coins mr-1"></i> Пропуск с оплатой</span></td>
                <td class="px-4 py-3 text-center"><span class="text-green-600 font-bold">✓ Да</span></td>
                <td class="px-4 py-3 text-sm text-gray-600">Ученик не пришёл, но занятие оплачено (например, не предупредил заранее)</td>
            </tr>
            <tr class="hover:bg-gray-50">
                <td class="px-4 py-3"><span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800"><i class="fas fa-times mr-1"></i> Пропуск без оплаты</span></td>
                <td class="px-4 py-3 text-center"><span class="text-red-600 font-bold">✗ Нет</span></td>
                <td class="px-4 py-3 text-sm text-gray-600">Ученик предупредил заранее, занятие не списывается</td>
            </tr>
            <tr class="hover:bg-gray-50">
                <td class="px-4 py-3"><span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800"><i class="fas fa-file-medical mr-1"></i> Уважительная причина</span></td>
                <td class="px-4 py-3 text-center"><span class="text-red-600 font-bold">✗ Нет</span></td>
                <td class="px-4 py-3 text-sm text-gray-600">Болезнь и т.п. — занятие не оплачивается</td>
            </tr>
        </tbody>
    </table>
</div>

<div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 my-6">
    <div class="flex gap-3">
        <div class="text-yellow-500"><i class="fas fa-exclamation-triangle text-xl"></i></div>
        <div>
            <div class="font-medium text-yellow-900">Важно</div>
            <div class="text-yellow-700 text-sm mt-1">Изменение ставки влияет только на будущие расчёты. Уже рассчитанные и утверждённые зарплаты не пересчитываются автоматически.</div>
        </div>
    </div>
</div>
HTML;
    }

    private function getPaySalaryContent(): string
    {
        return <<<'HTML'
<h2 id="calculation-process">Процесс расчёта зарплаты</h2>
<p>Расчёт зарплаты включает несколько шагов для обеспечения точности и прозрачности.</p>

<div class="grid gap-4 my-6">
    <!-- Шаг 1 -->
    <div class="flex gap-4 items-start">
        <div class="w-10 h-10 bg-indigo-500 text-white rounded-full flex items-center justify-center font-bold flex-shrink-0">1</div>
        <div class="flex-1 bg-white border border-gray-200 rounded-lg p-4">
            <h4 class="font-semibold text-gray-900 mb-2">Проведите занятия</h4>
            <p class="text-sm text-gray-600">Преподаватель проводит уроки, которые отмечаются в расписании как «Проведено».</p>
        </div>
    </div>
    <!-- Шаг 2 -->
    <div class="flex gap-4 items-start">
        <div class="w-10 h-10 bg-indigo-500 text-white rounded-full flex items-center justify-center font-bold flex-shrink-0">2</div>
        <div class="flex-1 bg-white border border-gray-200 rounded-lg p-4">
            <h4 class="font-semibold text-gray-900 mb-2">Проставьте посещения</h4>
            <p class="text-sm text-gray-600">После каждого занятия отметьте присутствие учеников с правильными статусами.</p>
        </div>
    </div>
    <!-- Шаг 3 -->
    <div class="flex gap-4 items-start">
        <div class="w-10 h-10 bg-indigo-500 text-white rounded-full flex items-center justify-center font-bold flex-shrink-0">3</div>
        <div class="flex-1 bg-white border border-gray-200 rounded-lg p-4">
            <h4 class="font-semibold text-gray-900 mb-2">Запустите расчёт</h4>
            <p class="text-sm text-gray-600">Перейдите в <strong>Зарплаты → Рассчитать</strong>, выберите преподавателя и период.</p>
        </div>
    </div>
    <!-- Шаг 4 -->
    <div class="flex gap-4 items-start">
        <div class="w-10 h-10 bg-indigo-500 text-white rounded-full flex items-center justify-center font-bold flex-shrink-0">4</div>
        <div class="flex-1 bg-white border border-gray-200 rounded-lg p-4">
            <h4 class="font-semibold text-gray-900 mb-2">Проверьте и утвердите</h4>
            <p class="text-sm text-gray-600">Проверьте детализацию, добавьте бонусы/удержания и утвердите зарплату.</p>
        </div>
    </div>
    <!-- Шаг 5 -->
    <div class="flex gap-4 items-start">
        <div class="w-10 h-10 bg-green-500 text-white rounded-full flex items-center justify-center font-bold flex-shrink-0">5</div>
        <div class="flex-1 bg-white border border-gray-200 rounded-lg p-4">
            <h4 class="font-semibold text-gray-900 mb-2">Выплатите зарплату</h4>
            <p class="text-sm text-gray-600">После фактической выплаты отметьте зарплату как «Выплачено».</p>
        </div>
    </div>
</div>

<h2 id="calculate">Как рассчитать зарплату</h2>
<ol class="list-decimal pl-6 space-y-3 my-4">
    <li>Перейдите в раздел <strong>Зарплаты</strong></li>
    <li>Нажмите кнопку <strong>«Рассчитать»</strong></li>
    <li>Выберите:
        <ul class="list-disc pl-6 mt-2 space-y-1">
            <li><strong>Преподавателя</strong> — или всех сразу</li>
            <li><strong>Период</strong> — месяц для расчёта</li>
        </ul>
    </li>
    <li>Нажмите <strong>«Рассчитать»</strong></li>
    <li>Система создаст расчёт в статусе «Черновик»</li>
</ol>

<h2 id="view-details">Детализация расчёта</h2>
<p>После расчёта вы увидите подробную информацию:</p>

<ul class="list-disc pl-6 space-y-2 my-4">
    <li><strong>Общая информация</strong> — ФИО преподавателя, период, итоговая сумма</li>
    <li><strong>Количество занятий</strong> — сколько уроков учтено в расчёте</li>
    <li><strong>Количество учеников</strong> — общее число оплачиваемых посещений</li>
    <li><strong>Детализация по дням</strong> — каждое занятие с расчётом</li>
</ul>

<div class="bg-blue-50 border border-blue-200 rounded-lg p-4 my-6">
    <div class="flex gap-3">
        <div class="text-blue-500"><i class="fas fa-search text-xl"></i></div>
        <div>
            <div class="font-medium text-blue-900">Проверяйте детализацию</div>
            <div class="text-blue-700 text-sm mt-1">Перед утверждением обязательно проверьте детализацию. Убедитесь, что все занятия учтены и статусы посещений правильные.</div>
        </div>
    </div>
</div>

<h2 id="bonus-deduction">Бонусы и удержания</h2>
<p>До утверждения можно добавить корректировки:</p>

<div class="grid md:grid-cols-2 gap-4 my-6">
    <div class="bg-green-50 border border-green-200 rounded-lg p-4">
        <h4 class="font-semibold text-green-900 mb-2"><i class="fas fa-plus-circle mr-2"></i>Бонусы</h4>
        <ul class="text-green-700 text-sm space-y-1">
            <li>Премия за результаты</li>
            <li>Доплата за замены</li>
            <li>Надбавка за сложность</li>
        </ul>
    </div>
    <div class="bg-red-50 border border-red-200 rounded-lg p-4">
        <h4 class="font-semibold text-red-900 mb-2"><i class="fas fa-minus-circle mr-2"></i>Удержания</h4>
        <ul class="text-red-700 text-sm space-y-1">
            <li>Штрафы за опоздания</li>
            <li>Возвраты авансов</li>
            <li>Компенсация ущерба</li>
        </ul>
    </div>
</div>

<h2 id="statuses">Статусы зарплаты</h2>

<div class="overflow-x-auto my-6">
    <table class="min-w-full bg-white border border-gray-200 rounded-lg overflow-hidden">
        <thead class="bg-gray-100">
            <tr>
                <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Статус</th>
                <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Что можно делать</th>
                <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Следующий шаг</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            <tr class="hover:bg-gray-50">
                <td class="px-4 py-3"><span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">Черновик</span></td>
                <td class="px-4 py-3 text-sm text-gray-600">Редактирование, пересчёт, добавление бонусов</td>
                <td class="px-4 py-3 text-sm text-gray-600">Утвердить →</td>
            </tr>
            <tr class="hover:bg-gray-50">
                <td class="px-4 py-3"><span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">Утверждено</span></td>
                <td class="px-4 py-3 text-sm text-gray-600">Только просмотр (редактирование заблокировано)</td>
                <td class="px-4 py-3 text-sm text-gray-600">Выплатить →</td>
            </tr>
            <tr class="hover:bg-gray-50">
                <td class="px-4 py-3"><span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">Выплачено</span></td>
                <td class="px-4 py-3 text-sm text-gray-600">Только просмотр истории</td>
                <td class="px-4 py-3 text-sm text-gray-600">— (финальный статус)</td>
            </tr>
        </tbody>
    </table>
</div>

<div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 my-6">
    <div class="flex gap-3">
        <div class="text-yellow-500"><i class="fas fa-lock text-xl"></i></div>
        <div>
            <div class="font-medium text-yellow-900">Права доступа</div>
            <div class="text-yellow-700 text-sm mt-1">Утверждение и выплата зарплаты доступны только директору или генеральному директору. Администратор может только создавать и редактировать черновики.</div>
        </div>
    </div>
</div>
HTML;
    }

    private function getSalaryHistoryContent(): string
    {
        return <<<'HTML'
<h2 id="history">История выплат</h2>
<p>Раздел «Зарплаты» содержит полную историю всех начислений и выплат организации.</p>

<h2 id="view-list">Просмотр списка</h2>
<p>В списке зарплат отображается:</p>
<ul class="list-disc pl-6 space-y-2 my-4">
    <li><strong>Преподаватель</strong> — ФИО сотрудника</li>
    <li><strong>Период</strong> — за какой месяц начислено</li>
    <li><strong>Уроков</strong> — количество учтённых занятий</li>
    <li><strong>Учеников</strong> — общее количество оплачиваемых посещений</li>
    <li><strong>Сумма</strong> — итоговая сумма к выплате</li>
    <li><strong>Статус</strong> — черновик / утверждено / выплачено</li>
</ul>

<h2 id="filters">Фильтрация</h2>
<p>Используйте фильтры для быстрого поиска:</p>
<ul class="list-disc pl-6 space-y-2 my-4">
    <li><strong>Преподаватель</strong> — поиск по имени</li>
    <li><strong>Статус</strong> — черновик, утверждено, выплачено</li>
    <li><strong>Период</strong> — выбор месяца</li>
</ul>

<h2 id="details">Детализация зарплаты</h2>
<p>При открытии записи видна полная информация:</p>

<div class="bg-white border border-gray-200 rounded-lg p-6 my-6">
    <h4 class="font-semibold text-gray-900 mb-4">Пример детализации</h4>
    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-3 py-2 text-left text-gray-600">Дата</th>
                    <th class="px-3 py-2 text-left text-gray-600">Группа</th>
                    <th class="px-3 py-2 text-center text-gray-600">Учеников</th>
                    <th class="px-3 py-2 text-left text-gray-600">Ставка</th>
                    <th class="px-3 py-2 text-right text-gray-600">Сумма</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <tr>
                    <td class="px-3 py-2">06.01.2026</td>
                    <td class="px-3 py-2">Математика Группа 1</td>
                    <td class="px-3 py-2 text-center">12</td>
                    <td class="px-3 py-2">500 ₸/ученик</td>
                    <td class="px-3 py-2 text-right font-medium">6 000 ₸</td>
                </tr>
                <tr>
                    <td class="px-3 py-2">08.01.2026</td>
                    <td class="px-3 py-2">Математика Группа 1</td>
                    <td class="px-3 py-2 text-center">11</td>
                    <td class="px-3 py-2">500 ₸/ученик</td>
                    <td class="px-3 py-2 text-right font-medium">5 500 ₸</td>
                </tr>
                <tr>
                    <td class="px-3 py-2">10.01.2026</td>
                    <td class="px-3 py-2">Математика Группа 2</td>
                    <td class="px-3 py-2 text-center">15</td>
                    <td class="px-3 py-2">500 ₸/ученик</td>
                    <td class="px-3 py-2 text-right font-medium">7 500 ₸</td>
                </tr>
                <tr class="bg-gray-50 font-bold">
                    <td class="px-3 py-2" colspan="4">Итого</td>
                    <td class="px-3 py-2 text-right">19 000 ₸</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<h2 id="teacher-view">Просмотр преподавателем</h2>
<p>Преподаватели видят только свои зарплаты:</p>
<ul class="list-disc pl-6 space-y-2 my-4">
    <li>Список всех начислений</li>
    <li>Детализация по занятиям</li>
    <li>Текущий статус</li>
</ul>

<div class="bg-blue-50 border border-blue-200 rounded-lg p-4 my-6">
    <div class="flex gap-3">
        <div class="text-blue-500"><i class="fas fa-eye text-xl"></i></div>
        <div>
            <div class="font-medium text-blue-900">Настройка доступа</div>
            <div class="text-blue-700 text-sm mt-1">Доступ преподавателей к просмотру своих зарплат можно включить или отключить в настройках организации.</div>
        </div>
    </div>
</div>

<h2 id="export">Экспорт данных</h2>
<ol class="list-decimal pl-6 space-y-2 my-4">
    <li>Примените нужные фильтры</li>
    <li>Нажмите кнопку <strong>«Экспорт»</strong></li>
    <li>Выберите формат (Excel)</li>
    <li>Скачайте файл</li>
</ol>
HTML;
    }

    private function getCalculationExamplesContent(): string
    {
        return <<<'HTML'
<h2 id="intro">Реальные примеры расчёта</h2>
<p>Рассмотрим примеры расчёта зарплаты для каждого типа ставки на основе реальных данных.</p>

<div class="bg-orange-50 border border-orange-200 rounded-lg p-4 my-6">
    <div class="flex gap-3">
        <div class="text-orange-500"><i class="fas fa-database text-xl"></i></div>
        <div>
            <div class="font-medium text-orange-900">Исходные данные</div>
            <div class="text-orange-700 text-sm mt-1">
                Период расчёта: январь 2026<br>
                Тариф учеников: 40 000 ₸ за 8 занятий (5 000 ₸ за занятие)
            </div>
        </div>
    </div>
</div>

<!-- Пример 1: Ставка за ученика -->
<div class="bg-white border-2 border-indigo-300 rounded-xl p-6 my-8">
    <div class="flex items-center gap-3 mb-6">
        <div class="w-12 h-12 bg-indigo-500 text-white rounded-lg flex items-center justify-center">
            <i class="fas fa-user text-xl"></i>
        </div>
        <div>
            <h3 class="text-xl font-bold text-gray-900 mb-0">Пример 1: Ставка за ученика</h3>
            <span class="text-sm text-indigo-600">Преподаватель Иванов П.С.</span>
        </div>
    </div>

    <div class="grid md:grid-cols-2 gap-6">
        <div>
            <h4 class="font-semibold text-gray-700 mb-3">Условия:</h4>
            <ul class="space-y-2 text-sm">
                <li class="flex gap-2"><span class="text-gray-500">Ставка:</span> <strong>500 ₸</strong> за ученика</li>
                <li class="flex gap-2"><span class="text-gray-500">Группы:</span> Математика-1 (15 уч.), Математика-2 (15 уч.)</li>
                <li class="flex gap-2"><span class="text-gray-500">Занятий:</span> 26 (13 + 13)</li>
            </ul>
        </div>
        <div>
            <h4 class="font-semibold text-gray-700 mb-3">Посещаемость за месяц:</h4>
            <ul class="space-y-1 text-sm">
                <li class="flex justify-between"><span class="text-green-600">✓ Присутствовали:</span> <strong>280</strong></li>
                <li class="flex justify-between"><span class="text-yellow-600">✓ Пропуск с оплатой:</span> <strong>39</strong></li>
                <li class="flex justify-between"><span class="text-red-600">✗ Пропуск без оплаты:</span> <span class="text-gray-400">61</span></li>
            </ul>
        </div>
    </div>

    <div class="mt-6 bg-indigo-50 rounded-lg p-4">
        <h4 class="font-semibold text-indigo-900 mb-3">Расчёт:</h4>
        <div class="space-y-2 font-mono text-sm">
            <div>Оплачиваемых посещений = 280 + 39 = <strong>319</strong></div>
            <div>Зарплата = 319 × 500 = <strong class="text-xl text-indigo-700">159 500 ₸</strong></div>
        </div>
    </div>
</div>

<!-- Пример 2: Ставка за занятие -->
<div class="bg-white border-2 border-emerald-300 rounded-xl p-6 my-8">
    <div class="flex items-center gap-3 mb-6">
        <div class="w-12 h-12 bg-emerald-500 text-white rounded-lg flex items-center justify-center">
            <i class="fas fa-chalkboard-teacher text-xl"></i>
        </div>
        <div>
            <h3 class="text-xl font-bold text-gray-900 mb-0">Пример 2: Ставка за занятие</h3>
            <span class="text-sm text-emerald-600">Преподаватель Сидорова А.В.</span>
        </div>
    </div>

    <div class="grid md:grid-cols-2 gap-6">
        <div>
            <h4 class="font-semibold text-gray-700 mb-3">Условия:</h4>
            <ul class="space-y-2 text-sm">
                <li class="flex gap-2"><span class="text-gray-500">Ставка:</span> <strong>3 000 ₸</strong> за занятие</li>
                <li class="flex gap-2"><span class="text-gray-500">Группа:</span> Английский (индивидуально)</li>
                <li class="flex gap-2"><span class="text-gray-500">Расписание:</span> Вт, Чт</li>
            </ul>
        </div>
        <div>
            <h4 class="font-semibold text-gray-700 mb-3">Занятия за месяц:</h4>
            <ul class="space-y-1 text-sm">
                <li class="flex justify-between"><span>Проведено занятий:</span> <strong>8</strong></li>
                <li class="flex justify-between"><span class="text-gray-400">Отменено (праздники):</span> <span class="text-gray-400">1</span></li>
            </ul>
        </div>
    </div>

    <div class="mt-6 bg-emerald-50 rounded-lg p-4">
        <h4 class="font-semibold text-emerald-900 mb-3">Расчёт:</h4>
        <div class="space-y-2 font-mono text-sm">
            <div>Занятий проведено = <strong>8</strong></div>
            <div>Зарплата = 8 × 3 000 = <strong class="text-xl text-emerald-700">24 000 ₸</strong></div>
        </div>
    </div>

    <div class="mt-4 bg-blue-50 border border-blue-200 rounded-lg p-3 text-sm">
        <strong>Примечание:</strong> При ставке за занятие количество учеников не влияет на расчёт. Преподаватель получает одинаковую сумму независимо от наполненности группы.
    </div>
</div>

<!-- Пример 3: Процентная ставка -->
<div class="bg-white border-2 border-amber-300 rounded-xl p-6 my-8">
    <div class="flex items-center gap-3 mb-6">
        <div class="w-12 h-12 bg-amber-500 text-white rounded-lg flex items-center justify-center">
            <i class="fas fa-percentage text-xl"></i>
        </div>
        <div>
            <h3 class="text-xl font-bold text-gray-900 mb-0">Пример 3: Процентная ставка</h3>
            <span class="text-sm text-amber-600">Преподаватель Козлов Д.А.</span>
        </div>
    </div>

    <div class="grid md:grid-cols-2 gap-6">
        <div>
            <h4 class="font-semibold text-gray-700 mb-3">Условия:</h4>
            <ul class="space-y-2 text-sm">
                <li class="flex gap-2"><span class="text-gray-500">Ставка:</span> <strong>30%</strong> от оплаты</li>
                <li class="flex gap-2"><span class="text-gray-500">Группа:</span> Физика (2 ученика)</li>
                <li class="flex gap-2"><span class="text-gray-500">Расписание:</span> Сб</li>
            </ul>
        </div>
        <div>
            <h4 class="font-semibold text-gray-700 mb-3">Данные тарифа:</h4>
            <ul class="space-y-1 text-sm">
                <li class="flex justify-between"><span>Стоимость тарифа:</span> <strong>40 000 ₸</strong></li>
                <li class="flex justify-between"><span>Занятий в тарифе:</span> <strong>8</strong></li>
                <li class="flex justify-between"><span>Цена за занятие:</span> <strong>5 000 ₸</strong></li>
            </ul>
        </div>
    </div>

    <div class="mt-6">
        <h4 class="font-semibold text-gray-700 mb-3">Детализация по занятиям:</h4>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm bg-gray-50 rounded-lg overflow-hidden">
                <thead class="bg-amber-100">
                    <tr>
                        <th class="px-3 py-2 text-left">Дата</th>
                        <th class="px-3 py-2 text-center">Учеников</th>
                        <th class="px-3 py-2 text-left">Расчёт</th>
                        <th class="px-3 py-2 text-right">Сумма</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="border-t border-gray-200">
                        <td class="px-3 py-2">04.01.2026</td>
                        <td class="px-3 py-2 text-center">1</td>
                        <td class="px-3 py-2 font-mono text-xs">5000 × 30% × 1</td>
                        <td class="px-3 py-2 text-right">1 500 ₸</td>
                    </tr>
                    <tr class="border-t border-gray-200">
                        <td class="px-3 py-2">11.01.2026</td>
                        <td class="px-3 py-2 text-center">2</td>
                        <td class="px-3 py-2 font-mono text-xs">5000 × 30% × 2</td>
                        <td class="px-3 py-2 text-right">3 000 ₸</td>
                    </tr>
                    <tr class="border-t border-gray-200">
                        <td class="px-3 py-2">18.01.2026</td>
                        <td class="px-3 py-2 text-center">2</td>
                        <td class="px-3 py-2 font-mono text-xs">5000 × 30% × 2</td>
                        <td class="px-3 py-2 text-right">3 000 ₸</td>
                    </tr>
                    <tr class="border-t border-gray-200">
                        <td class="px-3 py-2">25.01.2026</td>
                        <td class="px-3 py-2 text-center">2</td>
                        <td class="px-3 py-2 font-mono text-xs">5000 × 30% × 2</td>
                        <td class="px-3 py-2 text-right">3 000 ₸</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-6 bg-amber-50 rounded-lg p-4">
        <h4 class="font-semibold text-amber-900 mb-3">Итоговый расчёт:</h4>
        <div class="space-y-2 font-mono text-sm">
            <div>Занятий: 4, Посещений: 7 (1 + 2 + 2 + 2)</div>
            <div>Зарплата = 1500 + 3000 + 3000 + 3000 = <strong class="text-xl text-amber-700">10 500 ₸</strong></div>
        </div>
    </div>

    <div class="mt-4 bg-yellow-50 border border-yellow-200 rounded-lg p-3 text-sm">
        <strong>Важно:</strong> При процентной ставке для каждого ученика учитывается его персональный тариф. Если у учеников разные тарифы (например, один на групповом, другой на индивидуальном), расчёт делается по каждому тарифу отдельно.
    </div>
</div>

<h2 id="summary">Сводная таблица</h2>
<p>Итоги расчётов всех преподавателей за январь 2026:</p>

<div class="overflow-x-auto my-6">
    <table class="min-w-full bg-white border border-gray-200 rounded-lg overflow-hidden">
        <thead class="bg-gray-100">
            <tr>
                <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Преподаватель</th>
                <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Тип ставки</th>
                <th class="px-4 py-3 text-center text-sm font-semibold text-gray-700">Ставка</th>
                <th class="px-4 py-3 text-center text-sm font-semibold text-gray-700">Занятий</th>
                <th class="px-4 py-3 text-center text-sm font-semibold text-gray-700">Учеников</th>
                <th class="px-4 py-3 text-right text-sm font-semibold text-gray-700">Зарплата</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            <tr class="hover:bg-gray-50">
                <td class="px-4 py-3 font-medium">Иванов П.С.</td>
                <td class="px-4 py-3"><span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">За ученика</span></td>
                <td class="px-4 py-3 text-center">500 ₸</td>
                <td class="px-4 py-3 text-center">26</td>
                <td class="px-4 py-3 text-center">319</td>
                <td class="px-4 py-3 text-right font-bold text-indigo-600">159 500 ₸</td>
            </tr>
            <tr class="hover:bg-gray-50">
                <td class="px-4 py-3 font-medium">Сидорова А.В.</td>
                <td class="px-4 py-3"><span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">За занятие</span></td>
                <td class="px-4 py-3 text-center">3 000 ₸</td>
                <td class="px-4 py-3 text-center">8</td>
                <td class="px-4 py-3 text-center">—</td>
                <td class="px-4 py-3 text-right font-bold text-emerald-600">24 000 ₸</td>
            </tr>
            <tr class="hover:bg-gray-50">
                <td class="px-4 py-3 font-medium">Козлов Д.А.</td>
                <td class="px-4 py-3"><span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-amber-100 text-amber-800">Процент</span></td>
                <td class="px-4 py-3 text-center">30%</td>
                <td class="px-4 py-3 text-center">4</td>
                <td class="px-4 py-3 text-center">7</td>
                <td class="px-4 py-3 text-right font-bold text-amber-600">10 500 ₸</td>
            </tr>
        </tbody>
        <tfoot class="bg-gray-50">
            <tr>
                <td class="px-4 py-3 font-bold" colspan="5">Итого к выплате</td>
                <td class="px-4 py-3 text-right font-bold text-lg text-gray-900">194 000 ₸</td>
            </tr>
        </tfoot>
    </table>
</div>

<h2 id="faq">Частые вопросы</h2>

<div class="space-y-4 my-6">
    <div class="bg-white border border-gray-200 rounded-lg p-4">
        <h4 class="font-semibold text-gray-900 mb-2">Почему зарплата меньше ожидаемой?</h4>
        <p class="text-sm text-gray-600">Проверьте: 1) Все ли занятия отмечены как «Проведено»; 2) Правильно ли проставлены статусы посещений (пропуски без оплаты не учитываются); 3) Настроена ли ставка для данной группы/предмета.</p>
    </div>
    <div class="bg-white border border-gray-200 rounded-lg p-4">
        <h4 class="font-semibold text-gray-900 mb-2">Как пересчитать уже созданную зарплату?</h4>
        <p class="text-sm text-gray-600">Если зарплата в статусе «Черновик», откройте её и нажмите «Пересчитать». Утверждённые зарплаты пересчёту не подлежат.</p>
    </div>
    <div class="bg-white border border-gray-200 rounded-lg p-4">
        <h4 class="font-semibold text-gray-900 mb-2">У разных учеников разные тарифы — как считать процент?</h4>
        <p class="text-sm text-gray-600">Система автоматически берёт тариф каждого ученика и рассчитывает процент индивидуально. Суммы складываются.</p>
    </div>
    <div class="bg-white border border-gray-200 rounded-lg p-4">
        <h4 class="font-semibold text-gray-900 mb-2">Можно ли настроить разные ставки для разных групп?</h4>
        <p class="text-sm text-gray-600">Да. При создании ставки выберите конкретную группу. Эта ставка будет иметь приоритет над общей ставкой преподавателя.</p>
    </div>
</div>
HTML;
    }
}
