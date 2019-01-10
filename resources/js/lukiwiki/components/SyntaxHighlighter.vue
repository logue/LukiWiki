<script>
// CodemirrorによるSyntaxHilighter
import CodeMirror from "codemirror/lib/codemirror";
// モードの読み込み先（ビルド時にnode_modules内のCodeMirrorのmodeからpublic/js内にコピーされる）
CodeMirror.modeURL = "js/codemirror/mode/%N/%N.js";
// 読み込み済みのmode格納用変数
let loading = {};

function splitCallback(cont, n) {
  let countDown = n;
  return function() {
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
CodeMirror.colorize = function(collection, defaultMode) {
  if (!collection) collection = document.body.getElementsByTagName("pre");

  for (let i = 0; i < collection.length; ++i) {
    const options = {};
    const node = collection[i];
    const mode = node.getAttribute("data-lang") || defaultMode;
    options.tabsize = node.getAttribute("data-tab-size") || 4;
    options.state = node.getAttribute("data-state") || null;

    if (!mode) continue;

    const text = [];
    textContent(node, text);
    node.innerHTML = "";

    CodeMirror.requireMode(mode, function() {
      CodeMirror.runMode(text.join(""), mode, node, options);
    });
    node.className += " cm-s-default";
  }
};
*/

// https://codemirror.net/addon/runmode/runmode.js
CodeMirror.runMode = function(string, modespec, element, options) {
  const mode = CodeMirror.getMode(CodeMirror.defaults, modespec);
  let callback = element;
  if (mode.name === "null") {
    console.warn("CodeMirror: Could not load run mode:", modespec);
  } else {
    console.info("CodeMirror: loading", mode.name);
  }

  if (element.appendChild) {
    const tabSize = (options && options.tabSize) || CodeMirror.defaults.tabSize;
    const node = element;
    let col = 0;
    node.innerHTML = "";
    callback = function(text, style) {
      if (text === "\n") {
        // Emitting LF or CRLF on IE8 or earlier results in an incorrect display.
        // Emitting a carriage return makes everything ok.
        node.appendChild(document.createTextNode(text));
        col = 0;
        return;
      }
      let content = "";
      // replace tabs
      for (let pos = 0; ; ) {
        const idx = text.indexOf("\t", pos);
        if (idx === -1) {
          content += text.slice(pos);
          col += text.length - pos;
          break;
        } else {
          col += idx - pos;
          content += text.slice(pos, idx);
          const size = tabSize - (col % tabSize);
          col += size;
          for (let i = 0; i < size; ++i) content += " ";
          pos = idx + 1;
        }
      }

      if (style) {
        const sp = node.appendChild(document.createElement("span"));
        sp.className = "cm-" + style.replace(/ +/g, " cm-");
        sp.appendChild(document.createTextNode(content));
      } else {
        node.appendChild(document.createTextNode(content));
      }
    };
  }

  const lines = CodeMirror.splitLines(string),
    state = (options && options.state) || CodeMirror.startState(mode);
  for (let i = 0, e = lines.length; i < e; ++i) {
    if (i) {
      callback("\n");
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
/*
setTimeout(function() {
  CodeMirror.colorize();
}, 20);

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

    textContent(element, text);

    if (element.tagName === "TEXTAREA") {
      console.warn(
        "SyntaxHighlight to textarea tag does not supported. please use vue-codemirror directly."
      );
      return;
    }

    element.innerHTML = "";
    element.className += " cm-s-default";
    element.style.height = data.height || "auto";

    CodeMirror.requireMode(mode, function() {
      CodeMirror.runMode(text.join(""), mode, element, options);
    });
  }
};
</script>