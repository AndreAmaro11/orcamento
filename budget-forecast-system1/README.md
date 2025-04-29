# Sistema Web de Controle de Orçamento e Previsão

## Visão Geral

Este é um sistema web desenvolvido para gerenciar e comparar dados de orçamento (Budget) e previsão (Forecast) para diferentes centros de custo e contas contábeis. O sistema foi baseado em um layout de planilha Excel fornecido e inclui funcionalidades como controle de acesso baseado em papéis, regras de edição específicas para orçamento e previsão, sistema de comentários para colaboração e relatórios comparativos com visualizações gráficas.

## Tecnologias Utilizadas

*   **Backend:** PHP 8.1+ / Laravel 10+
*   **Frontend:** Blade (Laravel), Tailwind CSS, Alpine.js
*   **Banco de Dados:** SQLite (para desenvolvimento/teste inicial, pode ser migrado para MySQL/PostgreSQL)
*   **Gráficos:** Chart.js
*   **Autenticação:** Laravel Breeze (Blade com modo escuro)

## Funcionalidades Principais

*   **Autenticação e Autorização:**
    *   Login de usuários.
    *   Registro de novos usuários.
    *   Papéis de usuário: Administrador, Editor, Visualizador.
    *   Controle de acesso baseado em papéis e associação a centros de custo (usuários só veem/editam dados dos centros de custo aos quais estão associados, exceto Admin).
*   **Gerenciamento (Admin):**
    *   CRUD (Criar, Ler, Atualizar, Deletar) para Usuários.
    *   CRUD para Centros de Custo.
    *   CRUD para Contas Contábeis.
    *   Associação de usuários a múltiplos centros de custo.
    *   Atribuição de papéis aos usuários.
*   **Orçamento (Budget):**
    *   Visualização em formato de planilha por centro de custo e ano.
    *   Edição de valores permitida apenas para Administradores e Editores.
    *   Regra de negócio: Edição permitida apenas em um período específico (ex: Outubro-Novembro do ano anterior ao orçamento, configurável no código).
    *   Sistema de comentários por célula (Conta/Mês) para facilitar a comunicação e aprovação.
*   **Previsão (Forecast):**
    *   Visualização em formato de planilha por centro de custo e ano.
    *   Edição de valores permitida apenas para Administradores e Editores.
    *   Regra de negócio: Edição permitida apenas para o mês atual e meses futuros do ano selecionado.
    *   Sistema de comentários por célula (Conta/Mês).
*   **Relatórios:**
    *   Relatório comparativo Orçamento vs. Previsão.
    *   Tabela detalhada com valores mensais e anuais, variância (R$) e variância (%).
    *   Gráfico de linhas comparativo agregado por mês.
    *   Filtros por ano e centro de custo.

## Estrutura do Projeto (Laravel Padrão)

```
/budget-forecast-system
|-- app/
|   |-- Http/
|   |   |-- Controllers/ (BudgetController, ForecastController, ReportController, CommentController, Admin controllers...)
|   |   |-- Middleware/ (CheckRole.php)
|   |-- Models/ (User, CostCenter, Account, BudgetEntry, ForecastEntry, Role, Comment)
|   |-- Providers/
|-- bootstrap/
|-- config/
|-- database/
|   |-- factories/
|   |-- migrations/ (Arquivos de criação das tabelas)
|   |-- seeders/ (RoleSeeder, DatabaseSeeder)
|   |-- database.sqlite  (Arquivo do banco de dados SQLite)
|-- public/ (Arquivos públicos, CSS/JS compilados)
|-- resources/
|   |-- css/
|   |-- js/
|   |-- views/
|       |-- admin/       (Views de gerenciamento: users, cost_centers, accounts)
|       |-- auth/        (Views de autenticação - Breeze)
|       |-- budget/      (View da interface de orçamento: index.blade.php)
|       |-- forecast/    (View da interface de previsão: index.blade.php)
|       |-- layouts/     (Layouts base: app.blade.php, guest.blade.php - Breeze)
|       |-- reports/     (View do relatório comparativo: comparison.blade.php)
|       |-- components/  (Componentes Blade - Breeze)
|       |-- profile/     (Views de perfil de usuário - Breeze)
|       |-- dashboard.blade.php (Pode ser usado como ponto de entrada após login)
|-- routes/
|   |-- web.php        (Definição das rotas web, incluindo admin e rotas principais)
|   |-- auth.php       (Rotas de autenticação - Breeze)
|-- storage/
|-- tests/
|-- vendor/
|-- .env             (Arquivo de configuração do ambiente - **Importante: configurar DB_DATABASE para o caminho absoluto do SQLite**)
|-- composer.json
|-- package.json
|-- artisan          (Utilitário de linha de comando do Laravel)
|-- README.md        (Este arquivo)
|-- ... (outros arquivos do Laravel)
```

