<?php 
namespace ypum;
require_once(__DIR__."/../ypum.php"); 
require_once(__DIR__."/../include/inc_main.php");
require_once(__DIR__."/../include/code/userdata_posts.php");
?>
<!DOCTYPE html>
<html lang="de">
<head>
 
<script>

$(document).ready(function() {

    var table = $('#tabelle').DataTable({
		stateSave: true,
		"order": [[ 0, "asc" ]]
    });

    // Listener für den Tabellen-Klick
    $('#tabelle tbody').on( 'click', 'tr', function () {

        $('#f_id').val($(this).attr('data-id')); // Hidden Formularfeld zur Zuordnung in der DB
        $('#f_uf_name').val($(this).attr('data-uf')); 
        $('#f_int_name').val($(this).attr('data-fieldname')); 
        $("#RollenDialog").modal();	
     
    });

});
</script>
<title>Nutzerdaten definieren</title>
</head>
<body>
<!-------------------------------------------------------------------->
<?php include_once(__DIR__."/../components/navbar_config.php");?>
<!-------------------------------------------------------------------->
<div class="container">
    <div class = 'row'>
        <div class="col col-12">
        <p>Bitte geben Sie hier ein, welche Daten Sie von den Nutzern über den Anmeldenamen
        und das Passwort hinaus erfassen m&ouml;chten.</p> 
        </div>
    </div>

<form method='post'>
    <input type='hidden' class='form-control' id='f_id' name='id'/>
    <div class="row">

        <div class="form-group col-12 col-sm-4">
            <input required  type='text' class='form-control' id='f_uf_name' name='uf_name' placeholder='Name des Feldes'/>
        </div>
    
        <div class="form-group col-12 col-sm-4">
            <input required type='text' class='form-control' id="f_int_name" name='int_name' placeholder='Eindeutiger interner Name' pattern='^[a-z][a-z0-9]*'/>
            <small>Erlaubt sind Kleinbuchstaben und Ziffern (nicht am Beginn)</small>
        </div>

        <div class="form-group col-12 col-sm-4">
            <div class="form-check">
                <button type="submit" class="btn btn-danger btn-default" name="MACH_LOESCHEN" >L&ouml;schen</button>
                <button type="submit" class="btn btn-primary btn-success" name="MACH_SPEICHERN" >Speichern</button>
            </div>
        </div>

    </div>
</form>
<!--------------------------------------------------------------------> 
<?php


$tabelle="";
$rows=(object) $dbm->query("SELECT id, uf_name, fieldname from y_user_fields");

foreach($rows as $row){
    $id = $row['id'];
    $uf_name = $row['uf_name'];
    $fieldname = $row['fieldname'];

    $tabelle .= "<tr  data-id='$id' data-uf='$uf_name' data-fieldname='$fieldname'>" ;
    $tabelle .= "<td>$uf_name</td>";
    $tabelle .= "<td>$fieldname</td>";
    $tabelle .= "</tr>" ;
}
?>
<!--------------------------------------------------------------------> 
<div class="container">
    <p>
	<table id="tabelle" class="display " style="cursor: pointer;">
		<thead><tr>
			<th>Bezeichnung</th>
			<th>Interner Name (eindeutig)</th>
		</tr></thead>
		<tbody>
			<?=$tabelle?>
		<tbody>
    </table>
    </p>
</div>

<!-------------------------------------------------------------------->   
<?php $conf->getFooter();?>
</body>
</html>