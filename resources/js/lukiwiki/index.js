/**
 * LukiWiki処理系
 *
 * @author    Logue <logue@hotmail.co.jp>
 * @copyright 2018 Logue
 * @license   MIT
 */

import VueCodemirror from 'vue-codemirror'

// require more codemirror resource...

// you can set default global options and events when use
Vue.use(VueCodemirror, /* { 
  options: { theme: 'base16-dark', ... },
  events: ['scroll', ...]
} */)

window.CodeMirror = require('codemirror/lib/codemirror')
const querystring = require('query-string')
window.qs = querystring.parse(location.search);
require('./codemirror_lukiwiki')
require('./codemirror_syntaxhilighter')
require('./tooltip')


if (window.qs.action === 'edit'){
    Vue.component('lw-editor', require('./components/Editor.vue'));
   // require('./edit.js')
}