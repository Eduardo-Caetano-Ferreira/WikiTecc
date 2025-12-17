<?php
// =======================================================
// ARQUIVO: WikiTecc/index.php
// =======================================================

session_start(); // Inicia a sessão para checar o status de login
require 'db_connect.php'; 

// Variável para facilitar a checagem no HTML
$is_admin = $_SESSION['is_admin'] ?? false; 

// ... restante da lógica de status e busca de tópicos ...
// ... (Código para $mensagem_status, $status, $topicos, $erro) ...

// 1. LÓGICA DE STATUS (FEEDBACK)
$mensagem_status = '';
$status = $_GET['status'] ?? null;

// 1. LÓGICA DE STATUS (FEEDBACK)
$mensagem_status = '';
$status = $_GET['status'] ?? null;
$arquivos_deletados = $_GET['files_deleted'] ?? 0;

if (strpos($status, 'success_delete') !== false) {
    $msg = "Tópico excluído com sucesso!";
    if ($arquivos_deletados > 0) {
        $msg .= " Foram deletados **{$arquivos_deletados}** arquivo(s) de mídia.";
    } elseif ($arquivos_deletados === 0) {
        $msg .= " (Nenhum arquivo de mídia para deletar).";
    }
    $mensagem_status = '<p style="color: green; font-weight: bold;">' . $msg . '</p>';
    
} elseif ($status === 'error_id') {
    $mensagem_status = '<p style="color: red;">Erro: ID do tópico inválido ou não fornecido para exclusão.</p>';
} elseif ($status === 'not_found') {
    $mensagem_status = '<p style="color: orange;">Tópico não encontrado para exclusão.</p>';
}

// 2. BUSCA DE TÓPICOS
try {
    $stmt = $pdo->query('SELECT id, titulo, slug, data_criacao FROM topicos ORDER BY data_criacao DESC');
    $topicos = $stmt->fetchAll();
} catch (PDOException $e) {
    $topicos = [];
    $erro = "Erro ao carregar tópicos: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <link rel="icon" type="image/x-icon" href="img/favicon.ico">
    <link rel="stylesheet" href="style.css">
    <meta charset="UTF-8">
    <title>WikiTecc - O seu grande bloco de notas</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <h1>WikiTecc</h1>
        <p>Seu grande bloco de notas tecnológico</p>
        <div style="position: absolute; top: 10px; right: 10px;">
            <?php if ($is_admin): ?>
                <a href="logout.php" style="color: white;">Sair (Admin)</a>
            <?php else: ?>
                <a href="login.php" style="color: white;">Login Admin</a>
            <?php endif; ?>
        </div>
    </header>
    <main>
        <h2>Tópicos Criados</h2>
        
        <?php if ($is_admin): ?>
            <p><a href="admin_criar_topico.php">→ Criar Novo Tópico</a></p>
        <?php endif; ?>
        
        <?php echo $mensagem_status; ?>
        
        <?php if (isset($erro)): ?>
            <p style="color: red;"><?php echo $erro; ?></p>
        <?php elseif (empty($topicos)): ?>
            <p>Nenhum tópico encontrado. Que tal criar o primeiro?</p>
        <?php else: ?>
            <ul>
                <?php foreach ($topicos as $topico): ?>
                    <li>
                        <a href="topico.php?slug=<?php echo htmlspecialchars($topico['slug']); ?>">
                            **<?php echo htmlspecialchars($topico['titulo']); ?>**
                        </a>
                        
                        <?php if ($is_admin): ?>
                            <span style="font-size: 0.8em;">
                               [ <a href="admin_editar_topico.php?id=<?php echo $topico['id']; ?>">Editar</a> ] 
                            </span>
                            <span style="font-size: 0.8em;">
                               [ <a href="admin_deletar_topico.php?id=<?php echo $topico['id']; ?>" 
                                     onclick="return confirm('ATENÇÃO: Deseja realmente excluir este tópico?');"
                                     style="color: red;">Excluir</a> ] 
                            </span>
                        <?php endif; ?>
                        
                        <span style="font-size: 0.8em; color: #666;">
                           - Criado em: <?php echo date('d/m/Y H:i', strtotime($topico['data_criacao'])); ?>
                        </span>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </main>
</body>
</html>