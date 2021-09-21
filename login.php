<?php
	include(__DIR__.'/db/database.php');
	session_start(); // Starting Session
	$error=''; // Variable To Store Error Message
	if (isset($_POST['submit'])) {
		if (empty($_POST['username']) || empty($_POST['password'])) {
			$error = "Username or Password is invalid";
		} else {
			//Get username and password from query string
			$username=$_POST['username'];
			$password=$_POST['password'];

			// Clean input to protect against MySQL injection
			$username = stripslashes($username);
			$password = stripslashes($password);
			$username = mysqli_real_escape_string($con, $username);
			$password = mysqli_real_escape_string($con, $password);
			// SQL query to fetch information of registerd users and finds user match.
			$query = mysqli_query($con, "select * from users where password_hash='$password' AND username='$username'");
			$rows = mysqli_num_rows($query);
			if ($rows == 1) {
				$_SESSION['login_user'] = $username; // Initializing Session
				header("location: panel.php"); // Redirecting To Other Page
			} else {
				$error = "Username or Password is invalid";
			}
			mysqli_close($con); // Closing Connection
		}
	}
?>