/**
 * LukiWiki処理系
 *
 * @author    Logue <logue@hotmail.co.jp>
 * @copyright 2018-2019 Logue
 * @license   MIT
 */
import Vue from 'vue';

// Register global
window.qs = require('query-string').parse(location.search);

/*****************************************************************************/
// Vue FontAwesome

import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { library } from '@fortawesome/fontawesome-svg-core';
import { fab } from '@fortawesome/free-brands-svg-icons';
import { fas } from '@fortawesome/free-solid-svg-icons';
import { far } from '@fortawesome/free-regular-svg-icons';
library.add(fab, fas, far);
// 使用するアイコン
/*
import {
  faThumbtack,
  faEnvelope,
  faPhone,
  faGlobe,
  faExternalLinkAlt
} from '@fortawesome/free-solid-svg-icons';

library.add(
  // Generic
  faThumbtack,
  faEnvelope,
  faPhone,
  faGlobe,
  faExternalLinkAlt
);
*/

Vue.component('font-awesome-icon', FontAwesomeIcon);
Vue.config.productionTip = false;

/*****************************************************************************/
// Bootstrap Vue

import {VBTooltip, BAlert, BButton, BModal} from 'bootstrap-vue';
// ツールチップ
Vue.directive('b-tooltip', VBTooltip);
// アラート
Vue.component('b-alert', BAlert);
// ボタン
Vue.component('b-button', BButton);
// モーダル
Vue.component('b-modal', BModal);
/*****************************************************************************/
// Vue Codemirror
import VueCodemirror from 'vue-codemirror';
// require more codemirror resource...

// you can set default global options and events when use
Vue.use(VueCodemirror, /* {
  options: { theme: 'base16-dark', ... },
  events: ['scroll', ...]
} */);
// LukiWiki簡易シンタックスハイライタ
require('./codemirror_lukiwiki');

/*****************************************************************************/
// 独自タグ

// コンポーネント（作用する独自タグ）の登録
// 例：<lw-editor>...<lw-editor>
Vue.component('lw-calendar', require('./components/Calendar.vue').default);
Vue.component('lw-composer', require('./components/Composer.vue').default);
Vue.component('lw-editor', require('./components/Editor.vue').default);
Vue.component('lw-navbar', require('./components/Navbar.vue').default);
Vue.component('lw-media', require('./components/Media.vue').default);
Vue.component('lw-merge', require('./components/Merge.vue').default);
Vue.component('lw-breadcrumb', require('./components/Breadcrumb.vue').default);
Vue.component('lw-social', require('./components/Social.vue').default);

// ディレクティブ（作用する独自属性）の登録
// 例：<pre v-lw-sh>...</pre>
//Vue.directive('lw-passage', require('./components/Passage.vue').default);
Vue.directive('lw-sh', require('./components/SyntaxHighlighter.vue').default);