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

<?php
include_once(__DIR__."/../components/navbar_install.php");
require_once(__DIR__."/../include/classes/configmanager.php");



$PROD = true;

if(!$im){
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

<title>Projekt einrichten</title>
</head>
<body>


<!-------------------------------------------------------------------->
<!--  TESTE DIE VERBINDUNG, wenn submitted                          -->
<!-------------------------------------------------------------------->
<?php

function toSlash($path){

    $slashed = str_replace("\\", "/", $path);
    return $slashed;
}

$box_html = "";

$ypumSRVpath = realpath(__DIR__."/../../");
$ypumSRVpath = str_replace("\\","/",$ypumSRVpath);
$ypumWEBpath = "https://meineSeite.de/ypum";

$saveOnly = false;

if(isset($_POST['MACH_NUR_SPEICHERN'])) $saveOnly = true;


if(isset($_POST['MACH_DB']) || isset($_POST['MACH_PEPPER']) || isset($_POST['MACH_NUR_SPEICHERN'])){


    if(!$saveOnly){
        // Pepper anlegen
        $newpepperfile = "./../../yconf/pepper";
        $oldpepperfile = "./../../yconf/pepper_last_bak";
        @copy($newpepperfile, $oldpepperfile);
        file_put_contents($newpepperfile,$conf->getRandKey());
    }

    $dbhost = $_POST['db_hostname'];
    $dbname = $_POST['db_dbname'];
    $dbuser = $_POST['db_user'];
    $dbpass = $_POST['db_password'];
    $ypumWEBpath = $_POST['ypumWEBpath'];

    // Zugangsdaten speichern (auch wenn falsch, dann muss nicht alles neu eingegeben werden)
    $zugangsdaten['db_hostname'] = $dbhost;
    $zugangsdaten['db_dbname'] = $dbname;
    $zugangsdaten['db_user'] = $dbuser;
    $zugangsdaten['db_password'] = $dbpass;   
   
    if(strcmp(substr($ypumSRVpath, -1), "/")==0) $ypumSRVpath = substr($ypumSRVpath,0,strlen($ypumSRVpath)-1);
    if(strcmp(substr($ypumSRVpath, -1), "\\")==0) $ypumSRVpath = substr($ypumSRVpath,0,strlen($ypumSRVpath)-1);

    if(strcmp(substr($ypumWEBpath, -1), "/")==0) $ypumWEBpath = substr($ypumWEBpath,0,strlen($ypumWEBpath)-1);
    if(strcmp(substr($ypumWEBpath, -1), "\\")==0) $ypumWEBpath = substr($ypumWEBpath,0,strlen($ypumWEBpath)-1);
    
    $zugangsdaten['ypumSRVpath'] = $ypumSRVpath;
    $zugangsdaten['ypumWEBpath'] = $ypumWEBpath;

    // Unterschied web- und srv-Prefixe speichern
    $kleinereLaenge = min(strlen($ypumSRVpath),strlen($ypumWEBpath));
    $gemeinsamerTeil = "";
    $prefixe = array();
    for($i=1;$i<=$kleinereLaenge;$i++){
        $ni = (-1)*$i;
        $s_part = substr($ypumSRVpath,$ni);
        $w_part = substr($ypumWEBpath,$ni);
        if(strcmp($s_part, $w_part)!=0){
            $s_prefix = toSlash(substr($ypumSRVpath, 0, strlen($ypumSRVpath)-$i+1));
            $w_prefix = toSlash(substr($ypumWEBpath, 0, strlen($ypumWEBpath)-$i+1));
            
            $prefixe['srv_prefix'] = $s_prefix;
            $prefixe['web_prefix'] = $w_prefix;


            $prefixe = json_encode($prefixe);
            file_put_contents(__DIR__."/../../yconf/prefix.json",$prefixe);
            break;
        }
    }

    if (strlen($ypumWEBpath)<1){
        if(isset($_SERVER['SCRIPT_URI'])){
            $ypumWEBpath = substr($_SERVER['SCRIPT_URI'],0,strlen($_SERVER['SCRIPT_URI'])-30);
        }elseif(isset($_SERVER['DOCUMENT_ROOT']) && isset($_SERVER['SCRIPT_FILENAME']) && isset($_SERVER['REQUEST_SCHEME'])&& isset($_SERVER['HTTP_HOST'])){
            $docrootlen = strlen(($_SERVER['DOCUMENT_ROOT']));
            $postfix = substr($_SERVER['SCRIPT_FILENAME'],$docrootlen);
            $lnk = $_SERVER['REQUEST_SCHEME']."://".$_SERVER['HTTP_HOST'].$postfix;
            $ypumWEBpath = substr($lnk,0,strlen($lnk)-30);
        }else{
            $ypumWEBpath = "Automatische Ermittlung fehlgeschlagen.";
        }
        $zugangsdaten['ypumWEBpath'] = $ypumWEBpath;
    }

    $zugangsdaten = json_encode($zugangsdaten);
    file_put_contents(__DIR__."/../../yconf/dbconfig.json",$zugangsdaten);

    $dsn = "mysql:dbname=".$dbname.";host=".$dbhost;
    $options = [ \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION];

    try {

        // Gibt es den angegebenen Webpfad?
        $ok=false;
        $anker="123";
        @$anker = file_get_contents("$ypumWEBpath/yback/anker");
        if(strlen($anker)==42) $ok=true;
        if (!$ok) throw new \PDOException ("Das angegebene ypum-Web-Verzeichnis existiert nicht oder die Verzeichnisstruktur wurde ge&auml;ndert.");

        // Kleiner DB-check (nur check)
        if (strlen($dbhost)<1) throw new \PDOException ("Es wurde kein Host angegeben.");
        $pdo = new \PDO($dsn, $dbuser, $dbpass, $options);
        $result = "Die Verbindung zur Datenbank war erfolgreich.";
        $farbe = "alert-info";

        // Datenbankzugriff
        if(isset($_POST['MACH_DB'])){
            $query = "USE $dbname;";
            $query .= file_get_contents('./query/installquery.sql');
            $stmt = $pdo->prepare($query);
            if($PROD) $res = $stmt->execute();
    
            $args=array();
            $args[]=$conf->getYpumRoot(false)."/yback/y-admin";
            $stmt = $pdo->prepare("INSERT INTO y_sites (dir, roles) value (?, 1);");
            if($PROD) $res = $stmt->execute($args);

            $args=array();
            $args[]=$conf->getYpumRoot(false)."/yback/y-config";
            $stmt = $pdo->prepare("INSERT INTO y_sites (dir, roles) value (?, 1);");
            if($PROD) $res = $stmt->execute($args);

            $args=array();
            $args[]=$conf->getYpumRoot(false)."/yback/y-install";
            $stmt = $pdo->prepare("INSERT INTO y_sites (dir, roles) value (?, 1);");
            if($PROD) $res = $stmt->execute($args);

            $args=array();
            $args[]=$conf->getYpumRoot(false)."/index.php";
            $stmt = $pdo->prepare("INSERT INTO y_sites (dir, roles) value (?, 1);");
            if($PROD) $res = $stmt->execute($args);

            $args=array();
            $args[]=$conf->getYpumRoot(false)."/ydemo";
            $stmt = $pdo->prepare("INSERT INTO y_sites (dir, roles) value (?, 1);");
            if($PROD) $res = $stmt->execute($args);

            $args=array();
            $args[]=$conf->getYpumRoot(false)."/yfront/edit.php";
            $stmt = $pdo->prepare("INSERT INTO y_sites (dir, roles) value (?, 1);");
            if($PROD) $res = $stmt->execute($args);

            $args=array();
            $args[]=$conf->getYpumRoot(false)."/yfront/edit__min.php";
            $stmt = $pdo->prepare("INSERT INTO y_sites (dir, roles) value (?, 1);");
            if($PROD) $res = $stmt->execute($args);
   
        }

            /** 2. Die Datei demo.php und mydat.php anlegen     *************************************/
            $demobody = file_get_contents('vorlage_demo.php');
            $ypumSrv = str_replace("\\","/",$ypumSRVpath);
            $demoheader = "<?php require_once('$ypumSrv/yback/ypum.php');?>";
            $demofile = $demoheader."\n".$demobody;
            file_put_contents($ypumSRVpath."/ydemo/demo.php",$demofile);

            $mydatphp  = "";
            $mydatphp .= "<?php \n";
            $mydatphp .= "\$dir = './MeineDateien';\n";
            $mydatphp .= "include('$ypumSrv/yback/indexof.php');\n";
            $mydatphp .= "?>\n";
            file_put_contents($ypumSRVpath."/ydemo/mydat.php",$mydatphp);
            
        } catch (\PDOException $e) {
            $result = '<b>Verbindung fehlgeschlagen:</b><br>' . $e->getMessage();
            $farbe = "alert-danger";
        }
        $box_html.="<div class='container'>";
        $box_html.="<div class='row'>";
        $box_html.="<div class='alert $farbe' role='alert'>";
        $box_html.=$result;
        $box_html.="</div>";
        $box_html.="</div>";
        $box_html.="</div>";
    
}else{
    // Sonst versuchen, die Daten aus der json-Datei einzulesen (wenn nicht submittet wurde)
    $dbconfig = "./../../yconf/dbconfig.json";
    if(file_exists($dbconfig)){
        $zugangsdaten = file_get_contents($dbconfig);
        $zugangsdaten = json_decode($zugangsdaten,true);
    }

    $dbhost = "";
    $dbname = "";
    $dbuser = "";
    $dbpass = "";

    if(isset($zugangsdaten['db_hostname'])) $dbhost = $zugangsdaten['db_hostname'];
    if(isset($zugangsdaten['db_dbname'])) $dbname = $zugangsdaten['db_dbname'];
    if(isset($zugangsdaten['db_user'])) $dbuser = $zugangsdaten['db_user'];
    if(isset($zugangsdaten['db_password'])) $dbpass = $zugangsdaten['db_password'];
    if(isset($zugangsdaten['ypumSRVpath'])) $ypumSRVpath = $zugangsdaten['ypumSRVpath'];
    if(isset($zugangsdaten['ypumWEBpath'])) $ypumWEBpath = $zugangsdaten['ypumWEBpath'];
}
?>
<!-------------------------------------------------------------------->
<!--  EINGABE-FORMULAR                                              -->
<!-------------------------------------------------------------------->
<div class="container-fluid">
    <div class="container">
        <h2>Konfigurieren der Datenbankverbindung</h2>
        <form method='post'>
            <!------------------------>
            <div class="row">
                <div class="form-group col-12 col-sm-6">
                    <label>Hostname</label>
                    <input type="text" class="form-control" name="db_hostname" value="<?=$dbhost?>">
                </div>

                <div class="form-group col-12 col-sm-6">
                    <label>Name der Datenbank (muss existieren)</label>
                    <input type="text" class="form-control" name="db_dbname" value="<?=$dbname?>">
                </div>
            </div>
            <!------------------------>
            <div class="row">
                <div class="form-group col-12 col-sm-6">
                    <label>Anmeldename</label>
                    <input type="text" class="form-control" name="db_user" value="<?=$dbuser?>">
                </div>

                <div class="form-group col-12 col-sm-6">
                    <label>Passwort</label>
                    <input type="password" class="form-control" name="db_password" value="<?=$dbpass?>">
                </div>
            </div>
            <!------------------------>
            <div class="row">
                <div class="form-group col-12 col-sm-6">
                    <label>ypum-Web-Verzeichnis (https://www.meineSeite.de/ypum)<br></label>
                    <input type="hidden" class="form-control" name="ypumSRVpfad" value="<?=$ypumSRVpath?>">
                    <input type="text" class="form-control" name="ypumWEBpath" value="<?=$ypumWEBpath?>">
                    <label><i><ul><li>Bei einer weitergeleiteten Domain nutzen Sie bitte das <b>Weiterleitungsziel</b> (Webspace-Ordner).</li>
                    <li>Leeren Sie das Feld, um zu versuchen, das Verzeichnis automatisch zu bestimmen.</li></ul></i></label>
                </div>
            </div>
            <!------------------------>
            <div class="row">

                <div class="form-group col-6 text-left">
                <?=$box_html?>
                </div>

                <div class="form-group col-6 text-left">
                    <p><button type="submit" class="btn btn-block btn-primary text-left" name="MACH_NUR_SPEICHERN">
                    Speichern </button>
                    Die Datei demo.php wird bei jedem Speichern neu angelegt.<br>

                    <p><button type="submit" class="btn btn-block btn-warning text-left" name="MACH_PEPPER">
                    Speichern & alle Passw&ouml;rter zur&uuml;cksetzen</button>
                    Es wird ein neuer 'Pepper' generiert, so dass alle Passw&ouml;rter von den Anwendern neu gesetzt werden m&uuml;ssen.</p>

                    <p><button type="submit" class="btn btn-block btn-danger text-left" name="MACH_DB">
                    Speichern und Datenbank-Tabellen neu anlegen</button>
                    <b>Achtung! <u>Alle</u> ypum-Tabellen werden samt Inhalt gel&ouml;scht!</b></p>

                    

                </div>
            </div>


            <!------------------------>
        </form>
    </div>
</div>


<?= $conf->getFooter();?>



</body>
</html>