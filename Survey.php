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
    header("Location: https://www.cs.uky.edu/~anwh223/estudio/Survey.php");
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
    header("Location: https://www.cs.uky.edu/~anwh223/estudio/Survey.php");
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

//Get db information
$dbinfo = include('config.php');

//Connect to the database
$con = mysqli_connect($dbinfo[host],$dbinfo[username],$dbinfo[password],$dbinfo[dbname]);
if (!$con) {
  die('Could not connect: ' . mysqli_error($con));
}

mysqli_select_db($con,$dbinfo[dbname]);

//Check if there is an active survey in the database
$result = mysqli_query($con,"SELECT * FROM surveys WHERE active");
$surveyVersion;
$questions;

//If there is an active survey
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
  die('No active survey');
}
mysqli_close($con);

function isMobile() {
    return preg_match("/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i", $_SERVER["HTTP_USER_AGENT"]);
}

?>
<!DOCTYPE html>
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
  //This is how you retrieve the survey input to send to php/database

<?php

  //Create html for retrieving answers
  $htmlGetElementStart = "form_elements = document.getElementById(\"q";
  $htmlGetElementEnd = "\").elements;\n";
  for($i = 1; $i <= $questions; $i++)
  {
  echo $htmlGetElementStart . $i . $htmlGetElementEnd;
  echo "q" . $i . " = form_elements[\"q" . $i . "Answer\"].value;\n";
  }

?>

  comment = document.getElementById("comment").value;
  comment = encodeURIComponent(comment);
 if (window.XMLHttpRequest) {
    // code for IE7+, Firefox, Chrome, Opera, Safari
    xmlhttp=new XMLHttpRequest();
  } else { // code for IE6, IE5
    xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
  }

  xmlhttp.onreadystatechange=function() {
    //Response received from php
    if (xmlhttp.readyState==4 && xmlhttp.status==200) {

      //1 is a success, otherwise the response text is placed under the submit button
      //On a 1 we just refresh the page
      if(xmlhttp.responseText == "1")
      {
	alert('Your survey has been successfully submitted.');
        window.location.href="https://www.cs.uky.edu/~anwh223/estudio/Survey.php";
      }
      else
      {
        alert(xmlhttp.responseText);
      }
    }
  }

  //Send get request to php file which puts the survey result in the database and returns 1
  //    or encounters an error and returns an error message

<?php
  //Generate the html code to send answers to the database
  echo "xmlhttp.open(\"GET\",\"submitSurvey.php?a=\"";
  for($i = 1; $i <= $questions; $i++)
  {
    echo "+q" . $i;
  }

  echo "+\"&comment=\"+comment+\"&v=" . $version . "\",true);\n";
?>

  xmlhttp.send();
}
</script>
</head>
<body>
<h1 align="center">EStudio Survey</h1>
<br>
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
