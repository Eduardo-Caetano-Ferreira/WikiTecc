<?php

require 'admin_check.php'; // Proteção de login e session_start()
require 'db_connect.php'; 

// ... restante do código ...

$mensagem_feedback = '';

// Variável para armazenar o ID de Sessão para o JavaScript
$sessao_id = null;
if (!isset($_SESSION['upload_session_id'])) {
    $_SESSION['upload_session_id'] = uniqid('temp_', true);
}
$sessao_id = $_SESSION['upload_session_id'];

// 1. PROCESSAMENTO DE SALVAMENTO (CREATE)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = $_POST['titulo'] ?? '';
    $conteudo = $_POST['conteudo'] ?? '';

    if (empty($titulo) || empty($conteudo)) {
        $mensagem_feedback = '<p style="color: red;">Erro: O Título e o Conteúdo são obrigatórios.</p>';
    } else {
        try {
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $titulo)));
            $data_criacao = date('Y-m-d H:i:s');
            
            // 2. INSERÇÃO DO TÓPICO
            $sql = "INSERT INTO topicos (titulo, conteudo, slug, data_criacao) VALUES (?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$titulo, $conteudo, $slug, $data_criacao]);

            $novo_topico_id = $pdo->lastInsertId(); // PEGA O ID DO TÓPICO RECÉM-CRIADO!

            // 3. VINCULA IMAGENS TEMPORÁRIAS (ATUALIZA)
            $sql_update_img = "UPDATE imagens SET topico_id = ?, sessao_id = NULL WHERE sessao_id = ?";
            $stmt_update_img = $pdo->prepare($sql_update_img);
            // Usa o ID do novo tópico para vincular as imagens da sessão atual.
            $stmt_update_img->execute([$novo_topico_id, $sessao_id]);

            $mensagem_feedback = '<p style="color: green;">Tópico **' . htmlspecialchars($titulo) . '** criado com sucesso! Imagens vinculadas: ' . $stmt_update_img->rowCount() . '</p>';
            
            // 4. RESET DA SESSÃO para que o próximo tópico comece limpo
            unset($_SESSION['upload_session_id']);
            $titulo = '';
            $conteudo = '';

        } catch (PDOException $e) {
            $mensagem_feedback = '<p style="color: red;">Erro ao salvar no BD: ' . $e->getMessage() . '</p>';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <link rel="icon" type="image/x-icon" href="img/favicon.ico">
    <link rel="stylesheet" href="style.css">
    <meta charset="UTF-8">
    <title>Criar Novo Tópico - WikiTecc Admin</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <h1>WikiTecc Admin - Criar Novo Tópico</h1>
    </header>
    <main>
        <p><a href="index.php">← Voltar para a Página Inicial</a></p>
        
        <?php echo $mensagem_feedback; ?>

        <form method="POST" action="admin_criar_topico.php">
            
            <label for="titulo">Título do Tópico:</label><br>
            <input 
                type="text" 
                id="titulo" 
                name="titulo" 
                value="<?php echo htmlspecialchars($titulo ?? ''); ?>" 
                required 
                style="width: 100%; padding: 8px; margin-bottom: 20px;"
            ><br>

            <label for="conteudo">Conteúdo (Cole imagens aqui!):</label><br>
            <textarea 
                id="conteudo" 
                name="conteudo" 
                rows="15" 
                required 
                style="width: 100%; padding: 8px; margin-bottom: 20px;"
            ><?php echo htmlspecialchars($conteudo ?? ''); ?></textarea><br>

            <button type="submit" style="padding: 10px 20px; background-color: #007bff; color: white; border: none; cursor: pointer;">
                Salvar Tópico
            </button>
        </form>
    </main>

    <script>
        const contentArea = document.getElementById('conteudo');
        const sessaoId = <?php echo json_encode($sessao_id); ?>; 
        const uploadHandlerUrl = 'upload_handler.php'; 

        contentArea.addEventListener('paste', function(e) {
            e.preventDefault(); 
            const clipboardItems = e.clipboardData.items;

            if (!clipboardItems) return;

            let imageFound = false;
            
            for (let i = 0; i < clipboardItems.length; i++) {
                const item = clipboardItems[i];
                
                if (item.type.indexOf('image') !== -1) {
                    imageFound = true;
                    const file = item.getAsFile();
                    if (file) {
                        uploadFile(file);
                    }
                    break;
                }
            }

            if (!imageFound) {
                const text = e.clipboardData.getData('text/plain');
                document.execCommand('insertText', false, text);
            }
        });


        function uploadFile(file) {
            console.log("Iniciando upload temporário...");
            
            const loadingMarker = "\n[IMAGEM EM UPLOAD...] "; 
            contentArea.value += loadingMarker; 

            const formData = new FormData();
            formData.append('image', file); 
            // Passamos o sessao_id (e o topico_id que estará vazio/null)
            formData.append('sessao_id', sessaoId); 
            
            fetch(uploadHandlerUrl, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                contentArea.value = contentArea.value.replace(loadingMarker, '');

                if (data.success) {
                    const imageUrl = data.url;
                    const imageHtml = `\n<p><img src="${imageUrl}" alt="Imagem Colada" style="max-width: 600px; height: auto; display: block; margin: 10px auto;"></p>\n`;
                    contentArea.value += imageHtml; 
                } else {
                    alert('Erro ao fazer upload da imagem: ' + data.message);
                }
            })
            .catch(error => {
                contentArea.value = contentArea.value.replace(loadingMarker, '');
                alert('Erro de rede ou servidor: Verifique o console. ' + error);
            });
        }
    </script>
</body>
</html>