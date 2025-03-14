<!DOCTYPE html>
<?php include_once(__DIR__."/../yback/include/inc_main.php");?>
<html lang="de" class="h-100">
<head>
    
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mail ist raus</title>
</head>
<body>


<div class="container">
    <div class="row justify-content-center align-items-center pt-5">
        <div class="card">
            <h5 class="card-header">Sie erhalten in K&uuml;rze eine Mail</h5>
            <div class="card-body">
                <div class="row h-100 justify-content-center align-items-center">
                    <div class="col col-3"><img src="./../yback/img/ymail.png" class="img-fluid w-100"></div>
                        <div class="col col-9"><p class="card-text">Danke f&uuml;r Ihr Interesse! Sie erhalten in K&uuml;rze eine Mail, mit der Sie 
                        sich ein Passwort setzen k&ouml;nnen. Sollten Sie keine Mail erhalten, &uuml;berpr&uuml;fen Sie bitte auch Ihren 
                        Spam/Junk - Ordner. Auf der Login-Seite k&ouml;nnen Sie mit "Neues Passwort erstellen" die Passwort-Mail 
                        jederzeit nochmals ausl&ouml;sen. Beachten Sie aber bitte, dass damit &auml;ltere Mails ung&uuml;ltig werden.</p>
                        <a href="<?= $conf->getPathToLogin() ?>" class="btn btn-primary">Zum Log-In</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>