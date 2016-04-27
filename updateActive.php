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



$version = $_GET['v'];


//Make values safe for use
$version2 = mysqli_real_escape_string($con, $version);

//If values are not safe or version is not numeric
if($version != $version2 || !is_numeric($version))
{
  mysqli_close($con);
  die('Bad version passed.');
}
else
{
  //Convert to an int
  $version = intval($version);
}

//Check that there is a survey with that version number in the database
$versionCheckQuery = "SELECT * FROM surveys WHERE version=" . $version;
$result = mysqli_query($con,$versionCheckQuery);

//If there is a survey with that version
if($row = mysqli_fetch_array($result))
{

//Get all surveys
$result = mysqli_query($con,"SELECT * FROM surveys");
$rowsUpdated=0;
while($row = mysqli_fetch_array($result))
{
$active=$row['active'];
$surveyVersion=$row['version'];

//If the current survey is the one we're toggling or if the current survey is active
if($surveyVersion == $version || $active == 1)
{
  if($active == 1)
  {
    $activeString = "FALSE";
  }
  else
  {
    $activeString = "TRUE";
  }

  //Toggle active
  $updateQuery = "UPDATE surveys SET active=" . $activeString . " WHERE version=" . $surveyVersion;
  mysqli_query($con, $updateQuery);
  $rowsUpdated++;
}
}
  header("Location: https://www.cs.uky.edu/~anwh223/estudio/ControlPanel.php");
  mysqli_close($con);
  die();
}
else
{
  mysqli_close($con);
  die('No version with that id.');
}
?>
