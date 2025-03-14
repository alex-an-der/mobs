<?php 
require_once(__DIR__."/../yback/include/inc_main.php");
// Der Token muss IMMER mit der User-ID korrelieren

if(!isset($_GET['token'])){

    $err = "Ung&uuml;ltiger Aufruf";
    showError($err);
    die();
    
}elseif(!isset($_GET['id'])){

    $err = "Ung&uuml;ltiger Aufruf";
    showError($err);
    die();

}else{

    require_once(__DIR__."/../yback/include/inc_main.php");
    
    $args = array();
    $args[] = $_GET['id'];
    $args[] = $_GET['token'];   
    if ($auslaufmodell = $conf->load("divers")){ 
        $args[] = $auslaufmodell['tokenstunden'];
    }else{
        $args[] = 24;
    }
    
    $ok = $dbm->exists("SELECT ID FROM y_user WHERE id=? and token=? and UNIX_TIMESTAMP(now())-UNIX_TIMESTAMP(tokencreated)<(3600*?)",$args);

    // PW neu angelegt
    $data = $conf->load("divers");
    @$minlength = $data['minlength'];

    if(isset($_POST['submitpw'])){

        if(empty($_POST['pw1'])){
            $err =  ("Bitte geben Sie ein Passwort ein.<br>");
            include_once("pw_new.php");
        }elseif(strcmp($_POST['pw1'],$_POST['pw2']) !=0 ){
            $err =  ("Die eingegeben Passwörter stimmen nicht überein.<br>");
            include_once("pw_new.php");
        }elseif(!isset($_GET['id']) || !isset($_GET['token'])){
            $err = ("Die URL wurde manipuliert. Bitte verwenden Sie den Originallink.<br>");
            include_once("pw_new.php");
        }elseif(strlen($_POST['pw1'])<$minlength){
            $err = ("Das Passwort muss mindestens $minlength Zeichen lang sein.<br>"); 
            include_once("pw_new.php");
        }else{
            
            $pw = $_POST['pw1'];
            
            $pepper = file_get_contents($conf->getSecDir()."/pepper");
            $hash = password_hash($pw.$pepper, PASSWORD_DEFAULT);
            
            $args = array();
            $args[] = $_GET['id'];  
            $rollenVorhanden = $dbm->exists("SELECT ID FROM y_user WHERE id=? and roles>0 ",$args);

            if($rollenVorhanden){
                $args = array();
                $args[] = $hash;
                $args[] = $_GET['id'];  
                $dbm->query("update y_user set password=?, token=null where ID=?",$args,false);
                $suc = "Das Passwort wurde erfolgreich ge&auml;ndert. Sie k&ouml;nnen sich damit jetzt einloggen.";
            }else{
                $args = array();
                $initrolle = pow(2, $data['initrolle']);
                $args[] = $hash;
                $args[] = $initrolle;
                $args[] = $_GET['id'];  
                $dbm->query("update y_user set password=?, token=null, roles=? where ID=?",$args,false);
                $suc = "Ein neues Passwort wurde erfolgreich erstellt. Sie k&ouml;nnen sich damit jetzt einloggen.";
            }

            $conf->redirect($conf->getYpumRoot()."/yfront/login.php?suc=".urlencode($suc));
        }
    }
    elseif($ok){
        $args = array();
        $args[] = $_GET['id'];
        $dbm->query("update y_user set validated=NOW() where id=?;",$args,false);
        include_once("pw_new.php");
    }else{
        $err = "Ungültiger Token oder Token abgelaufen.";
        showError($err);
        die();
    }
    
}

function showError($err){
    echo "<div class='alert alert-danger' role='alert'>";
    echo $err;
    echo "</div>";

}
?>



