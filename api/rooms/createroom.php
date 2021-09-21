<?php
include(__DIR__.'/../../db/rooms.php');
if(!isset($_POST['room']))
	die('room not specified');
if(!isset($_POST['user']))
	die('user not specified');

$room = mysqli_real_escape_string($con, $_POST['room']);
$user = mysqli_real_escape_string($con, $_POST['user']);

setUserPermissions($room, $user, 1);
?>