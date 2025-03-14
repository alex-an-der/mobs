<?php
    namespace ypum;
    require_once(__DIR__."/../ypum.php");
    $dateiname = "mail_neu";
    $titel="Neuer Nutzer";
    require_once(__DIR__."/../include/classes/mailmanager.php");
    $mailer = new mailmanager($dateiname, $titel, $conf);
?>

