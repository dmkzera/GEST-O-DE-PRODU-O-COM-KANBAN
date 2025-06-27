<?php
require_once '../db/config.php';

header('Content-Type: application/json');

$sql = "SELECT id, title, description, status, production_steps, lamination_details, shipping_status FROM cards WHERE shipping_status = 'none' ORDER BY created_at DESC";
$result = $conn->query($sql);

$cards = [];
if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $cards[] = $row;
    }
}

echo json_encode($cards);

$conn->close();
?>
