<?php

include "db_connect.php";


$sql = "
SELECT *
FROM sensor_data
ORDER BY id DESC
LIMIT 1";

$result = $conn->query($sql);

if($result && $result->num_rows > 0){
    $row = $result->fetch_assoc();

// CREATE LIGHT STATUS
if($row['light_level'] < 2000){
$row['light_status'] = "BRIGHT";
}
else{
$row['light_status'] = "DARK";
}
echo json_encode($row);
}
else
{
echo json_encode([
"temperature"=>0,
"humidity"=>0,
"gas_level"=>0,
"light_level"=>0,
"light_status"=>"UNKNOWN",
"status"=>"NO DATA"
]);
}
$conn->close();


?>