<template>
  <b-navbar toggleable="md" variant="dark" type="dark">
    <b-navbar-brand v-bind:href="baseUri">BootstrapVue</b-navbar-brand>
    <b-navbar-toggle target="nav_collapse" />
    <b-collapse is-nav id="nav_collapse">
      <b-navbar-nav class="ml-auto">
        <b-nav-item-dropdown text="Page">
          <b-dropdown-item v-bind:href="pageUri + '?action=new'">
            <font-awesome-icon far fixed-width icon="file" class="mr-1"/>New
          </b-dropdown-item>
          <b-dropdown-item v-bind:href="pageUri + '?action=edit'">
            <font-awesome-icon fas fixed-width icon="edit" class="mr-1"/>Edit
          </b-dropdown-item>
          <b-dropdown-item v-bind:href="pageUri + '?action=clone'">
            <font-awesome-icon fas fixed-width icon="clone" class="mr-1"/>Clone
          </b-dropdown-item>
          <b-dropdown-item v-bind:href="pageUri + '?action=source'">
            <font-awesome-icon far fixed-width icon="code" class="mr-1"/>Source
          </b-dropdown-item>
          <b-dropdown-item v-bind:href="pageUri + '?action=attachment'">
            <font-awesome-icon fas fixed-width icon="paperclip" class="mr-1"/>Attachment
          </b-dropdown-item>
          <b-dropdown-item v-bind:href="pageUri + '?action=history'">
            <font-awesome-icon fas fixed-width icon="history" class="mr-1"/>History
          </b-dropdown-item>
          <b-dropdown-item v-bind:href="pageUri + '?action=lock'">
            <font-awesome-icon fas fixed-width icon="unlock" class="mr-1"/>Lock
          </b-dropdown-item>
        </b-nav-item-dropdown>
        <b-nav-item-dropdown text="List">
          <b-dropdown-item v-bind:href="baseUri + '?action=list'">
            <font-awesome-icon far fixed-width icon="list-alt" class="mr-1"/>Page List
          </b-dropdown-item>
          <b-dropdown-item v-bind:href="baseUri + '?action=recent'">Recent Changes</b-dropdown-item>
        </b-nav-item-dropdown>
      </b-navbar-nav>
      <b-navbar-nav class="mr-0">
        <b-nav-form v-bind:action="baseUri" method="post" class="my-lg-0 mr-0">
          <input type="hidden" name="csrf_token" v-bind:value="token" />
          <input type="hidden" name="action" value="search" />
          <b-form-input class="mr-sm-2" type="text" placeholder="Search"/>
          <b-button variant="outline-success" class="my-2 my-sm-0" type="submit">Search</b-button>
        </b-nav-form>
      </b-navbar-nav>
    </b-collapse>
  </b-navbar>
</template>
<script>
// ナビバー
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome";
import { library } from "@fortawesome/fontawesome-svg-core";
import { fas } from "@fortawesome/free-solid-svg-icons";
import { far } from "@fortawesome/free-regular-svg-icons";
import { querystring } from "query-string";
library.add(fas);
library.add(far);
export default {
  data() {
    //const qs = querystring.parse(location.search);
    //if (qs.action !== void 0) {
    //}
    console.log(this.$refs);
    return {
      pageUri: this.$attrs.baseuri + "/" + this.$attrs.page,
      baseUri: this.$attrs.baseuri,
      token: window.axios.defaults.headers.common["X-CSRF-TOKEN"]
    };
  },
  components: {
    "font-awesome-icon": FontAwesomeIcon
  }
};
</script>