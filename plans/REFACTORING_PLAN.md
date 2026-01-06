# План рефакторинга QazEduCRM

> Документ создан: 2026-01-06
> Обновлено: 2026-01-06
> Статус: В работе (выполнено 11/17 задач)

## Прогресс

| Приоритет | Всего | Выполнено | Осталось |
|-----------|-------|-----------|----------|
| 1 (Критические) | 4 | 4 | 0 ✅ |
| 2 (Высокие) | 4 | 3 | 1 |
| 3 (Средние) | 4 | 4 | 0 ✅ |
| 4 (Улучшения) | 5 | 0 | 5 |
| **ИТОГО** | **17** | **11** | **6** |

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

### 2.1 Разделение LidsController (800+ строк)

**Проблема:** Контроллер слишком большой, нарушает Single Responsibility

**Файл:** `modules/crm/controllers/LidsController.php`

**Решение:** Разделить на 3 контроллера:

1. **LidsController** (CRUD операции):
   - actionIndex, actionView, actionCreate, actionUpdate, actionDelete
   - actionCreateAjax, actionUpdateAjax, actionGetLid

2. **LidsFunnelController** (воронка продаж):
   - actionKanban, actionAnalytics
   - actionChangeStatus
   - actionGetSalesScript, actionGetAllSalesScripts

3. **LidsInteractionController** (взаимодействия):
   - actionAddInteraction
   - actionConvertToPupil
   - actionToggleTag, actionUpdateField
   - actionGetWhatsappTemplates, actionRenderWhatsappMessage
   - actionCheckDuplicates

**Шаги:**
1. Создать новые контроллеры
2. Перенести экшены
3. Обновить URL правила в config/web.php
4. Обновить ссылки во views

**Оценка сложности:** Высокая
**Затрагиваемые файлы:** ~15-20

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

### 4.1 Создание BaseEnum трейта

**Проблема:** Повторяющаяся логика getTypeList() в моделях

**Решение:**
```php
// traits/HasStatusTrait.php
trait HasStatusTrait
{
    abstract public static function getStatusList(): array;

    public function getStatusLabel(): string
    {
        return static::getStatusList()[$this->status] ?? 'Unknown';
    }

    public static function getStatusOptions(): array
    {
        return static::getStatusList();
    }
}
```

**Использование в:** LidHistory, Notification, Payment, OrganizationActivityLog

**Оценка сложности:** Низкая
**Затрагиваемые файлы:** 5-6

---

### 4.2 Расширение StringHelper

**Файл:** `helpers/StringHelper.php` (18 строк)

**Решение:** Добавить методы:
```php
class StringHelper
{
    public static function slugify(string $text): string { ... }
    public static function truncate(string $text, int $length): string { ... }
    public static function highlightMatches(string $text, string $query): string { ... }
    public static function camelCaseToSnake(string $text): string { ... }
    public static function snakeToCamelCase(string $text): string { ... }
}
```

**Оценка сложности:** Низкая
**Затрагиваемые файлы:** 1

---

### 4.3 Оптимизация StatCard виджета

**Проблема:** Встроенные иконки дублируют Icon виджет

**Файл:** `widgets/tailwind/StatCard.php`

**Решение:** Использовать `Icon::show()` вместо inline SVG

**Оценка сложности:** Низкая
**Затрагиваемые файлы:** 1

---

### 4.4 Улучшение PhoneNumberValidator

**Проблема:** Поддерживает только казахский формат (7xxx)

**Файл:** `components/PhoneNumberValidator.php`

**Решение:** Добавить поддержку разных форматов:
```php
class PhoneNumberValidator extends Validator
{
    public $country = 'KZ'; // Страна по умолчанию

    private $patterns = [
        'KZ' => '/^7[0-9]{9}$/',           // Казахстан
        'RU' => '/^7[0-9]{10}$/',          // Россия
        'UZ' => '/^998[0-9]{9}$/',         // Узбекистан
        'INTERNATIONAL' => '/^\+[0-9]{10,15}$/', // Международный
    ];

    protected function validateValue($value)
    {
        $pattern = $this->patterns[$this->country] ?? $this->patterns['INTERNATIONAL'];
        // ...
    }
}
```

**Оценка сложности:** Низкая
**Затрагиваемые файлы:** 1

---

### 4.5 Замена прямых date() на Formatter

**Проблема:** Везде используется `date('Y-m-d')` вместо Yii formatter

**Решение:** Заменить на:
```php
// Вместо:
date('Y-m-d')

// Использовать:
Yii::$app->formatter->asDate(time(), 'yyyy-MM-dd')
```

**Затрагиваемые файлы:** 10-15

---

## Сводка

| Приоритет | Задач | Статус | Файлов |
|-----------|-------|--------|--------|
| 1 (Критические) | 4 | **4 выполнено** ✅ | ~15 |
| 2 (Высокие) | 4 | **3 выполнено** | ~30 |
| 3 (Средние) | 4 | **4 выполнено** ✅ | ~15 |
| 4 (Улучшения) | 5 | 0 выполнено | ~15 |
| **ИТОГО** | **17** | **11/17** | **~75** |

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
| `LidsController.php` | 815 | Слишком большой |
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

---

## Тестирование

После каждого рефакторинга:
1. Запустить `php -l <file>` - проверка синтаксиса
2. Запустить `vendor/bin/codecept run` - unit тесты
3. Проверить функциональность в браузере
4. Проверить консольные команды (`php yii`)
