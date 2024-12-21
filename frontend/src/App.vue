<template>
  <div id="app">
    <h1>Upload de Arquivos em Chunks</h1>
    <FileUpload />
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import FileUpload from './components/FileUpload.vue';

export default defineComponent({
  name: 'App',
  components: {
    FileUpload,
  },
});
</script>

<style>
/* Estilos globais */
</style>
