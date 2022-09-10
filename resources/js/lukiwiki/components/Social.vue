<template>
  <div
    :class="{ 'form-group row': type === 'auth', 'd-inline': type === 'share' }"
  >
    <label v-if="type === 'auth'" class="col-md-4 col-form-label text-md-right">
      Login With
    </label>
    <div :class="{ 'col-md-8': type === 'auth', 'd-inline': type === 'share' }">
      <b-button
        v-b-tooltip
        title="Facebook"
        class="btn-social-icon btn-facebook"
        :class="{ 'btn-sm': type === 'share' }"
        @click="jump('facebook', type)"
      >
        <font-awesome-icon
          fixed-width
          :icon="{ prefix: 'fab', iconName: 'facebook' }"
        />
      </b-button>
      <b-button
        v-b-tooltip
        title="Twitter"
        class="btn-social-icon btn-twitter"
        :class="{ 'btn-sm': type === 'share' }"
        @click="jump('twitter', type)"
      >
        <font-awesome-icon
          fixed-width
          :icon="{ prefix: 'fab', iconName: 'twitter' }"
        />
      </b-button>
      <b-button
        v-if="type === 'auth'"
        v-b-tooltip
        title="Google Account"
        class="btn-social-icon btn-google"
        @click="jump('google', type)"
      >
        <font-awesome-icon
          fixed-width
          :icon="{ prefix: 'fab', iconName: 'google' }"
        />
      </b-button>
      <b-button
        v-if="type === 'auth'"
        v-b-tooltip
        title="Microsoft Account"
        class="btn-social-icon btn-microsoft"
        @click="jump('live', type)"
      >
        <font-awesome-icon
          fixed-width
          :icon="{ prefix: 'fab', iconName: 'microsoft' }"
        />
      </b-button>
      <b-button
        v-b-tooltip
        title="Line"
        class="btn-social-icon btn-line"
        :class="{ 'btn-sm': type === 'share' }"
        @click="jump('line', type)"
      >
        <font-awesome-icon
          fixed-width
          :icon="{ prefix: 'fab', iconName: 'line' }"
        />
      </b-button>
      <b-button
        v-if="type === 'auth'"
        v-b-tooltip
        title="Github"
        class="btn-social-icon btn-github"
        @click="jump('github', type)"
      >
        <font-awesome-icon
          fixed-width
          :icon="{ prefix: 'fab', iconName: 'github' }"
        />
      </b-button>
    </div>
  </div>
</template>

<script>
// Bootstrap Vue
import { BButton, VBTooltip } from 'bootstrap-vue';

// FontAwesome
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { library } from '@fortawesome/fontawesome-svg-core';
import {
  faFacebook,
  faTwitter,
  faGoogle,
  faGithub,
  faMicrosoft,
  faLine,
} from '@fortawesome/free-brands-svg-icons';
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
  components: {
    'b-button': BButton,
    'font-awesome-icon': FontAwesomeIcon,
  },
  directives: {
    'b-tooltip': VBTooltip,
  },
  data() {
    // console.log(this.$attrs);
    return {
      type: this.$attrs.type,
    };
  },
  methods: {
    jump: (sns, type) => {
      // console.log(sns, type);
      if (type === 'share') {
        const canonical = document.head.querySelector(
          'link[rel=canonical]'
        ).href;
        let url;
        switch (sns) {
          case 'facebook':
            url = 'http://www.facebook.com/share.php?u=';
            break;
          case 'twitter':
            url = 'https://twiter.com/share?url=';
            break;
          case 'line':
            url = 'https://social-plugins.line.me/lineit/share?url=';
            break;
        }
        window.open(url + canonical, 'share', 'width=512,height=512');
      } else {
        location.href = location.origin + '/:login/' + sns;
      }
    },
  },
};
</script>

<style lang="scss">
// This scss is inspired from https://github.com/ladjs/bootstrap-social/
@import '~/bootstrap/scss/bootstrap';

.btn-social {
  padding: $btn-padding-y;

  > :first-child {
  }

  > :nth-child(2) {
    border-left: 1px solid rgba(0, 0, 0, 0.2);
    padding-left: 0.25rem;
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
  background-image: none;

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
