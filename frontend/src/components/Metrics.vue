<template>
  <div>
    <h1>Métricas de Movimentações</h1>

    <h2>Data com Maior Movimentação: {{ maiorMovimentacao?.data }} ({{ maiorMovimentacao?.total }})</h2>
    <h2>Data com Menor Movimentação: {{ menorMovimentacao?.data }} ({{ menorMovimentacao?.total }})</h2>
    <h2>Data com Maior Soma: {{ maiorSoma?.data }} ({{ maiorSoma?.total }})</h2>
    <h2>Data com Menor Soma: {{ menorSoma?.data }} ({{ menorSoma?.total }})</h2>
    <h2>Dia da Semana com Mais Movimentações (RX1 e PX1): {{ diaMaisMovimentado?.dia }} ({{ diaMaisMovimentado?.total }})</h2>

    <h3>Movimentações por Cooperativa/Agência</h3>
    <ul>
      <li v-for="item in movimentacoesPorCoopAgencia" :key="`${item.cooperativa}-${item.agencia}`">
        {{ item.cooperativa }} - {{ item.agencia }}: {{ item.total_movimentacoes }} movimentações, Total: {{ item.total_valor }}
      </li>
    </ul>

    <h3>Relação de Créditos x Débitos por Hora</h3>
    <ul>
      <li v-for="item in movimentacoesPorHora" :key="item.hora">
        Hora {{ item.hora }}: Débito Total: {{ item.total_debito }}, Crédito Total: {{ item.total_credito }}
      </li>
    </ul>
  </div>
</template>

<script lang="ts">
import { defineComponent, ref, onMounted } from 'vue';
import axios from 'axios';

interface Movimentacao {
  data: string;
  total: number;
}

interface CoopAgencia {
  cooperativa: string;
  agencia: string;
  total_movimentacoes: number;
  total_valor: number;
}

interface HoraMovimentacao {
  hora: number;
  total_debito: number;
  total_credito: number;
}

export default defineComponent({
  name: 'Metrics',
  setup() {
    const maiorMovimentacao = ref<Movimentacao | null>(null);
    const menorMovimentacao = ref<Movimentacao | null>(null);
    const maiorSoma = ref<Movimentacao | null>(null);
    const menorSoma = ref<Movimentacao | null>(null);
    const diaMaisMovimentado = ref<Movimentacao | null>(null);
    const movimentacoesPorCoopAgencia = ref<CoopAgencia[]>([]);
    const movimentacoesPorHora = ref<HoraMovimentacao[]>([]);

    const fetchMetrics = async () => {
      try {
        const response = await axios.get('/api/metrics');
        const data = response.data;

        maiorMovimentacao.value = data.maior_movimentacao ?? null;
        menorMovimentacao.value = data.menor_movimentacao ?? null;
        maiorSoma.value = data.maior_soma ?? null;
        menorSoma.value = data.menor_soma ?? null;
        diaMaisMovimentado.value = data.dia_mais_movimentado ?? null;
        movimentacoesPorCoopAgencia.value = data.movimentacoes_por_coop_agencia ?? [];
        movimentacoesPorHora.value = data.movimentacoes_por_hora ?? [];
      } catch (error) {
        console.error('Erro ao buscar métricas:', error);
      }
    };

    onMounted(fetchMetrics);

    return {
      maiorMovimentacao,
      menorMovimentacao,
      maiorSoma,
      menorSoma,
      diaMaisMovimentado,
      movimentacoesPorCoopAgencia,
      movimentacoesPorHora,
    };
  },
});
</script>

<style scoped>
h1 {
  color: #333;
}
h2 {
  margin-top: 20px;
}
h3 {
  margin-top: 15px;
}
ul {
  list-style-type: none;
  padding: 0;
}
li {
  margin: 5px 0;
}
</style>
