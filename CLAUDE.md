
# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

QazEduCRM is an educational CRM system built on Yii2 Basic template. It manages educational organizations including pupils, teachers, groups, lessons, attendance tracking, payments, and leads.

## Development Commands

```bash
# Install dependencies
composer install

# Run database migrations
php yii migrate

# Run all tests (unit + functional)
vendor/bin/codecept run

# Run specific test suite
vendor/bin/codecept run unit
vendor/bin/codecept run functional

# Run single test file
vendor/bin/codecept run unit tests/unit/models/ContactFormTest.php

# Run with code coverage
vendor/bin/codecept run --coverage --coverage-html --coverage-xml

# Start development server (for tests)
tests/bin/yii serve

# Generate code with Gii (dev only)
# Access at: http://localhost/qazeducrm/web/gii
```

## Architecture

### Multi-Organization Support
- All data is scoped by `organization_id` automatically via `app\components\ActiveRecord`
- URLs include organization ID prefix: `/<oid>/<controller>/<action>`
- Current organization retrieved via `Organizations::getCurrentOrganizationId()`
- Base ActiveRecord auto-populates `organization_id` and `user_id` on new records

### Base Classes (components/)
- **ActiveRecord**: Extends Yii's AR with soft delete (`is_deleted`), JSON `info` field handling, organization scoping
- **ActiveQuery**: Adds `notDeleted()` and `byOrganization()` query scopes
- **BaseController**: Handles language switching (ru/kz/en) and organization context initialization
- **BaseWidget**: Base class for custom widgets

### Model Organization (models/)
- **Core models**: User, Pupil, Group, Subject, Tariff, Payment, Lesson, LessonAttendance
- **models/forms/**: Form models for complex operations (EducationForm, PaymentForm, TariffForm, TeacherForm)
- **models/search/**: DataProvider search models for GridView (PupilSearch, GroupSearch, etc.)
- **models/relations/**: Many-to-many junction tables (UserOrganization, TeacherGroup, EducationGroup, TariffSubject)
- **models/enum/**: Status constants (StatusEnum)
- **models/services/**: Business logic services (PupilService)

### Role-Based Access Control
- System roles in `helpers/SystemRoles`: SUPER, GUEST, PARENT, PUPIL
- Organization roles in `helpers/OrganizationRoles`: GENERAL_DIRECTOR, DIRECTOR, ADMIN, TEACHER, PUPIL, NO_ROLE
- RBAC config files in `rbac/` (items.php, assignments.php, rules.php)
- Custom PhpManager in `components/PhpManager`

### Traits
- **AttributesToInfoTrait**: Store/retrieve additional attributes in JSON `info` column
- **UpdateInsteadOfDeleteTrait**: Soft delete implementation

### Internationalization
- Default language: Russian (ru-RU)
- Supported: Russian, Kazakh (kk-KZ), English (en-US)
- Uses database message source via yii2-translate-manager
- Translation tables: `language_source`, `language_translate`

### Key Configuration
- **config/web.php**: Web app config with URL rules, i18n, components
- **config/console.php**: Console commands config
- **config/db.php**: Database connection (MySQL)
- **config/test.php**: Test environment config

### Database
- MySQL with migrations in `migrations/`
- Test database configured in `config/test_db.php`
- TimestampBehavior used for `created_at`/`updated_at` fields
