<?php
// Configurações do Banco de Dados
$host = 'localhost'; // XAMPP usa localhost
$db   = 'wikitecc_db'; // O nome que você criou
$user = 'root'; // Usuário padrão do XAMPP
$pass = ''; // Senha padrão do XAMPP (deixe VAZIA se não alterou)
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    // Define o modo de erro para exceções (melhor para desenvolvimento)
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    // Define o modo de busca padrão para arrays associativos
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    // Desativa a emulação de statements (melhor segurança/performance)
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
     // Cria a instância do PDO e conecta
     $pdo = new PDO($dsn, $user, $pass, $options);
     // O $pdo agora é sua variável de conexão para todo o projeto
     
} catch (\PDOException $e) {
     // Em caso de erro na conexão, pare o script e exiba a mensagem
     die("Erro de Conexão com o Banco de Dados: " . $e->getMessage());
}
?>