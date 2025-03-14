<?php require_once('/home/webpages/lima-city/x96/WWW_PROD/96bowling.de/ypum/yback/ypum.php');?>
<?php
require_once(__DIR__."/../yback/ypum.php");

echo("<div class='container'>");
if(!isset($_SESSION['uroles'])){
    echo 'Sie befinden sich im Installationsmodus und sind nicht eingeloggt. Daher k&ouml;nnen keine Kontodaten angezeigt werden.<br>';
    echo 'Vergessen SIe nicht, den Installationsmodus zu beenden, bevor Sie ypum produktiv nutzen.';
}else{
    echo ('<h4>Sie besitzen den Rollencode <b>'.$_SESSION['uroles'].'</b>. Damit sind Sie exemplarisch f&uuml;r folgende Rollencodes freigeschaltet:</h4>');

    // Explizierte Anfrage mit isBerechtigt(angefragte Rolle) erhalten Sie true, wenn der angemeldetet Nutzer
    // für die angefragte Rolle freigegeben ist.
    echo ('<START DER LISTE>');
    for($i=1;$i<32;$i++)
        if ($ypum->isBerechtigt($i)) echo ('Sie sind f&uuml;r Seiten mit dem Berechtigungscode  <b>'.$i.'</b>  freigegeben.<br>');
    echo ('<ENDE DER LISTE>');


    // Session direkt serverseitig zerstören und zurück zum LogIn. Optional kann eine Fehlermeldung mitgegeben werden.
    // (Auskommentiert, da diese Seite sonst sofort automatisch verlassen wird.)
    // $ypum->gotoLogin('Hier k&ouml;nnen Sie eine <b>Meldung</b> mitgeben.');

    // Session zerstoeren und optional eine Erfolgs- oder Fehlermeldung mitgeben (siehe HTML-Teil unten)
    // <a href='./../yfront/login.php?suc=Wie+gew%26uuml%3Bnscht+zum+LogIn.'>Wie gew&uuml;nscht zum LogIn.</a>
    // <a href='./../yfront/login.php?err=Wegen+eines+%3Cb%3EFehlers%3C%2Fb%3E+zum+LogIn.'>Wegen eines <b>Fehlers</b> zum LogIn.</a>

    // Zum LogOut (siehe HTML-Teil unten)
    // <a href='./../yfront/logout.php'>Log-Out</a>

    // Abfragen der gespeicherten Nutzerdaten

    echo('<p><h4>Abfrage der gespeicherten Nutzerdaten:</h4>');
    $usrdata = $ypum->getUserData();
    var_dump($usrdata);
}
?>

<p><h4>Links:</h4>
<a target="_blank" href='./../ydemo/mydat.php'>Demonstration eines gesch&uuml;tzten Ressourcen-Ordners f&uuml;r Bilder, Dokumente, etc.  (&ouml;ffnet in einem neuen Tab)</a>
<br>
<a href='./../yfront/login.php?suc=Wie+gew%26uuml%3Bnscht+zum+LogIn.'>Wie gew&uuml;nscht zum LogIn.</a>
<br>
<a href='./../yfront/login.php?err=Wegen+eines+%3Cb%3EFehlers%3C%2Fb%3E+zum+LogIn.'>Wegen eines <b>Fehlers</b> zum LogIn.</a>
<br>
<a href='./../yfront/logout.php'>Log-Out</a>
<br>
<a href='./../yback/y-config/index.php'>Konfiguration</a>
<br>
<a href='./../yback/y-admin/usertabelle.php'>Nutzer-Verwaltung</a>
<br>
</p>
</div>