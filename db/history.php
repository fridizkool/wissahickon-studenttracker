<?php
include(__DIR__.'/database.php');

/**
 * Gets an array of all history records
 * @param $studentid if set, only return records for this student.
 * @param $placename if set, only return records for this room.
 * @param $oldesttime if set, only return records during or after this time.
 * @return history records
 */
function getAllHistory($studentid = NULL, $placename = NULL, $oldesttime = NULL) {
	global $con, $login_session;
	$sql = "";
	if($oldesttime)
		$sql .= "SET @lastupdate = STR_TO_DATE('{$oldesttime}', '%Y-%m-%d %H:%i:%s');";
	$sql .= "SELECT *";
	if($oldesttime)
		$sql .= ", (@lastupdate < time_out AND @lastupdate > time_in)as updated";
	$sql .= " FROM `history`";
	if(strcmp(substr($studentid, -1), '*') == 0) //If the last character is '*'
		$sql .= " WHERE id LIKE '".substr($studentid, 0, -1)."%'";
	else
		$sql .= " WHERE id = '{$studentid}'";
	if(strcmp($placename, "") != 0)
		$sql .= " AND place='{$placename}'";
	if($oldesttime)
		$sql .= " AND (@lastupdate < time_in OR @lastupdate < time_out)";
	
	//User is admin or has permission to room
	$sql .= " AND ((SELECT permission_level FROM users WHERE username='{$login_session}')=2
					OR place = (SELECT placename FROM users_rooms WHERE username = '{$login_session}' AND users_rooms.placename = place));";
	mysqli_multi_query($con, $sql);
	$result = mysqli_store_result($con);
	if(mysqli_next_result($con))
		$result = mysqli_store_result($con);
	$rows = array();
	while ($row = mysqli_fetch_assoc($result))
		$rows[] = $row;
	return $rows;
}

/**
 * Gets an array of all signed in students
 * @param $placename if set, only return students signed into this room
 * @return array of students
 */
function getSignedInStudents($placename) {
	global $con, $login_session;
	$sql = "SELECT t1.id, t1.place, t1.time_in, t1.reason_in FROM history t1
			JOIN (SELECT id, MAX(time_in) time_in FROM history GROUP BY id) t2
			ON t1.id = t2.id AND t1.time_in = t2.time_in WHERE time_out IS NULL";
	if (strcmp($placename, "") != 0) {
		$sql .= " AND place='{$placename}'";
	}
	//User is admin or has permission to room
	$sql .= " AND ((SELECT permission_level FROM users WHERE username='{$login_session}')=2
					OR place = (SELECT placename FROM users_rooms WHERE username = '{$login_session}' AND users_rooms.placename = place));";
	$result = mysqli_query($con, $sql);
	$rows = array();
	while ($row = mysqli_fetch_assoc($result))
	{
		$rows[] = $row;
	}
	return $rows;
}

/**
 * Signs a student out of a room
 * @param $studentid student username
 * @param $place room
 * @param $reason reason
 */
function signOutStudent($studentid, $place, $reason) {
	global $con;
	$sql = "UPDATE history SET time_out = IF(time_out IS NULL, now(), time_out),
			reason_out = '{$reason}' 
			WHERE id = '{$studentid}' AND place = '{$place}' 
			ORDER BY time_in DESC
			LIMIT 1";
	echo mysqli_query($con,$sql);
	return mysqli_affected_rows($con);
}

/**
 * Signs a student into a room
 * @param $studentid student username
 * @param $place room
 * @param $reason reason
 */
function signInStudent($studentid, $place, $reason) {
	global $con;
	$sql = "INSERT INTO history (id, place, reason_in) VALUES ('{$studentid}', '{$place}', '{$reason}')";
	echo mysqli_query($con,$sql);
	return mysqli_affected_rows($con);
}

/**
 * Check if a student is signed in
 * @param $place if set, checks if the student is signed into this room
 * @param $studentid student username
 * @return true if the student is signed in
 */
function isSignedIn($place = "", $studentid)
{
	global $con;
	$arr = getSignedInStudents($place); //TODO: check if the student is signed in without getting all signed in students
	foreach($arr as $x)
	{
		if($x['id'] == $studentid)
		{
			return true;
		}
	}
	return false;
}
?>