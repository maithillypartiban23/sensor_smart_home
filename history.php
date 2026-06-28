<?php

include "db_connect.php";


$date = $_GET['date'];

$start = $_GET['start'];

$end = $_GET['end'];



$startTime = $date . " " . $start . ":00";

$endTime = $date . " " . $end . ":59";



$sql = "

SELECT 

temperature,
humidity,
gas_level,
light_level,
status,
created_at

FROM sensor_data


WHERE created_at >= '$startTime'

AND created_at <= '$endTime'


ORDER BY created_at ASC


";



$result = $conn->query($sql);



$data = [];



while($row = $result->fetch_assoc())

{


$data[] = $row;


}



echo json_encode($data);



$conn->close();


?>