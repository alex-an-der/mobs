<?php 
session_start();
$_SESSION = array();
session_destroy();
ini_set('session.use_strict_mode', 1);
@session_start();
require_once(__DIR__."/../yback/include/inc_main.php");

$ypdir = $conf->getYpumRoot();

?>
<!DOCTYPE html>
<html lang="de" class="h-100">
<head>
<!-- Favicon and mobile web app settings -->
<link rel="icon" href="<?=$ypdir?>/../inc/img/mobs.jpg" type="image/jpeg">
<link rel="apple-touch-icon" href="<?=$ypdir?>/../inc/img/mobs.jpg">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<meta name="apple-mobile-web-app-title" content="Login">
<!-- For Android devices -->
<meta name="theme-color" content="#ffffff">

<link href="<?=$ypdir?>/yback/lib/bootstrap_toggles/css/bootstrap4-toggle.css" rel="stylesheet">
<script src="<?=$ypdir?>/yback/lib/bootstrap_toggles/js/bootstrap4-toggle.js"></script>

<script>
$(document).ready(function() {
 
    // Listener Toggle (Show PW)
    $("#show_pw").change(function(){
        if(document.getElementById('show_pw').checked){
            document.getElementById('pw').type = 'password';
        }else{
            document.getElementById('pw').type = 'text';
        }   
    });
});

</script>

<title>Log-In</title>
<?php

$suc = "";

if(isset($_POST['smLOGIN'])){
    $pepper = file_get_contents(__DIR__."/../yconf/pepper");
    $args = array();
    $args[] = strtolower($_POST['usermail']);
    $args[] = 0;
    $user = $dbm->queryOne("select roles, id, password from y_user where mail=? and locked=?",$args,true);
    $usercount = $dbm->queryOne("select count(*) as usercount from y_user");
   
    if(isset($user['password']) && isset($_POST['pw'])){
        if(password_verify ( $_POST['pw'].$pepper , $user['password'] )){
            $_SESSION['usercount'] = $usercount['usercount'];
            $_SESSION['uid'] = $user['id'];
            $_SESSION['uroles'] = $user['roles'];
            $_SESSION['SESS_created'] = time();
            $_SESSION['ID_created'] = time();
            $data = $conf->load("divers");
            $startseite = $data['startseite'];
            $args = array();
            $args[] = $user['id'];
            $dbm->query("update y_user set lastlogin=NOW() where id=?",$args, false);
            $conf->redirect($startseite);
  
            exit();

        }else{
            $err = "Die Anmeldeinformationen sind nicht korrekt.";
        }
    }else{
        $err = "Die Anmeldeinformationen sind nicht korrekt.";
    }
}

if(isset($_POST['smNEWPW'])){
    $usm->updateUser($_POST['usermail'], false);
    $suc = "Es wurde eine Mail zur R&uuml;cksetzung des Passworts versendet.";
}

if(isset($_GET['suc'])){
    $suc = urldecode($_GET['suc']);
    unset($_GET['suc']);
}

if(isset($_SESSION['logout_error'])){   
    $err = $_SESSION['logout_error'];
    unset($_SESSION['logout_error']);
}

if(isset($_GET['err'])){
    $err = urldecode($_GET['err']);
    unset($_GET['err']);
}


$msg="";
if(!empty($suc)){
    $msg .= "<div class='alert alert-success' role='alert'>";
    $msg .= $suc;
    $msg .= "</div>";
}
if(!empty($err)){
    $msg .= "<div class='alert alert-danger' role='alert'>";
    $msg .= $err;
    $msg .= "</div>";
}

$register_link = $conf->getYpumRoot()."/yfront/register.php";

?>
 
</head>
<body class="text-center h-100">


<form class="form-signin" method="POST"> <!--action löscht übertragene GET-Werte -->
<div class="container h-100">
    <div class="row h-100 justify-content-center align-items-center">
        <div class="container">
            <div class="row justify-content-center align-items-center">
                <div class="col col-12 col-sm-8">
                    <?=$msg?>
                    <h1 class="h3 mb-3 font-weight-normal">Bitte melden Sie sich hier an</h1>
                    <input class="form-control" name="usermail" type="email" id="usermail" required autocomplete="off" autofocus placeholder="Mailadresse" >
                </div>
            </div>
            <div class="row mt-2 justify-content-center align-items-center">
                <div class="col col-12 col-sm-5">
                    <input class="form-control" name="pw" type="password" id="pw" autocomplete="off" placeholder="Passwort">
                </div>
                <div class="col col-12 col-sm-3">
                <input 
                    class="form-control b-2" data-width="100%" type="checkbox" id="show_pw" name="instModus" data-toggle="toggle" 
                    data-onstyle="outline-success" data-offstyle="outline-danger" data-on="anzeigen" data-off="verstecken" checked/>
                </div>
            </div>
            <div class="row mt-2 justify-content-center align-items-center">
                <div class="col col-12 col-sm-8">
                    <button class="btn btn-lg btn-success btn-block" type="submit" name="smLOGIN">Anmelden</button>
                    <button class="btn btn-lg btn-secondary btn-block" type="submit" name="smNEWPW">Neues Passwort erstellen</button>
                    <a href='<?=$register_link?>' class="btn btn-lg btn-secondary btn-block">Registrieren</a>
                </div>
            </div>
        </div>
    </div>
</div>
</form>
</body>
</html>