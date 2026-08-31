/** @type {import('tailwindcss').Config} */
export default {
  content: [
    './resources/**/*.blade.php',
    './resources/**/*.js',
    './app/**/*.php',
  ],
  theme: {
    extend: {
      colors: {
        houseBlack: '#050505',
        paper: '#F7F6F2',
        bone: '#EEEAE2',
        graphite: '#171717',
        muted: '#74716A',
        champagne: '#B49A70',
      },
      fontFamily: {
        sans: ['Arial', 'Helvetica Neue', 'Helvetica', 'sans-serif'],
        serif: ['Cormorant Garamond', 'Georgia', 'serif'],
      },
      maxWidth: {
        house: '1600px',
      },
      letterSpacing: {
        ui: '.12em',
        wideui: '.18em',
      },
    },
  },
  plugins: [require('@tailwindcss/forms')],
};
