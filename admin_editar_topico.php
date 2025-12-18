<?php
require 'admin_check.php'; 
require 'db_connect.php';

$mensagem_feedback = '';
$topico_id = $_GET['id'] ?? null;
if (!$topico_id) { header("Location: index.php"); exit; }

try {
    $stmt = $pdo->prepare("SELECT * FROM topicos WHERE id = ?");
    $stmt->execute([$topico_id]);
    $topico = $stmt->fetch();
} catch (PDOException $e) { die("Erro: " . $e->getMessage()); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = $_POST['titulo'] ?? '';
    $conteudo = $_POST['conteudo'] ?? '';
    if (!empty($titulo) && !empty($conteudo)) {
        try {
            $pdo->prepare("UPDATE topicos SET titulo = ?, conteudo = ? WHERE id = ?")->execute([$titulo, $conteudo, $topico_id]);
            $mensagem_feedback = '<p style="color: green;">Atualizado com sucesso!</p>';
            $topico['titulo'] = $titulo; $topico['conteudo'] = $conteudo;
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
    <title>Editar Tópico - WikiTecc</title>
    <link rel="stylesheet" href="style.css?v=3.0">
    <script src="https://cdn.ckeditor.com/ckeditor5/35.0.1/classic/ckeditor.js"></script>
    <style>.ck-editor__editable { min-height: 400px; color: #000 !important; }</style>
</head>
<body>
    <header><h1>WikiTecc Admin</h1><nav><p><a href="index.php">← Voltar</a></p></nav></header>
    <main>
        <h2>Editar Registro</h2>
        <div class="mensagem_feedback"><?php echo $mensagem_feedback; ?></div>
        <form method="POST">
            <label>Título:</label>
            <input type="text" name="titulo" value="<?php echo htmlspecialchars($topico['titulo']); ?>" required>
            <textarea id="editor" name="conteudo"><?php echo htmlspecialchars($topico['conteudo']); ?></textarea>
            <button type="submit" style="background-color: #fd7e14 !important; margin-top: 20px;">Salvar Alterações</button>
        </form>
    </main>
    <script>
        ClassicEditor.create(document.querySelector('#editor')).catch(error => { console.error(error); });
    </script>
</body>
</html>