<?php


include "db_connect.php";

$id=$_GET['device_id'];



$sql="
SELECT *
FROM device_settings
WHERE device_id='$id'
";

$result=$conn->query($sql);
echo json_encode(
$result->fetch_assoc()
);

$conn->close();


?>