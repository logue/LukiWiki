<template>
  <b-breadcrumb :items="items" />
</template>

<script>
import { BBreadcrumb } from 'bootstrap-vue';

export default {
  components: {
    'b-breadcrumb': BBreadcrumb,
  },
  data() {
    return {
      items: [
        {
          text: 'Admin',
          href: '#',
        },
        {
          text: 'Manage',
          href: '#',
        },
        {
          text: 'Library',
          active: true,
        },
      ],
    };
  },
};
</script>
