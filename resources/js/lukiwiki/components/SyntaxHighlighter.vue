<script>
// CodemirrorによるSyntaxHilighter
import CodeMirror from 'codemirror/lib/codemirror';
require('codemirror/addon/runmode/runmode');

window.CodeMirror = CodeMirror;

// モードの読み込み先（ビルド時にnode_modules内のCodeMirrorのmodeからpublic/js内にコピーされる）
const modeUrl = '/js/codemirror/mode/';
const meta = document.createElement('script');
meta.src = modeUrl + 'meta.js';
const others = document.getElementsByTagName('script')[0];
others.parentNode.insertBefore(meta, others);

CodeMirror.modeUrl = modeUrl + '%N/%N.js';

//
let loading = {};

function splitCallback (cont, n) {
  let countDown = n;
  return function () {
    if (--countDown === 0) cont();
  };
}

function ensureDeps(mode, cont) {
  const deps = CodeMirror.modes[mode].dependencies;
  if (!deps) return cont();
  const missing = [];
  for (let i = 0; i < deps.length; ++i) {
    if (!CodeMirror.modes.hasOwnProperty(deps[i])) missing.push(deps[i]);
  }
  if (!missing.length) return cont();
  const split = splitCallback(cont, missing.length);
  for (let i = 0; i < missing.length; ++i)
    CodeMirror.requireMode(missing[i], split);
}

CodeMirror.requireMode = function(name, cont) {
  console.log(name);
  if (CodeMirror.modes.hasOwnProperty(name)) return ensureDeps(name, cont);
  if (loading.hasOwnProperty(name)) return loading[name].push(cont);

  CodeMirror.on(meta, 'load', function() {
    const mode = CodeMirror.findModeByName(name).mode;
    console.log(name, mode);

    const script = document.createElement('script');
    script.src = CodeMirror.modeUrl.replace(/%N/g, mode);
    const list = (loading[mode] = [cont]);

    CodeMirror.on(script, 'load', function() {
      ensureDeps(mode, function() {
        for (let i = 0; i < list.length; ++i) list[i]();
      });
    });

    others.parentNode.insertBefore(script, others);
  });
};

CodeMirror.autoLoadMode = function(instance, mode) {
  if (CodeMirror.modes.hasOwnProperty(mode)) return;

  CodeMirror.requireMode(mode, function() {
    instance.setOption('mode', instance.getOption('mode'));
  });
};
function textContent(node, out) {
  const isBlock = /^(p|li|div|h\\d|pre|blockquote|td)$/;
  if (node.nodeType === 3) return out.push(node.nodeValue);
  for (let ch = node.firstChild; ch; ch = ch.nextSibling) {
    textContent(ch, out);
    if (isBlock.test(node.nodeType)) out.push('\n');
  }
}

/*
const textareas = document.body.getElementsByTagName("textarea");
for (let i = 0; i < textareas.length; ++i) {
  const options = {};
  const node = textareas[i];
  const mode = node.getAttribute("data-lang") || false;
  options.tabsize = node.getAttribute("data-tab-size") || 4;
  options.state = node.getAttribute("data-state") || null;
  if (!mode) continue;

  const height = node.getAttribute("data-height") || "auto";

  CodeMirror.requireMode(mode, function() {
    let cm = CodeMirror.fromTextArea(node, {
      lineNumbers: true,
      mode: mode
      //viewportMargin: Infinity
    });
    if (height !== "auto") {
      cm.setSize(null, height);
    }
  });
}
*/
export default {
  bind: element => {
    const data = element.dataset;

    let options = {};
    let text = [];
    let mode = data.lang || null;
    options.tabsize = data.tabSize || 4;
    options.state = data.state || null;
    options.lineNumbers = data.lineNumbers || true;

    if (element.tagName === 'TEXTAREA') {
      options.mode = mode;
      console.warn(
        'lw-sh:SyntaxHighlighting textarea tag does not supported. please use vue-codemirror directly.'
      );
      /*
      CodeMirror.requireMode(mode, function() {
        let cm = CodeMirror.fromTextArea(element, options);
        if (height !== "auto") {
          cm.setSize(null, data.height || "auto");
        }
      });
*/
      return;
    }

    textContent(element, text);

    if (text.length !== 0){
      element.innerHTML = '';
    }
    element.className += ' cm-s-default';
    element.style.height = data.height || 'auto';

    CodeMirror.requireMode(mode, function() {
      CodeMirror.runMode(text.join(''), mode, element, options);
    });
  }
};
</script>