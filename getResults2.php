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
      else
      {
	//Get boolean for whether or not we want all results
	$allResults = $_GET['t'];

	//Make sure values are safe to use
	$allResults2 =  mysqli_real_escape_string($con,$allResults);

	//If value is not safe
	if($allResults != $allResults2)
	{
          mysqli_close($con);
          die('Invalid query type passed.');
	}

	//If value is not numeric
	if(!is_numeric($allResults))
	{
          mysqli_close($con);
          die('Invalid query type passed.');
	}

	//Get the integer value
	$allResults = intval($allResults);

	//Get the survey version
	$version = $_GET['v'];

	//If we don't want all results
	if($allResults == 0)
	{
	
	//Get the start and end datetimes
	$startMonth = $_GET['sm'];
	$startDay = $_GET['sd'];
	$startYear = $_GET['sy'];
	$endMonth = $_GET['em'];
	$endDay = $_GET['ed'];
	$endYear = $_GET['ey'];

	//Make sure values are safe for use
	$startMonth2 =  mysqli_real_escape_string($con,$startMonth);
	$startDay2 =  mysqli_real_escape_string($con,$startDay);
	$startYear2 =  mysqli_real_escape_string($con,$startYear);
	$endMonth2 =  mysqli_real_escape_string($con,$endMonth);
	$endDay2 =  mysqli_real_escape_string($con,$endDay);
	$endYear2 =  mysqli_real_escape_string($con,$endYear);
	$version2 =  mysqli_real_escape_string($con,$version);

	//If values are safe for use
	if($startMonth == $startMonth2 && $startDay == $startDay2 && $startYear == $startYear2 && $endMonth == $endMonth2 && $endDay == $endDay2 && $endYear == $endYear2 && $version == $version2)
	{
	  //If values are numeric
	  if(is_numeric($startMonth) && is_numeric($startDay) && is_numeric($startYear) && is_numeric($endMonth) && is_numeric($endDay) && is_numeric($endYear) && is_numeric($version))
	  {
	    //Get integer values for start and end datetimes
	    $startMonth = intval($startMonth) + 1;
	    $startDay = intval($startDay);
	    $startYear = intval($startYear);
	    $endMonth = intval($endMonth) + 1;
	    $endDay = intval($endDay);
	    $endYear = intval($endYear);
	    $version = intval($version);
	  }
	  else
	  {
	    mysqli_close($con);
	    die('Invalid dates/version passed.');
	  }
	}
	else
	{
	  mysqli_close($con);
	  die('Invalid dates/version passed.');
	}

	//Make sure months, days, and years are mostly valid
        if($startMonth > 0 && $startMonth <= 12
	&& $startDay > 0 && $startDay <= 31
	&& $startYear > 0 && $startYear <= $endYear
	&& $endMonth > 0 && $endMonth <= 12
	&& $endDay > 0 && $endDay <= 31)
	{
		//Make sure end date is not before start date
		if($startYear == $endYear && (($startMonth > $endMonth) || ($startMonth == $endMonth && $startDay > $endDay)))
			die("Invalid dates.");

		//Create shell command to generate excel file
		$cmd = "../../SQL/./sqlTest " . $startYear . " " . $startMonth . " " . $startDay . " " . $endYear . " " . $endMonth . " " . $endDay . " " . $version;

		//Execute the command
		exec($cmd, $output, $exitCode);

		//Get the return code from the program
		$returnCode = $exitCode;

		//Handle return codes
		//If successful
		if( $returnCode == 0)
		{
			//Check if excel file exists
			$attachment_location = "Results.xls";
	        	if (file_exists($attachment_location)) {

				//Setup file for download and send it to the user
	            		header($_SERVER["SERVER_PROTOCOL"] . " 200 OK");
	            		header("Cache-Control: public"); // needed for i.e.
	            		header("Content-Type: application/vnd.ms-excel");
	            		header("Content-Transfer-Encoding: Binary");
	            		header("Content-Length:".filesize($attachment_location));
	            		header("Content-Disposition: attachment; filename=Results.xls");
	            		readfile($attachment_location);
	            		die();
	        	}
			else
			{
	            		die("Error: File " . $attachment_location . " not found.");
	        	}
		}
		else if($returnCode == 1)
		{
			die("No results matching range.");
		}
		else if($returnCode == 2)
		{
			die("Error connecting to database.");
		}
		else if($returnCode == 3)
		{
			die("Error intializing mysql..");
		}
		else if($returnCode == 4)
		{
			die("Error selecting database.");
		}
		else if($returnCode == 5)
		{
			die("Error executing query.");
		}
		else if($returnCode == 6)
		{
			die("Error retrieving query result.");
		}
		else
		{
			die("Unknown Error");
		}
	}
	else
	{
            die("Invalid dates.");
	}
	}
	else if($allResults == 1) //If we want all results
	{

	  //Make sure values are safe to use
	  $version2 =  mysqli_real_escape_string($con,$version);

	  //If value is not safe to use
	  if($version != $version2)
	  {
            mysqli_close($con);
            die('Invalid version passed.');
	  }

	  //If value is not numeric
	  if(!is_numeric($version))
	  {
            mysqli_close($con);
            die('Invalid version passed.');
	  }

	  //Get the integer value of the version
	  $version = intval($version);

		//Create shell command to generate excel file
		$cmd = "../../SQL/./sqlTest " . $version;

		//Execute the command
		exec($cmd, $output, $exitCode);

		//Get the return code
		$returnCode = $exitCode;

		//Handle return codes
		//If successful
		if( $returnCode == 0)
		{
			//Check if the excel file exists
			$attachment_location = "Results.xls";
	        	if (file_exists($attachment_location)) {
				
				//Setup file for download and send it to the user
	            		header($_SERVER["SERVER_PROTOCOL"] . " 200 OK");
	            		header("Cache-Control: public"); // needed for i.e.
	            		header("Content-Type: application/vnd.ms-excel");
	            		header("Content-Transfer-Encoding: Binary");
	            		header("Content-Length:".filesize($attachment_location));
	            		header("Content-Disposition: attachment; filename=Results.xls");
	            		readfile($attachment_location);
	            		die();
	        	}
			else
			{
	            		die("Error: File " . $attachment_location . " not found.");
	        	}
		}
		else if($returnCode == 1)
		{
			die("No results matching range.");
		}
		else if($returnCode == 2)
		{
			die("Error connecting to database.");
		}
		else if($returnCode == 3)
		{
			die("Error intializing mysql..");
		}
		else if($returnCode == 4)
		{
			die("Error selecting database.");
		}
		else if($returnCode == 5)
		{
			die("Error executing query.");
		}
		else if($returnCode == 6)
		{
			die("Error retrieving query result.");
		}
		else
		{
			die("Unknown Error");
		}

	}
	else
	{
	  mysqli_close($con);
	  die('Invalid query type passed.');
	}
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
?>
