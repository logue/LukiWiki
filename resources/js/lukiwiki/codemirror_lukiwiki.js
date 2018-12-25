// CodeMirror, copyright (c) by Marijn Haverbeke and others
// Distributed under an MIT license: http://codemirror.net/LICENSE

// LukiWiki for CodeMirror
// Author: Logue (http://github.com/logue)

(function (mod) {
    if (typeof exports === 'object' && typeof module === 'object') { // CommonJS
        mod(require('codemirror/lib/codemirror'), require('codemirror/addon/mode/simple'))
    } else if (typeof define === 'function' && define.amd) { // AMD
        define(['codemirror/lib/codemirror', 'codemirror/addon/mode/simple'], mod)
    } else { // Plain browser env
        mod(window.CodeMirror)
    }
})(function (CodeMirror) {
    'use strict'

    CodeMirror.defineSimpleMode('lukiwiki', {
        start: [
            // ul, ol, li, dt,
            {
                regex: /^(-|\+|:){1,3}/,
                token: 'def'
            },
            // table
            {
                regex: /\|/,
                token: 'qualifier'
            },
            // h2~h6
            {
                regex: /^\*{1,5}.+$/,
                token: 'keyword'
            },
            // Block Plugin
            {
                regex: /^#[a-z_]+\([^\)]+\)?(\{[^\)]+\})?/,
                token: 'string'
            },
            // Blacket
            {
                // regex: /\[\[(?:[A-Z](?:[a-z]|\xc3[\x9f-\xbf])+(?:[A-Z](?:[a-z]|\xc3[\x9f-\xbf])+)+)(?!\w)\]\]/,
                regex: /\[{2}[^\]]+?\]{2}/,
                token: 'bracket'
            },
            // Blockquote
            {
                regex: /^\>{1,3}.+$/,
                token: 'quote'
            },
            // hr / plugin
            {
                regex: /^-{4,}|^#(.+)$/,
                token: 'hr'
            },
            // Strings
            {
                regex: /\&(#[0-9]+|#x[0-9a-f]+|(?=[a-zA-Z0-9]{2,8})(?:apos|amp|lt|gt|quot))\;/,
                token: 'string'
            },

            // Plugin
            {
                regex: /&[a-zA-Z_]+[^;]*;/,
                token: 'string2'
            },
            // Align
            {
                regex: /^(LEFT|CENTER|RIGHT|JUSTIFY):$/,
                token: 'def'
            },

            // Inline
            {
                regex: /(_|\@|\'|\%){1,3}.+(_|\@|\'|\%){1,3}/,
                token: 'string3'
            },
            // Inline
            {
                regex: /(COLOR|SIZE|SUP|SUB|LANG|ABBR)\(.+\)\{.+\}/,
                token: 'atom'
            },

            // Note
            {
                regex: /\({2}[^\)]+?\){2}/,
                token: 'attribute'
            },

            // br
            {
                regex: /~|&amp;br;$/,
                token: 'hr'
            }
            // pre
            //   {
            //     regex: /^ .+/,
            //     token: 'number'
            //   },

            // Comment
            // {
            //  regex: /\/\//,
            //  token: 'comment',
            //  next: 'comment'
            // }
        ],
        comment: [{
            regex: /\/\*/,
            token: 'comment',
            next: 'start'
        },
        {
            regex: /\*\//,
            token: 'comment'
        }
        ],
        pre: [{
            regex: /```(.*?):?(.*?)/,
            token: 'comment',
            next: 'start'
        },
        {
            regex: /```/,
            token: 'comment'
        }
        ],
        meta: {
            electricInput: /^{{|}}$/,
            blockCommentStart: '/*',
            blockCommentEnd: '*/',
            lineComment: ['//']
        }
    })

    CodeMirror.defineMIME('text/lukiwiki', 'lukiwiki')
})