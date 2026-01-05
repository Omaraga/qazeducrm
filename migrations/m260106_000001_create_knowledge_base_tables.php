<?php

use yii\db\Migration;

/**
 * Миграция для создания базы знаний
 */
class m260106_000001_create_knowledge_base_tables extends Migration
{
    public function safeUp()
    {
        // Таблица категорий
        $this->createTable('{{%knowledge_category}}', [
            'id' => $this->primaryKey(),
            'slug' => $this->string(100)->notNull()->unique(),
            'name' => $this->string(255)->notNull(),
            'description' => $this->text(),
            'icon' => $this->string(50),
            'sort_order' => $this->integer()->defaultValue(0),
            'is_active' => $this->smallInteger(1)->defaultValue(1),
            'is_deleted' => $this->smallInteger(1)->defaultValue(0),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ]);

        // Таблица статей
        $this->createTable('{{%knowledge_article}}', [
            'id' => $this->primaryKey(),
            'category_id' => $this->integer()->notNull(),
            'slug' => $this->string(200)->notNull()->unique(),
            'title' => $this->string(500)->notNull(),
            'content' => $this->getDb()->getSchema()->createColumnSchemaBuilder('longtext')->notNull(),
            'excerpt' => $this->text(),
            'icon' => $this->string(50),
            'sort_order' => $this->integer()->defaultValue(0),
            'views' => $this->integer()->defaultValue(0),
            'is_featured' => $this->smallInteger(1)->defaultValue(0),
            'is_active' => $this->smallInteger(1)->defaultValue(1),
            'is_deleted' => $this->smallInteger(1)->defaultValue(0),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ]);

        // Индексы
        $this->createIndex('idx-knowledge_category-slug', '{{%knowledge_category}}', 'slug');
        $this->createIndex('idx-knowledge_article-slug', '{{%knowledge_article}}', 'slug');
        $this->createIndex('idx-knowledge_article-category', '{{%knowledge_article}}', 'category_id');
        $this->createIndex('idx-knowledge_article-active', '{{%knowledge_article}}', ['is_active', 'is_deleted']);

        // Foreign key
        $this->addForeignKey(
            'fk-knowledge_article-category',
            '{{%knowledge_article}}',
            'category_id',
            '{{%knowledge_category}}',
            'id',
            'RESTRICT',
            'CASCADE'
        );

        // ===================== SEED DATA =====================

        // Категории
        $this->batchInsert('{{%knowledge_category}}',
            ['slug', 'name', 'description', 'icon', 'sort_order'],
            [
                ['getting-started', 'Начало работы', 'Руководство для новых пользователей', 'rocket', 1],
                ['modules', 'Инструкции по модулям', 'Подробные инструкции по каждому модулю системы', 'book', 2],
                ['faq', 'Часто задаваемые вопросы', 'Ответы на популярные вопросы', 'question-mark', 3],
                ['support', 'Помощь и поддержка', 'Контакты и способы получения помощи', 'support', 4],
            ]
        );

        // Статьи
        $this->batchInsert('{{%knowledge_article}}',
            ['category_id', 'slug', 'title', 'content', 'excerpt', 'icon', 'sort_order', 'is_featured'],
            [
                // Начало работы (category_id = 1)
                [1, 'welcome', 'Добро пожаловать в QazEduCRM', $this->getWelcomeContent(), 'Обзор возможностей системы и первые шаги', 'home', 1, 1],
                [1, 'first-steps', 'Первые шаги после регистрации', $this->getFirstStepsContent(), 'Что делать сразу после входа в систему', 'flag', 2, 1],
                [1, 'navigation', 'Навигация по системе', $this->getNavigationContent(), 'Как ориентироваться в интерфейсе', 'map', 3, 0],

                // Модули (category_id = 2)
                [2, 'pupils-guide', 'Работа с учениками', $this->getPupilsGuideContent(), 'Добавление, редактирование и управление учениками', 'users', 1, 1],
                [2, 'groups-guide', 'Управление группами', $this->getGroupsGuideContent(), 'Создание групп и распределение учеников', 'group', 2, 0],
                [2, 'schedule-guide', 'Расписание занятий', $this->getScheduleGuideContent(), 'Настройка и управление расписанием', 'calendar', 3, 0],
                [2, 'payments-guide', 'Платежи и оплата', $this->getPaymentsGuideContent(), 'Приём оплаты и ведение финансов', 'payment', 4, 1],
                [2, 'reports-guide', 'Отчёты', $this->getReportsGuideContent(), 'Аналитика и отчётность', 'chart', 5, 0],

                // FAQ (category_id = 3)
                [3, 'faq-login', 'Проблемы со входом', $this->getFaqLoginContent(), 'Что делать, если не получается войти', 'key', 1, 0],
                [3, 'faq-payment', 'Вопросы по оплате', $this->getFaqPaymentContent(), 'Частые вопросы о приёме платежей', 'payment', 2, 0],
                [3, 'faq-schedule', 'Вопросы по расписанию', $this->getFaqScheduleContent(), 'Частые вопросы о расписании', 'calendar', 3, 0],

                // Поддержка (category_id = 4)
                [4, 'contact-support', 'Связь с поддержкой', $this->getSupportContactContent(), 'Как связаться с технической поддержкой', 'phone', 1, 0],
                [4, 'bug-report', 'Сообщить об ошибке', $this->getBugReportContent(), 'Как правильно сообщить о проблеме', 'warning', 2, 0],
            ]
        );
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk-knowledge_article-category', '{{%knowledge_article}}');
        $this->dropTable('{{%knowledge_article}}');
        $this->dropTable('{{%knowledge_category}}');
    }

