# InstaClone - Backend API

## Visao Geral

O InstaClone e uma rede social inspirada no Instagram, construida como projeto final da disciplina. O objetivo e aplicar os conceitos vistos ao longo do curso em uma aplicacao dividida em duas partes independentes: backend e frontend.

Este repositorio contem o backend: uma API RESTful em Laravel responsavel por autenticacao, usuarios, perfis, follows, posts, feed, curtidas e comentarios.

## Backend

### O Que E

Uma API RESTful construida com Laravel que gerencia a logica principal de uma rede social. Os endpoints respondem em JSON e sao consumidos pelo frontend separado do projeto. O backend mantem apenas views auxiliares do Laravel e da documentacao Swagger UI.

### Autenticacao

O sistema usa Laravel Sanctum para autenticacao por token. O usuario se cadastra, faz login e recebe um token que deve ser enviado nas requisicoes protegidas pelo header `Authorization: Bearer <token>`.

A API possui endpoints para:

- Registro
- Login
- Logout
- Renovacao de token
- Consulta do usuario autenticado

### Usuarios E Perfis

Cada usuario possui um perfil consultavel por `username`, com nome, username unico, email, biografia e avatar. O usuario autenticado pode editar o proprio perfil e enviar imagem de avatar.

Tambem existem endpoints para buscar usuarios por nome ou username e para listar sugestoes de perfis que o usuario ainda nao segue.

### Sistema De Follow

Os usuarios podem seguir e deixar de seguir outros perfis. Esse relacionamento e muitos-para-muitos auto-referencial na tabela `users`, usando a tabela intermediaria `follows`.

O par seguir/desseguir usa a mesma URL com metodos diferentes:

- `POST /api/users/{id}/follow`
- `DELETE /api/users/{id}/follow`

A tentativa de seguir a si mesmo e bloqueada no `FollowService` por meio da `SelfFollowException`, retornando erro `403`.

A API tambem disponibiliza:

- Listagem de seguidores
- Listagem de usuarios que um perfil segue
- Verificacao se o usuario autenticado segue outro perfil

### Posts

Os usuarios podem criar publicacoes com upload de imagem e legenda. Cada post pertence a um unico usuario.

Apenas o dono do post pode edita-lo ou remove-lo, regra protegida pela `PostPolicy`. A API tambem retorna os posts de um usuario especifico e o detalhe individual de cada post.

### Feed

O feed retorna os posts das pessoas que o usuario autenticado segue, ordenados do mais recente para o mais antigo e com paginacao. A montagem do feed fica encapsulada em uma camada de servico propria.

### Curtidas

Os usuarios podem curtir e descurtir posts. Cada curtida e unica: um mesmo usuario nao pode curtir o mesmo post mais de uma vez.

Curtir e descurtir usam a mesma URL com metodos diferentes:

- `POST /api/posts/{id}/like`
- `DELETE /api/posts/{id}/like`

O endpoint retorna o estado atualizado da interacao. A API tambem expoe a lista de usuarios que curtiram cada post.

### Comentarios

Os usuarios podem comentar em posts. Cada comentario pertence a um usuario e a um post.

Apenas o autor do comentario pode edita-lo ou remove-lo, regra protegida pela `CommentPolicy`. Os comentarios sao listados de forma paginada dentro de cada post.

## Dockerizacao

O backend e dockerizado para subir a API e o banco com Docker Compose. A configuracao principal vive em tres pontos:

- `Dockerfile`
- `compose.yaml`
- `docker/`

### Dockerfile

A imagem da aplicacao usa um `Dockerfile` multi-stage.

O primeiro estagio usa a imagem oficial do Composer para instalar as dependencias PHP de producao com:

```bash
composer install --no-dev --no-interaction --no-scripts --prefer-dist --no-progress
```

Depois copia o codigo da aplicacao e gera o autoload otimizado:

```bash
composer dump-autoload --classmap-authoritative --no-dev
```

O segundo estagio parte de `dunglas/frankenphp:1-php8.4-alpine`, que traz o FrankenPHP como servidor web/runtime PHP. Nele sao instaladas as extensoes e pacotes necessarios:

- `pdo_mysql`
- `intl`
- `zip`
- `bcmath`
- `opcache`
- `pcntl`
- `gd`
- `redis`
- `mysql-client`
- `tini`
- `bash`

A imagem final expoe a porta `8000`, possui `HEALTHCHECK` contra o endpoint `/up` do Laravel e usa `docker/entrypoint.sh` como ponto de entrada.

### compose.yaml

O `compose.yaml` orquestra dois servicos:

- `mysql`: usa a imagem oficial `mysql:latest`, cria o banco `laravel` e persiste os dados no volume `mysql_data`.
- `app`: builda a imagem local da API, le variaveis do `.env`, aponta `DB_HOST` para o servico `mysql`, expoe a porta `8000` no host e usa volume para persistir o diretorio `storage`.

O servico `app` possui `depends_on: mysql`, garantindo a ordem de inicializacao dos containers. O script de entrada ainda aguarda a porta do MySQL ficar disponivel antes de seguir com o boot da aplicacao.

### docker/

O diretorio `docker/` contem arquivos auxiliares copiados para dentro da imagem.

#### docker/entrypoint.sh

