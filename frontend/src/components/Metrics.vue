<template>
  <v-container>
    <v-card>
      <v-card-title>
        <h2>Métricas do Arquivo Processado</h2>
      </v-card-title>
      <v-card-text>
        <div v-if="metrics">
          <!-- Movimentações por Data -->
          <h3>Movimentações por Data</h3>
          <p>
            Maior: {{ metrics.maior_movimentacao.data || 'N/A' }} -
            {{ metrics.maior_movimentacao.total || 0 }} movimentações
          </p>
          <p>
            Menor: {{ metrics.menor_movimentacao.data || 'N/A' }} -
            {{ metrics.menor_movimentacao.total || 0 }} movimentações
          </p>

          <!-- Soma de Movimentações por Data -->
          <h3>Soma de Movimentações por Data</h3>
          <p>
            Maior: {{ metrics.maior_soma.data || 'N/A' }} -
            R$ {{ formatCurrency(metrics.maior_soma.total) }}
          </p>
          <p>
            Menor: {{ metrics.menor_soma.data || 'N/A' }} -
            R$ {{ formatCurrency(metrics.menor_soma.total) }}
          </p>

          <!-- Dia da Semana com Mais Movimentações -->
          <h3>Dia da Semana com Mais Movimentações (RX1 e PX1)</h3>
          <p>
            Dia: {{ metrics.dia_semana_mais_movimentacoes.dia || 'N/A' }} -
            {{ metrics.dia_semana_mais_movimentacoes.total || 0 }} movimentações
          </p>

          <!-- Movimentações por Coop/Agência -->
          <h3>Movimentações por Coop/Agência</h3>
          <v-list>
            <v-list-item-group>
              <v-list-item
                v-for="(item, index) in metrics.movimentacoes_por_coop_agencia || []"
                :key="index"
              >
                <v-list-item-content>
                  <v-list-item-title>
                    {{ item.cooperativa }} - {{ item.agencia }}
                  </v-list-item-title>
                  <v-list-item-subtitle>
                    {{ item.total_movimentacoes }} movimentações -
                    R$ {{ formatCurrency(item.total_valor) }}
                  </v-list-item-subtitle>
                </v-list-item-content>
              </v-list-item>
            </v-list-item-group>
          </v-list>

          <!-- Relação de Créditos x Débitos -->
          <h3>Relação de Créditos x Débitos ao Longo das Horas do Dia</h3>
          <apexchart
            :options="creditDebitChartOptions"
            :series="creditDebitChartData"
          />
        </div>
        <v-btn @click="fetchMetrics" color="primary">Carregar Métricas</v-btn>
      </v-card-text>
    </v-card>
  </v-container>
</template>

<script lang="ts">
import { ref } from 'vue';
import axios from '../services/axios';
import VueApexCharts from 'vue3-apexcharts';

// Tipos para descrever os dados retornados pela API
interface MovimentacaoData {
  data: string;
  total: number | null; // Total pode ser nulo ou número
}

interface SomaData {
  data: string;
  total: number | null; // Total pode ser nulo ou número
}

interface DiaSemanaData {
  dia: string;
  total: number;
}

interface MovimentacaoCoopAgencia {
  cooperativa: string;
  agencia: string;
  total_movimentacoes: number;
  total_valor: number | null; // Pode ser nulo
}

interface RelacaoCreditoDebito {
  hora: string;
  total_credito: number;
  total_debito: number;
}

interface Metrics {
  maior_movimentacao: MovimentacaoData;
  menor_movimentacao: MovimentacaoData;
  maior_soma: SomaData;
  menor_soma: SomaData;
  dia_semana_mais_movimentacoes: DiaSemanaData;
  movimentacoes_por_coop_agencia: MovimentacaoCoopAgencia[];
  relacao_creditos_debitos: RelacaoCreditoDebito[];
}

export default {
  name: 'MetricsDisplay',
  components: {
    apexchart: VueApexCharts,
  },
  setup() {
    const metrics = ref<Metrics | null>(null);
    const creditDebitChartData = ref([
      { name: 'Créditos', data: [] as number[] },
      { name: 'Débitos', data: [] as number[] },
    ]);
    const creditDebitChartOptions = ref({
      chart: {
        type: 'bar',
        height: 350,
      },
      xaxis: {
        categories: Array.from({ length: 24 }, (_, i) => `${i}h`), // Horas do dia
        title: {
          text: 'Horas do Dia',
        },
      },
      yaxis: {
        title: {
          text: 'Valores (R$)',
        },
      },
      tooltip: {
        y: {
          formatter: (val: number) => `R$ ${val.toFixed(2).replace('.', ',')}`,
        },
      },
    });

    const fetchMetrics = async () => {
      try {
        const response = await axios.get<Metrics>('/metrics');
        metrics.value = response.data;

        // Preenche os dados para o gráfico de Créditos x Débitos
        const creditSeries = metrics.value.relacao_creditos_debitos.map((item) => item.total_credito);
        const debitSeries = metrics.value.relacao_creditos_debitos.map((item) => item.total_debito);

        creditDebitChartData.value = [
          { name: 'Créditos', data: creditSeries },
          { name: 'Débitos', data: debitSeries },
        ];
      } catch (error) {
        console.error('Erro ao buscar métricas:', error);
      }
    };

    const formatCurrency = (value: number | null): string => {
      if (typeof value === 'number') {
        return value.toFixed(2).replace('.', ','); // Formata como moeda (exemplo brasileiro)
      }
      return '0,00'; // Retorno padrão caso o valor seja inválido
    };

    return {
      metrics,
      creditDebitChartData,
      creditDebitChartOptions,
      fetchMetrics,
      formatCurrency,
    };
  },
};
</script>

<style scoped>
.v-card {
  padding: 20px;
}

h3 {
  margin-top: 20px;
}
</style>
