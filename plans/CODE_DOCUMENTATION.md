# QazEduCRM - Полная документация кода

> Документация создана: 2026-01-06
> Версия: 1.0

## Содержание
1. [Обзор архитектуры](#обзор-архитектуры)
2. [Модели (64)](#модели)
3. [Контроллеры (22)](#контроллеры)
4. [Виджеты (15)](#виджеты)
5. [Хелперы (9)](#хелперы)
6. [Компоненты (9)](#компоненты)
7. [Трейты (2)](#трейты)
8. [Диаграммы связей](#диаграммы-связей)

---

## Обзор архитектуры

QazEduCRM - образовательная CRM система на базе Yii2 Basic Template.

### Ключевые особенности
- **Multi-Organization Support** - все данные изолированы по `organization_id`
- **Soft Delete** - мягкое удаление через `is_deleted` флаг
- **JSON Info Field** - дополнительные атрибуты в JSON поле `info`
- **RBAC** - ролевая система доступа (SystemRoles + OrganizationRoles)
- **i18n** - поддержка ru/kz/en через DbMessageSource

### Структура URL
```
/<organization_id>/<controller>/<action>
```

### Базовые классы
- `app\components\ActiveRecord` - авто-заполнение organization_id, soft delete
- `app\components\ActiveQuery` - scopes: `notDeleted()`, `byOrganization()`
- `app\components\BaseController` - инициализация языка и организации
- `modules\crm\controllers\CrmBaseController` - JSON helpers, flash messages

---

## Модели

### Основные модели (7)

#### User
**Файл:** `models/User.php`
**Назначение:** Пользователи системы, implements IdentityInterface
**Связи:** Organizations (many-to-many через UserOrganization)
**Поля:** id, username, password_hash, email, auth_key, access_token, status, created_at, updated_at

#### Pupil
**Файл:** `models/Pupil.php`
**Назначение:** Ученики/Студенты
**Связи:** PupilEducation, EducationGroup, Payment
**Ключевые поля:** name, surname, middlename, fio, phone, balance, status, organization_id
**Методы:** `save()` - автоматическое вычисление FIO
**Статусы:** STATUS_ACTIVE, STATUS_ARCHIVE

#### Group
**Файл:** `models/Group.php`
**Назначение:** Учебные группы
**Связи:** Subject, TeacherGroup, Lesson
**Ключевые поля:** name, subject_id, status, type, max_pupils
**Типы:** TYPE_GROUP (групповые), TYPE_INDIVIDUAL (индивидуальные)

#### Lesson
**Файл:** `models/Lesson.php`
**Назначение:** Уроки/Занятия
**Связи:** Group, User (teacher), Room, LessonAttendance
**Ключевые поля:** group_id, teacher_id, room_id, date, time_start, time_end, status
**Статусы:** STATUS_PLANNED, STATUS_COMPLETED, STATUS_CANCELLED

#### Payment
**Файл:** `models/Payment.php`
**Назначение:** Платежи учеников
**Связи:** Pupil, PayMethod, Organizations
**Ключевые поля:** pupil_id, summ, pay_method_id, type, date
**Типы:** TYPE_PAYMENT, TYPE_REFUND, TYPE_EXPENSE

#### Lids (Лиды)
**Файл:** `models/Lids.php` (710 строк)
**Назначение:** Потенциальные клиенты (воронка продаж)
**Связи:** LidHistory, LidTag (через LidTagRelation), User (manager)
**Ключевые поля:** name, phone, email, source, status, manager_id, next_contact_date
**Статусы воронки (7):**
- STATUS_NEW = 0 (Новый)
- STATUS_CONTACTED = 1 (Связались)
- STATUS_INTERESTED = 2 (Заинтересован)
- STATUS_MEETING = 3 (Назначена встреча)
- STATUS_TRIAL = 4 (Пробный урок)
- STATUS_NEGOTIATION = 5 (Переговоры)
- STATUS_CONVERTED = 6 (Конвертирован)
- STATUS_LOST = 7 (Потерян)

**Методы:**
- `getStatusLabel()`, `getSourceLabel()` - получение labels
- `addCustomTag()`, `removeCustomTag()` - управление тегами
- `checkDuplicate()` - поиск дубликатов
- `getWhatsAppLink()` - ссылка WhatsApp

#### Subject
**Файл:** `models/Subject.php`
**Назначение:** Учебные предметы/дисциплины
**Связи:** Group, TariffSubject
**Ключевые поля:** name, organization_id

---

### Модели отношений (4)

#### EducationGroup
**Файл:** `models/EducationGroup.php`
**Назначение:** Связь ученика с группой (N-to-M)
**Связи:** PupilEducation, Group, Pupil
**Поля:** education_id, group_id, pupil_id

#### TariffSubject
**Файл:** `models/TariffSubject.php`
**Назначение:** Связь тарифа с предметами
**Связи:** Tariff, Subject
**Поля:** tariff_id, subject_id

#### TeacherGroup
**Файл:** `models/TeacherGroup.php`
**Назначение:** Связь преподавателя с группой
**Связи:** User, Group
**Поля:** teacher_id, group_id

#### UserOrganization
**Файл:** `models/UserOrganization.php`
**Назначение:** Связь пользователя с организацией
**Связи:** User, Organizations
**Поля:** user_id, organization_id, role

---

### Модели истории и взаимодействий (3)

#### LidHistory
**Файл:** `models/LidHistory.php`
**Назначение:** История взаимодействий с лидами
**Типы взаимодействий (8):**
- TYPE_CREATED = 1 (Создан)
- TYPE_CALL = 2 (Звонок)
- TYPE_SMS = 3 (SMS)
- TYPE_WHATSAPP = 4 (WhatsApp)
- TYPE_NOTE = 5 (Заметка)
- TYPE_STATUS_CHANGE = 6 (Смена статуса)
- TYPE_MEETING = 7 (Встреча)
- TYPE_CONVERTED = 8 (Конвертирован)

**Методы:**
- `createStatusChange()` - запись смены статуса
- `createConverted()` - запись конверсии
- `getTypeIcon()`, `getTypeColor()` - визуальные атрибуты

#### OrganizationActivityLog
**Файл:** `models/OrganizationActivityLog.php`
**Назначение:** Логи активности организаций (аудит)
**Категории (6):**
- CATEGORY_GENERAL, CATEGORY_ORGANIZATION
- CATEGORY_SUBSCRIPTION, CATEGORY_PAYMENT
- CATEGORY_STATUS, CATEGORY_AUTH

**Поля:** organization_id, category, action, details, ip_address, user_agent

#### Notification
**Файл:** `models/Notification.php`
**Назначение:** Уведомления и напоминания
**Типы (5):**
- TYPE_INFO, TYPE_WARNING, TYPE_SUCCESS, TYPE_IMPORTANT, TYPE_REMINDER

**Методы:**
- `createLidReminder()` - напоминание о лиде
- `createOverdueContactAlert()` - просроченный контакт
- `markAsRead()`, `markAllAsRead()` - отметка прочитанным

---

### Модели лидов и тегов (4)

#### LidTag
**Файл:** `models/LidTag.php`
**Назначение:** Пользовательские теги для лидов
**Поля:** name, color, icon, organization_id
**Методы:**
- `createDefaults()` - создание стандартных тегов
- `getPresetColors()`, `getPresetIcons()` - предустановленные варианты

#### LidTagRelation
**Файл:** `models/LidTagRelation.php`
**Назначение:** Связь лидов с тегами (N-to-M)
**Поля:** lid_id, tag_id (unique constraint)

#### SalesScript
**Файл:** `models/SalesScript.php`
**Назначение:** Скрипты продаж для каждого статуса лида
**Поля:** status, title, script_text, objections (JSON), tips (JSON)
**Методы:**
- `getForStatus()` - скрипт для статуса
- `getAllGroupedByStatus()` - все скрипты по статусам
- `createDefaults()` - стандартные скрипты

---

### Модели расписаний (3)

#### ScheduleTemplate
**Файл:** `models/ScheduleTemplate.php`
**Назначение:** Шаблоны расписаний
**Связи:** TypicalSchedule
**Поля:** name, is_default, organization_id

#### TypicalSchedule
**Файл:** `models/TypicalSchedule.php`
**Назначение:** Типовое расписание (шаблоны занятий)
**Связи:** ScheduleTemplate, Group, User (teacher), Room
**Поля:** template_id, day_of_week, time_start, time_end, group_id, teacher_id, room_id

#### Room
**Файл:** `models/Room.php`
**Назначение:** Кабинеты/Помещения
**Связи:** Lesson, TypicalSchedule
**Поля:** name, capacity, organization_id

---

### Модели платежей и тарификации (8)

#### Tariff
**Файл:** `models/Tariff.php`
**Назначение:** Тарифы обучения
**Связи:** TariffSubject, Subject
**Поля:** name, price, lessons_count, duration_days, organization_id

#### PayMethod
**Файл:** `models/PayMethod.php`
**Назначение:** Методы оплаты
**Поля:** name, is_active, organization_id

#### PupilEducation
**Файл:** `models/PupilEducation.php`
**Назначение:** Образование ученика (связь с тарифом)
**Связи:** Pupil, Tariff, EducationGroup
**Поля:** pupil_id, tariff_id, start_date, end_date, lessons_left

#### OrganizationPayment
**Файл:** `models/OrganizationPayment.php`
**Назначение:** Платежи организации в систему
**Связи:** Organizations
**Поля:** organization_id, amount, payment_date, status

#### OrganizationSubscription
**Файл:** `models/OrganizationSubscription.php`
**Назначение:** Подписка организации
**Связи:** Organizations, SaasPlan
**Поля:** organization_id, plan_id, start_date, end_date, status

#### SaasPlan
**Файл:** `models/SaasPlan.php`
**Назначение:** SaaS планы системы (для супер-админа)
**Поля:** name, price, max_pupils, max_users, features (JSON)

#### TariffForm
**Файл:** `models/forms/TariffForm.php`
**Назначение:** Форма работы с тарифом

#### PaymentForm
**Файл:** `models/forms/PaymentForm.php`
**Назначение:** Форма платежа

---

### Модели персонала (5)

#### TeacherRate
**Файл:** `models/TeacherRate.php`
**Назначение:** Ставки преподавателей
**Связи:** User, Tariff
**Поля:** teacher_id, tariff_id, rate_per_lesson, rate_type

#### TeacherSalary
**Файл:** `models/TeacherSalary.php`
**Назначение:** Зарплата преподавателей
**Связи:** User, TeacherSalaryDetail
**Поля:** teacher_id, period_start, period_end, total_amount, status

#### TeacherSalaryDetail
**Файл:** `models/TeacherSalaryDetail.php`
**Назначение:** Детализация зарплаты
**Связи:** TeacherSalary, Lesson
**Поля:** salary_id, lesson_id, amount

#### TeacherForm
**Файл:** `models/forms/TeacherForm.php`
**Назначение:** Форма создания преподавателя

#### TeacherService
**Файл:** `models/services/TeacherService.php`
**Назначение:** Сервис операций с преподавателем

---

### Модели знаний и SMS (4)

#### KnowledgeArticle
**Файл:** `models/KnowledgeArticle.php`
**Назначение:** Статьи базы знаний
**Связи:** KnowledgeCategory
**Поля:** category_id, title, slug, content, view_count, is_featured
**Методы:**
- `findBySlug()` - поиск по slug
- `getFeatured()` - избранные статьи
- `search()` - полнотекстовый поиск
- `getRelatedArticles()` - связанные статьи

#### KnowledgeCategory
**Файл:** `models/KnowledgeCategory.php`
**Назначение:** Категории статей
**Связи:** KnowledgeArticle
**Поля:** name, slug, description, sort_order

#### SmsTemplate
**Файл:** `models/SmsTemplate.php`
**Назначение:** Шаблоны SMS и WhatsApp
**Коды SMS (7):** welcome, payment_reminder, lesson_reminder, birthday, missed_lesson, balance_low, custom
**Коды WhatsApp (5):** whatsapp_greeting, whatsapp_followup, whatsapp_reminder, whatsapp_trial, whatsapp_custom
**Методы:**
- `render()` - подстановка плейсхолдеров
- `createDefaults()` - стандартные SMS шаблоны
- `createWhatsAppDefaults()` - стандартные WhatsApp шаблоны

#### SmsLog
**Файл:** `models/SmsLog.php`
**Назначение:** Логи отправленных SMS
**Поля:** phone, message, status, template_code, created_at

---

### Модели форм (8)

| Форма | Файл | Назначение |
|-------|------|------------|
| LoginForm | `models/LoginForm.php` | Форма входа |
| ContactForm | `models/ContactForm.php` | Форма обратной связи |
| AttendancesForm | `models/forms/AttendancesForm.php` | Отметка посещаемости |
| EducationForm | `models/forms/EducationForm.php` | Форма образования |
| PaymentForm | `models/forms/PaymentForm.php` | Форма платежа |
| TeacherForm | `models/forms/TeacherForm.php` | Форма преподавателя |
| OrganizationRegistrationForm | `models/forms/OrganizationRegistrationForm.php` | Регистрация организации |
| TypicalLessonForm | `models/forms/TypicalLessonForm.php` | Форма типового урока |

---

### Модели поиска (8)

| Модель | Файл | Назначение |
|--------|------|------------|
| PupilSearch | `models/search/PupilSearch.php` | Поиск учеников |
| GroupSearch | `models/search/GroupSearch.php` | Поиск групп |
| PaymentSearch | `models/search/PaymentSearch.php` | Поиск платежей |
| UserSearch | `models/search/UserSearch.php` | Поиск пользователей |
| LidsSearch | `models/search/LidsSearch.php` | Поиск лидов |
| TeacherRateSearch | `models/search/TeacherRateSearch.php` | Поиск ставок |
| TeacherSalarySearch | `models/search/TeacherSalarySearch.php` | Поиск зарплат |
| DateSearch | `models/search/DateSearch.php` | Поиск по датам |

---

### Модели сервисов (7)

| Сервис | Файл | Методы |
|--------|------|--------|
| PupilService | `models/services/PupilService.php` | `updateBalance()` |
| LidService | `models/services/LidService.php` | Операции с лидами |
| ScheduleService | `models/services/ScheduleService.php` | Операции с расписанием |
| ScheduleTemplateService | `models/services/ScheduleTemplateService.php` | Работа с шаблонами |
| ScheduleConflictService | `models/services/ScheduleConflictService.php` | Проверка конфликтов |
| TariffService | `models/services/TariffService.php` | Операции с тарифами |
| TeacherService | `models/services/TeacherService.php` | Операции с преподавателями |

---

### Прочие модели

| Модель | Файл | Назначение |
|--------|------|------------|
| Organizations | `models/Organizations.php` | Организации-клиенты |
| LessonAttendance | `models/LessonAttendance.php` | Посещаемость уроков |
| StatusEnum | `models/enum/StatusEnum.php` | Перечисление статусов |
| LidsSubjectPoint | `models/LidsSubjectPoint.php` | Баллы предметов лидов |

---

## Контроллеры

### Основные контроллеры (app\controllers)

#### SiteController
**Файл:** `controllers/SiteController.php`
**Экшены:**
- `actionIndex` - редирект на CRM или Landing
- `actionLogin` - форма входа
- `actionLogout` - выход
- `actionContact` - контактная форма
- `actionAbout` - страница о системе
- `actionChangeRole` - смена роли пользователя

#### LandingController
**Файл:** `controllers/LandingController.php`
**Экшены:**
- `actionIndex` - главная страница
- `actionPricing` - тарифы
- `actionFeatures` - возможности
- `actionContact` - контакты

#### RegistrationController
**Файл:** `controllers/RegistrationController.php`
**Экшены:**
- `actionIndex` - форма регистрации
- `actionSuccess` - успешная регистрация
- `actionVerifyEmail` - подтверждение email

---

### CRM модуль (22 контроллера)

#### CrmBaseController (базовый)
**Файл:** `modules/crm/controllers/CrmBaseController.php`
**Назначение:** Базовый класс для CRM контроллеров
**Методы:**
- `jsonSuccess()`, `jsonError()` - JSON ответы
- `setFlashSuccess()`, `setFlashError()`, `setFlashWarning()` - flash сообщения
- `isAjaxPost()`, `isAjax()` - проверка типа запроса

#### DefaultController (Dashboard)
**Файл:** `modules/crm/controllers/DefaultController.php`
**Экшены:**
- `actionIndex` - главная страница CRM
- `actionDemo` - демо-дашборд Tailwind

#### PupilController
**Файл:** `modules/crm/controllers/PupilController.php`
**Экшены (9):**
- `actionIndex`, `actionView`, `actionCreate`, `actionUpdate`, `actionDelete`
- `actionEdu` - список обучения
- `actionCreateEdu`, `actionUpdateEdu`, `actionCopyEdu` - управление обучением
- `actionDeleteEdu` - удаление обучения
- `actionPayment` - история платежей
- `actionCreatePayment` - добавление платежа

#### GroupController
**Файл:** `modules/crm/controllers/GroupController.php`
**Экшены (8):**
- CRUD: `actionIndex`, `actionView`, `actionCreate`, `actionUpdate`, `actionDelete`
- `actionTeachers` - преподаватели группы
- `actionPupils` - ученики группы
- `actionDeleteTeacher`, `actionCreateTeacher` - управление преподавателями

#### LidsController (самый большой - 800+ строк)
**Файл:** `modules/crm/controllers/LidsController.php`
**Экшены (19):**
- Views: `actionIndex`, `actionKanban`, `actionAnalytics`
- CRUD: `actionView`, `actionCreate`, `actionUpdate`, `actionDelete`
- Status: `actionChangeStatus`, `actionAddInteraction`
- Convert: `actionConvertToPupil`
- AJAX: `actionCreateAjax`, `actionUpdateAjax`, `actionGetLid`
- Tags: `actionToggleTag`, `actionUpdateField`
- WhatsApp: `actionGetWhatsappTemplates`, `actionRenderWhatsappMessage`
- Duplicates: `actionCheckDuplicates`
- Scripts: `actionGetSalesScript`, `actionGetAllSalesScripts`

#### ScheduleController
**Файл:** `modules/crm/controllers/ScheduleController.php`
**Экшены (20):**
- View: `actionIndex`, `actionEvents`, `actionFilters`, `actionDetails`
- AJAX CRUD: `actionAjaxCreate`, `actionAjaxUpdate`, `actionAjaxDelete`
- `actionMove` - перемещение занятия
- Data: `actionTeachers`, `actionRooms`
- Conflicts: `actionCheckConflicts`
- Standard: `actionView`, `actionCreate`, `actionUpdate`, `actionDelete`
- Typical: `actionTypicalSchedule`, `actionTypicalEvents`, `actionTypicalPreview`, `actionTypicalGenerate`
- Settings: `actionSettings`, `actionSaveSettings`

#### Остальные контроллеры

| Контроллер | Файл | Особенности |
|------------|------|-------------|
| UserController | `modules/crm/controllers/UserController.php` | CRUD сотрудников |
| PaymentController | `modules/crm/controllers/PaymentController.php` | CRUD платежей |
| AttendanceController | `modules/crm/controllers/AttendanceController.php` | Посещаемость |
| ReportsController | `modules/crm/controllers/ReportsController.php` | Отчеты (day, month, employer) |
| SubjectController | `modules/crm/controllers/SubjectController.php` | CRUD предметов |
| TariffController | `modules/crm/controllers/TariffController.php` | CRUD тарифов |
| PayMethodController | `modules/crm/controllers/PayMethodController.php` | CRUD методов оплаты |
| RoomController | `modules/crm/controllers/RoomController.php` | CRUD кабинетов |
| SalaryController | `modules/crm/controllers/SalaryController.php` | Зарплаты (calculate, approve, pay, rates) |
| TypicalScheduleController | `modules/crm/controllers/TypicalScheduleController.php` | Типовое расписание |
| ScheduleTemplateController | `modules/crm/controllers/ScheduleTemplateController.php` | Шаблоны расписаний |
| SmsController | `modules/crm/controllers/SmsController.php` | SMS/WhatsApp шаблоны |
| LidTagController | `modules/crm/controllers/LidTagController.php` | AJAX-only: теги лидов |
| NotificationController | `modules/crm/controllers/NotificationController.php` | Уведомления |
| KnowledgeController | `modules/crm/controllers/KnowledgeController.php` | База знаний |
| SalesScriptController | `modules/crm/controllers/SalesScriptController.php` | Скрипты продаж |

---

## Виджеты

### Tailwind виджеты (активные)

#### Icon
**Файл:** `widgets/tailwind/Icon.php` (282 строки)
**Назначение:** Центральное хранилище SVG иконок (170+)
**Использование:** 50+ файлов
**Методы:**
```php
Icon::show('user')                  // Обычная иконка
Icon::show('user', 'w-6 h-6')       // С размером
Icon::svg('check', 'w-4 h-4')       // Только SVG
Icon::exists('user')                // Проверка существования
Icon::getAvailableIcons()           // Список всех иконок
```

#### StatusBadge
**Файл:** `widgets/tailwind/StatusBadge.php` (295 строк)
**Назначение:** Универсальный бейдж для статусов
**Использование:** 50+ файлов
**Методы:**
```php
StatusBadge::show('active', 'Активный', 'success')
StatusBadge::dot('error')
StatusBadge::boolean(true)
StatusBadge::count(5, 'info')
StatusBadge::buttons(['view' => [...], 'edit' => [...]])
```

#### Modal
**Файл:** `widgets/tailwind/Modal.php` (209 строк)
**Назначение:** Модальное окно с Alpine.js
**Размеры:** sm, md, lg, xl, full
**Методы:**
```php
Modal::begin(['id' => 'myModal', 'title' => 'Заголовок', 'size' => 'lg']);
// ... содержимое ...
Modal::end();
Modal::openButton('myModal', 'Открыть', ['class' => 'btn']);
Modal::closeButton('Закрыть');
```

#### EmptyState
**Файл:** `widgets/tailwind/EmptyState.php` (267 строк)
**Назначение:** Пустые состояния
**Методы:**
```php
EmptyState::compact('user', 'Нет данных')
EmptyState::tableRow(5, 'Нет записей')
EmptyState::card('folder', 'Папка пуста', 'Добавьте файлы')
EmptyState::noData(), EmptyState::noResults(), EmptyState::noAccess(), EmptyState::error()
```

#### Alert
**Файл:** `widgets/tailwind/Alert.php`
**Назначение:** Уведомления (success, error, warning, info)
**Методы:**
```php
Alert::success('Успешно сохранено')
Alert::error('Ошибка')
Alert::warning('Внимание')
Alert::info('Информация')
Alert::dismissible('success', 'Можно закрыть')
```

#### ConfirmModal
**Файл:** `widgets/tailwind/ConfirmModal.php` (137 строк)
**Назначение:** Модальное окно подтверждения
**Типы:** danger, warning, info

#### ActiveForm / ActiveField
**Файлы:** `widgets/tailwind/ActiveForm.php`, `widgets/tailwind/ActiveField.php`
**Назначение:** Tailwind стили для форм Yii2

#### LinkPager
**Файл:** `widgets/tailwind/LinkPager.php` (130 строк)
**Назначение:** Пагинация в стиле Tailwind

#### Breadcrumbs
**Файл:** `widgets/tailwind/Breadcrumbs.php` (78 строк)
**Назначение:** Хлебные крошки

#### SidebarMenu
**Файл:** `widgets/tailwind/SidebarMenu.php`
**Назначение:** Боковое меню с секциями и collapse

#### StatCard
**Файл:** `widgets/tailwind/StatCard.php`
**Назначение:** Карточки статистики с трендами

#### CollapsibleFilter
**Файл:** `widgets/tailwind/CollapsibleFilter.php`
**Назначение:** Сворачиваемые фильтры (localStorage)

#### FormValidation
**Файл:** `widgets/tailwind/FormValidation.php`
**Назначение:** Генерация правил валидации для Alpine.js

---

## Хелперы

#### StatusHelper
**Файл:** `helpers/StatusHelper.php` (241 строк)
**Назначение:** Централизованное управление статусами
**Методы:**
```php
StatusHelper::getColor('pupil', Pupil::STATUS_ACTIVE)     // Цвет
StatusHelper::getLabel('pupil', Pupil::STATUS_ACTIVE)     // Название
StatusHelper::getIcon('pupil', Pupil::STATUS_ACTIVE)      // Иконка
StatusHelper::getBadge('pupil', Pupil::STATUS_ACTIVE)     // Полный бейдж
StatusHelper::getAllStatuses('pupil')                     // Все статусы
```

#### Lists
**Файл:** `helpers/Lists.php` (463 строки) - МОНОЛИТ
**Назначение:** 16+ справочников
**Методы:**
- `getRoles()`, `getOrganizationRoles()` - роли
- `getNationalities()`, `getCitizenshipStatuses()` - география
- `getDaysOfWeek()`, `getMonths()` - время
- `getGrades()`, `getEducationCategories()` - образование
- `getSubjectsForCertificate()` - предметы аттестата
- И другие...

#### MenuHelper
**Файл:** `helpers/MenuHelper.php` (127 строк)
**Назначение:** Построение меню и настройки
**Методы:** `getMenuItems()`, `getSetting()`

#### Common
**Файл:** `helpers/Common.php` (44 строки)
**Назначение:** Общие утилиты
**Методы:** `byLang()` - получение значения по языку

#### OrganizationHelper
**Файл:** `helpers/OrganizationHelper.php` (35 строк)
**Назначение:** Помощник организаций

#### OrganizationUrl
**Файл:** `helpers/OrganizationUrl.php` (20 строк)
**Назначение:** URL с organization_id
**Методы:** `to()` - создание URL

#### StringHelper
**Файл:** `helpers/StringHelper.php` (18 строк)
**Назначение:** Работа со строками

#### SystemRoles / OrganizationRoles
**Файлы:** `helpers/SystemRoles.php`, `helpers/OrganizationRoles.php`
**Назначение:** Константы ролей

---

## Компоненты

#### ActiveRecord
**Файл:** `components/ActiveRecord.php` (88 строк)
**Назначение:** Базовый ActiveRecord
**Особенности:**
- Автозаполнение `organization_id` и `user_id`
- Soft delete через `is_deleted`
- JSON поддержка в `info` поле
- Magic методы: `getFieldByLang()`, `getFieldJson()`

#### ActiveQuery
**Файл:** `components/ActiveQuery.php` (40 строк)
**Назначение:** Базовый Query
**Scopes:**
```php
->notDeleted()      // WHERE is_deleted = 0
->byOrganization()  // WHERE organization_id = current
```

#### BaseController
**Файл:** `components/BaseController.php` (69 строк)
**Назначение:** Базовый контроллер
**Функции:** Инициализация языка и организации

#### PhpManager
**Файл:** `components/PhpManager.php` (28 строк)
**Назначение:** RBAC менеджер (extends PhpManager)

#### Валидаторы
- `PhoneNumberValidator` - валидация телефона (7xxx)
- `LanguageJsonValidator` - валидация мультиязычных полей

---

## Трейты

#### UpdateInsteadOfDeleteTrait
**Файл:** `traits/UpdateInsteadOfDeleteTrait.php` (42 строки)
**Назначение:** Мягкое удаление (soft delete)
**Использование:** 20+ моделей
**Метод:** Переопределяет `delete()` на `update(['is_deleted' => 1])`

#### AttributesToInfoTrait
**Файл:** `traits/AttributesToInfoTrait.php` (70 строк)
**Назначение:** Хранение дополнительных атрибутов в JSON поле `info`
**Использование:** 10+ моделей
**Методы:** `__get()`, `__set()` - magic методы для доступа к атрибутам

---

## Диаграммы связей

### Ученики и образование
```
Pupil (ученик)
  └── PupilEducation (обучение)
        ├── Tariff (тариф)
        │     └── TariffSubject → Subject (предметы)
        └── EducationGroup → Group (группа)
                               ├── TeacherGroup → User (преподаватели)
                               └── Lesson (уроки)
                                     └── LessonAttendance (посещаемость)
```

### Лиды и воронка продаж
```
Lids (лид)
  ├── LidHistory (история взаимодействий)
  ├── LidTagRelation → LidTag (теги)
  ├── User (manager) (менеджер)
  └── SalesScript (скрипты продаж)

Notification (уведомления)
  └── Lids (связанный лид)
```

### Организации и пользователи
```
Organizations (организация)
  ├── UserOrganization → User (пользователи)
  ├── OrganizationSubscription → SaasPlan (подписка)
  ├── OrganizationPayment (платежи в систему)
  └── OrganizationActivityLog (аудит)
```

### Расписание
```
ScheduleTemplate (шаблон расписания)
  └── TypicalSchedule (типовое расписание)
        ├── Group (группа)
        ├── User (teacher) (преподаватель)
        └── Room (кабинет)

Lesson (урок)
  ├── Group (группа)
  ├── User (teacher) (преподаватель)
  ├── Room (кабинет)
  └── LessonAttendance (посещаемость)
```

---

## Конфигурация

### URL правила (`config/web.php`)
```php
'<_o:[\d]+>/<controller:[\w-]+>/<action:[\w-]+>' => '<controller>/<action>',
'<_o:[\d]+>/<controller:[\w-]+>' => '<controller>/index',
'<_o:[\d]+>' => 'site/index',
```

### Компоненты
- **request** - JSON parser, cookie validation
- **i18n** - DbMessageSource (ru/kz/en)
- **cache** - FileCache
- **user** - User identity
- **authManager** - PhpManager (RBAC)
- **mailer** - SwiftMailer
- **formatter** - дата/время форматирование
- **db** - MySQL

---

## Статистика

| Категория | Количество |
|-----------|------------|
| Модели всего | 64 |
| Контроллеры CRM | 22 |
| Виджеты Tailwind | 14 |
| Хелперы | 9 |
| Компоненты | 9 |
| Трейты | 2 |
| Action методов | ~165 |
| View файлов | ~110 |
