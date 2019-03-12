const mix = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

mix.js('resources/js/app.js', 'public/js')
  .sass('resources/sass/app.scss', 'public/css')
  .sass('resources/sass/admin.scss', 'public/css')
  .copy('node_modules/codemirror/LICENSE', 'public/js/codemirror/LICENSE')
  .copyDirectory('node_modules/codemirror/mode', 'public/js/codemirror/mode')
  .extract([
    'lodash',
    'axios',
    'query-string',
    'codemirror',
    'vue',
    'bootstrap-vue',
    'vue-codemirror',
    '@fortawesome/vue-fontawesome',
    '@fortawesome/fontawesome-svg-core'
  ]);

if (mix.inProduction()) {
  mix.version();
} else {
  mix.sourceMaps();
}