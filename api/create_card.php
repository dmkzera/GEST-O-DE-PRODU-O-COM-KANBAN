<?php
require_once '../db/config.php';

header('Content-Type: application/json');

// Pega os dados enviados via POST
$data = json_decode(file_get_contents('php://input'), true);

$title = $data['title'] ?? '';
$description = $data['description'] ?? '';
$status = 'todo'; // Novos cards sempre começam em "A Fazer"

if (!empty($title)) {
        $stmt = $conn->prepare("INSERT INTO cards (title, description, status, production_steps, lamination_details) VALUES (?, ?, ?, '{}', '{}')");
    $stmt->bind_param("sss", $title, $description, $status);

    if ($stmt->execute()) {
        $new_card_id = $stmt->insert_id;
        echo json_encode(['success' => true, 'id' => $new_card_id]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro ao criar o card.']);
    }
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'O título é obrigatório.']);
}

$conn->close();
?>
