<template>
  <v-container>
    <v-card>
      <v-card-title>
        <h2>Métricas de Movimentação</h2>
      </v-card-title>
      <v-card-text>
        <v-row>
          <v-col v-if="maiorMovimentacao" cols="12" md="6">
            <v-card>
              <v-card-title>Maior Movimentação</v-card-title>
              <v-card-text>{{ maiorMovimentacao }}</v-card-text>
            </v-card>
          </v-col>
          <v-col v-if="menorMovimentacao" cols="12" md="6">
            <v-card>
              <v-card-title>Menor Movimentação</v-card-title>
              <v-card-text>{{ menorMovimentacao }}</v-card-text>
            </v-card>
          </v-col>
        </v-row>
        <v-row>
          <v-col v-if="maiorSoma" cols="12" md="6">
            <v-card>
              <v-card-title>Maior Soma</v-card-title>
              <v-card-text>{{ maiorSoma }}</v-card-text>
            </v-card>
          </v-col>
          <v-col v-if="menorSoma" cols="12" md="6">
            <v-card>
              <v-card-title>Menor Soma</v-card-title>
              <v-card-text>{{ menorSoma }}</v-card-text>
            </v-card>
          </v-col>
        </v-row>
        <v-row>
          <v-col v-if="diaMaisMovimentado" cols="12">
            <v-card>
              <v-card-title>Dia Mais Movimentado</v-card-title>
              <v-card-text>{{ diaMaisMovimentado }}</v-card-text>
            </v-card>
          </v-col>
        </v-row>
        <v-row>
          <v-col cols="12">
            <v-card>
              <v-card-title>Movimentações por Cooperativa e Agência</v-card-title>
              <v-data-table :headers="coopAgenciaHeaders" :items="movimentacoesPorCoopAgencia" />
            </v-card>
          </v-col>
        </v-row>
        <v-row>
          <v-col cols="12">
            <v-card>
              <v-card-title>Movimentações por Hora</v-card-title>
              <v-data-table :headers="horaHeaders" :items="movimentacoesPorHora" />
            </v-card>
          </v-col>
        </v-row>
      </v-card-text>
    </v-card>
  </v-container>
</template>

<script lang="ts">
import { defineComponent, ref, onMounted } from 'vue';
import axios from 'axios';

export default defineComponent({
  name: 'Metrics',
  setup() {
    const maiorMovimentacao = ref(null);
    const menorMovimentacao = ref(null);
    const maiorSoma = ref(null);
    const menorSoma = ref(null);
    const diaMaisMovimentado = ref(null);
    const movimentacoesPorCoopAgencia = ref([]);
    const movimentacoesPorHora = ref([]);

    const coopAgenciaHeaders = [
      { text: 'Cooperativa', value: 'cooperativa' },
      { text: 'Agência', value: 'agencia' },
      { text: 'Total Movimentações', value: 'total_movimentacoes' },
      { text: 'Total Valor', value: 'total_valor' },
    ];

    const horaHeaders = [
      { text: 'Hora', value: 'hora' },
      { text: 'Total Débito', value: 'total_debito' },
      { text: 'Total Crédito', value: 'total_credito' },
    ];

    const fetchMetrics = async () => {
      try {
        const response = await axios.get('/metrics'); // Ajuste a URL conforme necessário
        const data = response.data;

        maiorMovimentacao.value = data.maior_movimentacao;
        menorMovimentacao.value = data.menor_movimentacao;
        maiorSoma.value = data.maior_soma;
        menorSoma.value = data.menor_soma;
        diaMaisMovimentado.value = data.dia_mais_movimentado;
        movimentacoesPorCoopAgencia.value = data.movimentacoes_por_coop_agencia;
        movimentacoesPorHora.value = data.movimentacoes_por_hora;
      } catch (error) {
        console.error('Erro ao buscar métricas:', error);
      }
    };

    onMounted(() => {
      fetchMetrics();
    });

    return {
      maiorMovimentacao,
      menorMovimentacao,
      maiorSoma,
      menorSoma,
      diaMaisMovimentado,
      movimentacoesPorCoopAgencia,
      movimentacoesPorHora,
      coopAgenciaHeaders,
      horaHeaders,
    };
  },
});
</script>

<style scoped>
/* Adicione estilos personalizados aqui, se necessário */
</style>
