<?php
// =======================================================
// ARQUIVO: WikiTecc/upload_handler.php
// =======================================================

header('Content-Type: application/json'); 
session_start(); // **CRUCIAL: INICIA A SESSÃO**
require 'db_connect.php'; 

// 1. IDENTIFICAÇÃO DO VÍNCULO (Tópico ou Sessão)
// topico_id só virá no POST se estivermos em modo EDIÇÃO.
$topico_id = $_POST['topico_id'] ?? null;
$sessao_id = null;

// Se não houver topico_id (estamos em CRIAÇÃO)
if (empty($topico_id)) {
    // Garante que a sessão tem um ID único
    if (!isset($_SESSION['upload_session_id'])) {
        $_SESSION['upload_session_id'] = uniqid('temp_', true);
    }
    $sessao_id = $_SESSION['upload_session_id'];
    $topico_id = null; // Garante que topico_id é NULL para o BD
} else {
    // Se há topico_id (estamos em EDIÇÃO), forçamos sessao_id para NULL
    $sessao_id = null; 
}

if (empty($topico_id) && empty($sessao_id)) {
    echo json_encode(['success' => false, 'message' => 'ID de rastreamento (tópico ou sessão) inválido.']);
    exit;
}

// 2. VERIFICAÇÃO DE ARQUIVO
if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'Erro no upload do arquivo.']);
    exit;
}

$file_info = $_FILES['image'];
$nome_temporario = $file_info['tmp_name'];
$mime_type = mime_content_type($nome_temporario);

if (!in_array($mime_type, ['image/jpeg', 'image/png', 'image/gif'])) {
    echo json_encode(['success' => false, 'message' => 'Formato de arquivo não suportado.']);
    exit;
}

// 3. SALVAR O ARQUIVO NO SERVIDOR
// Certifique-se que a pasta 'uploads/' existe e tem permissão de escrita
$extensao = match($mime_type) {
    'image/jpeg' => 'jpg',
    'image/png' => 'png',
    'image/gif' => 'gif',
    default => 'tmp',
};
$nome_unico = uniqid('img_', true) . '.' . $extensao;
$caminho_servidor = 'uploads/' . $nome_unico;

if (!move_uploaded_file($nome_temporario, $caminho_servidor)) {
    echo json_encode(['success' => false, 'message' => 'Falha ao mover o arquivo para a pasta uploads.']);
    exit;
}

// 4. REGISTRAR NO BANCO DE DADOS
try {
    // O PDO aceita NULL para colunas que aceitam NULL
    $sql = "INSERT INTO imagens (topico_id, sessao_id, nome_arquivo, caminho, ordem, data_upload) 
            VALUES (?, ?, ?, ?, ?, NOW())";
    
    $ordem = 1; 

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $topico_id,     // Será NULL se for criação
        $sessao_id,     // Será NULL se for edição
        $nome_unico, 
        $caminho_servidor, 
        $ordem
    ]);

    // 5. RETORNA SUCESSO E O CAMINHO
    echo json_encode([
        'success' => true, 
        'message' => 'Imagem salva com sucesso.', 
        'url' => $caminho_servidor
    ]);

} catch (PDOException $e) {
    @unlink($caminho_servidor); 
    echo json_encode(['success' => false, 'message' => 'Erro ao registrar no BD: ' . $e->getMessage()]);
}

?>