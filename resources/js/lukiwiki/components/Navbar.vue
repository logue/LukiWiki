<template>
  <b-navbar toggleable="md" variant="dark" type="dark">
    <b-navbar-brand v-bind:href="baseUri">{{ brand }}</b-navbar-brand>
    <b-navbar-toggle target="nav_collapse"/>
    <b-collapse is-nav id="nav_collapse">
      <b-navbar-nav class="ml-auto">
        <b-nav-item-dropdown text="Page" v-bind:disabled="this.$attrs.page === ''">
          <b-dropdown-item
            v-bind:href="baseUri + ':new'"
            v-bind:active="isPageAction && action === 'new'"
          >
            <font-awesome-icon far fixed-width icon="file" class="mr-1"/>New
          </b-dropdown-item>
          <b-dropdown-item
            v-bind:href="pageUri + ':edit'"
            v-bind:active="isPageAction && action === 'edit'"
          >
            <font-awesome-icon fas fixed-width icon="edit" class="mr-1"/>Edit
          </b-dropdown-item>
          <b-dropdown-item
            v-bind:href="pageUri + ':copy'"
            v-bind:active="isPageAction && action === 'copy'"
          >
            <font-awesome-icon fas fixed-width icon="copy" class="mr-1"/>Copy
          </b-dropdown-item>
          <b-dropdown-item
            v-bind:href="pageUri + ':source'"
            v-bind:active="isPageAction && action === 'source'"
          >
            <font-awesome-icon far fixed-width icon="file-code" class="mr-1"/>Source
          </b-dropdown-item>
          <b-dropdown-item
            v-bind:href="pageUri + ':attachments'"
            v-bind:active="isPageAction && action === 'attachments'"
          >
            <font-awesome-icon fas fixed-width icon="paperclip" class="mr-1"/>Attachments
          </b-dropdown-item>
          <b-dropdown-item
            v-bind:href="pageUri + ':history'"
            v-bind:active="isPageAction && action === 'history'"
          >
            <font-awesome-icon fas fixed-width icon="history" class="mr-1"/>History
          </b-dropdown-item>
          <b-dropdown-item
            v-bind:href="pageUri + ':lock'"
            v-bind:active="isPageAction && action === 'lock'"
          >
            <font-awesome-icon fas fixed-width icon="unlock" class="mr-1"/>Lock
          </b-dropdown-item>
          <b-dropdown-item
            v-bind:href="pageUri + ':print'"
            v-bind:active="isPageAction && action === 'print'"
          >
            <font-awesome-icon fas fixed-width icon="print" class="mr-1"/>Print
          </b-dropdown-item>
        </b-nav-item-dropdown>
        <b-nav-item-dropdown text="List">
          <b-dropdown-item
            v-bind:href="baseUri + ':list'"
            v-bind:active="isPageAction && action === 'list'"
          >
            <font-awesome-icon far fixed-width icon="list-alt" class="mr-1"/>Page List
          </b-dropdown-item>
          <b-dropdown-item
            v-bind:href="baseUri + ':recent'"
            v-bind:active="isPageAction && action === 'recent'"
          >
            <font-awesome-icon far fixed-width icon="clock" class="mr-1"/>Recent Changes
          </b-dropdown-item>
        </b-nav-item-dropdown>
      </b-navbar-nav>
      <b-navbar-nav class="mr-0">
        <b-nav-form v-bind:action="baseUri" method="post" class="my-lg-0 mr-0">
          <input type="hidden" name="_token" v-bind:value="token">
          <input type="hidden" name="action" value="search">
          <b-form-input class="mr-sm-2" type="search" placeholder="Search"/>
          <b-button variant="outline-success" class="my-2 my-sm-0" type="submit">
            <font-awesome-icon far fixed-width icon="search" class="mr-1"/>Search
          </b-button>
        </b-nav-form>
      </b-navbar-nav>
    </b-collapse>
  </b-navbar>
</template>
<script>
// Button
import bButton from "bootstrap-vue/es/components/button/button";
// Collapse
import bCollapse from "bootstrap-vue/es/components/collapse/collapse";
// Dropdown
import bDropdownItem from "bootstrap-vue/es/components/dropdown/dropdown-item";
// Form
import bFormInput from "bootstrap-vue/es/components/form-input/form-input";
// Nav
import bNavItemDropdown from "bootstrap-vue/es/components/nav/nav-item-dropdown";
import bNavForm from "bootstrap-vue/es/components/nav/nav-form";
// Navbar
import bNavbar from "bootstrap-vue/es/components/navbar/navbar";
import bNavbarBrand from "bootstrap-vue/es/components/navbar/navbar-brand";
import bNavbarNav from "bootstrap-vue/es/components/navbar/navbar-nav";
import bNavbarToggle from "bootstrap-vue/es/components/navbar/navbar-toggle";

// 使用するアイコンの登録
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome";
import { library } from "@fortawesome/fontawesome-svg-core";
import {
  faClock,
  faCopy,
  faEdit,
  faFile,
  faFileCode,
  faHistory,
  faListAlt,
  faLock,
  faPaperclip,
  faSearch,
  faUnlock,
  faPrint
} from "@fortawesome/free-solid-svg-icons";

library.add(
  faClock,
  faCopy,
  faEdit,
  faFile,
  faFileCode,
  faHistory,
  faListAlt,
  faLock,
  faPaperclip,
  faSearch,
  faUnlock,
  faPrint
);

export default {
  data() {
    this.page = encodeURI(this.$attrs.page).replace("%2F", "/");
    this.action = qs.action;
    this.isPageAction = this.action !== void 0 && this.page !== "";

    return {
      pageUri: this.$attrs.baseuri + "/" + this.page,
      baseUri: this.$attrs.baseuri + "/",
      brand: this.$attrs.brand,
      token: window.axios.defaults.headers.common["X-CSRF-TOKEN"]
    };
  },
  components: {
    "b-button": bButton,
    "b-collapse": bCollapse,
    "b-dropdown-item": bDropdownItem,
    "b-form-input": bFormInput,
    "b-nav-item-dropdown": bNavItemDropdown,
    "b-nav-form": bNavForm,
    "b-navbar": bNavbar,
    "b-navbar-brand": bNavbarBrand,
    "b-navbar-nav": bNavbarNav,
    "b-navbar-toggle": bNavbarToggle,
    "font-awesome-icon": FontAwesomeIcon
  }
};
</script>