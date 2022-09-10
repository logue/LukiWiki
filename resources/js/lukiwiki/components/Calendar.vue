<template>
  <table class="table table-bordered">
    <caption>Calendar</caption>
    <thead>
      <tr>
        <td colspan="7">
          <div class="d-flex justify-content-between">
            <div class="order-1">{{ year }}/{{ month }}</div>
            <b-button class="order-0" @click="goPrevMonth">prev</b-button>
            <b-button class="order-2" @click="goNextMonth">next</b-button>
          </div>
        </td>
      </tr>
      <tr>
        <th scope="col" style="color: var(--red)">日</th>
        <th scope="col">月</th>
        <th scope="col">火</th>
        <th scope="col">水</th>
        <th scope="col">木</th>
        <th scope="col">金</th>
        <th scope="col" style="color: var(--blue)">土</th>
      </tr>
    </thead>
    <tbody>
      <tr v-for="week in calendarData" :key="week">
        <td v-for="day in week" :key="day" class="text-center">
          <a v-if="day && dayLink(day)" :href="dayLink(day)" v-text="day" />
          <span v-else v-text="day" />
        </td>
      </tr>
    </tbody>
  </table>
</template>

<script>
// Bootstrap Vue
import { VBTooltip, BButton } from 'bootstrap-vue';
// FontAwesome
import { library } from '@fortawesome/fontawesome-svg-core';
import {
  faChevronLeft,
  faChevronRight,
} from '@fortawesome/free-solid-svg-icons';

library.add(faChevronLeft, faChevronRight);

import moment from 'moment';
import axios from 'axios';

export default {
  components: {
    'b-button': BButton,
  },
  directives: {
    'b-tooltip': VBTooltip,
  },
  props: {
    page: String,
  },
  data: () => ({
    current: 0,
    pages: [],
  }),
  computed: {
    currentMoment() {
      return moment().add(this.current, 'months');
    },
    year() {
      return this.currentMoment.format('YYYY');
    },
    month() {
      return this.currentMoment.format('MM');
    },
    calendarData() {
      // この月に何日まであるかを算出
      const numOfMonth = this.currentMoment.endOf('month').date();
      // この月の1日〜最終日までの配列
      const daysOfMonth = [...Array(numOfMonth).keys()].map(i => ++i);
      // 1日の曜日（0~6の数値で取得）
      const firstWeekDay = this.currentMoment.startOf('month').weekday();
      // 週ごとの二次元配列を生成
      const data = [...Array(6)].map((empty, weekIndex) =>
        [...Array(7)].map((empty, dayIndex) => {
          const i = 7 * weekIndex + dayIndex - firstWeekDay;
          if (i < 0 || daysOfMonth[i] === undefined) {
            return null;
          }
          return daysOfMonth[i];
        })
      );
      // 全てnullの配列があれば除去する
      return data.filter(week => week.filter(day => day != null).length > 0);
    },
  },
  async created() {
    const ret = await axios.get('/:api/list:' + this.page);
    const { data } = ret;
    this.pages = data;
  },
  methods: {
    goNextMonth() {
      this.current++;
    },
    goPrevMonth() {
      this.current--;
    },
    dayLink(day = '0') {
      const dayStr = `${this.currentMoment.format('YYYY-MM')}-${day
        .toString()
        .padStart(2, '0')}`;

      return Object.keys(this.pages).includes(`${this.page}/${dayStr}`)
        ? `${this.page}/${dayStr}`
        : null;
    },
    async hasPage(d) {
      return;
    },
  },
};
</script>
