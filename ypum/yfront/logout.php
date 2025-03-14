<?php
// Log-Out (Session löschen)
session_start();
$_SESSION = array();
session_destroy();
?>

Hier k&ouml;nnen Sie sich von Ihrem Anwender verabschieden. Und hier geht es zum 
<a href='login.php'>LogIn</a>.

