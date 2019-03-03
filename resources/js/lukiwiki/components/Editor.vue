<template>
  <div>
    <b-button-toolbar aria-label="Editor Toolbar" class="align-items-baseline form-row" justify>
      <div class="col-12 col-md-4">
        <b-input-group>
          <b-input-group-text slot="prepend">
            <font-awesome-icon fas icon="file-signature"/>
          </b-input-group-text>
          <b-form-input
            v-model="page"
            name="page"
            autocomplete="off"
            v-b-tooltip
            title="Page Name"
          />
        </b-input-group>
      </div>
      <div class="col-md-8">
        <b-button-group>
          <b-button
            size="sm"
            variant="outline-secondary"
            v-b-tooltip
            title="Bold"
            v-on:click="replace('b')"
          >
            <font-awesome-icon fas fixed-width icon="bold"/>
          </b-button>
          <b-button
            size="sm"
            variant="outline-secondary"
            v-b-tooltip
            title="Italic"
            v-on:click="replace('i')"
          >
            <font-awesome-icon fas fixed-width icon="italic"/>
          </b-button>
          <b-button
            size="sm"
            variant="outline-secondary"
            v-b-tooltip
            title="Strike"
            v-on:click="replace('s')"
          >
            <font-awesome-icon fas fixed-width icon="strikethrough"/>
          </b-button>
          <b-button
            size="sm"
            variant="outline-secondary"
            v-b-tooltip
            title="Underline"
            v-on:click="replace('u')"
          >
            <font-awesome-icon fas fixed-width icon="underline"/>
          </b-button>
          <b-button
            size="sm"
            variant="outline-secondary"
            v-b-tooltip
            title="Code"
            v-on:click="replace('code')"
          >
            <font-awesome-icon fas fixed-width icon="code"/>
          </b-button>
          <b-button
            size="sm"
            variant="outline-secondary"
            v-b-tooltip
            title="Quotation"
            v-on:click="replace('q')"
          >
            <font-awesome-icon fas fixed-width icon="quote-left"/>
          </b-button>
        </b-button-group>
        <b-button-group>
          <b-button
            size="sm"
            variant="outline-secondary"
            v-b-tooltip
            title="Insert Link"
            v-on:click="replace('url')"
          >
            <font-awesome-icon fas fixed-width icon="link"/>
          </b-button>
          <b-button
            size="sm"
            variant="outline-secondary"
            v-b-tooltip
            title="Font Size"
            v-on:click="replace('size')"
          >
            <font-awesome-icon fas fixed-width icon="text-height"/>
          </b-button>
          <b-button
            size="sm"
            variant="outline-secondary"
            v-b-tooltip
            title="Insert Color"
            v-on:click="insert('color')"
          >
            <font-awesome-icon fas fixed-width icon="palette"/>
          </b-button>
        </b-button-group>
        <b-button-group>
          <b-button
            size="sm"
            variant="outline-secondary"
            v-b-tooltip
            title="Convert character reference"
            v-on:click="replace('ncr')"
          >&amp;#</b-button>
          <b-button
            size="sm"
            variant="outline-secondary"
            v-b-tooltip
            title="Hint"
            v-on:click="hint()"
          >
            <font-awesome-icon fas fixed-width icon="question-circle"/>
          </b-button>
        </b-button-group>
      </div>
    </b-button-toolbar>
    <codemirror
      ref="cm"
      name="source"
      v-model="source"
      v-bind:options="cmOption"
      @ready="onCmReady"
      @focus="onCmFocus"
      @input="onCmCodeChange"
      class="my-1"
    ></codemirror>
    <div class="form-row align-items-center d-flex" aria-label="Editor Footer">
      <div class="p-1">
        <b-form-checkbox
          switch
          v-model="keep_timestamp"
          name="keep_timestamp"
          value="1"
          unchecked-value="0"
        >Keep Timestamp</b-form-checkbox>
      </div>
      <div class="p-1">
        <b-input-group>
          <b-input-group-text slot="prepend">
            <font-awesome-icon fas icon="key"/>
          </b-input-group-text>
          <b-form-input
            name="password"
            type="password"
            v-model="key"
            autocomplete="off"
            placeholder="Password"
            v-bind:disabled="keep_timestamp == 0"
          />
        </b-input-group>
      </div>
      <div class="ml-auto px-1">
        <b-button
          variant="primary"
          type="submit"
          name="action"
          value="save"
          v-bind:disabled="keep_timestamp == 1 && key == ''"
        >
          <font-awesome-icon fas fixed-width icon="check" class="mr-1"/>Submit
        </b-button>
        <b-button variant="secondary" type="submit" name="action" value="cancel">
          <font-awesome-icon fas fixed-width icon="ban" class="mr-1"/>Cancel
        </b-button>
      </div>
    </div>
  </div>
