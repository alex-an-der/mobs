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
<div class='container py-4'>
    <div class='row justify-content-center'>
        <div class='col-12 col-md-8 col-lg-6'>
            <div class='card'>
                <div class='card-body'>
                    <form method='post'>
                        <div class='mb-3'>
                            <label for='mail' class='form-label'>Mailadresse</label>
                            <input required class='form-control' required type='email' id='mail' name='mail' value='<?= isset($_POST['mail']) ? $_POST['mail'] : '' ?>'/>
                        </div>
                        <div class='mb-3'>
                            <label for='vname' class='form-label'>Vorname</label>
                            <input required class='form-control' type='text' id='vname' name='vname' value='<?= isset($_POST['vname']) ? $_POST['vname'] : '' ?>' />
                        </div>
                        <div class='mb-3'>
                            <label for='nname' class='form-label'>Nachname</label>
                            <input required class='form-control' type='text' id='nname' name='nname' value='<?= isset($_POST['nname']) ? $_POST['nname'] : '' ?>' />
                        </div>
                        <?php
                        require_once(__DIR__."/../../config/db_connect.php");
                        $options = '';
                        $query = "SELECT id, auswahl FROM b___geschlecht";
                        $result = $db->query($query);
                        foreach ($result['data'] as $row) {
                            $options .= "<option value='".$row['id']."'>".$row['auswahl']."</option>";
                        }
                        ?>
                        <div class='mb-3'>
                            <label for='geschlecht' class='form-label'>Geschlecht</label>
                            <select required class='form-select' id='geschlecht' name='geschlecht'>
                                <option value='' disabled selected>Bitte wählen...</option>
                                <?= $options ?>
                            </select>
                        </div>
                        <div class='mb-3'>
                            <label for='gebdatum' class='form-label'>Geburtsdatum</label>
                            <input required class='form-control' type='date' id='gebdatum' name='gebdatum' value='<?= isset($_POST['geburtsdatum']) ? $_POST['geburtsdatum'] : '' ?>' />
                        </div>
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
                        <div class='mb-3'>
                            <label for='okformail' class='form-label'>Ich bin einverstanden, &uuml;ber Veranstaltungen und relevante Turniere per Mail vom Betriebssportverband unterrichtet zu werden. Diese Einstellung kann ich jederzeit &auml;ndern.</label>
                            <select required class='form-select' id='okformail' name='okformail'>
                                <?= $options_an_aus ?>
                            </select>
                        </div>
                        <div class="form-check mb-3">
                            <input type="checkbox" class="form-check-input" id="datenschutz" name="datenschutz" required>
                            <label class="form-check-label" for="datenschutz">Ich habe die <a href="https://lbsv-nds.de/datenschutz/" target="_blank">Datenschutzerklärung</a> gelesen und bin damit einverstanden.</label>
                        </div>
                        <div class='d-grid'>
                            <button type='submit' class='btn btn-success' name='saveandmail'>Speichern und Bestätigungsmail senden</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

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