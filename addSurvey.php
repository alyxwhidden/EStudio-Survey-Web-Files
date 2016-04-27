<?php

//Start php session
session_start();

//If logged in flag is not set
if(!(isset($_SESSION['EStudioLoggedIn'])))
{
  //Redirect to the login page
  //header("Location: https://www.cs.uky.edu/~anwh223/estudio/Login.php");
  die("2");
}
else
{
  //If not logged in
  if($_SESSION['EStudioLoggedIn'] !== TRUE)
  {
    //Redirect to the login page
    //header("Location: https://www.cs.uky.edu/~anwh223/estudio/Login.php");
    die("2");
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
        //header("Location: https://www.cs.uky.edu/~anwh223/estudio/Login.php");
        mysqli_close($con);
        die("2");
      }

      //If usertype should not have access to this page
      if($usertype != 0 && $usertype != 1 && $usertype != 2)
      {
        //Redirect to the login page
        //header("Location: https://www.cs.uky.edu/~anwh223/estudio/Login.php");
        mysqli_close($con);
        die("2");
      }
    }
    else //username and usertype are not set
    {
        //Redirect to the login page
        //header("Location: https://www.cs.uky.edu/~anwh223/estudio/Login.php");
        die("2");
    }
  }
}

//Get the question count
$c = $_GET['c'];

//Get the url encoded survey name, decode it, and strip all html/xml tags from it
$name = strip_tags(urldecode($_GET['n']));


//Make sure values safe for use to avoid sql injection
$c2 = mysqli_real_escape_string($con, $c);
$name2 = mysqli_real_escape_string($con, $name);

//If the original strings don't match their string with
//  unsafe/harmful entries removed
if($c != $c2 || $name != $name2)
{
  //Exit with error
  mysqli_close($con);
  die('Bad version or name passed.');
}
else if(!(is_numeric($c))) //If strings don't contain harmful entries, but the question count isn't numeric
{
  mysqli_close($con);
  die('Non numeric count passed.');
}

//Get the integer value from the question count string
$numOfQuestions = intval($c);

if($numOfQuestions > 50 || $numOfQuestions < 1)
{
  mysqli_close($con);
  die('Invalid number of questions.');
}

//Create an array for the questions
$questions = array();

//for each question
for($i = 1; $i <= $numOfQuestions; $i++)
{
  //Question names are in the format q(question number)
  //  so question 1 is stored in $_GET["q1"]
  //  so we set the question name to "q" . $i to get that question
  $qName = "q" . $i;
  
  //Get the question, decode it, and strip all html/xml tags from it
  $question = strip_tags(urldecode($_GET[$qName]));

  //Make sure values safe for use to avoid sql injection
  $question2 = mysqli_real_escape_string($con, $question);

  //If the original strings don't match their string with
  //  unsafe/harmful entries removed
  if($question != $question2)
  {
    mysqli_close($con);
    die('Question contains harmful characters.');
  }
  else //Question is okay to use
  {
    //Store the question in the array
    $questions[] = $question;
  }
}

//Get the highest survey version
$highestVersionQuery = "SELECT MAX(version) AS highestVersion FROM surveys";
$result = mysqli_query($con,$highestVersionQuery);

//If there is a max version number
if($row = mysqli_fetch_array($result))
{

//The format for a survey object is
// name,active,timestamp,version,questions

//Get the highest version number
$highestVersion = $row['highestVersion'];

//Add 1 to it for the new survey version number
$version = $highestVersion + 1;

//Get the current DATETIME information
$month = intval(date('m', time()));
$day = intval(date('d', time()));
$year = intval(date('y', time()));
$hour = intval(date('h', time()));
$minute = intval(date('i', time()));
$second = intval(date('s', time()));

//Format it as a SQL DATETIME object
$timestamp =  $year . "-" . $month . "-" . $day . " " . $hour . ":" . $minute . ":" . $second;

//Insert the new survey into the database
$insertSurveyQuery = "INSERT INTO surveys values (\"" . $name . "\", \"FALSE\", \"" . $timestamp . "\", \"" . $version . "\", \"" . $numOfQuestions . "\");";
mysqli_query($con,$insertSurveyQuery);

//The format for a question object is
//  question,version,id

//Setup the start of the SQL insert
$insertQuestionsQuery = "INSERT INTO questions values ";

//For each question
for($i = 1; $i <= $numOfQuestions; $i++)
{
  //Create sql code to insert question into database
  $questionValues = "(\"" . $questions[$i - 1] . "\", \"" . $version . "\", \"" . $i . "\")";

  //If it's not the last question
  if($i != $numOfQuestions)
  {
    //Add the code for the question and a "," for adding another question
    $insertQuestionsQuery = $insertQuestionsQuery . $questionValues . ",";
  }
  else //It's the last question
  {
    //Add the code for the question and a ";" for ending the SQL statement
    $insertQuestionsQuery = $insertQuestionsQuery . $questionValues . ";";
  }
}

//Insert the questions into the database
mysqli_query($con,$insertQuestionsQuery);

//Redirect to the control panel
//header("Location: https://www.cs.uky.edu/~anwh223/estudio/ControlPanel.php");
mysqli_close($con);
die("1");
}
else //Couldn't get the highest version number
{
  mysqli_close($con);
  die('Could not retrieve highest survey version number.');
}
?>
