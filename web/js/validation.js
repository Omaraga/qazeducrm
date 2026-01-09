/**
 * QazEdu Form Validation System
 * Система клиентской валидации форм на Alpine.js
 *
 * Использование:
 *   <form x-data="formValidation({
 *       first_name: { required: true, minLength: 2 },
 *       email: { required: true, email: true },
 *       phone: { phone: true }
 *   })">
 */

// Правила валидации
const ValidationRules = {
    /**
     * Обязательное поле
     */
    required: (value) => {
        if (value === null || value === undefined) return false;
        if (typeof value === 'string') return value.trim().length > 0;
        if (Array.isArray(value)) return value.length > 0;
        return true;
    },

    /**
     * Email адрес
     */
    email: (value) => {
        if (!value) return true;
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(value);
    },

    /**
     * Телефон (Казахстан: +7 XXX XXX XX XX)
     */
    phone: (value) => {
        if (!value) return true;
        const cleaned = value.replace(/[\s\-\(\)\+]/g, '');
        const normalized = cleaned.replace(/^8/, '7');
        return /^7\d{10}$/.test(normalized);
    },

    /**
     * ИИН (12 цифр)
     */
    iin: (value) => {
        if (!value) return true;
        return /^\d{12}$/.test(value);
    },

    /**
     * Минимальная длина
     */
    minLength: (value, min) => {
        if (!value) return true;
        return String(value).length >= min;
    },

    /**
     * Максимальная длина
     */
    maxLength: (value, max) => {
        if (!value) return true;
        return String(value).length <= max;
    },

    /**
     * Минимальное значение (число)
     */
    min: (value, min) => {
        if (value === '' || value === null || value === undefined) return true;
        return parseFloat(value) >= min;
    },

    /**
     * Максимальное значение (число)
     */
    max: (value, max) => {
        if (value === '' || value === null || value === undefined) return true;
        return parseFloat(value) <= max;
    },

    /**
     * Целое число
     */
    integer: (value) => {
        if (value === '' || value === null || value === undefined) return true;
        return Number.isInteger(parseFloat(value)) && !isNaN(value);
    },

    /**
     * Число (дробное или целое)
     */
    number: (value) => {
        if (value === '' || value === null || value === undefined) return true;
        return !isNaN(parseFloat(value)) && isFinite(value);
    },

    /**
     * Дата
     */
    date: (value) => {
        if (!value) return true;
        const date = new Date(value);
        return date instanceof Date && !isNaN(date);
    },

    /**
     * Совпадение с другим полем
     */
    match: (value, fieldId) => {
        const otherField = document.getElementById(fieldId);
        if (!otherField) return true;
        return value === otherField.value;
    },

    /**
     * Регулярное выражение
     */
    pattern: (value, regex) => {
        if (!value) return true;
        const re = typeof regex === 'string' ? new RegExp(regex) : regex;
        return re.test(value);
    }
};

// Сообщения об ошибках (русский язык)
const ValidationMessages = {
    required: 'Это поле обязательно для заполнения',
    email: 'Введите корректный email адрес',
    phone: 'Введите корректный номер телефона',
    iin: 'ИИН должен содержать 12 цифр',
    minLength: (min) => `Минимальная длина: ${min} символов`,
    maxLength: (max) => `Максимальная длина: ${max} символов`,
    min: (min) => `Минимальное значение: ${min}`,
    max: (max) => `Максимальное значение: ${max}`,
    integer: 'Введите целое число',
    number: 'Введите корректное число',
    date: 'Введите корректную дату',
    match: 'Значения не совпадают',
    pattern: 'Неверный формат'
};

/**
 * Alpine.js компонент для валидации форм
 * @param {Object} rules - Правила валидации { fieldName: { rule: param } }
 */
