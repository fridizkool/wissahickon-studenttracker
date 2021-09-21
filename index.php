<?php
include(__DIR__.'../login.php');

if (isset($_SESSION['login_user']))
	header("location: panel.php");
?>
<!DOCTYPE html>
<html>
	<link rel="stylesheet" type="text/css" href="custom.css">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Student tracking</title>
	<div class="banner" id="top">
		<img id="logo" src="wiss.png">
		<span class="pagetitle" style="font-size: 1vw">
			<h2>Student Sign-in/Sign-out Management</h2>
			<h3>Login Form</h3>
		</span>
	</div>
	<body class="content" id="page">
		<div id="main">
			<div id="login">
				<form action="" method="post" autocomplete="off">
					<label>Username :</label><br>
					<input id="name" name="username" placeholder="username" type="text" autofocus="true"><br>
					<label>Password :</label><br>
					<input id="password" name="password" placeholder="**********" type="password"><br><br>
					<input name="submit" type="submit" value=" Login " class="pagebutton" style="padding: 5px 5px; font-size: 2vw">
					<span><?php echo $error; ?></span>
				</form>
			</div>
		</div>
	</body>
</html>