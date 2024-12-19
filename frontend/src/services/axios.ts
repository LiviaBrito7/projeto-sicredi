import axios from 'axios';

const instance = axios.create({
  baseURL: 'http://127.0.0.1:8000/api',
  headers: {
    'Content-Type': 'multipart/form-data',
  },
});


instance.interceptors.response.use(
  (response) => response,
  (error) => {
    console.error('Erro na requisição:', error);
    return Promise.reject(error);
  }
);

export default instance;
