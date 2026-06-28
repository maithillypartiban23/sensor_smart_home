<?php

include "db_connect.php";


$device_id = $_POST['device_id'];
$status = $_POST['actuator_status'];


$sql = "
UPDATE device_settings
SET actuator_status='$status',
updated_at=NOW()
WHERE device_id='$device_id'
";


if($conn->query($sql))
{
    echo "UPDATED: ".$status;
}
else
{
    echo "ERROR: ".$conn->error;
}


$conn->close();

?>