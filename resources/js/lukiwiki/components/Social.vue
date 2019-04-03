<template>
  <div v-bind:class="{ 'form-group row': type === 'auth', 'd-inline': type==='share' }">
    <label v-if="type === 'auth'" class="col-md-4 col-form-label text-md-right">Login With</label>
    <div  v-bind:class="{ 'col-md-8': type === 'auth', 'd-inline': type==='share'}">
      <b-button
        v-on:click="jump('facebook', type)"
        title="Facebook"
        class="btn-social-icon btn-facebook"
        v-bind:class="{ 'btn-sm': type === 'share' }"
        v-b-tooltip
      >
        <font-awesome-icon fixed-width :icon="{ prefix: 'fab', iconName: 'facebook' }" />
      </b-button>
      <b-button
        v-on:click="jump('twitter', type)"
        title="Twitter"
        class="btn-social-icon btn-twitter"
        v-bind:class="{ 'btn-sm': type === 'share' }"
        v-b-tooltip
      >
        <font-awesome-icon fixed-width :icon="{ prefix: 'fab', iconName: 'twitter' }" />
      </b-button>
      <b-button
        v-on:click="jump('google', type)"
        v-if="type === 'auth'"
        title="Google Account"
        class="btn-social-icon btn-google"
        v-b-tooltip
      >
        <font-awesome-icon fixed-width :icon="{ prefix: 'fab', iconName: 'google' }"/>
      </b-button>
      <b-button
        v-on:click="jump('live', type)"
        v-if="type === 'auth'"
        title="Microsoft Account"
        class="btn-social-icon btn-microsoft"
        v-b-tooltip
      >
        <font-awesome-icon fixed-width :icon="{ prefix: 'fab', iconName: 'microsoft' }"/>
      </b-button>
      <b-button
        v-on:click="jump('line', type)"
        title="Line"
        class="btn-social-icon btn-line"
        v-bind:class="{ 'btn-sm': type === 'share' }"
        v-b-tooltip
      >
        <font-awesome-icon fixed-width :icon="{ prefix: 'fab', iconName: 'line' }"/>
      </b-button>
      <b-button
        v-on:click="jump('github', type)"
        v-if="type === 'auth'"
        title="Github"
        class="btn-social-icon btn-github"
        v-b-tooltip
      >
        <font-awesome-icon fixed-width :icon="{ prefix: 'fab', iconName: 'github' }"/>
      </b-button>
    </div>
  </div>
</template>
<script>
// Button
import bButton from "bootstrap-vue/es/components/button/button";
// Tooltip
import vBTooltip from "bootstrap-vue/es/directives/tooltip/tooltip";
// FontAwesome
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome";
import { library } from "@fortawesome/fontawesome-svg-core";
import {
  faFacebook,
  faTwitter,
  faGoogle,
  faGithub,
  faMicrosoft,
  faLine
} from "@fortawesome/free-brands-svg-icons";

library.add(
  // SNS
  faFacebook,
  faTwitter,
  faGoogle,
  faGithub,
  faMicrosoft,
  faLine
);
export default {
  data() {
    console.log(this.$attrs);
    return {
      type: this.$attrs.type
    };
  },
  methods: {
    jump: (sns, type) => {
      console.log(sns, type);
      switch (type) {
        case "share":
          const canonical = document.head.querySelector("link[rel=canonical]")
            .href;
          let url;
          switch (sns) {
            case "facebook":
              url = "http://www.facebook.com/share.php?u=";
              break;
            case "twitter":
              url = "https://twiter.com/share?url=";
              break;
            case "line":
              url = "https://social-plugins.line.me/lineit/share?url=";
              break;
          }
          window.open(url + canonical, 'share', 'width=400,height=400');
          break;
        case "auth":
          location.href = location.origin + "/:login/" + sns;
          break;
      }
    }
  },
  components: {
    "b-button": bButton,
    "font-awesome-icon": FontAwesomeIcon
  },
  directives: {
    "b-tooltip": vBTooltip
  }
};
</script>
<style lang="scss">
// This scss is inspired from https://github.com/ladjs/bootstrap-social/
@import "~bootstrap/scss/functions";
@import "~bootstrap/scss/variables";
@import "~bootstrap/scss/mixins";


.btn-social {
  padding: $btn-padding-y;
  
  > :first-child {
    
  }

   > :nth-child(2){
        border-left: 1px solid rgba(0, 0, 0, 0.2);
        padding-left:0.25rem;
   }

  &.btn-lg {
    padding: $btn-padding-y-lg;
  }
  &.btn-sm {
    padding: $btn-padding-y-sm;
  }
}

.btn-social-icon {
  padding: $btn-padding-y;

  > :first-child {
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
  }

  &.btn-lg {
    padding: $btn-padding-y-lg;
  }
  &.btn-sm {
    padding: $btn-padding-y-sm;
  }
}

@mixin btn-social($color-bg, $color: null) {
  @if $color {
    color: $color;
  }

  @include button-variant($color-bg, $color-bg);
}

.btn-facebook {
  @include btn-social(#3b5998);
}

.btn-github {
  @include btn-social(#444444);
}

.btn-google {
  @include btn-social(#dd4b39);
}

.btn-microsoft {
  @include btn-social(#2672ec);
}

.btn-line {
  @include btn-social(#1bb71f);
}

.btn-twitter {
  @include btn-social(#1da1f2, #fff);
}
</style>
