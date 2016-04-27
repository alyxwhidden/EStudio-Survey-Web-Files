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
    header("Location: https://www.cs.uky.edu/~anwh223/estudio/ControlPanel.php");
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
    header("Location: https://www.cs.uky.edu/~anwh223/estudio/ControlPanel.php");
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





//If logged in flag is not set
if(!(isset($_SESSION['EStudioLoggedIn'])))
{
  //Redirect to the login page
  header("Location: https://www.cs.uky.edu/~anwh223/estudio/Login.php");
  die();
}
else
{
  //If not logged in
  if($_SESSION['EStudioLoggedIn'] !== TRUE)
  {
    //Redirect to the login page
    header("Location: https://www.cs.uky.edu/~anwh223/estudio/Login.php");
    die();
  }
  else //Logged in
  {
    //If usertype and username are set
    if(isset($_SESSION['EStudioUsertype']) && isset($_SESSION['EStudioUsername']))
    {
      //Get username and usertype from session
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

      //Double check that the user is in the database
      $userResult = mysqli_query($con, "SELECT * FROM users WHERE username='" . $username . "'");

      //If there is a user in the database with that username
      if($user = mysqli_fetch_array($userResult))
      {
        //Update the usertype of the user to make sure their permissions are valid
        //  Usertypes can be modified so we need to make sure that if a user
        //  logs in as one usertype (Admin) and is changed to another (Staff),
        //  they have access to their new usertype and not their old usertype
	$usertype = $user['usertype'];
      }
      else
      {
	//Redirect to login page
        header("Location: https://www.cs.uky.edu/~anwh223/estudio/Login.php");
        mysqli_close($con);
        die();
      }

      //If usertype should not have access to this page
      if($usertype != 0 && $usertype != 1 && $usertype != 2)
      {
        header("Location: https://www.cs.uky.edu/~anwh223/estudio/Login.php");
        mysqli_close($con);
        die();
      }
    }
    else
    {
	//Redirect to login page
        header("Location: https://www.cs.uky.edu/~anwh223/estudio/Login.php");
        die();
    }
  }
}

function isMobile() {
    return preg_match("/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i", $_SERVER["HTTP_USER_AGENT"]);
}
?>


<?php
//Start the html code for the page
echo "<html>\n";
?>
<head>
<?php
if(isMobile())
echo "<meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">\n";

?>
<title>EStudio Survey Control Panel</title>

<link rel="stylesheet" href="//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css">
  <script src="//code.jquery.com/jquery-1.10.2.js"></script>
  <script src="//code.jquery.com/ui/1.11.4/jquery-ui.js"></script>
  <script>

  var startMonth = 0;
  var startDay = 0;
  var startYear = 0;
  var endMonth = 0;
  var endDay = 0;
  var endYear = 0;


  $(function() {
    $( "#accordion" ).accordion({
      active: false,
      collapsible: true,
      heightStyle: "content"
    });
  });

<?php

//Generate html to handle date pickers for each survey in the database
$result = mysqli_query($con,"SELECT * FROM surveys order by active DESC");
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

?>

function submit(version,allResults) {
  if(allResults == 0)
  {
  if(startMonth != 0 && startDay != 0 && startYear != 0 && endMonth != 0 && endDay != 0 && endYear != 0)
  {
        window.location = "getResults2.php?sm="+startMonth+"&sd="+startDay+"&sy="+startYear+"&em="+endMonth+"&ed="+endDay+"&ey="+endYear+"&v="+version+"&t=0";
  }
  else
  {
        alert("Must select dates.");
  }
  }
  else if(allResults == 1)
  {
        window.location = "getResults2.php?v="+version+"&t=1";
  }
}

function updateActive( version ) {

        window.location = "updateActive.php?v="+version;
  }

function deleteSurvey( version ) {
	if (confirm("Are you sure you want to delete this survey and all responses?") == true)
	{
        	window.location = "deleteSurvey.php?v="+version;
	}
  }

function previewSurvey( version ) {
       	window.location = "PreviewSurvey.php?v="+version;
  }


  </script>


<link rel="stylesheet" href="foundation.css" />

</head>
<body>
<h1 align="center">EStudio Survey Control Panel</h1>
<div style="text-align: right">
<button onclick="location.href='logout.html';">Logout</button>
</div>
<br>

<!-- <a href='logout.html'> -->
<!-- <h2 align='right'>Logout</h2> -->
</a>

<br>

<fieldset >

<legend>Manage Surveys/Retrieve Results</legend>
<div id="accordion">

<?php

//For each survey
$i = 0;
while($row = mysqli_fetch_array($result))
{

//Get the attributes of the survey
$name=$row['name'];
$active=$row['active'];
$timeInfo=$row['timeInfo'];
$version=$row['version'];
$questions=$row['questions'];

//Generate html code for the accordion
echo "<h3>" . $name . "</h3>\n";
echo "<div>\n";
echo "<br>\n";

//Add the input fields for the datepicker
echo "<label for=\"from" . $i . "\">From</label>\n";
echo "<input type=\"text\" id=\"from" . $i . "\" name=\"from" . $i . "\" readonly>\n";
echo "<label for=\"to" . $i . "\">To</label>\n";
echo "<input type=\"text\" id=\"to" . $i . "\" name=\"to" . $i . "\" readonly>\n";
echo "<!-- Runs submit function defined in javascript above -->\n";

//Add the button to get results in the date range
echo "<button onclick='submit(" . $version . ",0)'>Retrieve Results In Date Range</button><br>\n";

//Add the button to get results all results in the survey
echo "<button onclick='submit(" . $version . ",1)'>Retrieve All Results</button><br>\n";

//Add the button to delete the survey
echo "<button onclick='previewSurvey(" . $version . ")'>Preview Survey</button><br>\n";

//If the survey is the active survey
if($active == 1)
{
  //Add the button to deactivate the survey
  echo "<button onclick='updateActive(" . $version . ")'>Deactivate</button><br>\n";
}
else
{
  //Add the button to activate the survey
  echo "<button onclick='updateActive(" . $version . ")'>Set As Active Survey</button><br>\n";
}

//Add the button to delete the survey
echo "<button onclick='deleteSurvey(" . $version . ")'>Delete Survey</button><br>\n";

echo "</div>\n";

$i++;
}
mysqli_close($con);

?>
</div>
<br>
<button onclick="location.href='https://www.cs.uky.edu/~anwh223/estudio/Create.php';">Create New Survey</button>
</fieldset>
<br>

<fieldset>
<legend>Manage/Invite Users</legend>
<button onclick="location.href='https://www.cs.uky.edu/~anwh223/estudio/Users.php';">Manage Users</button>
<!-- txtHint is used for error messages returned by survey.php -->
<div id="txtHint"><b></b></div>
</fieldset>

<footer style="margin: 0 auto; text-align:center;"></footer>
</body>
</html>
