/* eslint-disable require-jsdoc */
/* eslint-disable no-import-assign */
/* eslint-disable no-prototype-builtins */
/**
 * CodeMirrorでシンタックスハイライター
 *
 * @author    Logue <logue@hotmail.co.jp>
 * @copyright 2018 Logue
 * @license   MIT
 */

import * as CodeMirror from 'codemirror/lib/codemirror';

// CodeMirror.modeURL = 'https://cdnjs.cloudflare.com/ajax/libs/codemirror/' + CodeMirror.version + '/mode/%N/%N.js'
// eslint-disable-next-line no-import-assign
CodeMirror.modeURL = 'js/codemirror/mode/%N/%N.js';

const loading = {};

/**
 * Helper for loading multiple dependencies.
 * @param {Function} cont
 * @param {number} n
 * @return {function}
 */
function splitCallback(cont, n) {
  let countDown = n;
  return () => {
    if (--countDown === 0) cont();
  };
}

/**
 * Ensure that dependencies are loaded.
 * @param {string} mode
 * @param {Function} cont
 * @return {void}
 */
function ensureDeps(mode, cont) {
  const deps = CodeMirror.modes[mode].dependencies;
  if (!deps) return cont();
  const missing = [];
  for (const dep in deps) {
    if (!CodeMirror.modes.hasOwnProperty(dep)) {
      missing.push(dep);
    }
  }
  if (!missing.length) return cont();
  const split = splitCallback(cont, missing.length);
  for (const dep in missing) {
    if (!CodeMirror.modes.hasOwnProperty(dep)) {
      CodeMirror.requireMode(dep, split);
    }
  }
}

CodeMirror.requireMode = (mode, cont) => {
  if (typeof mode !== 'string') mode = mode.name;
  if (CodeMirror.modes.hasOwnProperty(mode)) return ensureDeps(mode, cont);
  if (loading.hasOwnProperty(mode)) return loading[mode].push(cont);

  const file = CodeMirror.modeURL.replace(/%N/g, mode);

  const script = document.createElement('script');
  script.src = file;
  const others = document.getElementsByTagName('script')[0];
  const list = (loading[mode] = [cont]);

  CodeMirror.on(script, 'load', () => {
    ensureDeps(mode, () => {
      list.each((e) => e());
    });
  });

  others.parentNode.insertBefore(script, others);
};

CodeMirror.autoLoadMode = (instance, mode) => {
  if (CodeMirror.modes.hasOwnProperty(mode)) return;

  CodeMirror.requireMode(mode, () => {
    instance.setOption('mode', instance.getOption('mode'));
  });
};

const isBlock = /^(p|li|div|h\\d|pre|blockquote|td)$/;

function textContent(node, out) {
  if (node.nodeType === 3) return out.push(node.nodeValue);
  for (let ch = node.firstChild; ch; ch = ch.nextSibling) {
    textContent(ch, out);
    if (isBlock.test(node.nodeType)) out.push('\n');
  }
}

CodeMirror.colorize = function (collection, defaultMode) {
  if (!collection) collection = document.body.getElementsByTagName('pre');

  Array.from(collection).forEach((node) => {
    const options = {};
    const mode = node.getAttribute('data-lang') || defaultMode;
    options.tabsize = node.getAttribute('data-tab-size') || 4;
    options.state = node.getAttribute('data-state') || null;

    if (!mode) return;

    const text = [];
    textContent(node, text);
    node.innerHTML = '';

    CodeMirror.requireMode(mode, function () {
      CodeMirror.runMode(text.join(''), mode, node, options);
    });
    node.className += ' cm-s-default';
  });
};

CodeMirror.runMode = (string, modespec, callback, options) => {
  const mode = CodeMirror.getMode(CodeMirror.defaults, modespec);
  if (mode.name === 'null') {
    console.warn('CodeMirror: Could not load run mode:', modespec);
  } else {
    console.info('CodeMirror: loading', mode.name);
  }

  if (callback.appendChild) {
    const tabSize = (options && options.tabSize) || CodeMirror.defaults.tabSize;
    const node = callback;
    let col = 0;
    node.innerHTML = '';
    callback = (text, style) => {
      if (text === '\n') {
        // Emitting LF or CRLF on IE8 or earlier results in an incorrect display.
        // Emitting a carriage return makes everything ok.
        node.appendChild(document.createTextNode(text));
        col = 0;
        return;
      }
      let content = '';
      // replace tabs
      for (let pos = 0; ; ) {
        const idx = text.indexOf('\t', pos);
        if (idx === -1) {
          content += text.slice(pos);
          col += text.length - pos;
          break;
        } else {
          col += idx - pos;
          content += text.slice(pos, idx);
          const size = tabSize - (col % tabSize);
          col += size;
          for (let i = 0; i < size; ++i) content += ' ';
          pos = idx + 1;
        }
      }

      if (style) {
        const sp = node.appendChild(document.createElement('span'));
        sp.className = 'cm-' + style.replace(/ +/g, ' cm-');
        sp.appendChild(document.createTextNode(content));
      } else {
        node.appendChild(document.createTextNode(content));
      }
    };
  }

  const lines = CodeMirror.splitLines(string);
  const state = (options && options.state) || CodeMirror.startState(mode);
  for (let i = 0, e = lines.length; i < e; ++i) {
    if (i) {
      callback('\n');
    }
    const stream = new CodeMirror.StringStream(lines[i]);
    if (!stream.string && mode.blankLine) mode.blankLine(state);
    while (!stream.eol()) {
      const style = mode.token(stream, state);
      callback(stream.current(), style, i, stream.start, state);
      stream.start = stream.pos;
    }
  }
};

setTimeout(function () {
  CodeMirror.colorize();
}, 20);

const textareas = document.body.getElementsByTagName('textarea');
Array.from(textareas).forEach((node) => {
  const options = {};
  const mode = node.getAttribute('data-lang') || false;
  options.tabsize = node.getAttribute('data-tab-size') || 4;
  options.state = node.getAttribute('data-state') || null;
  if (!mode) return;

  const height = node.getAttribute('data-height') || 'auto';

  CodeMirror.requireMode(mode, () => {
    const cm = CodeMirror.fromTextArea(node, {
      lineNumbers: true,
      mode: mode,
      // viewportMargin: Infinity
    });
    if (height !== 'auto') {
      cm.setSize(null, height);
    }
  });
});

window.CodeMirror = CodeMirror;
export default CodeMirror;
