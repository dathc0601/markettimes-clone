import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';
import typography from '@tailwindcss/typography';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
            },
            aspectRatio: {
                '3/2': '3 / 2',
            },
            colors: {
                primary: '#037A84',
                'primary-dark': '#025a62',
                accent: '#BE1E2D',
                'accent-dark': '#9a1825',
                beige: '#F6F2E7',
                'beige-dark': '#EAE7DB',
            },
            screens: {
                'lg': '992px',
            },
        },
    },

    plugins: [forms, typography],
};
