<script>
// 経過時間を追記
export default {
  bind: el => {
    const data = el.dataset;
    if (data.timestamp) {
      const UNITS = { m: 60, h: 24, d: 1 };
      const UTIME = parseInt(new Date() / 1000);

      let passage = Math.max(0, (UTIME - data.timestamp) / 60);
      let unit;
      for (unit in UNITS) {
        if (passage < UNITS[unit]) {
          break;
        }

        passage /= UNITS[unit];
      }

      el.title += ' (' + Math.floor(passage) + unit + ')';
    }
  }
  /*
  update: el => {
    console.log('update');
  },
  unbind: el => {
    console.log('remove');
  },
  */
};
</script>