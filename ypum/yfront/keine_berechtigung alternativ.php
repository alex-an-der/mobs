<?php 
session_start();
if(isset($_SESSION['lastSite'])) $relBackPath = getRelPath(__dir__, $_SESSION['lastSite']);
else                             $relBackPath = "./login.php";

?>
<!DOCTYPE html>
<meta http-equiv="refresh" content="5; URL=<?=$relBackPath?>">
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keine Berechtigung</title>
</head>
<body>
Leider besitzen Sie nicht die Berechtigung, diese Seite anzuzeigen. Sie werden in 5 Sekunden 
zur&uuml;ckgeleitet.


<?php

function getRelPath($from, $to)
{
    $from = is_dir($from) ? rtrim($from, '\/') . '/' : $from;
    $to   = is_dir($to)   ? rtrim($to, '\/') . '/'   : $to;

    $from = str_replace('\\', '/', $from);
    $to   = str_replace('\\', '/', $to);

    $from     = explode('/', $from);
    $to       = explode('/', $to);
    $relPath  = $to;

    // Gehe Dirs von links nach rechs durch
    foreach($from as $depth => $dir) {
        // Gleich => ignorieren und Weiter
        if($dir === $to[$depth]) {
            array_shift($relPath);
        } else {
            $remaining = count($from) - $depth;
            if($remaining > 1) {
                $padLength = (count($relPath) + $remaining - 1) * -1;
                $relPath = array_pad($relPath, $padLength, '..');
                break;
            } else {
                $relPath[0] = './' . $relPath[0];
            }
        }
    }
    return implode('/', $relPath);
}

?>
</body>
</html>