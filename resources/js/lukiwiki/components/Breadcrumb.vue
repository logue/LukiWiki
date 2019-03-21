<template>
  <b-breadcrumb :items="items"/>
</template>
<script>
import bBreadcrumb from "bootstrap-vue/es/components/breadcrumb/breadcrumb";
import bBreadcrumbItem from "bootstrap-vue/es/components/breadcrumb/breadcrumb-item";
export default {
  data() {
    return {
      items: [
        {
          text: "Admin",
          href: "#"
        },
        {
          text: "Manage",
          href: "#"
        },
        {
          text: "Library",
          active: true
        }
      ]
    };
  },
  components: {
    "b-breadcrumb": bBreadcrumb,
    "b-breadcrumb-item": bBreadcrumbItem
  }
};
</script>