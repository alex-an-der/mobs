<?php require_once(__DIR__."/../ypum.php");


if(empty($_POST['startseite'])) $_POST['startseite'] = $conf->getYpumRoot()."/ydemo/demo.php";

$data['tokenstunden'] = $_POST['tokenstunden'];
$data['minlength'] = $_POST['minlength'];
$data['initrolle'] = $_POST['initrolle'];
$data['startseite'] = $_POST['startseite'];
$data['sessionrenew'] = $_POST['sessionrenew'];
$data['sessiondiscard'] = $_POST['sessiondiscard'];

$data['allerollen'] = $_POST['allerollen'] == "alle" ? true : false;

$conf->save("divers", $data);

$pos_x = $_POST['screennpos_x'];
$pos_y = $_POST['screennpos_y'];

?>

<html>
<head>
<script>
$(document).ready(function() {
    $('#formular').submit();

});
</script>

</head>
<body>
<div class="alert alert-success" role="alert">
  <h1 class='text-center'>Daten werden gespeichert</h1>
</div>
<form id='formular' method='post' action='diverses.php'>
<input hidden name='px' value='<?=$pos_x?>'>
<input hidden name='py' value='<?=$pos_y?>'>
</form>

</form>
</body>
</html>