<?php
require_once '../db/config.php';
header('Content-Type: application/json');

// Get the posted data.
$data = json_decode(file_get_contents('php://input'), true);

$destination = $data['destination'] ?? null;
$driver = $data['driver'] ?? null;

if (empty($destination) || empty($driver)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Destino e motorista são obrigatórios.']);
    exit;
}

$stmt = $conn->prepare("INSERT INTO shipping_lanes (destination, driver) VALUES (?, ?)");
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro na preparação da consulta: ' . $conn->error]);
    exit;
}

$stmt->bind_param("ss", $destination, $driver);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Nova rota criada com sucesso.']);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro ao criar a rota: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>
