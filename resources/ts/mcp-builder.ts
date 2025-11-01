/**
 * MCP Builder Main TypeScript Module
 * 
 * This module handles core functionality for the MCP Builder package.
 * All functionality is namespaced under McpBuilder to prevent conflicts.
 */

namespace McpBuilder {
  'use strict';

  export class Main {
    private static readonly VERSION: string = '1.0.0';
    private initialized: boolean = false;

    constructor() {
      if (this.initialized) {
        return;
      }
      this.init();
    }

    /**
     * Initialize the MCP Builder functionality
     */
    public init(): void {
      if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => this.initialize());
      } else {
        this.initialize();
      }
    }

    /**
     * Initialize auto-dismiss alerts
     */
    private initialize(): void {
      if (this.initialized) {
        return;
      }

      // Auto-dismiss alerts after 5 seconds (only for .mcp-builder-alert class)
      const alerts = document.querySelectorAll<HTMLElement>('.mcp-builder-alert.alert');
      
      alerts.forEach((alert: HTMLElement) => {
        if (window.bootstrap && window.bootstrap.Alert) {
          setTimeout(() => {
            try {
              const bsAlert = new window.bootstrap.Alert(alert);
              bsAlert.close();
            } catch (error) {
              console.warn('MCP Builder: Could not close alert', error);
            }
          }, 5000);
        }
      });

      this.initialized = true;
    }

    /**
     * Get version
     */
    public static getVersion(): string {
      return Main.VERSION;
    }
  }

  // Initialize on load
  if (typeof window !== 'undefined') {
    window.McpBuilder = new Main();
  }
}

// Namespace is available globally
// No export needed for global namespace

