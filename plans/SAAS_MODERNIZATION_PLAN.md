# План модернизации QazEduCRM в SaaS платформу

## Обзор проекта

**Текущее состояние**: Yii2 CRM для учебных центров с multi-tenancy (organization_id), Bootstrap 4, базовым RBAC.

**Цель**: Превратить в SaaS платформу с современным UI, системой подписок и супер-админкой.

**Стек модернизации**:
- Frontend: Tailwind CSS + Alpine.js
- Биллинг: Ручное управление подписками (без автооплаты)
- Локализация: Русский + Казахский

---

## ФАЗА 1: SaaS Инфраструктура (1-2 недели)

### 1.1 Миграции базы данных

```
migrations/
  m250104_000001_create_saas_plan_table.php
  m250104_000002_create_organization_subscription_table.php
  m250104_000003_create_organization_payment_table.php
  m250104_000004_add_saas_columns_to_organization_table.php
  m250104_000005_create_organization_activity_log_table.php
```

**Таблица saas_plan**:
- code (free, basic, pro, enterprise)
- max_pupils, max_teachers, max_groups, max_admins
- price_monthly, price_yearly, trial_days
- features (JSON)

**Таблица organization_subscription**:
- organization_id, saas_plan_id
- status (trial, active, expired, suspended, cancelled)
- started_at, expires_at, trial_ends_at
- custom_limits (JSON)

**Таблица organization_payment**:
- organization_id, subscription_id
- amount, currency, period_start, period_end
- status (pending, completed, failed, refunded)
- payment_method, payment_reference

**Расширение organization**:
- status (pending, active, suspended, blocked)
- email, email_verified_at, verification_token
- bin, legal_name, logo, timezone, locale
- **parent_id** (NULL для головной организации, ID для филиала)
- **type** (head = головная, branch = филиал)

### 1.2 Архитектура филиалов

```
Головная организация (parent_id = NULL, type = 'head')
  ├── Филиал 1 (parent_id = 1, type = 'branch')
  ├── Филиал 2 (parent_id = 1, type = 'branch')
  └── Филиал 3 (parent_id = 1, type = 'branch')
```

**Иерархия ролей**:
- GENERAL_DIRECTOR - видит все филиалы головной организации
- DIRECTOR - видит только свой филиал
- Несколько GENERAL_DIRECTOR / DIRECTOR на организацию разрешено

**Изменения в UserOrganization**:
- Один пользователь может быть GENERAL_DIRECTOR в головной и DIRECTOR в филиале
- branch_ids (JSON) - список филиалов для GENERAL_DIRECTOR

**Подписка**:
- Подписка привязывается к головной организации
- Лимиты распределяются между филиалами
- Филиал наследует подписку от головной

### 1.3 Новые модели

```
models/
  SaasPlan.php
  OrganizationSubscription.php
  OrganizationPayment.php
  OrganizationActivityLog.php
```

**Критические файлы для изменения**:
- `models/Organizations.php` - добавить связи и методы проверки лимитов

### 1.4 Сервисы

```
services/
  SubscriptionLimitService.php      # Проверка лимитов тарифа
  OrganizationRegistrationService.php  # Регистрация организаций
```

---

## ФАЗА 2: Супер-админка (1-2 недели)

### 2.1 Структура модуля

```
modules/superadmin/
  Module.php
  controllers/
    DefaultController.php       # Dashboard
    OrganizationController.php  # CRUD организаций + филиалы
    BranchController.php        # Управление филиалами
    SubscriptionController.php  # Управление подписками
    PlanController.php          # CRUD тарифных планов
    PaymentController.php       # Платежи организаций
    ActivityController.php      # Логи активности
  models/search/
    OrganizationSearch.php
    SubscriptionSearch.php
    PaymentSearch.php
  views/
    layouts/main.php
    default/index.php           # Dashboard со статистикой
    organization/               # CRUD views
      index.php                 # Список с фильтром по головным/филиалам
      view.php                  # Карточка с вкладкой "Филиалы"
      branches.php              # Список филиалов организации
    branch/
      create.php                # Создать филиал
      update.php
    subscription/
    plan/
    payment/
```

### 2.2 Функции для филиалов

**OrganizationController**:
- `actionBranches($id)` - список филиалов организации
- `actionCreateBranch($parent_id)` - создать филиал

