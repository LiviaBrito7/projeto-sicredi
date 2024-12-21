<template>
  <v-app>
    <Home />
  </v-app>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import Home from './pages/Home.vue';
export default defineComponent({
  name: 'App',
  components: {
    Home,
  },
});
</script>

<style>
/* Adicione estilos globais aqui, se necessário */
</style>
