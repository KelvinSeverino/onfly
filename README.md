# ONFLY

## ❓ Para que serve?
Este repositorio se trata de um projeto de Backend desenvolvido em Laravel 12 na estrutura de API para gerenciar pedidos de viagem corporativa.

## 🏗️ Arquitetura e Princípios
O projeto segue boas práticas como:
- **SOLID**
- **Clean Code**
- **DRY**

## 🔧 Tecnologias e Estrutura

### 🖥️ Backend (Laravel)
O backend foi desenvolvido com Laravel e segue uma estrutura modular para garantir organização e escalabilidade:
- **Handler Global** para tratamento de exceções
- **Repositories** para abstração de acesso aos dados
- **Services** para lógica de negócios
- **Migrations, Seeders, Factories** para gerenciamento do banco de dados
- **Testes Unitários, Integrações** para garantir qualidade do código
- **Notifications** uso de Notifications Email aliado ao Jobs do Laravel

## 💻 Pré-requisitos
Antes de começar, verifique se você atendeu aos seguintes requisitos:
* docker
* docker-compose
* composer

### 💻 Como executar o PROJETO

Baixar repositório
```sh
git clone https://github.com/KelvinSeverino/onfly.git
```

Acessar diretório do projeto
```sh
cd onfly
```

Crie o arquivo .env
```sh
cp .env.example .env
```

Atualize as variáveis de ambiente do arquivo .env
```sh
APP_NAME=onfly
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8080

APP_LOCALE=pt_BR
APP_FALLBACK_LOCALE=pt_BR
APP_FAKER_LOCALE=pt_BR

DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=onfly
DB_USERNAME=onfly
DB_PASSWORD=onfly

MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=
MAIL_FROM_NAME="Onfly Viagens Corporativas"
 
 **Atenção:** O preenchimento correto dessas variáveis MAIL é necessário para o funcionamento das notificações por e-mail do sistema.
```

Iniciar os containers
```sh
docker-compose up -d
```

Executar comando composer para realizar download de arquivos necessários
```sh
docker exec -it onfly_api composer update
```

Gerar key do projeto Laravel
```sh
docker exec -it onfly_api php artisan key:generate
```

Criar tabelas no Banco de Dados
```sh
docker exec -it onfly_api php artisan migrate:fresh --seed
```

Gerar Secret para auth JWT
```sh
docker exec -it onfly_api php artisan jwt:secret
```

Iniciar o worker de filas para envio de e-mails (em background)
```sh
docker exec onfly_api php artisan queue:work --queue=emails &
```

> Esse comando inicia o processamento das filas de e-mails em background. É necessário para que as notificações sejam enviadas automaticamente.

Feito os processo acima, você poderá acessar e consumir as rotas disponibilizadas abaixo.

* Backend API Laravel - [http://localhost:8080](http://localhost:8080)

## 📂 Arquivos Auxiliares
O projeto inclui materiais para facilitar o entendimento da API:

📌 **Consumo da API via Postman**  
📜 Arquivo: `backend/docs/Onfly.postman_collection.json`  
📜 Como usar: **Importe no Postman para testar as rotas da API.**

## ✅ Testes automatizados
Este projeto possui testes automatizados no backend.

### Como rodar os testes
Para executar os testes, você pode rodar o seguinte comando dentro do container Docker do backend (ou diretamente na máquina local se tiver o ambiente configurado):

```sh
docker exec -it onfly_api php artisan test
```