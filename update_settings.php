<?php

include "db_connect.php";


$device_id = $_POST['device_id'];

$temp = $_POST['temperature_threshold'];

$gas = $_POST['gas_threshold'];


// FIX HERE
if(isset($_POST['actuator_status']))
{
    $relay = $_POST['actuator_status'];
}
else
{
    $relay = "OFF";
}



$sql = "

UPDATE device_settings

SET

temperature_threshold='$temp',

gas_threshold='$gas',


actuator_status='$relay',

updated_at=NOW()


WHERE device_id='$device_id'

";




if($conn->query($sql))
{

echo "SETTINGS UPDATED";

}

else

{

echo "ERROR: ".$conn->error;

}



$conn->close();


?>