<?php
$dbHost = 'sql201.infinityfree.com'; 
$dbName = 'if0_42143309_apexmotors'; 
$dbUser = 'if0_42143309'; 
$dbPass = 'Dseven741516'; 
$tabela = 'veiculos';

try {
    $pdo = new PDO("mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4", $dbUser, $dbPass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    die("Erro crítico de conexão com o banco de dados: " . htmlspecialchars($e->getMessage()));
}
