/** @type {import('tailwindcss').Config} */
module.exports = {
  mode: 'jit',
  darkMode: ['selector'],
  content: [
    './templates/**/*.html.twig',
    './assets/**/*.js',
  ],
  theme: {
    extend: {
      fontFamily: {
        sans: ['Inter var', 'sans-serif'],
      },
    },
  },
  daisyui: {
    themes: [
      {
        mytheme: {
          "primary": "#3b82f6",
          "secondary": "#d1d5db",
          "accent": "#00ffff",
          "neutral": "#374151",
          "base-100": "#1f2937",
          "info": "#38bdf8",
          "success": "#4ade80",
          "warning": "#fef08a",
          "error": "#ef4444",
        },
      },
    ],
  },
  plugins: [
    require("@tailwindcss/typography"),
    require('daisyui'),
    function ({ addComponents }) {
      addComponents({
        '.container': {
          maxWidth: '100%',
          '@screen sm': {
            maxWidth: '640px',
          },
          '@screen md': {
            maxWidth: '768px',
          },
          '@screen lg': {
            maxWidth: '1024px',
          },
          '@screen xl': {
            maxWidth: '1280px',
          },
        },
      })
    }
  ],
}