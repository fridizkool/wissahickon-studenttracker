<?php
	if (!file_exists(__DIR__.'/../config.php')) {
		die('Config file not found. Please run setup.');
	}
	require(__DIR__.'/../config.php');
	// mysqli_connect(host,username,password,dbname,port,socket);
	$con = mysqli_connect($db_host, $db_username, $db_password, $db_name, $db_port, $db_socket);
	if (!$con) {
		die('Could not connect: ' . mysqli_error($con));
	}
?>