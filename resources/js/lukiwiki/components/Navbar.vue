<template>
  <b-navbar
    toggleable="md"
    variant="dark"
    type="dark"
  >
    <b-navbar-brand :href="baseUri">
      {{ brand }}
    </b-navbar-brand>
    <b-navbar-toggle target="nav_collapse" />
    <b-collapse
      id="nav_collapse"
      is-nav
    >
      <b-navbar-nav class="ml-auto">
        <!-- page menu -->
        <b-nav-item-dropdown
          text="Page"
          :disabled="this.$attrs.page === ''"
        >
          <b-dropdown-item
            :href="baseUri + ':new'"
            :active="isPageAction && action === 'new'"
          >
            <font-awesome-icon
              far
              fixed-width
              icon="file"
              class="mr-1"
            />New
          </b-dropdown-item>
          <b-dropdown-item
            :href="pageUri + ':edit'"
            :active="isPageAction && action === 'edit'"
          >
            <font-awesome-icon
              fas
              fixed-width
              icon="edit"
              class="mr-1"
            />Edit
          </b-dropdown-item>
          <b-dropdown-item
            :href="pageUri + ':copy'"
            :active="isPageAction && action === 'copy'"
          >
            <font-awesome-icon
              fas
              fixed-width
              icon="copy"
              class="mr-1"
            />Copy
          </b-dropdown-item>
          <b-dropdown-item
            :href="pageUri + ':source'"
            :active="isPageAction && action === 'source'"
          >
            <font-awesome-icon
              far
              fixed-width
              icon="file-code"
              class="mr-1"
            />Source
          </b-dropdown-item>
          <b-dropdown-item
            :href="pageUri + ':diff'"
            :active="isPageAction && action === 'diff'"
          >
            <font-awesome-layers class="mr-1">
              <font-awesome-icon
                fas
                fixed-width
                icon="file"
              />
              <font-awesome-icon
                fas
                icon="slash"
                :style="{ color: 'white' }"
                class="fa-flip-vertical"
              />
            </font-awesome-layers>Diff
          </b-dropdown-item>
          <b-dropdown-item
            :href="pageUri + ':attachments'"
            :active="isPageAction && action === 'attachments'"
          >
            <font-awesome-icon
              fas
              fixed-width
              icon="paperclip"
              class="mr-1"
            />Attachments
          </b-dropdown-item>
          <b-dropdown-item
            :href="pageUri + ':history'"
            :active="isPageAction && action === 'history'"
          >
            <font-awesome-icon
              fas
              fixed-width
              icon="history"
              class="mr-1"
            />History
          </b-dropdown-item>
          <b-dropdown-item
            :href="pageUri + ':lock'"
            :active="isPageAction && action === 'lock'"
          >
            <font-awesome-icon
              fas
              fixed-width
              icon="unlock"
              class="mr-1"
            />Lock
          </b-dropdown-item>
          <b-dropdown-item
            :href="pageUri + ':print'"
            :active="isPageAction && action === 'print'"
          >
            <font-awesome-icon
              fas
              fixed-width
              icon="print"
              class="mr-1"
            />Print
          </b-dropdown-item>
        </b-nav-item-dropdown>
        <!-- List menu -->
        <b-nav-item-dropdown text="List">
          <b-dropdown-item
            :href="baseUri + ':list'"
            :active="isPageAction && action === 'list'"
          >
            <font-awesome-icon
              far
              fixed-width
              icon="list-alt"
              class="mr-1"
            />Page List
          </b-dropdown-item>
          <b-dropdown-item
            :href="baseUri + ':recent'"
            :active="isPageAction && action === 'recent'"
          >
            <font-awesome-icon
              far
              fixed-width
              icon="clock"
              class="mr-1"
            />Recent Changes
          </b-dropdown-item>
        </b-nav-item-dropdown>
        <!-- search form -->
        <b-nav-form
          :action="baseUri + ':search'"
          method="post"
          class="my-lg-0 mr-0"
        >
          <input
            type="hidden"
            name="_token"
            :value="token"
          >
          <b-form-input
            class="mr-sm-2"
            type="search"
            name="keyword"
          />
          <b-button
            variant="outline-success"
            class="my-2 my-sm-0"
            type="submit"
          >
            <font-awesome-icon
              far
              fixed-width
              icon="search"
              class="mr-1"
            />Search
          </b-button>
        </b-nav-form>
        <!-- user menu -->
        <b-nav-item-dropdown right>
          <!-- Using button-content slot -->
          <template slot="button-content">
            <font-awesome-icon
              far
              fixed-width
              icon="user"
            />
          </template>
          <b-dropdown-item href=":user/logout">
            <font-awesome-icon
              far
              fixed-width
              icon="sign-out-alt"
              class="mr-1"
            />Signout
          </b-dropdown-item>
        </b-nav-item-dropdown>
      </b-navbar-nav>
    </b-collapse>
  </b-navbar>
</template>
<script>
// Button
import bButton from 'bootstrap-vue/es/components/button/button';
// Collapse
import bCollapse from 'bootstrap-vue/es/components/collapse/collapse';
// Dropdown
import bDropdownItem from 'bootstrap-vue/es/components/dropdown/dropdown-item';
// Form
import bFormInput from 'bootstrap-vue/es/components/form-input/form-input';
// Nav
import bNavItem from 'bootstrap-vue/es/components/nav/nav-item';
import bNavItemDropdown from 'bootstrap-vue/es/components/nav/nav-item-dropdown';
import bNavForm from 'bootstrap-vue/es/components/nav/nav-form';
// Navbar
import bNavbar from 'bootstrap-vue/es/components/navbar/navbar';
import bNavbarBrand from 'bootstrap-vue/es/components/navbar/navbar-brand';
import bNavbarNav from 'bootstrap-vue/es/components/navbar/navbar-nav';
import bNavbarToggle from 'bootstrap-vue/es/components/navbar/navbar-toggle';

// 使用するアイコンの登録
import {
  FontAwesomeIcon,
  FontAwesomeLayers
} from '@fortawesome/vue-fontawesome';
import { library } from '@fortawesome/fontawesome-svg-core';
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
  faPrint,
  faSlash,
  faUser,
  faCog,
  faSignInAlt,
  faSignOutAlt
} from '@fortawesome/free-solid-svg-icons';

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
  faPrint,
  faSlash,
  faUser,
  faCog,
  faSignInAlt,
  faSignOutAlt
);

export default {
  components: {
    'b-button': bButton,
    'b-collapse': bCollapse,
    'b-dropdown-item': bDropdownItem,
    'b-form-input': bFormInput,
    'b-nav-item-dropdown': bNavItemDropdown,
    'b-nav-form': bNavForm,
    'b-navbar': bNavbar,
    'b-navbar-brand': bNavbarBrand,
    'b-navbar-nav': bNavbarNav,
    'b-navbar-toggle': bNavbarToggle,
    'font-awesome-icon': FontAwesomeIcon,
    'font-awesome-layers': FontAwesomeLayers
  },
  data() {
    this.page = encodeURI(this.$attrs.page).replace('%2F', '/');
    this.action = window.qs.action;
    this.isPageAction = this.action !== void 0 && this.page !== '';
    //console.log(this.$attrs);
    this.baseUri = this.$attrs['base-uri'] + '/';
    this.pageUri = this.baseUri + this.page;

    return {
      baseUri: this.baseUri,
      pageUri: this.pageUri,
      brand: this.$attrs.brand,
      token: document.head.querySelector('meta[name="csrf-token"]').content
    };
  }
};
</script>