</template>

<script>
// Button
import bButton from "bootstrap-vue/es/components/button/button";
// Button Group
import bButtonGroup from "bootstrap-vue/es/components/button-group/button-group";
// Button Toolbar
import bButtonToolbar from "bootstrap-vue/es/components/button-toolbar/button-toolbar";
// Form Checkbox
import bFormCheckbox from "bootstrap-vue/es/components/form-checkbox/form-checkbox";
// Form Checkbox group
import bFormCheckboxGroup from "bootstrap-vue/es/components/form-checkbox/form-checkbox-group";
// Form Group
import bFormGroup from "bootstrap-vue/es/components/form-group/form-group";
// Form Input
import bFormInput from "bootstrap-vue/es/components/form-input/form-input";
// Input Group
import bInputGroup from "bootstrap-vue/es/components/input-group/input-group";
import bInputGroupText from "bootstrap-vue/es/components/input-group/input-group-text";
// Tooltip
import vBTooltip from "bootstrap-vue/es/directives/tooltip/tooltip";

// FontAwesome
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome";
import { library } from "@fortawesome/fontawesome-svg-core";
import {
  faBan,
  faBold,
  faCheck,
  faCode,
  faFileSignature,
  faItalic,
  faKey,
  faLink,
  faPalette,
  faQuestionCircle,
  faQuoteLeft,
  faStrikethrough,
  faTextHeight,
  faUnderline
} from "@fortawesome/free-solid-svg-icons";
library.add(
  faBan,
  faBold,
  faCheck,
  faCode,
  faFileSignature,
  faItalic,
  faKey,
  faLink,
  faPalette,
  faQuestionCircle,
  faQuoteLeft,
  faStrikethrough,
  faTextHeight,
  faUnderline
);

// language
import "../codemirror_lukiwiki.js";
// active-line.js
import "codemirror/addon/selection/active-line.js";
// styleSelectedText
import "codemirror/addon/selection/mark-selection.js";
import "codemirror/addon/search/searchcursor.js";
// highlightSelectionMatches
import "codemirror/addon/scroll/annotatescrollbar.js";
import "codemirror/addon/search/matchesonscrollbar.js";
import "codemirror/addon/search/searchcursor.js";
import "codemirror/addon/search/match-highlighter.js";
// keyMap
//import "codemirror/mode/clike/clike.js";
import "codemirror/addon/edit/matchbrackets.js";
import "codemirror/addon/comment/comment.js";
import "codemirror/addon/dialog/dialog.js";
import "codemirror/addon/dialog/dialog.css";
import "codemirror/addon/search/searchcursor.js";
import "codemirror/addon/search/search.js";
import "codemirror/keymap/sublime.js";
// foldGutter
//import 'codemirror/addon/fold/foldgutter.css'
//import "codemirror/addon/fold/brace-fold.js";
//import "codemirror/addon/fold/comment-fold.js";
//import "codemirror/addon/fold/foldcode.js";
//import "codemirror/addon/fold/foldgutter.js";
//import "codemirror/addon/fold/indent-fold.js";
//import "codemirror/addon/fold/markdown-fold.js";
//import "codemirror/addon/fold/xml-fold.js";
// Show hint
import "codemirror/addon/hint/show-hint.js";
import "codemirror/addon/hint/show-hint.css";

