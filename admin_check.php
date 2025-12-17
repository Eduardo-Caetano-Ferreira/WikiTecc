<?php
// =======================================================
// ARQUIVO: WikiTecc/admin_check.php
// =======================================================

session_start(); // Garante que a sessão foi iniciada

// Verifica se a sessão 'is_admin' está definida e é verdadeira
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    // Se não for admin, interrompe o script e redireciona para a página de login
    header('Location: login.php');
    exit;
}
// Se a execução chegou aqui, o usuário é administrador.
?>