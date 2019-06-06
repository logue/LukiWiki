<template>
  <span v-if="src.match(/\.(jpe?g|gif|png|webp)$/)">
    <b-img-lazy
      :id="name"
      v-b-tooltip
      :src="src"
      :alt="alt"
      blank-color="#f8f9fa"
      :title="title"
    />
  </span>
  <span v-else-if="src.match(/\.(mp3|m4a|wave?|aif?f|flac)$/)">
    <figure class="p-3 mb-2 bg-light rounded">
      <figcaption>{{ title }}</figcaption>
      <div :id="name" />
      <div class="d-flex justify-content-around">
        <!-- controls -->
        <div>
          <b-button
            variant="outline-primary"
            class="playPause"
            @click="playPause"
          >
            <span v-if="playing">
              <font-awesome-icon :icon="pauseIcon" />
            </span>
            <span v-if="!playing">
              <font-awesome-icon :icon="playIcon" />
            </span>
          </b-button>
          <span>{{ currentTime }}/{{ getDuration }}</span>
        </div>
        <div>
          <b-input-group>
            <b-form-input
              v-model="volume"
              type="range"
              min="1"
              max="100"
              @input="setVolume"
            />
            <b-input-group-append>
              <b-button
                variant="outline-secondary"
                class="mute"
                @click="mute"
              >
                <span v-if="muted">
                  <font-awesome-icon :icon="muteIcon" />
                </span>
                <span v-else>
                  <font-awesome-icon :icon="volumeUpIcon" />
                </span>
              </b-button>
            </b-input-group-append>
          </b-input-group>
        </div>
      </div>
    </figure>
  </span>
  <span v-else-if="src.match(/\.(mov|avi|mp4|webm)$/)">
    <video
      :id="name"
      v-b-tooltip
      :src="src"
      :alt="alt"
      controls="controls"
      :title="title"
    />
  </span>
  <span v-else>
    <a
      v-if="src.match(/\:attachment/)"
      :id="name"
      v-b-tooltip
      :title="title"
      :href="src"
      target="_blank"
    >
      <font-awesome-icon
        fas
        icon="paperclip"
        class="ml-1"
      />
      {{ alt }}
    </a>
    <a
      v-else
      :id="name"
      v-b-tooltip
      :title="title"
      :href="src"
      target="_blank"
    >
      {{ alt }}
      <font-awesome-icon
        far
        size="xs"
        icon="external-link-alt"
        class="ml-1"
      />
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
  VBTooltip
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
  faVolumeUp } from '@fortawesome/free-solid-svg-icons';
library.add(
  faPaperclip,
  faPause,
  faPlay,
  faStop,
  faVolumeDown,
  faVolumeMute,
  faVolumeUp
);

// オーディオプレイヤー
// https://github.com/ChadRoberts21/WaveSurferVue/ を参考に作成
import WaveSurfer from 'wavesurfer.js';

export default {
  components: {
    'b-button':BButton,
    'b-img-lazy': BImgLazy,
    'b-form-input': BFormInput,
    'b-input-group': BInputGroup,
    'b-input-group-append': BInputGroupAppend,
    'font-awesome-icon': FontAwesomeIcon
  },
  directives: {
    'b-tooltip': VBTooltip
  },
  data() {
    const data = this.$slots.default[0].data.attrs;
    
    this.href = data.href;
    return {
      src: data.href,
      name: this.name,
      alt: data.href.match('.+/(.+?)([?#;].*)?$')[1],
      title: data.title,
      ext: this.$vnode.data.ext,
      // Websurfer
      wavesurfer: null,
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
      volumeDownIcon: faVolumeDown
    };
  },
  computed: {
    id() {
      //console.log(this.name);
      return `#${this.name}`;
    },
    getDuration() {
      if (this.wavesurfer) {
        return this.timeDisplay(this.wavesurfer.getDuration());
      } else {
        return null;
      }
    },
    getPlaybackRate() {
      if (this.wavesurfer) {
        return this.wavesurfer.getPlaybackRate();
      } else {
        return null;
      }
    },
    getVolume() {
      if (this.wavesurfer) {
        return this.wavesurfer.getVolume();
      } else {
        return null;
      }
    },
    getMute() {
      if (this.wavesurfer) {
        return this.wavesurfer.getMute();
      } else {
        return false;
      }
    }
  },
  watch: {
    audio(newValue, oldValue) {
      this.wavesurfer.load(newValue);
    }
  },
  created(){
    // 重複しないであろうランダムのIDを生成
    const N = 8;
    var S = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    const id = Array.from(crypto.getRandomValues(new Uint8Array(N)))
      .map(n => S[n % S.length])
      .join('');
    this.name = 'media_'+id;
  },
  mounted() {
    this.$nextTick(() => {
      this.wavesurfer = WaveSurfer.create({
        container: '#' + this.name,
        waveColor: '#ced4da',
        progressColor: '#007bff',
        cursorColor: '#343a40',
        cursorWidth: 1,
        height: '128',
        fillParent: true,
        loopSelection: true,
        interact: true,
        removeMediaElementOnDestroy: this.removeMediaElementOnDestroy
      });

      this.wavesurfer.load(this.href);
    });
  },
  beforeDestroy() {
    this.wavesurfer.destroy();
  },
  methods: {
    timeDisplay(time) {
      // Hours, minutes and seconds
      let hrs = Math.floor(time / 3600);
      let mins = Math.floor((time % 3600) / 60);
      let secs = Math.floor(time % 60);
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
      this.playing = this.wavesurfer.isPlaying();
      if (this.playing) {
        this.pause();
      } else {
        this.play();
      }
      this.playing = this.wavesurfer.isPlaying();
    },
    play() {
      this.timeInterval = setInterval(() => {
        this.currentTime = this.timeDisplay(this.wavesurfer.getCurrentTime());
      }, 1000);
      this.wavesurfer.play();
    },
    pause() {
      this.wavesurfer.pause();
    },
    stop() {
      this.wavesurfer.stop();
      this.timeInterval = null;
      this.currentTime = this.timeDisplay(0);
    },
    mute() {
      this.muted = this.getMute;
      this.wavesurfer.setMute(!this.muted);
      this.muted = this.getMute;
    },
    setVolume() {
      let floatValue = this.volume / 100;
      this.wavesurfer.setVolume(Number.parseFloat(floatValue.toFixed(2)));
    }
  }
};
</script>

<style>
.fill {
  width: 100%;
  height: 100%;
}
</style>