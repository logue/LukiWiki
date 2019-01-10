<template>
  <b-navbar toggleable="md" variant="dark" type="dark">
    <b-navbar-brand v-bind:href="baseUri">{{ brand }}</b-navbar-brand>
    <b-navbar-toggle target="nav_collapse"/>
    <b-collapse is-nav id="nav_collapse">
      <b-navbar-nav class="ml-auto">
        <b-nav-item-dropdown text="Page" v-bind:disabled="this.$attrs.page === ''">
          <b-dropdown-item
            v-bind:href="pageUri + '?action=new'"
            v-bind:active="isPageAction && action === 'new'"
          >
            <font-awesome-icon far fixed-width icon="file" class="mr-1"/>New
          </b-dropdown-item>
          <b-dropdown-item
            v-bind:href="pageUri + '?action=edit'"
            v-bind:active="isPageAction && action === 'edit'"
          >
            <font-awesome-icon fas fixed-width icon="edit" class="mr-1"/>Edit
          </b-dropdown-item>
          <b-dropdown-item
            v-bind:href="pageUri + '?action=copy'"
            v-bind:active="isPageAction && action === 'copy'"
          >
            <font-awesome-icon fas fixed-width icon="copy" class="mr-1"/>Copy
          </b-dropdown-item>
          <b-dropdown-item
            v-bind:href="pageUri + '?action=source'"
            v-bind:active="isPageAction && action === 'source'"
          >
            <font-awesome-icon far fixed-width icon="file-code" class="mr-1"/>Source
          </b-dropdown-item>
          <b-dropdown-item
            v-bind:href="pageUri + '?action=attachment'"
            v-bind:active="isPageAction && action === 'attachment'"
          >
            <font-awesome-icon fas fixed-width icon="paperclip" class="mr-1"/>Attachment
          </b-dropdown-item>
          <b-dropdown-item
            v-bind:href="pageUri + '?action=history'"
            v-bind:active="isPageAction && action === 'history'"
          >
            <font-awesome-icon fas fixed-width icon="history" class="mr-1"/>History
          </b-dropdown-item>
          <b-dropdown-item
            v-bind:href="pageUri + '?action=lock'"
            v-bind:active="isPageAction && action === 'lock'"
          >
            <font-awesome-icon fas fixed-width icon="unlock" class="mr-1"/>Lock
          </b-dropdown-item>
        </b-nav-item-dropdown>
        <b-nav-item-dropdown text="List">
          <b-dropdown-item
            v-bind:href="baseUri + '?action=list'"
            v-bind:active="isPageAction && action === 'list'"
          >
            <font-awesome-icon far fixed-width icon="list-alt" class="mr-1"/>Page List
          </b-dropdown-item>
          <b-dropdown-item
            v-bind:href="baseUri + '?action=recent'"
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
// 使用するアイコンの登録
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome";
import { library } from "@fortawesome/fontawesome-svg-core";
import {
  faFile,
  faCopy,
  faEdit,
  faFileCode,
  faHistory,
  faPaperclip,
  faLock,
  faUnlock,
  faSearch,
  faListAlt,
  faClock
} from "@fortawesome/free-solid-svg-icons";

library.add(
  faEdit,
  faCopy,
  faPaperclip,
  faHistory,
  faLock,
  faUnlock,
  faSearch,
  faFile,
  faFileCode,
  faListAlt,
  faClock
);

export default {
  data() {
    this.page = this.$attrs.page;
    this.action = qs.action;
    this.isPageAction = this.action !== void 0 && this.page !== "";

    return {
      pageUri: this.$attrs.baseuri + "/" + encodeURIComponent(this.page),
      baseUri: this.$attrs.baseuri,
      brand: this.$attrs.brand,
      token: window.axios.defaults.headers.common["X-CSRF-TOKEN"]
    };
  },
  components: {
    "font-awesome-icon": FontAwesomeIcon
  }
};
</script>