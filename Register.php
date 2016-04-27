<?php

//Get email and token from link clicked in email
$email = urldecode($_GET['e']);
$token = $_GET['t'];

//validated flag for later use
//entry fields for username and password will only display if validated
//validated means their email and token are in the accountTokens table
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
$email2 =  mysqli_real_escape_string($con,$email);
$token2 =  mysqli_real_escape_string($con,$token);

//Create string that will display any error found to the user later on
$error = "";

//If values are safe to use
if(($email == $email2 && $token == $token2) && ($email2 != "" && $token2 != ""))
{

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
          header("Location: https://www.cs.uky.edu/~anwh223/estudio/Register.php?e=" . urlencode($email) . "&t=" . $token);
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
          header("Location: https://www.cs.uky.edu/~anwh223/estudio/Register.php?e=" . urlencode($email) . "&t=" . $token);
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

  //Check that there is an accountToken with the provided email
  $result = mysqli_query($con,"SELECT * FROM accountTokens WHERE email='" . $email . "'");

  //If there is an account token with the provided email
  if($row = mysqli_fetch_array($result))
  {
    //Set the number of iterations for the hash
    $iterations = 1000;

    //Get the 16 byte salt from the token in the database
    $salt = hex2bin(substr($row['token'], 0, 32));

    //Generate the hash using the salt and the provided token
    $hash = hash_pbkdf2("sha256", $token, $salt, $iterations, 64);

    //Create the key (salt + hash)
    $key = substr($row['token'], 0, 32) . $hash;

    //If key matches token in database
    if($key == $row['token'])
    {
      //Set validated flag to true
      $validated = TRUE;
    }
    else
    {
      $error = "Invalid token.";
    }
  }
  else
  {
    $error = "An invitation has not been sent to this email or the token has been used.";
  }
}
else
{
  $error = "Invalid email/token passed.";
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
<title>EStudio Account Registration</title>
<?php

//Only create register function is user is validated
if($validated)
{

echo "<script>\n";
echo "function register() {\n";
echo "  username = encodeURIComponent(document.getElementById(\"username\").value);\n";
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
echo "	alert('You have successfully registered.');\n";
echo "        window.location.href = \"https://www.cs.uky.edu/~anwh223/estudio/Login.php\";\n";
echo "      }\n";
echo "      else\n";
echo "      {\n";
echo "        document.getElementById(\"txtHint\").innerHTML=xmlhttp.responseText;\n";
echo "      }\n";
echo "    }\n";
echo "  }\n";
echo "  xmlhttp.open(\"GET\",\"register.php?u=\"+username+\"&p=\"+password+\"&e=" . urlencode($email) . "&t=" . $token . "\",true);\n";
echo "  xmlhttp.send();\n";
echo "}\n";
echo "</script>\n";

}

?>
</head>
<body>
<br>
<fieldset >
<legend>Enter New Account Information</legend>
<?php

//Only display input fields and submit button if validated
if($validated)
{

echo "<label for='username' >Username:</label>\n";
echo "<input type='text' name='username' id='username' maxlength=\"20\" />\n";
echo "<label for='password' >Password:</label>\n";
echo "<input type='password' name='password' id='password' maxlength=\"50\" />\n";
echo "<button onclick='register()'>Register</button><br>\n";
echo "<br>\n";
}

//Always set txtHint to error
echo "<div id=\"txtHint\"><b>" . $error . "</b></div>";
?>

</fieldset>
<br>
</body>
</html>

