<?php require_once(__DIR__."/../ypum.php");?>
<!DOCTYPE html>
<html lang="de">
<head>

<title>Sonstige Einstellungen</title>


<?php
$posX = 0;
$posY = 0;

if(isset($_POST['px'])) $posX = $_POST['px'];
if(isset($_POST['py'])) $posY = $_POST['py'];

?>

<script>

$(document).ready(function() {
    $('.triggerData').change(function() { 
        console.log("EVENT");
        $('#screennpos_x').val(window.pageXOffset);
        $('#screennpos_y').val(window.pageYOffset);
        $('#formular').submit();
    });

    pX = <?=$posX?>;
    pY = <?=$posY?>;
    window.scrollBy(pX,pY);
    console.log(pX + "/" + pX);
});



</script>

</head>
<body class="bg-secondary">
<!-------------------------------------------------------------------->
<?php include_once(__DIR__."/../components/navbar_config.php");?>
<!-------------------------------------------------------------------->

<?php




$ypumdatei = $conf->getYpumRoot(false)."/yback/ypum.php";
$ypumdateicpy = str_replace("\\","/",$ypumdatei);
$ypumdateicpy = "<?php require_once(\'$ypumdateicpy\');?>";

$noypumdatei = $conf->getYpumRoot(false)."/yback/noypum.php";

$hta = "<pre><code><br>";
$hta .= "Require all denied<br>";
$hta .= "Deny from all<br>";
$hta .= "</code></pre>";

$indexOf = "<pre><code><br>";
$indexOf .= "&lt;?php<br>";
$indexOf .= "\$dir = \"<i>./Server-Pfad_zum_Ordner</i>\";<br>";
$indexOf .= "require_once(\"".$conf->getYpumRoot(false)."/yback/indexof.php\");<br>";
$indexOf .= "?&gt;<br>";
$indexOf .= "</code></pre>";


/*
if(isset($_POST['MACH_SPEICHERN'])){

    if(empty($_POST['startseite'])) $_POST['startseite'] = $conf->getYpumRoot()."/ydemo/demo.php";

    $data['tokenstunden'] = $_POST['tokenstunden'];
    $data['minlength'] = $_POST['minlength'];
    $data['initrolle'] = $_POST['initrolle'];
    $data['startseite'] = $_POST['startseite'];
    $data['sessionrenew'] = $_POST['sessionrenew'];
    $data['sessiondiscard'] = $_POST['sessiondiscard'];

    $data['allerollen'] = $_POST['allerollen'] == "alle" ? true : false;

    $conf->save("divers", $data);
}
*/
$data = $conf->load("divers");
@$tokenstunden = $data['tokenstunden'];
@$minlength = $data['minlength'];
@$initrolle = $data['initrolle'];
@$startseite = $data['startseite'];
@$sessionrenew = $data['sessionrenew'];
@$sessiondiscard = $data['sessiondiscard'];


$selAlle = "";
$selEine = "";
if($data['allerollen']) $selAlle = "selected";
else $selEine = "selected";
// --------------------------------------------------------------------------- 
$rollen=$dbm->query("SELECT bit, name from y_roles order by bit asc");
$rollenangebot = "";
foreach($rollen as $rolle){
    
	$rbit = $rolle['bit'];
    $selected = "";
    if($rbit==$initrolle) $selected = "selected";
    $rname = $rolle['name'];
	$id1 = "rolopt$rbit";
	$rollenangebot .= "<option $selected class='rollenoptionen' id=$id1 value='$rbit'>#$rbit: $rname</option>";
}


$rowclasses = "mb-5 p-3 bg-light align-items-center";
?>

<div class="container">
<form id='formular' method='post' action='./diverses_save.php'>

