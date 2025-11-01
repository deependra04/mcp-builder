/**
 * MCP Builder Toast Notification System
 * TypeScript implementation with full type safety
 */

namespace McpBuilder {
  'use strict';

  interface ToastTypeConfig {
    bgClass: string;
    textClass: string;
    icon: string;
  }

  interface ToastAction {
    label: string;
    action: string | (() => void);
  }

  export interface ToastManagerOptions {
    title?: string;
    duration?: number;
    dismissible?: boolean;
    actions?: ToastAction[];
  }

  export class ToastManager {
    private container: HTMLElement;
    private toasts: HTMLElement[] = [];

    private readonly typeConfigs: Record<string, ToastTypeConfig> = {
      'success': {
        bgClass: 'bg-success',
        textClass: 'text-white',
        icon: '✓'
      },
      'error': {
        bgClass: 'bg-danger',
        textClass: 'text-white',
        icon: '✗'
      },
      'warning': {
        bgClass: 'bg-warning',
        textClass: 'text-dark',
        icon: '⚠'
      },
      'info': {
        bgClass: 'bg-info',
        textClass: 'text-white',
        icon: 'ℹ'
      }
    };

    constructor() {
      this.container = this.initializeContainer();
      this.init();
    }

    /**
     * Initialize toast container
     */
    private initializeContainer(): HTMLElement {
      let container = document.getElementById('mcp-builder-toast-container');
      
      if (!container) {
        container = document.createElement('div');
        container.id = 'mcp-builder-toast-container';
        container.className = 'position-fixed bottom-0 end-0 p-3';
        container.style.zIndex = '11';
        document.body.appendChild(container);
      }

      return container;
    }

    /**
     * Initialize existing toasts from DOM
     */
    private init(): void {
      document.querySelectorAll<HTMLElement>('.mcp-builder-toast').forEach((toastEl: HTMLElement) => {
        this.showExistingToast(toastEl);
      });
    }

    /**
     * Show existing toast element
     */
    private showExistingToast(toastEl: HTMLElement): void {
      if (!window.bootstrap || !window.bootstrap.Toast) {
        console.warn('MCP Builder: Bootstrap Toast not available');
        return;
      }

      const delay = toastEl.dataset.bsDelay ? parseInt(toastEl.dataset.bsDelay) : 5000;
      
      const toast = new window.bootstrap.Toast(toastEl, {
        autohide: toastEl.dataset.bsAutohide !== 'false',
        delay: delay
      });

      toast.show();

      toastEl.addEventListener('hidden.bs.toast', () => {
        toastEl.remove();
        this.toasts = this.toasts.filter(t => t !== toastEl);
      }, { once: true });

      this.toasts.push(toastEl);
    }

    /**
     * Show a toast notification
     */
    public show(
      type: 'success' | 'error' | 'warning' | 'info',
      message: string,
      options: ToastManagerOptions = {}
    ): Bootstrap.Toast | null {
      if (!window.bootstrap || !window.bootstrap.Toast) {
        console.warn('MCP Builder: Bootstrap Toast not available');
        return null;
      }

      const {
        title = null,
        duration = 5000,
        dismissible = true,
        actions = []
      } = options;

      const toastId = `mcp-builder-toast-${Date.now()}`;
      const toastEl = this.createToastElement(toastId, type, title, message, duration, dismissible, actions);

      this.container.appendChild(toastEl);
      this.toasts.push(toastEl);

      const toast = new window.bootstrap.Toast(toastEl, {
        autohide: dismissible,
        delay: duration
      });

      toast.show();

      toastEl.addEventListener('hidden.bs.toast', () => {
        toastEl.remove();
        this.toasts = this.toasts.filter(t => t !== toastEl);
      }, { once: true });

      return toast;
    }

