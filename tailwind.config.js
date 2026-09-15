/** @type {import('tailwindcss').Config} */
/**
 * Colores vía CSS variables (--rgb-*) para que Ajustes → Colores
 * siga funcionando en producción sin el CDN de Tailwind.
 */
const colorKeys = [
  'cyan', 'light-cyan', 'cyan-deep', 'brand-yellow', 'brand-orange',
  'teal', 'mint', 'sage', 'mist',
  'primary', 'primary-container', 'on-primary', 'on-primary-container',
  'primary-fixed', 'primary-fixed-dim', 'on-primary-fixed', 'on-primary-fixed-variant',
  'surface-tint', 'inverse-primary',
  'secondary', 'secondary-container', 'on-secondary', 'on-secondary-fixed',
  'on-secondary-container', 'on-secondary-fixed-variant', 'secondary-fixed', 'secondary-fixed-dim',
  'tertiary', 'tertiary-container', 'tertiary-fixed', 'tertiary-fixed-dim',
  'on-tertiary', 'on-tertiary-container', 'on-tertiary-fixed', 'on-tertiary-fixed-variant',
  'background', 'on-background', 'surface', 'surface-bright', 'surface-dim',
  'surface-container-lowest', 'surface-container-low', 'surface-container',
  'surface-container-high', 'surface-container-highest', 'surface-variant',
  'on-surface', 'on-surface-variant', 'outline', 'outline-variant',
  'inverse-surface', 'inverse-on-surface',
  'error', 'on-error', 'error-container', 'on-error-container',
];

const colors = Object.fromEntries(
  colorKeys.map((key) => [
    key,
    ({ opacityValue }) =>
      opacityValue === undefined
        ? `rgb(var(--rgb-${key}))`
        : `rgb(var(--rgb-${key}) / ${opacityValue})`,
  ])
);

module.exports = {
  content: [
    './*.php',
    './includes/**/*.php',
    './api/**/*.php',
    './assets/js/**/*.js',
  ],
  darkMode: 'class',
  theme: {
    extend: {
      colors,
      borderRadius: {
        DEFAULT: '0.5rem',
        lg: '0.75rem',
        xl: '1rem',
        '2xl': '1rem',
        '3xl': '1.5rem',
        full: '9999px',
      },
      spacing: {
        'section-padding-desktop': '80px',
        'section-padding-mobile': '32px',
        gutter: '24px',
        'container-max': '1280px',
        unit: '8px',
      },
      fontFamily: {
        'headline-lg': ['Lexend', 'sans-serif'],
        'display-lg-mobile': ['Lexend', 'sans-serif'],
        'body-md': ['Inter', 'sans-serif'],
        'label-md': ['Inter', 'sans-serif'],
        'label-sm': ['Inter', 'sans-serif'],
        'label-caps': ['Inter', 'sans-serif'],
        'headline-md': ['Lexend', 'sans-serif'],
        'display-lg': ['Lexend', 'sans-serif'],
        'body-lg': ['Inter', 'sans-serif'],
        sans: ['Inter', 'sans-serif'],
        display: ['Lexend', 'sans-serif'],
      },
      fontSize: {
        'body-md': ['16px', { lineHeight: '1.5', fontWeight: '400' }],
        'body-lg': ['18px', { lineHeight: '1.6', fontWeight: '400' }],
        'label-caps': ['12px', { lineHeight: '1', letterSpacing: '0.1em', fontWeight: '700' }],
        'headline-md': ['24px', { lineHeight: '1.3', fontWeight: '500' }],
        'display-lg-mobile': ['32px', { lineHeight: '1.2', fontWeight: '600' }],
        'display-lg': ['48px', { lineHeight: '1.1', letterSpacing: '-0.02em', fontWeight: '600' }],
      },
    },
  },
  plugins: [],
};