Script executado antes do FrankenPHP subir. Ele:

- Garante que exista um `.env` dentro do container.
- Tenta gerar `APP_KEY` quando ela nao existe no arquivo.
- Aguarda o MySQL aceitar conexao TCP via `nc`, com retry de ate 60 segundos.
- Roda `php artisan migrate --force --no-interaction` quando `RUN_MIGRATIONS=true`.
- Aplica caches do Laravel em producao.
- Limpa caches em ambiente local/desenvolvimento.
- Executa `php artisan storage:link`.
- Finaliza com `exec "$@"`, entregando o controle ao FrankenPHP.

Mesmo com essa protecao, na primeira execucao recomenda-se gerar a `APP_KEY` no host antes de subir o Compose. Isso evita conflito com o comportamento do `env_file` do Docker Compose quando `APP_KEY` esta vazia.

#### docker/php.ini

Arquivo com ajustes de PHP carregados em `conf.d/zz-app.ini`, incluindo:

- `memory_limit`
- Limites de upload
- `max_execution_time`
- Timezone `UTC`
- Configuracoes de OPcache/JIT para producao

## Como Rodar

### Primeira Execucao

Na primeira vez em que voce sobe o projeto em uma maquina nova, siga os passos abaixo na ordem.

Eles existem por causa de um detalhe do Docker Compose: variaveis definidas no `.env` entram no container como variaveis de ambiente. Se `APP_KEY` estiver vazia no `.env`, ela tambem entra vazia no container, e isso pode causar `Illuminate\Encryption\MissingAppKeyException` em toda requisicao.

#### 1. Crie o arquivo `.env`

```bash
cp .env.example .env
```

#### 2. Gere a `APP_KEY` e grave no `.env`

A `APP_KEY` do Laravel e `base64:` seguido de 32 bytes aleatorios, exatamente o que o `php artisan key:generate` faz por baixo dos panos.

Como o `vendor/` so existe dentro da imagem durante o fluxo com Docker, o caminho mais simples e gerar a chave direto no host com `openssl`:

```bash
sed -i "s|^APP_KEY=.*|APP_KEY=base64:$(openssl rand -base64 32)|" .env
```

Confirme:

```bash
grep APP_KEY .env
```

O resultado deve ser parecido com:

```env
APP_KEY=base64:xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx=
```

#### 3. Suba a stack

```bash
docker compose up -d --build
```

Esse comando builda a imagem, sobe o `mysql` e o `app`, roda as migrations automaticamente no boot e deixa a API disponivel em:

```text
http://localhost:8000
```

#### 4. Verifique se a API subiu

```bash
curl -i http://localhost:8000/up
```

Esperado:

```text
HTTP/1.1 200 OK
```

Esse endpoint e o healthcheck nativo do Laravel.

### Popular Dados De Exemplo

Se voce quiser que telas como `/discover` tenham usuarios e sugestoes logo depois de subir o projeto, rode o seeder:

```bash
docker exec instaclone-backend-app-1 php artisan db:seed
```

Sem seed, a API funciona normalmente, mas o banco pode estar vazio e nao havera perfis para sugerir.

### Mudei O `.env` - E Agora?

`docker compose restart` nao rele o `env_file`. Ele reaproveita as variaveis de ambiente recebidas quando o container foi criado.

Toda vez que editar o `.env`, recrie o container da aplicacao:

```bash
docker compose up -d app
```

O Compose detecta a mudanca de configuracao e recria o servico quando necessario.

Para conferir se uma variavel chegou no container:

```bash
docker exec instaclone-backend-app-1 printenv APP_KEY
```

## Endpoints Principais

Todas as rotas protegidas exigem token Sanctum.

### Auth

- `POST /api/auth/register`
- `POST /api/auth/login`
- `POST /api/auth/logout`
- `POST /api/auth/refresh`
- `GET /api/auth/me`

### Users

- `GET /api/users/search`
- `GET /api/users/suggestions`
- `GET /api/users/{username}`
- `PUT /api/users/me`
- `POST /api/users/me/avatar`

### Follows

- `POST /api/users/{id}/follow`
- `DELETE /api/users/{id}/follow`
- `GET /api/users/{id}/followers`
- `GET /api/users/{id}/following`
- `GET /api/users/{id}/is-following`

### Posts E Feed

- `POST /api/posts`
- `GET /api/posts/{id}`
- `PUT /api/posts/{id}`
- `DELETE /api/posts/{id}`
- `GET /api/users/{id}/posts`
- `GET /api/feed`

### Likes

- `POST /api/posts/{id}/like`
- `DELETE /api/posts/{id}/like`
- `GET /api/posts/{id}/likes`

### Comentarios

- `POST /api/posts/{id}/comments`
- `GET /api/posts/{id}/comments`
- `PUT /api/comments/{id}`
- `DELETE /api/comments/{id}`

## Observacoes Para Desenvolvimento

- O `.env` nao deve ser commitado.
- O `.env.example` deve ser mantido como modelo limpo para quem clonar o repositorio.
- Para ambiente Docker local, `DB_HOST` deve ser `mysql`, porque esse e o nome do servico no `compose.yaml`.
- Depois de alterar o `.env`, recrie o container `app` com `docker compose up -d app`.
