<?php
require_once(__DIR__."/../config/config.php");
require_once(__DIR__."/classes/datenbank.php");
$db = new Datenbank();
require_once(__DIR__."/classes/array2table.php");
$a2t = new Array2Table();

echo ("<link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>");
?>