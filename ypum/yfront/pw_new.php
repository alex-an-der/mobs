<!DOCTYPE html>
<html lang="de">
<head>
<?php
require_once(__DIR__."/../yback/include/inc_main.php");
$ypdir = $conf->getYpumRoot();
?>

<link href="<?=$ypdir?>/yback/lib/bootstrap_toggles/css/bootstrap4-toggle.css" rel="stylesheet">
<script src="<?=$ypdir?>/yback/lib/bootstrap_toggles/js/bootstrap4-toggle.js"></script>

<script>
$(document).ready(function() {
 
 // Listener Toggle (Show PW)
 $("#show_pw").change(function(){

     if(document.getElementById('show_pw').checked){
         document.getElementById('pw1').type = 'password';
         document.getElementById('pw2').type = 'password';
     }else{
         document.getElementById('pw1').type = 'text';
         document.getElementById('pw2').type = 'text';
     }   
 });
});

</script>

    <title>Passwort festlegen</title>
    <?php 
    

    if(isset($err)){
        $err = "<div class='alert alert-danger' role='alert'>$err</div>";
    }else{
        $err="";
    }
    ?>
 
</head>
<body class="text-center">

<form class="form-signin" method="POST">
    <div class="container h-100">
        <div class="row h-100 justify-content-center align-items-center">

            <div class="col col-12 col-sm-8">
                
                    <!--img class="mb-4" alt="" width="72" height="72"-->
                    <?=$err?>
                    <h1 class="h3 mb-3 font-weight-normal">Bitte legen Sie Ihr Passwort fest</h1>
                    <div class="form-group"><input class="form-control" name="pw1" type="password" id="pw1" required autocomplete="off" autofocus placeholder="Passwort"></div>
                    <div class="form-group"><input class="form-control" name="pw2" type="password" id="pw2" required autocomplete="off" placeholder="Passwort wiederholen"></div>

                    <div class="form-group">
                        <input class="form-control" data-width="100%" type="checkbox" id="show_pw" name="instModus" data-toggle="toggle" 
                        data-onstyle="outline-success" data-offstyle="outline-danger" data-on="anzeigen" data-off="verstecken" checked/>
                    </div>
                    
                    <div class="form-group"><button class="btn btn-lg btn-success btn-block" type="submit" name="submitpw">Passwort speichern</button></div>
                
            </div>
        </div>
    </div>
</form>




</body>
</html>