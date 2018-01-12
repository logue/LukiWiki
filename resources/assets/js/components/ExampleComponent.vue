<template>
    <b-card header="Vue Component" style="max-width: 20rem;" class="mx-auto">
        <font-awesome-icon size="lg" :icon="['fab', 'vuejs']" />
        <p class="card-text">I'm an example component!</p>
    </b-card>
</template>

<script>
import FontAwesomeIcon from "@fortawesome/vue-fontawesome";
import { vuejs } from "@fortawesome/fontawesome-free-brands";
import bCard from "bootstrap-vue/es/components/card/card";

export default {
  mounted() {
    console.log("Component mounted.");
  },
  components: {
    FontAwesomeIcon,
    bCard
  }
};
</script>
