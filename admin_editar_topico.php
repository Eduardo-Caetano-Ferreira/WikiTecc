<?php

require 'admin_check.php'; // Proteção de login e session_start()
require 'db_connect.php'; 

// ... restante do código ...

$mensagem_feedback = '';
$topico_atual = null;

$topico_id = $_GET['id'] ?? null;

if (empty($topico_id) || !is_numeric($topico_id)) {
    die("ID do tópico inválido ou não fornecido.");
}

// -------------------------------------------------------
// 2. PROCESSAR O ENVIO DO FORMULÁRIO (UPDATE)
// -------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = $_POST['titulo'] ?? '';
    $conteudo = $_POST['conteudo'] ?? '';
    $id_para_atualizar = $_POST['id'] ?? $topico_id;

    if (empty($titulo) || empty($conteudo)) {
        $mensagem_feedback = '<p style="color: red;">Erro: O Título e o Conteúdo são obrigatórios.</p>';
    } else {
        try {
            // Recria o SLUG baseado no novo título
            $novo_slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $titulo)));
            
            // Prepara e executa o UPDATE
            $sql = "UPDATE topicos SET titulo = ?, conteudo = ?, slug = ? WHERE id = ?";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$titulo, $conteudo, $novo_slug, $id_para_atualizar]);

            $mensagem_feedback = '<p style="color: green;">Tópico **' . htmlspecialchars($titulo) . '** atualizado com sucesso!</p>';
            
        } catch (PDOException $e) {
            $mensagem_feedback = '<p style="color: red;">Erro ao salvar no BD: ' . $e->getMessage() . '</p>';
        }
    }
}

// -------------------------------------------------------
// 3. CARREGAR OS DADOS ATUAIS DO TÓPICO
// -------------------------------------------------------
try {
    // Buscamos o slug também para o link de visualização
    $stmt = $pdo->prepare('SELECT id, titulo, conteudo, slug FROM topicos WHERE id = ?');
    $stmt->execute([$topico_id]);
    $topico_atual = $stmt->fetch();

    if (!$topico_atual) {
        die("Tópico não encontrado no banco de dados.");
    }
    
    // Atualiza as variáveis que preenchem o form, ou mantém os dados do POST em caso de erro.
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !empty($mensagem_feedback) && strpos($mensagem_feedback, 'Erro') !== false) {
        $titulo = $topico_atual['titulo'];
        $conteudo = $topico_atual['conteudo'];
    }

} catch (PDOException $e) {
    die("Erro ao carregar dados do tópico: " . $e->getMessage());
}

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <link rel="icon" type="image/x-icon" href="img/favicon.ico">
    <link rel="stylesheet" href="style.css">
    <meta charset="UTF-8">
    <title>Editar Tópico: <?php echo htmlspecialchars($topico_atual['titulo']); ?> - WikiTecc Admin</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <h1>WikiTecc Admin - Editar Tópico</h1>
    </header>
    <main>
        <p><a href="index.php">← Voltar para a Lista</a> | 
           <a href="topico.php?slug=<?php echo htmlspecialchars($topico_atual['slug']); ?>">Visualizar Tópico →</a> |
           <a href="admin_deletar_topico.php?id=<?php echo $topico_id; ?>"
      onclick="return confirm('ATENÇÃO: Deseja realmente excluir este tópico?');"
      style="color: red;">Excluir Tópico
   </a>
</p>

        <h2>Editando: <?php echo htmlspecialchars($topico_atual['titulo']); ?></h2>
        
        <?php echo $mensagem_feedback; ?>

        <form method="POST" action="admin_editar_topico.php?id=<?php echo $topico_id; ?>">
            
            <input type="hidden" name="id" value="<?php echo $topico_id; ?>">
            
            <label for="titulo">Título do Tópico:</label><br>
            <input 
                type="text" 
                id="titulo" 
                name="titulo" 
                value="<?php echo htmlspecialchars($titulo); ?>" 
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
            ><?php echo htmlspecialchars($conteudo); ?></textarea><br>

            <button type="submit" style="padding: 10px 20px; background-color: #ffc107; color: black; border: none; cursor: pointer;">
                Salvar Edição
            </button>
        </form>
    </main>

    <script>
        const contentArea = document.getElementById('conteudo');
        const topicoId = <?php echo json_encode($topico_id); ?>; // ID do tópico atual
        const uploadHandlerUrl = 'upload_handler.php'; // Script PHP para processar o upload

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
                // Se não for imagem, cole o texto normal
                const text = e.clipboardData.getData('text/plain');
                document.execCommand('insertText', false, text);
            }
        });


        function uploadFile(file) {
            console.log("Iniciando upload para o servidor...");
            
            // Marcador que será substituído após o upload
            const loadingMarker = "\n[IMAGEM EM UPLOAD...] "; 
            contentArea.value += loadingMarker; // Insere o marcador

            const formData = new FormData();
            formData.append('image', file); 
            formData.append('topico_id', topicoId);
            
            fetch(uploadHandlerUrl, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                // Remove o marcador de carregamento
                contentArea.value = contentArea.value.replace(loadingMarker, '');

                if (data.success) {
                    // Se sucesso, insere a tag HTML <img>
                    const imageUrl = data.url;
                    const imageHtml = `\n<p><img src="${imageUrl}" alt="Imagem Colada" style="max-width: 100%; height: auto;"></p>\n`;
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