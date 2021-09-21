<?php
include(__DIR__.'/common.php');
?>
<!DOCTYPE HTML>
<html>
	<head>
	<link href="custom.css" rel="stylesheet" type="text/css">
	<script type="text/javascript" src="jquery.js"></script>
	<script>
		var was = 0;
		var selected = 0;
		var reasonlist = [''];

		$(document).ready(function()
		{
			//Button actions
			$("#inbutton").click(function() //If in button is clicked
			{
				if(selected < 0) //If signout is selected
				{
					$("#reasonselect").slideUp(100).slideDown(100);
					$("#submit").slideUp("fast");
					selected = 1;
				}
				else //If nothing was selected
				{
					selected = 1;
					$("#reasonselect").slideDown(100);
				}
					
				document.getElementById("condition").innerHTML = "Signing in";

				createTable(true);
			});
			$("#outbutton").click(function() //If out button is clicked
			{
				if(selected > 0) //If sign in was selcted
				{
					$("#reasonselect").slideUp(100).slideDown(100)
					$("#submit").slideUp("fast");
					selected = -1;
				}
				else //If nothing was selected
				{
					selected = -1;
					$("#reasonselect").slideDown(100);
				}
				document.getElementById("condition").innerHTML = "Signing out";
				createTable(false);
			});

			//ID action
			$("#studentid").keyup(function() //On id being entered
			{
				var id = document.getElementById('studentid').value;
				idValid(id);
			});

		});

		var currentItem = -1;
		var reasonSize = -1;
		var inout = 'signin';
		var currentReason = "No reason";

		function createTable(b)
		{
			// console.log("Create table started");
			var table = "";
			var reasonstr = "";
			inout = (b?'signin':'signout');
			table += "<table id=\"reasonlist\" class=\"cuslist\">\n\t<tr><th style='text-align: center;'>Reason " + (b ? "In" : "Out") + "</th></tr>\n";
			if(b == true)
			{
				reasonlist=<?php
				$pl = $_GET['place'];
				$inarr = json_encode(getReasonsIn($pl));
				echo $inarr;
				?>;
				document.getElementById("inbutton").classList.add('pagebuttonclick');
				document.getElementById("outbutton").classList.remove('pagebuttonclick');
				// console.log("Sign in reasons created");
			}
			else
			{
				reasonlist=<?php
				$pl = $_GET['place'];
				$outarr = json_encode(getReasonsOut($pl));
				echo $outarr;
				?>;
				document.getElementById("outbutton").classList.add('pagebuttonclick');
				document.getElementById("inbutton").classList.remove('pagebuttonclick');
				// console.log("Sign out reasons created");
			}
			
			for(var x = 0; x < reasonlist.length; x++)
			{
				table += "\t<tr><td class='reason' id='reason " + x + "' onclick=\"console.log('Created ' + this.id); resetOther(); selectItem(" + x + ");\">" + reasonlist[x].reason + "</td></tr>\n";
				reasonSize = x;
			}
			reasonSize++;
			table += "\t<tr><td class ='reason' id='Other' onclick='otherSelect();'>Other</td></tr>\n</table>";

			document.getElementById("reasonselect").innerHTML = table;
			// console.log('Created table');
		}

		function selectItem(pos)
		{
			var doc = (currentItem != reasonSize ? document.getElementById("reason " + currentItem) : document.getElementById("Other"));
			try //If no other options are present doc throughs NULL, this stops such action
			{
				if(currentItem != -1)
					doc.style.backgroundColor = "rgba(0, 0, 0, 0)";
			}catch(e){console.log(e);}
			//console.log("Reset previous selection bg color");
			currentItem = pos;
			document.getElementById("submit").visibility = "hidden";
			//Checks if it is other reason, if it is equal to reasonSize's length it is other
			if(pos != reasonSize)
			{
				doc = document.getElementById("reason " + pos);
				currentReason = document.getElementById("reason " + pos).innerHTML;
			}
			else
			{
				doc = document.getElementById("Other");
				currentReason = doc.value;
			}

			document.getElementById("reasontxt").innerHTML = "Reason: " + currentReason;
			doc.style.backgroundColor = "rgba(130, 130, 130, 1)";
			// console.log("Selection bg color changed");
			$("#submit").slideDown("fast");
		}

		function resetOther()
		{
			// console.log("Reset other started");
			document.getElementById("Other").innerHTML = "Other";
			document.getElementById("Other").onclick = otherSelect;
			// console.log("Reset other finished");
		}

		function otherSelect()
		{
			// console.log("Other select start")
			selectItem(reasonSize);
			var reason = "";

			document.getElementById("Other").innerHTML = "<div><input type='text' name='studentio' id='OtherInput' placeholder='Other reason' style='text-align: center;' class='txtinput' autocomplete='off' onkeypress='if(event.keyCode==13){currentReason=this.value;return false;}' oninput=\"currentReason=this.value; document.getElementById('reasontxt').innerHTML=currentReason;\"></input></div>";
			document.getElementById("OtherInput").focus();
			// console.log("Other form created");

			document.getElementById("Other").onclick = null;
			// console.log("Other select finished");
		}

		var type_input, id_input, reason_input;
		function updateValues()
		{
			var form_inputs = document.forms["form_inputs"];
			type_input = "" + inout;
			id_input = form_inputs.elements["id_input"].value;
			reason_input = "" + currentReason;
		}
		function signInOut()
		{
			updateValues();
			idChange();
			document.getElementById('studentid').value = '';
			document.getElementById('studentid').focus();
		    
	        if (window.XMLHttpRequest) {
	            // code for IE7+, Firefox, Chrome, Opera, Safari
	            xmlhttp = new XMLHttpRequest();
	        } else {
	            // code for IE6, IE5
	            xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
	        }
			var params = "studentid=" + id_input
						+ "&room=" + '<?php echo isset($_GET['place']) ? $_GET['place'] : ''; ?>';
			if(reason_input.length > 0) {
				params += "&reason=" + reason_input;
			}

			selected = 0;
	        xmlhttp.open("POST", "api/history/" + type_input + "student.php", true);
			xmlhttp.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
	        xmlhttp.send(params);

	        inout = "sign";
	        currentReason = "No reason";
	        selected = 0;
	        document.getElementById("studentname").innerHTML = "First Middle Last";
	        document.getElementById("condition").innerHTML = "Sign in/out";
	        document.getElementById("reasontxt").innerHTML = "No reason";
		}

		function idChange()
		{
			$("#submit").slideUp("fast");
			$("#reasonselect").slideUp("fast");
			$("#inbutton").delay(100).slideUp("fast");
			$("#outbutton").delay(100).slideUp("fast");
			document.getElementById("inbutton").classList.remove('pagebuttonclick');
			document.getElementById("outbutton").classList.remove('pagebuttonclick');
			$(".studentinfo").animate({width:'0%'}, "fast");
			was = 0;
			selected = 0;
		}

		function idValid(id)
		{
			if (window.XMLHttpRequest) {
				// code for IE7+, Firefox, Chrome, Opera, Safari
				xmlhttp = new XMLHttpRequest();
			} else {
				// code for IE6, IE5
				xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
			}
			xmlhttp.onreadystatechange = function() {
				if (this.readyState == 4 && this.status == 200){
					var info = JSON.parse(this.responseText);

					//var studentid = document.getElementById("studentid").value;
					if(info != null)
					{
						$("#inbutton").slideDown("fast");
						$("#outbutton").slideDown("fast");
						$(".studentinfo").animate({width:'29.5%'}, "fast");
						document.getElementById("studentname").innerHTML = info['FirstName'] + " " + info['LastName'];//put name here
						document.getElementById("studentimage").src = "api/students/getimage.php?image=" + id;
					}
					else
						idChange();
					was = document.getElementById("studentid").value.length;
				}
			};
			xmlhttp.open("GET","api/students/getinfo.php?studentid="+id, true);
			xmlhttp.send();
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
	<body class="content" id="page">
	<div class="banner" id="banner">
		<img id="logo" src="wiss.png">
		<span class="pagetitle">
			<h1><b>Student Sign in/out - Room: </b><b id='temp'><?php echo $_GET['place']; ?></b></h1>
		</span>
		<title>Student Sign in/out</title>
	</div>
		<form name="form_inputs" onSubmit="signInOut(); return false;">
			<div id="identer">
				<input type="text" name="id_input" id="studentid" class="txtinput" placeholder="Enter username" autocomplete="off" onkeypress="if(event.keyCode==13){return false;}"  autofocus>
			</div>
			<div id="studentio">
				<div id="signio">
					<input type="button" class="pagebutton" id="inbutton" style="display: none;" name="studentio" value="Sign in">
					<input type="button" class="pagebutton" id="outbutton" style="display: none;" name="studentio" value="Sign out">
				</div><br>
				<div id="reasonselect" style="display: none;"></div><br>
				<input type="submit" value="Submit" class="pagebutton" id="submit">
			</div>
			<div class="studentinfo">
				<img id="studentimage" onerror="this.src='icon/noimage.jpg'">
				<p id="studentname">First Middle Last</p>
				<p id="condition">Signing in/out</p>
				<p id="reasontxt">No Reason</p>
			</div>
		</form>
	</body>
</html>