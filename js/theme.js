// Theme Management System for RSO Research Management System

class ThemeManager {
    constructor() {
        this.theme = this.getStoredTheme() || this.getSystemTheme();
        this.init();
    }

    // Initialize theme system
    init() {
        this.applyTheme(this.theme);
        this.setupThemeToggle();
        this.setupSystemThemeListener();
    }

    // Get stored theme from localStorage
    getStoredTheme() {
        return localStorage.getItem('rso-theme');
    }

    // Get system theme preference
    getSystemTheme() {
        if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
            return 'dark';
        }
        return 'light';
    }

    // Apply theme to document
    applyTheme(theme) {
        document.documentElement.setAttribute('data-theme', theme);
        this.theme = theme;
        localStorage.setItem('rso-theme', theme);
        this.updateThemeToggleIcon(theme);
    }

    // Toggle between light and dark themes
    toggleTheme() {
        const newTheme = this.theme === 'light' ? 'dark' : 'light';
        this.applyTheme(newTheme);
    }

    // Update theme toggle button icon
    updateThemeToggleIcon(theme) {
        const toggleBtn = document.querySelector('.theme-toggle i');
        if (toggleBtn) {
            if (theme === 'dark') {
                toggleBtn.className = 'fas fa-sun';
                toggleBtn.title = 'Switch to Light Mode';
            } else {
                toggleBtn.className = 'fas fa-moon';
                toggleBtn.title = 'Switch to Dark Mode';
            }
        }
    }

    // Setup theme toggle button
    setupThemeToggle() {
        const toggleBtn = document.querySelector('.theme-toggle');
        if (toggleBtn) {
            toggleBtn.addEventListener('click', () => {
                this.toggleTheme();
            });
        }
    }

    // Listen for system theme changes
    setupSystemThemeListener() {
        if (window.matchMedia) {
            const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
            mediaQuery.addEventListener('change', (e) => {
                // Only auto-switch if user hasn't manually set a preference
                if (!localStorage.getItem('rso-theme')) {
                    const newTheme = e.matches ? 'dark' : 'light';
                    this.applyTheme(newTheme);
                }
            });
        }
    }

    // Get current theme
    getCurrentTheme() {
        return this.theme;
    }

    // Set specific theme
    setTheme(theme) {
        if (theme === 'light' || theme === 'dark') {
            this.applyTheme(theme);
        }
    }
}

// Initialize theme manager when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.themeManager = new ThemeManager();
});

// Export for use in other scripts
if (typeof module !== 'undefined' && module.exports) {
    module.exports = ThemeManager;
} 