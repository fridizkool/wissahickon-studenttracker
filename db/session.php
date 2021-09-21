<?php
	include(__DIR__."/database.php");
	// Selecting Database
	mysqli_select_db($con, $db_name);
	if (session_status() == PHP_SESSION_NONE) {
		session_start();// Starting Session
	}
	// Storing Session
	$user_check=$_SESSION['login_user'];
	// SQL Query To Fetch Complete Information Of User
	$ses_sql=mysqli_query($con, "SELECT username, permission_level FROM users WHERE username='$user_check'");
	$row = mysqli_fetch_assoc($ses_sql);
	$login_session = $row['username'];
	$permission_level = $row['permission_level'];
	if (!isset($login_session)) {
		mysqli_close($con); // Closing Connection
		header('Location: index.php'); // Redirecting To Home Page
		if(session_destroy()) { // Destroying All Sessions
			header("Location: ./"); // Redirecting To Home Page
	}
	}
?>