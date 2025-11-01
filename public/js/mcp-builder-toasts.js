/**
 * MCP Builder Toast Notification System
 */
(function() {
    'use strict';

    class ToastManager {
        constructor() {
            this.container = document.getElementById('toastContainer');
            if (!this.container) {
                this.container = document.createElement('div');
                this.container.className = 'position-fixed bottom-0 end-0 p-3';
                this.container.style.zIndex = '11';
                document.body.appendChild(this.container);
            }
            this.toasts = [];
            this.init();
        }

        init() {
            // Initialize existing toasts from Blade
            document.querySelectorAll('.mcp-toast').forEach(toastEl => {
                this.showExistingToast(toastEl);
            });
        }

        showExistingToast(toastEl) {
            const toast = new bootstrap.Toast(toastEl, {
                autohide: true,
                delay: toastEl.dataset.bsDelay || 5000
            });
            toast.show();
            
            toastEl.addEventListener('hidden.bs.toast', () => {
                toastEl.remove();
            });
        }

        /**
         * Show a toast notification
         */
        show(type, message, options = {}) {
            const {
                title = null,
                duration = 5000,
                dismissible = true,
                actions = []
            } = options;

            const toastId = 'toast-' + Date.now();
            const toastEl = this.createToastElement(toastId, type, title, message, duration, dismissible, actions);
            
            this.container.appendChild(toastEl);
            this.toasts.push(toastEl);

            const toast = new bootstrap.Toast(toastEl, {
                autohide: dismissible,
                delay: duration
            });

            toast.show();

            toastEl.addEventListener('hidden.bs.toast', () => {
                toastEl.remove();
                this.toasts = this.toasts.filter(t => t !== toastEl);
            });

            return toast;
        }

        createToastElement(id, type, title, message, duration, dismissible, actions) {
            const types = {
                'success': ['bg-success', 'text-white', '✓'],
                'error': ['bg-danger', 'text-white', '✗'],
                'warning': ['bg-warning', 'text-dark', '⚠'],
                'info': ['bg-info', 'text-white', 'ℹ']
            };

            const [bgClass, textClass, icon] = types[type] || types.info;
            
            const toastEl = document.createElement('div');
            toastEl.id = id;
            toastEl.className = `mcp-toast toast align-items-center ${bgClass} ${textClass} border-0`;
            toastEl.setAttribute('role', 'alert');
            toastEl.setAttribute('aria-live', 'assertive');
            toastEl.setAttribute('aria-atomic', 'true');
            toastEl.dataset.bsAutohide = dismissible ? 'true' : 'false';
            toastEl.dataset.bsDelay = duration;

            let actionsHtml = '';
            if (actions.length > 0) {
                actionsHtml = '<div class="mt-2">' +
                    actions.map(action => 
                        `<button class="btn btn-sm btn-outline-light me-1" onclick="${action.action}">${action.label}</button>`
                    ).join('') +
                    '</div>';
            }

            toastEl.innerHTML = `
                <div class="d-flex">
                    <div class="toast-body">
                        ${title ? `<strong>${icon} ${title}</strong><br>` : `<strong>${icon}</strong> `}
                        ${message}
                        ${actionsHtml}}
                    </div>
                    ${dismissible ? '<button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>' : ''}
                </div>
            `;

            return toastEl;
        }

        /**
         * Convenience methods
         */
        success(message, options = {}) {
            return this.show('success', message, options);
        }

        error(message, options = {}) {
            return this.show('error', message, options);
        }

        warning(message, options = {}) {
            return this.show('warning', message, options);
        }

        info(message, options = {}) {
            return this.show('info', message, options);
        }
    }

    // Initialize on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            window.McpToast = new ToastManager();
        });
    } else {
        window.McpToast = new ToastManager();
    }

    // Handle AJAX errors globally
    if (window.jQuery) {
        $(document).ajaxError(function(event, xhr, settings, thrownError) {
            let message = 'An error occurred';
            
            if (xhr.responseJSON && xhr.responseJSON.error) {
                message = xhr.responseJSON.error.message || message;
                
                if (xhr.responseJSON.error.suggestions) {
                    const suggestions = xhr.responseJSON.error.suggestions.join(', ');
                    message += '. ' + suggestions;
                }
            } else if (xhr.status === 0) {
                message = 'Network error. Please check your connection.';
            } else if (xhr.status === 422) {
                message = 'Validation failed. Please check your input.';
            } else if (xhr.status >= 500) {
                message = 'Server error. Please try again later.';
            }

            window.McpToast.error(message, { duration: 8000 });
        });

        // Handle successful AJAX
        $(document).ajaxSuccess(function(event, xhr, settings) {
            if (xhr.responseJSON && xhr.responseJSON.success && xhr.responseJSON.message) {
                window.McpToast.success(xhr.responseJSON.message);
            }
        });
    }
})();

