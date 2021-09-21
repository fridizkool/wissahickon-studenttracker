<?php
include(__DIR__.'/session.php');

/**
 * Gets the permissions of every user with access to the room. Returns null if the user doesn't have access.
 * Does not include kiosk users.
 * @param $placename room name
 * @param $is_owner if set, filters to users with this access level.
 * @return user permissions
 */
function getRoomUsers($placename, $is_owner = -1) {
	global $con;
	$sql = "SELECT username, is_owner FROM (
				SELECT *
				FROM users_rooms
				WHERE placename='{$placename}'";
	if($is_owner != -1) {
		$sql .= " AND is_owner={$is_owner}";
	}
	$sql .=		" UNION
				SELECT username, NULL as placename, NULL as is_owner
				FROM users) AS combined
				GROUP BY username"; //TODO: Make this not include kiosk users
	$result = mysqli_query($con, $sql);
	$rows = array();
	while ($row = mysqli_fetch_assoc($result)) {
		$rows[] = $row;
	}
	return $rows;
}

/**
 * Gets the password for a room's kiosk account
 * @param $placename room name
 * @return kiosk password
 */
function getKiosk($placename)
{
	global $con;
	if(!currentUserHasRoomPermissions($placename)) {
		die("Permission denied");
	}
	$sql = "SELECT password_hash FROM users WHERE username='{$placename}student' AND permission_level=0";
	$result = mysqli_query($con, $sql);
	$row = mysqli_fetch_assoc($result);
	return $row['password_hash'];
}

/**
 * Gets all of the rooms that the currently logged-in user has access to
 * @return room list
 */
function getRoomsAvailableToCurrentUser() {
	global $con, $login_session;
	$sql = "SELECT placename FROM users_rooms
			WHERE username='{$login_session}'
			OR (SELECT permission_level FROM users WHERE username='{$login_session}')=2
            GROUP BY placename
			ORDER BY placename ASC";
			
	$result = mysqli_query($con, $sql);
	$rows = array();
	while ($row = mysqli_fetch_assoc($result)) {
		$rows[] = $row;
	}
	return $rows;
}

/**
 * Check if logged-in user has permissions to a room
 * @param $placename room name
 * @param $permissions minimum permission level
 * @return boolean value
 */
function currentUserHasRoomPermissions($placename, $permissions = 1) {
	global $permission_level, $login_session;
	if($permission_level == 2) //If the logged-in user is an admin
			return true;
	
	global $con;
	$permissions = intval($permissions);
	$sql = "SELECT COUNT(*) FROM users_rooms WHERE username='{$username}' AND placename='{$placename}' AND is_owner >= {$permissions}";
	$result = mysqli_query($con, $sql);
	return intval(mysqli_fetch_row($result)[0]);
}

/**
 * Sets a user's access level for a room
 * Also creates a kiosk account for the room if it doesn't exist
 * @param $room room name
 * @param $user user name
 * @param $permissions permission level
 */
function setUserPermissions($room, $user, $permissions) {
	if(removeRoomUser($room, $user)) { //If user was removed successfully, we know that the current user has permission
		$permissions = intval($permissions);
		if($permissions >= 0) {
			global $con;
			$sql = "INSERT INTO users_rooms(username, placename, is_owner) VALUES('{$user}','{$room}',{$permissions})";
			$result = mysqli_query($con, $sql);
			$sql = "SELECT * FROM users WHERE username='{$room}student'";
			$result = mysqli_query($con, $sql);
			if(mysqli_num_rows($result) == null) {	//Create login kiosk for room if none exist
				$pass = getRandomWord(8);
				$sql = "INSERT INTO users (username, password_hash, permission_level) VALUES ('{$room}student', '{$pass}', 0);
						INSERT INTO users_rooms (username, placename, is_owner) VALUES ('{$room}student', '{$room}', false)";
				$result = mysqli_multi_query($con, $sql);

				echo "Success! Created room and login kiosk!<br>";
				echo "Login Kiosk login info:<br>";
				echo "Username: ".$room."student<br>";
				echo "Password: {$pass}";
			}
		}
		return true;
	}
	else return false;
}

/**
 * Removes a user's access to a room
 * @param $room room name
 * @param $user user name
 */
function removeRoomUser($room, $user) {
	if (currentUserHasRoomPermissions($room)) {
		global $con;
		$sql = "DELETE FROM users_rooms WHERE (username='{$user}' AND placename='{$room}')";
		$result = mysqli_query($con, $sql);
		return true;
	} else {
		die("Permission denied");
	}
}

