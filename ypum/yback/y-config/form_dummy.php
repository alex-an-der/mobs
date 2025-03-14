<?php 
namespace ypum;
require_once(__DIR__."/../ypum.php");
require_once(__DIR__."/../include/inc_main.php");
?>  
<!DOCTYPE html>
<html lang="de">
<head>
<title></title>
</head>
<body>
<!-------------------------------------------------------------------->
<?php include_once(__DIR__."/../components/navbar_config.php");?>
<!-------------------------------------------------------------------->
<?php 
include_once(__DIR__."/../include/code/form_template_string.php");
include_once(__DIR__."/../include/code/form_edit_template_string.php");
?>
<!-------------------------------------------------------------------->
<?php
$proddatei = "register.php";
$proddateimin = "register__min.php";

$prodeditdatei = "edit.php";
$prodeditdateimin = "edit__min.php";

$html_bst = $html;
$htmledit_bst = $htmledit;

$prependfile = realpath(__DIR__."/../include/inc_main.php");

$html_bst = str_replace("</head>", "<?php require_once('$prependfile');?>&#13;&#10;</head>", $html_bst);
$html_bst = str_replace("<body>", "<body>&#13;&#10;<div class='container'><div class='row'>", $html_bst);
$html_bst = str_replace("</form>", "</form>&#13;&#10;</div></div>", $html_bst);
$html_bst = str_replace("type='submit'", "type='submit' class='btn btn-success btn-block'", $html_bst);
$html_bst = str_replace("<input", "<input class='form-control'", $html_bst);
$html_bst = str_replace("&#13;&#10;", "\n", $html_bst);

$htmledit_bst = str_replace("<body>", "<body>&#13;&#10;<div class='container'><div class='row'>", $htmledit_bst);
$htmledit_bst = str_replace("</form>", "</form>&#13;&#10;</div></div>", $htmledit_bst);
$htmledit_bst = str_replace("type='submit'", "type='submit' class='btn btn-success btn-block'", $htmledit_bst);
$htmledit_bst = str_replace("<input", "<input class='form-control'", $htmledit_bst);
$htmledit_bst = str_replace("&#13;&#10;", "\n", $htmledit_bst);

$html_min = str_replace("&#13;&#10;", "\n", $html);
$htmledit_min = str_replace("&#13;&#10;", "\n", $htmledit);


if(isset($_POST['MACH_KOPIE'])){

    $handle = fopen (__DIR__."/../../yfront/".$proddatei, "w");
    fwrite ($handle, $html_bst);
    fclose ($handle);

    $handle = fopen (__DIR__."/../../yfront/".$proddateimin, "w");
    fwrite ($handle, $html_min);
    fclose ($handle);

    $handle = fopen (__DIR__."/../../yfront/".$prodeditdatei, "w");
    fwrite ($handle, $htmledit_bst);
    fclose ($handle);

    $handle = fopen (__DIR__."/../../yfront/".$prodeditdateimin, "w");
    fwrite ($handle, $htmledit_min);
    fclose ($handle);
}

// Alte tmp-Dateien löschen
$dateien = glob(__DIR__."/../tmp/demoform*"); // Dateienamen holen
foreach($dateien as $datei){ 
  if(is_file($datei))
    unlink($datei); 
}

$dateiname = __DIR__."/../tmp/demoform_".time().".php";
$handle = fopen ($dateiname, "w");
fwrite ($handle, $html_bst);
fclose ($handle);
?>

<div class="container">

    <!--h2>Eine rudiment&auml;re Dateneingabe der Nutzerdaten:</h2>
    <div class="container">
        <div class="row">
        
            <div class="col col-6">
                < ?php 
                    //include_once($dateiname);
                    //unlink($dateiname);
                ?>
                <textarea  rows=20 class="form-control w-100">< ?=$html?></textarea>
            </div-->

  
            
            <div class="col col-12">
                <form method='post'>
                    <div class="alert alert-info" role="alert">
                        Die Eingabeseiten, die Sie in Ihr Webprojekt einbinden k&ouml;nnen, befinden sich im Ordner <b>/yfront</b>. Da sich
                        dieses Formulare dynamisch Ihren Einstellungen anpassen, k&ouml;nnen Sie selbst entscheiden, ob
                        Sie &Auml;nderungen manuell in Ihrer ggf. bereits angepassten Seite nachziehen oder dieses Basis-Formular
                        erneut kopieren. Die <b>Register</b>-Formulare sind für eine Neuregistrierung gedacht, die <b>Edit</b>-Formulare für 
                        bereits bestehende Nutzer (erfordern entsprechend ein LogIn).
                    </div>
                    <p>
                        <button type='submit' name='MACH_KOPIE' class='btn btn-success btn-block'>Die Formulare generieren</button>
                    </p>
                    <div class="alert alert-warning" role="alert">
                        <!--b>Achtung!</b> Die m&ouml;glicherweise bereits vorhandene Dateien <br><b>/yfront/?=$proddatei?></b> wird damit überschrieben.--->
                        <b>Achtung!</b> Die m&ouml;glicherweise bereits vorhandene Dateien <ul><b>
                        <li><a target='_blank' href='./../../yfront/<?=$proddatei?>'>/yfront/<?=$proddatei?></a></li>
                        <li><a target='_blank' href='./../../yfront/<?=$proddateimin?>'>/yfront/<?=$proddateimin?></a></li>
                        <li><a target='_blank' href='./../../yfront/<?=$prodeditdatei?>'>/yfront/<?=$prodeditdatei?></a></li>
                        <li><a target='_blank' href='./../../yfront/<?=$prodeditdateimin?>'>/yfront/<?=$prodeditdateimin?></a></li>
                        </b></ul>werden damit &uuml;berschrieben. <i>Hinweis: Die min-Dateien verzichten 
                        zugunsten der &Uuml;bersichtlichkeit auf Bootstrap-Formatierungen.
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!-------------------------------------------------------------------->
<?php $conf->getFooter();?>
</body>
</html>
