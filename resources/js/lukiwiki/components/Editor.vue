<template>
  <div>
    <b-button-toolbar
      aria-label="Editor Toolbar"
      class="align-items-baseline form-row"
      justify
    >
      <div class="col-12 col-md-4">
        <b-input-group>
          <b-input-group-text slot="prepend">
            <font-awesome-icon fas icon="file-signature" />
          </b-input-group-text>
          <b-form-input
            v-model="page"
            v-b-tooltip
            name="page"
            autocomplete="off"
            title="Page Name"
          />
        </b-input-group>
      </div>
      <div class="col-md-8">
        <b-button-group>
          <b-button
            v-b-tooltip
            size="sm"
            variant="outline-secondary"
            title="Bold"
            @click="replace('b')"
          >
            <font-awesome-icon fas fixed-width icon="bold" />
          </b-button>
          <b-button
            v-b-tooltip
            size="sm"
            variant="outline-secondary"
            title="Italic"
            @click="replace('i')"
          >
            <font-awesome-icon fas fixed-width icon="italic" />
          </b-button>
          <b-button
            v-b-tooltip
            size="sm"
            variant="outline-secondary"
            title="Strike"
            @click="replace('s')"
          >
            <font-awesome-icon fas fixed-width icon="strikethrough" />
          </b-button>
          <b-button
            v-b-tooltip
            size="sm"
            variant="outline-secondary"
            title="Underline"
            @click="replace('u')"
          >
            <font-awesome-icon fas fixed-width icon="underline" />
          </b-button>
          <b-button
            v-b-tooltip
            size="sm"
            variant="outline-secondary"
            title="Code"
            @click="replace('code')"
          >
            <font-awesome-icon fas fixed-width icon="code" />
          </b-button>
          <b-button
            v-b-tooltip
            size="sm"
            variant="outline-secondary"
            title="Quotation"
            @click="replace('q')"
          >
            <font-awesome-icon fas fixed-width icon="quote-left" />
          </b-button>
        </b-button-group>
        <b-button-group>
          <b-button
            v-b-tooltip
            size="sm"
            variant="outline-secondary"
            title="Insert Link"
            @click="replace('url')"
          >
            <font-awesome-icon fas fixed-width icon="link" />
          </b-button>
          <b-button
            v-b-tooltip
            size="sm"
            variant="outline-secondary"
            title="Font Size"
            @click="replace('size')"
          >
            <font-awesome-icon fas fixed-width icon="text-height" />
          </b-button>
          <b-button
            v-b-tooltip
            size="sm"
            variant="outline-secondary"
            title="Insert Color"
            @click="insert('color')"
          >
            <font-awesome-icon fas fixed-width icon="palette" />
          </b-button>
        </b-button-group>
        <b-button-group>
          <b-button
            v-b-tooltip
            size="sm"
            variant="outline-secondary"
            title="Convert character reference"
            @click="replace('ncr')"
          >
            &amp;#
          </b-button>
          <b-button
            v-b-tooltip
            size="sm"
            variant="outline-secondary"
            title="Hint"
            @click="hint()"
          >
            <font-awesome-icon fas fixed-width icon="question-circle" />
          </b-button>
        </b-button-group>
      </div>
    </b-button-toolbar>
    <codemirror
      ref="cm"
      v-model="source"
      name="source"
      :options="cmOption"
      class="my-1"
      @ready="onCmReady"
      @focus="onCmFocus"
      @input="onCmCodeChange"
    />
    <div class="form-row align-items-center d-flex" aria-label="Editor Footer">
      <div class="p-1">
        <b-form-checkbox
          v-model="keep_timestamp"
          switch
          name="keep_timestamp"
          value="1"
          unchecked-value="0"
        >
          Keep Timestamp
        </b-form-checkbox>
      </div>
      <div class="p-1">
        <b-input-group>
          <b-input-group-text slot="prepend">
            <font-awesome-icon fas icon="key" />
          </b-input-group-text>
          <b-form-input
            v-model="key"
            name="password"
            type="password"
            autocomplete="off"
            placeholder="Password"
          />
        </b-input-group>
      </div>

      <div class="ml-auto px-1">
        <b-button
          variant="secondary"
          type="submit"
          name="action"
          value="cancel"
        >
          <font-awesome-icon fas fixed-width icon="ban" class="mr-1" />
          Cancel
        </b-button>
        <b-button
          variant="primary"
          type="submit"
          name="action"
          value="save"
          :disabled="keep_timestamp == 1 && key == ''"
        >
          <font-awesome-icon fas fixed-width icon="check" class="mr-1" />
          Submit
        </b-button>
      </div>
    </div>
  </div>
</template>

<script>
// Bootstrap Vue
import {
  BButton,
  BButtonGroup,
  BButtonToolbar,
  BFormCheckbox,
  BFormInput,
  BInputGroup,
  BInputGroupText,
  VBTooltip,
} from 'bootstrap-vue';

// FontAwesome
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { library } from '@fortawesome/fontawesome-svg-core';
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
  faUnderline,
} from '@fortawesome/free-solid-svg-icons';
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

// CodeMirror
import CodeMirror from 'codemirror';

