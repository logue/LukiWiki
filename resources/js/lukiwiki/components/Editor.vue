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
    <codemirror v-model="editor" :options="cmOption" ref="cm" class="my-1"></codemirror>
    <div class="form-row align-items-center" aria-label="Editor Footer">
      <div class="col-md-3 col-sm-6">
        <b-form-checkbox
          id="keep_timestamp"
          v-model="keep_timestamp"
          value="1"
          unchecked-value="0"
        >Keep Timestamp</b-form-checkbox>
      </div>
      <div class="col-md col-sm-6">
        <b-input-group>
          <b-input-group-text slot="prepend">
            <font-awesome-icon fas icon="key"/>
          </b-input-group-text>
          <b-form-input
            name="password"
            autocomplete="off"
            v-b-tooltip
            title="Password"
            v-bind:disabled="keep_timestamp === 0"
          />
        </b-input-group>
      </div>
      <div class="col-md-4 col-sm-12 text-right mr-0 mt-1 mt-md-0">
        <b-button variant="primary" type="submit" name="action" value="post">
          <font-awesome-icon fas fixed-width icon="check"/>Submit
        </b-button>
        <b-button variant="secondary" type="submit" name="action" value="cancel">
          <font-awesome-icon fas fixed-width icon="ban"/>Cancel
        </b-button>
      </div>
    </div>
  </div>
</template>

<script>
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome";
import { library } from "@fortawesome/fontawesome-svg-core";
import {
  faFileSignature,
  faBold,
  faItalic,
  faStrikethrough,
  faUnderline,
  faCode,
  faQuoteLeft,
  faLink,
  faTextHeight,
  faPalette,
  faQuestionCircle,
  faKey,
  faCheck,
  faBan
} from "@fortawesome/free-solid-svg-icons";
library.add(
  faFileSignature,
  faBold,
  faItalic,
  faStrikethrough,
  faUnderline,
  faCode,
  faQuoteLeft,
  faLink,
  faTextHeight,
  faPalette,
  faQuestionCircle,
  faKey,
  faCheck,
  faBan
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
import "codemirror/mode/clike/clike.js";
import "codemirror/addon/edit/matchbrackets.js";
import "codemirror/addon/comment/comment.js";
import "codemirror/addon/dialog/dialog.js";
import "codemirror/addon/dialog/dialog.css";
import "codemirror/addon/search/searchcursor.js";
import "codemirror/addon/search/search.js";
import "codemirror/keymap/sublime.js";
// foldGutter
//import 'codemirror/addon/fold/foldgutter.css'
import "codemirror/addon/fold/brace-fold.js";
import "codemirror/addon/fold/comment-fold.js";
import "codemirror/addon/fold/foldcode.js";
import "codemirror/addon/fold/foldgutter.js";
import "codemirror/addon/fold/indent-fold.js";
import "codemirror/addon/fold/markdown-fold.js";
import "codemirror/addon/fold/xml-fold.js";

export default {
  data() {
    return {
      editor: this.$slots.body[0] ? this.$slots.body[0].children[0].text : "",
      page: this.$slots.header[0]
        ? this.$slots.header[0].children[2].data.attrs.value
        : "",
      keep_timestamp: false,
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
    //console.log(this.$slots)
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
      let str = this.$refs.cm.codemirror.getSelection();
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
          ret = "%%" + str + "%%";
          break;
        case "code":
          ret = "`" + str + "`";
          break;
        case "q":
          ret = "@@@" + str + "@@@";
          break;

        case "url":
          //	var regex = "^s?https?://[-_.!~*'()a-zA-Z0-9;/?:@&=+$,%#]+$";
          var my_link = prompt("URL:", "https://");
          if (my_link !== null) {
            ret = "[[" + str + ">" + my_link + "]]";
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
    hint() {}
  },
  components: {
    "font-awesome-icon": FontAwesomeIcon
  }
};
</script>

<style lang="scss">
</style>