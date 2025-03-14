<?php require_once('/var/www/html/udama/ypum/yback/ypum.php');?><!DOCTYPE html>
<html lang='de'>
<head>
<meta charset='UTF-8'>
<meta name='viewport' content='width=device-width, initial-scale=1.0'>
<title>Edit-Vorlage</title>
<?php
	require_once('/var/www/html/udama/ypum/yback/include/inc_main.php');
if(isset($_POST['save'])) $usr = $_POST;
else $usr = $usm->readUserData();
?>
</head>
<body>
<div class='container'><div class='row'>

<form method='post'>
	<p>Mailadresse<br><input class='form-control' readonly required type='email' name='mail' value='<?= isset($usr['mail']) ? $usr['mail'] : '' ?>'/></p>
	<p>Nachname<br><input class='form-control' type='text' name='nname' value='<?= isset($usr['nname']) ? $usr['nname'] : '' ?>' /></p>
	<p>Vorname<br><input class='form-control' type='text' name='vname' value='<?= isset($usr['vname']) ? $usr['vname'] : '' ?>' /></p>
	<p><button type='submit' class='btn btn-success btn-block' name='save'>Speichern</button></p>
</form>
</div></div>

<?php
if(isset($_POST['save'])){
try{
$usm->writeUserData($_POST, true, false);
}catch(Exception  $e){
echo('<b>Fehler! </b>'.$e->getMessage());
}
}?>

</body>
</html>