<input hidden class='form-control' name='screennpos_x' id='screennpos_x' value='0'>
<input hidden class='form-control' name='screennpos_y' id='screennpos_y' value='0'>

    <div class="row <?=$rowclasses?>">
        <div class="col col-12 ">
            <p>Sie binden YPUM auf Ihren Seiten ein, indem Sie diesen Code als <b>erste Zeile</b> nach einem evtl. Namespace einbinden. Es wird immer eine 
            Session gestartet, ein erneutes Starten ist also &uuml;berfl&uuml;ssig.</p>
            
            <p><b>&lt;&quest;php require_once("<?=$ypumdatei?>");&quest;&gt;</b></p>
           
            <p>Bitte beachten Sie, dass Sie je nach Einstellungen Ihres Webservers ggf. mit relativen Pfaden arbeiten
            m&uuml;ssen. Sie k&ouml;nnen nat&uuml;rlich auch eine existierende Autoprepend-Datei nutzen. Beachten Sie dabei, dass 
            YPUM Server-Sessions nutzt und daher ganz zu Beginn eingef&uuml;gt werden muss.</p>

            <p>Um eine Seite absichtlich nicht in die ypum-Authentifizierung einzubinden, k&ouml;nnen Sie die noypum.php-Seite einbinden. 
            So teilen Sie dem Site-Scanner mit, dass dies bewusst geschieht, vermeiden Warnungen und 
            verlieren nicht den &Uuml;berblick.</p>

            <p><b>&lt;&quest;php require_once("<?=$noypumdatei?>");&quest;&gt;</b></p>

        </div>
        <!--div class='col col-4'>
        <button class="btn btn-secondary" onclick='cpyToClpb(1)'>Zeile kopieren</button>
        </div-->
    </div>

    <div class="row <?=$rowclasses?>">
        <div class="col col-12 ">
            Direkt k&ouml;nnen Sie mit ypum nur php-Seiten sch&uuml;tzen. Es gibt jedoch auch einen Weg, mit dem Sie beliebige Ordner mit z.B. 
            Bilder oder pdf-Dokumente sch&uuml;tzen k&ouml;nnen. Dazu wird das Verzeichnis mit einer .htaccess-Datei gesch&uuml;tzt. Ein ypum-indexOf-Modul 
            greift dann serverseitig auf die Inhalte zu und kann wie gewohnt im Berechtigungskonzept verwaltet werden. Der Ort dieses Moduls ist dabei unabh&auml;ngig vom zu 
            sch&uuml;tzenden Ressourcen-Ordner. Die Vorgehensweise im Einzelnen:
            <p><ol>
            <li><b>Erstellen Sie im zu sch&uuml;tzenden Verzeichnis eine .htaccess-Datei mit folgendem Inhalt:</b><br>
            <?=$hta?></li>
            <li><b>Erstellen Sie eine php-Datei mit folgendem Inhalt in einem beliebigen anderen Verzeichnis, um den Inhalt des gesch&uuml;tzten Ordners anzuzeigen:</b><br>
            <?=$indexOf?></li>
            </ol></p>
            Anmerkungen:
            <ul>
            <li>Als Pfad m&uuml;ssen Sie einen Server-Pfad eingeben, Sie k&ouml;nnen nicht mit URLs (http://...) arbeiten. Dieser Pfad kann absolut oder relativ sein.</li>
            <li>Vergessen Sie nicht, f&uuml;r die IndexOf-Seite in der Konfiguration Rollen zuzuweisen. Die ypum-Einbindung ist im indexOf-Modul bereits enthalten.</li>
            </ul>
        </div>
        <!--div class='col col-4'>
        <button class="btn btn-secondary" onclick='cpyToClpb()'>Zeile kopieren</button>
        </div-->
    </div>

    <div class="row <?=$rowclasses?>">
        <div class="col col-6 ">
            Auf welche Seite soll der Besucher nach einem erfolgreichen Login geleitet werden? 
            Bitte stellen Sie sicher, dass alle Nutzer auf dieser Seite berechtigt sind. <i>Wenn Sie das 
            Feld leer lassen, wird automatisch die Demo-Seite eingetragen.</i>
        </div>
        <div class='col col-6'>
            <input type='text' class='form-control triggerData' name='startseite'  aria-describedby='basic-addon3' value='<?=$startseite?>'>
        </div>
    </div>

     <div class="row <?=$rowclasses?>">
        <div class="col col-6 ">
            Bei einer Neuanmeldung oder bei einem vergessenen Passwort bekommt der Nutzer einen Token zugeschickt, 
            womit er sich dann ein neues Passwort anlegen kann. Wie lange soll dieser Token g&uuml;ltig sein?
        </div>
        <div class='col col-2'>
            <input required type='number' class='form-control triggerData' name='tokenstunden' aria-describedby='basic-addon3' value='<?=$tokenstunden?>'>
        </div>
        <div class='col col-4'>
        <span class='input-group-text'>Stunden</span>
        </div>
    </div>

    <div class="row <?=$rowclasses?>">
        <div class="col col-6">
            Mindestl&auml;nge eines Passwortes:
        </div>
        <div class='col col-2'>
            <input required type='number' class='form-control triggerData' name='minlength'  aria-describedby='basic-addon3' value='<?=$minlength?>'>
        </div>
        <div class='col col-4'>
        <span class='input-group-text'>Zeichen</span>
        </div>
    </div>

    <div class="row <?=$rowclasses?>">
        <div class="col col-6">
            Nach welcher Zeit soll die Session-ID erneuert werden? Kurze Zeiten sind sicherer, k&ouml;nnen aber bei 
            paralleler Session-Nutzung (Browser-Tabs, Mobilger&auml;te) zu Session-Abbr&uuml;chen f&uuml;hren.
        </div>
        <div class='col col-2'>
            <input id='ttt' required type='number' class='form-control triggerData' name='sessionrenew'  aria-describedby='basic-addon3' value='<?=$sessionrenew?>'>
        </div>
        <div class='col col-4'>
        <span class='input-group-text'>Minuten</span>
        </div>
    </div>

    <div class="row <?=$rowclasses?>">
        <div class="col col-6">
            Nach welcher Zeit der Inaktivit&auml;t (kein Aufruf einer registrierten Seite) 
            soll die Session automatisch ung&uuml;ltig werden? Nach dieser Zeit muss sich der Nutzer erneut einloggen.
        </div>
        <div class='col col-2'>
            <input required type='number' class='form-control triggerData' name='sessiondiscard'  aria-describedby='basic-addon3' value='<?=$sessiondiscard?>'>
        </div>
        <div class='col col-4'>
        <span class='input-group-text'>Minuten</span>
        </div>
    </div>

    <div class="row <?=$rowclasses?>">
        <div class="col col-6 ">
            Welche Rollen soll ein Nutzer standardmäßig erhalten?
        </div>
        <div class='col col-6'>
        <select required name="initrolle" class="form-control triggerData">
            <?=$rollenangebot?>
        </select>
        </div>
    </div>

    <div class="row <?=$rowclasses?>">
        <div class="col col-8 ">
            <p>Sowohl eine Seite als auch ein Anwender kann mehrere Rollen besitzen. Welches Konzept m&ouml;chten Sie verfolgen?</p>
            <ul>
            <li><p><b>ALLE:</b> Der Anwender muss <b>alle</b> angegebenen Rollen besitzen (UND-Verkn&uuml;pfung).<br><i>Beispiel: Der Anwender muss 
            f&uuml;r die Seite xy sowohl f&uuml;r die Buchhaltung als auch f&uuml;r die Mitgliederverwaltung berechtigt sein.</i></p></li>
            <li><p><b>EINE:</b> Der Anwender muss <b>eine</b> angegebenen Rollen besitzen (ODER-Verkn&uuml;pfung).<br><i>Beispiel: Der Anwender muss  
            f&uuml;r die Seite xy entweder als Mitgliederverwalter oder als Admin berechtigt sein.</i></p></li>
            </ul>
        </div>
        <div class='col col-4'>
        <select required name="allerollen" class="form-control triggerData">
            <option <?=$selAlle?> value="alle">Alle (UND)</option>
            <option <?=$selEine?> value="eine">EINE (ODER)</option> 
        </select>
        </div>
    </div>

<br>

    <div class="row <?=$rowclasses?>">
        <div class="col col-8">
          
        </div>
        <div class="col col-4">
            <button class="btn btn-primary" name="MACH_SPEICHERN">Speichern</button>
        </div>
</form>
</div>


<!-------------------------------------------------------------------->   
<?php $conf->getFooter();?>

<script>
function cpyToClpb(wasdenn){

    var tempItem = document.createElement('input');

    tempItem.setAttribute('type','text');
    tempItem.setAttribute('display','none');
    console.log(wasdenn);
    let inhalt = "";
    if(wasdenn==1) {inhalt = "<?=$ypumdateicpy?>";}
    console.log(inhalt);
  
    tempItem.setAttribute('value',inhalt);
    document.body.appendChild(tempItem);
    
    tempItem.select();
    document.execCommand('Copy');

    tempItem.parentElement.removeChild(tempItem);
}

</script>


</body>
</html>