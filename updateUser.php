<?php


session_start();

if(!(isset($_SESSION['EStudioLoggedIn'])))
{
  header("Location: https://www.cs.uky.edu/~anwh223/estudio/Login.php");
  die();
}
else
{
  if($_SESSION['EStudioLoggedIn'] !== TRUE)
  {
    header("Location: https://www.cs.uky.edu/~anwh223/estudio/Login.php");
    die();
  }
  else
  {
    if(isset($_SESSION['EStudioUsertype']) && isset($_SESSION['EStudioUsername']))
    {
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

      $userResult = mysqli_query($con, "SELECT * FROM users WHERE username='" . $username . "'");
      if($user = mysqli_fetch_array($userResult))
      {
        $usertype = $user['usertype'];
      }
      else
      {
        header("Location: https://www.cs.uky.edu/~anwh223/estudio/Login.php");
        mysqli_close($con);
        die();
      }
      if($usertype != 0 && $usertype != 1 && $usertype != 2)
      {
        header("Location: https://www.cs.uky.edu/~anwh223/estudio/Login.php");
        mysqli_close($con);
        die();
      }
    }
    else
    {
        header("Location: https://www.cs.uky.edu/~anwh223/estudio/Login.php");
        die();
    }
  }
}


$targetUsername = $_GET['u'];
$modification = $_GET['m'];

//Make values safe for use
$targetUsername2 = mysqli_real_escape_string($con, $targetUsername);
$modification2 = mysqli_real_escape_string($con, $modification);

//If values aren't safe
if($targetUsername != $targetUsername2 || $modification != $modification2)
{
  mysqli_close($con);
  die('Bad username or modification passed.');
}
else if(!(is_numeric($modification))) //If type of modification is non numeric
{
  mysqli_close($con);
  die('Non numeric modification passed.');
}

//Convert to int
$modification = intval($modification);

//Get the target user
$targetUser = mysqli_query($con,"SELECT * FROM users WHERE username=\"" . $targetUsername . "\"");

//If the user exists
if($row = mysqli_fetch_array($targetUser))
{


$targetUsertype = $row['usertype'];
//Perform the modification
//0 == change in user level, 1 == deletion of user, 2 == toggle email alerts
if($modification == 0)
{

//If the user is not able to modify the target user
if($usertype >= $targetUsertype)
{
  mysqli_close($con);
  die('You cannot modify this user.');
}
$value = $_GET['v'];
$value2 = mysqli_real_escape_string($con, $value);
if($value != $value2)
{
  mysqli_close($con);
  die('Bad user value passed.');
}
else if(!(is_numeric($value)))
{
  mysqli_close($con);
  die('Non numeric user value passed.');
}

$value = intval($value);
if($value >=0 && $value < 3)
{
  if($value != $targetUsertype)
  {
    mysqli_query($con,"UPDATE users SET usertype=" . $value . " where username=\"" . $targetUsername . "\"");
    header("Location: https://www.cs.uky.edu/~anwh223/estudio/Users.php");
    mysqli_close($con);
    die();
  }
  else
  {
    header("Location: https://www.cs.uky.edu/~anwh223/estudio/Users.php");
    mysqli_close($con);
    die();
  }
}
else
{
  mysqli_close($con);
  die('Invalid usertype passed.');
}

}
else if($modification == 1)
{
  if($usertype >= $targetUsertype && $username != $targetUsername)
  {
    mysqli_close($con);
    die('You cannot modify this user.');
  }
  mysqli_query($con,"DELETE FROM users WHERE username=\"" . $targetUsername . "\"");
  mysqli_query($con,"DELETE FROM resetTokens WHERE username=\"" . $targetUsername . "\"");
  header("Location: https://www.cs.uky.edu/~anwh223/estudio/Users.php");
  mysqli_close($con);
  die();
}
else if($modification == 2)
{
  if($username != $targetUsername)
  {
    mysqli_close($con);
    die('You cannot modify this user.');
  }
  mysqli_query($con,"UPDATE users SET alert=\"" . !($row['alert']) . "\" WHERE username=\"" . $targetUsername . "\"");
  header("Location: https://www.cs.uky.edu/~anwh223/estudio/Users.php");
  mysqli_close($con);
  die();

}
else
{
  mysqli_close($con);
  die('Invalid modification passed.');
}


}
else
{
  mysqli_close($con);
  die('No user with that username.');

}

?>
