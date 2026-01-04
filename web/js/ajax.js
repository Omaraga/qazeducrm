/**
 * QazFetch - AJAX wrapper with automatic error handling and toast notifications
 */
const QazFetch = {
    /**
     * Get CSRF token from meta tag or Yii form
     */
    getCsrfToken() {
        const metaToken = document.querySelector('meta[name="csrf-token"]');
        if (metaToken) {
            return metaToken.getAttribute('content');
        }
        const formToken = document.querySelector('input[name="_csrf"]');
        if (formToken) {
            return formToken.value;
        }
        return null;
    },

    /**
     * Get CSRF param name
     */
    getCsrfParam() {
        const metaParam = document.querySelector('meta[name="csrf-param"]');
        if (metaParam) {
            return metaParam.getAttribute('content');
        }
        return '_csrf';
    },

    /**
     * Show toast notification (uses Alpine.js store if available)
     */
    toast(type, message) {
        if (window.Alpine && Alpine.store('toast')) {
            Alpine.store('toast')[type](message);
        } else {
            console.log(`[${type.toUpperCase()}] ${message}`);
        }
    },

    /**
     * Handle HTTP errors
     */
    handleError(status, message) {
        const errorMessages = {
            400: 'Неверный запрос',
            401: 'Требуется авторизация',
            403: 'Доступ запрещён',
            404: 'Страница не найдена',
            422: 'Ошибка валидации',
            429: 'Слишком много запросов. Подождите немного.',
            500: 'Ошибка сервера',
            502: 'Сервер временно недоступен',
            503: 'Сервис временно недоступен',
        };

        const errorMessage = message || errorMessages[status] || 'Произошла ошибка';
        this.toast('error', errorMessage);

        // Redirect to login if unauthorized
        if (status === 401) {
            setTimeout(() => {
                window.location.href = '/login';
            }, 2000);
        }
    },

    /**
     * Make a fetch request with common options
     */
    async request(url, options = {}) {
        const defaultOptions = {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            },
            credentials: 'same-origin',
        };

        // Add CSRF token for non-GET requests
        if (options.method && options.method !== 'GET') {
            const csrfToken = this.getCsrfToken();
            if (csrfToken) {
                defaultOptions.headers['X-CSRF-Token'] = csrfToken;
            }
        }

        // Merge options
        const mergedOptions = {
            ...defaultOptions,
            ...options,
            headers: {
                ...defaultOptions.headers,
                ...options.headers,
            },
        };

        // Handle FormData - don't set Content-Type (browser will set it with boundary)
        if (options.body instanceof FormData) {
            delete mergedOptions.headers['Content-Type'];
        } else if (options.body && typeof options.body === 'object' && !(options.body instanceof FormData)) {
            mergedOptions.headers['Content-Type'] = 'application/json';
            mergedOptions.body = JSON.stringify(options.body);
        }

        try {
            const response = await fetch(url, mergedOptions);

            // Handle non-JSON responses
            const contentType = response.headers.get('content-type');
            const isJson = contentType && contentType.includes('application/json');

            if (!response.ok) {
                let errorMessage = null;
                if (isJson) {
                    try {
                        const errorData = await response.json();
                        errorMessage = errorData.message || errorData.error;
                    } catch (e) {
                        // JSON parse failed
                    }
                }
                this.handleError(response.status, errorMessage);
                throw new Error(errorMessage || `HTTP ${response.status}`);
            }

            // Return JSON or text based on content type
            if (isJson) {
                return await response.json();
            }
            return await response.text();

        } catch (error) {
            // Network errors
            if (error.name === 'TypeError' && error.message.includes('fetch')) {
                this.toast('error', 'Ошибка сети. Проверьте подключение к интернету.');
            } else if (!error.message.startsWith('HTTP')) {
                // Don't show toast for already handled HTTP errors
                console.error('QazFetch error:', error);
            }
            throw error;
        }
    },

    /**
     * GET request
     */
    async get(url, params = {}) {
        const urlObj = new URL(url, window.location.origin);
        Object.entries(params).forEach(([key, value]) => {
            if (value !== null && value !== undefined) {
                urlObj.searchParams.append(key, value);
            }
        });
        return this.request(urlObj.toString(), { method: 'GET' });
    },

    /**
     * POST request
     */
    async post(url, data = {}) {
        return this.request(url, {
            method: 'POST',
            body: data,
        });
    },

    /**
     * POST with FormData (for forms)
     */
    async postForm(url, formData) {
        // Add CSRF token to FormData
        const csrfToken = this.getCsrfToken();
        const csrfParam = this.getCsrfParam();
        if (csrfToken && !formData.has(csrfParam)) {
            formData.append(csrfParam, csrfToken);
        }
        return this.request(url, {
            method: 'POST',
            body: formData,
        });
    },

    /**
     * PUT request
     */
    async put(url, data = {}) {
        return this.request(url, {
            method: 'PUT',
            body: data,
        });
    },

    /**
     * DELETE request
     */
    async delete(url) {
        return this.request(url, {
            method: 'DELETE',
        });
    },

    /**
     * Submit form via AJAX
     */
    async submitForm(form, options = {}) {
        const formData = new FormData(form);
        const url = options.url || form.action || window.location.href;
        const method = (options.method || form.method || 'POST').toUpperCase();

        const submitButton = form.querySelector('[type="submit"]');
        const originalText = submitButton ? submitButton.textContent : '';

        try {
            // Disable submit button
            if (submitButton) {
                submitButton.disabled = true;
                submitButton.textContent = options.loadingText || 'Сохранение...';
            }

            let response;
            if (method === 'GET') {
                const params = Object.fromEntries(formData.entries());
                response = await this.get(url, params);
            } else {
                response = await this.postForm(url, formData);
            }

            // Show success message
            if (options.successMessage) {
                this.toast('success', options.successMessage);
            } else if (response.message) {
                this.toast('success', response.message);
            }

            // Handle redirect
            if (response.redirect) {
                window.location.href = response.redirect;
                return response;
            }

            // Call success callback
            if (options.onSuccess) {
                options.onSuccess(response);
            }

            return response;

        } catch (error) {
            // Call error callback
            if (options.onError) {
                options.onError(error);
            }
            throw error;

        } finally {
            // Re-enable submit button
            if (submitButton) {
                submitButton.disabled = false;
                submitButton.textContent = originalText;
            }
        }
    },
};

// Make available globally
window.QazFetch = QazFetch;

// jQuery compatibility layer (optional)
if (window.jQuery) {
    jQuery.qazFetch = QazFetch;
}
