/**
 * QazConfirm - система модальных подтверждений с Alpine.js
 *
 * Использование:
 *   QazConfirm.show('Удалить запись?', {
 *       title: 'Подтверждение удаления',
 *       type: 'danger', // danger, warning, info
 *       confirmText: 'Удалить',
 *       cancelText: 'Отмена',
 *       onConfirm: () => { // действие при подтверждении }
 *   });
 */

const QazConfirm = {
    currentCallback: null,

    /**
     * Показать диалог подтверждения
     */
    show(message, options = {}) {
        this.currentCallback = options.onConfirm || null;

        if (window.Alpine && Alpine.store('confirm')) {
            Alpine.store('confirm').show(message, options);
        } else {
            // Fallback на стандартный confirm
            if (confirm(message)) {
                if (this.currentCallback) {
                    this.currentCallback();
                }
            }
        }
    },

    /**
     * Подтвердить действие
     */
    confirm() {
        if (this.currentCallback) {
            this.currentCallback();
        }
        this.hide();
    },

    /**
     * Закрыть диалог
     */
    hide() {
        if (window.Alpine && Alpine.store('confirm')) {
            Alpine.store('confirm').hide();
        }
        this.currentCallback = null;
    }
};

// Alpine.js Store для подтверждений
document.addEventListener('alpine:init', () => {
    Alpine.store('confirm', {
        open: false,
        message: '',
        title: 'Подтверждение',
        type: 'danger',
        confirmText: 'Подтвердить',
        cancelText: 'Отмена',

        show(message, options = {}) {
            this.message = message;
            this.title = options.title || 'Подтверждение';
            this.type = options.type || 'danger';
            this.confirmText = options.confirmText || 'Подтвердить';
            this.cancelText = options.cancelText || 'Отмена';
            this.open = true;
        },

        hide() {
            this.open = false;
        },

        confirm() {
            QazConfirm.confirm();
        }
    });
});

// Глобальный доступ
window.QazConfirm = QazConfirm;
