<?php
include(__DIR__.'/common.php');
?>
<!DOCTYPE html>
<html>
	<head>
	<title>Your Home Page</title>
	<link href="custom.css" rel="stylesheet" type="text/css">
	<link href="style.css" rel="stylesheet" type="text/css">
	<script type="text/javascript" src="jQuery.js"></script>
	<script type="text/javascript" src="jquery.tablesorter.js"></script>
	<script>
		var updateTimeout;
		var lastUpdateTime;
		var autoUpdateDelay = 5000;
		var lastStudent = new LastStudent("h", 999999, new Date('2000-01-01T01:00:00'));
		var currentRoom = "";
		var tableSelect = [[2,1]];
		var prevTable = [];

		function LastStudent(name, id, date)
		{
			this.Name = name;
			this.ID = id;
			this.Time = date;
		}

		function autoUpdate() {
			if(!document.getElementById('updateCheckbox').checked) {
				clearTimeout(updateTimeout);
				return;
			}
			
			updateTimeout = setTimeout(updateSignedInUsers, lastUpdateTime + autoUpdateDelay - Date.now());
		}
		function updateSignedInUsers(room)
		{
			if(room != undefined)
			{
				currentRoom = room;
				document.getElementById("roomselect").innerHTML = (room == '' ? 'Any room': room);
			}

			var placename = currentRoom;

			lastUpdateTime = Date.now();
			clearTimeout(updateTimeout);

			if (window.XMLHttpRequest) {
				// code for IE7+, Firefox, Chrome, Opera, Safari
				xmlhttp = new XMLHttpRequest();
			} else {
				// code for IE6, IE5
				xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
			}
			xmlhttp.onreadystatechange = function() {
				if (this.readyState == 4 && this.status == 200) {
					var jsontable = this.responseText;
					if(jsontable != prevTable) //only update on change
					{
						document.getElementById("txtHint").innerHTML = generateUserTable(jsontable);
						prevTable = jsontable;
					}
				}
			};
			xmlhttp.open("GET","api/history/getsignedinstudents.php?room="+placename, true);
			xmlhttp.send();
			
			autoUpdate();
		}
		function generateUserTable(json){
			var history = JSON.parse(json);
			var info = ["", "", "", ""];
			var temp = "";
			var v = 0;

			if (!history.length) //No students found
				return "<b>No students are currently signed in.</b>"

			var table = "<table id='historytable' class='tablesorter'><thead><tr><th id='idh'>ID</th><th id='roomh'>Room</th><th id='tih'>Time in</th><th id='rih'>Reason in</th></tr></thead><tbody>";
			for (var i = 0; i < history.length; i++) {
				var record = history[i];
				for (var column in record) {
					info[v] = "" + record[column];
					temp += "<td>" + info[v] + "</td>";
					v++;
				}
				table += "<tr onclick=\"clickshow('"+info[0]+"');\">" + temp + "</tr>";
				temp = "";
				v=0;
			}
			var dc = compareDates(lastStudent.Time, info[2]);
			if(dc == 1)
			{
				lastStudent = new LastStudent(dc, info[0], info[2]);
				clickshow(info[0]);
			}
			else if(dc == -1)
			{
				clickshow(dc, lastStudent.ID, lastStudent.Time, history.length-1);
				lastStudent = new LastStudent(dc, info[0], info[2]);
			}
			table += "</tbody></table>";
			var ids = ['idh', 'roomh', 'tih', 'rih'];
			tableSelect = [];
			for(var i = 0; i < ids.length; i++)
			{
				try
				{
					var doc = document.getElementById(ids[i]).className;
					console.log(ids[i]+":"+doc)
					if(doc=="header headerSortDown")
						tableSelect[tableSelect.length]=[i,0];
					else if(doc=="header headerSortUp")
						tableSelect[tableSelect.length]=[i,1];
				}catch(err){}
			}
			$(document).ready(function(){$("#historytable").tablesorter({sortList: tableSelect});});

			return table;
		}

		function clickshow(id, place, reason)
		{
			if($("#studentinfotext").css('width')=='0px')
				$("#studentinfotext").css('width', '33%');
			$("#studentinfotext").hide("fast", function()
			{
				$("#studentinfotext").show("fast");
				document.getElementById("studentid").innerHTML = "ID: " + id;
				getStudentInfo(id);
				document.getElementById("studentimage").src = "api/students/getimage.php?image=" + id;
			});
		}

		function compareDates(date1, date2)
		{
			var d1 = new Date(date1);
			var d2 = new Date(date2);
			if(d1.toString() == d2.toString()) //equal
				return 0;
			else if(d1 < d2) //newer
				return 1;
			else //older
				return -1;
			return 0;
		}

		function getStudentInfo(id)
		{
			if (window.XMLHttpRequest) {
				// code for IE7+, Firefox, Chrome, Opera, Safari
				xmlhttp = new XMLHttpRequest();
			} else {
				// code for IE6, IE5
				xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
			}
			xmlhttp.onreadystatechange = function() {
				if (this.readyState == 4 && this.status == 200) {
					var info = JSON.parse(this.responseText);
					
					document.getElementById("studentname").innerHTML = "Name: " + info['FirstName'] + " " + info['LastName'];
					document.getElementById("studentgrade").innerHTML = "Grade: " + info['GradeLevel'];
				}
			};
			xmlhttp.open("GET","api/students/getinfo.php?studentid="+id, true);
			xmlhttp.send();
			document.getElementById("studentname").innerHTML = "No name";
			document.getElementById("studentgrade").innerHTML = "No grade";
		}
	</script>
	</head>
	<?php printTopBar("Dashboard"); ?>
	<body id="page" class="content" onload="updateSignedInUsers();">
		<?php if($permission_level == 0) { ?>
			<div class="menu" style="left: 0;">
				<span class="cusdrop">
					<b>Sign in/out pages</b>
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
			</div>
		<?php } else { ?>
			<span class="cusdrop" style="min-width: 150px; font-size: 16px; background-color: rgb(255, 217, 96);">
				<b id="roomselect">Any room</b>
				<div class="cusdroptext">
					<a class='cusbutton' onclick='updateSignedInUsers("");'>Any room</a>
					<?php
						$arr = getRoomsAvailableToCurrentUser();
						foreach($arr as $key=>$value)
						{
							echo "<a class='cusbutton' onclick='updateSignedInUsers(\"{$value['placename']}\");'>{$value['placename']}</a>";
						}
					?>
				</div>
			</span>
			<button onclick="updateSignedInUsers();" class="pagebutton">Update list</button>
	        <input type="checkbox" id="updateCheckbox" onclick="autoUpdate();" class="cusbutton">Auto update</input>
	        <br>
	        Signed in users:
			<div id="historyinfo">
	            <div id="txtHint">
	            </div>
	            <div id="studentinfo">
	            	<span id="studentinfotext">
	            		<img id="studentimage" src="noimage.jpg" onerror="this.src='icon/noimage.jpg'">
	            		<p id="studentid">ID</p>
	            		<p id="studentname">Name</p>
	            		<p id="studentgrade">Grade</p>
	            	</span>
	          	</div>
	        </div>
	    <?php } ?>
	</body>
</html>