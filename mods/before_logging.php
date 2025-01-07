<?php
# $pdo ist ein PDO-Objekt für die aktuelle Datenbank
# $eintrag wird im nächsten Schritt zusammen mit dem aktuellen Timestamp in die Datenbank geschrieben
global $ypum;
$userData = $ypum->getUserData();
$userMail = $userData['mail'];
$eintrag .= " ($userMail)";
?>