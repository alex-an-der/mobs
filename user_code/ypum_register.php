<!DOCTYPE html>
<html lang='de'>
<head>
<meta charset='UTF-8'>
<meta name='viewport' content='width=device-width, initial-scale=1.0'>
<title>Form-Vorlage</title>
<?php require_once('/var/www/html/udama/ypum/yback/include/inc_main.php');?>
</head>
<body>
<div class='container'><div class='row'>

<form method='post'>
	<p>Mailadresse<br><input required class='form-control' required type='email' name='mail' value='<?= isset($_POST['mail']) ? $_POST['mail'] : '' ?>'/></p>
	<p>Nachname<br><input required class='form-control' type='text' name='nname' value='<?= isset($_POST['nname']) ? $_POST['nname'] : '' ?>' /></p>
	<p>Vorname<br><input required class='form-control' type='text' name='vname' value='<?= isset($_POST['vname']) ? $_POST['vname'] : '' ?>' /></p>
	<p><button type='submit' class='btn btn-success btn-block' name='saveandmail'>Speichern und Bestätigungsmail senden</button></p>
</form>
</div></div>

<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

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
