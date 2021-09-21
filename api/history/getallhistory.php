<?php
include(__DIR__.'/../../common.php');
if(!isset($_GET['studentid']))
	die('studentid not specified');

$studentid = mysqli_real_escape_string($con, $_GET['studentid']);	
$room = isset($_GET['room']) ? mysqli_real_escape_string($con, $_GET['room']) : "";
$oldesttime = mysqli_real_escape_string($con, $_GET['oldesttime']);
if(strcmp($oldesttime, 'null') == 0)
	$oldesttime = 0;
echo json_encode(getAllHistory($studentid, $room, $oldesttime));
?>