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
    header("Location: https://www.cs.uky.edu/~anwh223/estudio/Create.php");
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
    header("Location: https://www.cs.uky.edu/~anwh223/estudio/Create.php");
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
        mysqli_close($con);
      }
      else
      {
        //Redirect to the login page
        header("Location: https://www.cs.uky.edu/~anwh223/estudio/Login.php");
        mysqli_close($con);
        die();
      }

      //If usertype should not have access to this page
      if($usertype != 0 && $usertype != 1 && $usertype != 2)
      {
        //Redirect to the login page
        header("Location: https://www.cs.uky.edu/~anwh223/estudio/Login.php");
        mysqli_close($con);
        die();
      }
    }
    else //username and usertype are not set
    {
        //Redirect to the login page
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

//Start generating the html code
echo "<html>\n";
?>
<head>

<?php
if(isMobile())
echo "<meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">\n";

?>
<title>EStudio Survey Creator</title>

<link rel="stylesheet" href="//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css">
  <script src="//code.jquery.com/jquery-1.10.2.js"></script>
  <script src="//code.jquery.com/ui/1.11.4/jquery-ui.js"></script>
  <script>

  

  var questionCount = 1;

function submit()
{
	var url = "addSurvey.php?";
        var question = "";
	for(i = 1; i <= questionCount; i++)
	{
		question = document.getElementById("q"+i).value;
		question = encodeURIComponent(question);
		url += "q"+i+"="+question+"&";
	}
        var surveyName = encodeURIComponent(document.getElementById("name").value);
	url += "c="+questionCount+"&n="+surveyName;

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
        alert('Your survey has been successfully added.');
        window.location.href="https://www.cs.uky.edu/~anwh223/estudio/ControlPanel.php";
      }
      else if(xmlhttp.responseText == "2")
      {
        alert('You are no longer logged in.');
        window.location.href="https://www.cs.uky.edu/~anwh223/estudio/Login.php";
      }
      else
      {
        alert(xmlhttp.responseText);
      }
    }
  }
  xmlhttp.open("GET",url,true);
  xmlhttp.send();

}

function addQuestion() 
{
  if(questionCount != 50)
  {
  questionCount++;
  var questionCountString = "q" + questionCount;
  var label = document.createElement("label");
  label.htmlFor = questionCountString;
  label.innerHTML = "Question " + questionCount;

  var input = document.createElement("input");
  input.type = "text";
  input.id = questionCountString;
  input.name = questionCountString;
  document.getElementById("questions").appendChild(label);
  document.getElementById("questions").appendChild(input);
  }
  else
  {
    alert('Cannot have more than 50 questions.');
  }
}

  </script>


<link rel="stylesheet" href="foundation.css" />

</head>
<body>
<h1 align="center">EStudio Survey Creator</h1>
<div style="text-align: right">
<button onclick="location.href='https://www.cs.uky.edu/~anwh223/estudio/ControlPanel.php';">Control Panel</button>
<button onclick="location.href='logout.html';">Logout</button>
</div>

<br>

<fieldset >

<legend>Create New Survey</legend>
<div>

<!-- set max length and such later for all inputs -->
<label for="name" >Survey Name</label><br>
<input type="text" id="name" name="name"><br>

<div id="questions">

<!-- set max length and such later for all inputs -->
<label for="q1" >Question 1</label>
<input type="text" id="q1" name="q1">

</div>
<button onclick='addQuestion()'>Add A Question</button><br>

</div><br>
<button onclick='submit()'>Add Survey</button><br>
<!-- txtHint is used for error messages returned by survey.php -->
<div id="txtHint"><b></b></div>
</fieldset>


<footer style="margin: 0 auto; text-align:center;"></footer>
</body>
</html>
