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
        } catch (PDOException $e) { $mensagem_feedback = '<p style="color: red;">Erro: ' . $e->getMessage() . '</p>'; }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/x-icon" href="img/favicon.ico">
    <link rel="stylesheet" href="style.css">
    <title>Criar Tópico - WikiTecc</title>
    <link rel="stylesheet" href="style.css?v=3.0">
    <script src="https://cdn.ckeditor.com/ckeditor5/35.0.1/classic/ckeditor.js"></script>
    <style>.ck-editor__editable { min-height: 350px; color: #000 !important; }</style>
</head>
<body>
    <header><h1>WikiTecc Admin</h1><nav><p><a href="index.php">← Voltar</a></p></nav></header>
    <main>
        <h2>Novo Registro</h2>
        <div class="mensagem_feedback"><?php echo $mensagem_feedback; ?></div>
        <form method="POST">
            <label>Título:</label>
            <input type="text" name="titulo" value="<?php echo htmlspecialchars($titulo ?? ''); ?>" required>
            <textarea id="editor" name="conteudo"><?php echo htmlspecialchars($conteudo ?? ''); ?></textarea>
            <button type="submit" style="margin-top: 20px;">Salvar Tópico</button>
        </form>
    </main>
    <script>
        ClassicEditor.create(document.querySelector('#editor')).catch(error => { console.error(error); });
    </script>
</body>
</html>