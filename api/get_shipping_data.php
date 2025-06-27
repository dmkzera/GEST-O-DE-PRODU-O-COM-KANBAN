<?php
require_once '../db/config.php';
header('Content-Type: application/json');

// Estrutura de resposta que o JavaScript espera
$response = [
    'cards' => [
        'pending' => [],
        'assigned' => []
    ],
    'lanes' => []
];

// 1. Buscar todas as rotas de envio (lanes)
$lanes_result = $conn->query("SELECT id, destination AS name, driver FROM shipping_lanes");
if ($lanes_result) {
    while ($row = $lanes_result->fetch_assoc()) {
        $response['lanes'][] = $row;
        // Inicializa um array de cards atribuídos para cada rota
        $response['cards']['assigned'][$row['id']] = [];
    }
}

// 2. Buscar todos os cards concluídos e distribuí-los
$cards_result = $conn->query("SELECT id, title, description, shipping_status, shipping_lane_id FROM cards WHERE status = 'done'");
if ($cards_result) {
    while ($row = $cards_result->fetch_assoc()) {
        if ($row['shipping_status'] === 'pending') {
            $response['cards']['pending'][] = $row;
        } elseif ($row['shipping_status'] === 'assigned' && isset($response['cards']['assigned'][$row['shipping_lane_id']])) {
            $response['cards']['assigned'][$row['shipping_lane_id']][] = $row;
        }
    }
}

echo json_encode($response);
$conn->close();
?>
