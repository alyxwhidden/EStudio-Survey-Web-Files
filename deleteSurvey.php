<?php

//Start php session
session_start();

//If logged in flag is not set
if(!(isset($_SESSION['EStudioLoggedIn'])))
{
  //Redirect to the login page
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
      //Get username and usertype from session
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

      //Double check that the user is in the database
      $userResult = mysqli_query($con, "SELECT * FROM users WHERE username='" . $username . "'");

      //If there is a user in the database with that username
      if($user = mysqli_fetch_array($userResult))
      {
        //Update the usertype of the user to make sure their permissions are valid
        //  Usertypes can be modified so we need to make sure that if a user
        //  logs in as one usertype (Admin) and is changed to another (Staff),
        //  they have access to their new usertype and not their old usertype
        $usertype = $user['usertype'];
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
    else //username and usertype are not set
    {
        //Redirect to the login page
        header("Location: https://www.cs.uky.edu/~anwh223/estudio/Login.php");
        die();
    }
  }
}

//Get the survey version
$version = $_GET['v'];



//Make values safe for use
$version2 = mysqli_real_escape_string($con, $version);

//If version isn't safe to use ir it isn't a number
if($version != $version2 || !is_numeric($version))
{
  mysqli_close($con);
  die('Bad version passed.');
}
else
{
  //Get the integer value of the version
  $version = intval($version);
}

//Check that there is a survey with that version in the database
$versionCheckQuery = "SELECT * FROM surveys WHERE version=" . $version;
$result = mysqli_query($con,$versionCheckQuery);

//If there is a survey with that version
if($row = mysqli_fetch_array($result))
{

//Create queries to delete the survey, it's questions, and it's responses
$deleteResponsesQuery = "DELETE FROM responses WHERE version=" . $version;
$deleteSurveyQuery = "DELETE FROM surveys WHERE version=" . $version;
$deleteQuestionsQuery = "DELETE FROM questions WHERE version=" . $version;

//Run the queries
mysqli_query($con,$deleteResponsesQuery);
mysqli_query($con,$deleteSurveyQuery);
mysqli_query($con,$deleteQuestionsQuery);

//Redirect to the control panel
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
