<!DOCTYPE html>
<html lang='de'>
<head>
<meta charset='UTF-8'>
<meta name='viewport' content='width=device-width, initial-scale=1.0'>
<title>Form-Vorlage</title>
<?php require_once('/homepages/45/d923706743/htdocs/ypum/yback/include/inc_main.php');?>
</head>
<body>
<div class='container'><div class='row'>

<form method='post'>
	<p>Mailadresse<br><input class='form-control' required type='email' name='mail' value='<?= isset($_POST['mail']) ? $_POST['mail'] : '' ?>'/></p>
	<p>Wenn zutreffend: Mannschaft<br><input class='form-control' type='text' name='mannschaft' value='<?= isset($_POST['mannschaft']) ? $_POST['mannschaft'] : '' ?>' /></p>
	<p>Nachname<br><input class='form-control' type='text' name='nname' value='<?= isset($_POST['nname']) ? $_POST['nname'] : '' ?>' /></p>
	<p>Vorname<br><input class='form-control' type='text' name='vname' value='<?= isset($_POST['vname']) ? $_POST['vname'] : '' ?>' /></p>
	<p><button type='submit' class='btn btn-success btn-block' name='saveandmail'>Speichern und Bestätigungsmail senden</button></p>
</form>
</div></div>

<?php
if(isset($_POST['saveandmail'])){
	require_once('/homepages/45/d923706743/htdocs/ypum/yback/include/inc_main.php');
	$datensatz = array();
try{
	$usm->writeUserData($_POST, false, true);
	$conf->redirect('registermail_sent.php');
}catch(Exception  $e){
	echo('<b>Fehler! </b>'.$e->getMessage());
}}?>

</body>
</html>
