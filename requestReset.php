<?php

//Get username and password
$username = urldecode($_GET['un']);
$email = urldecode($_GET['email']);

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
$email2 =  mysqli_real_escape_string($con,$email);

//If values are safe to use
if($username == $username2 && $email == $email2)
{
  //If values are not null
  if($username != "" && $email != "")
  {
    //Check that the user exists in the database
    $userResult = mysqli_query($con,"SELECT * FROM users WHERE username='" . $username . "'");

    //If the user is in the database
    if($row = mysqli_fetch_array($userResult))
    {
      //Get the user's email
      $emailOnFile = $row['email'];

      //If the user's email matches the provided email
      if($emailOnFile == $email)
      {
	//Delete the resetToken from the database
	mysqli_query($con,"DELETE FROM resetTokens WHERE username='" . $username . "'");

	//Set the number of iterations for the hash
        $iterations = 1000;

	//Generate a 16 byte salt and 32 hexit token using openssl_random_pseudo_bytes()
        $salt = openssl_random_pseudo_bytes(16);

	//The token that will be emailed to the user
        $token = bin2hex(openssl_random_pseudo_bytes(16));

	//Generate hash using salt and token
        $hash = hash_pbkdf2("sha256", $token, $salt, $iterations, 64);

	//Convert salt to hexits
        $saltHex = bin2hex($salt);

	//Get current datetime info
	$month = intval(date('m', time()));
	$day = intval(date('d', time()));
	$year = intval(date('y', time()));
	$hour = intval(date('h', time()));
	$minute = intval(date('i', time()));
	$second = intval(date('s', time()));

	//Put datetime info in SQL DATETIME format
	//  "YYYY-MM-DD HH:MM:SS"
	$timestamp = $year . "-" . $month . "-" . $day . " " . $hour . ":" . $minute . ":" . $second;

	//Insert the reset token into the database
	//The format for a reset token is
	// username, token (salt+hash), timestamp
        $tokeninsert="INSERT INTO resetTokens values(\"" . $username . "\",\"" . $saltHex . $hash . "\",\"" . $timestamp . "\");";
        mysqli_query($con, $tokeninsert);

	//Setup automated email to send reset link to user
        $to      = $email;
        $subject = 'EStudio Password Reset';
        $message = "You have received this email because you either requested it or have been registered as a user.\n";
        $message = $message . "\nYour username is: " . $username . "\n\nYour password reset link is:\n\n";
	$message = $message . "https://www.cs.uky.edu/~anwh223/estudio/Reset.php?u=" . urlencode($username) . "&t=" . $token;
        $headers = 'From: EStudio <noreply@estudio.uky.edu>' . "\r\n" . 'X-Mailer: PHP/' . phpversion();

        //Send email
        mail($to, $subject, $message, $headers);

        echo "1";
      }
      else
      {
        echo "Email does not match registered user's email.";
      }
    }
    else
    {
      echo "User does not exist.";
    }
  }
  else
  {
    echo "Fields cannot be blank.";
  }
}
else
{
  echo "Bad username/email passed.";
}
mysqli_close($con);
die();
?>
