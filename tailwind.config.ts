/** @type {import('tailwindcss').Config} */
module.exports = {
	content: [
	  './src/**/*.{js,ts,svelte}',
	  './node_modules/flowbite/**/*.js',
	],
	theme: {
	  extend: {
		fontFamily: {
			'libre-franklin': ['Libre Franklin', 'sans-serif'],
			'mango-grotesque': ['Mango Grotesque', 'sans-serif'],
			'pp-neue': ['"PP Neue Montreal"', 'sans-serif'],
		  },
	  },
	},
	plugins: [
	  require('flowbite/plugin'),
	],
  }
