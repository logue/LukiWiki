<template>
    <div class="mx-auto my-auto" style="max-width:18rem;">
        <div class="card card-primary border-light">
            <h5 class="card-header">Vue Component</h5>
            <div class="card-body text-center">
                <h5 class="card-title"><font-awesome-icon size="lg" :icon="['fab', 'vuejs']" size="lg" /></h5>
                <p class="card-text">I'm an example component!</p>
            </div>
        </div>
    </div>
</template>

<script>
import FontAwesomeIcon from '@fortawesome/vue-fontawesome'
import { vuejs } from '@fortawesome/fontawesome-free-brands'

export default {
  mounted() {
    console.log("Component mounted.");
  },
  components: {
    FontAwesomeIcon
  }
};
</script>
