<?php
require_once '../db/config.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$card_id = $data['card_id'] ?? 0;
$lane_id = $data['lane_id'] ?? 0;

if ($card_id > 0 && $lane_id > 0) {
    $stmt = $conn->prepare("UPDATE cards SET shipping_status = 'assigned', shipping_lane_id = ? WHERE id = ? AND status = 'done'");
    if (!$stmt) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Erro na preparação da consulta: ' . $conn->error]);
        exit;
    }

    $stmt->bind_param("ii", $lane_id, $card_id);
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Erro ao atribuir o card: ' . $stmt->error]);
    }
    $stmt->close();
} else {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'IDs do card e do destino inválidos.']);
}

$conn->close();
?>
