<?php
function isMobile() {
    return preg_match("/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i", $_SERVER["HTTP_USER_AGENT"]);
}
?>

<html>
<head>
<?php
if(isMobile())
echo "<meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">\n";

?>
<link rel="stylesheet" href="foundation.css" />
<title>404 Page Not Found</title>

</head>
<body>
<br>
<fieldset >
<legend>404 Page Not Found</legend>
Click <a href='https://www.cs.uky.edu/~anwh223/estudio/Survey.php'>here</a> to return to the survey!<br><br><br>
Click <a href='https://www.cs.uky.edu/~anwh223/estudio/ControlPanel.php'>here</a> to return to the control panel!
<br>
</fieldset>
<br>

</body>
</html>
