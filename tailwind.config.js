const defaultTheme = require('tailwindcss/defaultTheme');

module.exports = {
    mode: 'jit',
    purge: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './vendor/laravel/jetstream/**/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
        './node_modules/@protonemedia/inertiajs-tables-laravel-query-builder/**/*.{js,vue}'
    ],
    theme: {
        extend: {
            fontFamily: {
                sans: ['Nunito Sans', ...defaultTheme.fontFamily.sans]
            }
        },
        screens: {
            'sm': '640px',
            'md': '848px',
            'lg': '1024px',
            'xl': '1280px',
            '2xl': '1536px'
        }
    },
    variants: {
        textColor: ['disabled'],
        backgroundColor: ['disabled'],
        borderColor: ['disabled'],
        opacity: ['disabled'],
        cursor: ['disabled']
    },
    plugins: [
        require('@tailwindcss/forms'),
        require('@tailwindcss/typography'),
        require('tailwind-scrollbar')
    ]
};
