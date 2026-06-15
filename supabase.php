<?php

// Load local config with sensitive credentials if present (do NOT commit it)
if (file_exists(__DIR__ . '/config.local.php')) {
    require __DIR__ . '/config.local.php';
}

// Fallback defaults (kept for compatibility). It's recommended to create
// a `config.local.php` with your credentials and add it to .gitignore.
if (!defined('SUPABASE_DB_HOST')) define('SUPABASE_DB_HOST', 'db.ttnjltqowroupzgfvmxx.supabase.co');
if (!defined('SUPABASE_DB_PORT')) define('SUPABASE_DB_PORT', 5432);
if (!defined('SUPABASE_DB_NAME')) define('SUPABASE_DB_NAME', 'ApexMotors');
if (!defined('SUPABASE_DB_USER')) define('SUPABASE_DB_USER', 'mihoneybee');
if (!defined('SUPABASE_DB_PASSWORD')) define('SUPABASE_DB_PASSWORD', '6Dn?YdfY?H3YKC.');
if (!defined('SUPABASE_CARS_TABLE')) define('SUPABASE_CARS_TABLE', 'veiculos');
if (!defined('SUPABASE_GALLERY_TABLE')) define('SUPABASE_GALLERY_TABLE', 'veiculos_galeria');

function supabaseConnect()
{
    static $connection;

    if ($connection !== null) {
        return $connection;
    }

    if (extension_loaded('pdo_pgsql')) {
        $dsn = sprintf(
            'pgsql:host=%s;port=%d;dbname=%s;sslmode=require',
            SUPABASE_DB_HOST,
            SUPABASE_DB_PORT,
            SUPABASE_DB_NAME
        );

        try {
            $pdo = new PDO($dsn, SUPABASE_DB_USER, SUPABASE_DB_PASSWORD, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
            $connection = $pdo;
        } catch (PDOException $e) {
            $connection = false;
        }

        return $connection;
    }

    if (extension_loaded('pgsql')) {
        $connString = sprintf(
            'host=%s port=%d dbname=%s user=%s password=%s sslmode=require',
            SUPABASE_DB_HOST,
            SUPABASE_DB_PORT,
            SUPABASE_DB_NAME,
            SUPABASE_DB_USER,
            SUPABASE_DB_PASSWORD
        );

        $pg = @pg_connect($connString);
        $connection = $pg ?: false;
        return $connection;
    }

    return false;
}

function supabaseQueryRows(string $sql, array $params = []): array
{
    $connection = supabaseConnect();
    if (!$connection) {
        return [];
    }

    if ($connection instanceof PDO) {
        $statement = $connection->prepare($sql);
        $statement->execute($params);
        return $statement->fetchAll();
    }

    $result = pg_query_params($connection, $sql, array_values($params));
    if (!$result) {
        return [];
    }

    $rows = [];
    while ($row = pg_fetch_assoc($result)) {
        $rows[] = $row;
    }

    return $rows;
}

function supabaseQueryOne(string $sql, array $params = []): ?array
{
    $rows = supabaseQueryRows($sql, $params);
    return $rows[0] ?? null;
}

function buscarVeiculosPorCategoria(string $categoria): array
{
    $sql = sprintf(
        'SELECT * FROM %s WHERE categoria = :categoria ORDER BY id ASC',
        SUPABASE_CARS_TABLE
    );

    return supabaseQueryRows($sql, ['categoria' => $categoria]);
}

function buscarVeiculosDestaquePorCategoria(string $categoria, int $limit = 3): array
{
    $limit = max(1, min($limit, 50));

    $sql = sprintf(
        'SELECT * FROM %s WHERE categoria = :categoria ORDER BY id ASC LIMIT %d',
        SUPABASE_CARS_TABLE,
        $limit
    );

    return supabaseQueryRows($sql, ['categoria' => $categoria]);
}

function buscarVeiculosPorMarca(string $marca): array
{
    $brandSlug = preg_replace('/[^a-z0-9]+/u', '-', strtolower(trim($marca)));
    $brandSlug = trim($brandSlug, '-');

    $sql = sprintf(
        "SELECT * FROM %s WHERE regexp_replace(lower(marca), '[^a-z0-9]+', '-', 'g') = :brandSlug ORDER BY id ASC",
        SUPABASE_CARS_TABLE
    );

    return supabaseQueryRows($sql, ['brandSlug' => $brandSlug]);
}

function buscarVeiculoById($id): ?array
{
    $sql = sprintf('SELECT * FROM %s WHERE id = :id LIMIT 1', SUPABASE_CARS_TABLE);
    return supabaseQueryOne($sql, ['id' => $id]);
}

function buscarVeiculoPorSlug(string $slug): ?array
{
    $slugNormalized = strtolower(trim($slug));
    $slugNormalized = preg_replace('/[^a-z0-9]+/u', '-', $slugNormalized);
    $slugNormalized = trim($slugNormalized, '-');

    $sql = sprintf(
        "SELECT * FROM %s WHERE regexp_replace(lower(marca || ' ' || modelo), '[^a-z0-9]+', '-', 'g') = :slug OR lower(marca || ' ' || modelo) = :plain LIMIT 1",
        SUPABASE_CARS_TABLE
    );

    return supabaseQueryOne($sql, [
        'slug' => $slugNormalized,
        'plain' => str_replace('-', ' ', $slugNormalized),
    ]);
}

function buscarGaleriaPorVeiculoId($veiculoId): array
{
    $sql = sprintf(
        "SELECT * FROM %s WHERE veiculo_id = :id OR veiculos_id = :id ORDER BY id ASC",
        SUPABASE_GALLERY_TABLE
    );

    return supabaseQueryRows($sql, ['id' => $veiculoId]);
}
