<?php
require_once '../db/config.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

$lane_id = $data['lane_id'] ?? 0;
$destination = $data['destination'] ?? null;
$driver = $data['driver'] ?? null;

if (empty($lane_id) || empty($destination) || empty($driver)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID da rota, destino e motorista são obrigatórios.']);
    exit;
}

$stmt = $conn->prepare("UPDATE shipping_lanes SET destination = ?, driver = ? WHERE id = ?");
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro na preparação da consulta: ' . $conn->error]);
    exit;
}

$stmt->bind_param("ssi", $destination, $driver, $lane_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Rota atualizada com sucesso.']);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro ao atualizar a rota: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>
