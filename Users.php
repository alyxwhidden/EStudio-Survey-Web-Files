<?php

//Start php session
session_start();

$redirect = false;
if(!isset($_SERVER['HTTPS']))
{
  $redirect = true;
}

if($_SERVER['HTTPS'] !== "on")
{
  $redirect = true;
}

if($redirect)
{
  if(!(isset($_SESSION['EStudioRedirects'])))
  {
    $_SESSION['EStudioRedirects'] = 1;
    header("Location: https://www.cs.uky.edu/~anwh223/estudio/Users.php");
    die();
  }
  else if($_SESSION['EStudioRedirects'] > 0)
  {
    $_SESSION['EStudioRedirects'] = 0;
    header("Location: http://www.cs.uky.edu/~anwh223/estudio/404.html");
    die();
  }
  else
  {
    $_SESSION['EStudioRedirects'] = 1;
    header("Location: https://www.cs.uky.edu/~anwh223/estudio/Users.php");
    die();
  }
}
else
{
  if(isset($_SESSION['EStudioRedirects']))
  {
    $_SESSION['EStudioRedirects'] = 0;
  }
}


if(!(isset($_SESSION['EStudioLoggedIn'])))
{
  header("Location: https://www.cs.uky.edu/~anwh223/estudio/Login.php");
  die();
}
else
{
  if($_SESSION['EStudioLoggedIn'] !== TRUE)
  {
    header("Location: https://www.cs.uky.edu/~anwh223/estudio/Login.php");
    die();
  }
  else
  {
    if(isset($_SESSION['EStudioUsertype']) && isset($_SESSION['EStudioUsername']))
    {
      $usertype=$_SESSION['EStudioUsertype'];
      $username=$_SESSION['EStudioUsername'];

      //Get db information
      $dbinfo = include('config.php');

      //Connect to the database
      $con = mysqli_connect($dbinfo[host],$dbinfo[username],$dbinfo[password],$dbinfo[dbname]);
      if (!$con) {
        die('Could not connect: ' . mysqli_error($con));
      }

      mysqli_select_db($con,$dbinfo[dbname]);

      $userResult = mysqli_query($con, "SELECT * FROM users WHERE username='" . $username . "'");
      if($user = mysqli_fetch_array($userResult))
      {
        $usertype = $user['usertype'];
      }
      else
      {
        header("Location: https://www.cs.uky.edu/~anwh223/estudio/Login.php");
        mysqli_close($con);
        die();
      }
      if($usertype != 0 && $usertype != 1 && $usertype != 2)
      {
        header("Location: https://www.cs.uky.edu/~anwh223/estudio/Login.php");
        mysqli_close($con);
        die();
      }
    }
    else
    {
        header("Location: https://www.cs.uky.edu/~anwh223/estudio/Login.php");
        die();
    }
  }
}
?>

<?php
function isMobile() {
    return preg_match("/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i", $_SERVER["HTTP_USER_AGENT"]);
}
echo "<html>\n";
?>
<head>

<?php
if(isMobile())
echo "<meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">\n";

?>
<title>EStudio User Management</title>

<link rel="stylesheet" href="//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css">
  <script src="//code.jquery.com/jquery-1.10.2.js"></script>
  <script src="//code.jquery.com/ui/1.11.4/jquery-ui.js"></script>
  <script>

  $(function() {
    $( "#accordion" ).accordion({
      active: false,
      collapsible: true,
      heightStyle: "content"
    });
  });

<?php


/*

$result = mysqli_query($con,"SELECT * FROM users order by username");
for($i = 0; $i < mysqli_num_rows($result); $i++)
{

echo "$(function() {\n";

echo "    $( \"#from" . $i . "\" ).datepicker({\n";
echo "      changeMonth: true,\n";
echo "      changeYear: true,\n";
echo "      numberOfMonths: 1,\n";
echo "      onClose: function( selectedDate ) {\n";
echo "        $( \"#to" . $i . "\" ).datepicker( \"option\", \"minDate\", selectedDate );\n";
echo "        startMonth = $( \"#from" . $i . "\" ).datepicker( \"getDate\" ).getMonth();\n";
echo "        startDay = $( \"#from" . $i . "\" ).datepicker( \"getDate\" ).getDate();\n";
echo "        startYear = $( \"#from" . $i . "\" ).datepicker( \"getDate\" ).getFullYear();\n";
echo "      }\n";
echo "    });\n";

echo "    $( \"#to" . $i . "\" ).datepicker({\n";
echo "      changeMonth: true,\n";
echo "      changeYear: true,\n";
echo "      numberOfMonths: 1,\n";
echo "      onClose: function( selectedDate ) {\n";
echo "        $( \"#from" . $i . "\" ).datepicker( \"option\", \"maxDate\", selectedDate );\n";
echo "        endMonth = $( \"#to" . $i . "\" ).datepicker( \"getDate\" ).getMonth();\n";
echo "        endDay = $( \"#to" . $i . "\" ).datepicker( \"getDate\" ).getDate();\n";
echo "        endYear = $( \"#to" . $i . "\" ).datepicker( \"getDate\" ).getFullYear();\n";
echo "      }\n";
echo "    });\n";
echo "  });\n";
}
*/
?>

