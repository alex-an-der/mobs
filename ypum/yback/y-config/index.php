<?php 
namespace ypum;
require_once(__DIR__."/../ypum.php");
?>
<!DOCTYPE html>
<html lang="de">
<head>
<?php require_once(__DIR__."/../include/inc_main.php");?>
<title>Y-Konfiguration</title>
</head>
<body>
<!-------------------------------------------------------------------->
<?php 
include_once("./../components/navbar_config.php");
?>
<!-------------------------------------------------------------------->
<div class="container">


<h3>Rollen</h3>
Es k&ouml;nnen bis zu 32 Rollen definiert werden.<br>
<p>
Generell sind die Rollen/Rollengruppen/Berechtigungsstufen zwei Bin&auml;nzahlen: eine auf der Seite des Nutzers und eine auf der Seite der PHP-Seite. 
Dabei steht jedes Bit für eine einzelne Rolle. Legen Sie z.B. die Rolle des Admins auf das 1. Bit, Moderatoren auf das 2. Bit und Besucher auf das 3. Bit, 
so h&auml;tte ein normaler Besucher die Rolle 0100 (dezimal: 4) und ein Admin, der auch moderieren darf die Rolle 0011 (dezimal: 3). Eine 
Seite, die ausschließlich von Admins besucht werden darf, erh&auml;lt die Rolle 0001 (dezimal: 1). 
Bei einer Berechtigungsprüfung werden die beiden Zahlen (Nutzer und Seite) je nach Einstellung ALLE oder EINES (unter Sonstiges) miteinander 
verglichen. Bei EINES muss mindestens eine Berechtigung erf&uuml;llt sein, bei ALLE m&uuml;ssen alle Berechtigungsbits gesetzt sein. Ein Admin (0001) 
d&uuml;rfte also bei EINES die Seite 0111 besuchen, nicht jedoch bei der Einstellung ALLE. <i>Bitte beachten Sie, dass die Bitnummerierung mathematisch bedingt bei 0 beginnt - das erste Bit hat also 
die Nummer '0'. N&auml;heres zur Umrechnung bin&auml;r <-> dezimal findet sich im Internet.</p>
</p>
<h3>Seiten</h3>
<p>Die Spalte <b>Genehmigt f&uuml;r diese Rollen</b> zeigt die Summe aller Rollen, die auf die Seite zugreifen dürfen. Ob 
eine Rolle (z.B. Gast ODER Admin) oder alle Rollen (z.B. Mitgliederverwaltung UND Schreibrecht) erf&uuml;llt sein m&uuml;ssen, 
kann unter 'Sonstiges' eingestellt werden. Bleibt man mit der Maus &uuml;ber den Daten, werden die Einzelrollen aufgeschl&uuml;sselt.

<p><b>Filter nach Rolle</b> filtert die Tabelle nach Seiten, die (ggf. unter anderen) eine bestimmte Rolle erfordern.

<p>Mit dem <b>Scanner</b> kann das Webverzeichnis eingelesen werden. Dabei werden die Verzeichnisse und/oder Dateien nur aufgelistet 
werden nicht automatisch &uuml;bernommen. Dies geschieht in der Tabelle. Bei Rollenkonflikten wir dem l&auml;ngeren Pfadnamen priorit&auml;t einger&auml;umt. 
Beispiel: <p>
<u>www.meineseite.de</u> verlangt die Rolle <u>Freunde</u>,<br>
<u>www.meineseite.de/privat</u> verlangt die Rolle <u>Familie</u> und<br>
<u>www.meineseite.de/privat/ich.php</u> die Rolle <u>Privat</u>.<p>
<u>Freunde</u> k&ouml;nnen somit auf den ganzen Ordner <u>www.meineseite.de</u> zugreifen mit Ausnahme
des Ordners <u>www.meineseite.de/privat</u>. Dieser ist wiederrum der Rolle <u>Familie</u> vorbehalten, welche aber nicht die 
Seite <u>www.meineseite.de/privat/ich.php</u> &ouml;ffnen kann. Nur die Rolle <u>Privat</u> kann alles &ouml;ffnen.

<p>Ein Klick in die Tabelle selektiert eine oder mehrere Zeilen, welche unter <b>Auswahl bearbeiten</b> bearbeitet werden k&ouml;nnen.

<p>Ein Klick in der ersten Spalte registriert einen Ordner, eine Datei oder l&ouml;scht - je nach Symbol - die Registrierung wieder. Gr&uuml;ne Zeilen sind registriert, 
rote Zeilen sind registriert aber sind nicht mehr vorhanden.

<h3>Nutzerdaten</h3>
Der interne Name wird d&uuml;r die Benamung der Formularfelder ben&ouml;tigt.
<h3>Dateneingabe</h3>
<p>Der Code wird dynamisch generiert und erzeugt eine lauff&auml;hige PHP-Seite. Diese Seite entspricht der Datei <u>/yfront/register__min.php</u></p>
<p>Das Beispiel-Formular ist der gleiche code, allerdings mit CSS optisch leicht aufgewertet. Diese Seite entspricht der Datei <u>/yfront/register.php</u></p>

<h3>Site-Check-Farbcode</h3>
<b>ROT -> </b>Die Seite hat ypum NICHT eingebunden, obwohl sie in der DB steht<br>
<b>GELB -> </b>Kein Check -> Es kann keine Aussage getroffen werden<br>
<b>GELB -> </b>Es ist ein Verzeichnis. Nur Dateien können direkt in ypum eingebunden werden.<br>
<b>GRÜN -> </b>Die Seite hat ypum eingebunden<br>
<b>GRAU -> </b>Die Seite gibt es nicht mehr<br>

</div>



<!-------------------------------------------------------------------->   
<?php $conf->getFooter();?>
</body>
</html>