    // ===================== CONTENT HELPERS =====================

    private function getWelcomeContent(): string
    {
        return <<<HTML
<h2>Добро пожаловать в QazEduCRM!</h2>
<p>QazEduCRM — это современная система управления образовательным центром, которая поможет вам:</p>
<ul>
    <li><strong>Управлять учениками</strong> — ведение базы учеников, история обучения, контакты</li>
    <li><strong>Формировать группы</strong> — создание учебных групп, распределение учеников</li>
    <li><strong>Составлять расписание</strong> — гибкое планирование занятий</li>
    <li><strong>Вести учёт посещаемости</strong> — отмечайте присутствие на уроках</li>
    <li><strong>Принимать оплату</strong> — удобный приём и учёт платежей</li>
    <li><strong>Анализировать данные</strong> — отчёты и статистика</li>
</ul>
<h3>Начните работу</h3>
<p>Рекомендуем начать с изучения раздела "Первые шаги" и настройки основных справочников.</p>
HTML;
    }

    private function getFirstStepsContent(): string
    {
        return <<<HTML
<h2>Первые шаги после регистрации</h2>
<p>После входа в систему выполните следующие действия:</p>
<h3>1. Настройте справочники</h3>
<p>Перейдите в раздел <strong>Настройки</strong> и добавьте:</p>
<ul>
    <li><strong>Предметы</strong> — список учебных дисциплин</li>
    <li><strong>Тарифы</strong> — стоимость обучения</li>
    <li><strong>Кабинеты</strong> — учебные помещения</li>
    <li><strong>Способы оплаты</strong> — наличные, карта, перевод</li>
</ul>
<h3>2. Добавьте сотрудников</h3>
<p>В разделе <strong>Сотрудники</strong> создайте учётные записи для преподавателей и администраторов.</p>
<h3>3. Создайте группы</h3>
<p>Перейдите в <strong>Группы</strong> и создайте учебные группы с указанием предмета и преподавателя.</p>
<h3>4. Добавьте учеников</h3>
<p>В разделе <strong>Ученики</strong> заведите карточки учеников и запишите их в группы.</p>
HTML;
    }

