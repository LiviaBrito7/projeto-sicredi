<template>
  <v-container>
    <v-card class="mx-auto" max-width="800px" outlined>
      <v-card-title class="d-flex justify-space-between align-center">
        <h2 class="headline font-weight-bold">Métricas do Arquivo Processado</h2>
        <v-btn @click="fetchMetrics" color="primary" outlined>
          Carregar Métricas
        </v-btn>
      </v-card-title>

      <v-divider></v-divider>

      <v-card-text>
        <div v-if="metrics">
          <!-- Movimentações por Data -->
          <v-row>
            <v-col v-for="(item, index) in [
              { title: 'Maior', data: metrics.maior_movimentacao },
              { title: 'Menor', data: metrics.menor_movimentacao }
            ]" :key="index" cols="12" sm="6">
              <v-card elevation="2" class="pa-4 text-center">
                <h3 class="subheading">{{ item.title }} Movimentação</h3>
                <p><strong>Data:</strong> {{ item.data.data || 'N/A' }}</p>
                <p><strong>Total:</strong> {{ item.data.total || 0 }} movimentações</p>
              </v-card>
            </v-col>
          </v-row>

          <!-- Soma de Movimentações por Data -->
          <v-row>
            <v-col v-for="(item, index) in [
              { title: 'Maior', data: metrics.maior_soma },
              { title: 'Menor', data: metrics.menor_soma }
            ]" :key="index" cols="12" sm="6">
              <v-card elevation="2" class="pa-4 text-center">
                <h3 class="subheading">{{ item.title }} Soma</h3>
                <p><strong>Data:</strong> {{ item.data.data || 'N/A' }}</p>
                <p><strong>Total:</strong> R$ {{ formatCurrency(item.data.total) }}</p>
              </v-card>
            </v-col>
          </v-row>

          <!-- Dia da Semana com Mais Movimentações -->
          <v-card class="mt-4" elevation="2" outlined>
            <v-card-title class="headline">Dia da Semana com Mais Movimentações (RX1 e PX1)</v-card-title>
            <v-card-text class="text-center">
              <p>
                <strong>Dia:</strong> {{ metrics.dia_semana_mais_movimentacoes.dia || 'N/A' }}<br />
                <strong>Total:</strong> {{ metrics.dia_semana_mais_movimentacoes.total || 0 }} movimentações
              </p>
            </v-card-text>
          </v-card>

          <!-- Movimentações por Coop/Agência -->
          <v-card class="mt-4" elevation="2" outlined>
            <v-card-title class="headline">Movimentações por Coop/Agência</v-card-title>
            <v-card-text>
              <v-list>
                <v-list-item-group>
                  <v-list-item
                    v-for="(item, index) in metrics.movimentacoes_por_coop_agencia || []"
                    :key="index"
                    class="pa-2"
                  >
                    <v-list-item-content>
                      <v-list-item-title class="font-weight-bold">
                        {{ item.cooperativa }} - {{ item.agencia }}
                      </v-list-item-title>
                      <v-list-item-subtitle>
                        {{ item.total_movimentacoes }} movimentações - R$ {{ formatCurrency(item.total_valor) }}
                      </v-list-item-subtitle>
                    </v-list-item-content>
                  </v-list-item>
                </v-list-item-group>
              </v-list>
            </v-card-text>
          </v-card>

          <!-- Gráfico de Créditos x Débitos -->
          <v-card class="mt-4" elevation="2" outlined>
            <v-card-title class="headline">Relação de Créditos x Débitos ao Longo das Horas do Dia</v-card-title>
            <v-card-text>
              <apexchart
                :options="creditDebitChartOptions"
                :series="creditDebitChartData"
              />
            </v-card-text>
          </v-card>
        </div>
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
  margin-bottom: 20px;
}

.v-card-title {
  font-weight: bold;
}

.v-divider {
  margin: 20px 0;
}

h3 {
  margin-top: 20px;
}

.subheading {
  font-weight: bold;
  margin-bottom: 8px;
}

.v-btn {
  text-transform: none;
  font-weight: 500;
}

.v-list-item-content {
  padding: 8px 0;
}

.apexcharts-canvas {
  background: #f5f5f5;
  border-radius: 8px;
}
</style>
