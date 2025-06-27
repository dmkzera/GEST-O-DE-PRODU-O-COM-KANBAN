<?php
require_once '../db/config.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$id = $data['id'] ?? 0;

if ($id > 0) {
    $stmt = $conn->prepare("DELETE FROM cards WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro ao deletar o card.']);
    }
    $stmt->close();
} else {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID do card inválido.']);
}

$conn->close();
?>