    private function getNavigationContent(): string
    {
        return <<<HTML
<h2>Навигация по системе</h2>
<p>Интерфейс системы состоит из следующих элементов:</p>
<h3>Боковое меню</h3>
<p>В левой части экрана расположено главное меню со всеми разделами системы.</p>
<h3>Верхняя панель</h3>
<p>Содержит переключатель филиалов (если у вас несколько), уведомления и доступ к профилю.</p>
<h3>Основные разделы</h3>
<ul>
    <li><strong>Dashboard</strong> — главная страница с ключевыми показателями</li>
    <li><strong>Ученики</strong> — база учеников</li>
    <li><strong>Группы</strong> — учебные группы</li>
    <li><strong>Расписание</strong> — расписание занятий</li>
    <li><strong>Платежи</strong> — финансы</li>
    <li><strong>Отчёты</strong> — аналитика</li>
</ul>
HTML;
    }

    private function getPupilsGuideContent(): string
    {
        return <<<HTML
<h2>Работа с учениками</h2>
<h3>Добавление ученика</h3>
<ol>
    <li>Перейдите в раздел <strong>Ученики</strong></li>
    <li>Нажмите кнопку <strong>Добавить ученика</strong></li>
    <li>Заполните обязательные поля: ФИО, телефон, пол</li>
    <li>Укажите дополнительную информацию при необходимости</li>
    <li>Нажмите <strong>Сохранить</strong></li>
</ol>
<h3>Запись в группу</h3>
<ol>
    <li>Откройте карточку ученика</li>
    <li>Перейдите на вкладку <strong>Обучение</strong></li>
    <li>Нажмите <strong>Записать в группу</strong></li>
    <li>Выберите группу и тариф</li>
</ol>
<h3>Баланс ученика</h3>
<p>Баланс автоматически рассчитывается на основе внесённых платежей и стоимости обучения.</p>
HTML;
    }

    private function getGroupsGuideContent(): string
    {
        return <<<HTML
<h2>Управление группами</h2>
<h3>Создание группы</h3>
<ol>
    <li>Перейдите в раздел <strong>Группы</strong></li>
    <li>Нажмите <strong>Создать группу</strong></li>
    <li>Укажите код группы, название, предмет</li>
    <li>Назначьте преподавателя</li>
</ol>
<h3>Добавление учеников в группу</h3>
<p>Учеников можно добавить двумя способами:</p>
<ul>
    <li>Из карточки группы — вкладка "Ученики"</li>
    <li>Из карточки ученика — вкладка "Обучение"</li>
</ul>
HTML;
    }

    private function getScheduleGuideContent(): string
    {
        return <<<HTML
<h2>Расписание занятий</h2>
<h3>Типовое расписание</h3>
<p>Создайте шаблон расписания, который будет повторяться каждую неделю. Это позволит автоматически генерировать уроки.</p>
<h3>Создание урока</h3>
<ol>
    <li>Перейдите в раздел <strong>Расписание</strong></li>
    <li>Нажмите на нужную дату в календаре</li>
    <li>Выберите группу, время начала и окончания</li>
    <li>Укажите кабинет (опционально)</li>
    <li>Сохраните урок</li>
</ol>
<h3>Отметка посещаемости</h3>
<p>Нажмите на созданный урок и отметьте присутствующих учеников.</p>
HTML;
    }

    private function getPaymentsGuideContent(): string
    {
        return <<<HTML
<h2>Платежи и оплата</h2>
<h3>Приём платежа</h3>
<ol>
    <li>Перейдите в раздел <strong>Платежи</strong></li>
    <li>Нажмите <strong>Принять оплату</strong></li>
    <li>Выберите ученика</li>
    <li>Укажите сумму и способ оплаты</li>
    <li>Подтвердите платёж</li>
</ol>
<h3>Быстрая оплата из карточки</h3>
<p>Платёж можно принять прямо из карточки ученика, нажав кнопку "Принять оплату".</p>
<h3>История платежей</h3>
<p>Все платежи сохраняются в истории и доступны в отчётах.</p>
HTML;
    }

