<?php
session_start();

$host = "localhost";
$user = "root";
$password = "";
$database = "iot_system";

$conn = new mysqli($host, $user, $password, $database);

if($conn->connect_error){
    echo "Database connection failed.";
    exit;
}

$device_id            = $_SESSION['device_id'] ?? $_POST['device_id'] ?? 'ESP32_01';
$temperature_threshold = $_POST['temperature_threshold'];
$gas_threshold         = $_POST['gas_threshold'];

$conn->query(
    "UPDATE device_settings
     SET temperature_threshold='$temperature_threshold',
         gas_threshold='$gas_threshold'
     WHERE device_id='$device_id'"
);

if($conn->affected_rows > 0){
    echo "Settings saved successfully.";
} else {
    echo "No changes made or device not found.";
}

$conn->close();
?>