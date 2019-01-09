/**
 * LukiWiki処理系
 *
 * @author    Logue <logue@hotmail.co.jp>
 * @copyright 2018 Logue
 * @license   MIT
*/
import BootstrapVue from 'bootstrap-vue'
Vue.use(BootstrapVue);

window.CodeMirror = require('codemirror/lib/codemirror');
import VueCodemirror from 'vue-codemirror';
// require more codemirror resource...

// you can set default global options and events when use
Vue.use(VueCodemirror, /* {
  options: { theme: 'base16-dark', ... },
  events: ['scroll', ...]
} */)


require('./codemirror_lukiwiki');
require('./codemirror_syntaxhilighter');
//require('./tooltip');

Vue.component('lw-editor', require('./components/Editor.vue'));
Vue.component('lw-navbar', require('./components/Navbar.vue'));
Vue.directive('passage', require('./components/Passage.vue'))