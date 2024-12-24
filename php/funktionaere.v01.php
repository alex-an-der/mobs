<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Funktion&auml;re bearbeiten</title>

<?php
require_once(__DIR__."/../inc/include.php");

$admin = true;
$tabelle = "funktionaere";
$spartenDaten = $db->query("SELECT * FROM $tabelle");
if (!$spartenDaten) $db->log(__FILE__.":".__LINE__." - ". $db->error);
$tabelle = $a2t->getTable($spartenDaten);

?>

</head>
<body>
    <?=$tabelle?>
</body>
</html>