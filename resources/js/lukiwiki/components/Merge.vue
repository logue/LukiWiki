<template>
  <div>
    <div class="d-flex">
      <div class="mx-auto">
        Remote
        <b-button
          v-b-tooltip
          size="sm"
          variant="outline-secondary"
          title="Help"
          @click="help('remote')"
        >
          <font-awesome-icon
            fas
            size="sm"
            fixed-width
            icon="question"
          />
        </b-button>
      </div>
      <div class="mx-auto">
        Local
      </div>
      <div class="mx-auto">
        Origin
      </div>
    </div>
    <codemirror
      v-model="source"
      :merge="true"
      :options="cmOption"
      name="source"
      @cursorActivity="onCmCursorActivity"
      @ready="onCmReady"
      @focus="onCmFocus"
      @blur="onCmBlur"
      @input="onCmInput"
      @scroll="onCmScroll"
    />
    <div
      class="form-row align-items-center d-flex"
      aria-label="Editor Footer"
    >
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
            <font-awesome-icon
              fas
              icon="key"
            />
          </b-input-group-text>
          <b-form-input
            v-model="key"
            name="password"
            type="password"
            autocomplete="off"
            placeholder="Password"
            :disabled="keep_timestamp == 0"
          />
        </b-input-group>
      </div>
      <div class="ml-auto px-1">
        <b-button
          variant="primary"
          type="submit"
          name="action"
          value="save"
          :disabled="keep_timestamp == 1 && key == ''"
        >
          <font-awesome-icon
            fas
            fixed-width
            icon="check"
            class="mr-1"
          />Submit
        </b-button>
        <b-button
          variant="secondary"
          type="submit"
          name="action"
          value="cancel"
        >
          <font-awesome-icon
            fas
            fixed-width
            icon="ban"
            class="mr-1"
          />Cancel
        </b-button>
      </div>
    </div>
  </div>
</template>

<script>
// Bootstrap Vue
import {
  BButton,
  BFormCheckbox,
  BFormInput,
  BInputGroup,
  BInputGroupText
} from 'bootstrap-vue';

// language
//import "codemirror/mode/css/css.js";
//import "codemirror/mode/xml/xml.js";
//import "codemirror/mode/htmlmixed/htmlmixed.js";

// merge css
//import "codemirror/addon/merge/merge.css";

// merge js
import 'codemirror/addon/merge/merge.js';

// google DiffMatchPatch
import DiffMatchPatch from 'diff-match-patch';

import 'codemirror/addon/selection/active-line.js';

// DiffMatchPatch config with global
window.diff_match_patch = DiffMatchPatch;
window.DIFF_DELETE = -1;
window.DIFF_INSERT = 1;
window.DIFF_EQUAL = 0;

// FontAwesome
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { library } from '@fortawesome/fontawesome-svg-core';
import { faBan, faCheck, faQuestion } from '@fortawesome/free-solid-svg-icons';
library.add(faBan, faCheck, faQuestion);

export default {
  components: {
    'b-button': BButton,
    'b-form-checkbox': BFormCheckbox,
    'b-form-input': BFormInput,
    'b-input-group': BInputGroup,
    'b-input-group-text': BInputGroupText,
    'font-awesome-icon': FontAwesomeIcon
  },

  data() {
    // console.log('html', html, 'orig1', 'orig2', orig2)
    const slot = this.$slots;

    return {
      keep_timestamp: 0,
      key: '',
      source: '',
      cmOption: {
        value: slot.default[1].children[2].children[0].children[0].text,
        origLeft: slot.default[1].children[0].children[0].children[0].text,
        orig: slot.origin[0].data.attrs.value,
        connect: 'align',
        mode: 'lukiwiki',
        lineNumbers: true,
        collapseIdentical: false,
        highlightDifferences: true
      }
    };
  },
  beforeCreate() {},
  mounted() {},
  methods: {
    onCmCursorActivity(a, b, c) {
      console.log('onCmCursorActivity', a, b, c);
    },
    onCmReady(a, b, c) {
      console.log('onCmReady', a, b, c);
    },
    onCmFocus(a, b, c) {
      console.log('onCmFocus', a, b, c);
    },
    onCmBlur(a, b, c) {
      console.log('onCmBlur', a, b, c);
    },
    onCmInput(code) {
      console.log('onCmInput', code);
    },
    onCmScroll() {
      console.log('onCmScroll');
    }
  }
};
</script>