function updateUser(username, modification, id) {
	id = "u"+id;
	var value = document.getElementById(id).value;
	if(value == "SuperAdmin")
	{
		value = 0;
	}
	else if(value == "Admin")
	{
		value = 1;
	}
	else if(value == "Staff")
	{
		value = 2;
	}
        window.location = "updateUser.php?u="+username+"&m="+modification+"&v="+value;
}

function deleteUser(username) {
	if (confirm("Are you sure you want to delete this user?") == true)
        {
	        window.location = "updateUser.php?u="+username+"&m=1";
	}
}

function toggleAlerts(username) {
	        window.location = "updateUser.php?u="+username+"&m=2";
}


function invite() {
  var email = encodeURIComponent(document.getElementById("email").value);
  value = document.getElementById("newUserLevel").value;
        if(value == "SuperAdmin")
        {
                value = 0;
        }
        else if(value == "Admin")
        {
                value = 1;
        }
        else if(value == "Staff")
        {
                value = 2;
        }

  if (window.XMLHttpRequest) 
  {
    // code for IE7+, Firefox, Chrome, Opera, Safari
    xmlhttp=new XMLHttpRequest();
  } 
  else
  { // code for IE6, IE5
    xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
  }
  xmlhttp.onreadystatechange=function() {
  if (xmlhttp.readyState==4 && xmlhttp.status==200)
  {
    if(xmlhttp.responseText == "0")
    {
      alert("An invitation to register has been sent to "+document.getElementById("email").value);
      window.location.href="https://www.cs.uky.edu/~anwh223/estudio/Users.php";
    }
    else
    {
      document.getElementById("txtHint").innerHTML = xmlhttp.responseText;
    }
  }
  };
  xmlhttp.open("GET","requestRegistration.php?e="+email+"&v="+value,true);
  xmlhttp.send();
}

  </script>


<link rel="stylesheet" href="foundation.css" />

</head>
<body>
<h1 align="center">EStudio User Management</h1>
<div style="text-align: right">
<button onclick="location.href='https://www.cs.uky.edu/~anwh223/estudio/ControlPanel.php';">Control Panel</button>
<button onclick="location.href='logout.html';">Logout</button>
</div>
<br>

<!-- <a href='logout.html'> -->
<!-- <h2 align='right'>Logout</h2> -->
</a>

<br>

<fieldset >

<legend>Users</legend>
<div id="accordion">

<?php
$i = 0;
$result = mysqli_query($con,"select * from users order by username");
while($row = mysqli_fetch_array($result))
{
$tempusername=$row['username'];
$tempusertype=$row['usertype'];
$tempemail=$row['email'];
$tempalert=$row['alert'];
echo "<h3>" . $tempusername . " (" . $tempemail . ")</h3>\n";
echo "<div>\n";
echo "<br>\n";
if($usertype >= $tempusertype)
{
  if($username == $tempusername)
  {
    if($tempalert)
    {
      echo "<button onclick=\"toggleAlerts('" . $tempusername . "')\">Disable Email Alerts</button><br>";
    }
    else
    {
      echo "<button onclick=\"toggleAlerts('" . $tempusername . "')\">Enable Email Alerts</button><br>";
    }
    echo "<button onclick=\"deleteUser('" . $tempusername . "')\">Delete Your Account</button><br>";
  }
  else
  {
    echo "<br>Cannot Modify This User<br>";
  }
}
else
{
  echo "<select name=\"u" . $i . "\" id=\"u" . $i . "\">\n";
  if($usertype == 0)
  {
    echo "\t<option>SuperAdmin</option>\n";
  }
  if($tempusertype == 1)
  {
    echo "\t<option selected=\"selected\">Admin</option>\n";
  }
  else
  {
    echo "\t<option>Admin</option>\n";
  }
  if($tempusertype == 2)
  {
    echo "\t<option selected=\"selected\">Staff</option>\n";
  }
  else
  {
    echo "\t<option>Staff</option>\n";
  }
  echo "</select>\n";
  echo "<button onclick=\"updateUser('" . $tempusername . "',0," . $i . ")\">Change User Level</button><br>";
  echo "<button onclick=\"deleteUser('" . $tempusername . "')\">Delete User</button><br>";
}


echo "</div>\n";

$i++;
}

echo "<h3>Add User</h3>\n";
echo "<div>\n";
echo "<br>\n";
?>

<label for='email' >Send Invite to Email Address:</label><br>
<input type='text' name='email' id='email' maxlength="100" /><label id="emailHint"></label><br>
<?php

echo "<label for='newUserLevel' >Set New User Level:</label><br>";
echo "<select name=\"newUserLevel\" id=\"newUserLevel\">\n";
if($usertype == 0)
{
  echo "\t<option selected=\"selected\">SuperAdmin</option>\n";
  echo "\t<option>Admin</option>\n";
  echo "\t<option>Staff</option>\n";
}
elseif($usertype == 1)
{
  echo "\t<option selected=\"selected\">Admin</option>\n";
  echo "\t<option>Staff</option>\n";
}
elseif($usertype == 2)
{
  echo "\t<option selected=\"selected\">Staff</option>\n";
}
echo "</select>\n";
echo "<button onclick=\"invite()\">Invite to Register</button><br>";

mysqli_close($con);

?>
<!-- txtHint is used for error messages returned by survey.php -->
<div id="txtHint"><b></b></div>
</div>
<br>
</fieldset>


<footer style="margin: 0 auto; text-align:center;"></footer>
</body>
</html>
