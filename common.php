<?php
include(__DIR__.'/db/session.php');
include(__DIR__.'/db/rooms.php');
include(__DIR__.'/db/history.php');
include(__DIR__.'/db/student.php');
include(__DIR__.'/db/users.php');

/**
 * Prints the top bar on the current page
 * @param $name the current page name to be used in the header
 */
function printTopBar($name) {
	?>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<div class="banner">
		<a href="index.php"><img id="logo" src="wiss.png"></a>
		<span class="pagetitle"><h1><?php echo $name; ?></h1></span>
		<span class="info">
			<b id="welcome">Logged in as <i
			<?php
				global $login_session, $permission_level;
				echo ($permission_level == 2 ? ' style="color:red">' : '>');
				echo $login_session;
			?></i></b><br>
		</span>
		<span class="menuarea">
			<div class="menu">
				<span class="cusdrop">
					<b>Student sign in/out</b>
					<div class="cusdroptext">
						<?php
							$arr = getRoomsAvailableToCurrentUser();
							foreach($arr as $key=>$value)
							{
								echo "<a class='cusbutton' href='studentpage.php?place={$value['placename']}'>{$value['placename']}</a>";
							}
						?>
					</div>
				</span>
				<?php if($permission_level != 0) { ?>
					<b id="dashboard"><a href="index.php" class="cusbutton">Dashboard</a></b>
					<b id="userhistory"><a href="gethistory.php" class="cusbutton">View history</a></b>
					<b id="rooms"><a href="rooms.php" class="cusbutton">Rooms</a></b>
					<?php if($permission_level == 2) { ?>
					<b id="rooms"><a href="control.php" class="cusbutton">Admin controls</a></b>
				<?php }} ?>
					<b id="logout"><a href="logout.php" class="cusbutton">Log Out</a></b><br>
			</div>
		</span>
	</div>
	<?php
}
?>