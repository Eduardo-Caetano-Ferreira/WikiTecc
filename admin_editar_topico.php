<?php
require 'admin_check.php'; 
require 'db_connect.php';

$mensagem_feedback = '';
$id = $_GET['id'] ?? null;

// Busca os dados atuais do tópico
if ($id) {
    $stmt = $pdo->prepare("SELECT * FROM topicos WHERE id = ?");
    $stmt->execute([$id]);
    $topico = $stmt->fetch();
}

if (!$topico) {
    die("Tópico não encontrado.");
}

// Processa a atualização
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = $_POST['titulo'] ?? '';
    $conteudo = $_POST['conteudo'] ?? ''; 

    if (!empty($titulo) && !empty($conteudo)) {
        try {
            $stmt = $pdo->prepare("UPDATE topicos SET titulo = ?, conteudo = ? WHERE id = ?");
            $stmt->execute([$titulo, $conteudo, $id]);
            $mensagem_feedback = '<p style="color: green; font-weight: bold;">Alterações salvas com sucesso!</p>';
            
            // Atualiza a variável para refletir no formulário
            $topico['titulo'] = $titulo;
            $topico['conteudo'] = $conteudo;
        } catch (PDOException $e) { 
            $mensagem_feedback = '<p style="color: red;">Erro ao atualizar: ' . $e->getMessage() . '</p>'; 
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Editar Tópico - WikiTecc</title>
    <link rel="stylesheet" href="style.css">
    <link rel="icon" type="image/x-icon" href="img/favicon.ico">
    <script src="https://code.jquery.com/jquery-3.4.1.slim.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>
    
    <style>
        .main-container { width: 85%; margin: 20px auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .note-editor { background: white !important; color: black !important; }
        label { display: block; margin-bottom: 5px; font-weight: bold; color: #333; }
    </style>
</head>
<body>
    <header>
        <h1>WikiTecc Admin</h1>
        <nav><a href="index.php">Ver Wiki</a></nav>
    </header>
    
    <main class="main-container">
        <h2>Editar Registro: <?php echo htmlspecialchars($topico['titulo']); ?></h2>
        
        <div class="mensagem_feedback">
            <?php echo $mensagem_feedback; ?>
        </div>
        
        <form method="POST">
            <div style="margin-bottom: 20px;">
                <label>Título do Tópico:</label>
                <input type="text" name="titulo" value="<?php echo htmlspecialchars($topico['titulo']); ?>" required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px;">
            </div>
            
            <div style="margin-bottom: 20px;">
                <label>Conteúdo:</label>
                <textarea id="summernote" name="conteudo"><?php echo $topico['conteudo']; ?></textarea>
            </div>
            
            <button type="submit" style="padding: 12px 30px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 16px;">
                Atualizar Tópico
            </button>
        </form>
    </main>

    <script>
        $(document).ready(function() {
            $('#summernote').summernote({
                placeholder: 'Edite o conteúdo aqui...',
                tabsize: 2,
                height: 500,
                lang: 'pt-BR', // Se quiser em português, precisa carregar o script de tradução, mas o padrão já resolve bem.
                toolbar: [
                    ['style', ['style']],
                    ['font', ['bold', 'underline', 'clear']],
                    ['fontname', ['fontname']],
                    ['color', ['color']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['table', ['table']],
                    ['insert', ['link', 'picture', 'video']],
                    ['view', ['fullscreen', 'codeview', 'help']]
                ]
            });
        });
    </script>
</body>
</html>