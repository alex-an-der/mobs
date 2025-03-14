<!DOCTYPE html>
<html lang='de'>
<head>
<meta charset='UTF-8'>
<meta name='viewport' content='width=device-width, initial-scale=1.0'>
<title>Form-Vorlage</title>
</head>
<body>

<form method='post'>
	<p>Mailadresse<br><input required type='email' name='mail' value='<?= isset($_POST['mail']) ? $_POST['mail'] : '' ?>'/></p>
	<p>Nachname<br><input type='text' name='nname' value='<?= isset($_POST['nname']) ? $_POST['nname'] : '' ?>' /></p>
	<p>Vorname<br><input type='text' name='vname' value='<?= isset($_POST['vname']) ? $_POST['vname'] : '' ?>' /></p>
	<p><button type='submit' name='saveandmail'>Speichern und Bestätigungsmail senden</button></p>
</form>

<?php
if(isset($_POST['saveandmail'])){
	require_once('/var/www/html/udama/ypum/yback/include/inc_main.php');
	$datensatz = array();
try{
	$usm->writeUserData($_POST, false, true);
	$conf->redirect('registermail_sent.php');
}catch(Exception  $e){
	echo('<b>Fehler! </b>'.$e->getMessage());
}}?>

</body>
</html>
