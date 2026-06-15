<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require __DIR__ . '/supabase.php';

$extensions = [
    'pdo_pgsql' => extension_loaded('pdo_pgsql'),
    'pgsql' => extension_loaded('pgsql'),
];

echo "<h1>ApexMotors Healthcheck</h1>\n";
echo "<p>PHP version: " . PHP_VERSION . "</p>\n";
echo "<ul>\n";
foreach ($extensions as $name => $loaded) {
    echo "<li>{$name}: " . ($loaded ? '<strong>enabled</strong>' : '<strong>disabled</strong>') . "</li>\n";
}
echo "</ul>\n";

$connection = supabaseConnect();
if ($connection === false) {
    echo "<p style='color:red;'><strong>Falha ao conectar ao Supabase/Postgres.</strong></p>\n";
    echo "<p>Verifique se a hospedagem suporta `pdo_pgsql` ou `pgsql`, e se as credenciais estão corretas em <code>config.local.php</code>.</p>\n";
    exit(1);
}

echo "<p style='color:green;'><strong>Conexão com Supabase estabelecida.</strong></p>\n";

try {
    $result = supabaseQueryOne('SELECT 1 AS test');
    if ($result && isset($result['test'])) {
        echo "<p>Query de teste executada com sucesso: <code>SELECT 1</code> retornou " . htmlspecialchars($result['test']) . "</p>\n";
    } else {
        echo "<p style='color:orange;'>Query de teste executada, mas o resultado não foi o esperado.</p>\n";
    }
} catch (Exception $e) {
    echo "<p style='color:red;'><strong>Erro ao executar query de teste:</strong> " . htmlspecialchars($e->getMessage()) . "</p>\n";
}
