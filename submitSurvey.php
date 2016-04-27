<?php

//Connect to the database
$con = mysqli_connect('mysql','anwh223','$apphire','anwh223');

//Get db information
$dbinfo = include('config.php');

//Connect to the database
$con = mysqli_connect($dbinfo[host],$dbinfo[username],$dbinfo[password],$dbinfo[dbname]);
if (!$con) {
  die('Could not connect: ' . mysqli_error($con));
}

mysqli_select_db($con,$dbinfo[dbname]);

//Get survey values sent from html
$answers = strip_tags($_GET['a']);
$comment = strip_tags(urldecode($_GET['comment']));
$version = strip_tags(intval($_GET['v']));
$month = intval(date('m', time()));
$day = intval(date('d', time()));
$year = intval(date('y', time()));
$hour = intval(date('h', time()));
$minute = intval(date('i', time()));
$second = intval(date('s', time()));
//Make all values safe for use
$answers2 = mysqli_real_escape_string($con,$answers);
$comment2 = mysqli_real_escape_string($con,$comment);
$version2 = mysqli_real_escape_string($con,$version);


//If all values equal their safe values
if( $answers == $answers2 &&  $comment == $comment2 && $version == $version2)
{
  //Verify passed information
  $result = mysqli_query($con,"SELECT * FROM surveys WHERE active");
  $surveyVersion;
  $questions;
  if($row = mysqli_fetch_array($result))
  {
    $surveyVersion = $row['version'];
    $questions=$row['questions'];
    if($surveyVersion == $version)
    {
      //If user rated every question
      if(strpos($answers,'0') == false && strlen($answers) == $questions)
      {
        //Verify that all answers are valid
        for($i = 0; $i < strlen($answers); $i++)
        {
          if(!is_numeric($answers[$i]))
          {
            die('Invalid rating.');
          }
	  if(intval($answers[$i]) < 1 || intval($answers[$i]) > 5)
          {
            die('Invalid rating.');
          }
        }

        //Truncate comment if more than 1000 characters long
        if(strlen($comment) > 1000)
        {
          $comment=substr($comment,0,1000);
        }

        //Generate SQL command to add the survey result
        $surveyinsert="INSERT INTO responses values(\"" . $answers . "\",\"" . $comment . "\",\"" . $year . "-" . $month . "-" . $day . " " . $hour . ":" . $minute . ":" . $second . "\",\"" . $version . "\");";
        //Send the command to the database
        mysqli_query($con, $surveyinsert);
        //Echo 1 for success
        echo "1";
        
        //If any rating is a 1
        if(strpos($answers,'1') !== false)
        {
	  $to = "";
	  $usersToAlert = mysqli_query($con,"SELECT * FROM users WHERE alert");
	  $numOfUsers = mysqli_num_rows($usersToAlert);
	  $usersAdded = 0;
	  while($userToAlert = mysqli_fetch_array($usersToAlert))
	  {
	    $usersAdded++;
	    
	    $to = $to . $userToAlert['email'];
	    if($usersAdded != $numOfUsers)
	      $to = $to . ",";
	  }

	  if($to != "")
	  {
	  //Setup automated email to send on unsatisfactory survey response
          $subject = 'Survey Alert';
          $message = 'Answers: ' . $answers . "\n" . "Comments:\n\r" . $comment . "\n" . $month . "/" . $day . "/" . $year . " " . $hour . ":" . $minute . ":" . $second . "\nVersion: " . $version;
          $headers = 'From: EStudio <noreply@estudio.uky.edu>' . "\r\n" . 'X-Mailer: PHP/' . phpversion();

	  //Send email
          mail($to, $subject, $message, $headers);
	  }
        }
      }
      else //User left a rating blank
      {
        echo "Please rate all questions before submitting your survey.";
      }
    }
    else
    {
      die('Survey is no longer active or never was.');
    }
  }
  else
  {
    die('Survey is no longer active or never was.');
  }
}
else //Non safe characters passed
{
    echo "Please use only alphanumeric characters.";
}

//Close the connection to the database
mysqli_close($con);
?>
