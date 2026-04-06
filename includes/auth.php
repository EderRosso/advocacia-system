<?php
session_start();
require_once __DIR__ . '/../config/conexao.php';

function verifica_login() {
    if (!isset($_SESSION['usuario_id'])) {
        header("Location: " . BASE_URL . "login.php");
        exit();
    }
    
    // Trava de Primeiro Acesso
    if (!empty($_SESSION['primeiro_acesso']) && basename($_SERVER['PHP_SELF']) !== 'primeiro_acesso.php') {
        header("Location: " . BASE_URL . "primeiro_acesso.php");
        exit();
    }
}

function verifica_admin() {
    if (!isset($_SESSION['usuario_perfil']) || $_SESSION['usuario_perfil'] !== 'administrador') {
        echo "<script>alert('Acesso restrito a administradores.'); window.history.back();</script>";
        exit();
    }
}

function data_br($data) {
    if (!$data) return '';
    return date('d/m/Y', strtotime($data));
}

function moeda_br($valor) {
    return 'R$ ' . number_format($valor, 2, ',', '.');
}

function tem_permissao($modulo) {
    if (!isset($_SESSION['usuario_perfil'])) return false;
    if ($_SESSION['usuario_perfil'] === 'administrador') return true;
    
    $acessos = isset($_SESSION['usuario_acessos']) ? $_SESSION['usuario_acessos'] : [];
    return in_array($modulo, $acessos);
}

function verifica_permissao($modulo) {
    if (!tem_permissao($modulo)) {
        header("Location: " . BASE_URL . "dashboard.php?erro_permissao=1");
        exit();
    }
}
?>
