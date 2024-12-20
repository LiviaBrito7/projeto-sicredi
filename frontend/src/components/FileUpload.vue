<template>
  <div class="p-4">
    <div class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center">
      <input
        type="file"
        ref="fileInput"
        @change="handleFileChange"
        class="hidden"
      />
      <button
        @click="triggerFileInput"
        class="bg-blue-500 text-white px-4 py-2 rounded"
      >
        Selecionar Arquivo
      </button>
      <p v-if="selectedFile" class="mt-2">
        {{ selectedFile.name }} ({{ formatFileSize(selectedFile.size) }})
      </p>
      <button
        v-if="selectedFile"
        @click="uploadFile"
        class="bg-green-500 text-white px-4 py-2 rounded mt-4"
        :disabled="uploading"
      >
        {{ uploading ? 'Enviando...' : 'Enviar Arquivo' }}
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import axios from '../services/axios';

const fileInput = ref<HTMLInputElement | null>(null);
const selectedFile = ref<File | null>(null);
const uploading = ref(false);

const triggerFileInput = () => {
  fileInput.value?.click();
};

const handleFileChange = (event: Event) => {
  const target = event.target as HTMLInputElement;
  if (target.files) {
    selectedFile.value = target.files[0];
  }
};

const formatFileSize = (bytes: number) => {
  if (bytes === 0) return '0 Bytes';
  const k = 1024;
  const sizes = ['Bytes', 'KB', 'MB', 'GB'];
  const i = Math.floor(Math.log(bytes) / Math.log(k));
  return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
};

const uploadFile = async () => {
  if (!selectedFile.value) return;

  uploading.value = true;
  try {
    const formData = new FormData();
    formData.append('file', selectedFile.value);

    // Usando a instância do Axios para enviar o arquivo
    const response = await axios.post('/upload', formData);

    console.log('Arquivo enviado com sucesso:', response.data);
    selectedFile.value = null;
  } catch (error) {
    console.error('Erro ao enviar arquivo:', error);
  } finally {
    uploading.value = false;
  }
};
</script>
