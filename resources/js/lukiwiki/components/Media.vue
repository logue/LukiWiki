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
      <figure class="p-3 mb-2 bg-light rounded">
        <figcaption>{{title}}</figcaption>
        <div v-bind:id="name"></div>
        <div class="d-flex justify-content-around">
          <!-- controls -->
          <div>
            <b-button variant="outline-primary" @click="playPause" class="playPause">
              <span v-if="playing">
                <font-awesome-icon :icon="pauseIcon"/>
              </span>
              <span v-if="!playing">
                <font-awesome-icon :icon="playIcon"/>
              </span>
            </b-button>
            <span>{{ currentTime }}/{{ getDuration }}</span>
          </div>
          <div>
            <b-input-group>
              <b-form-input type="range" min="1" max="100" v-model="volume" @input="setVolume"></b-form-input>
              <b-input-group-append>
                <b-button variant="outline-secondary" @click="mute" class="mute">
                  <span v-if="muted">
                    <font-awesome-icon :icon="muteIcon"/>
                  </span>
                  <span v-if="!muted">
                    <font-awesome-icon :icon="volumeUpIcon"/>
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
// Button
import bButton from "bootstrap-vue/es/components/button/button";
// imgタグの展開を遅らせることで描画負荷を減らす
import bImgLazy from "bootstrap-vue/es/components/image/img-lazy";
// Form Input
import bFormInput from "bootstrap-vue/es/components/form-input/form-input";

import bInputGroup from "bootstrap-vue/es/components/input-group/input-group";
import bInputGroupAppend from "bootstrap-vue/es/components/input-group/input-group-append";

// オーディオプレイヤー
// https://github.com/ChadRoberts21/WaveSurferVue/ を参考に作成
import WaveSurfer from "wavesurfer.js";
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome";
import {
  faPlay,
  faPause,
  faStop,
  faVolumeMute,
  faVolumeUp,
  faVolumeDown
} from "@fortawesome/free-solid-svg-icons";

export default {
  data() {
    const data = this.$slots.default[0].data.attrs;
    // 重複しないであろうIDを生成
    const N = 8;
    var S = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
    const id = Array.from(crypto.getRandomValues(new Uint8Array(N)))
      .map(n => S[n % S.length])
      .join("");
    this.name = id;
    this.href = data.href;
    return {
      src: data.href,
      name: id,
      alt: data.href.match(".+/(.+?)([?#;].*)?$")[1],
      title: data.title,
      ext: this.$vnode.data.ext,
      // Websurfer
      wavesurfer: null,
      currentTime: "0:00",
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
  methods: {
    timeDisplay(time) {
      // Hours, minutes and seconds
      let hrs = Math.floor(time / 3600);
      let mins = Math.floor((time % 3600) / 60);
      let secs = Math.floor(time % 60);
      // Output like "1:01" or "4:03:59" or "123:03:59"
      let output = "";
      if (hrs > 0) {
        output += "" + hrs + ":" + (mins < 10 ? "0" : "");
      }
      output += "" + mins + ":" + (secs < 10 ? "0" : "");
      output += "" + secs;
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
  },
  computed: {
    id() {
      console.log(this.name);
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
  mounted() {
    this.$nextTick(() => {
      this.wavesurfer = WaveSurfer.create({
        container: "#" + this.name,
        waveColor: "#ced4da",
        progressColor: "#007bff",
        cursorColor: "#343a40",
        cursorWidth: 1,
        height: "128",
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
  components: {
    "b-img-lazy": bImgLazy,
    "b-form-imput": bFormInput,
    "b-input-group": bInputGroup,
    "b-input-group-append": bInputGroupAppend,
    "font-awesome-icon": FontAwesomeIcon
  }
};
</script>

<style>
.fill {
  width: 100%;
  height: 100%;
}
</style>