## Configuração do Ambiente Local

1.  **Pré-requisitos:**
    *   PHP >= 8.1
    *   Composer
    *   Node.js & NPM
    *   Extensões PHP: php-sqlite3, php-mbstring, php-xml, php-zip, php-curl
2.  **Clonar o repositório (ou descompactar os arquivos).**
3.  **Navegar até o diretório do projeto:** `cd budget-forecast-system`
4.  **Instalar dependências PHP:** `composer install`
5.  **Instalar dependências Node.js:** `npm install`
6.  **Compilar assets frontend:** `npm run build` (ou `npm run dev` para desenvolvimento)
7.  **Copiar arquivo de ambiente:** `cp .env.example .env`
8.  **Gerar chave da aplicação:** `php artisan key:generate`
9.  **Configurar Banco de Dados (SQLite):**
    *   No arquivo `.env`, certifique-se que `DB_CONNECTION=sqlite`.
    *   **Importante:** Comente as linhas `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`.
    *   Adicione a linha: `DB_DATABASE=/caminho/absoluto/para/budget-forecast-system/database/database.sqlite` (substitua pelo caminho correto no seu sistema).
    *   Crie o arquivo do banco de dados: `touch database/database.sqlite`
10. **Executar migrações e seeders:** `php artisan migrate --seed` (Isso criará as tabelas e os papéis padrão).
11. **Iniciar o servidor:** `php artisan serve`
12. **Acessar a aplicação:** Abra o navegador em `http://127.0.0.1:8000` (ou o endereço fornecido pelo `artisan serve`).

## Uso Básico

1.  **Registro/Login:** Acesse a aplicação e registre um novo usuário ou faça login.
2.  **Administração (apenas Admin):**
    *   Acesse as seções de gerenciamento (ex: `/admin/users`, `/admin/cost-centers`, `/admin/accounts`) para criar/editar/excluir dados mestre.
    *   Associe usuários a centros de custo e atribua papéis na tela de edição de usuário.
3.  **Orçamento/Previsão:**
    *   Navegue para as seções "Orçamento" ou "Previsão".
    *   Selecione o Ano e o Centro de Custo desejado.
    *   Visualize os dados na planilha.
    *   Se tiver permissão (Admin/Editor) e estiver dentro do período permitido, edite os valores diretamente nas células.
    *   Clique no ícone de comentário (balão) que aparece ao passar o mouse sobre uma célula para adicionar ou visualizar comentários.
4.  **Relatórios:**
    *   Acesse a seção "Relatórios".
    *   Selecione o Ano e o Centro de Custo.
    *   Visualize o gráfico comparativo e a tabela detalhada.

## Próximos Passos Possíveis

*   Implementar funcionalidade de exportação para Excel.
*   Implementar logs de edição detalhados.
*   Implementar sistema de compartilhamento/notificação.
*   Adicionar testes automatizados (Unit, Feature).
*   Migrar para MySQL/PostgreSQL para produção.
*   Refinar interface e experiência do usuário.

