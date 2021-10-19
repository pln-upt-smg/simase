const minify = require('minify-html-webpack-plugin');
const mix = require('laravel-mix');

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
});

mix.js('resources/js/app.js', 'public/js').vue()
    .postCss('resources/css/app.css', 'public/css', [
        require('postcss-import'),
        require('tailwindcss'),
    ])
    .webpackConfig(require('./webpack.config'));

if (mix.inProduction()) {
    mix.version();
} else {
    mix.browserSync({
        proxy: 'stocktake-web.test',
        host: 'stocktake-web.test',
        open: 'external'
    });
}
