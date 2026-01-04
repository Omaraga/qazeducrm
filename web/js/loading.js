/**
 * Loading States - управление состояниями загрузки
 *
 * Использование:
 *
 * 1. Loading Button (автоматически для форм):
 *    <button type="submit" data-loading-text="Сохранение...">Сохранить</button>
 *
 * 2. Loading Button (вручную):
 *    <button onclick="QazLoading.button(this, true)">Загрузить</button>
 *
 * 3. Page Loader:
 *    QazLoading.page.show('Загрузка данных...');
 *    QazLoading.page.hide();
 *
 * 4. Skeleton для элемента:
 *    QazLoading.skeleton(element);
 *    QazLoading.removeSkeleton(element);
 */

const QazLoading = {
    // Spinner SVG
    spinnerSvg: `<svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
    </svg>`,

    /**
     * Управление состоянием кнопки
     */
    button(btn, loading = true) {
        if (loading) {
            // Сохраняем оригинальное состояние
            btn.dataset.originalContent = btn.innerHTML;
            btn.dataset.originalWidth = btn.style.width || '';

            // Фиксируем ширину
            btn.style.width = btn.offsetWidth + 'px';

            // Получаем текст загрузки
            const loadingText = btn.dataset.loadingText || 'Загрузка...';

            // Заменяем контент
            btn.innerHTML = this.spinnerSvg + ' <span>' + loadingText + '</span>';
            btn.disabled = true;
            btn.classList.add('opacity-75', 'cursor-not-allowed');
        } else {
            // Восстанавливаем оригинальное состояние
            if (btn.dataset.originalContent) {
                btn.innerHTML = btn.dataset.originalContent;
                btn.style.width = btn.dataset.originalWidth;
                delete btn.dataset.originalContent;
                delete btn.dataset.originalWidth;
            }
            btn.disabled = false;
            btn.classList.remove('opacity-75', 'cursor-not-allowed');
        }
    },

    /**
     * Page Loader
     */
    page: {
        element: null,

        create() {
            if (this.element) return;

            this.element = document.createElement('div');
            this.element.id = 'qaz-page-loader';
            this.element.className = 'fixed inset-0 bg-white/80 backdrop-blur-sm z-[9999] flex items-center justify-center transition-opacity duration-300';
            this.element.innerHTML = `
                <div class="text-center">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-primary-100 mb-4">
                        <svg class="animate-spin h-8 w-8 text-primary-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </div>
                    <p class="text-gray-600 font-medium" id="qaz-page-loader-text">Загрузка...</p>
                </div>
            `;
            this.element.style.display = 'none';
            document.body.appendChild(this.element);
        },

        show(text = 'Загрузка...') {
            this.create();
            const textEl = this.element.querySelector('#qaz-page-loader-text');
            if (textEl) textEl.textContent = text;
            this.element.style.display = 'flex';
            this.element.style.opacity = '0';
            requestAnimationFrame(() => {
                this.element.style.opacity = '1';
            });
        },

        hide() {
            if (!this.element) return;
            this.element.style.opacity = '0';
            setTimeout(() => {
                this.element.style.display = 'none';
            }, 300);
        },

        setText(text) {
            if (!this.element) return;
            const textEl = this.element.querySelector('#qaz-page-loader-text');
            if (textEl) textEl.textContent = text;
        }
    },

    /**
     * Skeleton loading для элемента
     */
    skeleton(element) {
        element.dataset.originalContent = element.innerHTML;
        element.classList.add('animate-pulse');
        element.innerHTML = `
            <div class="space-y-3">
                <div class="h-4 bg-gray-200 rounded w-3/4"></div>
                <div class="h-4 bg-gray-200 rounded w-1/2"></div>
                <div class="h-4 bg-gray-200 rounded w-5/6"></div>
            </div>
        `;
    },

    removeSkeleton(element) {
        element.classList.remove('animate-pulse');
        if (element.dataset.originalContent) {
            element.innerHTML = element.dataset.originalContent;
            delete element.dataset.originalContent;
        }
    },

    /**
     * Table skeleton
     */
    tableSkeleton(tbody, rows = 5, cols = 5) {
        let html = '';
        for (let i = 0; i < rows; i++) {
            html += '<tr class="animate-pulse">';
            for (let j = 0; j < cols; j++) {
                const width = 40 + Math.random() * 40; // 40-80%
                html += `<td class="px-6 py-4"><div class="h-4 bg-gray-200 rounded" style="width: ${width}%"></div></td>`;
            }
            html += '</tr>';
        }
        tbody.innerHTML = html;
    },

    /**
     * Инициализация автоматических обработчиков
     */
    init() {
        // Автоматическое состояние загрузки для форм
        document.addEventListener('submit', (e) => {
            const form = e.target;
            if (form.tagName !== 'FORM') return;

            // Ищем кнопку submit
            const submitBtn = form.querySelector('button[type="submit"], input[type="submit"]');
            if (submitBtn && submitBtn.tagName === 'BUTTON') {
                // Не блокируем если форма не валидна
                if (form.checkValidity && !form.checkValidity()) return;

                this.button(submitBtn, true);

                // Восстанавливаем через 10 секунд (защита от зависания)
                setTimeout(() => {
                    this.button(submitBtn, false);
                }, 10000);
            }
        });

        // Кнопки с data-loading
        document.addEventListener('click', (e) => {
            const btn = e.target.closest('[data-loading]');
            if (!btn) return;

            const action = btn.dataset.loading;
            if (action === 'page') {
                this.page.show(btn.dataset.loadingText);
            } else {
                this.button(btn, true);
            }
        });
    }
};

// Авто-инициализация
document.addEventListener('DOMContentLoaded', () => {
    QazLoading.init();
});

// Экспорт для использования в других скриптах
window.QazLoading = QazLoading;
