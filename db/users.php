<?php
include(__DIR__.'/session.php');

/**
 * Creates a user
 * @param $username user name
 * @param $password user password
 * @param $permissions permission level
 */
function createUser($username, $password, $permissions)
{
	global $permission_level;
	if ($permission_level != 2) {
		echo "Permission denied";
		return false;
	}
	global $con;
	$sql = "SELECT * FROM users WHERE username='{$username}'";
	$result = mysqli_query($con, $sql);
	if(mysqli_num_rows($result) > 0)
	{
		echo "User already exists";
		return false;
	}

	$sql = "INSERT INTO users (username, password_hash, permission_level)
	VALUES ('{$username}', '{$password}', '{$permissions}')";
	$result = mysqli_query($con, $sql);

	echo "Success! Created user!<br>";
	echo "User info:<br>";
	echo "Username: ".$username."<br>";
	echo "Password: ".$password."<br>";
	echo "Permission level: ".$permissions;

	return true;
}

/**
 * Deletes a user
 * @param $username user name
 */
function deleteUser($username)
{
	global $login_session, $permission_level;
	if (strcmp($login_session, $username) == 0) {
		echo "You cannot delete yourself!";
		return false;
	}
	if ($permission_level != 2) {
		echo "Permission denied";
		return false;
	}
	global $con;
	$sql = "SELECT * FROM users WHERE username='{$username}'";
	$result = mysqli_query($con, $sql);
	if(mysqli_num_rows($result) == null)
	{
		echo "User does not exist";
		return false;
	}

	$sql = "DELETE FROM users WHERE username='{$username}';
			DELETE FROM users_rooms WHERE username='{$username}'";
	$result = mysqli_multi_query($con, $sql);

	$num_rooms = mysqli_affected_rows($con);

	echo "Success! Removed user!<br>";
	echo "User, ".$username." has been removed from the database and ".$num_rooms." rooms";

	return true;
}

/**
 * Gets a list of all users
 * @param $exclude exclude all users with this permission level
 * @param $excludecurrentuser if true, the the currently logged-in user will not be included in the list
 * @return list of users 
 */
function getUsers($exclude = -1, $excludecurrentuser = false) {
	global $con;

	$sql = "SELECT username FROM users
			WHERE permission_level != '{$exclude}'";
	
	if($excludecurrentuser) {
		global $login_session;
		$sql .= " AND username != '{$login_session}'";
	}
	
	$sql .= " ORDER BY username ASC";
	$result = mysqli_query($con, $sql);
	
	$rows = array();
	while ($row = mysqli_fetch_assoc($result)) {
		$rows[] = $row;
	}
	return $rows;
}
?>