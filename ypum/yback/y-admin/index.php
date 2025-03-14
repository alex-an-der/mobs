<?php require_once(__DIR__."/../ypum.php");?>
<!DOCTYPE html>
<html lang="de">
<head>
<?php require_once(__DIR__."/../include/inc_main.php");?>  
<title></title>
</head>
<body>
<!-------------------------------------------------------------------->
<?php include_once(__DIR__."/../components/navbar_userverwaltung.php");?>
<!-------------------------------------------------------------------->
<div class="container">
    <h3>&Uuml;bersicht</h3>
    <ul>
    <li>Saplten k&ouml;nnen verschoben (Spaltenkopf nach links/rechts ziehen), sowie ein- und ausgeblendet werden.</li>
    <li>Daten k&ouml;nnen direkt in der Tabelle ge&auml;ndert werden. Bei &Auml;nderungen wird das Feld gelb. Ein Doppelklcik schreibt den EIntrag in die Datenbank. 
    Erfolgreiche Schreibvorg&auml;nge werden gr&uuml;n gekennzeichnet, nicht erfolgreiche (z.B. doppelte Mailadresse) rot.</li>
    <li>Sind mehrere Zeilen ausgew&auml;hlt, kann die &Auml;nderung wahlweise auf alle ausge&auml;hlte Zeilen angewandt werden.</li>
    <li>Wird auf die Rollen-Spalte geklickt, wird der Rollencode mit Hilfe der Rollenanzeige im oberen Bereich aufgedr&ouml;selt.</li>
    <li>Die Rollenanzeige hilft auch, aus mehreren Rollen den entsprechenden Gesamtcode zu erzeugen. Dieser muss dann manuell in die Tabelle eingetragen werden.</li>
    </ul>
    <h3>Register</h3>
    Hier k&ouml;nnen Nutzer manuell angelegt werden. Mit dem Speichern der Daten wird eine Mail an den Nutzer gesendet,
    der die Mailadresse best&auml;tigt und zur Eingabe eines neuen Passworts auffordert.
    <h3>Wie kann ich YPUM einbinden?</h3>
    <ul>
    <li>require-link (s. Sonstiges bei der Konfiguration - Link hier rein)</li>
    <li>yfront-Seiten</li>
    <li>Klassenmethoden erkl&auml;ren.</li>
    </ul>
</div>


<!-------------------------------------------------------------------->   
<?php $conf->getFooter();?>
</body>
</html>