**Фильтры в списке**:
- Показать только головные организации
- Показать все (с вложенностью)
- Поиск по филиалам

### 2.3 Dashboard статистика
- Общее количество организаций (active/pending/suspended)
- Количество филиалов
- Подписки (trial/active/expired/expiring soon)
- Выручка (this month/last month/pending)
- Последние регистрации
- Ожидающие платежи

### 2.4 Конфигурация (config/web.php)
```php
'modules' => [
    'superadmin' => [
        'class' => 'app\modules\superadmin\Module',
    ],
],
'urlManager' => [
    'rules' => [
        'superadmin' => 'superadmin/default/index',
        'superadmin/<controller:\w+>/<action:\w+>' => 'superadmin/<controller>/<action>',
        // ... existing rules
    ],
],
```

---

## ФАЗА 3: Регистрация организаций (3-5 дней)

### 3.1 Форма регистрации
```
models/forms/OrganizationRegistrationForm.php
```
- Данные организации (name, email, phone, bin)
- Данные админа (first_name, last_name, email, password)
- Выбор тарифного плана
- Согласие с условиями

### 3.2 Контроллер
```
controllers/RegistrationController.php
  - actionIndex()         # Форма регистрации
  - actionSuccess()       # Успешная регистрация
  - actionVerifyEmail()   # Подтверждение email
```

### 3.3 Email шаблоны
```
mail/organization-verification.php
```

---

## ФАЗА 4: Landing Page (3-5 дней)

### 4.1 Контроллер и views
```
controllers/LandingController.php
  - actionIndex()     # Главная
  - actionPricing()   # Тарифы
  - actionFeatures()  # Возможности
  - actionContact()   # Контакты

views/layouts/landing.php   # Отдельный layout для публичных страниц
views/landing/
  index.php
  pricing.php
  features.php
  contact.php
```

---

## ФАЗА 5: Редизайн UI/UX (2-3 недели)

### 5.1 Установка Tailwind CSS

```bash
npm init -y
npm install -D tailwindcss @tailwindcss/forms @tailwindcss/typography
npx tailwindcss init
```

**Структура файлов**:
```
web/src/css/tailwind.css
web/dist/css/app.css
tailwind.config.js
package.json
```

**tailwind.config.js**:
```javascript
module.exports = {
  content: ['./views/**/*.php', './widgets/**/*.php'],
  darkMode: 'class',
  theme: {
    extend: {
      colors: {
        primary: { 500: '#3b82f6', 600: '#2563eb', 700: '#1d4ed8' },
        success: '#10b981',
        warning: '#f59e0b',
        danger: '#ef4444',
      },
    },
  },
  plugins: [require('@tailwindcss/forms')],
}
```

### 5.2 Новый Asset Bundle
```
assets/TailwindAsset.php
```

### 5.3 Новые виджеты
```
widgets/tailwind/
  Sidebar.php           # Боковое меню
  Card.php              # Карточки
  DataTable.php         # Замена GridView
  Form.php              # Формы Tailwind
  InputField.php
  SelectField.php
  DatePickerField.php   # Alpine.js based
  Modal.php
  Alert.php
  Badge.php
  Breadcrumbs.php
  Pagination.php
  StatCard.php          # Карточка статистики
```

### 5.4 Новый Layout
```
views/layouts/
  main-tailwind.php     # Sidebar layout
  guest.php             # Для login/landing
  partials/
    sidebar.php
    header.php
    breadcrumbs.php
```

### 5.5 Дизайн-система
- **Цвета**: Primary (синий), Success (зеленый), Warning (оранжевый), Danger (красный)
- **Шрифт**: Inter
- **Layout**: Sidebar слева (сворачиваемый), header сверху
- **Dark mode**: Опционально

### 5.6 Этапы миграции views
1. Layouts + базовые виджеты
2. site/login, site/index (dashboard)
3. pupil/ (все CRUD)
4. group/, user/, payment/
5. reports/, schedule/
6. Справочники (subject, tariff, pay-method)

---

## ФАЗА 6: Продуктовые улучшения (параллельно)

### 6.1 Модуль зарплаты учителей (КРИТИЧНО)
```
models/
  TeacherSalary.php     # organization_id, teacher_id, period, amount, status
  TeacherRate.php       # teacher_id, subject_id, rate_type, rate_value

controllers/
  SalaryController.php

views/salary/
  index.php             # Ведомость зарплат
  calculate.php         # Расчёт за период
```

