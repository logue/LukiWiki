<template>
  <b-breadcrumb :items="items" />
</template>
<script>
import { BBreadcrumb, BBreadcrumbItem } from 'bootstrap-vue';
export default {
  components: {
    'b-breadcrumb': BBreadcrumb,
    'b-breadcrumb-item': BBreadcrumbItem
  },
  data() {
    return {
      items: [
        {
          text: 'Admin',
          href: '#'
        },
        {
          text: 'Manage',
          href: '#'
        },
        {
          text: 'Library',
          active: true
        }
      ]
    };
  }
};
</script>