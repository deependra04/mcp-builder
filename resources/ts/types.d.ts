/**
 * Type definitions for MCP Builder
 */

declare namespace Bootstrap {
  class Toast {
    constructor(element: HTMLElement, options?: ToastOptions);
    show(): void;
    hide(): void;
    dispose(): void;
  }

  interface ToastOptions {
    autohide?: boolean;
    delay?: number;
  }

  class Alert {
    constructor(element: HTMLElement);
    close(): void;
    dispose(): void;
  }
}

interface Window {
  bootstrap?: {
    Toast: typeof Bootstrap.Toast;
    Alert: typeof Bootstrap.Alert;
  };
  McpToast?: any; // ToastManager instance
  McpBuilder?: any; // Main instance
  jQuery?: any;
  $?: any;
}

declare namespace McpBuilder {
  interface ToastOptions {
    title?: string;
    duration?: number;
    dismissible?: boolean;
    actions?: Array<{
      label: string;
      action: string | (() => void);
    }>;
  }

  interface ToastManager {
    show(type: 'success' | 'error' | 'warning' | 'info', message: string, options?: ToastOptions): Bootstrap.Toast;
    success(message: string, options?: ToastOptions): Bootstrap.Toast;
    error(message: string, options?: ToastOptions): Bootstrap.Toast;
    warning(message: string, options?: ToastOptions): Bootstrap.Toast;
    info(message: string, options?: ToastOptions): Bootstrap.Toast;
  }

  interface Main {
    version: string;
    init(): void;
  }
}

interface JQueryStatic {
  (selector: string | Element | Document): JQuery;
  ajaxError(handler: (event: JQuery.Event, xhr: JQuery.jqXHR, settings: JQuery.AjaxSettings, thrownError?: string) => void): void;
  ajaxSuccess(handler: (event: JQuery.Event, xhr: JQuery.jqXHR, settings: JQuery.AjaxSettings) => void): void;
}

interface JQuery {
  (): JQuery;
  on(event: string, handler: (event: JQuery.Event) => void): JQuery;
  trigger(event: string): JQuery;
}

interface JQuery {
  ajaxError(handler: (event: JQuery.Event, xhr: JQuery.jqXHR, settings: JQuery.AjaxSettings, thrownError?: string) => void): void;
  ajaxSuccess(handler: (event: JQuery.Event, xhr: JQuery.jqXHR, settings: JQuery.AjaxSettings) => void): void;
}

