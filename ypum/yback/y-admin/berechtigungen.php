<?php 
require_once(__DIR__."/../ypum.php");
$dataFormat = "Y-m-d H:i:s"; 
include_once(__DIR__."/../components/navbar_userverwaltung.php");

/*
navbar_userverwaltung.php
berechtigungen.php
csv-export.php
*/

?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rollen&uuml;bersicht</title>

    <script src="./../includes/lib/datatables/datatables.js"></script>
    <script src="./../includes/lib/ColReorder-1.5.3/js/dataTables.colReorder.min.js"></script>

    <script>
        function reloadGET(bit){
            window.location = "berechtigungen.php?bit=" + bit;
        }
    
$(document).ready(function() {

    dataTable = $('#tab').DataTable({
        stateSave: true,
        colReorder: true
    });

});

</script>

</head>
<body>

<?php

$bttnColClass = "col col-m-3";
$bttnColClass = "";
$gesetztesBit = -1;
$gesetzterName = "Bitte w&auml;hlen...";
if(isset($_GET['bit'])) $gesetztesBit = $_GET['bit'];
$rollenauswahl  = "";
$alert = "";
$db = $ypum->getDB();
if($rollen=$db->query("SELECT bit, name, role_comment FROM y_roles WHERE (name IS NOT NULL AND NAME !='')",array(),true)){
    foreach($rollen as $rolle){
        $bit = $rolle['bit'];
        $name = $rolle['name'];
        if($gesetztesBit==$bit) $gesetzterName = $name;
        $rollenauswahl  .= "<a class='dropdown-item' href='#' onclick='reloadGET($bit);'>$name</a>";
    }
}else{
    $alert .= "<div class='alert alert-danger w-100' role='alert'>";
    $alert .= "Es wurden keine definierten Rollen gefunden!";
    $alert .= "</div>";
}

$fieldBttns = "";
$tabelle = "<table id='tab'></table>";
// --------------------------------------------------------------------------------------------
if(isset($_GET['bit'])){

    $bit = $_GET['bit'];

    $aFieldColumns = array();
    $tabelle = "<table id='tab'><thead><tr><th>Mail</th>\n";

    // Userdaten (=Userdetails) 
    if($fields=$db->query("SELECT uf_name, fieldname FROM y_user_fields order by fieldname;",array(),true)){
        foreach($fields as $field){

            $fieldAnzeige = $field['uf_name'];
            $fieldSpalte = $field['fieldname'];
            $aFieldColumns[] = $fieldSpalte;
            $tabelle .= "<th>$fieldAnzeige</th>\n";
            //$fieldBttns .= "<div class=$bttnColClass>";            
            //$fieldBttns .= "<button class='btn-block btn-primary'>$fieldAnzeige</button>"; 
            //$fieldBttns .= "</div>"; 
        }
    }
    $tabelle .= "</tr></thead>\n<tbody>\n";

    // User (mit Details) mit den Rollen
    if($userdetails=$db->query("SELECT y_user.roles as roles, y_v_userdata.* FROM y_v_userdata JOIN y_user ON y_v_userdata.userID = y_user.id",array(),true)){
        
        foreach($userdetails as $userdetail){

            if($ypum->isRolleBerechtigt($userdetail['roles'],pow(2,$bit))){
            
                $mail = $userdetail['mail'];
                $tabelle .= "<tr><td>$mail</td>";

                foreach($aFieldColumns as $fieldColumn){
                    
                    $detail = $userdetail[$fieldColumn];
                    $tabelle .= "<td>$detail</td>";
                }
                $tabelle .= "</tr>";
            }
        }

    }
    $tabelle .= "</tbody></table>";
}

// --------------------------------------------------------------------------------------------


?>


<div class='container'>
<div class='row'>
    <?=$alert?>
</div>
    <div class='row mt-3 mb-3'>
    <div class="input-group"><h4>Eine Liste aller Nutzer mit der Berechtigung </h4>
        <div class= <?=$bttnColClass?>>
            <div class='dropdown pl-3'>
                <button name='rollenauswahl' class='btn-block btn-outline-secondary dropdown-toggle pl-3 pr-3' type='button' id='rollenauswahlbox'
                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <?=$gesetzterName?>
                </button>
                <div class="dropdown-menu" aria-labelledby="rollenauswahlbox">
                    <?=$rollenauswahl?>
                </div>
            </div>
        </div>
    </div>
        <?=$fieldBttns?>

    </div>
    <div class='row'>
        <?=$tabelle?>
    </div>
</div>
</body>
</html>