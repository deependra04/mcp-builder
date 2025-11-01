"use strict";
/**
 * MCP Builder Main TypeScript Module
 *
 * This module handles core functionality for the MCP Builder package.
 * All functionality is namespaced under McpBuilder to prevent conflicts.
 */
var McpBuilder;
(function (McpBuilder) {
    'use strict';
    class Main {
        constructor() {
            this.initialized = false;
            if (this.initialized) {
                return;
            }
            this.init();
        }
        /**
         * Initialize the MCP Builder functionality
         */
        init() {
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', () => this.initialize());
            }
            else {
                this.initialize();
            }
        }
        /**
         * Initialize auto-dismiss alerts
         */
        initialize() {
            if (this.initialized) {
                return;
            }
            // Auto-dismiss alerts after 5 seconds (only for .mcp-builder-alert class)
            const alerts = document.querySelectorAll('.mcp-builder-alert.alert');
            alerts.forEach((alert) => {
                if (window.bootstrap && window.bootstrap.Alert) {
                    setTimeout(() => {
                        try {
                            const bsAlert = new window.bootstrap.Alert(alert);
                            bsAlert.close();
                        }
                        catch (error) {
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
        static getVersion() {
            return Main.VERSION;
        }
    }
    Main.VERSION = '1.0.0';
    McpBuilder.Main = Main;
    // Initialize on load
    if (typeof window !== 'undefined') {
        window.McpBuilder = new Main();
    }
})(McpBuilder || (McpBuilder = {}));
// Namespace is available globally
// No export needed for global namespace
//# sourceMappingURL=mcp-builder.js.map