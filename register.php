<?php

//Get username, password, email, and token
$username = urldecode($_GET['u']);
$password = urldecode($_GET['p']);
$email = urldecode($_GET['e']);
$token = $_GET['t'];

//Get db information
$dbinfo = include('config.php');

//Connect to the database
$con = mysqli_connect($dbinfo[host],$dbinfo[username],$dbinfo[password],$dbinfo[dbname]);
if (!$con) {
  die('Could not connect: ' . mysqli_error($con));
}

mysqli_select_db($con,$dbinfo[dbname]);

//Make sure values are safe
$username2 =  mysqli_real_escape_string($con,$username);
$password2 =  mysqli_real_escape_string($con,$password);
$email2 =  mysqli_real_escape_string($con,$email);
$token2 =  mysqli_real_escape_string($con,$token);

//If values are safe
if(($email == $email2 && $token == $token2 &&  $username == $username2 &&  $password == $password2) && ($email2 != "" && $token2 != "" && $username2 != "" && $password2 != ""))
{
  //Check if token exists for provided email
  $result = mysqli_query($con,"SELECT * FROM accountTokens WHERE email='" . $email . "'");

  //If token exists for provided email
  if($row = mysqli_fetch_array($result))
  {
    //Get the usertype of the new user and the email of the user that sent the invite
    $usertype = $row['usertype'];
    $invitedBy = $row['invitedBy'];

    //Set the number of iterations for hashing
    $iterations = 1000;

    //Get the salt from the token in the database
    $salt = hex2bin(substr($row['token'], 0, 32));

    //Generate the hash using the salt and the provided token
    $hash = hash_pbkdf2("sha256", $token, $salt, $iterations, 64);

    //Create the key (salt + hash)
    $key = substr($row['token'], 0, 32) . $hash;

    //If key equals token in database
    if($key == $row['token'])
    {
      //Check if there are no users with the same username or email
      $userResult = mysqli_query($con,"SELECT * FROM users WHERE username='" . $username . "'");
      $emailResult = mysqli_query($con,"SELECT * FROM users WHERE email='" . $email . "'");
      if($row = mysqli_fetch_array($userResult))
      {
        echo "Username already taken.";
      }
      else if($row = mysqli_fetch_array($emailResult))
      {
        echo "Email already registered.";
      }
      else if(!filter_var($email, FILTER_VALIDATE_EMAIL))
      {
        echo "Email is invalid.";
      }
      else
      {

	//Generate a random 16 byte salt using openssl_random_pseudo_bytes()
        $salt = openssl_random_pseudo_bytes(16);

	//Generate hash using random salt and provided password
        $hash = hash_pbkdf2("sha256", $password, $salt, $iterations, 64);

	//Convert random salt to hexits
        $saltHex = bin2hex($salt);

	//Insert new user into database
	//The format for a user is
	//  username, password(salt+token), email, usertype, locked, failedAttempts, activated
        $userinsert="INSERT INTO users values(\"" . $username . "\",\"" . $saltHex . $hash . "\",\"" . $email . "\",\"" . $usertype . "\",\"0\",\"0\",\"1\",\"0\");";
        mysqli_query($con, $userinsert);
        mysqli_query($con, "DELETE FROM accountTokens WHERE email=\"" . $email . "\"");

	//Setup automated email to notify user that sent invite that it has been accepted
        $to      = $invitedBy;
        $subject = 'EStudio Account Invitation Accepted';
        $message = "Your invitation to " . $email . " has been accepted.\n\nThey have registered with username:\n\n" . $username;
        $headers = 'From: EStudio <noreply@estudio.uky.edu>' . "\r\n" . 'X-Mailer: PHP/' . phpversion();

        //Send email
        mail($to, $subject, $message, $headers);

        echo "0";
      }
    }
    else
    {
      echo "Invalid token.";
    }
  }
  else
  {
    echo "An invitation has not been sent to this email.";
  }
}
else
{
  echo "Invalid username/password/email/token passed.";
}
mysqli_close($con);
die();
?>



