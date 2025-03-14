<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keine Berechtigung</title>
    <?php include_once(__DIR__."/../yback/include/inc_main.php"); ?>
    <!--script>window.history.back();</script-->
</head>
<body>
<div class="container">
    <div class="row justify-content-center align-items-center pt-5">
        <div class="card">
            <h5 class="card-header">Hierauf haben Sie leider keine Berechtigung</h5>
            <div class="card-body">
                <div class="row h-100 justify-content-center align-items-center">
                    <p class="card-text">F&uuml;r diese Seite sind Sie leider nicht freigeschaltet. M&ouml;chten Sie hierauf zugreifen, 
                    wenden Sie sich bitte an den Betreiber dieser Seite.</p>
                </div>
                <div class="row h-100 justify-content-center align-items-center mt-5"> 
                <div class="col col-3"></div>
                <div class="col col-3"><a href="#" class="btn btn-primary btn-block" onclick='window.history.back();'>Zur&uuml;ck zur letzten Seite</a></div>
                <div class="col col-3"><a href="<?= $conf->getPathToLogin() ?>" class="btn btn-primary btn-block">Zum Log-In</a></div>
                <div class="col col-3"></div>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>