// language
import '../codemirror_lukiwiki.js';
// active-line.js
import 'codemirror/addon/selection/active-line.js';
// styleSelectedText
import 'codemirror/addon/selection/mark-selection.js';
import 'codemirror/addon/search/searchcursor.js';
// highlightSelectionMatches
import 'codemirror/addon/scroll/annotatescrollbar.js';
import 'codemirror/addon/search/matchesonscrollbar.js';
import 'codemirror/addon/search/match-highlighter.js';
// keyMap
// import "codemirror/mode/clike/clike.js";
import 'codemirror/addon/edit/matchbrackets.js';
import 'codemirror/addon/comment/comment.js';
import 'codemirror/addon/dialog/dialog.js';
// import "codemirror/addon/dialog/dialog.css";
import 'codemirror/addon/search/search.js';
import 'codemirror/keymap/sublime.js';
// foldGutter
// import 'codemirror/addon/fold/foldgutter.css'
// import "codemirror/addon/fold/brace-fold.js";
// import "codemirror/addon/fold/comment-fold.js";
// import "codemirror/addon/fold/foldcode.js";
// import "codemirror/addon/fold/foldgutter.js";
// import "codemirror/addon/fold/indent-fold.js";
// import "codemirror/addon/fold/markdown-fold.js";
// import "codemirror/addon/fold/xml-fold.js";
// Show hint
import 'codemirror/addon/hint/show-hint.js';
// import "codemirror/addon/hint/show-hint.css";

import axios from 'axios';

export default {
  components: {
    'b-button': BButton,
    'b-button-group': BButtonGroup,
    'b-button-toolbar': BButtonToolbar,
    'b-form-checkbox': BFormCheckbox,
    'b-form-input': BFormInput,
    'b-input-group': BInputGroup,
    'b-input-group-text': BInputGroupText,
    'font-awesome-icon': FontAwesomeIcon,
  },
  directives: {
    'b-tooltip': VBTooltip,
  },
  data() {
    return {
      source: this.$slots.body[0].children[0].text || '',
      page: this.$slots.header[0].children[2].data.attrs.value || '',
      keep_timestamp: 0,
      key: '',
      hints: null,
      cmOption: {
        tabSize: 4,
        foldGutter: true,
        styleActiveLine: true,
        lineNumbers: true,
        lineWrapping: true,
        line: true,
        keyMap: 'sublime',
        mode: 'lukiwiki',
        extraKeys: {
          F11(cm) {
            cm.setOption('fullScreen', !cm.getOption('fullScreen'));
          },
          Esc(cm) {
            if (cm.getOption('fullScreen')) cm.setOption('fullScreen', false);
          },
        },
      },
    };
  },
  created: function () {
    // ヒントの定義ファイル
    const hints = [];
    axios
      .get('/js/emoji.json')
      .then((response) => {
        const data = response.data;
        for (const line in data) {
          hints.push({
            text: data[line],
            displayText: `${line} ${data[line]}`,
          });
        }
      })
      .catch((error) => console.error(error));

    this.hints = hints;
  },
  methods: {
    insert(v) {
      let ret = '';
      switch (v) {
        case 'br':
          ret = '&br;' + '\n';
          break;
        case 'color':
          // $('#color_palette').dialog('open');
          break;
        default:
          ret = '&(' + v + ');';
          break;
      }
      const cursor = this.$refs.cm.codemirror.getDoc().getCursor();
      this.$refs.cm.codemirror.replaceRange(ret, cursor);
    },
    replace(v) {
      let ret = '';
      const str = this.$refs.cm.codemirror.getSelection();
      if (str === '') {
        alert('Please select text.');
        return;
      }
      switch (v) {
        case 'size':
          const val = prompt('font-size (rem)', '1');
          if (!val || !val.match(/\d+/)) {
            return;
          }
          ret = '&size(' + val + '){' + str + '};';
          break;
        case 'ncr':
          for (const s in str) {
            ret += '&#' + str.charCodeAt(s) + ';';
          }
          break;
        case 'b':
          ret = "''" + str + "''";
          break;
        case 'i':
          ret = "'''" + str + "'''";
          break;
        case 'u':
          ret = '__' + str + '__';
          break;
        case 's':
          ret = '~~' + str + '~~';
          break;
        case 'code':
          ret = '`' + str + '`';
          break;
        case 'q':
          ret = '``' + str + '``';
          break;

        case 'url':
          //	var regex = "^s?https?://[-_.!~*'()a-zA-Z0-9;/?:@&=+$,%#]+$";
          const uri = prompt('URL:', 'https://');
          if (uri !== null) {
            ret = '[' + str + '](' + uri + ')';
          } else {
            return;
          }
          break;
        default:
          alert('error');
          return;
      }
      this.$refs.cm.codemirror.replaceSelection(ret);
    },
    hint() {
      // TODO:
    },
    onCmReady(cm) {
      // console.log(this.emojiList);
      cm.on('keypress', () => {
        // ヒント
        // https://qiita.com/yymm@github/items/6aa5b869ef8c22683ccc
        CodeMirror.showHint(
          cm,
          function () {
            const cur = cm.getCursor();
            const token = cm.getTokenAt(cur);
            const end = cur.ch;
            let ch = cur.ch;
            const line = cur.line;
            const currentWord = token.string;

            while (ch-- > -1) {
              const t = cm.getTokenAt({ ch, line }).string;
              if (t === ':') {
                const filteredList = this.hints.filter((item) => {
                  return item.text.indexOf(':' + currentWord) == 0
                    ? true
                    : false;
                });
                // console.log(filteredList);
                if (filteredList.length >= 1) {
                  return {
                    list: filteredList,
                    from: CodeMirror.Pos(line, ch),
                    to: CodeMirror.Pos(line, end),
                  };
                }
              }
              // currentWord = t + currentWord;
            }
          },
          { completeSingle: false }
        );
      });
    },
    onCmFocus() {
      // console.log("the editor is focus!", cm);
    },
    onCmCodeChange(newCode) {
      // console.log("this is new code", newCode);
      // this.code = newCode;
      this.source = newCode;
    },
  },
};
</script>

<style lang="scss"></style>
