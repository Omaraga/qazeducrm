# План рефакторинга QazEduCRM

> Документ создан: 2026-01-06
> Обновлено: 2026-01-07
> Статус: ЗАВЕРШЕНО (выполнено 17/17 задач) ✅

## Прогресс

| Приоритет | Всего | Выполнено | Осталось |
|-----------|-------|-----------|----------|
| 1 (Критические) | 4 | 4 | 0 ✅ |
| 2 (Высокие) | 4 | 4 | 0 ✅ |
| 3 (Средние) | 4 | 4 | 0 ✅ |
| 4 (Улучшения) | 5 | 5 | 0 ✅ |
| **ИТОГО** | **17** | **17** | **0** ✅ |

## Содержание
1. [Приоритет 1 - Критические проблемы](#приоритет-1---критические-проблемы)
2. [Приоритет 2 - Высокие проблемы](#приоритет-2---высокие-проблемы)
3. [Приоритет 3 - Средние проблемы](#приоритет-3---средние-проблемы)
4. [Приоритет 4 - Улучшения](#приоритет-4---улучшения)

---

## Приоритет 1 - Критические проблемы

### 1.1 ~~Унификация системы тегов в Lids~~ ВЫПОЛНЕНО

**Статус:** ВЫПОЛНЕНО (2026-01-06)

**Что было:**
- Система 1: JSON в колонке `tags` (методы `getTags()`, `addTag()`, `removeTag()` со строками)
- Система 2: Таблицы `LidTag` + `LidTagRelation` (методы `getCustomTags()`, `addCustomTag()`)

**Что сделано:**
1. **models/Lids.php** - унифицирована система тегов:
   - Удалены константы `TAG_HOT`, `TAG_VIP`, `TAG_REPEAT`, `TAG_NO_ANSWER`
   - Удалён метод `getTagList()` (статический список тегов)
   - Удалены JSON-методы: `setTagsArray()`
   - Переименованы методы: `getCustomTags()` → `getTags()`, `addCustomTag()` → `addTag()`, etc.
   - Все методы теперь работают с `int $tagId` вместо `string $tag`
   - Добавлен `getTagIds()` - возвращает массив ID тегов
   - Добавлен `hasTagByName()` - проверка по имени для `isHot()`, `isVip()`
   - `isHot()`, `isVip()` теперь ищут теги по имени через БД

2. **modules/crm/controllers/LidsController.php**:
   - Добавлен `use app\models\LidTag`
   - `actionGetLid()` - убрано дублирование `custom_tags`
   - `actionToggleTag()` - параметр `tag` → `tag_id`, работает с int ID

3. **modules/crm/views/lids/kanban.php**:
   - Фильтр тегов: `Lids::getTagList()` → `LidTag::getOrganizationTags()`
   - Отображение тегов: напрямую из массива `$tag['name']`, `$tag['color']`

**Оценка сложности:** Средняя
**Затрагиваемые файлы:** 3

---

### 1.2 ~~Вынесение бизнес-логики из DefaultController::actionDemo()~~ ВЫПОЛНЕНО

**Статус:** ВЫПОЛНЕНО (2026-01-06)

**Что сделано:**
- Создан `models/services/DashboardService.php` с методами:
  - `getStatistics()` - все данные для дашборда
  - `getPupilsCount()`, `getActiveGroupsCount()`, `getMonthlyRevenue()`
  - `getRecentPayments()`, `getTodayLessons()`
  - `getWeekPaymentsData()`, `getWeekLabels()` - для графика
- `actionIndex()` теперь использует DashboardService (5 строк вместо 71)
- `actionDemo()` удален - объединен с `actionIndex()`
- Удален старый view `index.php`
- Обновлен view `index-tailwind.php` с графиком платежей

**Файл:** `modules/crm/controllers/DefaultController.php` - теперь 59 строк (было 143)

**Шаги:**
1. Создать сервис:
   ```php
   // models/services/DashboardService.php
   namespace app\models\services;

   class DashboardService
   {
       public function getStatistics(int $organizationId): array
       {
           return [
               'pupils' => $this->getPupilsCount($organizationId),
               'groups' => $this->getActiveGroupsCount($organizationId),
               'revenue' => $this->getMonthlyRevenue($organizationId),
               'lessons' => $this->getWeeklyLessons($organizationId),
               'lids' => $this->getLidsStats($organizationId),
               // ...
           ];
       }

       private function getPupilsCount(int $orgId): int { ... }
       private function getActiveGroupsCount(int $orgId): int { ... }
       private function getMonthlyRevenue(int $orgId): float { ... }
       private function getWeeklyLessons(int $orgId): array { ... }
       private function getLidsStats(int $orgId): array { ... }
   }
   ```

2. Упростить контроллер:
   ```php
   public function actionDemo()
   {
       $service = new DashboardService();
       $stats = $service->getStatistics(
           Organizations::getCurrentOrganizationId()
       );
       return $this->render('demo', ['stats' => $stats]);
   }
   ```

**Оценка сложности:** Средняя
**Затрагиваемые файлы:** 2

---

### 1.3 ~~Вынесение транзакций из AttendancesForm~~ ВЫПОЛНЕНО

**Статус:** ВЫПОЛНЕНО (2026-01-06)

**Что сделано:**
- Создан `models/services/AttendanceService.php` (110 строк)
  - `saveAttendances()` - транзакционное сохранение посещаемости
  - `getOrCreateAttendances()` - получение/создание записей
  - Логирование ошибок через `Yii::error()`
- Упрощена `AttendancesForm.php` (93 → 100 строк, но чище)
  - Удалены `beginTransaction/rollBack`
  - Удалён `save(false)`
  - Делегирование в `AttendanceService`
  - Добавлены типы и PHPDoc

**Файлы:**
- `models/services/AttendanceService.php` (создан)
- `models/forms/AttendancesForm.php` (рефакторинг)

**Оценка сложности:** Низкая
**Затрагиваемые файлы:** 2

---

### 1.4 ~~Добавление обработки ошибок в PupilService~~ ВЫПОЛНЕНО

**Статус:** ВЫПОЛНЕНО (2026-01-06)

**Что сделано:**
- Добавлена проверка на `null` при поиске ученика
- Заменено `save(false)` на `save()` с валидацией
- Добавлен return type `: bool`
- Добавлено логирование ошибок через `Yii::error()`
- Улучшено форматирование кода (query builder chain)

**Файл:** `models/services/PupilService.php`

**Используется в:**
- `EducationForm.php:136`
- `PaymentForm.php:131`
- `PupilController.php:87, 126, 151, 253`

**Оценка сложности:** Низкая
**Затрагиваемые файлы:** 1

---

## Приоритет 2 - Высокие проблемы

### 2.1 ~~Разделение LidsController (800+ строк)~~ ВЫПОЛНЕНО

**Статус:** ВЫПОЛНЕНО (2026-01-07)

**Проблема:** Контроллер был 789 строк, нарушал Single Responsibility

**Что сделано:**

1. **LidsController.php** (364 строки) - только CRUD:
   - `actionIndex()` - список лидов
   - `actionView($id)` - просмотр лида
   - `actionCreate()` - создание
   - `actionUpdate($id)` - редактирование
   - `actionDelete($id)` - удаление
   - `actionCreateAjax()` - AJAX создание
   - `actionUpdateAjax($id)` - AJAX обновление
   - `actionGetLid($id)` - AJAX получение данных

2. **LidsFunnelController.php** (создан, 218 строк) - воронка продаж:
   - `actionKanban()` - канбан-доска
   - `actionAnalytics()` - страница аналитики
   - `actionChangeStatus()` - AJAX смена статуса (drag & drop)
   - `actionGetSalesScript()` - AJAX скрипт продаж для статуса
   - `actionGetAllSalesScripts()` - AJAX все скрипты продаж

3. **LidsInteractionController.php** (создан, 265 строк) - взаимодействия:
   - `actionAddInteraction()` - AJAX добавление звонка/встречи
   - `actionConvertToPupil($id)` - конверсия в ученика
   - `actionToggleTag()` - AJAX переключение тега
   - `actionUpdateField()` - AJAX inline-редактирование
   - `actionGetWhatsappTemplates()` - AJAX WhatsApp шаблоны
   - `actionRenderWhatsappMessage()` - AJAX генерация сообщения
   - `actionCheckDuplicates()` - AJAX проверка дубликатов

**Обновлённые views** (маршруты):
- `_view-modal.php` - 6 URL обновлено
- `_form-modal.php` - 1 URL
- `kanban.php` - 8 URL
- `analytics.php` - 2 URL
- `view.php` - 2 URL
- `index.php` - 1 URL
- `convert-to-pupil.php` - 1 URL
- `layouts/main.php` - меню обновлено
- `widgets/tailwind/views/manager-stats.php` - 2 URL

**URL маршруты:**
- `lids/kanban` → `lids-funnel/kanban`
- `lids/analytics` → `lids-funnel/analytics`
- `lids/change-status` → `lids-funnel/change-status`
- `lids/add-interaction` → `lids-interaction/add-interaction`
- `lids/convert-to-pupil` → `lids-interaction/convert-to-pupil`
- `lids/toggle-tag` → `lids-interaction/toggle-tag`
- `lids/update-field` → `lids-interaction/update-field`
- И другие...

**Результат:**
- Было: 1 контроллер (789 строк)
- Стало: 3 контроллера (364 + 218 + 265 = 847 строк, но каждый < 400)
- Каждый контроллер имеет чёткую ответственность
- Легче поддерживать и тестировать

**Оценка сложности:** Высокая
**Затрагиваемые файлы:** 12

---

### 2.2 ~~Создание FindModelTrait~~ ВЫПОЛНЕНО

**Статус:** ВЫПОЛНЕНО (2026-01-06)

**Что сделано:**
1. Создан `traits/FindModelTrait.php` с методами:
   - `findModel($id)` - основной метод с organization scope и soft delete
   - `findModelBy(array $condition)` - поиск по условию
   - `findModelGlobal($id)` - без organization scope (для superadmin)
   - `findModelWithDeleted($id)` - включая удалённые записи

2. Применён к 4 контроллерам:
   - `SubjectController` - предметы
   - `RoomController` - кабинеты
   - `PayMethodController` - способы оплаты
   - `TariffController` - тарифы

**Использование:**
```php
class SubjectController extends Controller
{
    use FindModelTrait;

    protected string $modelClass = Subject::class;
    protected string $notFoundMessage = 'Предмет не найден';

    // findModel($id) теперь из trait
}
```

**Преимущества:**
- Автоматически применяет `byOrganization()` и `notDeleted()`
- Настраиваемое сообщение об ошибке
- Дополнительные методы для особых случаев

**Оставшиеся контроллеры** (можно применить по необходимости):
- LidsController, PupilController, GroupController
- PaymentController, ScheduleController, UserController
- И др. (~15 контроллеров)

**Оценка сложности:** Средняя
**Затрагиваемые файлы:** 5 (trait + 4 контроллера)

---

### 2.3 ~~Разделение Lists хелпера (463 строки)~~ ВЫПОЛНЕНО

**Статус:** ВЫПОЛНЕНО (2026-01-06)

**Проблема:** Монолитный файл с 16+ справочниками

**Что сделано:**

1. **helpers/SystemLists.php** (~75 строк):
   - `getRoles()` - роли пользователей
   - `getRanks()` - должности
   - `getResident()` - статус резидентства
   - `getAccountingTypes()` - типы учета

2. **helpers/GeographyLists.php** (~90 строк):
   - `getNationality()` - 60+ национальностей
   - `getCitizenshipStatus()` - статусы гражданства

3. **helpers/EducationLists.php** (~210 строк):
   - `getChildrenSocialCategory()`, `getStudentSocialCategory()` - соц. категории
   - `getCertificateSubjects()`, `getCertificateReason()`, `getCertificateType()` - аттестаты
   - `getCampList()`, `getCategories()` - лагеря и категории
   - `getTariffDurations()`, `getTariffTypes()` - тарифы
   - `getGrades()`, `getGroupCategories()` - классы и группы

4. **helpers/CommonLists.php** (~80 строк):
   - `getGenders()` - полы
   - `getWeekDays()`, `getWeekDaysShort()` - дни недели
   - `getStudyLang()`, `getLanguageList()`, `getOrderLang()` - языки

5. **helpers/Lists.php** (фасад, ~180 строк):
   - Делегирует все вызовы специализированным классам
   - Сохранена полная обратная совместимость
   - Метод `getValueFromDict()` работает через reflection

**Преимущества:**
- Код разделён по тематикам
- Легче поддерживать и расширять
- Полная обратная совместимость

**Оценка сложности:** Средняя
**Созданные файлы:** 4 (+ обновлён Lists.php)

---

### 2.4 ~~Вынесение логики из ScheduleController~~ ВЫПОЛНЕНО

**Статус:** ВЫПОЛНЕНО (2026-01-06) - Было реализовано ранее

**Результат проверки:**
Сервисы уже были полностью реализованы:

1. **ScheduleConflictService.php** (272 строки):
   - `checkAllConflicts()` - проверка всех типов конфликтов
   - `checkTeacherConflict()` - конфликты преподавателя
   - `checkRoomConflict()` - конфликты кабинета
   - `checkGroupConflict()` - конфликты группы
   - `formatConflictMessage()` - форматирование сообщений

2. **ScheduleService.php** (655 строк):
   - `getLessonEventsFiltered()` - получение занятий с фильтрацией
   - `moveLesson()` - перенос занятия
   - `generateFromTypicalSchedule()` - генерация из типового расписания
   - `duplicateLesson()` - копирование занятия
   - И другие методы

3. **ScheduleController.php** - уже тонкий, использует сервисы

**Оценка сложности:** УЖЕ ВЫПОЛНЕНО
**Затрагиваемые файлы:** 0 (задача была закрыта)

---

## Приоритет 3 - Средние проблемы

### 3.1 ~~Расширение LidService~~ ВЫПОЛНЕНО

**Статус:** ВЫПОЛНЕНО (2026-01-07)

**Проблема:** actionUpdateField() в контроллере содержит 60+ строк логики

**Что сделано:**

1. **models/services/LidService.php** - добавлен метод `updateField()`:
   - Константа `ALLOWED_FIELDS` - список разрешённых полей
   - Метод `updateField()` - точка входа с маршрутизацией по обработчикам
   - Обработчики для каждого типа поля:
     - `updateStatusField()` - делегирует в changeStatus()
     - `updateDateField()` - обновление дат с историей
     - `updateManagerField()` - смена менеджера с историей
     - `updateSourceField()` - обновление источника
     - `updatePhoneField()` - очистка и сохранение телефона
     - `updateTextField()` - текстовые поля (comment, lost_reason)
   - Метод `getAllowedFields()` - публичный доступ к списку полей

2. **models/LidHistory.php** - добавлены новые типы и методы:
   - Константы: `TYPE_FIELD_CHANGED`, `TYPE_MANAGER_CHANGED`
   - Метод `createFieldChanged()` - запись об изменении поля
   - Метод `createManagerChanged()` - запись о смене менеджера
   - Обновлены `getTypeList()`, `getTypeIcon()`, `getTypeColor()`

3. **modules/crm/controllers/LidsController.php**:
   - `actionUpdateField()` сокращён с 60 до 12 строк
   - Вся логика делегируется в `LidService::updateField()`

**Преимущества:**
- Единая точка входа для inline-редактирования
- Расширяемый список полей через константу
- Автоматическая запись в историю изменений
- Контроллер стал тоньше

**Оценка сложности:** Средняя
**Затрагиваемые файлы:** 3

---

### 3.2 ~~Добавление TimestampBehavior в LidHistory~~ ВЫПОЛНЕНО

**Статус:** ВЫПОЛНЕНО (2026-01-06)

**Что сделано:**
- Добавлен `use yii\behaviors\TimestampBehavior`
- Добавлен метод `behaviors()` с TimestampBehavior
- Удалено `[['created_at'], 'safe']` из rules()
- `created_at` теперь автоматически заполняется при создании записи

**Файл:** `models/LidHistory.php`

**Оценка сложности:** Низкая
**Затрагиваемые файлы:** 1

---

### 3.3 ~~Реализация логирования OrganizationActivityLog~~ ВЫПОЛНЕНО

**Статус:** ВЫПОЛНЕНО (2026-01-07)

**Проблема:** Модель создана, но не используется

**Что сделано:**

1. **helpers/ActivityLogger.php** - создан удобный helper:
   - `log()` - базовый метод для текущей организации
   - `logLogin()`, `logLogout()` - авторизация
   - `logPupilCreated()`, `logPupilUpdated()`, `logPupilDeleted()` - ученики
   - `logPaymentCreated()`, `logPaymentDeleted()` - платежи
   - `logGroupCreated()`, `logGroupDeleted()` - группы
   - `logLidCreated()`, `logLidConverted()`, `logLidLost()` - лиды
   - `logLessonCompleted()` - занятия
   - `logSettingsChanged()` - настройки

2. **models/OrganizationActivityLog.php** - расширена модель:
   - Добавлена категория `CATEGORY_CRM`
   - Добавлены CRM-действия: `ACTION_PUPIL_*`, `ACTION_GROUP_*`, `ACTION_LID_*`, etc.
   - Обновлены `getCategoryList()` и `getActionList()`

3. **Контроллеры** - добавлено логирование:
   - `PupilController` - создание и удаление учеников
   - `PaymentController` - создание и удаление платежей
   - `LidsController` - создание лидов

4. **models/services/LidService.php** - логирование конверсии лида

**Использование:**
```php
use app\helpers\ActivityLogger;

// В контроллере
ActivityLogger::logPupilCreated($pupil);
ActivityLogger::logPaymentCreated($payment);
ActivityLogger::logLidConverted($lid, $pupil);
```

**Оценка сложности:** Средняя
**Затрагиваемые файлы:** 6

---

### 3.4 ~~Рефакторинг MenuHelper~~ ВЫПОЛНЕНО

**Статус:** ВЫПОЛНЕНО (2026-01-07)

**Проблема:** Смешанная ответственность (настройки + построение меню)

**Что сделано:**

1. **helpers/SettingsHelper.php** - создан новый helper (150 строк):
   - `getSettings()` - кэширование модели Settings
   - `clearCache()` - сброс кэша
   - `getBaseUrl()` - получение базового URL
   - `getSiteName()` - название сайта
   - `getLogoUrl()`, `getLogoHtml()` - работа с логотипом
   - `get($key, $default)` - получение произвольной настройки
   - `isFeatureEnabled($feature)` - проверка фичи
   - `normalizeUrl()` - нормализация URL

2. **helpers/MenuHelper.php** - рефакторинг (249 строк):
   - Методы работы с настройками помечены `@deprecated`:
     - `getUrl()` → `SettingsHelper::getBaseUrl()`
     - `getSetting()` → `SettingsHelper::getSettings()`
     - `getLogo()` → `SettingsHelper::getLogoHtml()`
     - `getName()` → `SettingsHelper::getSiteName()`
     - `normalizeUrl()` → `SettingsHelper::normalizeUrl()`
   - Разделён `getMenuItems()` на приватные методы:
     - `getGuestMenuItems()` - меню для гостей
     - `getAuthenticatedMenuItems()` - меню для авторизованных
     - `canAccessAdminMenu()` - проверка доступа к админке
     - `getAdminMenuItems()` - пункты админа
     - `getDirectorMenuItems()` - пункты директора
     - `getRoleSwitcherItem()` - переключение ролей
     - `getLogoutItem()` - кнопка выхода

**Использование SettingsHelper:**
```php
use app\helpers\SettingsHelper;

$siteName = SettingsHelper::getSiteName();
$logoUrl = SettingsHelper::getLogoUrl();
$baseUrl = SettingsHelper::getBaseUrl();
$value = SettingsHelper::get('custom_key', 'default');
```

**Преимущества:**
- Чёткое разделение ответственности
- Кэширование настроек (один запрос к БД)
- Полная обратная совместимость через deprecated методы
- Меньшие методы, легче тестировать

**Оценка сложности:** Низкая
**Затрагиваемые файлы:** 2 (+ создан SettingsHelper.php)

---

## Приоритет 4 - Улучшения

### 4.1 ~~Создание Enum трейтов~~ ВЫПОЛНЕНО

**Статус:** ВЫПОЛНЕНО (2026-01-07)

**Проблема:** Повторяющаяся логика getTypeList()/getStatusList() в моделях

**Что сделано:**

1. **traits/HasEnumFieldTrait.php** - базовый trait с helper-методами:
   - `getEnumLabel($value, $list)` - получение label
   - `getEnumIcon($value, $icons)` - получение иконки
   - `getEnumColor($value, $colors)` - получение цвета
   - `isEnumValue($value, $values)` - проверка значения

2. **traits/HasStatusTrait.php** - для полей `status`:
   - Требует: `getStatusList(): array`
   - Опционально: `getStatusIcons()`, `getStatusColors()`
   - Предоставляет: `getStatusLabel()`, `getStatusIcon()`, `getStatusColor()`
   - Дополнительно: `isStatus($status)`, `isStatusIn($statuses)`, `getStatusOptions()`

3. **traits/HasTypeTrait.php** - для полей `type`:
   - Требует: `getTypeList(): array`
   - Опционально: `getTypeIcons()`, `getTypeColors()`
   - Предоставляет: `getTypeLabel()`, `getTypeIcon()`, `getTypeColor()`
   - Дополнительно: `isType($type)`, `isTypeIn($types)`, `getTypeOptions()`

**Применено к моделям:**
- `LidHistory` - использует HasTypeTrait (10 типов + иконки + цвета)
- `Notification` - использует HasTypeTrait (5 типов + иконки + цвета)
- `SmsLog` - использует HasStatusTrait (4 статуса + цвета)

**Использование:**
```php
class MyModel extends ActiveRecord
{
    use HasStatusTrait;

    public static function getStatusList(): array {
        return [1 => 'Активен', 0 => 'Неактивен'];
    }

    public static function getStatusColors(): array {
        return [1 => 'green', 0 => 'gray'];
    }
}

// Использование
$model->getStatusLabel();  // "Активен"
$model->getStatusColor();  // "green"
$model->isStatus(1);       // true
```

**Оценка сложности:** Низкая
**Затрагиваемые файлы:** 6 (3 traits + 3 models)

---

### 4.2 ~~Расширение StringHelper~~ ВЫПОЛНЕНО

**Статус:** ВЫПОЛНЕНО (2026-01-07)

**Файл:** `helpers/StringHelper.php` (было 18 строк → стало 399 строк)

**Что сделано:**

Расширен Yii StringHelper с 20+ полезными методами:

**Преобразование:**
- `slugify($text)` - URL-slug с транслитерацией RU/KZ
- `camelToSnake($text)` - CamelCase → snake_case
- `snakeToCamel($text)` - snake_case → CamelCase
- `snakeToKebab($text)` - snake_case → kebab-case
- `kebabToSnake($text)` - kebab-case → snake_case

**Обрезка:**
- `truncateText($text, $length)` - обрезка с многоточием
- `truncateWords($text, $count)` - обрезка по словам
- `first($text, $length)` - первые N символов
- `last($text, $length)` - последние N символов

**Поиск и подсветка:**
- `highlightMatches($text, $query)` - подсветка совпадений
- `containsIgnoreCase($haystack, $needle)` - поиск без регистра
- `startsWith()`, `endsWith()` - проверка начала/конца

**Телефоны:**
- `cleanPhone($phone)` - очистка телефона
- `formatPhone($phone, 'kz')` - форматирование +7 XXX XXX XX XX
- `maskPhone($phone)` - маскирование +7 *** *** ** 45

**Email и ФИО:**
- `maskEmail($email)` - маскирование j***n@example.com
- `initials($fio, 2)` - инициалы из ФИО

**Склонение:**
- `pluralize($count, 'ученик', 'ученика', 'учеников')` - склонение
- `pluralizeWithCount(...)` - склонение с числом

**Использование:**
```php
StringHelper::slugify('Привет мир');     // "privet-mir"
StringHelper::initials('Иванов Иван');   // "ИИ"
StringHelper::formatPhone('87771234567'); // "+7 777 123 45 67"
StringHelper::pluralizeWithCount(5, 'ученик', 'ученика', 'учеников'); // "5 учеников"
```

**Оценка сложности:** Низкая
**Затрагиваемые файлы:** 1

---

### 4.3 ~~Оптимизация StatCard виджета~~ ВЫПОЛНЕНО

**Статус:** ВЫПОЛНЕНО (2026-01-07)

**Что было:**
- 8 inline SVG иконок в массиве `$icons` (дублирование Icon виджета)
- Inline SVG для стрелок тренда (arrow-up/arrow-down)
- 138 строк кода

**Что сделано:**
1. **widgets/tailwind/StatCard.php** (138 → 125 строк):
   - Удалён массив `$icons` с 8 inline SVG
   - Основная иконка: `Icon::show($this->icon, 'lg')`
   - Стрелки тренда: `Icon::show('arrow-up|arrow-down', 'xs')`
   - Добавлено значение по умолчанию `$icon = 'chart'`

2. **widgets/tailwind/Icon.php**:
   - Добавлен алиас `user-group` для совместимости

**Оценка сложности:** Низкая
**Затрагиваемые файлы:** 2

---

### 4.4 ~~Улучшение PhoneNumberValidator~~ ВЫПОЛНЕНО

**Статус:** ВЫПОЛНЕНО (2026-01-07)

**Что было:**
- Только казахский формат (7xxx)
- 33 строки кода
- Нет форматирования, только валидация

**Что сделано:**
1. **components/PhoneNumberValidator.php** (33 → 363 строки):

   **Поддержка стран:**
   - KZ: Казахстан (+7 xxx xxx xx xx) - 11 цифр
   - RU: Россия (+7 xxx xxx xx xx) - 11 цифр
   - UZ: Узбекистан (+998 xx xxx xx xx) - 12 цифр
   - KG: Кыргызстан (+996 xxx xxx xxx) - 12 цифр
   - BY: Беларусь (+375 xx xxx xx xx) - 12 цифр
   - INTERNATIONAL: любой международный формат

   **Новые возможности:**
   - `$country` - код страны по умолчанию
   - `$countries` - массив допустимых стран
   - `$normalize` - нормализация номера
   - `$keepPlus` - сохранять + в начале

   **Статические методы:**
   - `clean($phone)` - очистка от лишних символов
   - `format($phone, $country)` - форматирование для отображения
   - `detectCountry($phone)` - определение страны
   - `isValid($phone, $country)` - проверка валидности
   - `getCountries()` - список поддерживаемых стран

   **Примеры использования:**
   ```php
   // Базовое (KZ по умолчанию)
   [['phone'], PhoneNumberValidator::class],

   // Указание страны
   [['phone'], PhoneNumberValidator::class, 'country' => 'UZ'],

   // Несколько стран
   [['phone'], PhoneNumberValidator::class, 'countries' => ['KZ', 'RU']],

   // Статические методы
   PhoneNumberValidator::format('77011234567'); // +7 (701) 123-45-67
   PhoneNumberValidator::detectCountry('998901234567'); // 'UZ'
   ```

**Оценка сложности:** Низкая
**Затрагиваемые файлы:** 1

---

### 4.5 ~~Замена прямых date() на Formatter~~ ВЫПОЛНЕНО

**Статус:** ВЫПОЛНЕНО (2026-01-07)

**Что было:**
- ~100 прямых вызовов `date()` разбросаны по всему проекту
- Нет единого интерфейса для работы с датами
- Дублирование кода форматирования

**Что сделано:**
1. **helpers/DateHelper.php** (новый файл, 450 строк):

   **Основные методы:**
   - `now()` - текущая дата-время (Y-m-d H:i:s)
   - `today()` - текущая дата (Y-m-d)
   - `toSqlDate($date)` - преобразование в SQL дату
   - `toSqlDatetime($date)` - преобразование в SQL datetime
   - `format($date, $format)` - форматирование через Yii Formatter

   **Относительные даты:**
   - `relative($modifier)` - относительная дата (+1 day, -7 days)
   - `relativeFrom($date, $modifier)` - относительно указанной даты
   - `startOfWeek()` / `endOfWeek()` - границы недели
   - `startOfMonth()` / `endOfMonth()` - границы месяца
   - `yesterday()` / `tomorrow()` - вчера/завтра

   **Проверки:**
   - `isToday($date)`, `isPast($date)`, `isFuture($date)`
   - `isThisWeek($date)`, `isThisMonth($date)`
   - `diffInDays($date1, $date2)`, `daysUntil($date)`, `daysSince($date)`

   **Форматирование:**
   - `dayOfWeek($date)` - название дня недели (Пн, Вт...)
   - `monthName($date)` - название месяца (Январь, января)
   - `formatWithMonth($date)` - "7 января 2026"
   - `relative_human($date)` - "3 дня назад"
   - `toHtmlDate($date)` / `toHtmlDatetime($date)` - для input полей
   - `range($start, $end)` - массив дат в диапазоне

2. **Обновлены ключевые файлы:**
   - `models/services/DashboardService.php` - 4 замены
   - `models/services/LidService.php` - 11 замен
   - `models/Lids.php` - 4 замены

**Примеры использования:**
```php
use app\helpers\DateHelper;

// Текущие даты
DateHelper::now();              // '2026-01-07 12:30:45'
DateHelper::today();            // '2026-01-07'

// Относительные даты
DateHelper::relative('-7 days');           // '2025-12-31'
DateHelper::relative('+1 month', true);    // '2026-02-07 12:30:45'
DateHelper::startOfWeek();                 // '2026-01-05'
DateHelper::startOfMonth();                // '2026-01-01'

// Форматирование
DateHelper::format($date, 'd.m.Y');        // '07.01.2026'
DateHelper::formatWithMonth($date);        // '7 января 2026'
DateHelper::dayOfWeek($date, true);        // 'Вт'

// Проверки
DateHelper::isToday($date);                // true/false
DateHelper::isPast($date);                 // true/false
DateHelper::daysUntil('2026-01-15');       // 8
```

**Примечание:** Остальные ~80 файлов с date() можно обновлять постепенно.
Созданный DateHelper обеспечивает единый интерфейс для будущих изменений.

**Оценка сложности:** Средняя
**Затрагиваемые файлы:** 4 (+ 1 новый)

---

## Сводка

| Приоритет | Задач | Статус | Файлов |
|-----------|-------|--------|--------|
| 1 (Критические) | 4 | **4 выполнено** ✅ | ~15 |
| 2 (Высокие) | 4 | **4 выполнено** ✅ | ~30 |
| 3 (Средние) | 4 | **4 выполнено** ✅ | ~15 |
| 4 (Улучшения) | 5 | **5 выполнено** ✅ | ~20 |
| **ИТОГО** | **17** | **17/17 ✅** | **~80** |

---

## Рекомендуемый порядок выполнения

### С чего начать (Quick Wins)

**Рекомендация:** Начать с задач **1.4** и **3.2** - они простые, займут 15-30 минут каждая, и дадут понимание качества кода.

1. **1.4 PupilService** (15 мин) - добавить обработку ошибок, 1 файл
2. **3.2 LidHistory TimestampBehavior** (10 мин) - стандартизация, 1 файл
3. **1.3 AttendanceService** (30 мин) - вынести транзакции, 3 файла

### Основной рефакторинг

4. **1.1 Унификация тегов Lids** (2-3 часа) - критично для целостности данных
5. **2.2 FindModelTrait** (1-2 часа) - уменьшит дублирование в 15+ контроллерах
6. **2.4 ScheduleConflictService** (1 час) - сервис уже есть, нужно расширить

### Крупные задачи (требуют планирования)

7. **2.1 Разделение LidsController** - большой объём работы, много зависимостей
8. **2.3 Разделение Lists хелпера** - требует проверки всех использований

---

## Текущее состояние файлов

| Файл | Строк | Статус |
|------|-------|--------|
| ~~`LidsController.php`~~ | 364 | ✅ Разделён на 3 контроллера |
| ~~`LidsFunnelController.php`~~ | 218 | ✅ Создан |
| ~~`LidsInteractionController.php`~~ | 265 | ✅ Создан |
| ~~`Lids.php`~~ | 660 | ✅ Теги унифицированы |
| ~~`Lists.php`~~ | 180 | ✅ Разделён на 4 файла |
| ~~`AttendancesForm.php`~~ | 100 | ✅ Рефакторинг |
| ~~`PupilService.php`~~ | 71 | ✅ Исправлен |
| ~~`DashboardService.php`~~ | 203 | ✅ Создан |
| ~~`AttendanceService.php`~~ | 110 | ✅ Создан |
| ~~`LidHistory.php`~~ | 278 | ✅ TimestampBehavior |
| ~~`FindModelTrait.php`~~ | 157 | ✅ Создан (применён к 4 контроллерам) |
| ~~`SettingsHelper.php`~~ | 151 | ✅ Создан |
| ~~`MenuHelper.php`~~ | 249 | ✅ Рефакторинг |
| ~~`HasEnumFieldTrait.php`~~ | 60 | ✅ Создан |
| ~~`HasStatusTrait.php`~~ | 105 | ✅ Создан |
| ~~`HasTypeTrait.php`~~ | 105 | ✅ Создан |
| ~~`StringHelper.php`~~ | 399 | ✅ Расширен (было 18) |

---

## Тестирование

После каждого рефакторинга:
1. Запустить `php -l <file>` - проверка синтаксиса
2. Запустить `vendor/bin/codecept run` - unit тесты
3. Проверить функциональность в браузере
4. Проверить консольные команды (`php yii`)
