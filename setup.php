<?php
//Commands to generate history and users tables
//Generated using "SHOW CREATE TABLE <tablename>"
if (file_exists(__DIR__.'/config.php')) {
		die('config.php already exists, delete it to run setup.');
}
$create_table_history = "CREATE TABLE `history` (
	`id` varchar(256) NOT NULL COMMENT 'Student ID',
	`place` varchar(256) NOT NULL COMMENT 'Location',
	`time_in` datetime DEFAULT CURRENT_TIMESTAMP COMMENT 'Time signed in',
	`time_out` datetime DEFAULT NULL COMMENT 'Time signed out',
	`reason_in` varchar(256) DEFAULT NULL COMMENT 'Sign in reason',
	`reason_out` varchar(256) DEFAULT NULL COMMENT 'Sign out reason'
	) ENGINE=InnoDB DEFAULT CHARSET=latin1";
$create_table_users = "CREATE TABLE `users` (
	`username` varchar(256) NOT NULL,
	`password_hash` varchar(256) NOT NULL,
	`permission_level` int(11) DEFAULT '0' COMMENT '0=Login Computer, 1=Room owner, 2=Admin'
	) ENGINE=InnoDB DEFAULT CHARSET=latin1";
$create_table_users_rooms = "CREATE TABLE `users_rooms` (
	`username` varchar(256) NOT NULL,
	`placename` varchar(256) NOT NULL,
	`is_owner` tinyint(1) NOT NULL DEFAULT '0'
	) ENGINE=InnoDB DEFAULT CHARSET=latin1";
$create_table_rooms_reasons_in = "CREATE TABLE `rooms_reasons_in` (
	`place` varchar(256) NOT NULL,
	`reason` varchar(256) NOT NULL
	) ENGINE=InnoDB DEFAULT CHARSET=latin1";
$create_table_rooms_reasons_out = "CREATE TABLE `rooms_reasons_out` (
	`place` varchar(256) NOT NULL,
	`reason` varchar(256) NOT NULL
	) ENGINE=InnoDB DEFAULT CHARSET=latin1";
if (isset($_POST['db_host'])
	&& isset($_POST['db_username'])
	&& isset($_POST['db_password'])
	&& isset($_POST['db_name'])
	&& isset($_POST['db_port'])
	&& isset($_POST['db_socket'])
	&& isset($_POST['admin_username'])
	&& isset($_POST['admin_password'])) {
	$db_host = $_POST['db_host'];
	$db_username = $_POST['db_username'];
	$db_password = $_POST['db_password'];
	$db_name = $_POST['db_name'];
	$db_port = $_POST['db_port'];
	$db_socket = $_POST['db_socket'];
	
	// mysqli_connect(host,username,password,dbname,port,socket);
	$con = mysqli_connect($db_host, $db_username, $db_password, "", intval($db_port), $db_socket);
	if (mysqli_connect_errno())
		die("Failed to connect to MySQL: " . mysqli_connect_error());
	if (!mysqli_select_db($con, $db_name)) {
		echo "Database was not found.<br>";
		$sql = "CREATE DATABASE $db_name";
		if (!$con->query($sql)) {
			die("Could not create database");
		}
		echo "Database '$db_name' created successfully<br>";
	}
	mysqli_select_db($con, $db_name);
	
	function tableExists($con, $db_name, $table_name) {
		$sql = "SELECT * FROM information_schema.tables
			WHERE table_schema = '$db_name'
			AND table_name = '$table_name'
			LIMIT 1";
		$query = mysqli_query($con, $sql);
		if(mysqli_num_rows($query) > 0) {
			echo "Table '$table_name' already exists in database '$db_name'<br>";
			return true;
		}
		return false;
	}
	
	if(!tableExists($con, $db_name, 'history')) {
		if($con->query($create_table_history)) {
			echo "Table 'history' created successfully<br>";
		} else {
			die("Error creating table 'history': " . $con->error);
		}
	}
	if(!tableExists($con, $db_name, 'users_rooms')) {
		if($con->query($create_table_users_rooms)) {
			echo "Table 'users_rooms' created successfully<br>";
		} else {
			die("Error creating table 'users_rooms': " . $con->error);
		}
	}
	if(!tableExists($con, $db_name, 'users')) {
		if($con->query($create_table_users)) {
			echo "Table 'users' created successfully<br>";
			$admin_username = mysqli_real_escape_string($con, stripslashes($_POST['admin_username']));
			$admin_password = mysqli_real_escape_string($con, stripslashes($_POST['admin_password']));
			$sql = "INSERT INTO users (username, password_hash)
					VALUES ('{$admin_username}','{$admin_password}')";
			if($con->query($sql)) {
				echo 'Admin account created successfully<br>';
			} else {
				die('Error creating admin account: ' . $con->error);
			}
		} else {
			die("Error creating table 'users': " . $con->error);
		}
	} else {
		echo "Table 'users' already exists, admin username and password were not changed.<br>";
	}
	if(!tableExists($con, $db_name, 'rooms_reasons_in')) {
		if($con->query($create_table_rooms_reasons_in)) {
			echo "Table 'rooms_reasons_in' created successfully<br>";
		} else {
			die("Error creating table 'rooms_reasons_in': " . $con->error);
		}
	}
	if(!tableExists($con, $db_name, 'rooms_reasons_out')) {
		if($con->query($create_table_rooms_reasons_out)) {
			echo "Table 'rooms_reasons_out' created successfully<br>";
		} else {
			die("Error creating table 'rooms_reasons_out': " . $con->error);
		}
	}
	
	$configfile = "<?php\n";
	$configfile .= "\$db_host = '$db_host';\n";
	$configfile .= "\$db_username = '$db_username';\n";
	$configfile .= "\$db_password = '$db_password';\n";
	$configfile .= "\$db_name = '$db_name';\n";
	$configfile .= "\$db_port = ".intval($db_port).";\n";
	$configfile .= "\$db_socket = '$db_socket';\n";
	$configfile .= "?>";
	
	if (!file_put_contents('config.php', $configfile)) {
		die('Could not save to config.php');
	}
}
?>
<!DOCTYPE html>
<html>
	<head>
		<title>StudentTracker Setup</title>
	</head>
	<body>
		<h1>StudentTracker Database Setup</h1>
		<div id="main">
			<form action="" method="post">
				<label>Database Host:</label>
				<input id="name" name="db_host" type="text" value="localhost">
				<br>
				<label>Database Username:</label>
				<input id="name" name="db_username" type="text" value="root">
				<br>
				<label>Database Password:</label>
				<input id="name" name="db_password" type="text">
				<br>
				<label>Database Name:</label>
				<input id="name" name="db_name" type="text" value="libraryproject">
				<br>
				<label>Database Port:</label>
				<input id="name" name="db_port" type="text">
				<br>
				<label>Database Socket:</label>
				<input id="name" name="db_socket" type="text">
				<br>
				<label>Admin Username:</label>
				<input id="name" name="admin_username" type="text">
				<br>
				<label>Admin Password:</label>
				<input id="password" name="admin_password" type="password">
				<br>
				<input name="submit" type="submit" value="Create">
			</form>
		</div>
	</body>
</html>