<?php

//Start php session
session_start();

//If logged in flag is not set
if(!(isset($_SESSION['EStudioLoggedIn'])))
{
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
      //Get username and usertype of current user
      $userUsertype=$_SESSION['EStudioUsertype'];
      $userUsername=$_SESSION['EStudioUsername'];

      //Get db information
      $dbinfo = include('config.php');

      //Connect to the database
      $con = mysqli_connect($dbinfo[host],$dbinfo[username],$dbinfo[password],$dbinfo[dbname]);
      if (!$con) {
        die('Could not connect: ' . mysqli_error($con));
      }

      mysqli_select_db($con,$dbinfo[dbname]);

      //Double check that the user is in the database
      $userResult = mysqli_query($con, "SELECT * FROM users WHERE username='" . $userUsername . "'");

      //If there is a user in the database with that username
      if($user = mysqli_fetch_array($userResult))
      {
	//Update the usertype of the user to make sure their permissions are valid
        //  Usertypes can be modified so we need to make sure that if a user
        //  logs in as one usertype (Admin) and is changed to another (Staff),
        //  they have access to their new usertype and not their old usertype
        $userUsertype = $user['usertype'];

	//Get current user's email so we can send them a notification
	//  when their invitation is accepted
	$userEmail = $user['email'];
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
    else
    {
	//Redirect to the login page
        header("Location: https://www.cs.uky.edu/~anwh223/estudio/Login.php");
        die();
    }
  }
}

//Get the email to send the invitation to and their usertype
$email = urldecode($_GET['e']);
$usertype = $_GET['v'];

//Make sure values are safe for use
$usertype2 =  mysqli_real_escape_string($con,$usertype);
$email2 =  mysqli_real_escape_string($con,$email);

//If values are safe for use in sql queries
if($usertype == $usertype2 && $email == $email2)
{
  //If values are not null and usertype is numeric
  if($usertype != "" && $email != "" && is_numeric($usertype))
  {
    //If usertype is 0, 1, or 2
    if(is_numeric($usertype) && ($usertype == "0" || $usertype == "1" || $usertype == "2"))
    {
      //If email is valid
      if(filter_var($email, FILTER_VALIDATE_EMAIL))
      {
	//Convert usertype string to integer
        $usertype = intval($usertype);
	
	//Check if other users are using the provided email
        $userResult = mysqli_query($con,"SELECT * FROM users WHERE email='" . $email . "'");

	//If the email is not being used by other users
        if(!($row = mysqli_fetch_array($userResult)))
        {
	  //If the user sending the invitation is of equal or higher user level
	  //  Highest user level is 0 (SuperAdmin), lowest user level is 2(Staff)
          if($userUsertype <= $usertype)
          {
	    //Delete all other invitations to the email
            mysqli_query($con,"DELETE FROM accountTokens WHERE email='" . $email . "'");

	    //Set the number of iterations for the hash
            $iterations = 1000;

	    //Generate a 16 byte salt and 32 hexit token using openssl_random_pseudo_bytes()
            $salt = openssl_random_pseudo_bytes(16);

	    //The token that will be emailed to the new user
            $token = bin2hex(openssl_random_pseudo_bytes(16));

	    //Generate hash using the salt and token
            $hash = hash_pbkdf2("sha256", $token, $salt, $iterations, 64);

	    //Convert the salt to hexits
            $saltHex = bin2hex($salt);

	    //Get the current datetime info
            $month = intval(date('m', time()));
            $day = intval(date('d', time()));
            $year = intval(date('y', time()));
            $hour = intval(date('h', time()));
            $minute = intval(date('i', time()));
            $second = intval(date('s', time()));

	    //Put datetime info into SQL DATETIME format
	    //  "YYYY-MM-DD HH:MM:SS"
            $timestamp = $year . "-" . $month . "-" . $day . " " . $hour . ":" . $minute . ":" . $second;

	    //Insert the accountToken into the database
	    //The format for an accountToken is
	    //  email, token(salt+hash), usertype, timestamp, invitedBy(email of user that sent invite)
            $tokeninsert="INSERT INTO accountTokens values(\"" . $email . "\",\"" . $saltHex . $hash . "\",\"" . $usertype . "\",\"" . $timestamp . "\",\"" . $userEmail . "\");";
            mysqli_query($con, $tokeninsert);

            //Setup automated email to send registration invitation
            $to      = $email;
            $subject = 'EStudio Account Invitation';
            $message = "Here is your invitation to register for an account to manage the EStudio Survey.\n";
            $message = $message . "\nYour link to register is:\n\n";
            $message = $message . "https://www.cs.uky.edu/~anwh223/estudio/Register.php?e=" . urlencode($email) . "&t=" . $token;
            $headers = 'From: EStudio <noreply@estudio.uky.edu>' . "\r\n" . 'X-Mailer: PHP/' . phpversion();

            //Send email
            mail($to, $subject, $message, $headers);

            echo "0";
          }
          else
          {
            echo "You are not allowed to invite accounts higher than your own.";
          }
        }
        else
        {
          echo "Email matches an already registered user.";
        }
      }
      else
      {
        echo "Invalid email.";
      }
    }
    else
    {
      echo "Non numeric/valid usertype passed.";
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
