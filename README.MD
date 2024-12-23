# Projeto Sicredi - Ambiente com Docker

Este projeto configura um ambiente de desenvolvimento utilizando Docker para uma aplicação composta por um backend em Laravel, um frontend em Vue.js e um banco de dados MySQL.

## Pré-requisitos

Antes de começar, certifique-se de ter os seguintes itens instalados em sua máquina:

- Docker
- Docker Compose
- PHP (versão 8.2 ou superior)
- Composer
- Node.js
- NPM

## Estrutura do Projeto

- `backend/`: Contém o código da aplicação Laravel.
- `frontend/`: Contém o código da aplicação Vue.js.
- Banco de Dados: MySQL 8.0 para armazenar os dados.

## Passo a Passo para Rodar o Projeto

### 1. Clone o Repositório

```bash
git clone <URL_DO_REPOSITORIO>
cd <PASTA_DO_PROJETO>

2. Configure os Arquivos de Ambiente
2.1. Laravel
Crie o arquivo .env na pasta backend/:

bash
Copiar código
cp backend/.env.example backend/.env
Atualize as seguintes variáveis no arquivo .env para conectar ao banco de dados MySQL:

env
Copiar código
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sicredi-db
DB_USERNAME=livia
DB_PASSWORD=livia

2.2. Vue.js
Se necessário, configure as variáveis de ambiente no arquivo frontend/.env.

Rodar o Projeto Usando Docker
1. Construa os Containers
Na raiz do projeto, execute:


docker-compose up --build
Este comando irá:

Construir o container do backend Laravel.
Construir o container do frontend Vue.js.
Inicializar o banco de dados MySQL.

php artisan migrate PARA CONSTRUIR AS TABELAS
2. Acesse a Aplicação
Backend (Laravel)
O backend estará acessível em:


http://localhost:8000
Frontend (Vue.js)
O frontend estará acessível em:


http://localhost:5173
Banco de Dados
Você pode acessar o banco de dados usando uma ferramenta como o MySQL Workbench ou DBeaver:

Host: localhost
Porta: 3306
Usuário: livia
Senha: livia
Banco de Dados: sicredi-db
Rodar o Projeto Localmente Sem Docker
1. Configurar o Ambiente Local
1.1. Backend (Laravel)
No terminal, vá até a pasta backend/.

Copie o arquivo .env.example para .env:

cp .env.example .env
Atualize as variáveis de ambiente para o banco de dados no arquivo .env:

env

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sicredi-db
DB_USERNAME=livia
DB_PASSWORD=livia

1.2. Frontend (Vue.js)
No terminal, vá até a pasta frontend/.

Certifique-se de que o arquivo .env esteja configurado corretamente para o seu ambiente local.

2. Instalar Dependências e Rodar a Aplicação
2.1. Backend (Laravel)
No terminal, dentro da pasta backend/, execute os seguintes comandos:

Instalar dependências do Composer:


composer install
Rodar o servidor de desenvolvimento do Laravel:


php artisan serve
Executar as migrações para o banco de dados:


php artisan migrate
O backend estará acessível em http://localhost:8000.

2.2. Frontend (Vue.js)
No terminal, dentro da pasta frontend/, execute os seguintes comandos:

Instalar dependências do NPM:

bash
Copiar código
npm install
Rodar o servidor de desenvolvimento do Vue.js:

bash
Copiar código
npm run dev
O frontend estará acessível em http://localhost:5173.