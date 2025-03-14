<?php namespace ypum; 
require_once(__DIR__."/../include/classes/configmanager.php");

// Wenn noch keine DB eingerichtet ist, läuft ypum auf eine Exception
$conf=new configmanager();
$im = $conf->isInstallmodus();
if(!$im) require_once(__DIR__."/../ypum.php");
else require_once(__DIR__."/../noypum.php");

?>
<!DOCTYPE html>
<html lang="de">
<head>
<meta charset='UTF-8'>
<meta name='viewport' content='width=device-width, initial-scale=1, shrink-to-fit=no'>

<link  href='./../lib/bootstrap/css/bootstrap.min.css' rel='stylesheet'></link>
<script src='./../lib/jquery/jquery.js'></script>
<script src='./../lib/bootstrap/js/bootstrap.js'></script>


<!-------------------------------------------------------------------->

<?php 

include_once(__DIR__."/../components/navbar_install.php");
//require_once(__DIR__."/../include/classes/configmanager.php");

$l_def = "<a class='list-group-item list-group-item-action' data-toggle='list' role='tab' "; 
$c_def = "<div class='tab-pane fade show'role='tabpanel' ";

$c = new configmanager();
$installmodus = $c->isInstallmodus();

if(!$installmodus){
    $box_html ="<div class='container'>";
    $box_html.="<div class='row'>";
    $box_html.="<div class='alert alert-danger' role='alert'>";
    $box_html.="YPUM wurde bereits eingerichtet. Bitte &auml;ndern Sie ggf. die Parameter in der <a href='./../y-config/index.php'>Konfiguration</a>.";
    $box_html.="</div>";
    $box_html.="</div>";
    $box_html.="</div>";
    echo $box_html;
    die();
}

?>
</head>
<body>


<!-------------------------------------------------------------------->
<div class="container">
    <div class="row">
        <div class="col-4">
            <div class='list-group' id='list-tab' role='tablist'>
                <?=$l_def?> id='l_01' href='#c_01'> Funktionsweise</a>
                <?=$l_def?> id='l_02' href='#c_02'> Besonderheiten</a>
                <?=$l_def?> id='l_03' href='#c_03'> Datenbank</a>
                <?=$l_def?> id='l_04' href='#c_03'> Wie geht es weiter?</a>
            </div>
        </div>
        
        <div class='col-8'>
            <div class='tab-content' id='nav-tabContent'>

                <div class='tab-pane fade show active' ><p>Willkommen bei der Grundinstallation von YPUM.</p><p>Bitte w&auml;hlen Sie links ein Hilfethema.</p></div>

                <?=$c_def?> aria-labelledby='l_01'  id='c_01' >
                    Mit YPUM k&ouml;nnen Sie sowohl den registrierten Nutzern, sowie den Seiten eine oder mehrere Berechtigungsstufen zuweisen. 
                    Wenn eine oder alle (je nach Einstellung) Berechtigungsstufen &uuml;bereinstimmen, wird die Seite angezeigt, sonst nicht. 
                    &Uuml;ber eine Klasse k&ouml;nnen Sie auch explizit anfragen, ob der angemeldete Nutzer &uuml;ber eine bestimmte Berechtigungsstufe/Rolle 
                    verf&uuml;gt, z.B. um bestimmten Inhalt ein- oder auszublenden. Mehr dazu bei der Administration.
                </div>

                <?=$c_def?> aria-labelledby='l_02'  id='c_02' >
                    Zwei Besonderheiten sind zu beachten:<p>
                    <ol>
                    <li>Einstellungen und Verbindungsdaten werden im Verzeichnis <i>/yconf</i> abgelegt. Dieses Verzeichnis muss durch eine entsprechende 
                    .htaccess-Datei abgesichert werden, um unberechtigten Zugriff zu verhindern. YPUM beinhaltet bereits eine geeignete Datei in diesem 
                    Verzeichnis.</li><p>
                    <li>Nach erfolgreicher Installation wird aus dem Verzeichnis <i>/yconf</i> die Datei <i>install</i></li> entfernt. Ohne dieser Datei wird aus 
                    Sicherheitsgr&uuml;nden keine Installation gestartet. Soll YPUM erneut installiert werden, muss in das Verzeichnis <i>/yconf</i> 
                    eine Datei <i>install</i>(ohne Endung und Inhalt) gelegt werden. <b>Achtung</b> Wenn sich eine solche Datei dort befindet, kann jeder mit Zugriff auf 
                    das Verzeichnis y-install eine neue Installation starten und alle Daten somit unwiderbringlich l&ouml;schen.</li>
                    </ol>
                </div>              

                <?=$c_def?> aria-labelledby='l_03'  id='c_03' >
                    <p>Unter dem Menupunkt <i>Datenbank</i> k&ouml;nnen Sie die Datenbankverbindung konfigurieren. In dieser Datenbank werden von YPUM 
                    verschiedene Tabellen angelegt, die zur Nutzerverwaltung ben&ouml;tigt werden. Sie k&ouml;nnen in Ihrem Projekt eine von YPUM 
                    getrennte Datenbank verwenden oder die selbe nutzen. Die YPUM-Tabellen beginnen alle mit 'y_' (y_user, y_sites, ...). <b>Achtung!</b> 
                    Bei der Einrichtung von YPUM werden solche m&ouml;glicherweise bereits vorhandenen Tabellen ohne erneuter Warnung &uuml;berschrieben.</p>

                    <p>Unter <i>Schnittstellenklasse</i> geben Sie bitte den Ort der Datei <i>ypum.php</i> an. Der Standardpfad ist bereits eingetragen.</p>
                </div>

                <?=$c_def?> aria-labelledby='l_03'  id='c_03' >
                    Der Menupunkt <i>Datenbank</i> f&uuml;hrt Sie direkt zur Konfiguration. M&ouml;chten Sie die Installation wiederholen, beachten Sie bitte den 
                    Hilfe-Punkt <i>Besonderheiten</i>.
                </div>

            </div>
        </div>
    </div>
</div>

<!-------------------------------------------------------------------->   
<?= $c->getFooter();?>
</body>
</html>