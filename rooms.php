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
			$(document).ready(function()
			{
			});

			var availableRooms;
			var reasonsIn = [];
			var reasonsOut = [];

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
						
						var sel = document.getElementById('manageroomname');
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
						
						if(lastShownRoomName != null) {
							var manageroomname = document.getElementById('manageroomname');
							manageroomname.value = lastShownRoomName;
						}
					}
				};
				xmlhttp.open("GET","api/rooms/getavailablerooms.php", true);
				xmlhttp.send();
			}
			
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
				
				xmlhttp.open("POST", "api/rooms/createroom.php", true);
				xmlhttp.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
				xmlhttp.send("user="+owner_username+"&room="+placename);
			}
			var lastShownRoomName;
			function removeFromRoom(username) {
				if (window.XMLHttpRequest) {
					// code for IE7+, Firefox, Chrome, Opera, Safari
					xmlhttp = new XMLHttpRequest();
				} else {
					// code for IE6, IE5
					xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
				}
				xmlhttp.onreadystatechange = function() {
					if (this.readyState == 4 && this.status == 200) {
						//document.getElementById("txtHint").innerHTML = this.responseText;
						updateAvailableRooms();
						getRoom();
					}
				};
				
				xmlhttp.open("POST", "api/rooms/setuserpermissions.php", true);
				xmlhttp.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
				xmlhttp.send("user="+username+"&room="+lastShownRoomName+"&permissions=-1");
			}
			function getRoom() {
				var placename = document.getElementById("manageroomname").value;
				lastShownRoomName = placename;
			
				if (window.XMLHttpRequest) {
					// code for IE7+, Firefox, Chrome, Opera, Safari
					xmlhttp = new XMLHttpRequest();
				} else {
					// code for IE6, IE5
					xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
				}
				xmlhttp.onreadystatechange = function() {
					if (this.readyState == 4 && this.status == 200) {
						updateAvailableRooms();
						
						var txtHint = document.getElementById('manage');
						
						var fragment = updateRoomManagement(this.responseText);
						while (txtHint.firstChild) {
							txtHint.removeChild(txtHint.firstChild); //Clear nodes
						}
						fragment.appendChild(document.createElement('br'));
						fragment.appendChild(document.createElement('br'));
						txtHint.appendChild(fragment);
						getKiosk();
					}
				};
				
				xmlhttp.open("GET","api/rooms/getusers.php?room=" + placename, true);
				xmlhttp.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
				xmlhttp.send();
			}

			function getKiosk() {
				var placename = document.getElementById("manageroomname").value;
				lastShownRoomName = placename;
			
				if (window.XMLHttpRequest) {
					// code for IE7+, Firefox, Chrome, Opera, Safari
					xmlhttp = new XMLHttpRequest();
				} else {
					// code for IE6, IE5
					xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
				}
				xmlhttp.onreadystatechange = function() {
					if (this.readyState == 4 && this.status == 200) {
						document.getElementById('kioskinfo').innerHTML = "<br><h3><b>Login kiosk info:</b></h3>Username: "+placename+"student<br>Password: "+this.responseText;
					}
				};
				
				xmlhttp.open("GET","api/rooms/getkiosk.php?room=" + placename, true);
				xmlhttp.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
				xmlhttp.send();
			}

			function addUser() {
				var username = document.getElementById("addroomuser").value;
				if (window.XMLHttpRequest) {
					// code for IE7+, Firefox, Chrome, Opera, Safari
					xmlhttp = new XMLHttpRequest();
				} else {
					// code for IE6, IE5
					xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
				}
				xmlhttp.onreadystatechange = function() {
					if (this.readyState == 4 && this.status == 200) {
						updateAvailableRooms();
						getRoom();
					}
				};
				
				xmlhttp.open("POST","api/rooms/setuserpermissions.php", true);
				xmlhttp.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
				xmlhttp.send("user="+username+"&room="+lastShownRoomName+"&permissions=0");
			}
			function togglePermissions(username, permissions) {
				if (window.XMLHttpRequest) {
					// code for IE7+, Firefox, Chrome, Opera, Safari
					xmlhttp = new XMLHttpRequest();
				} else {
					// code for IE6, IE5
					xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
				}
				xmlhttp.onreadystatechange = function() {
					if (this.readyState == 4 && this.status == 200) {
						updateAvailableRooms();
						getRoom();
					}
				};
				permissions = (+permissions + 1) % 2; //The + is needed before permissions to add it as an integer
				xmlhttp.open("POST","api/rooms/setuserpermissions.php", true);
				xmlhttp.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
				xmlhttp.send("user="+username+"&room="+lastShownRoomName+"&permissions="+permissions);
			}
			function addReasonIn() {
				var reason = document.getElementById("addreason").value;
				if (window.XMLHttpRequest) {
					// code for IE7+, Firefox, Chrome, Opera, Safari
					xmlhttp = new XMLHttpRequest();
				} else {
					// code for IE6, IE5
					xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
				}
				xmlhttp.onreadystatechange = function() {
					if (this.readyState == 4 && this.status == 200) {
						updateAvailableRooms();
						getRoom();
					}
				};
				
				xmlhttp.open("POST","api/rooms/createreasonin.php", true);
				xmlhttp.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
				xmlhttp.send("room="+lastShownRoomName+"&reason="+reason);
			}
			function addReasonOut() {
				var reason = document.getElementById("addreason").value;
				if (window.XMLHttpRequest) {
					// code for IE7+, Firefox, Chrome, Opera, Safari
					xmlhttp = new XMLHttpRequest();
				} else {
					// code for IE6, IE5
					xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
				}
				xmlhttp.onreadystatechange = function() {
					if (this.readyState == 4 && this.status == 200) {
						updateAvailableRooms();
						getRoom();
					}
				};
				
				xmlhttp.open("POST","api/rooms/createreasonout.php", true);
				xmlhttp.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
				xmlhttp.send("room="+lastShownRoomName+"&reason="+reason);
			}

			function getReasonsIn()
			{
				var placename = document.getElementById("manageroomname").value;
				if (window.XMLHttpRequest) {
					// code for IE7+, Firefox, Chrome, Opera, Safari
					xmlhttp = new XMLHttpRequest();
				} else {
					// code for IE6, IE5
					xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
				}
				xmlhttp.onreadystatechange = function(){
					if (this.readyState == 4 && this.status == 200)
					{
						var txthint = document.getElementById('manageroomreasonin');
						if(document.getElementById('reasonInTable') != null)
							txthint.removeChild(document.getElementById('reasonInTable'));
						var table = document.createElement('table');
						table.id = "reasonInTable";
						var tt = document.createElement('tr');
						var th = document.createElement('th');
						th.innerHTML = "Reason in";
						tt.appendChild(th);
						th = document.createElement('th');
						th.innerHTML = "Remove";
						tt.appendChild(th);
						table.appendChild(tt);
						var r = this.responseText;
						reasonsIn = JSON.parse(r);

						for(var x = 0; x < reasonsIn.length; x++)
						{
							var tr = document.createElement('tr');
							var td = document.createElement('td');
							tr.id = "reason: " + reasonsIn[x].reason;
							td.innerHTML = reasonsIn[x].reason;
							tr.appendChild(td);
							td = document.createElement('td');
							var remove = document.createElement('button');
							remove.appendChild(document.createTextNode("Remove"));
							remove.value = reasonsIn[x].reason;
							remove.onclick = function()
							{
								var doc = document.getElementById('reasonInTable');
								doc.removeChild(document.getElementById("reason: "+this.value));
								removeReasonIn(this.value);
							};
							td.appendChild(remove);
							tr.appendChild(td);
							table.appendChild(tr);
						}

						txthint.appendChild(table);
					}
				}
				xmlhttp.open("GET","api/rooms/getreasonsin.php?room="+placename, true);
				xmlhttp.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
				xmlhttp.send();
			}

			function getReasonsOut()
			{
				var placename = document.getElementById("manageroomname").value;
				if (window.XMLHttpRequest) {
					// code for IE7+, Firefox, Chrome, Opera, Safari
					xmlhttp = new XMLHttpRequest();
				} else {
					// code for IE6, IE5
					xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
				}
				xmlhttp.onreadystatechange = function(){
					if (this.readyState == 4 && this.status == 200) {
						var txthint = document.getElementById('manageroomreasonout');
						if(document.getElementById("reasonOutTable") != null)
							txthint.removeChild(document.getElementById("reasonOutTable"));
						var table = document.createElement('table');
						table.id = "reasonOutTable";
						var tt = document.createElement('tr');
						var th = document.createElement('th');
						th.innerHTML = "Reason out";
						tt.appendChild(th);
						th = document.createElement('th');
						th.innerHTML = "Remove";
						tt.appendChild(th);
						table.appendChild(tt);
						var r = this.responseText;
						reasonsOut = JSON.parse(r);

						for(var x = 0; x < reasonsOut.length; x++)
						{
							var tr = document.createElement('tr');
							var td = document.createElement('td');
							tr.id = "reason: " + reasonsOut[x].reason;
							td.innerHTML = reasonsOut[x].reason;
							tr.appendChild(td);
							td = document.createElement('td');
							var remove = document.createElement('button');
							remove.appendChild(document.createTextNode("Remove"));
							remove.value = reasonsOut[x].reason;
							remove.onclick = function()
							{
								var doc = document.getElementById('reasonOutTable');
								doc.removeChild(document.getElementById("reason: "+this.value));
								removeReasonOut(this.value);
							};
							td.appendChild(remove);
							tr.appendChild(td);
							table.appendChild(tr);
						}
						if(reasonsOut.length > reasonsIn.length)
							document.getElementById('manageroomreasonin').style = "float: left;";
						else if(reasonsOut.length < reasonsIn.length)
							txthint.style = "float: right;";

						txthint.appendChild(table);
					}
				}
				xmlhttp.open("GET","api/rooms/getreasonsout.php?room="+placename, true);
				xmlhttp.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
				xmlhttp.send();
			}

			function removeReasonIn(reason) {
				var placename = document.getElementById("manageroomname").value;
				lastShownRoomName = placename;
				if (window.XMLHttpRequest) {
					// code for IE7+, Firefox, Chrome, Opera, Safari
					xmlhttp = new XMLHttpRequest();
				} else {
					// code for IE6, IE5
					xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
				}
				xmlhttp.onreadystatechange = function() {
					if (this.readyState == 4 && this.status == 200) {
						updateRoomManagement();
						getRoom();
					}
				};
				
				xmlhttp.open("POST","api/rooms/deletereasonin.php", true);
				xmlhttp.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
				xmlhttp.send("reason="+reason+"&room="+lastShownRoomName);
			}

			function removeReasonOut(reason) {
				var placename = document.getElementById("manageroomname").value;
				lastShownRoomName = placename;
				if (window.XMLHttpRequest) {
					// code for IE7+, Firefox, Chrome, Opera, Safari
					xmlhttp = new XMLHttpRequest();
				} else {
					// code for IE6, IE5
					xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
				}
				xmlhttp.onreadystatechange = function() {
					if (this.readyState == 4 && this.status == 200) {
						updateRoomManagement();
						getRoom();
					}
				};
				
				xmlhttp.open("POST","api/rooms/deletereasonout.php", true);
				xmlhttp.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
				xmlhttp.send("reason="+reason+"&room="+lastShownRoomName);
			}

			function updateRoomManagement(json) {
				var fragment = document.createDocumentFragment();
				var users = JSON.parse(json);
				var info = ["", "", "", ""];
				//var temp = "";
				var v = 0;
				if (!users.length) {
					return "<b>Room doesn't exist or has no users.</b>";
				}
				var usersNotInRoom;
				var manager = document.createElement('div');
				manager.id = "manager";
				var header = document.createElement('b');
				header.id = 'tableheader';
				header.style = "font-size: 30px;"
				header.appendChild(document.createTextNode("Room: " + lastShownRoomName));
				manager.appendChild(header);
				manager.appendChild(document.createElement('br'));
				
				var select = document.createElement('select');
				select.id = 'addroomuser';
				select.style = "height: 28px; min-width: 150px;";
				
				var addButton = document.createElement('button');
				addButton.onclick = function() {
					addUser();
				};
				addButton.id = 'addroomuser';
				addButton.appendChild(document.createTextNode("Add User"));
				addButton.style = "height: 30px;";
				
				var table = document.createElement('table');
				var tr = document.createElement('tr');
				
				var th = document.createElement('th');
				th.appendChild(document.createTextNode("User"));
				tr.appendChild(th);
				th = document.createElement('th');
				th.appendChild(document.createTextNode("Is Owner"));
				tr.appendChild(th);
				th = document.createElement('th');
				tr.appendChild(th);				
				table.appendChild(tr);
				
				for (var i = 0; i < users.length; i++) {
					var record = users[i];
					if(record['is_owner'] == null) {
						var opt = document.createElement('option');
						opt.innerHTML = record['username'];
						opt.value = record['username'];
						select.appendChild(opt);
					} else {
						tr = document.createElement('tr');
						for (var column in record) {
							info[v] = "" + record[column];
							var td = document.createElement('td');
							td.appendChild(document.createTextNode(info[v]));
							tr.appendChild(td);
							v++;
						}
						
						var td = document.createElement('td');
						
						var removebutton = document.createElement('button');
						removebutton.value = record['username'];
						removebutton.onclick = function() {
							//The 2nd parameter is the is_owner value
							removeFromRoom(this.value);
						};
						removebutton.appendChild(document.createTextNode("Remove"));
						
						td.appendChild(removebutton);
						
						var is_owner = record['is_owner'];
						var ownerbutton = document.createElement('button');
						ownerbutton.value = record['username'];
						ownerbutton.onclick = function() {
							togglePermissions(this.value, this.parentNode.parentNode.childNodes[1].childNodes[0].nodeValue);
						};
						ownerbutton.appendChild(document.createTextNode(is_owner == 0 ? "Set as room owner" : "Unset as room owner"));
						
						td.appendChild(ownerbutton);
						
						tr.appendChild(td);
						table.appendChild(tr);
						v = 0;
					}
				}
				manager.appendChild(select);
				manager.appendChild(addButton);
				manager.appendChild(table);

				var kioskinfo = document.createElement('span');
				kioskinfo.id = "kioskinfo";
				kioskinfo.setAttribute('onload', 'getKiosk();');
				manager.appendChild(kioskinfo);
				fragment.appendChild(manager);

				var divider = document.createElement('div');
				divider.classList.add("divider");
				fragment.appendChild(divider);

				var reasonmanage = document.createElement('div');
				reasonmanage.id = 'reasonmanage';
				reasonmanage.innerHTML += "<b style='font-size: 30px;''>Manage room reasons</b><br>";
				
				var addreason = document.createElement('input');
				addreason.id = 'addreason';
				addreason.type = 'text';
				addreason.style="height: 35px;";
				addreason.placeholder="Insert reason";
				if(document.getElementById('addreason')!=null && lastShownRoomName==document.getElementById('manageroomname').value)
					addreason.value=document.getElementById('addreason').value;
				reasonmanage.appendChild(addreason);
				var button = document.createElement('button');
				button.appendChild(document.createTextNode("Add Reason In"));
				button.style="height: 35px; position: relative; top: -5px;";
				button.onclick = function() {
					document.getElementById("addreason").focus();
					addReasonIn();
				}
				reasonmanage.appendChild(button);
				button = document.createElement('button');
				button.appendChild(document.createTextNode("Add Reason Out"));
				button.style="height: 35px; position: relative; top: -5px;";
				button.onclick = function() {
					document.getElementById("addreason").focus();
					addReasonOut();
				}
				reasonmanage.appendChild(button);

				var reasontable = document.createElement('table');
				reasontable.id = "manageroomreason";
				reasontable.style = "position: relative;";
				var trr = document.createElement('tr');
				var trh = document.createElement('tr');
				var thi = document.createElement('th');
				var tho = document.createElement('th');
				var tdi = document.createElement('td');
				var tdo = document.createElement('td');
				thi.innerHTML = "Reasons in";
				trh.appendChild(thi);
				tho.innerHTML = "Reasons out";
				trh.appendChild(tho);
				reasontable.appendChild(trh);
				trr.id = "manageroomtables";
				tdi.id = "manageroomreasonin";
				trr.appendChild(tdi);
				tdo.id = "manageroomreasonout";
				trr.appendChild(tdo);
				reasontable.appendChild(trr);
				reasonmanage.appendChild(reasontable);
				fragment.appendChild(reasonmanage);
				getReasonsIn();
				getReasonsOut();

				return fragment;
			}
		</script>
	</head>
	<?php printTopBar('Room Management');?>
	<body id="page" class="content" onload="updateAvailableRooms();">
		<div id="manageroom" class="manage" style="z-index: 5">
			<b style="font-size: 30px;">Manage room</b><br>
			<select id="manageroomname" style="min-width: 10%;"></select>
			<button onclick="getRoom()" style="height: 30px;">Manage Room</button>
			<br>
			<div id="manage"></div>
		</div>
	</body>
</html>