# Receitas Culinárias

Aplicação full-stack para gerenciamento de receitas culinárias: cadastro, listagem, edição e autenticação de usuários.

---

## Tecnologias

### Backend (API)
- **PHP 8.1+** com **Laravel 10**
- **Laravel Sanctum** – autenticação API (tokens)
- **MySQL 8** – banco de dados
- **L5-Swagger (darkaonline/l5-swagger)** – documentação OpenAPI/Swagger
- **PHPUnit** – testes

### Frontend
- **Vue 3**
- **TypeScript**
- **Vite 7** – build e dev server
- **Vue Router 5**
- **Pinia** – estado global
- **Tailwind CSS 4**
- **Reka UI** – componentes acessíveis
- **Axios** – requisições HTTP
- **Vitest** – testes unitários | **Cypress** – testes E2E

### Infraestrutura
- **Docker** e **Docker Compose** – ambiente containerizado

---

## Como rodar o projeto

### Pré-requisitos
- [Docker](https://docs.docker.com/get-docker/) e [Docker Compose](https://docs.docker.com/compose/install/)
- Ou, para rodar localmente: PHP 8.1+, Composer, Node.js 20+, npm, MySQL 8

### Com Docker (recomendado)

Na raiz do projeto:

```bash
docker compose up -d --build
```

- **API:** http://localhost:8000  
- **Frontend:** http://localhost:5173  
- **MySQL:** porta `3307` (host) → `3306` (container)

O entrypoint da API já copia `.env.example` para `.env` (se não existir), instala dependências, gera `APP_KEY`, roda migrations e seeders.

### Sem Docker (desenvolvimento local)

1. **Banco de dados**  
   Subir um MySQL 8 com um banco `receitas` e usuário/senha (ex.: `user` / `secret`), ou usar o mesmo config do `.env.example` da API.

2. **API (Laravel)**  
   ```bash
   cd api
   cp .env.example .env
   composer install
   php artisan key:generate
   # Ajustar DB_HOST, DB_PORT, DB_DATABASE, DB_USERNAME, DB_PASSWORD no .env
   php artisan migrate
   php artisan db:seed
   php artisan serve
   ```  
   API em http://localhost:8000.

3. **Frontend (Vue)**  
   ```bash
   cd frontend
   cp .env.example .env
   npm install
   npm run dev
   ```  
   Em `.env` do frontend, deixe `VITE_API_URL` e `VITE_API_PROXY_TARGET` apontando para a API (ex.: `http://localhost:8000`).  
   App em http://localhost:5173.

---

## Documentação Swagger

A API é documentada com **OpenAPI (Swagger)** via **L5-Swagger**.

- **URL da documentação (Swagger UI):**  
  **http://localhost:8000/api/documentation**

Com a API rodando (Docker ou `php artisan serve`), abra o link da Swagger UI no navegador para ver e testar os endpoints (login, usuário, categorias, receitas, etc.).

---

## Arquitetura de pastas

### Backend (`api/`)

```
api/
├── app/
│   ├── Console/           # Comandos Artisan
│   ├── Contracts/         # Interfaces (AuthService, CategoriaService, ReceitaService)
│   ├── Exceptions/        # Handler de exceções
│   ├── Http/
│   │   ├── Controllers/   # Auth, Categoria, Receita
│   │   ├── Kernel.php
│   │   └── Middleware/    # Auth, CORS, etc.
│   ├── Models/            # User, Categoria, Receita
│   ├── Providers/         # Service providers
│   ├── Services/          # Lógica de negócio (Auth, Categorias, Receitas)
│   │   ├── Auth/          # EloquentAuthService, FakeAuthService
│   │   ├── Categorias/
│   │   └── Receitas/
│   └── Support/           # Utilitários (ex.: OfflineJsonStore)
├── bootstrap/
├── config/                # Configurações (app, auth, database, l5-swagger, etc.)
├── database/
│   ├── factories/
│   ├── migrations/
│   └── seeders/
├── public/                # index.php, assets
├── resources/             # views, css, js
├── routes/
│   ├── api.php            # Rotas da API REST
│   └── web.php
├── storage/               # logs, cache, api-docs (Swagger JSON)
├── tests/
│   ├── Feature/           # Testes de API (Auth, Categoria, Receita)
│   ├── Unit/              # Testes de modelos
│   └── fixtures/
├── .env.example
├── artisan
├── composer.json
├── Dockerfile
└── entrypoint.sh
```

### Frontend (`frontend/`)

```
frontend/
├── public/                # favicon, assets estáticos
├── src/
│   ├── api/               # Cliente HTTP e módulos por domínio
│   │   ├── auth/
│   │   ├── categorias/
│   │   ├── receitas/
│   │   ├── user/
│   │   ├── client.ts      # Axios configurado
│   │   ├── index.ts
│   │   └── types.ts
│   ├── components/        # Componentes reutilizáveis
│   │   ├── Layout/        # AppLayout
│   │   ├── Toast/
│   │   ├── ui/            # Button, Input, Label, Tabs (Reka UI)
│   │   └── ConfirmModal.vue
│   ├── lib/               # Utils, tratamento de erros da API
│   ├── router/            # Vue Router (index.ts)
│   ├── stores/            # Pinia (auth, toast, counter)
│   ├── views/             # Páginas (Login, Profile, Receitas, ReceitaDetail, ReceitaForm)
│   ├── App.vue
│   ├── main.ts
│   └── style.css
├── .env.example
├── index.html
├── package.json
├── vite.config.ts
├── Dockerfile
└── docker-entrypoint.sh
```

---

## Informações úteis

### Variáveis de ambiente

- **API (`api/.env`):**  
  `APP_KEY`, `DB_*`, `APP_URL` são as principais. O restante segue o padrão Laravel.

- **Frontend (`frontend/.env`):**  
  - `VITE_API_URL` – URL base da API (ex.: `http://localhost:8000`) quando não usar proxy.  
  - `VITE_API_PROXY_TARGET` – alvo do proxy do Vite (em dev o front faz proxy de `/api`, `/sanctum`, `/storage` para a API).

### Comandos úteis

| Onde    | Comando | Descrição |
|--------|---------|-----------|
| Raiz   | `docker compose up -d` | Sobe API, frontend e MySQL |
| Raiz   | `docker compose down`  | Para e remove containers |
| API    | `php artisan migrate`  | Roda migrations |
| API    | `php artisan db:seed` | Roda seeders |
| API    | `php artisan test`    | Testes PHPUnit |
| Front  | `npm run dev`         | Dev server (Vite) |
| Front  | `npm run build`       | Build de produção |

### Endpoints principais da API

- `POST /api/login` – login  
- `POST /api/register` – registro  
- `GET/PATCH /api/user` – perfil (autenticado)  
- `POST /api/logout` – logout (autenticado)  
- `GET /api/categorias` – listar categorias (autenticado)  
- `GET/POST /api/receitas` – listar e criar receitas (autenticado)  
- `GET/PUT/PATCH/DELETE /api/receitas/{id}` – ver, atualizar e excluir receita (autenticado)

Todos os detalhes e schemas estão na **documentação Swagger**: http://localhost:8000/api/documentation.

Figma com as telas desenhadas: https://www.figma.com/design/Yu64JHgJHKgiwCvMupVStJ/Receitas-culin%C3%A1rias?node-id=0-1&t=XWzP8uBmWqbsj8kY-1

---