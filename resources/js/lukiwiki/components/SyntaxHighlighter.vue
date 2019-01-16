<script>
// CodemirrorによるSyntaxHilighter
// モードの読み込み先（ビルド時にnode_modules内のCodeMirrorのmodeからpublic/js内にコピーされる）
CodeMirror.modeURL = "/js/codemirror/mode/%N/%N.js";

import CodeMirror from "codemirror/lib/codemirror";
require("codemirror/addon/runmode/runmode");

//
let loading = {};

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

CodeMirror.requireMode = function(mode, cont) {
  if (typeof mode !== "string") mode = mode.name;
  if (CodeMirror.modes.hasOwnProperty(mode)) return ensureDeps(mode, cont);
  if (loading.hasOwnProperty(mode)) return loading[mode].push(cont);

  const file = CodeMirror.modeURL.replace(/%N/g, mode);

  const script = document.createElement("script");
  script.src = file;
  const others = document.getElementsByTagName("script")[0];
  const list = (loading[mode] = [cont]);

  CodeMirror.on(script, "load", function() {
    ensureDeps(mode, function() {
      for (let i = 0; i < list.length; ++i) list[i]();
    });
  });

  others.parentNode.insertBefore(script, others);
};

CodeMirror.autoLoadMode = function(instance, mode) {
  if (CodeMirror.modes.hasOwnProperty(mode)) return;

  CodeMirror.requireMode(mode, function() {
    instance.setOption("mode", instance.getOption("mode"));
  });
};
function textContent(node, out) {
  const isBlock = /^(p|li|div|h\\d|pre|blockquote|td)$/;
  if (node.nodeType === 3) return out.push(node.nodeValue);
  for (let ch = node.firstChild; ch; ch = ch.nextSibling) {
    textContent(ch, out);
    if (isBlock.test(node.nodeType)) out.push("\n");
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
    let mode = data.lang || "text";
    options.tabsize = data.tabSize || 4;
    options.state = data.state || null;
    options.lineNumbers = data.lineNumbers || true;

    if (element.tagName === "TEXTAREA") {
      options.mode = mode;
      console.warn(
        "lw-sh:SyntaxHighlighting textarea tag does not supported. please use vue-codemirror directly."
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

    element.innerHTML = "";
    element.className += " cm-s-default";
    element.style.height = data.height || "auto";

    CodeMirror.requireMode(mode, function() {
      CodeMirror.runMode(text.join(""), mode, element, options);
    });
  }
};
</script>