# ONFLY

## ❓ Para que serve?
Este repositorio se trata de um projeto de Backend desenvolvido em Laravel 12 na estrutura de API para gerenciar pedidos de viagem corporativa.

## 🏗️ Arquitetura e Princípios
O projeto segue boas práticas como:
- **SOLID**
- **Clean Code**

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

Feito os processo acima, você poderá acessar e consumir as rotas disponibilizadas abaixo.

* Backend API Laravel - [http://localhost:8080](http://localhost:8080)