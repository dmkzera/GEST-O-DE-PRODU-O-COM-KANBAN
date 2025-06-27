<?php
require_once '../db/config.php';
header('Content-Type: application/json');


$response = [
    'cards' => [
        'pending' => [],
        'assigned' => []
    ],
    'lanes' => []
];


$lanes_result = $conn->query("SELECT id, destination AS name, driver FROM shipping_lanes");
if ($lanes_result) {
    while ($row = $lanes_result->fetch_assoc()) {
        $response['lanes'][] = $row;
        $response['cards']['assigned'][$row['id']] = [];
    }
}


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
