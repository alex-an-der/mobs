<?php require_once(__DIR__."/yback/ypum.php"); ?>

<?php 
$vversion = '1.0.2'; 
?>



<!DOCTYPE html>
<html lang="de">
<head>

<?php 
echo("<meta charset='UTF-8'>");
echo("<meta name='viewport' content='width=device-width, initial-scale=1, shrink-to-fit=no'>");
echo("<link  href='./yback/lib/bootstrap/css/bootstrap.min.css' rel='stylesheet'></link>");
echo("<script src='./yback/lib/jquery/jquery.js'></script>");
echo("<script src='./yback/lib/bootstrap/js/bootstrap.bundle.js'></script>");
?>

<title>Willkommen bei YPUM</title>

</head>
<body>

  <div class="container-fluid">

    <div class="row m-5">
      <div class="col col-12 text-center">
        <h1>Herzlich willkommen bei <span class="badge badge-pill badge-success">YPUM</span></h1>
        <h2>Yet another PHP-User-Manager
      </div>
    </div>

    <div class="row">

      <div class="col col-12 col-lg-4 mb-3">
        <div class="card h-100">
          <div class="card-body">
            <h4 class="card-title">Was ist YPUM?</h4>
            <p class="card-text">
            Mit YPUM sparen Sie sich das m&uuml;hevolle Erstellen einer eigenen Nutzerverwaltung und k&ouml;nnen sich gleich direkt 
            auf die Programmierung der Kernaufgaben konzentrieren. YPUM l&auml;sst sich einfach konfigurieren und &uuml;bernimmt die Nutzer- und 
            Rechteverwaltung im Hintergrund.
            </p>
          </div>
        </div>
      </div>

   
      <div class="col col-12 col-lg-8  mb-3">
        <div class="card h-100">
          <div class="card-body">
            <h4 class="card-title">Wo finde ich was?</h4>
            <p class="card-text">
            <!-- ------------------------------------------------------------- -->
            <?php 
            $l_def = "<a class='list-group-item list-group-item-action' data-toggle='list' role='tab' "; 
            $c_def = "<div class='tab-pane fade show'role='tabpanel' ";

            ?>
            <div class="row">
              <div class="col-4">
                <div class='list-group' id='list-tab' role='tablist'>
                   <?=$l_def?> id='l_yp' href='#c_yp'>      /ypum</a>
                   <?=$l_def?> id='l_ypi' href='#c_ypi'>    /ypum/index.php</a>
                   <?=$l_def?> id='l_yb' href='#c_yb'>      /yback/</a>
                   <?=$l_def?> id='l_ybs' href='#c_ybs'>    /yconf</a>
                   <?=$l_def?> id='l_yf' href='#c_yf'>      /yfront/</a>
                   <?=$l_def?> id='l_yus' href='#c_yus'>    /ydemo/demo.php</a>
                </div>

              </div>
              <div class='col-8'>
                <div class='tab-content' id='nav-tabContent'>
                  <div class='tab-pane fade show active' >Bitte w&auml;hlen Sie links ein Verzeichnis, um zu sehen, was Sie darin finden.</div>

                  <?=$c_def?> aria-labelledby='l_yp'  id='c_yp' >
                    Das Basisverzeichnis des YPUM-Frameworks. Sie k&ouml;nnen das Verzeichnis mit seinen Unterverzeichnissen beliebig in Ihrem Webverzeichnis platzieren. Wichtig ist jedoch, 
                    dass die darunterliegende Verzeichnisstruktur nicht zerst&ouml;rt wird.
                  </div>

                  <?=$c_def?> aria-labelledby='l_ypi'  id='c_ypi' >
                    Diese Datei. Sie soll Ihnen als Einstieg dienen. Detailiertere Hilfe erfahren Sie jeweils in den Bereichen Installieren, Konfigurieren und Administrieren.
                  </div>

                  <?=$c_def?> aria-labelledby='l_yb'  id='c_yb' >
                    Hierunter verbirgt sich das Backend von YPUM. Unter y-install, y-config und y-admin finden Sie Seiten f&uuml;r den Einrichtungsvorgang, 
                    wie er unten beschrieben wird. In jedem Unterverzeichnis gibt es eine Datei <i>index.php</i>, auf der weitergehende Informationen zu 
                    finden sind.
                  </div>

                  <?=$c_def?> aria-labelledby='l_ybs'  id='c_ybs' >
                    Dieses besondere Verzeichnis ent&auml;lt sensible Konfigurationsdaten wie z.B. das Passwort f&uuml;r die Datenbank. Diese Daten werden durch eine .htaccess-Datei 
                    serverseitig vor unbefugtem Zugriff gesch&uuml;tzt. Da Webprojekte, die auf angemietetem Webspace gehostet werden, meist nicht die M&ouml;glichkeit bieten, 
                    au&szlig;erhalb des Document-Roots zu speichern, wurde diese alternative Schutzma&szlig;nahme gew&auml;hlt. .htaccess gilt derzeit in Verbindung mit 
                    einer aktuellen Webserverversion als sichere Alternative. Durch den Eintrag <i>Deny from all</i> kann das Verzeichnis nur vom Server aus erreicht werden. 
                  </div>

                  <?=$c_def?> aria-labelledby='l_yf'  id='c_yf' >
                    <p>Hier finden Sie alle Seiten, die von Ihnen modifiziert und genutzt werden k&ouml;nnen. Sie k&ouml;nnen diese Seiten auch an 
                    anderer Stelle platzieren, m&uuml;ssen dann jedoch darauf achten, dass Sie die Referenzen zu den eingebundenen Seiten aus 
                    dem Backend entsprechend korrigieren. Einfacher ist es, eine neue Seite an der gew&uuml;nschten Stelle zu platzieren und 
                    die Seiten aus yfront per include zu nutzen (z.B.  &lt;?php include_once("./frameworks/ypum/yfront/register.php")?&gt;).</p>
                    <p><b>pw_new.php</b> l&auml;sst den Nutzer ein Passwort eingeben und <b>register.php</b> nimmt die Daten des Nutzers entgegen. Eventuelle Anpassungen, 
                    z.B. welche Angaben verpflichtend oder freiwillig sind, m&uuml;ssen Sie dementsprechend manuell erg&auml;nzen. Sehen Sie die Dateien in diesem Verzeichnis als 
                    funktionale Rohdateien an, die unter anderem auch Ihrem Seiten-Layout angepasst werden m&uuml;ssen. Die Dateien mit der Endung <i>__min</i> sind frei von jeder 
                    Formatierungsanweisung (CSS).</p>
                  </div>

                  <?=$c_def?> aria-labelledby='l_yus' id='c_yus' >
                    Auf dieser Seite werden die Funktionen des Frameworks demonstriert. Wenn Sie das YPUM konfiguriert und einen Testnutzer erstellt haben,
                    k&ouml;nnen Sie mit diesem auf dieser Seite die YPUM-Funktionen ausprobieren:<p>
                    <ul>
                    <li>Generelles Einbinden und Standard-Zugriffs-Pr&uuml;fung (darf der Anwender die Seite sehen?)</li>
                    <li>Explizite Berechtigungsanfrage</li>
                    <li>Nutzerdaten abfragen</li>
                    <li>Login / Session zerst&ouml;ren / Logout</li>
                    </ul></p>
                  </div>

                </div>
              </div>
            </div>

            <!-- ------------------------------------------------------------- -->  


            </p>
          </div>
        </div>
      </div>

    </div>
    <div class="row">

      <div class="col col-12 col-sm-6 col-lg-4 mb-3">
        <div class="card h-100">
          <div class="card-body">
            <h4 class="card-title">Installieren</h4>
            <p class="card-text">
            Die YPUM-Verzeichnisse k&ouml;nnen in ein beliebiges Unterverzeichnis auf dem Web-Laufwerk abgelegt werden. Es wird dringend empfolen
            die vorgegebene Verzeichnisstruktur beizubehalten. Nichtsdestotrotz ist YPUM quelloffen und daher frei anpassbar.
            YPUM ben&ouml;tigt eine mySQL oder Maria-DB. Diese muss zun&auml;chst in der Installation festgelegt werden. Die Installationsseiten 
            befinden sich im Ordner y-install.
            </p>
            <a href='./yback/y-install/index.php'>Hier geht es zur Installation</a>
            
          </div>
        </div>
      </div>

      <div class="col col-12 col-sm-6 col-lg-4 mb-3">
        <div class="card h-100">
          <div class="card-body">
            <h4 class="card-title">Konfigurieren</h4>
            <p class="card-text">
            Hier werden alle Einstellungen get&auml;tigt, die f&uuml;r Ihr Webprojekt gelten sollen, z.B. 
            <ul>
            <li>Welche Berechtigungsstufen gibt es?</li>
            <li>Welche Nutzerdaten sollen ausgenommen werden?</li>
            <li>Mit welchem Text versenden Sie vergessene Passw&ouml;rter?</li>
            <li>Sollen Passw&ouml;rter eine Mindestl&auml;nge haben?</li>
            <li>usw....</li>
            </ul>
            Die Konfigurationsseiten befinden sich im Ordner y-config. 
            </p>
            <a href='./yback/y-config/index.php'>Hier geht es zur Konfiguration</a>
         
          </div>
        </div>
      </div>

      <div class="col col-12 col-sm-6 col-lg-4 mb-3">
        <div class="card h-100">
          <div class="card-body">
            <h4 class="card-title">Administrieren</h4>
            <p class="card-text">
            Hier k&ouml;nnen Sie dann schlie&szlig;lich alle Nutzerdaten &auml;ndern, Rollen zuweisen oder auch Nutzer l&ouml;schen. 
            Die Seiten zur Administration befinden sich im Ordner y-admin.
            </p>
            <a href='./yback/y-admin/index.php'>Hier geht es zur Nutzerverwaltung</a>
           
          </div>
        </div>
      </div>
    </div>
  </div>
 
</body>
</html>
