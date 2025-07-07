# Dark Mode and Light Mode Feature

## Overview

The RSO Research Management System now includes a comprehensive dark mode and light mode feature that allows users to switch between themes based on their preference. The theme system is built using CSS variables and JavaScript for smooth transitions and persistent storage.

## Features

### 🌙 Dark Mode
- **Dark Background**: Deep navy blue (`#0f172a`) for the main background
- **Card Backgrounds**: Dark slate (`#1e293b`) for cards and components
- **Text Colors**: Light colors for optimal readability
- **Accent Colors**: Adjusted blue tones for better contrast
- **Shadows**: Enhanced shadows for depth in dark environments

### ☀️ Light Mode
- **Light Background**: Clean light gray (`#f8fafc`) for the main background
- **Card Backgrounds**: Pure white (`#ffffff`) for cards and components
- **Text Colors**: Dark colors for optimal readability
- **Accent Colors**: Standard blue tones
- **Shadows**: Subtle shadows for depth

### 🔄 Theme Switching
- **Toggle Button**: Located in the header with sun/moon icons
- **Smooth Transitions**: 0.3s ease transitions for all color changes
- **Persistent Storage**: Theme preference saved in localStorage
- **System Preference**: Automatically detects user's system theme preference
- **Manual Override**: Users can manually override system preference

## Implementation Details

### Files Added/Modified

#### New Files
- `css/theme.css` - CSS variables and theme definitions
- `js/theme.js` - Theme management JavaScript class
- `theme-demo.html` - Demo page showcasing the theme system
- `THEME_README.md` - This documentation file

#### Modified Files
- `index.php` - Added theme toggle button and theme CSS/JS
- `php/loginpage.php` - Added theme toggle for login page
- `css/modern-theme.css` - Updated to use CSS variables

### CSS Variables System

The theme system uses CSS custom properties (variables) defined in `:root` for light mode and `[data-theme="dark"]` for dark mode:

```css
:root {
  --bg-primary: #f8fafc;      /* Light background */
  --text-primary: #1e293b;    /* Dark text */
  --border-primary: #e2e8f0;  /* Light borders */
  /* ... more variables */
}

[data-theme="dark"] {
  --bg-primary: #0f172a;      /* Dark background */
  --text-primary: #f8fafc;    /* Light text */
  --border-primary: #334155;  /* Dark borders */
  /* ... more variables */
}
```

### JavaScript Theme Manager

The `ThemeManager` class handles:
- Theme detection and application
- localStorage persistence
- System preference detection
- Toggle button functionality
- Smooth transitions

```javascript
// Initialize theme manager
window.themeManager = new ThemeManager();

// Manual theme switching
themeManager.setTheme('dark');
themeManager.setTheme('light');

// Get current theme
const currentTheme = themeManager.getCurrentTheme();
```

## Usage

### For Users

1. **Toggle Theme**: Click the sun/moon icon in the header
2. **Automatic Detection**: The system detects your OS theme preference
3. **Persistent**: Your choice is remembered across sessions
4. **System Sync**: Automatically follows system theme changes (if no manual preference set)

### For Developers

#### Adding Theme Support to New Components

1. **Use CSS Variables**: Instead of hardcoded colors, use the defined variables:
   ```css
   .my-component {
     background: var(--bg-card);
     color: var(--text-primary);
     border: 1px solid var(--border-primary);
   }
   ```

2. **Add Transitions**: Include smooth transitions for theme changes:
   ```css
   .my-component {
     transition: background-color 0.3s ease, color 0.3s ease;
   }
   ```

#### Including Theme System in New Pages

1. **Add CSS**: Include the theme CSS file:
   ```html
   <link rel="stylesheet" href="css/theme.css">
   ```

2. **Add JavaScript**: Include the theme JavaScript file:
   ```html
   <script src="js/theme.js"></script>
   ```

3. **Add Toggle Button**: Include the theme toggle button:
   ```html
   <button class="theme-toggle" title="Toggle Theme">
     <i class="fas fa-moon"></i>
   </button>
   ```

## Available CSS Variables

### Background Colors
- `--bg-primary` - Main background
- `--bg-secondary` - Secondary background (cards, modals)
- `--bg-tertiary` - Tertiary background (hover states)
- `--bg-card` - Card backgrounds
- `--bg-header` - Header background
- `--bg-modal` - Modal background
- `--bg-dropdown` - Dropdown background

### Text Colors
- `--text-primary` - Primary text color
- `--text-secondary` - Secondary text color
- `--text-tertiary` - Tertiary text color
- `--text-muted` - Muted text color
- `--text-inverse` - Inverse text color (for dark backgrounds)

### Border Colors
- `--border-primary` - Primary border color
- `--border-secondary` - Secondary border color
- `--border-focus` - Focus border color

### Button Colors
- `--btn-primary-bg` - Primary button background
- `--btn-primary-hover` - Primary button hover
- `--btn-secondary-bg` - Secondary button background
- `--btn-secondary-hover` - Secondary button hover
- `--btn-success-bg` - Success button background
- `--btn-success-hover` - Success button hover
- `--btn-danger-bg` - Danger button background
- `--btn-danger-hover` - Danger button hover
- `--btn-warning-bg` - Warning button background
- `--btn-warning-hover` - Warning button hover

### Input Colors
- `--input-bg` - Input background
- `--input-border` - Input border
- `--input-focus-border` - Input focus border
- `--input-placeholder` - Input placeholder text

### Shadows
- `--shadow-sm` - Small shadow
- `--shadow-md` - Medium shadow
- `--shadow-lg` - Large shadow
- `--shadow-xl` - Extra large shadow

## Browser Support

The theme system uses modern CSS features and is compatible with:
- Chrome 49+
- Firefox 31+
- Safari 9.1+
- Edge 12+

## Testing

To test the theme system:

1. **Demo Page**: Visit `theme-demo.html` to see all components in both themes
2. **Main System**: Use the theme toggle in the main dashboard
3. **Login Page**: Test theme switching on the login page
4. **Persistence**: Refresh the page to verify theme persistence
5. **System Preference**: Change your OS theme preference to test automatic detection

## Future Enhancements

Potential improvements for the theme system:
- **Custom Themes**: Allow users to create custom color schemes
- **High Contrast Mode**: Add accessibility-focused high contrast theme
- **Auto-switch**: Schedule-based theme switching (e.g., dark mode at night)
- **Animation Preferences**: Allow users to disable transitions
- **Export/Import**: Share theme preferences between devices

## Troubleshooting

### Theme Not Switching
1. Check if `theme.js` is properly loaded
2. Verify CSS variables are being applied
3. Check browser console for JavaScript errors
4. Ensure localStorage is enabled

### Inconsistent Colors
1. Verify all hardcoded colors are replaced with CSS variables
2. Check for conflicting CSS rules
3. Ensure proper CSS specificity

### Performance Issues
1. Reduce transition duration if needed
2. Limit the number of elements with transitions
3. Consider using `transform` instead of `background-color` for animations

## Contributing

When adding new components or modifying existing ones:

1. **Always use CSS variables** instead of hardcoded colors
2. **Test in both themes** before committing changes
3. **Add transitions** for smooth theme switching
4. **Update this documentation** if adding new variables or features
5. **Follow the existing naming convention** for CSS variables

---

For questions or issues with the theme system, please refer to the main project documentation or create an issue in the project repository. 