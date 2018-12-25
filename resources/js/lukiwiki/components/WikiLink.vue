<template>
  <a v-bind:href="url" v-bind:title="title" v-b-tooltip>{{ name }}</a>
</template>
<script>
export default {
  data() {
    // 経過時間を追記
    const UNITS = { m: 60, h: 24, d: 1 };
    const UTIME = parseInt(new Date() / 1000);

    let passage = Math.max(
      0,
      (UTIME - this.$slots.default[0].data.attrs.timestamp) / 60
    );
    let unit;
    for (unit in UNITS) {
      if (passage < UNITS[unit]) {
        break;
      }

      passage /= UNITS[unit];
    }
    this.title =
      this.$slots.default[0].data.attrs.title +
      " (" +
      Math.floor(passage) +
      unit +
      ")";
    return {
      name: this.$slots.default[0].children[0].text,
      url: this.$slots.default[0].data.attrs.href
    };
  },
  created: function() {
    console.log(this.$slots.default);
  }
};
</script>