<?php
include(__DIR__.'/../../common.php');

if(!isset($_POST['studentid']))
	die('studentid not specified');
if(!isset($_POST['room']))
	die('room not specified');

$studentid = mysqli_real_escape_string($con, $_POST['studentid']);
$room = mysqli_real_escape_string($con, $_POST['room']);
$reason = isset($_POST['reason']) ? mysqli_real_escape_string($con, $_POST['reason']) : "";

signOutStudent($studentid, $room, $reason);
?>