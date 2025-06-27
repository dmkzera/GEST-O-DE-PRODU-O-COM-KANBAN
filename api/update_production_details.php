<?php
require_once '../db/config.php';

header('Content-Type: application/json');
$data = json_decode(file_get_contents('php://input'), true);

$id = $data['id'] ?? 0;
$production_steps = $data['production_steps'] ?? null;
$lamination_details = $data['lamination_details'] ?? null;

if ($id > 0) {
    $sql_parts = [];
    $params = [];
    $types = "";

    if ($production_steps !== null) {
        $sql_parts[] = "production_steps = ?";
        $params[] = json_encode($production_steps);
        $types .= "s";
    }
    if ($lamination_details !== null) {
        $sql_parts[] = "lamination_details = ?";
        $params[] = json_encode($lamination_details);
        $types .= "s";
    }

    if (!empty($sql_parts)) {
        $sql = "UPDATE cards SET " . implode(", ", $sql_parts) . " WHERE id = ?";
        $types .= "i";
        $params[] = $id;

        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);

        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Erro ao atualizar detalhes da produção.']);
        }
        $stmt->close();
    } else {
         echo json_encode(['success' => false, 'message' => 'Nenhum dado para atualizar.']);
    }

} else {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Dados inválidos.']);
}

$conn->close();
?>
