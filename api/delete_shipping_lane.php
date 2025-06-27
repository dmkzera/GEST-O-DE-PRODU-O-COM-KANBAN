<?php
require_once '../db/config.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$lane_id = $data['lane_id'] ?? 0;

if (empty($lane_id)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID da rota é obrigatório.']);
    exit;
}

$check_stmt = $conn->prepare("SELECT COUNT(*) as count FROM cards WHERE shipping_lane_id = ?");
$check_stmt->bind_param("i", $lane_id);
$check_stmt->execute();
$result = $check_stmt->get_result()->fetch_assoc();
$check_stmt->close();

if ($result['count'] > 0) {
    http_response_code(409); 
    echo json_encode(['success' => false, 'message' => 'Não é possível excluir a rota, pois existem pedidos atribuídos a ela.']);
    exit;
}


$stmt = $conn->prepare("DELETE FROM shipping_lanes WHERE id = ?");
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro na preparação da consulta: ' . $conn->error]);
    exit;
}

$stmt->bind_param("i", $lane_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Rota excluída com sucesso.']);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro ao excluir a rota: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>
