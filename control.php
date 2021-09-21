<?php
include(__DIR__.'/common.php');
?>
<!DOCTYPE html>
<html>
	<head>
		<title>Room management</title>
		<link rel="stylesheet" type="text/css" href="custom.css">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<script type="text/javascript" src="jQuery.js"></script>
		<script>
			var lastShownRoomName = "";
			var lastShownUser = "";
			var availableRooms = [];
			var availableUsers = [];
			function createRoom() {
				var owner_username = document.getElementById("createroomuser").value;
				var placename = document.getElementById("createroomname").value;
			
				if (window.XMLHttpRequest) {
					// code for IE7+, Firefox, Chrome, Opera, Safari
					xmlhttp = new XMLHttpRequest();
				} else {
					// code for IE6, IE5
					xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
				}
				xmlhttp.onreadystatechange = function() {
					if (this.readyState == 4 && this.status == 200) {
						document.getElementById("txtHint").innerHTML = this.responseText;
						updateAvailableRooms();
					}
				};
				
				xmlhttp.open("POST","api/rooms/createroom.php", true);
				xmlhttp.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
				xmlhttp.send("user="+owner_username+"&room="+placename);
			}

			function removeRoom() {
				var roomname = document.getElementById("removeroom").value;
			
				if (window.XMLHttpRequest) {
					// code for IE7+, Firefox, Chrome, Opera, Safari
					xmlhttp = new XMLHttpRequest();
				} else {
					// code for IE6, IE5
					xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
				}
				xmlhttp.onreadystatechange = function() {
					if (this.readyState == 4 && this.status == 200) {
						document.getElementById("txtHint2").innerHTML = this.responseText;
						updateAvailableRooms();
					}
				};
				
				xmlhttp.open("POST","api/rooms/removeroom.php", true);
				xmlhttp.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
				xmlhttp.send("room="+roomname);
			}

			function updateAvailableRooms() {
				if (window.XMLHttpRequest) {
					// code for IE7+, Firefox, Chrome, Opera, Safari
					xmlhttp = new XMLHttpRequest();
				} else {
					// code for IE6, IE5
					xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
				}
				xmlhttp.onreadystatechange = function(){
					if (this.readyState == 4 && this.status == 200) {
						availableRooms = JSON.parse(this.responseText);
						
						var sel = document.getElementById('removeroom');
						while (sel.firstChild) {
							sel.removeChild(sel.firstChild); //Clear existing options
						}
						var fragment = document.createDocumentFragment();
						
						availableRooms.forEach(function(room, index) {
							var opt = document.createElement('option');
							opt.innerHTML = room['placename'];
							opt.value = room['placename'];
							fragment.appendChild(opt);
						});
						sel.appendChild(fragment);
					}
				};
				xmlhttp.open("GET","api/rooms/getavailablerooms.php", true);
				xmlhttp.send();
			}

			function createUser() {
				var username = document.getElementById("createusername").value;
				var password = document.getElementById("createpass").value;
				var permission = document.getElementById("createperm").value;
			
				if (window.XMLHttpRequest) {
					// code for IE7+, Firefox, Chrome, Opera, Safari
					xmlhttp = new XMLHttpRequest();
				} else {
					// code for IE6, IE5
					xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
				}
				xmlhttp.onreadystatechange = function() {
					if (this.readyState == 4 && this.status == 200) {
						document.getElementById("txtHint3").innerHTML = this.responseText;
						updateAvailableUsers("createroomuser");
						updateAvailableUsers("removeusername");
					}
				};
				
				xmlhttp.open("POST","api/users/createuser.php", true);
				xmlhttp.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
				xmlhttp.send("user="+username+"&pass="+password+"&perm="+permission);
			}

			function removeUser() {
				var username = document.getElementById("removeusername").value;
			
				if (window.XMLHttpRequest) {
					// code for IE7+, Firefox, Chrome, Opera, Safari
					xmlhttp = new XMLHttpRequest();
				} else {
					// code for IE6, IE5
					xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
				}
				xmlhttp.onreadystatechange = function() {
					if (this.readyState == 4 && this.status == 200) {
						document.getElementById("txtHint4").innerHTML = this.responseText;
						updateAvailableUsers("createroomuser");
						updateAvailableUsers("removeusername");
					}
				};
				
				xmlhttp.open("POST","api/users/deleteuser.php", true);
				xmlhttp.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
				xmlhttp.send("user="+username);
			}

			function updateAvailableUsers(elementid) {
				var id= elementid;
				if (window.XMLHttpRequest) {
					// code for IE7+, Firefox, Chrome, Opera, Safari
					xmlhttp = new XMLHttpRequest();
				} else {
					// code for IE6, IE5
					xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
				}
				xmlhttp.onreadystatechange = function(){
					if (this.readyState == 4 && this.status == 200) {
						availableUsers = JSON.parse(this.responseText);
						
						var sel = document.getElementById(id);
						while (sel.firstChild) {
							sel.removeChild(sel.firstChild); //Clear existing options
						}
						var fragment = document.createDocumentFragment();
						
						availableUsers.forEach(function(user, index) {
							var opt = document.createElement('option');
							opt.innerHTML = user['username'];
							opt.value = user['username'];
							fragment.appendChild(opt);
						});
						sel.appendChild(fragment);
					}
				};
				xmlhttp.open("GET","api/users/getusers.php", true);
				xmlhttp.send();
			}
		</script>
	</head>
	<?php printTopBar('Room Management');?>
	<body id="page" class="content" onload=>
		<?php if($permission_level == 2) { ?>
			<div id="createroom" class="create">
				<b style="font-size: 30px;">Create room</b><br>
				Room Owner: 
				<select id="createroomuser" style="height: 28px; min-width: 150px;">
					<?php
						$arr = getUsers(0);
						foreach($arr as $key=>$value) {
							echo "<option value='{$value['username']}'>{$value['username']}</option>";
						}
					?>
				</select>
				<br><br>
				<input type="text" id="createroomname" placeholder="Room name" style="height: 35px;">
				<button onclick="createRoom()" style="height: 35px; position: relative; top: -5px;">Create Room</button>
				<div id="txtHint"></div>
				<b style="font-size: 30px;">Remove room</b><br>
				Room: 
				<select id="removeroom" style="height: 28px; min-width: 150px;">
					<?php
						$arr = getRoomsAvailableToCurrentUser();
						foreach($arr as $key=>$value) {
							echo "<option value='{$value['placename']}'>{$value['placename']}</option>";
						}
					?>
				</select>
				<br><br>
				<button onclick="removeRoom()" style="height: 35px; position: relative; top: -5px;">Remove Room</button>
				<div id="txtHint2"></div>
			</div>
			<div class="divider"></div>
			<div id="createuser">
				<b style="font-size: 30px;">Create user</b><br>
				<input type="text" id="createusername" placeholder="Username"><br>
				<input type="text" id="createpass" placeholder="Password"><br>
				Permission level: <input type="number" min=0 max=2 id="createperm" value=1 style="width: 30px;">(0=Login kiosk, 1=Teacher/staff, 2=Admin)<br>
				<button onclick="createUser()" style="height: 35px;">Create user</button>
				<div id="txtHint3"></div>
				<b style="font-size: 30px;">Remove user</b><br>
				User: 
				<select id="removeusername" style="height: 28px; min-width: 150px;">
					<?php
						$arr = getUsers(0, true);
						foreach($arr as $key=>$value) {
							echo "<option value='{$value['username']}'>{$value['username']}</option>";
						}
					?>
				</select>
				<br><br>
				<button onclick="removeUser()" style="height: 35px;">Remove user</button>
				<div id="txtHint4"></div>
			</div>
		<?php } ?>
	</body>
</html>