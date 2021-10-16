<template>
  <table class="table table-bordered">
    <thead>
      <tr>
        <td colspan="7">
          <div class="d-flex justify-content-between">
            <div class="order-1">
              {{ yearMonth }}
            </div>
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
          {{ day }}
        </td>
      </tr>
    </tbody>
  </table>
</template>

<script>
// Bootstrap Vue
import { VBTooltip, BButton } from 'bootstrap-vue';
// FontAwesome
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { library } from '@fortawesome/fontawesome-svg-core';
import {
  faChevronLeft,
  faChevronRight,
} from '@fortawesome/free-solid-svg-icons';

library.add(faChevronLeft, faChevronRight);

import moment from 'moment';

export default {
  components: {
    'b-button': BButton,
    'font-awesome-icon': FontAwesomeIcon,
  },
  directives: {
    'b-tooltip': VBTooltip,
  },
  data: () => ({
    current: 0,
  }),
  computed: {
    currentMoment() {
      return moment().add(this.current, 'months');
    },
    yearMonth() {
      return this.currentMoment.format('YYYY MM');
    },
    calendarData() {
      // この月に何日まであるかを算出
      const numOfMonth = this.currentMoment.endOf('month').date();
      // この月の1日〜最終日までの配列
      const daysOfMonth = [...Array(numOfMonth).keys()].map((i) => ++i);
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
      return data.filter(
        (week) => week.filter((day) => day != null).length > 0
      );
    },
  },
  methods: {
    goNextMonth() {
      this.current++;
    },
    goPrevMonth() {
      this.current--;
    },
  },
};
</script>
