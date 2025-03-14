<?php 
namespace ypum;
require_once(__DIR__."/../ypum.php");
?>
<!DOCTYPE html>
<html lang="de">
<head>
<?php 
require_once(__DIR__."/../include/inc_main.php");
require_once(__DIR__."/../include/code/rollen_posts.php");
require_once(__DIR__."/../components/navbar_config.php");

?>  


<script>
$(document).ready(function() {

    var table = $('#tabelle').DataTable({
		stateSave: true,
		"order": [[ 0, "asc" ]]
    });

    // Tooltips
	$(function () {
		$('[data-toggle="tooltip"]').tooltip()
    })
    // Listener für den Formular-Leeren-Button
    $("#f_leeren").click(function(){
        $('#r_name').attr("required", false);
    });

    // Listener für den Tabellen-Klick
    $('#tabelle tbody').on( 'click', 'tr', function () {

        $('#r_rollennummer').html($(this).attr('data-r_nummer'));
        $('#r_name').val($(this).attr('data-r_name')); 
        $('#r_bit').val($(this).attr('data-r_nummer')); // Hidden Formularfeld zur Zuordnung in der DB
        $('#r_role_comment').val($(this).attr('data-r_role_comment')); 

        $("#RollenDialog").modal();	
        
	});
    
});

</script>
<title>Rollenverwaltung</title>
</head>
<body>

<?php


$tabelle="";
$rows=(object) $dbm->query("SELECT bit, name, role_comment from y_roles");

foreach($rows as $row){
    $bit = $row['bit'];
    $name = $row['name'];
    $role_comment = $row['role_comment'];
    $wert = pow(2,intval($bit));

    $tabelle .= "<tr data-r_nummer='$bit' data-r_name='$name'  data-r_role_comment='$role_comment'>" ;
    $tabelle .= "<td>$bit</td>";
    $tabelle .= "<td>$name</td>";
    $tabelle .= "<td>$role_comment</td>";
    $tabelle .= "</tr>" ;
}

?>


<!------------------------------------------------------------------ -->   
<div class="container">

<p>Zum Bearbeiten bitte auf das Element klicken.</p>
	<table id="tabelle" class="display" style="cursor: pointer;">
		<thead><tr>
			<th>Bit</th>
			<th>Name</th>
			<th>Beschreibung</th>
		</tr></thead>
		<tbody>
			<?=$tabelle?>
		<tbody>
	</table>

</div>
<!-------------------------------------------------------------------->   
<?php 
require_once(__DIR__."/../include/code/rollen_dialog.php");
$conf->getFooter();
?>
</body>
</html>