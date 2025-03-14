<?php

$row = $dbm->query("select uf_name, fieldname from y_user_fields order by fieldname asc");

$html="<!DOCTYPE html>&#13;&#10;";
$html.="<html lang='de'>&#13;&#10;";
$html.="<head>&#13;&#10;";
$html.="<meta charset='UTF-8'>&#13;&#10;";
$html.="<meta name='viewport' content='width=device-width, initial-scale=1.0'>&#13;&#10;";
$html.="<title>Form-Vorlage</title>&#13;&#10;";
$html.="</head>&#13;&#10;";
$html.="<body>&#13;&#10;&#13;&#10;";
$html.="<form method='post'>&#13;&#10;";

$html .= "\t<p>Mailadresse<br><input required type='email' name='mail' value='<?= isset(\$_POST['mail']) ? \$_POST['mail'] : '' ?>'/></p>&#13;&#10;";

foreach($row as $ds){

    $label = $ds['uf_name'];
    $name =  $ds['fieldname'];
    $html .= "\t<p>$label<br><input type='text' name='$name' value='<?= isset(\$_POST['$name']) ? \$_POST['$name'] : '' ?>' /></p>&#13;&#10;";
}

$html .= "\t<p><button type='submit' name='saveandmail'>Speichern und Bestätigungsmail senden</button></p>&#13;&#10;";
$html .= "</form>&#13;&#10;&#13;&#10;";

$zugangsdaten = $conf->load("dbconfig");

$classesfile = realpath(__DIR__."/../inc_main.php");

$html .= "<?php&#13;&#10;";

$html .= "if(isset(\$_POST['saveandmail'])){&#13;&#10;";
$html .= "\trequire_once('$classesfile');&#13;&#10;";
$html .= "\t\$datensatz = array();&#13;&#10;";

// Usermanager aus der main-inc nutzen
$html .= "try{&#13;&#10;";
$html .= "\t\$usm->writeUserData(\$_POST, false, true);&#13;&#10;";
$html .= "\t\$conf->redirect('registermail_sent.php');&#13;&#10;";
$html .= "}catch(Exception  \$e){&#13;&#10;";
$html .= "\techo('<b>Fehler! </b>'.\$e->getMessage());&#13;&#10;";
$html .= "}}?>&#13;&#10;&#13;&#10;";

$html.="</body>&#13;&#10;";
$html.="</html>&#13;&#10;";

?>