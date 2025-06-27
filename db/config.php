<?php
// Configuração do Banco de Dados
define('DB_SERVER', '127.0.0.1');
define('DB_USERNAME', 'root'); // Altere para seu usuário
define('DB_PASSWORD', ''); // Altere para sua senha
define('DB_NAME', 'kanban_db');

// Tenta conectar ao banco de dados MySQL
$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Checa a conexão
if($conn->connect_error){
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro de conexão com o banco de dados: ' . $conn->connect_error]);
    die();
}

// Define o charset para UTF-8 para suportar caracteres especiais
$conn->set_charset("utf8mb4");

?>
