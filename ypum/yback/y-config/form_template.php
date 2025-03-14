<?php 
namespace ypum;
require_once(__DIR__."/../ypum.php");
require_once(__DIR__."/../include/inc_main.php");
?>  
<!DOCTYPE html>
<html lang="de">
<head>
<title></title>
</head>
<body>
<!-------------------------------------------------------------------->
<?php include_once(__DIR__."/../components/navbar_config.php");?>
<!-------------------------------------------------------------------->
<?php include_once(__DIR__."/../include/code/form_template_string.php");?>
<!-------------------------------------------------------------------->
<div class="container">
    <h2>Eine Vorlage f&uuml;r eine rudiment&auml;re Dateneingabe der Nutzerdaten:</h2>
    <textarea  rows=20 class="form-control w-100"><?=$html?></textarea>
</div>
<!-------------------------------------------------------------------->
<?php $conf->getFooter();?>
</body>
</html>