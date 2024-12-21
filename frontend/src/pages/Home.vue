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
  name: 'Dashboard',
  components: {
    FileUpload,
    Metrics,
  },
});
</script>

<style scoped>
/* Adicione estilos personalizados aqui, se necessário */
</style>
