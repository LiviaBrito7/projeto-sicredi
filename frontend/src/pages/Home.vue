<template>
  <v-container>
    <fileUpload />
    <Metrics />
  </v-container>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import FileUpload from '../components/FileUpload.vue';
import Metrics from '../components/Metrics.vue';

export default defineComponent({
  name: 'Home',
  components: {
    FileUpload,
    Metrics,
  },
});
</script>


