<?php require_once(__DIR__."/../ypum.php");?>
<!DOCTYPE html>
<html lang="de">
<head>

<title></title>
<?php
include_once(__DIR__."/../components/navbar_userverwaltung.php");
// Erzeuge Tabelle aller benamten(!) Rollen
$csv = "ID, Mail, Rollen, ";
$users   = $dbm->query("SELECT id, mail, roles from y_user", array(), true);
$rollen = $dbm->query("SELECT bit, name from y_roles", array(), true);

$rollen_mit_namen = array();
$rollen_ohne_namen = array();

foreach($rollen as $rolle){
   
    if(strlen($rolle['name'])>0){
        $rolle['name'] =  html_entity_decode($rolle['name'], ENT_QUOTES | ENT_HTML5, "ISO-8859-1");
        $rollen_mit_namen[] = $rolle;
    }else{
         $rollen_ohne_namen[] = $rolle;
    }
}

foreach($rollen_mit_namen as $rolle){
    $bit = $rolle['bit'];
    $name = $rolle['name'];
    $csv .= "$name [$bit],";
}
$csv = substr($csv, 0, -1)."\n";

foreach($users as $user){
    $userid = $user['id'];
    $usermail = $user['mail'];
    $userroles = $user['roles'];
    $csv .= "$userid, $usermail, $userroles,";

    foreach($rollen_mit_namen as $rolle){
        $bit = $rolle['bit'];
        $rollenname = $rolle['name'];
        $berechtigt = "0";
        if($ypum->isRolleBerechtigt($userroles, pow(2, $bit))) $berechtigt = "1";
        $csv .= "$berechtigt,";
    }
    $csv = substr($csv, 0, -1)."\n";
}

// Prüfe, ob unbenamte Rechte vergeben sind
$achtung=false;
$ausgabe="";
foreach($users as $user){
    $userid = $user['id'];
    $usermail = $user['mail'];
    $userroles = $user['roles'];

    foreach($rollen_ohne_namen as $rolle){
        $bit = $rolle['bit'];
        $rollenname = $rolle['name'];
        
        if($ypum->isRolleBerechtigt($userroles, pow(2, $bit))){
            $ausgabe .= "<b>Achtung!</b> User $usermail (ID: $userid) hat eine Berechtigung ohne Namen (Bit = $bit)<br>";
            $achtung=true;
        }
    }
}

file_put_contents("roles.csv", $csv, LOCK_EX);


if(!$achtung) $ausgabe= "<p>Es wurden keine Zuweisungen ohne Rollenname gefunden. Alles in Ordnung.</p>";


?>
</head>
<body>
<div class="container mt-5">

    <p><?=$ausgabe?></p>
    <p><a href='roles.csv'>&Uuml;bersicht herunterladen</a></p>
</div>

</body>
</html>