/**
 * LukiWiki処理系
 *
 * @author    Logue <logue@hotmail.co.jp>
 * @copyright 2018 Logue
 * @license   MIT
 */
window.CodeMirror = require('codemirror/lib/codemirror')
const querystring = require('query-string')
window.qs = querystring.parse(location.search);
require('./codemirror_lukiwiki')
require('./codemirror_syntaxhilighter')
require('./tooltip')

if (window.qs.action === 'edit'){
    require('./edit.js')
}