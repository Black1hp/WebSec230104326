// Dark Mode Toggle Functionality
document.addEventListener('DOMContentLoaded', function() {
    // Check for saved theme preference or prefer-color-scheme
    const savedTheme = localStorage.getItem('theme');
    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
    
    // Apply theme based on saved preference or system preference
    if (savedTheme === 'dark' || (!savedTheme && prefersDark)) {
        document.documentElement.setAttribute('data-bs-theme', 'dark');
        if (document.getElementById('darkModeToggle')) {
            document.getElementById('darkModeToggle').checked = true;
        }
    } else {
        document.documentElement.setAttribute('data-bs-theme', 'light');
    }
    
    // Add event listeners to theme toggle buttons/switches
    document.querySelectorAll('.theme-toggle').forEach(toggle => {
        toggle.addEventListener('click', function() {
            toggleDarkMode();
        });
    });
    
    // Add event listener to checkbox toggle if it exists
    const darkModeToggle = document.getElementById('darkModeToggle');
    if (darkModeToggle) {
        darkModeToggle.addEventListener('change', function() {
            toggleDarkMode(this.checked);
        });
    }
});

// Function to toggle dark mode
function toggleDarkMode(forceDark = null) {
    const html = document.documentElement;
    const isDark = html.getAttribute('data-bs-theme') === 'dark';
    
    // If forceDark is provided, use it, otherwise toggle current state
    const newTheme = (forceDark !== null) ? (forceDark ? 'dark' : 'light') : (isDark ? 'light' : 'dark');
    
    // Apply theme
    html.setAttribute('data-bs-theme', newTheme);
    
    // Save preference
    localStorage.setItem('theme', newTheme);
    
    // Update toggle state if exists
    const darkModeToggle = document.getElementById('darkModeToggle');
    if (darkModeToggle) {
        darkModeToggle.checked = newTheme === 'dark';
    }
    
    // Update icon if exists
    const themeIcon = document.getElementById('themeIcon');
    if (themeIcon) {
        themeIcon.className = newTheme === 'dark' ? 'bi bi-moon-fill' : 'bi bi-sun-fill';
    }
} 