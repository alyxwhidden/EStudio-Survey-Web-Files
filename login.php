<?php
//Start php session
session_start();

//Create logged in flag and set to false
$_SESSION['EStudioLoggedIn'] = FALSE;

//Get username and password
$username = urldecode($_GET['un']);
$password = urldecode($_GET['pw']);

//Set number of iterations for hashing
$iterations = 1000;

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

//If values are safe for use
if(($username == $username2 && $password == $password2) && ($username2 != "" && $password2 != ""))
{

//Check if username is in database
$result = mysqli_query($con,"SELECT * FROM users WHERE username='" . $username . "'");
if($row = mysqli_fetch_array($result))
{
  //If account is not locked
  if(!($row['locked']))
  {
    //Generate key using provided password
    //Get 16 byte salt
    $salt = hex2bin(substr($row['password'], 0, 32));
    $hash = hash_pbkdf2("sha256", $password, $salt, $iterations, 64);
    $key = substr($row['password'], 0, 32) . $hash;

    //If key matches password in database
    if($key == $row['password'])
    {
      //Refresh the number of failed attempts
      mysqli_query($con,"UPDATE users SET failedAttempts=\"0\" WHERE username=\"" . $username . "\"");

      //Generate a random 16 byte salt using openssl_random_pseudo_bytes()
      $salt = openssl_random_pseudo_bytes(16);

      //Generate hash using random salt and provided password
      $hash = hash_pbkdf2("sha256", $password, $salt, $iterations, 64);

      //Convert random salt to hexits
      $saltHex = bin2hex($salt);

      //Update user's hash in database
      mysqli_query($con,"UPDATE users SET password=\"" . $saltHex . $hash . "\" WHERE username=\"" . $username . "\"");


      //Set username, usertype, and logged in flag
      $_SESSION['EStudioLoggedIn'] = TRUE;
      $_SESSION['EStudioUsername'] = $row['username'];
      $_SESSION['EStudioUsertype'] = $row['usertype'];
      echo "1";
    }
    else
    {
      //If this is the 4th failed attempt to login
      if($row['failedAttempts'] == 3)
      {
	//Lock the user's account
        mysqli_query($con,"UPDATE users SET locked=\"1\" WHERE username=\"" . $username . "\"");

	//Let them know they have been locked out
	echo "Failed login 4 times, account is now locked.\nReset your password to unlock it.";

        //Setup automated email to notify user their account has been locked
        $to      = $row['email'];
        $subject = 'EStudio Account Locked';
        $message = "You have received this email because your account has been locked after 4 unsuccessful login attempts.\n";
        $message = $message . "\nYou will need to reset you password to unlock your account.\n\nYou can request a password reset here:\n\n";
        $message = $message . "http://www.cs.uky.edu/~anwh223/estudio/ResetPassword.html";
        $headers = 'From: EStudio <noreply@estudio.uky.edu>' . "\r\n" . 'X-Mailer: PHP/' . phpversion();

        //Send email
        mail($to, $subject, $message, $headers);

      }
      else
      {
	//Get current number of failed attempts and add 1
	$newFailedAttempts = intval($row['failedAttempts']) + 1;

	//Set failedAttempts to new value
        mysqli_query($con,"UPDATE users SET failedAttempts=\"" . $newFailedAttempts . "\" WHERE username=\"" . $username . "\"");
	echo "Invalid username and/or password.";
      }
    }
  }
  else
  {
    echo "Your account is locked.\nYou will need to reset your password to unlock your account.";
  }
}
else
{
  echo "Invalid username and/or password.";
}
}
else
{
  echo "Invalid username and/or password.";
}
mysqli_close($con);
die();
?>
