<?php
include(__DIR__.'/../../db/rooms.php');
if(!isset($_POST['room']))
	die('room not specified');
if(!isset($_POST['reason']))
	die('reason not specified');

$room = mysqli_real_escape_string($con, $_POST['room']);
$reason = mysqli_real_escape_string($con, $_POST['reason']);

addReasonIn($room, $reason);
?>