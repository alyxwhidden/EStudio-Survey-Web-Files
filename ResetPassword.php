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
    header("Location: https://www.cs.uky.edu/~anwh223/estudio/ResetPassword.php");
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
    header("Location: https://www.cs.uky.edu/~anwh223/estudio/ResetPassword.php");
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

<script>
function reset() {
  username = encodeURIComponent(document.getElementById("username").value);
  email = encodeURIComponent(document.getElementById("email").value);
  if (window.XMLHttpRequest) {
    // code for IE7+, Firefox, Chrome, Opera, Safari
    xmlhttp=new XMLHttpRequest();
  } else { // code for IE6, IE5
    xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
  }
  xmlhttp.onreadystatechange=function() {
    if (xmlhttp.readyState==4 && xmlhttp.status==200) {
      if(xmlhttp.responseText == "1")
      {
	alert('Your password reset link has been sent to your email on file.');
        window.location.href="https://www.cs.uky.edu/~anwh223/estudio/Login.php";
      }
      else
      {
        alert(xmlhttp.responseText);
      }
    }
  }
  xmlhttp.open("GET","requestReset.php?un="+username+"&email="+email,true);
  xmlhttp.send();
}


</script>
<link rel="stylesheet" href="foundation.css" />
</head>
<body>
<br>
<fieldset >
<legend>Reset Password</legend>
<label for='username' >Username:</label><br>
<input type='text' name='username' id='username' maxlength="20" /><label id="userHint"></label><br>
<label for='email' >Email Address:</label><br>
<input type='text' name='email' id='email' maxlength="100" /><label id="emailHint"></label><br>
<div id="txtHint"><b></b></div><br>
<button onclick='reset()'>Submit</button>
 
</fieldset>



</body>
</html>
