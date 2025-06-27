<?php
require_once '../db/config.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$id = $data['id'] ?? 0;

if ($id > 0) {
    $stmt = $conn->prepare("UPDATE cards SET shipping_status = 'pending' WHERE id = ? AND status = 'done'");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro ao enviar para cargas.']);
    }
    $stmt->close();
} else {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID do card inválido.']);
}
$conn->close();
?>
