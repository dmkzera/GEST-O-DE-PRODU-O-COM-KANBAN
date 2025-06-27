<?php
require_once '../db/config.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

$id = $data['id'] ?? 0;
$title = $data['title'] ?? '';
$description = $data['description'] ?? '';

if ($id > 0 && !empty($title)) {
    $stmt = $conn->prepare("UPDATE cards SET title = ?, description = ? WHERE id = ?");
    $stmt->bind_param("ssi", $title, $description, $id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro ao editar o card.']);
    }
    $stmt->close();
} else {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Dados inválidos. O ID e o título são obrigatórios.']);
}

$conn->close();
?>