export default {
  data() {
    let source, page;
    return {
      source: this.$slots.body[0].children[0].text || "",
      page: this.$slots.header[0].children[2].data.attrs.value,
      keep_timestamp: 0,
      key: "",
      cmOption: {
        tabSize: 4,
        foldGutter: true,
        styleActiveLine: true,
        lineNumbers: true,
        lineWrapping: true,
        line: true,
        keyMap: "sublime",
        mode: "lukiwiki",
        extraKeys: {
          F11(cm) {
            cm.setOption("fullScreen", !cm.getOption("fullScreen"));
          },
          Esc(cm) {
            if (cm.getOption("fullScreen")) cm.setOption("fullScreen", false);
          }
        }
      }
    };
  },
  created: function() {
    // 絵文字一覧を保存
    let emojiList = [];
    axios
      .get("/js/emoji.json")
      .then(response => {
        const data = response.data;
        for (let line in data) {
          emojiList.push({
            text: data[line],
            displayText: `${line} ${data[line]}`
          });
        }
      })
      .catch(error => console.error(error));

    this.emojiList = emojiList;
  },
  methods: {
    insert(v) {
      let ret = "";
      switch (v) {
        case "br":
          ret = "&br;" + "\n";
          break;
        case "color":
          //$('#color_palette').dialog('open');
          break;
        default:
          ret = "&(" + v + ");";
          break;
      }
      const doc = editor.getDoc();
      const cursor = doc.getCursor();
      this.$refs.cm.codemirror.replaceRange(ret, cursor);
    },
    replace(v) {
      let ret = "";
      const str = this.$refs.cm.codemirror.getSelection();
      if (str === "") {
        alert("Please select text.");
        return;
      }
      switch (v) {
        case "size":
          var val = prompt("font-size (rem)", "1");
          if (!val || !val.match(/\d+/)) {
            return;
          }
          ret = "&size(" + val + "){" + str + "};";
          break;
        case "ncr":
          var i, len;
          for (i = 0, len = str.length; i < len; i++) {
            ret += "&#" + str.charCodeAt(i) + ";";
          }
          break;
        case "b":
          ret = "''" + str + "''";
          break;
        case "i":
          ret = "'''" + str + "'''";
          break;
        case "u":
          ret = "__" + str + "__";
          break;
        case "s":
          ret = "~~" + str + "~~";
          break;
        case "code":
          ret = "`" + str + "`";
          break;
        case "q":
          ret = "``" + str + "``";
          break;

        case "url":
          //	var regex = "^s?https?://[-_.!~*'()a-zA-Z0-9;/?:@&=+$,%#]+$";
          var my_link = prompt("URL:", "https://");
          if (my_link !== null) {
            ret = "[" + str + "](" + my_link + ")";
          } else {
            return;
          }
          break;
        default:
          alert("error");
          return;
          break;
      }
      this.$refs.cm.codemirror.replaceSelection(ret);
    },
    hint() {},
    onCmReady(cm) {
      const emojiList = this.emojiList;
      //console.log(this.emojiList);
      cm.on("keypress", () => {
        // 絵文字メニュー
        // https://qiita.com/yymm@github/items/6aa5b869ef8c22683ccc
        CodeMirror.showHint(
          cm,
          function() {
            let cur = cm.getCursor(),
              token = cm.getTokenAt(cur);
            let start = token.start,
              end = cur.ch,
              word = token.string.slice(0, end - start);
            let ch = cur.ch,
              line = cur.line;
            let currentWord = token.string;

            while (ch-- > -1) {
              let t = cm.getTokenAt({ ch, line }).string;
              if (t === ":") {
                let filteredList = emojiList.filter(item => {
                  return item.text.indexOf(":" + currentWord) == 0
                    ? true
                    : false;
                });
                //console.log(filteredList);
                if (filteredList.length >= 1) {
                  return {
                    list: filteredList,
                    from: CodeMirror.Pos(line, ch),
                    to: CodeMirror.Pos(line, end)
                  };
                }
              }
              //currentWord = t + currentWord;
            }
          },
          { completeSingle: false }
        );
      });
    },
    onCmFocus(cm) {
      //console.log("the editor is focus!", cm);
    },
    onCmCodeChange(newCode) {
      //console.log("this is new code", newCode);
      //this.code = newCode;
      this.source = newCode;
    }
  },
  components: {
    "b-button": bButton,
    "b-button-group": bButtonGroup,
    "b-button-toolbar": bButtonToolbar,
    "b-form-checkbox": bFormCheckbox,
    "b-form-checkbox-group": bFormCheckboxGroup,
    "b-form-group": bFormGroup,
    "b-form-input": bFormInput,
    "b-input-group": bInputGroup,
    "b-input-group-text": bInputGroupText,
    "font-awesome-icon": FontAwesomeIcon
  },
  directives: {
    "b-tooltip": vBTooltip
  }
};
</script>

<style lang="scss">
</style>