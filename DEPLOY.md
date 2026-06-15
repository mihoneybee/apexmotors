## Deploy no InfinityFree

Passos rápidos para publicar o site ApexMotors no InfinityFree:

1. Crie um arquivo `config.local.php` (não commitá-lo) na raiz do projeto com suas credenciais Supabase:

```php
<?php
// Exemplo: config.local.php
if (!defined('SUPABASE_DB_HOST')) define('SUPABASE_DB_HOST', 'seu-host.supabase.co');
if (!defined('SUPABASE_DB_PORT')) define('SUPABASE_DB_PORT', 5432);
if (!defined('SUPABASE_DB_NAME')) define('SUPABASE_DB_NAME', 'nome_do_banco');
if (!defined('SUPABASE_DB_USER')) define('SUPABASE_DB_USER', 'usuario');
if (!defined('SUPABASE_DB_PASSWORD')) define('SUPABASE_DB_PASSWORD', 'senha_super_secreta');
```

2. No painel do InfinityFree, anote as credenciais FTP e o diretório `htdocs`.

3. Envie o conteúdo do repositório para `htdocs` (substitua se necessário). Mantenha a mesma estrutura:

- `index.php`
- `detalhes.php`
- `categoria.php`
- `marca.php`
- `supabase.php` e `config.local.php`
- `styles.css` e pastas `carros/`, `servicos/`, `imagens/` etc.

4. Teste no navegador: `https://SEU_DOMINIO.infinityfreeapp.com/index.php`

5. Depuração rápida:
- Ative erros temporariamente no topo de `index.php` para ver mensagens:

```php
ini_set('display_errors', 1);
error_reporting(E_ALL);
```

6. Manter Supabase no InfinityFree:
- use o Supabase como banco e o FTP do InfinityFree apenas para subir os arquivos PHP.
- verifique se o servidor tem a extensão `pdo_pgsql` ou `pgsql` habilitada.
- verifique se o host InfinityFree permite conexões externas ao Supabase (`*.supabase.co`).
- se a hospedagem não oferecer PostgreSQL, troque para um host PHP que suporte `pdo_pgsql`.

7. Segurança: se você cometeu credenciais no GitHub, gere novas senhas/chaves no Supabase e remova as antigas.

8. Se quiser automatizar upload pela linha de comando, use o `upload.sh` (preencha credenciais localmente).
