<?php
include(__DIR__.'/../../common.php');
$room = isset($_GET['room']) ? mysqli_real_escape_string($con, $_GET['room']) : "";
$room = mysqli_real_escape_string($con, $_GET['room']);
echo json_encode(getSignedInStudents($room))
?>