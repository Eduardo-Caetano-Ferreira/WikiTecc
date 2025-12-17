<?php
// =======================================================
// ARQUIVO: WikiTecc/admin_deletar_topico.php
// =======================================================

require 'admin_check.php'; // Proteção de login e session_start()
require 'db_connect.php'; 

$topico_id = $_GET['id'] ?? null;

if (empty($topico_id) || !is_numeric($topico_id)) {
    header("Location: index.php?status=error_id");
    exit;
}

$confirmacao = $_GET['confirm'] ?? 'false';

if ($confirmacao !== 'true') {
    // Redireciona para a confirmação JavaScript (injetada no HTML)
    echo "<script>
            if (confirm('Tem certeza de que deseja EXCLUIR o tópico de ID: {$topico_id}? Esta ação é irreversível e excluirá as imagens relacionadas DO DISCO!')) {
                window.location.href = 'admin_deletar_topico.php?id={$topico_id}&confirm=true';
            } else {
                window.location.href = 'index.php';
            }
          </script>";
    exit;
}

// ----------------------------------------------------
// 1. OTIMIZAÇÃO DE MÍDIA: EXCLUIR ARQUIVOS FÍSICOS
// ----------------------------------------------------
try {
    // A. Buscar os caminhos (caminho) de todas as imagens ligadas a este tópico
    $sql_select_files = "SELECT caminho FROM imagens WHERE topico_id = ?";
    $stmt_files = $pdo->prepare($sql_select_files);
    $stmt_files->execute([$topico_id]);
    $arquivos = $stmt_files->fetchAll(PDO::FETCH_COLUMN); // Pega apenas a coluna 'caminho'

    $arquivos_deletados = 0;

    // B. Iterar sobre os arquivos e tentar apagá-los
    foreach ($arquivos as $caminho_arquivo) {
        // Usa '../' se o script estiver em um subdiretório, mas no seu caso (raiz)
        // o caminho é relativo à raiz (ex: 'uploads/img_....jpg')
        if (file_exists($caminho_arquivo)) {
            // Tenta apagar o arquivo do disco. @ ignora erros se, por exemplo, não tiver permissão.
            if (@unlink($caminho_arquivo)) { 
                $arquivos_deletados++;
            }
        }
    }

} catch (PDOException $e) {
    // Não paramos o processo se a exclusão de arquivos falhar, apenas reportamos.
    $arquivos_erro = true;
    error_log("Erro ao buscar caminhos de arquivos para exclusão: " . $e->getMessage());
}

// ----------------------------------------------------
// 2. EXCLUSÃO DO REGISTRO (DELETE)
// ----------------------------------------------------
try {
    // Este DELETE acionará o ON DELETE CASCADE na tabela 'imagens'
    $sql_delete_topic = "DELETE FROM topicos WHERE id = ?";
    
    $stmt_delete = $pdo->prepare($sql_delete_topic);
    $stmt_delete->execute([$topico_id]);

    // 3. REDIRECIONA COM MENSAGEM DE SUCESSO
    if ($stmt_delete->rowCount() > 0) {
        $status_msg = "success_delete";
        if (isset($arquivos_deletados)) {
             $status_msg .= "&files_deleted=" . $arquivos_deletados;
        }
        header("Location: index.php?status=" . $status_msg);
        exit;
    } else {
        header("Location: index.php?status=not_found");
        exit;
    }

} catch (PDOException $e) {
    header("Location: index.php?status=error_db&details=" . urlencode($e->getMessage()));
    exit;
}
?>