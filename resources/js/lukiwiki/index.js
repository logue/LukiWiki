/**
 * LukiWiki処理系
 *
 * @author    Logue <logue@hotmail.co.jp>
 * @copyright 2018-2019 Logue
 * @license   MIT
 */
import Vue from 'vue/dist/vue.common.dev';

import '~/bootstrap/scss/bootstrap.scss';
/** */
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

Vue.component('FontAwesomeIcon', FontAwesomeIcon);
Vue.config.productionTip = false;

// Bootstrap Vue
import { VBTooltip, BAlert, BButton, BModal } from 'bootstrap-vue';
// ツールチップ
Vue.directive('b-tooltip', VBTooltip);
// アラート
Vue.component('BAlert', BAlert);
// ボタン
Vue.component('BButton', BButton);
// モーダル
Vue.component('BModal', BModal);
/** */
// Vue Codemirror
import VueCodemirror from 'vue-codemirror';
// require more codemirror resource...

// you can set default global options and events when use
Vue.use(
  VueCodemirror /* {
  options: { theme: 'base16-dark', ... },
  events: ['scroll', ...]
} */
);
// LukiWiki簡易シンタックスハイライタ
// import './codemirror_lukiwiki';

// 独自タグ
// コンポーネント（作用する独自タグ）の登録
// 例：<lw-editor>...<lw-editor>
Vue.component('LwCalendar', () => import('./components/Calendar.vue'));
Vue.component('LwComposer', () => import('./components/Composer.vue'));
Vue.component('LwNavbar', () => import('./components/Navbar.vue'));
Vue.component('LwEditor', () => import('./components/Editor.vue'));
Vue.component('LwMedia', () => import('./components/Media.vue'));
Vue.component('LwMerge', () => import('./components/Merge.vue'));
Vue.component('LwBreadcrumb', () => import('./components/Breadcrumb.vue'));
Vue.component('LwSocial', () => import('./components/Social.vue'));

// ディレクティブ（作用する独自属性）の登録
// 例：<pre v-lw-sh>...</pre>
Vue.directive('lw-passage', () => import('./components/Passage.vue'));
Vue.directive('lw-sh', () => import('./components/SyntaxHighlighter.vue'));
