<template>
  <a v-if="src.match(/[jp?eg|gif|png|webp]$/)" v-bind:title="src" v-bind:href="src" target="_blank" v-b-tooltip>
    <b-img-lazy v-bind:src="src" blank-color="#f8f9fa" v-bind:alt="src" />
  </a>
  <span v-else-if="src.match(/[mp3|m4a|wav?e|aif?f|flac]$/)" v-bind:title="src">
    <audio v-bind:src="src" v-bind:alt="src" controls="controls" />
  </span>
  <span v-else-if="src.match(/[mov|avi|mp4|webm]$/)" v-bind:title="src">
    <video v-bind:src="src" v-bind:alt="src" controls="controls" />
  </span>
</template>
<script>
// imgタグの展開を遅らせることで描画負荷を減らす
import bImgLazy from "bootstrap-vue/es/components/image/img-lazy";

// TODO: オーディオプレイヤーとビデオプレイヤー

export default {
  data(){
     return {
        src: this.$slots.default[0].data.attrs.href,
        ext: this.$vnode.data.ext
     }
  },
  components: {
    "b-img-lazy": bImgLazy
  }
};
</script>