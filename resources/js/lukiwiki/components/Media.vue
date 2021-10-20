<template>
  <figure v-if="src.match(/\.(?:jpe?g|gif|png|webp)$/)">
    <b-img-lazy
      v-b-tooltip
      v-bind="imgProps"
      :src="src"
      :alt="alt"
      :title="title"
    />
  </figure>
  <figure
    v-else-if="src.match(/.(?:mp3|m4a|wav|aif?f|flac)$/)"
    class="p-3 mb-2 bg-light rounded"
  >
    <b-skeleton-wrapper :loading="loading">
      <b-row>
        <b-col>
          <figcaption class="flex-grow-1" v-text="title" />
        </b-col>
        <b-col>
          <b-input-group>
            <b-input-group-prepend>
              <b-button
                variant="outline-primary"
                class="playPause"
                size="sm"
                @click="playPause"
              >
                <font-awesome-icon :icon="playing ? pauseIcon : playIcon" />
              </b-button>
            </b-input-group-prepend>
            <b-form-input
              size="sm"
              :value="currentTime + '/' + getDuration"
              readonly
            />
          </b-input-group>
        </b-col>
        <b-col>
          <b-input-group>
            <b-form-input
              v-model="volume"
              type="range"
              size="sm"
              min="1"
              max="100"
              @input="setVolume"
            />
            <b-input-group-append>
              <b-button
                size="sm"
                variant="outline-secondary"
                class="mute"
                @click="mute"
              >
                <font-awesome-icon :icon="muted ? muteIcon : volumeUpIcon" />
              </b-button>
            </b-input-group-append>
          </b-input-group>
        </b-col>
      </b-row>
    </b-skeleton-wrapper>
    <wavesurfer ref="surf" :src="src" :option="wavesurferOption"></wavesurfer>
  </figure>
  <figure v-else-if="src.match(/\.(?:mov|avi|mp4|webm)$/)">
    <figcaption v-text="title" />
    <video
      :id="name"
      v-b-tooltip
      :src="src"
      :alt="alt"
      controls="controls"
      :title="title"
    />
  </figure>
  <span v-else>
    <a
      v-if="src.match(/\:attachment/)"
      :id="name"
      v-b-tooltip
      :title="title"
      :href="src"
      target="_blank"
    >
      <font-awesome-icon fas icon="paperclip" class="ml-1" />
      {{ alt }}
    </a>
    <a v-else :id="name" v-b-tooltip :title="title" :href="src" target="_blank">
      {{ alt }}
      <font-awesome-icon far size="xs" icon="external-link-alt" class="ml-1" />
    </a>
  </span>
</template>

<script>
// Bootstrap Vue
import {
  BButton,
  BFormInput,
  BImgLazy,
  BInputGroup,
  BInputGroupAppend,
  BInputGroupPrepend,
  VBTooltip,
  BCol,
  BRow,
  BSkeletonWrapper,
} from 'bootstrap-vue';

// FontAwesome
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { library } from '@fortawesome/fontawesome-svg-core';
import {
  faPaperclip,
  faPause,
  faPlay,
  faStop,
  faVolumeDown,
  faVolumeMute,
  faVolumeUp,
} from '@fortawesome/free-solid-svg-icons';
library.add(
  faPaperclip,
  faPause,
  faPlay,
  faStop,
  faVolumeDown,
  faVolumeMute,
  faVolumeUp
);

import WaveSurferVue from 'wavesurfer.js-vue/src/WaveSurferVue.vue';

export default {
  components: {
    'b-button': BButton,
    'b-img-lazy': BImgLazy,
    'b-form-input': BFormInput,
    'b-input-group': BInputGroup,
    'b-input-group-append': BInputGroupAppend,
    'b-input-group-prepend': BInputGroupPrepend,
    'b-col': BCol,
    'b-row': BRow,
    'b-skeleton-wrapper': BSkeletonWrapper,
    'font-awesome-icon': FontAwesomeIcon,
    wavesurfer: WaveSurferVue,
  },
  directives: {
    'b-tooltip': VBTooltip,
  },
  data() {
    const data = this.$slots.default[0].data.attrs;
    this.href = data.href;
    return {
      src: data.href,
      name: this.name,
      alt: data.href.match('.+/(.+?)([?#;].*)?$')[1],
      title: data.title,
      player: null,
      ext: this.$vnode.data.ext,
      loading: true,
      imgProps: {
        blankColor: '#f8f9fa',
      },
      wavesurferOption: {
        waveColor: '#ced4da',
        progressColor: '#007bff',
        cursorColor: '#343a40',
        cursorWidth: 1,
        height: '128',
        fillParent: true,
        loopSelection: true,
        interact: true,
        removeMediaElementOnDestroy: false,
      },
      // Websurfer
      currentTime: '0:00',
      timeInterval: null,
      volume: 100,
      playing: false,
      muted: false,
      playIcon: faPlay,
      pauseIcon: faPause,
      stopIcon: faStop,
      muteIcon: faVolumeMute,
      volumeUpIcon: faVolumeUp,
      volumeDownIcon: faVolumeDown,
    };
  },
  computed: {
    getDuration() {
      if (this.player) {
        return this.timeDisplay(this.player.getDuration());
      } else {
        return null;
      }
    },
    getPlaybackRate() {
      if (this.$refs.player) {
        return this.player.getPlaybackRate();
      } else {
        return null;
      }
    },
    getVolume() {
      if (this.player) {
        return this.player.getVolume();
      } else {
        return null;
      }
    },
    getMute() {
      if (this.player) {
        return this.player.getMute();
      } else {
        return false;
      }
    },
  },
  watch: {
    audio(newValue) {
      this.player.load(newValue);
    },
  },
  created() {},
  mounted() {
    if (this.$refs.surf) {
      this.player = this.$refs.surf.waveSurfer;
      this.player.on('ready', () => {
        this.loading = false;
      });
    } else {
      this.loading = false;
    }
  },
  methods: {
    timeDisplay(time) {
      // Hours, minutes and seconds
      const hrs = Math.floor(time / 3600);
      const mins = Math.floor((time % 3600) / 60);
      const secs = Math.floor(time % 60);
      // Output like "1:01" or "4:03:59" or "123:03:59"
      let output = '';
      if (hrs > 0) {
        output += '' + hrs + ':' + (mins < 10 ? '0' : '');
      }
      output += '' + mins + ':' + (secs < 10 ? '0' : '');
      output += '' + secs;
      return output;
    },
    playPause() {
      this.playing = this.player.isPlaying();
      if (this.playing) {
        this.pause();
      } else {
        this.play();
      }
      this.playing = this.player.isPlaying();
    },
    play() {
      this.timeInterval = setInterval(() => {
        this.currentTime = this.timeDisplay(this.player.getCurrentTime());
      }, 1000);
      this.player.play();
    },
    pause() {
      this.player.pause();
    },
    stop() {
      this.player.stop();
      this.timeInterval = null;
      this.currentTime = this.timeDisplay(0);
    },
    mute() {
      this.muted = this.getMute;
      this.player.setMute(!this.muted);
      this.muted = this.getMute;
    },
    setVolume() {
      const floatValue = this.volume / 100;
      this.player.setVolume(Number.parseFloat(floatValue.toFixed(2)));
    },
  },
};
</script>

<style>
.fill {
  width: 100%;
  height: 100%;
}
</style>
