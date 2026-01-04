/**
 * QazEdu Toast Notification System
 * Система всплывающих уведомлений на Alpine.js
 *
 * Использование:
 *   Alpine.store('toast').success('Успешно сохранено');
 *   Alpine.store('toast').error('Произошла ошибка');
 *   Alpine.store('toast').warning('Внимание!');
 *   Alpine.store('toast').info('Информация');
 *
 * Или через глобальный объект:
 *   QazToast.success('Успешно сохранено');
 *   QazToast.error('Произошла ошибка');
 */

document.addEventListener('alpine:init', () => {
    Alpine.store('toast', {
        items: [],
        counter: 0,

        /**
         * Показать уведомление
         * @param {string} message - Текст сообщения
         * @param {string} type - Тип: 'success' | 'error' | 'warning' | 'info'
         * @param {number} duration - Длительность в мс (по умолчанию 5000)
         * @returns {number} ID уведомления
         */
        show(message, type = 'info', duration = 5000) {
            const id = ++this.counter;
            this.items.push({ id, message, type, visible: true });

            if (duration > 0) {
                setTimeout(() => this.dismiss(id), duration);
            }
            return id;
        },

        /**
         * Показать успешное уведомление
         */
        success(message, duration = 5000) {
            return this.show(message, 'success', duration);
        },

        /**
         * Показать уведомление об ошибке
         */
        error(message, duration = 8000) {
            return this.show(message, 'error', duration);
        },

        /**
         * Показать предупреждение
         */
        warning(message, duration = 6000) {
            return this.show(message, 'warning', duration);
        },

        /**
         * Показать информационное уведомление
         */
        info(message, duration = 5000) {
            return this.show(message, 'info', duration);
        },

        /**
         * Закрыть уведомление по ID
         */
        dismiss(id) {
            const index = this.items.findIndex(item => item.id === id);
            if (index > -1) {
                this.items[index].visible = false;
                // Удаляем из массива после анимации
                setTimeout(() => {
                    this.items = this.items.filter(item => item.id !== id);
                }, 300);
            }
        },

        /**
         * Закрыть все уведомления
         */
        clear() {
            this.items.forEach(item => item.visible = false);
            setTimeout(() => {
                this.items = [];
            }, 300);
        }
    });
});

// Глобальный объект для использования вне Alpine контекста
window.QazToast = {
    show(message, type, duration) {
        if (window.Alpine && Alpine.store('toast')) {
            return Alpine.store('toast').show(message, type, duration);
        }
        // Fallback если Alpine ещё не загружен
        console.warn('QazToast: Alpine.js not initialized yet');
        return null;
    },
    success(message, duration) {
        return this.show(message, 'success', duration);
    },
    error(message, duration) {
        return this.show(message, 'error', duration || 8000);
    },
    warning(message, duration) {
        return this.show(message, 'warning', duration);
    },
    info(message, duration) {
        return this.show(message, 'info', duration);
    }
};

// Автоматическое отображение flash-сообщений из PHP
document.addEventListener('DOMContentLoaded', () => {
    // Ищем скрытые flash-сообщения и показываем как toast
    const flashMessages = document.querySelectorAll('[data-flash-message]');
    flashMessages.forEach(el => {
        const type = el.dataset.flashType || 'info';
        const message = el.dataset.flashMessage;
        if (message) {
            setTimeout(() => {
                QazToast[type] ? QazToast[type](message) : QazToast.info(message);
            }, 100);
        }
        el.remove();
    });
});
