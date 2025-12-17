<?php
session_start();

$mensagem = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = $_POST['usuario'] ?? '';
    $senha = $_POST['senha'] ?? '';

    $ADMIN_USER = 'admin';
    $ADMIN_PASS = '12345'; 

    if ($usuario === $ADMIN_USER && $senha === $ADMIN_PASS) {
        // Sucesso: Define a sessão do administrador
        $_SESSION['is_admin'] = true;
        $mensagem = '<p style="color: green;">Login realizado com sucesso. Redirecionando...</p>';
        header('Refresh: 2; URL=index.php'); // Redireciona após 2 segundos
        exit;
    } else {
        $mensagem = '<p style="color: red;">Usuário ou senha inválidos.</p>';
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <link rel="icon" type="image/x-icon" href="img/favicon.ico">
    <link rel="stylesheet" href="style.css">
    <meta charset="UTF-8">
    <title>Login Admin - WikiTecc</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <h1>WikiTecc - Acesso Administrativo</h1>
    </header>
    <main>
        <h2>Login</h2>
        <?php echo $mensagem; ?>
        
        <form method="POST" action="login.php">
            <label for="usuario">Usuário:</label><br>
            <input type="text" id="usuario" name="usuario" required><br><br>

            <label for="senha">Senha:</label><br>
            <input type="password" id="senha" name="senha" required><br><br>

            <button type="submit" style="padding: 10px; background-color: #333; color: white;">Entrar</button>
        </form>
        <p style="margin-top: 20px;"><a href="index.php">← Voltar para o Site</a></p>
    </main>
</body>
</html>