    /**
     * Create toast element
     */
    private createToastElement(
      id: string,
      type: string,
      title: string | null,
      message: string,
      duration: number,
      dismissible: boolean,
      actions: ToastAction[]
    ): HTMLElement {
      const config = this.typeConfigs[type] || this.typeConfigs['info'];
      const { bgClass, textClass, icon } = config;

      const toastEl = document.createElement('div');
      toastEl.id = id;
      toastEl.className = `mcp-builder-toast toast align-items-center ${bgClass} ${textClass} border-0`;
      toastEl.setAttribute('role', 'alert');
      toastEl.setAttribute('aria-live', 'assertive');
      toastEl.setAttribute('aria-atomic', 'true');
      toastEl.dataset.bsAutohide = dismissible ? 'true' : 'false';
      toastEl.dataset.bsDelay = duration.toString();

      let actionsHtml = '';
      if (actions.length > 0) {
        actionsHtml = '<div class="mt-2">' +
          actions.map(action => {
            const actionStr = typeof action.action === 'function' 
              ? `(${action.action.toString()})()` 
              : action.action;
            return `<button class="btn btn-sm btn-outline-light me-1" onclick="${actionStr}">${action.label}</button>`;
          }).join('') +
          '</div>';
      }

      toastEl.innerHTML = `
        <div class="d-flex">
          <div class="toast-body">
            ${title ? `<strong>${icon} ${this.escapeHtml(title)}</strong><br>` : `<strong>${icon}</strong> `}
            ${this.escapeHtml(message)}
            ${actionsHtml}
          </div>
          ${dismissible ? '<button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>' : ''}
        </div>
      `;

      return toastEl;
    }

    /**
     * Escape HTML to prevent XSS
     */
    private escapeHtml(text: string): string {
      const div = document.createElement('div');
      div.textContent = text;
      return div.innerHTML;
    }

    /**
     * Convenience methods
     */
    public success(message: string, options?: ToastManagerOptions): Bootstrap.Toast | null {
      return this.show('success', message, options);
    }

    public error(message: string, options?: ToastManagerOptions): Bootstrap.Toast | null {
      return this.show('error', message, options);
    }

    public warning(message: string, options?: ToastManagerOptions): Bootstrap.Toast | null {
      return this.show('warning', message, options);
    }

    public info(message: string, options?: ToastManagerOptions): Bootstrap.Toast | null {
      return this.show('info', message, options);
    }
  }

  // Initialize global toast manager
  let toastManager: ToastManager | null = null;

  function initializeToastManager(): void {
    if (typeof window !== 'undefined' && !toastManager) {
      toastManager = new ToastManager();
      window.McpToast = toastManager;
    }
  }

  // Initialize on DOM ready
  if (typeof document !== 'undefined') {
    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', initializeToastManager);
    } else {
      initializeToastManager();
    }
  }

  // Handle AJAX errors globally (if jQuery is available)
  if (typeof window !== 'undefined' && window.jQuery && typeof window.jQuery === 'function') {
    const $ = window.jQuery as any;
    $(document).ajaxError((_event: any, xhr: any, _settings: any, _thrownError?: string) => {
      let message = 'An error occurred';

      try {
        const responseText = typeof xhr.responseText === 'string' ? xhr.responseText : String(xhr.responseText || '{}');
        const response = JSON.parse(responseText);
        if (response.error) {
          message = response.error.message || message;

          if (response.error.suggestions && Array.isArray(response.error.suggestions)) {
            const suggestions = response.error.suggestions.join(', ');
            message += '. ' + suggestions;
          }
        }
      } catch (e) {
        // If response is not JSON, use status-based messages
        const status = xhr.status || 0;
        if (status === 0) {
          message = 'Network error. Please check your connection.';
        } else if (status === 422) {
          message = 'Validation failed. Please check your input.';
        } else if (status >= 500) {
          message = 'Server error. Please try again later.';
        }
      }

      if (toastManager) {
        toastManager.error(message, { duration: 8000 });
      }
    });

    // Handle successful AJAX
    $(document).ajaxSuccess((_event: any, xhr: any, _settings: any) => {
      try {
        const responseText = typeof xhr.responseText === 'string' ? xhr.responseText : String(xhr.responseText || '{}');
        const response = JSON.parse(responseText);
        if (response.success && response.message && toastManager) {
          toastManager.success(response.message);
        }
      } catch (e) {
        // Ignore non-JSON responses
      }
    });
  }
}

// Make ToastManager available globally
if (typeof window !== 'undefined') {
  // TypeScript namespace is available at compile time
  // Runtime: window.McpToast will be set by initializeToastManager()
}

