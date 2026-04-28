## Correção e Finalização: Setup Inicial e Autenticação (Tasks 1 e 2)

### Implementações Realizadas
* **Migrations Iniciais:** Criada a estrutura base do banco de dados com a migration para as tabelas `users`, `password_reset_tokens` e `sessions`.
* **Seeders:** Configurado o `DatabaseSeeder` para gerar 10 usuários fake via factory, além de um usuário fixo para facilitar testes manuais de login.
* **Rotas e Middleware:** Configurado o arquivo `api.php` com os verbos HTTP corretos (`POST`, `GET`) para os endpoints `/api/auth/*`. Foi aplicado o middleware `auth:sanctum` nas rotas de logout, refresh e perfil (`/me`) para garantir o bloqueio a acessos não autenticados.

## Task 3: Perfil de Usuário - Concluída

### O que foi feito:
* **Base de Dados:** Expandida a tabela `users` com `username` (único), `bio` e `avatar_url` para suportar perfis sociais.
* **Gestão de Mídia:** Configurado o sistema de arquivos para armazenamento local de avatares, com limpeza automática de arquivos antigos ao atualizar a foto.
* **Camada de Serviço:** Implementado o `UserService` para isolar a lógica de busca por username, algoritmos simples de sugestão de usuários (quem você ainda não segue) e busca textual.
* **Endpoints:** Disponibilizadas rotas para edição de perfil, upload de fotos e consulta pública de perfis.

## Task 4: Sistema de Follow - Concluída

### O que foi feito:
* **Banco de Dados:** Criada a migration `follows` para suportar um relacionamento muitos-para-muitos auto-referencial (usuários seguindo usuários).
* **Modelagem:** Criado o Model `Follow` e adicionados os métodos `followers()` e `following()` no Model `User` usando o Eloquent.
* **Lógica de Negócio:** Desenvolvido o `FollowService` com uma função idempotente `toggleFollow` que lida com o par curtir/descurtir.
* **Tratamento de Erros:** Criada a exceção customizada `SelfFollowException` para garantir que o sistema retorne `HTTP 403` se um usuário tentar seguir a si mesmo.
* **Endpoints:** Rotas de listagem de seguidores, seguindo, e verificação se um usuário segue outro criadas com sucesso.

## Task 5: Posts - Concluída

### O que foi feito:
* **Estrutura de Dados:** Implementada a tabela `posts` vinculada ao `user_id`, com suporte para armazenamento de caminhos de imagem e legendas.
* **Relacionamentos:** Definida a relação `HasMany` no Model `User` e `BelongsTo` no Model `Post`.
* **Segurança:** Implementada a `PostPolicy` para restringir edições e exclusões apenas ao autor da publicação.
* **Camada de Serviço:** O `PostService` agora gerencia o ciclo de vida das mídias (upload e deleção física de arquivos no Storage) e a persistência dos dados.
* **Endpoints:** Rotas completas de CRUD de postagens e listagem por usuário disponibilizadas.

## Task 6: Feed - Concluída

### O que foi feito:
* **Lógica de Negócio (FeedService):** Implementada a montagem do feed principal da rede social.
* **Query Otimizada:** Utilização de *subqueries* no Eloquent (`whereIn` com callback) para cruzar a tabela de `follows` com a tabela de `posts` diretamente no banco de dados, poupando memória da aplicação.
* **Ordenação e Paginação:** Os dados retornam do mais recente para o mais antigo (`latest()`) e estão limitados por paginação de offset padrão do Laravel (`paginate(15)`).
* **Prevenção de N+1:** O método `with('user')` foi aplicado para trazer os autores das publicações em uma única query.
* **Contagem de Interações:** O método `withCount` foi preparado para trazer a soma de likes (e futuramente de comentários) em cada publicação.

## Task 7: Curtidas - Concluída

### O que foi feito:
* **Banco de Dados:** Migration da tabela `likes` criada com uma *unique constraint* combinando `user_id` e `post_id` para garantir a integridade em nível de banco.
* **Modelagem:** Criado o model `Like` com relacionamentos `belongsTo` para os models `User` e `Post`.
* **Regras e Idempotência:** Implementado o `LikeService` com os métodos `like` e `unlike`. Utilizados os métodos do Eloquent `firstOrCreate` e `delete()` para garantir operações seguras e idempotentes (sem erros se repetidas acidentalmente).
* **Performance:** Retorno da contagem total atualizada no próprio endpoint de curtir/descurtir, poupando o *frontend* de realizar um GET adicional.
* **Endpoints:** Rotas de POST (curtir), DELETE (descurtir) e GET (listar quem curtiu) disponibilizadas na API.

## Task 8: Comentários - Concluída

### O que foi feito:
* **Banco de Dados:** Migration `comments` criada com vínculo entre usuário e postagem.
* **Modelagem e Segurança:** Relacionamentos estabelecidos nos models `Post` e `Comment`. A `CommentPolicy` foi implementada para garantir que apenas o autor do comentário possa editá-lo ou excluí-lo.
* **Lógica de Negócio:** O `CommentService` gerencia a criação, atualização, exclusão e a listagem paginada (do comentário mais antigo para o mais novo).
* **Endpoints:** Rotas CRUD de comentários protegidas via Sanctum e Policies.

