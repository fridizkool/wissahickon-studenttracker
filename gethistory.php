<?php
include(__DIR__.'/common.php');
?>
<!DOCTYPE html>
<html>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<head>
	<link href="custom.css" rel="stylesheet" type="text/css">
	<link href="style.css" rel="stylesheet" type="text/css">
	<script type="text/javascript" src="jQuery.js"></script>
	<script type="text/javascript" src="jquery.tablesorter.js"></script>
	<script>
		var inputUpdateDelay = 1000;
		var autoUpdateDelay = 10000;
		var updateTimeout;
		var lastUpdateTime;
		var changedSinceUpdate = true;
		var currentRoom = "";
		var tableSelect = [[2,1]];
		var prevTable = [];
		var lastInput = "";
		var lastKnownRecord = "0";
		
		$(document).ready(function(){ 
			$("#historytable").tablesorter(); 
		}); 

		function inputChanged(room)
		{
			var searchbox = document.getElementById("searchbox");
			if(room != undefined)
			{
				currentRoom = room;
				document.getElementById("roomselect").innerHTML = (room == '' ? 'Any room': room);
				searchbox.focus();
			}
			else if(searchbox.value == lastInput) //Don't force update if nothing changed
				return;
			changedSinceUpdate = true;
			lastInput = searchbox.value;
			lastKnownRecord = "0";
			removeAllRowsFromTable();
			updateUser();
		}
		
		function updateUser()
		{
			clearTimeout(updateTimeout);
			if(changedSinceUpdate)
				updateTimeout = setTimeout(showUser, lastUpdateTime + inputUpdateDelay - Date.now());//, searchbox);
			else
				updateTimeout = setTimeout(showUser, lastUpdateTime + autoUpdateDelay - Date.now());//, searchbox);
		}

		function showUser()
		{
			changedSinceUpdate = false;
			var studentid = document.getElementById('searchbox').value;
			var placename = currentRoom;
			
			lastUpdateTime = Date.now();
		    if (studentid == '') {
		        document.getElementById("txtHint").innerHTML = "<b>Type a Student ID in the textbox above to see their history. Use '*' to search for any student.</b>";
		    } else {
		        if (window.XMLHttpRequest) {
		            // code for IE7+, Firefox, Chrome, Opera, Safari
		            xmlhttp = new XMLHttpRequest();
		        } else {
		            // code for IE6, IE5
		            xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
		        }
		        xmlhttp.onreadystatechange = function(){
		            if (this.readyState == 4 && this.status == 200) {
						document.getElementById("txtHint").innerHTML = "";
						addRowsToTable(this.responseText);
		            }
		        };
		        xmlhttp.open("GET","api/history/getallhistory.php?studentid="+studentid+"&placename="+placename+"&oldesttime="+lastKnownRecord, true);
		        xmlhttp.send();
		    }
			updateUser();
		}
		function removeAllRowsFromTable()
		{
			var tablerows = document.getElementById('tablerows');
			while(tablerows.firstChild != null)
				tablerows.removeChild(tablerows.firstChild);
			$("#historytable").trigger("update");
		}
		function removeRowsFromTable(id, room, time_in)
		{
			var tablerows = document.getElementById('tablerows');
			for(var i = 0; i < tablerows.childNodes.length; i++)
			{
				var row = tablerows.childNodes[i];
				if(row.cells[0].innerHTML == id
				&& row.cells[1].innerHTML == room
				&& row.cells[2].innerHTML == time_in)
				{
					tablerows.removeChild(row);
				}
			}
			console.log("removed certain row " + id + room + time_in);
		}
		
		function addRowsToTable(json)
		{
			var history = JSON.parse(json);
			var fragment = document.createDocumentFragment();
			var info = ["", "", "", ""];
			var v = 0;
			for (var i = 0; i < history.length; i++)
			{
				var updated = false;
				var record = history[i];
				var row = document.createElement('tr');
				for (var column in record)
				{
					if(column == 'updated')
					{
						updated = true;
						continue;
					}
					//Create info on student for click
					info[v] = "" + record[column];
					var cell = document.createElement('td');
					cell.innerHTML = info[v];
					row.appendChild(cell);
					v++;
				}
				row.onclick = function()
				{
					clickshow(this.childNodes[0].innerHTML, this.childNodes[1].innerHTML, this.childNodes[3].innerHTML);
				}
				if(updated)
				{
					//find the old version of this row and remove it from the table
					removeRowsFromTable(info[0], info[1], info[2]);
				}
				fragment.appendChild(row);
				v = 0;
				var timestamp = info[2];
				if(timestamp != 'null' && timestamp > lastKnownRecord)
				{
					lastKnownRecord = timestamp;
				}
				timestamp = info[3];
				if(timestamp != 'null' && timestamp > lastKnownRecord)
				{
					lastKnownRecord = timestamp;
				}
				info = ["", "", "", ""];
			}
			var tablerows = document.getElementById('tablerows');
			tablerows.appendChild(fragment);
			$("#historytable").trigger("update");
			
			var sorting = $("#historytable")[2].config.sortList; //TODO: Fix re-sorting after update
            // sort on the first column 
            $("table").trigger("sorton",[sorting]); 
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
			if(d1.toString() == d2.toString())
				return 0;
			else if(d1 < d2)
				return 1;
			else
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
	<title>Student History</title>
	<?php printTopBar("History"); ?>
	<body class="content" id="page">
		<?php if($permission_level != 0) { ?>
			<div id="historyinfo" onload="updateUser()">
				<form id="studentsearch" onsubmit="return false">
					<input type="text" oninput="this.value = this.value.replace(/\*(?=.+$)|[^\da-zA-Z\*]/g, ''); inputChanged()" id="searchbox" placeholder="Enter an ID" autofocus>
					<span class="cusdrop" id="pagedrop" style="min-width: 150px; font-size: 22px; bottom: 2px; background-color: rgb(255, 217, 96);">
							<b id="roomselect">Any room</b>
							<div class="cusdroptext">
								<a class='cusbutton' onclick='inputChanged("");'>Any room</a>
								<?php
									$arr = getRoomsAvailableToCurrentUser();
									foreach($arr as $key=>$value)
									{
										echo "<a class='cusbutton' onclick='inputChanged(\"{$value['placename']}\");'>{$value['placename']}</a>";
									}
								?>
							</div>
						</span>
				</form>
				<br>
				<div id="txtHint">
					<b>Type a Student ID in the textbox above to see their history.</b>
				</div>
				<table id='historytable' class='tablesorter'>
					<thead style='background-color: rgba(242, 242, 242, .1);'>
						<tr><th class="{sorter: 'text'}">ID</th><th>Room</th><th>Time in</th><th>Time out</th><th>Reason in</th><th>Reason out</th></tr>
					</thead>
					<tbody id='tablerows'></tbody>
				</table>
				
				<div id="studentinfo">
	            	<span id="studentinfotext">
	            		<img id="studentimage" src="noimage.jpg" onerror="this.src='icon/noimage.jpg'">
	            		<p id="studentid">ID: </p>
	            		<p id="studentname">Name: </p>
	            		<p id="studentgrade">Grade: </p>
	            	</span>
	          	</div>
			</div>
		<?php } ?>
	<br>
	</body>
</html>