<?php

include "db_connect.php";


if(isset($_POST['device_id']))
{


$device_id=$_POST['device_id'];

$temp=$_POST['temperature'];

$humidity=$_POST['humidity'];

$gas=$_POST['gas_level'];

$light=$_POST['light_level'];

$status=$_POST['status'];



$sql="

INSERT INTO sensor_data

(
device_id,
temperature,
humidity,
gas_level,
light_level,
status
)

VALUES

(
'$device_id',
'$temp',
'$humidity',
'$gas',
'$light',
'$status'
)

";



if($conn->query($sql))
{

echo "SUCCESS";

}

else

{

echo $conn->error;

}


}

else

{

echo "NO DATA";

}


$conn->close();


?>