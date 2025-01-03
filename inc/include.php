<?php
// remove header
header_remove('ETag');
header_remove('Pragma');
header_remove('Cache-Control');
header_remove('Last-Modified');
header_remove('Expires');

// set header
header('Expires: Thu, 1 Jan 1970 00:00:00 GMT');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0',false);
header('Pragma: no-cache');

require_once(__DIR__."/../config/config.php");
require_once(__DIR__."/classes/datenbank.php");
$db = new Datenbank();


echo ("<link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>");

function show($var, $die=false){
    echo '<pre>' . var_export($var, true) . '</pre>';
    if($die)die();
    }
?>