/**
 * Deletes a room and its login kiosk account
 * This does not clear the room's reason list, so reasons will still be there if the room is recreated.
 * @param $room
 */
function removeRoom($room)
{
	global $permission_level;
	if ($permission_level != 2) {
		die("Permission denied");
	}
	global $con;
	$sql = "DELETE FROM users_rooms WHERE placename='{$room}';
			DELETE FROM users WHERE username='{$room}student'";
	$result = mysqli_multi_query($con, $sql);

	echo "Deleted room '".$room."' and login kiosk '".$room."student'!";

	return true;
}

/**
 * Adds a sign-in reason to a room
 * @param $placename room name
 * @param $reason reason
 */
function addReasonIn($placename, $reason) {
	if (!currentUserHasRoomPermissions($placename)) {
		die("Permission denied");
	}
	global $con;
	$sql = "SELECT * FROM rooms_reasons_in WHERE place='{$placename}' AND reason='{$reason}'";
	$result = mysqli_query($con, $sql);
	if(mysqli_num_rows($result) > 0) {
		echo "Reason in already exists";
		return false;
	}
	$sql = "INSERT INTO rooms_reasons_in (place, reason)
	VALUES ('{$placename}', '{$reason}')";
	$result = mysqli_query($con, $sql);
	return true;
}

/**
 * Adds a sign-out reason to a room
 * @param $placename room name
 * @param $reason reason
 */
function addReasonOut($placename, $reason) {
	if (!currentUserHasRoomPermissions($placename)) {
		die("Permission denied");
	}
	global $con;
	$sql = "SELECT * FROM rooms_reasons_out WHERE place='{$placename}' AND reason='{$reason}'";
	$result = mysqli_query($con, $sql);
	if(mysqli_num_rows($result) > 0) {
		echo "Reason out already exists";
		return false;
	}
	$sql = "INSERT INTO rooms_reasons_out (place, reason)
	VALUES ('{$placename}', '{$reason}')";
	$result = mysqli_query($con, $sql);
	return true;
}

/**
 * Deletes a sign-in reason from a room
 * @param $placename room name
 * @param $reason reason
 */
function removeReasonIn($placename, $reason) {
	if (!currentUserHasRoomPermissions($placename)) {
		die("Permission denied");
	}
	global $con;
	$sql = "DELETE FROM rooms_reasons_in WHERE place='{$placename}' AND reason='{$reason}'";
	$result = mysqli_query($con, $sql);
	return true;
}

/**
 * Deletes a sign-out reason from a room
 * @param $placename room name
 * @param $reason reason
 */
function removeReasonOut($placename, $reason) {
	if (!currentUserHasRoomPermissions($placename)) {
		die("Permission denied");
	}
	global $con;
	$sql = "DELETE FROM rooms_reasons_out WHERE place='{$placename}' AND reason='{$reason}'";
	$result = mysqli_query($con, $sql);
	return true;
}

/**
 * Gets a list of sign-in reasons for a room
 * @param $placename room name
 * @return reason list
 */
function getReasonsIn($placename) {
	global $con;
	$sql = "SELECT DISTINCT reason FROM rooms_reasons_in WHERE place='{$placename}'";
	$result = mysqli_query($con, $sql);
	$rows = array();
	while ($row = mysqli_fetch_assoc($result)) {
		$rows[] = $row;
	}
	return $rows;
}

/**
 * Gets a list of sign-out reasons for a room
 * @param $placename room name
 * @return reason list
 */
function getReasonsOut($placename) {
	global $con;
	$sql = "SELECT DISTINCT reason FROM rooms_reasons_out WHERE place='{$placename}'";
	$result = mysqli_query($con, $sql);
	$rows = array();
	while ($row = mysqli_fetch_assoc($result)) {
		$rows[] = $row;
	}
	return $rows;
}

//TODO: Move this to a common file
//		It can't be put in the current common.php because it includes rooms.php
/**
 * Gets a random word containing capital and lowercase letters
 * @param $len word size
 * @return random word
 */
function getRandomWord($len = 10)
{
    $alphabet = array_merge(range('a', 'z'), range('A', 'Z'));
	$alphaLength = sizeof($alphabet) - 1;
	
	$word = array();
    for ($i = 0; $i < $len; $i++) {
        $n = rand(0, $alphaLength);
        $word[] = $alphabet[$n];
    }
    return implode($word);
}
?>