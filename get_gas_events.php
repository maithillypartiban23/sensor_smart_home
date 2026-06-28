<?php
header('Content-Type: application/json');

$host = "localhost";
$user = "root";
$password = "";
$database = "iot_system";

$conn = new mysqli($host, $user, $password, $database);

if($conn->connect_error){
    echo json_encode(['count'=>0,'gas_threshold'=>400,'temperature_threshold'=>30]);
    exit;
}

// GET THRESHOLDS - use defaults if no row found
$gasThreshold  = 400;
$tempThreshold = 30;

$setting = $conn->query("SELECT gas_threshold, temperature_threshold FROM device_settings WHERE device_id='ESP32_01' LIMIT 1");
if($setting && $setting->num_rows > 0){
    $row = $setting->fetch_assoc();
    $gasThreshold  = intval($row['gas_threshold']);
    $tempThreshold = floatval($row['temperature_threshold']);
}

// COUNT TODAY'S POOR AIR EVENTS
$today = date('Y-m-d');
$result = $conn->query("SELECT COUNT(*) as count FROM sensor_data WHERE gas_level > $gasThreshold AND DATE(created_at) = '$today'");
$row = $result->fetch_assoc();

echo json_encode([
    'count'                 => intval($row['count']),
    'gas_threshold'         => $gasThreshold,
    'temperature_threshold' => $tempThreshold
]);

$conn->close();
?>
