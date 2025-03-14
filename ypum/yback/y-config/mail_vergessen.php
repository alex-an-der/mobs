<?php
    namespace ypum;
    require_once(__DIR__."/../ypum.php");
    $dateiname = "mail_vergessen";
    $titel="PW vergessen";
    require_once(__DIR__."/../include/classes/mailmanager.php");
    $mailer = new mailmanager($dateiname, $titel, $conf);
?>