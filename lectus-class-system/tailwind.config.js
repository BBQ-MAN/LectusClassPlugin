/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    './**/*.php',
    './assets/js/**/*.js',
    './templates/**/*.php',
    './includes/**/*.php',
    './admin/**/*.php'
  ],
  theme: {
    extend: {
      colors: {
        'lectus-primary': '#007cba',
        'lectus-primary-dark': '#005a87',
        'lectus-secondary': '#667eea',
        'lectus-secondary-dark': '#764ba2',
        'lectus-success': '#4CAF50',
        'lectus-success-dark': '#45a049',
        'lectus-danger': '#f44336',
        'lectus-warning': '#ff9800',
        'lectus-info': '#5bc0de',
      },
      fontFamily: {
        'sans': ['-apple-system', 'BlinkMacSystemFont', 'Segoe UI', 'Roboto', 'Helvetica Neue', 'Arial', 'sans-serif'],
      },
      boxShadow: {
        'card': '0 2px 4px rgba(0,0,0,0.1)',
        'card-hover': '0 4px 12px rgba(0,0,0,0.15)',
      },
      animation: {
        'slide-up': 'slideUp 0.3s ease-out',
        'fade-in': 'fadeIn 0.3s ease-out',
      },
      keyframes: {
        slideUp: {
          '0%': { transform: 'translateY(10px)', opacity: '0' },
          '100%': { transform: 'translateY(0)', opacity: '1' },
        },
        fadeIn: {
          '0%': { opacity: '0' },
          '100%': { opacity: '1' },
        }
      }
    },
  },
  plugins: [
    require('@tailwindcss/forms'),
    require('@tailwindcss/typography'),
    require('@tailwindcss/aspect-ratio'),
  ],
}