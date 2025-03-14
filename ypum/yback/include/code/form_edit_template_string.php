<?php

$row = $dbm->query("select uf_name, fieldname from y_user_fields order by fieldname asc");
$classesfile = realpath(__DIR__."/../inc_main.php");
$ypumfile    = realpath(__DIR__."/../../ypum.php");

$htmledit ="<?php require_once('$ypumfile');?>";
$htmledit.="<!DOCTYPE html>&#13;&#10;";
$htmledit.="<html lang='de'>&#13;&#10;";
$htmledit.="<head>&#13;&#10;";
$htmledit.="<meta charset='UTF-8'>&#13;&#10;";
$htmledit.="<meta name='viewport' content='width=device-width, initial-scale=1.0'>&#13;&#10;";
$htmledit.="<title>Edit-Vorlage</title>&#13;&#10;";
$htmledit.="<?php&#13;&#10;";
$htmledit .= "\trequire_once('$classesfile');&#13;&#10;";
$htmledit.= "if(isset(\$_POST['save'])) \$usr = \$_POST;&#13;&#10;";
$htmledit.="else \$usr = \$usm->readUserData();&#13;&#10;";
$htmledit.="?>&#13;&#10;";
$htmledit.="</head>&#13;&#10;";
$htmledit.="<body>&#13;&#10;&#13;&#10;";
$htmledit.="<form method='post'>&#13;&#10;";

$htmledit .= "\t<p>Mailadresse<br><input readonly required type='email' name='mail' value='<?= isset(\$usr['mail']) ? \$usr['mail'] : '' ?>'/></p>&#13;&#10;";

foreach($row as $ds){

    $label = $ds['uf_name'];
    $name =  $ds['fieldname'];
    $htmledit .= "\t<p>$label<br><input type='text' name='$name' value='<?= isset(\$usr['$name']) ? \$usr['$name'] : '' ?>' /></p>&#13;&#10;";
}

$htmledit .= "\t<p><button type='submit' name='save'>Speichern</button></p>&#13;&#10;";
$htmledit .= "</form>&#13;&#10;&#13;&#10;";

$zugangsdaten = $conf->load("dbconfig");



$htmledit .= "<?php&#13;&#10;";
$htmledit .= "if(isset(\$_POST['save'])){&#13;&#10;";
$htmledit .= "try{&#13;&#10;";
$htmledit .= "\$usm->writeUserData(\$_POST, true, false);&#13;&#10;";
$htmledit .= "}catch(Exception  \$e){&#13;&#10;";
$htmledit .= "echo('<b>Fehler! </b>'.\$e->getMessage());&#13;&#10;";
$htmledit .= "}&#13;&#10;";


$htmledit .= "}?>&#13;&#10;&#13;&#10;";

$htmledit.="</body>&#13;&#10;";
$htmledit.="</html>&#13;&#10;";

?>