function formValidation(rules = {}) {
    return {
        errors: {},
        touched: {},
        isSubmitting: false,

        /**
         * Инициализация - привязка обработчиков
         */
        init() {
            this.$el.querySelectorAll('input, select, textarea').forEach(input => {
                const fieldName = this.getFieldName(input);
                if (fieldName && rules[fieldName]) {
                    // Валидация при потере фокуса
                    input.addEventListener('blur', () => {
                        this.touched[fieldName] = true;
                        this.validateField(fieldName, input.value);
                    });

                    // Валидация при вводе (только если поле уже было touched)
                    input.addEventListener('input', () => {
                        if (this.touched[fieldName]) {
                            this.validateField(fieldName, input.value);
                        }
                    });
                }
            });
        },

        /**
         * Получить имя поля из атрибута name
         * Поддерживает форматы: "field" и "Model[field]"
         */
        getFieldName(input) {
            if (!input.name) return null;
            const match = input.name.match(/\[(\w+)\]$/);
            return match ? match[1] : input.name;
        },

        /**
         * Валидация одного поля
         */
        validateField(fieldName, value) {
            this.touched[fieldName] = true;
            const fieldRules = rules[fieldName];

            if (!fieldRules) {
                delete this.errors[fieldName];
                return true;
            }

            for (const [rule, param] of Object.entries(fieldRules)) {
                const validator = ValidationRules[rule];
                if (!validator) continue;

                const isValid = typeof param === 'boolean'
                    ? (param ? validator(value) : true)
                    : validator(value, param);

                if (!isValid) {
                    const message = typeof ValidationMessages[rule] === 'function'
                        ? ValidationMessages[rule](param)
                        : ValidationMessages[rule];
                    this.errors[fieldName] = message;
                    return false;
                }
            }

            delete this.errors[fieldName];
            return true;
        },

        /**
         * Валидация всех полей
         */
        validateAll() {
            let isValid = true;

            this.$el.querySelectorAll('input, select, textarea').forEach(input => {
                const fieldName = this.getFieldName(input);
                if (fieldName && rules[fieldName]) {
                    if (!this.validateField(fieldName, input.value)) {
                        isValid = false;
                    }
                }
            });

            return isValid;
        },

        /**
         * Проверка наличия ошибки
         */
        hasError(fieldName) {
            return !!this.errors[fieldName];
        },

        /**
         * Получить текст ошибки
         */
        getError(fieldName) {
            return this.errors[fieldName] || '';
        },

        /**
         * Обработка отправки формы
         */
        handleSubmit(e) {
            if (!this.validateAll()) {
                e.preventDefault();

                // Фокус на первое поле с ошибкой
                const firstErrorField = Object.keys(this.errors)[0];
                if (firstErrorField) {
                    const input = this.$el.querySelector(
                        `[name="${firstErrorField}"], [name*="[${firstErrorField}]"]`
                    );
                    if (input) {
                        input.focus();
                        input.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                }

                // Показать toast уведомление
                if (window.QazToast) {
                    QazToast.error('Пожалуйста, исправьте ошибки в форме');
                }

                return false;
            }

            this.isSubmitting = true;
            return true;
        },

        /**
         * Получить CSS класс для поля
         */
        inputClass(fieldName) {
            if (this.errors[fieldName]) {
                return 'form-input-error';
            }
            if (this.touched[fieldName] && !this.errors[fieldName]) {
                return 'form-input-success';
            }
            return '';
        },

        /**
         * Сброс формы
         */
        resetValidation() {
            this.errors = {};
            this.touched = {};
            this.isSubmitting = false;
        }
    };
}

// Делаем доступным глобально
window.formValidation = formValidation;
window.ValidationRules = ValidationRules;
window.ValidationMessages = ValidationMessages;

/**
 * Регистрируем Alpine.js компоненты и директивы
 * Используем alpine:init чтобы гарантировать что всё зарегистрировано ДО инициализации DOM
 */
document.addEventListener('alpine:init', () => {
    // Регистрируем formValidation как Alpine.js data компонент
    // Это гарантирует доступность при инициализации x-data="formValidation(...)"
    Alpine.data('formValidation', (rules = {}) => formValidation(rules));

    /**
     * Маска для телефона (Alpine.js директива)
     * Использование: <input x-mask-phone>
     */
    Alpine.directive('mask-phone', (el) => {
        el.addEventListener('input', (e) => {
            let value = e.target.value.replace(/\D/g, '');

            // Убираем начальную 8 и заменяем на 7
            if (value.startsWith('8')) {
                value = '7' + value.substring(1);
            }

            // Если не начинается с 7, добавляем
            if (value.length > 0 && !value.startsWith('7')) {
                value = '7' + value;
            }

            // Форматируем
            let formatted = '';
            if (value.length > 0) {
                formatted = '+' + value.substring(0, 1);
            }
            if (value.length > 1) {
                formatted += ' (' + value.substring(1, 4);
            }
            if (value.length > 4) {
                formatted += ') ' + value.substring(4, 7);
            }
            if (value.length > 7) {
                formatted += '-' + value.substring(7, 9);
            }
            if (value.length > 9) {
                formatted += '-' + value.substring(9, 11);
            }

            e.target.value = formatted;
        });

        // Начальное форматирование
        if (el.value) {
            el.dispatchEvent(new Event('input'));
        }
    });

    /**
     * Маска для ИИН (12 цифр)
     */
    Alpine.directive('mask-iin', (el) => {
        el.addEventListener('input', (e) => {
            let value = e.target.value.replace(/\D/g, '');
            e.target.value = value.substring(0, 12);
        });
    });
});
