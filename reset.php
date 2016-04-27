<?php

//Get username, token, and password
$username = urldecode($_GET['u']);
$token = $_GET['t'];
$password = urldecode($_GET['p']);

//Get db information
$dbinfo = include('config.php');

//Connect to the database
$con = mysqli_connect($dbinfo[host],$dbinfo[username],$dbinfo[password],$dbinfo[dbname]);
if (!$con) {
  die('Could not connect: ' . mysqli_error($con));
}

mysqli_select_db($con,$dbinfo[dbname]);

//Make sure values are safe for use
$username2 =  mysqli_real_escape_string($con,$username);
$password2 =  mysqli_real_escape_string($con,$password);
$token2 =  mysqli_real_escape_string($con,$token);

//If values are safe for use
if(($username == $username2 && $token == $token2 && $password == $password2) && ($username2 != "" && $token2 != "" && $password2 != ""))
{

  //Check if there is a resetToken with the provided username
  $result = mysqli_query($con,"SELECT * FROM resetTokens WHERE username='" . $username . "'");

  //If there is a resetToken with the provided username
  if($row = mysqli_fetch_array($result))
  {

    //Set the number of iterations for the hash
    $iterations = 1000;

    //Get the 16 bytes salt from the token in the database
    $salt = hex2bin(substr($row['token'], 0, 32));

    //Generate hash using the salt and the provided token
    $hash = hash_pbkdf2("sha256", $token, $salt, $iterations, 64);

    //Create the key (salt + hash)
    $key = substr($row['token'], 0, 32) . $hash;

    //If the key matches the token in the database
    if($key == $row['token'])
    {
      //Delete the reset token from the database
      mysqli_query($con,"DELETE FROM resetTokens WHERE username='" . $username . "'");

      //Generate a random 16 byte salt using openssl_random_pseudo_bytes()
      $salt = openssl_random_pseudo_bytes(16);

      //Generate the hash using the salt and the provided password
      $hash = hash_pbkdf2("sha256", $password, $salt, $iterations, 64);

      //Convert the salt to hexits
      $saltHex = bin2hex($salt);

      //Update the user's password
      mysqli_query($con,"UPDATE users SET password=\"" . $saltHex . $hash . "\" WHERE username=\"" . $username . "\"");

      //Set the locked flag to 0
      mysqli_query($con,"UPDATE users SET locked=\"0\" WHERE username=\"" . $username . "\"");

      //Set the number of failed login attempts to 0
      mysqli_query($con,"UPDATE users SET failedAttempts=\"0\" WHERE username=\"" . $username . "\"");
      echo "0";
    }
    else
    {
      echo "Invalid username and/or token and/or password.";
    }
  }
  else
  {
    echo "Invalid username and/or token and/or password.";
  }
}
else
{
  echo "Invalid username and/or token and/or password.";
}
mysqli_close($con);
die();
?>
