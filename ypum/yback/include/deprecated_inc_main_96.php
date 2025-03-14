<?php
namespace ypum;

require_once(__DIR__."/classes/configmanager.php");
require_once(__DIR__."/classes/databasemanager.php");
require_once(__DIR__."/classes/usermanager.php");

$conf = new configmanager();
$webRoot = $conf->getYpumRoot();
$secDir = $conf->getSecDir();
$im=$conf->isInstallmodus();


$dbm = new databasemanager($secDir);
$usm = new usermanager($dbm, $conf);

/*
echo("<meta charset='UTF-8'>");
echo("<meta name='viewport' content='width=device-width, initial-scale=1, shrink-to-fit=no'>");
echo("<link  href='$webRoot/yback/lib/bootstrap/css/bootstrap.min.css' rel='stylesheet'></link>");
echo("<script src='$webRoot/yback/lib/jquery/jquery.js'></script>");
echo("<script src='$webRoot/yback/lib/bootstrap/js/bootstrap.bundle.js'></script>");

echo("<link rel='stylesheet' type='text/css' href='$webRoot/yback/lib/datatables/css/jquery.dataTables.css'>");
echo("<script type='text/javascript' src='$webRoot/yback/lib/datatables/js/jquery.dataTables.js'></script>");
*/
?>