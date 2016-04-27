<?php

//Get the username and token from link clicked in email
$username = urldecode($_GET['u']);
$token = $_GET['t'];

//validated flag for later use
//entry field for new password will only display if validated
//validated means their username and token are in the resetTokens table
$validated = FALSE;

//Get db information
$dbinfo = include('config.php');

//Connect to the database
$con = mysqli_connect($dbinfo[host],$dbinfo[username],$dbinfo[password],$dbinfo[dbname]);
if (!$con) {
  die('Could not connect: ' . mysqli_error($con));
}

mysqli_select_db($con,$dbinfo[dbname]);

//Make sure values are safe to use
$username2 =  mysqli_real_escape_string($con,$username);
$token2 =  mysqli_real_escape_string($con,$token);

//Create string that will display any error found to the user later on
$error = "";

//If values are safe to use
if(($username == $username2 && $token == $token2) && ($username2 != "" && $token2 != ""))
{

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
    header("Location: https://www.cs.uky.edu/~anwh223/estudio/Reset.php?u=" . urlencode($username) . "&t=" . $token);
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
    header("Location: https://www.cs.uky.edu/~anwh223/estudio/Reset.php?u=" . urlencode($username) . "&t=" . $token);
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

  //Check that there is a resetToken with the provided username
  $result = mysqli_query($con,"SELECT * FROM resetTokens WHERE username='" . $username . "'");

  //If there is a resetToken with the provided username
  if($row = mysqli_fetch_array($result))
  {
    //Set the number of iterations for the hash
    $iterations = 1000;

    //Get the 16 byte salt ffrom the token in the database
    $salt = hex2bin(substr($row['token'], 0, 32));

    //Generate the hash using the salt and the provided token
    $hash = hash_pbkdf2("sha256", $token, $salt, $iterations, 64);

    //Create the key (salt+hash)
    $key = substr($row['token'], 0, 32) . $hash;

    //If the key matches token in the database
    if($key == $row['token'])
    {
      //Set validated flag to true
      $validated = TRUE;
    }
    else
    {
      $error = "Invalid username and/or token passed.";
    }
  }
  else
  {
    $error = "Invalid username and/or token passed.";
  }
}
else
{
  $error = "Invalid username and/or token passed.";
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
<title>EStudio Password Reset</title>
<?php
echo "<script>\n";

//Only handle initial error if there is one
if($error != "")
{
  echo "function handleInitialError(error) {\n";
  echo "\talert(error);\n";
  echo "\twindow.location.href = \"https://www.cs.uky.edu/~anwh223/estudio/Login.php\";\n";
  echo "}\n";
}

//Only create reset function if user is validated
if($validated)
{

echo "function reset() {\n";
echo "  password = encodeURIComponent(document.getElementById(\"password\").value);\n";
echo "  if (window.XMLHttpRequest) {\n";
echo "    // code for IE7+, Firefox, Chrome, Opera, Safari\n";
echo "    xmlhttp=new XMLHttpRequest();\n";
echo "  } else { // code for IE6, IE5\n";
echo "    xmlhttp=new ActiveXObject(\"Microsoft.XMLHTTP\");\n";
echo "  }\n";
echo "  xmlhttp.onreadystatechange=function() {\n";
echo "    if (xmlhttp.readyState==4 && xmlhttp.status==200) {\n";
echo "      if(xmlhttp.responseText == \"0\")\n";
echo "      {\n";
echo "	alert('Your password has been successfully changed.');\n";
echo "        window.location.href = \"https://www.cs.uky.edu/~anwh223/estudio/Login.php\";\n";
echo "      }\n";
echo "      else\n";
echo "      {\n";
echo "        alert(xmlhttp.responseText);\n";
echo "      }\n";
echo "    }\n";
echo "  }\n";
echo "  xmlhttp.open(\"GET\",\"reset.php?u=" . urlencode($username) . "&p=\"+password+\"&t=" . $token . "\",true);\n";
echo "  xmlhttp.send();\n";
echo "}\n";

}
echo "</script>\n";

echo "</head>\n";

if($error == "")
	echo "<body>\n";
else
	echo "<body onload=\"handleInitialError('" . $error . "')\">\n";


?>

<br>
<fieldset >
<?php

//Only display input fields and submit button if validated
if($validated)
{

echo "<legend>Enter New Password</legend>\n";

echo "<label for='password' >Password:</label>\n";
echo "<input type='password' name='password' id='password' maxlength=\"50\" />\n";
echo "<button onclick='reset()'>Submit New Password</button><br>\n";
echo "<br>\n";

}

//txtHint no longer needed
echo "<div id=\"txtHint\"><b></b></div>";
?>

</fieldset>
<br>
</body>
</html>
