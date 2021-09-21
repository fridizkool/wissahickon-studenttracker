<?php
include(__DIR__.'/database.php');


//TODO: Test student management functions
function addStudent($FirstName, $LastName, $MiddleName, $GradeLevel, $GradYear, $Id)
{
	if ($permission_level != 2) {
		echo "Permission denied";
		return false;
	}
	global $con;
	$sql = "INSERT INTO students (FirstName, LastName, MiddleName, GradeLevel, GradYear, Id)
	VALUES ('{$FirstName}', '{$LastName}', '{$MiddleName}', '{$GradeLevel}', '{$GradYear}', '{$Id}')";
	$result = mysqli_query($con, $sql);
	return true;
}

function removeStudent($studentid)
{
	if ($permission_level != 2) {
		echo "Permission denied";
		return false;
	}
	global $con;
	$sql = "DELETE FROM students WHERE Id='{$studentid}'";
	$result = mysqli_query($con, $sql);
	return true;
}

function getStudent($studentid)
{
	global $con;
	$sql = "SELECT * FROM students WHERE Id='{$studentid}'";
	$result = mysqli_query($con, $sql);
	$row = mysqli_fetch_assoc($result);
	return $row;
}

/*function getStudentName($studentid)
{
	global $con;
	$sql = "SELECT LastName, FirstName, MiddleName FROM students WHERE Id='{$studentid}'";
	$result = mysqli_query($con, $sql);
	$row = mysqli_fetch_assoc($result);
	return $row['FirstName'] . ' ' . $row['MiddleName'] . ' ' . $row['LastName'];
}

function getGradeLevel($studentid)
{
	global $con;
	$sql = "SELECT GradeLevel FROM students WHERE Id='{$studentid}'";
	$result = mysqli_query($con, $sql);
	$row = mysqli_fetch_assoc($result);
	return $row['GradeLevel'];
}

function getGradYear($studentid)
{
	global $con;
	$sql = "SELECT GradYear FROM students WHERE Id='{$studentid}'";
	$result = mysqli_query($con, $sql);
	$row = mysqli_fetch_assoc($result);
	return $row['GradYear'];
}*/
?>