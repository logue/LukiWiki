<template>
  <div>
    <span v-if="src.match(/\.(jpe?g|gif|png|webp)$/)">
      <b-img-lazy
        v-bind:src="src"
        v-bind:alt="alt"
        blank-color="#f8f9fa"
        v-bind:title="title"
        v-b-tooltip
      />
    </span>
    <span v-else-if="src.match(/\.(mp3|m4a|wave?|aif?f|flac)$/)">
      <audio
        v-bind:src="src"
        v-bind:alt="alt"
        controls="controls"
        v-bind:title="title"
        v-b-tooltip
      />
    </span>
    <span v-else-if="src.match(/\.(mov|avi|mp4|webm)$/)">
      <video
        v-bind:src="src"
        v-bind:alt="alt"
        controls="controls"
        v-bind:title="title"
        v-b-tooltip
      />
    </span>
    <span v-else>
      <a
        v-if="src.match(/\:attachment/)"
        v-bind:title="title"
        v-bind:href="src"
        target="_blank"
        v-b-tooltip
      >
        <font-awesome-icon fas icon="paperclip" class="ml-1"></font-awesome-icon>
        {{alt}}
      </a>
      <a v-else v-bind:title="title" v-bind:href="src" target="_blank" v-b-tooltip>
        {{alt}}
        <font-awesome-icon far size="xs" icon="external-link-alt" class="ml-1"></font-awesome-icon>
      </a>
    </span>
  </div>
</template>
<script>
// imgタグの展開を遅らせることで描画負荷を減らす
import bImgLazy from "bootstrap-vue/es/components/image/img-lazy";

// TODO: オーディオプレイヤーとビデオプレイヤー

export default {
  data() {
    return {
      src: this.$slots.default[0].data.attrs.href,
      alt: this.$slots.default[0].data.attrs.href.match(
        ".+/(.+?)([?#;].*)?$"
      )[1],
      title: this.$slots.default[0].data.attrs.title,
      ext: this.$vnode.data.ext
    };
  },
  components: {
    "b-img-lazy": bImgLazy
  }
};
</script>