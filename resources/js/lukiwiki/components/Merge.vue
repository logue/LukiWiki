<template>
  <div class="codemirror">
    <codemirror
      :merge="true"
      :options="cmOption"
      @cursorActivity="onCmCursorActivity"
      @ready="onCmReady"
      @focus="onCmFocus"
      @blur="onCmBlur"
      @input="onCmInput"
      @scroll="onCmScroll"
    ></codemirror>
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
        <b-button variant="primary" type="submit" name="action" value="save">
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
// Form Checkbox
import bFormCheckbox from "bootstrap-vue/es/components/form-checkbox/form-checkbox";
// Form Input
import bFormInput from "bootstrap-vue/es/components/form-input/form-input";
// Input Group
import bInputGroup from "bootstrap-vue/es/components/input-group/input-group";
import bInputGroupText from "bootstrap-vue/es/components/input-group/input-group-text";

// language
import "codemirror/mode/css/css.js";
import "codemirror/mode/xml/xml.js";
import "codemirror/mode/htmlmixed/htmlmixed.js";

// merge css
import "codemirror/addon/merge/merge.css";

// merge js
import "codemirror/addon/merge/merge.js";

// google DiffMatchPatch
import DiffMatchPatch from "diff-match-patch";

// DiffMatchPatch config with global
window.diff_match_patch = DiffMatchPatch;
window.DIFF_DELETE = -1;
window.DIFF_INSERT = 1;
window.DIFF_EQUAL = 0;

// FontAwesome
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome";
import { library } from "@fortawesome/fontawesome-svg-core";
import { faBan, faCheck } from "@fortawesome/free-solid-svg-icons";
library.add(faBan, faCheck);

export default {
  beforeCreate() {},
  mounted() {},

  data() {
    // console.log('html', html, 'orig1', 'orig2', orig2)
    const slot = this.$slots;
    return {
      cmOption: {
        value: slot.default[1].children[2].children[0].children[0].text,
        origLeft: slot.default[1].children[0].children[0].children[0].text,
        orig: slot.origin[0].data.attrs.value,
        connect: "align",
        mode: "text/lukiwiki",
        lineNumbers: true,
        collapseIdentical: false,
        highlightDifferences: true
      }
    };
  },
  methods: {
    onCmCursorActivity(a, b, c) {
      //console.log("onCmCursorActivity", a, b, c);
    },
    onCmReady(a, b, c) {
      //console.log("onCmReady", a, b, c);
    },
    onCmFocus(a, b, c) {
      //console.log("onCmFocus", a, b, c);
    },
    onCmBlur(a, b, c) {
      //console.log("onCmBlur", a, b, c);
    },
    onCmInput(code) {
      //console.log("onCmInput", code);
    },
    onCmScroll() {
      //console.log("onCmScroll");
    }
  },
  components: {
    "b-button": bButton,
    "b-form-checkbox": bFormCheckbox,
    "b-form-input": bFormInput,
    "b-input-group": bInputGroup,
    "b-input-group-text": bInputGroupText,
    "font-awesome-icon": FontAwesomeIcon
  }
};
</script>
