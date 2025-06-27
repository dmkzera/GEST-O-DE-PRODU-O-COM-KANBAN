<?php
require_once '../db/config.php';

header('Content-Type: application/json');

// Pega os dados enviados via POST
$data = json_decode(file_get_contents('php://input'), true);

$id = $data['id'] ?? 0;
$status = $data['status'] ?? '';

$allowed_statuses = ['todo', 'doing', 'done'];

if ($id > 0 && in_array($status, $allowed_statuses)) {
    $stmt = $conn->prepare("UPDATE cards SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro ao atualizar o status.']);
    }
    $stmt->close();
} else {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Dados inválidos.']);
}

$conn->close();
?>
