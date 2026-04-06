<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Detecta se está rodando localmente (XAMPP) ou online (InfinityFree)
$is_local = in_array($_SERVER['SERVER_NAME'], ['localhost', '127.0.0.1', '::1']);

// Define a URL base do sistema (corrige os links do CSS e menus)
define('BASE_URL', $is_local ? '/advocacia-system/' : '/');

if ($is_local) {
    // Credenciais do XAMPP (Local)
    $host = 'localhost';
    $dbname = 'advocacia_db'; // Certifique-se de que este é o nome do seu banco local
    $user = 'root';
    $pass = ''; // A senha padrão do XAMPP é vazia
} else {
    // Credenciais do InfinityFree (Site Online)
    $host = 'sql104.infinityfree.com';
    $dbname = 'if0_41441525_advocacia_db';
    $user = 'if0_41441525';
    $pass = 'WgMJ9PkRywY7K';
}

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
}
catch (PDOException $e) {
    die("Erro de conexão: " . $e->getMessage());
}
?>
