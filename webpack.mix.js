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
  .sass('resources/sass/default.scss', 'public/css')
  .sass('resources/sass/dashboard.scss', 'public/css')
  .sass('resources/sass/themes/cerulean.scss', 'public/css')
  .sass('resources/sass/themes/cosmo.scss', 'public/css')
  .sass('resources/sass/themes/cyborg.scss', 'public/css')
//  .sass('resources/sass/themes/darkly.scss', 'public/css')
  .sass('resources/sass/themes/flatly.scss', 'public/css')
  .sass('resources/sass/themes/journal.scss', 'public/css')
  .sass('resources/sass/themes/litera.scss', 'public/css')
  .sass('resources/sass/themes/lumen.scss', 'public/css')
  .sass('resources/sass/themes/lux.scss', 'public/css')
  .sass('resources/sass/themes/materia.scss', 'public/css')
  .sass('resources/sass/themes/minty.scss', 'public/css')
//  .sass('resources/sass/themes/pulse.scss', 'public/css')
  .sass('resources/sass/themes/sandstone.scss', 'public/css')
  .sass('resources/sass/themes/simplex.scss', 'public/css')
//  .sass('resources/sass/themes/sketchy.scss', 'public/css')
  .sass('resources/sass/themes/slate.scss', 'public/css')
  .sass('resources/sass/themes/solar.scss', 'public/css')
  .sass('resources/sass/themes/spacelab.scss', 'public/css')
  .sass('resources/sass/themes/superhero.scss', 'public/css')
  .sass('resources/sass/themes/united.scss', 'public/css')
  .sass('resources/sass/themes/yeti.scss', 'public/css')
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