<?php
require 'admin_check.php'; 
require 'db_connect.php';

$mensagem_feedback = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = $_POST['titulo'] ?? '';
    $conteudo = $_POST['conteudo'] ?? ''; 

    if (!empty($titulo) && !empty($conteudo)) {
        try {
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $titulo)));
            $stmt = $pdo->prepare("INSERT INTO topicos (titulo, conteudo, slug, data_criacao) VALUES (?, ?, ?, NOW())");
            $stmt->execute([$titulo, $conteudo, $slug]);
            $mensagem_feedback = '<p style="color: green;">Tópico criado com sucesso!</p>';
            $titulo = $conteudo = '';
        } catch (PDOException $e) { 
            $mensagem_feedback = '<p style="color: red;">Erro: ' . $e->getMessage() . '</p>'; 
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    
    <title>Criar Tópico - WikiTecc</title>
    <link rel="stylesheet" href="style.css">
    <link rel="icon" type="image/x-icon" href="img/favicon.ico">
    <script src="https://code.jquery.com/jquery-3.4.1.slim.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>
    
    <style>
        .main-container { width: 85%; margin: 20px auto; background: white; padding: 20px; border-radius: 8px; }
        .note-editor { background: white !important; color: black !important; }
    </style>
</head>
<body>
    <header><h1>WikiTecc Admin</h1><nav><a href="index.php">← Voltar</a></nav></header>
    <main class="main-container">
        <h2>Novo Registro</h2>
        <div class="mensagem_feedback"><?php echo $mensagem_feedback; ?></div>
        <form method="POST">
            <label>Título:</label>
            <input type="text" name="titulo" value="<?php echo htmlspecialchars($titulo ?? ''); ?>" required style="width: 100%; margin-bottom: 20px; padding: 10px;">
            
            <textarea id="summernote" name="conteudo"><?php echo $conteudo ?? ''; ?></textarea>
            
            <button type="submit" style="margin-top: 20px; padding: 12px 25px; background: #28a745; color: white; border: none; cursor: pointer;">Salvar Tópico</button>
        </form>
    </main>

    <script>
        $(document).ready(function() {
            $('#summernote').summernote({
                placeholder: 'Escreva seu tópico aqui ou cole um print...',
                tabsize: 2,
                height: 400,
                toolbar: [
                    ['style', ['style']],
                    ['font', ['bold', 'underline', 'clear']],
                    ['color', ['color']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['table', ['table']],
                    ['insert', ['link', 'picture', 'video']], // 'picture' é o que faz o print funcionar
                    ['view', ['fullscreen', 'codeview', 'help']]
                ]
            });
        });
    </script>
</body>
</html>