**Алгоритм расчёта**:
- По посещаемости (LessonAttendance)
- Ставка за урок или % от оплаты ученика
- Бонусы за 100% посещаемость группы

### 6.2 Улучшение воронки лидов
```php
// models/Lids.php - добавить:
const STATUS_NEW = 1;
const STATUS_CONTACTED = 2;
const STATUS_TRIAL = 3;
const STATUS_ENROLLED = 4;
const STATUS_PAID = 5;
const STATUS_LOST = 6;

// Добавить поле source (instagram, whatsapp, 2gis, website)
```

### 6.3 SMS уведомления (v1.0)
- Интеграция с Mobizon/SMS.kz
- Шаблоны: напоминание о занятии, задолженность, день рождения
- Console команда для отправки

---

## Тарифные планы SaaS

| Тариф | Цена/мес | Ученики | Учителя | Группы | Функции |
|-------|----------|---------|---------|--------|---------|
| Free | 0 | 10 | 2 | 3 | Базовый CRM |
| Basic | 9,990 KZT | 50 | 5 | 10 | + SMS, отчёты |
| Pro | 29,990 KZT | 200 | 20 | 50 | + API, лиды |
| Enterprise | 99,990 KZT | ∞ | ∞ | ∞ | Всё + кастом |

---

## Критические файлы для модификации

1. **models/Organizations.php** - добавить связи с подписками, методы проверки лимитов, parent_id для филиалов
2. **models/relations/UserOrganization.php** - поддержка нескольких DIRECTOR на организацию
3. **components/ActiveRecord.php** - паттерн для новых моделей
4. **components/BaseController.php** - добавить SubscriptionBehavior + переключение филиалов
5. **config/web.php** - модуль superadmin, новые URL rules
6. **assets/AppAsset.php** - паттерн для TailwindAsset
7. **views/layouts/main.php** - основа для main-tailwind.php
8. **helpers/MenuHelper.php** - расширить для sidebar меню + селектор филиалов

---

## Переключение филиалов для пользователей

**GENERAL_DIRECTOR**:
- В header dropdown "Выбор филиала" (все филиалы + "Все филиалы")
- При выборе "Все филиалы" - агрегированные данные
- При выборе конкретного - фильтрация по organization_id

**DIRECTOR**:
- Видит только свои филиалы (где role = DIRECTOR)
- Может переключаться между ними

**Реализация**:
```php
// User.php
public $active_branch_id; // текущий выбранный филиал

// Organizations.php
public function getBranches() {
    return $this->hasMany(self::class, ['parent_id' => 'id']);
}
public function getParentOrganization() {
    return $this->hasOne(self::class, ['id' => 'parent_id']);
}
```

---

## Порядок выполнения

### Неделя 1-2: SaaS Инфраструктура
- [ ] Миграции БД (5 файлов)
- [ ] Модели SaaS (4 файла)
- [ ] Сервисы (2 файла)
- [ ] Обновление Organizations.php

### Неделя 3-4: Супер-админка
- [ ] Модуль superadmin (структура)
- [ ] Dashboard контроллер
- [ ] CRUD организаций + филиалы
- [ ] CRUD подписок
- [ ] Платежи

### Неделя 5: Регистрация + Landing
- [ ] Форма регистрации
- [ ] Email верификация
- [ ] Landing pages

### Неделя 6-8: Редизайн UI
- [ ] Установка Tailwind
- [ ] Базовые виджеты
- [ ] Новые layouts
- [ ] Миграция ключевых views

### Параллельно: Продукт
- [ ] Модуль зарплаты учителей
- [ ] Воронка лидов
- [ ] Локализация KZ

---

## Рекомендации по продукту

### Уникальные фичи для Казахстана
1. **Kaspi интеграция** - автоматическое зачисление оплаты
2. **Семейный аккаунт** - единый баланс на всех детей
3. **Умное расписание** - оптимизация загрузки кабинетов

### Метрики успеха (Year 1)
- 200+ активных организаций
- MRR 5 млн KZT
- Churn < 7%
- NPS > 50

### Идеи для удержания
- Программа лояльности EduPoints
- Telegram-чат руководителей центров
- Вебинары и шаблоны документов
