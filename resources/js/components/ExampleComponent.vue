<template>
    <b-card header="Vue Component" style="max-width: 20rem;" class="mx-auto">
        <font-awesome-icon size="lg" :icon="['fab', 'vuejs']" />
        <p class="card-text">I'm an example component!</p>
    </b-card>
</template>

<script>
import { library } from '@fortawesome/fontawesome-svg-core'
import { bCard } from '@fortawesome/free-solid-svg-icons'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'

library.add(bCard)

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
