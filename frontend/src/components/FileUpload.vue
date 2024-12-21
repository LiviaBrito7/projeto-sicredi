<template>
  <v-container>
    <v-row justify="center">
      <v-col cols="12" md="6">
        <v-card>
          <v-card-title>
            <h2>File Upload</h2>
          </v-card-title>
          <v-card-text>
            <!-- v-file-input para seleção de arquivos -->
            <v-file-input
              v-model="selectedFiles"
              label="Select Files"
              multiple
              outlined
              show-size
            ></v-file-input>

            <!-- Botão para iniciar o upload -->
            <v-btn @click="uploadFiles" color="primary" :disabled="!selectedFiles || !selectedFiles.length">
              Upload
            </v-btn>

            <!-- Barra de progresso para cada arquivo -->
            <div v-if="selectedFiles && selectedFiles.length > 0">
              <div v-for="(progress, index) in fileUploadProgress" :key="index">
                <v-progress-linear
                  :value="progress"
                  height="20"
                  color="green"
                  class="mt-3"
                >
                  <span>{{ progress }}%</span>
                </v-progress-linear>
              </div>
            </div>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>
  </v-container>
</template>

<script lang="ts">
import { ref } from 'vue';
import axios from '../services/axios';

export default {
  name: 'FileUpload',
  setup() {
    // Arquivos selecionados
    const selectedFiles = ref<File[]>([]);
    const fileUploadProgress = ref<number[]>([]);

    // Tratamento após seleção dos arquivos
    const onFileChange = () => {
      if (selectedFiles.value) {
        fileUploadProgress.value = Array(selectedFiles.value.length).fill(0);
      }
    };

    // Função para upload de arquivos
    const uploadFiles = async () => {
      const chunkSize = 1024 * 1024; // 1MB

      // Itera por cada arquivo selecionado
      for (let fileIndex = 0; fileIndex < selectedFiles.value.length; fileIndex++) {
        const file = selectedFiles.value[fileIndex];
        const totalChunks = Math.ceil(file.size / chunkSize);

        // Divide o arquivo em chunks e realiza o upload
        for (let chunkIndex = 0; chunkIndex < totalChunks; chunkIndex++) {
          const start = chunkIndex * chunkSize;
          const end = Math.min(start + chunkSize, file.size);
          const chunk = file.slice(start, end);

          const formData = new FormData();
          formData.append('file', chunk);
          formData.append('chunk', chunkIndex.toString());
          formData.append('totalChunks', totalChunks.toString());
          formData.append('original_name', file.name);

          try {
            await axios.post('/upload', formData);

            // Atualiza o progresso baseado nos chunks enviados
            const progress = ((chunkIndex + 1) / totalChunks) * 100;
            fileUploadProgress.value[fileIndex] = Math.round(progress);
          } catch (error) {
            console.error(`Error uploading chunk ${chunkIndex} of file ${file.name}:`, error);
            fileUploadProgress.value[fileIndex] = 0;
            return; // Interrompe o upload do arquivo em caso de erro
          }
        }

        console.log(`File "${file.name}" uploaded successfully.`);
      }
    };

    return {
      selectedFiles,
      fileUploadProgress,
      onFileChange,
      uploadFiles,
    };
  },
};
</script>

<style scoped>
.v-card {
  padding: 20px;
}
</style>
