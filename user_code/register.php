<!DOCTYPE html>
<html lang='de'>
<head>
<meta charset='UTF-8'>
<meta name='viewport' content='width=device-width, initial-scale=1.0'>
<title>Form-Vorlage</title>
<?php 
require_once(__DIR__.'/../yback/include/inc_main.php')
// register
// ypum-Details
// TRIGGER
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

                        <!-- New BSG Selection Section -->
                        <div class='mb-3'>
                            <label for='verband' class='form-label'>Welche Betriebssportgemeinschaft (BSG) darf deine Daten verarbeiten? Willst du die Berechtigungen erweitern, kannst du das jederzeit in der Tabelle <b>'Wer darf meine Daten sehen?'</b> einstellen. Ist deine BSG noch nicht in der Liste, wende dich bitte an die Spartenleitung - die BSG muss zuerst erstellt werden.</label>
                            
                            <!-- Regional association dropdown -->
                            <?php
                            $verband_options = '';
                            $query_verband = "SELECT id, Verband FROM b_regionalverband ORDER BY Verband";
                            $result_verband = $db->query($query_verband);
                            foreach ($result_verband['data'] as $row) {
                                $selected = (isset($_POST['verband']) && $_POST['verband'] == $row['id']) ? 'selected' : '';
                                $verband_options .= "<option value='".$row['id']."' $selected>".htmlspecialchars($row['Verband'])."</option>";
                            }
                            ?>
                            <label for='verband' class='form-label'>Regionalverband</label><br>
                            <select class='form-select mb-2' id='verband' name='verband' onchange="loadBSGs(this.value)">
                                <option value='' disabled selected>Bitte wählen...</option>
                                <?= $verband_options ?>
                            </select>
                            
                            <!-- BSG dropdown, will be populated by JavaScript -->
                            <label for='bsg' class='form-label'>Betriebssportgemeinschaft</label><br>
                            <select required class='form-select' id='bsg' name='bsg'>
                                <option value='' disabled selected>Bitte erst Regionalverband wählen...</option>
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

<script>
// Function to load BSGs based on selected Verband
function loadBSGs(verbandId) {
    if (!verbandId) return;
    
    // Create AJAX request
    const xhr = new XMLHttpRequest();
    xhr.open('POST', './../../user_code/get_bsgs.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    
    xhr.onload = function() {
        if (this.status === 200) {
            try {
                const response = JSON.parse(this.responseText);
                
                // Get the BSG dropdown
                const bsgSelect = document.getElementById('bsg');
                
                // Clear existing options
                bsgSelect.innerHTML = '';
                
                // Add default option
                const defaultOption = document.createElement('option');
                defaultOption.text = 'Bitte wählen...';
                defaultOption.value = '';
                defaultOption.disabled = true;
                defaultOption.selected = true;
                bsgSelect.appendChild(defaultOption);
                
                // Add BSG options from response
                if (response.bsgs && response.bsgs.length > 0) {
                    response.bsgs.forEach(bsg => {
                        const option = document.createElement('option');
                        option.text = bsg.BSG;
                        option.value = bsg.id;
                        bsgSelect.appendChild(option);
                    });
                } else {
                    // If no BSGs found
                    const option = document.createElement('option');
                    option.text = 'Keine BSGs gefunden';
                    option.disabled = true;
                    bsgSelect.appendChild(option);
                }
            } catch (e) {
                console.error('Error parsing JSON response', e);
            }
        }
    };
    
    xhr.onerror = function() {
        console.error('Request failed');
    };
    
    // Send request with verbandId
    xhr.send('verband_id=' + verbandId);
}

// Initialize BSG dropdown if verband is already selected (e.g., on form reload after validation error)
document.addEventListener('DOMContentLoaded', function() {
    const verbandSelect = document.getElementById('verband');
    if (verbandSelect.value) {
        loadBSGs(verbandSelect.value);
    }
});
</script>

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
        
        // Add BSG and Verband to the data
        $verband = isset($_POST['verband']) ? (int)$_POST['verband'] : null;
        $bsg = isset($_POST['bsg']) ? (int)$_POST['bsg'] : null;
        
        $userData = array_merge($_POST, [
            'geschlecht' => $geschlecht,
            'okformail' => $okformail,
            'bsg' => $bsg
        ]);
        
        $usm->writeUserData($userData, false, true);
        $conf->redirect('registermail_sent.php');
    } catch(Exception $e) {
        echo('<b>Fehler! </b>'.$e->getMessage());
    }
}
?>

</body>
</html>