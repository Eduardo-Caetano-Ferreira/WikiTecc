<?php
// =======================================================
// ARQUIVO: WikiTecc/topico.php
// =======================================================
session_start(); // Inicia a sessão para checar status de admin
require 'db_connect.php'; 

// Variável para checar se é admin (usada apenas para links de edição)
$is_admin = $_SESSION['is_admin'] ?? false; 

// 1. OBTÉM O SLUG DA URL
$slug = $_GET['slug'] ?? ''; 

$topico = null;
$erro = '';

if (empty($slug)) {
    $erro = "Erro: Nenhum tópico especificado. Retorne à página inicial.";
} else {
    try {
        // 2. PREPARA E EXECUTA A BUSCA NO BANCO DE DADOS
        // Buscamos o ID também, caso o admin queira um link rápido para a edição.
        $sql = "SELECT id, titulo, conteudo FROM topicos WHERE slug = ?";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$slug]);
        
        $topico = $stmt->fetch(); 

        if (!$topico) {
            $erro = "Tópico com slug '{$slug}' não foi encontrado.";
        }

    } catch (PDOException $e) {
        $erro = "Erro de Banco de Dados: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <link rel="icon" type="image/x-icon" href="favicon.ico">
    <link rel="stylesheet" href="style.css">
    <meta charset="UTF-8">
    <title>
        <?php 
        echo $topico ? htmlspecialchars($topico['titulo']) . ' | WikiTecc' : 'Tópico não encontrado'; 
        ?>
    </title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <h1>WikiTecc</h1>
        <nav>
            <p>
                <a href="index.php">← Voltar para a Lista de Tópicos</a>
                
                <?php if ($is_admin && $topico): ?>
                    | <a href="admin_editar_topico.php?id=<?php echo $topico['id']; ?>" 
                         style="color: #ffc107;">Editar Tópico
                      </a>
                <?php endif; ?>
            </p>
        </nav>
    </header>
    <main>
        <?php if ($erro): ?>
            <h2 style="color: red;">Erro ao Carregar Tópico</h2>
            <p><?php echo htmlspecialchars($erro); ?></p>
            
        <?php elseif ($topico): ?>
            
            <article>
                <h2><?php echo htmlspecialchars($topico['titulo']); ?></h2>
                
                <hr>
                
                <div class="content-body">
                    <?php echo $topico['conteudo']; ?>
                </div>

            </article>
        <?php endif; ?>
    </main>
</body>
</html>