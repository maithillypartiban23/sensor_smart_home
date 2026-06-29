<?php
header('Content-Type: application/json');
session_start();

$host = "localhost";
$user = "root";
$password = "";
$database = "iot_system";

$conn = new mysqli($host, $user, $password, $database);

if($conn->connect_error){
    echo json_encode(['temperature_threshold'=>35,'gas_threshold'=>400]);
    exit;
}

$device_id = $_SESSION['device_id'] ?? $_GET['device_id'] ?? 'ESP32_01';

$result = $conn->query(
    "SELECT temperature_threshold, gas_threshold
     FROM device_settings
     WHERE device_id='$device_id'
     LIMIT 1"
);

if($result && $result->num_rows > 0){
    $row = $result->fetch_assoc();
    echo json_encode([
        'temperature_threshold' => $row['temperature_threshold'],
        'gas_threshold'         => $row['gas_threshold']
    ]);
} else {
    echo json_encode(['temperature_threshold'=>35,'gas_threshold'=>400]);
}

$conn->close();
?>