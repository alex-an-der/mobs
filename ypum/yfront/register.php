<!DOCTYPE html>
<html lang='de'>
<head>
<meta charset='UTF-8'>
<meta name='viewport' content='width=device-width, initial-scale=1.0'>
<title>Form-Vorlage</title>
<?php 
require_once(__DIR__.'/../yback/include/inc_main.php')
?>
</head>
<body>
<div class='container'><div class='row'>

<form method='post'>
	<p>Mailadresse<br><input required class='form-control' required type='email' name='mail' value='<?= isset($_POST['mail']) ? $_POST['mail'] : '' ?>'/></p>
	<p>Vorname<br><input required class='form-control' type='text' name='vname' value='<?= isset($_POST['vname']) ? $_POST['vname'] : '' ?>' /></p>
	<p>Nachname<br><input required class='form-control' type='text' name='nname' value='<?= isset($_POST['nname']) ? $_POST['nname'] : '' ?>' /></p>
	<?php
	require_once(__DIR__."/../../config/db_connect.php");
	$options = '';
	$query = "SELECT id, auswahl FROM b___geschlecht";
	$result = $db->query($query);
	foreach ($result['data'] as $row) {
		$options .= "<option value='".$row['id']."'>".$row['auswahl']."</option>";
	}
	?>
	<p>Geschlecht<br>
		<select required class='form-control' name='geschlecht'>
			<option value='' disabled selected>Bitte wählen...</option>
			<?= $options ?>
		</select>
	</p>
	<p>Geburtsdatum<br><input required class='form-control' type='date' name='gebdatum' value='<?= isset($_POST['geburtsdatum']) ? $_POST['geburtsdatum'] : '' ?>' /></p>
	<?php
	$options_an_aus = '';
	$query_an_aus = "SELECT id, wert FROM b___an_aus ORDER BY id ASC";
	$result_an_aus = $db->query($query_an_aus);
	$preselected_id = 1;
	foreach ($result_an_aus['data'] as $row) {
		$selected = ($row['id'] == $preselected_id) ? 'selected' : '';
		$options_an_aus .= "<option value='".$row['id']."' $selected>".$row['wert']."</option>";
	}

	?>
	<p>Ich bin einverstanden, &uuml;ber Veranstaltungen und relevante Turniere per Mail vom Betriebssportverband unterrichtet zu werden. Diese Einstellung kann ich jederzeit &auml;ndern. <br>
		<select required class='form-control' name='okformail'>
			<?= $options_an_aus ?>
		</select>
	</p>
	<div class="form-check mb-3">
		<input type="checkbox" class="form-check-input" id="datenschutz" name="datenschutz" required>
		<label class="form-check-label" for="datenschutz">Ich habe die <a href="https://lbsv-nds.de/datenschutz/" target="_blank">Datenschutzerklärung</a> gelesen und bin damit einverstanden.</label>
	</div>
	<p><button type='submit' class='btn btn-success btn-block' name='saveandmail'>Speichern und Bestätigungsmail senden</button></p>
</form>
</div></div>

<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

if(isset($_POST['saveandmail'])){
	$datensatz = array();
	try{
		// Ensure the geschlecht value (ID) is correctly handled
		$geschlecht = isset($_POST['geschlecht']) ? (int)$_POST['geschlecht'] : null;
		 $okformail = isset($_POST['okformail']) ? (int)$_POST['okformail'] : $preselected_id;
		$usm->writeUserData(array_merge($_POST, ['geschlecht' => $geschlecht, 'okformail' => $okformail]), false, true);
		$conf->redirect('registermail_sent.php');
	}catch(Exception  $e){
		echo('<b>Fehler! </b>'.$e->getMessage());
	}
}
?>

</body>
</html>