    private function getReportsGuideContent(): string
    {
        return <<<HTML
<h2>Отчёты</h2>
<h3>Доступные отчёты</h3>
<ul>
    <li><strong>Дневной отчёт</strong> — посещаемость и платежи за день</li>
    <li><strong>Месячный отчёт</strong> — итоги за месяц</li>
    <li><strong>Отчёт по сотрудникам</strong> — показатели преподавателей</li>
</ul>
<h3>Фильтры</h3>
<p>Используйте фильтры по датам, группам и преподавателям для получения нужной информации.</p>
HTML;
    }

    private function getFaqLoginContent(): string
    {
        return <<<HTML
<h2>Проблемы со входом</h2>
<h3>Забыли пароль?</h3>
<p>На странице входа нажмите "Забыли пароль?" и следуйте инструкциям для восстановления.</p>
<h3>Учётная запись заблокирована</h3>
<p>Обратитесь к администратору вашей организации или в техническую поддержку.</p>
<h3>Не приходит письмо для восстановления</h3>
<ul>
    <li>Проверьте папку "Спам"</li>
    <li>Убедитесь, что указали правильный email</li>
    <li>Подождите несколько минут и попробуйте снова</li>
</ul>
HTML;
    }

    private function getFaqPaymentContent(): string
    {
        return <<<HTML
<h2>Вопросы по оплате</h2>
<h3>Как отменить платёж?</h3>
<p>Для отмены платежа обратитесь к администратору. Удалённые платежи восстановить нельзя.</p>
<h3>Как вернуть деньги ученику?</h3>
<p>Создайте платёж типа "Возврат" на нужную сумму.</p>
<h3>Не отображается баланс</h3>
<p>Баланс рассчитывается автоматически. Если данные некорректны, обратитесь в поддержку.</p>
HTML;
    }

    private function getFaqScheduleContent(): string
    {
        return <<<HTML
<h2>Вопросы по расписанию</h2>
<h3>Как отменить урок?</h3>
<p>Откройте урок в расписании и нажмите "Отменить". При отмене посещаемость не списывается.</p>
<h3>Как перенести урок?</h3>
<p>Измените дату и время урока в режиме редактирования.</p>
<h3>Расписание не генерируется</h3>
<p>Убедитесь, что создан шаблон типового расписания для группы.</p>
HTML;
    }

    private function getSupportContactContent(): string
    {
        return <<<HTML
<h2>Связь с поддержкой</h2>
<h3>Способы связи</h3>
<ul>
    <li><strong>Email:</strong> support@qazedu.kz</li>
    <li><strong>Telegram:</strong> @qazedu_support</li>
    <li><strong>WhatsApp:</strong> +7 (777) 123-45-67</li>
</ul>
<h3>Время работы</h3>
<p>Понедельник — Пятница: 09:00 — 18:00 (Алматы)</p>
<h3>Срочные вопросы</h3>
<p>Для срочных вопросов используйте Telegram или WhatsApp.</p>
HTML;
    }

    private function getBugReportContent(): string
    {
        return <<<HTML
<h2>Сообщить об ошибке</h2>
<h3>Как правильно сообщить о проблеме</h3>
<p>Чтобы мы могли быстро решить проблему, укажите:</p>
<ol>
    <li><strong>Что вы делали</strong> — опишите ваши действия пошагово</li>
    <li><strong>Что произошло</strong> — опишите ошибку или неожиданное поведение</li>
    <li><strong>Что ожидали</strong> — как система должна была работать</li>
    <li><strong>Скриншот</strong> — приложите снимок экрана с ошибкой</li>
</ol>
<h3>Куда отправить</h3>
<p>Отправьте описание на support@qazedu.kz с темой "Ошибка в системе".</p>
HTML;
    }
}
