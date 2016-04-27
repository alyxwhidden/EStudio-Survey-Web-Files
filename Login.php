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
    header("Location: https://www.cs.uky.edu/~anwh223/estudio/Login.php");
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
    header("Location: https://www.cs.uky.edu/~anwh223/estudio/Login.php");
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


//If logged in flag is set
if(isset($_SESSION['EStudioLoggedIn']))
{
  if($_SESSION['EStudioLoggedIn'])
  {
  //Redirect to the Control Panel page
  header("Location: https://www.cs.uky.edu/~anwh223/estudio/ControlPanel.php");
  die();
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
<link rel="stylesheet" href="foundation.css" />
<title>EStudio Survey Login</title>

<script>
function login() {
  username = document.getElementById("username").value;
  password = document.getElementById("password").value;
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
        window.location.href = "https://www.cs.uky.edu/~anwh223/estudio/ControlPanel.php";
      }
      else
      {
        alert(xmlhttp.responseText);
      }
    }
  }
  xmlhttp.open("GET","login.php?un="+encodeURIComponent(username)+"&pw="+encodeURIComponent(password),true);
  xmlhttp.send();
}
</script>
</head>
<body>
<br>
<fieldset >
<legend>Login</legend>
<label for='username' >Username:</label>
<input type='text' name='username' id='username' maxlength="50" />
<label for='password' >Password:</label>
<input type='password' name='password' id='password' maxlength="50" />
<button onclick='login()'>Login</button><br>
Forgot your password? Click <a href='https://www.cs.uky.edu/~anwh223/estudio/ResetPassword.php'>here</a> to reset it!
<br>
</fieldset>
<br>

<div id="txtHint"><b></b></div>

</body>
</html>
