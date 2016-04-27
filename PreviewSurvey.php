<?php

//Start php session
session_start();




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

     //Get survey version
     $requestedVersion = $_GET['v'];

     //Make value safe for use
     $requestedVersion2 = mysqli_real_escape_string($con,$requestedVersion);
     if($requestedVersion2 != $requestedVersion || $requestedVersion == "")
     {
	//Redirect to control panel
        header("Location: https://www.cs.uky.edu/~anwh223/estudio/ControlPanel.php");
        mysqli_close($con);
        die();
     }

     if(!is_numeric($requestedVersion))
     {
	//Redirect to control panel
        header("Location: https://www.cs.uky.edu/~anwh223/estudio/ControlPanel.php");
        mysqli_close($con);
        die();
     }

     $requestedVersion = intval($requestedVersion);


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
          header("Location: https://www.cs.uky.edu/~anwh223/estudio/PreviewSurvey.php?v=" . $requestedVersion);
          mysqli_close($con);
          die();
        }
        else if($_SESSION['EStudioRedirects'] > 0)
        {
          $_SESSION['EStudioRedirects'] = 0;
          header("Location: http://www.cs.uky.edu/~anwh223/estudio/404.html");
          mysqli_close($con);
          die();
        }
        else
        {
          $_SESSION['EStudioRedirects'] = 1;
          header("Location: https://www.cs.uky.edu/~anwh223/estudio/PreviewSurvey.php?v=" . $requestedVersion);
          mysqli_close($con);
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


//Check if there is a survey in the database with that version
$result = mysqli_query($con,"SELECT * FROM surveys WHERE version=\"" . $requestedVersion . "\"");
$surveyVersion;
$questions;

//If there is a survey with that version
if($row = mysqli_fetch_array($result))
{

//Get the survey's attributes
$name=$row['name'];
$active=$row['active'];
$timeInfo=$row['timeInfo'];
$version=$row['version'];
$surveyVersion = $version;
$questions=$row['questions'];

//Get the questions for the active survey
$questionQuery = "SELECT * FROM questions WHERE version=" . $surveyVersion . " order by id";
$results = mysqli_query($con, $questionQuery);

}
else
{
  die('No survey with that version');
}
mysqli_close($con);

function isMobile() {
    return preg_match("/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i", $_SERVER["HTTP_USER_AGENT"]);
}

?>

<html>
<head>

<?php
if(isMobile())
echo "<meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">\n";

?>
<link rel="stylesheet" href="foundation.css" />
<title>EStudio Survey</title>
<script>
function submit() {
	alert('You cannot submit this survey');
}
</script>
</head>
<body>
<br>
<div style="text-align: right">
<button onclick="location.href='https://www.cs.uky.edu/~anwh223/estudio/ControlPanel.php';">Control Panel</button>
<button onclick="location.href='logout.html';">Logout</button>
</div>
<fieldset >

<legend>Survey</legend>
<br>
Please rate the following from 1 to 5<br>
(1 = Terrible, 5 = Excellent)
<br>
<br>
<?php

//For each question
$i = 1;
while($row = mysqli_fetch_array($results))
{

//Get question
$question=$row['question'];
$id=$row['id'];

//Create html code for the question and the radio buttons to answer it
echo "<!-- Radio button input for question " . $i . " -->\n";
echo "<form id=\"q" . $i . "\">\n";
echo "Question " . $i . ": " . $question . "<br><br>\n";
for($j = 1; $j <= 5; $j++)
{
echo "        <input type=\"radio\" name=\"q" . $i . "Answer\" value=\"" . $j . "\"> " . $j . "&nbsp;&nbsp;&nbsp;&nbsp;\n";
}
echo "</form>\n";
$i++;
}

?>


<!-- Text input box for additional comments -->
<label for='comment' >Additional Comments:</label><br>
<textarea name='comment' id='comment' maxlength="1000" cols="100" rows="10">
</textarea>

<br>

<!-- Runs submit function defined in javascript above -->
<button onclick='submit()'>Submit</button>

</fieldset>

<!-- txtHint is used for error messages returned by survey.php -->
<div id="txtHint"><b></b></div>


</body>
</html>

<?php
die();
?>
