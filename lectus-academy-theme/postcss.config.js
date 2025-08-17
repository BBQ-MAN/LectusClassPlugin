module.exports = {
  plugins: {
    tailwindcss: {},
    autoprefixer: {
      overrideBrowserslist: [
        '> 1%',
        'last 2 versions',
        'Firefox ESR',
        'not dead',
        'IE 11',
        'Edge >= 12',
        'Chrome >= 45',
        'Firefox >= 38',
        'Safari >= 9',
        'iOS >= 9',
        'Android >= 4.4'
      ],
      grid: 'autoplace'
    },
  },
}
