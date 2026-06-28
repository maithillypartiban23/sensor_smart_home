<?php

include "db_connect.php";


$device_id = $_GET['device_id'];


$sql =
"SELECT actuator_status 
FROM device_settings
WHERE device_id='$device_id'";


$result = $conn->query($sql);


$row = $result->fetch_assoc();


echo trim($row['actuator_status']);


$conn->close();

?>