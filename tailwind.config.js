/** @type {import('tailwindcss').Config} config */
const config = {
  content: ['./index.php', './app/**/*.php', './resources/**/*.{php,vue,js}'],
  theme: {
    fontFamily: {
      heading: ['Gotham Bold', 'sans-serif'],
      body: ['Gotham Light', 'sans-serif'],
      cta: ['Gotham Book', 'sans-serif'],
    },
    extend: {
      colors: {
        maroon: '#221418',
        maroonLight: '#2c1f21',
        sand: '#aa8471',
        lightSand: '#EED1C0',
        offWhite: '#f9f9f9',
      }, // Extend Tailwind's default colors
    },
  },
  plugins: [],
};

export default config;
