const mix = require('laravel-mix')
const minify = require('minify-html-webpack-plugin')
require('laravel-mix-workbox')

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel applications. By default, we are compiling the CSS
 | file for the application as well as bundling up all the JS files.
 |
 */

mix.webpackConfig({
    stats: {
        warnings: false
    },
    plugins: [
        new minify({
            afterBuild: true,
            src: './storage/framework/views',
            dest: './storage/framework/views',
            ignoreFileNameRegex: /\.(gitignore)$/,
            rules: {
                useShortDoctype: true,
                collapseBooleanAttributes: true,
                collapseWhitespace: true,
                removeAttributeQuotes: true,
                removeComments: true,
                minifyJS: true
            }
        })
    ]
})

mix.vue()
    .webpackConfig(require('./webpack.config'))
    .js('resources/js/app.js', 'public/js')
    .postCss('resources/css/app.css', 'public/css', [
        require('postcss-import'),
        require('tailwindcss')
    ])
    .minify('public/js/app.js', 'public/js')
    .minify('public/css/app.css', 'public/css')
    .sourceMaps()
    .generateSW({
        runtimeCaching: [{
            handler: 'NetworkFirst'
        }],
        maximumFileSizeToCacheInBytes: 10000000,
        skipWaiting: true,
        cleanupOutdatedCaches: true,
        sourcemap: true
    })

if (mix.inProduction()) {
    mix.version()
}

mix.disableNotifications()
