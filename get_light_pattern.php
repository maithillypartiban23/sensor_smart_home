<?php

header('Content-Type: application/json');

$host = "localhost";
$user = "root";
$password = "";
$database = "iot_system";


$conn = new mysqli(
    $host,
    $user,
    $password,
    $database
);


if($conn->connect_error){
echo json_encode([
"percent"=>0,
"dark_percent"=>0,
"pattern"=>"No light data"
]);
exit;
}

// BRIGHT COUNT
// LDR value below 2000 = BRIGHT
$brightQuery = $conn->query(
"SELECT COUNT(*) AS total
FROM sensor_data
WHERE light_level < 2000"
);

$brightRow = $brightQuery->fetch_assoc();
$bright = intval($brightRow['total']);

// DARK COUNT
// LDR value 2000 and above = DARK
$darkQuery = $conn->query(
"SELECT COUNT(*) AS total
FROM sensor_data
WHERE light_level >= 2000"
);

$darkRow = $darkQuery->fetch_assoc();
$dark = intval($darkRow['total']);
$total = $bright + $dark;
if($total > 0){
$brightPercent = round(($bright / $total) * 100);
$darkPercent = 100 - $brightPercent;
}
else
{
$brightPercent = 0;
$darkPercent = 0;
}





if($darkPercent > 60)

{

$pattern = "Room is mostly dark. Consider improving lighting.";

}

else if($brightPercent > 60)

{

$pattern = "Good natural lighting detected.";

}

else

{

$pattern = "Balanced lighting condition.";

}





echo json_encode([

"percent"=>$brightPercent,

"dark_percent"=>$darkPercent,

"pattern"=>$pattern

]);



$conn->close();


?>