<?php 
namespace ypum;
require_once(__DIR__."/../ypum.php"); 

const NO_YPUM = 1;
const YPUM_OK = 2; 
?>

<!DOCTYPE html>
<html lang="de">
<head>
<?php
require_once(__DIR__."/../include/code/sitescan_posts.php");
$_SESSION['ypum_sourcekeyscan'] = $conf->getRandKey();
$sourceHash = password_hash($_SESSION['ypum_sourcekeyscan'], PASSWORD_DEFAULT);
?>
<link rel="stylesheet" href="sitescan.css">
<title>Seitenverwaltung</title>
<script src="./../lib/datatables/extensions/ColReorder-1.5.3/js/dataTables.colReorder.min.js"></script>
<script src="./../lib/datatables/extensions/Buttons-1.6.5/js/buttons.colVis.min.js"></script>

<!-- Datenexport - Buttons -->
<script src="./../lib/datatables/extensions/Buttons-1.6.5/js/dataTables.buttons.min.js"></script>
<script src="./../lib/datatables/extensions/Buttons-1.6.5/js/buttons.flash.min.js"></script>
<script src="./../lib/datatables/extensions/JSZip-2.5.0/jszip.min.js"></script>
<script src="./../lib/datatables/extensions/pdfmake-0.1.36/pdfmake.min.js"></script>
<script src="./../lib/datatables/extensions/pdfmake-0.1.36/vfs_fonts.js"></script>
<script src="./../lib/datatables/extensions/Buttons-1.6.5/js/buttons.html5.min.js"></script>
<script src="./../lib/datatables/extensions/Buttons-1.6.5/js/buttons.print.min.js"></script>


<!-------------------------------------------------------------------->

<script>


$(document).ready(function() {

    var table = $('#tabelle').DataTable({
		stateSave: true,
		"order": [[ 1, "asc" ]],
		colReorder: true
	});
	
 
	new $.fn.dataTable.Buttons( table, {
		buttons: [
			{
                extend: 'copy',
                title: 'ypum-Seitentabelle'
            },
            {
                extend: 'csv',
                title: 'ypum-Seitentabelle'
            },
            {
                extend: 'excel',
                title: 'ypum-Seitentabelle'
            },
			{
                extend: 'pdf',
                title: 'ypum-Seitentabelle'
            },
			{
                extend: 'print',
                title: 'ypum-Seitentabelle'
            }
        ]
} );


	let buttons = table.buttons();
	buttons.each(function(index){
		index.node.className = "btn btn-info";
	});
	buttons.container()[0].className = "ml-5";


	$('#tabbttns').html(table.buttons().container());
	

	// Tooltips
	$(function () {
		$('[data-toggle="tooltip"]').tooltip()
	})

	// Listener für Doppelklick in Tabelle

	$('#tabelle tbody').on( 'dblclick', 'tr', function () {
		pfad = $(this).attr('data-file');
		// ... Pfad übernehmen
		$('#rootdir').val(pfad);
		// ... Scan-Eingabe zeigen
		$('#f_scan').collapse('show');
	});
	


	// Listener für den Rollenfilter-Dialog-Button
	$("#f_rollenfilter_drop").change(function(){
		$('#f_rollenfilter').submit();
	});

	// Listener für den Rollenfilter-Dialog-Button
	$("#filterDialog").click(function(){
		$("#ModalRollenfilter").modal();	
	});

	// Listener für den Edit-Dialog-Button
	$("#editDialog").click(function(){
		
		if($("tr.selected").length>0){ // Wenn etwas ausgewählt wurde
			$('.rollenoptionen').attr("selected",false); // Selection aufheben
			let selectedIDs="";
			let rolecompare = 0;
			let rollenFuerDialog = 0;
			$.each($("tr.selected"), function() {
				
				selectedIDs = selectedIDs + "," + $(this).attr('data-id');
				
				if(rolecompare==0 && $(this).attr('data-role')>0){
					rolecompare=$(this).attr('data-role');
					rollenFuerDialog = rolecompare;
				}
				if(rolecompare != $(this).attr('data-role')) rollenFuerDialog =0;
				
			});

			if(rollenFuerDialog>0) {
				
				$('#role-dialog-alert').hide();
				// Trenne int in bits auf
				binString = parseInt(rollenFuerDialog).toString(2);
				for(let pos=(binString.length-1); pos>-1;pos--){
					 bit = (binString.length-1)-pos; // 0, 1, 2, ...
					 bitset = parseInt(binString.charAt(pos));
					if(bitset){
						$('#rolopt'+bit).attr("selected",true);
					}
				}
				selectedIDs = selectedIDs.substring(1);
			}else{
				$('#role-dialog-alert').show();
			}

			$('#IDsToEdit').val(selectedIDs);
			$("#ModalRollenzuweisung").modal();		
		}

	  });
	  
	  $("#unscan").click(function(){
		$('#f_unscan').submit();
	});
	
	// Listener für den Check--Button
	$("#checkauth").click(function(){
		let res = confirm("Ein zuverlässiges Ergebnis erhalten Sie, wenn Sie vorher den Cache des Browsers leeren. Idealerweise starten Sie nach dem Leeren des Caches den Browser neu.\n\nFortfahren?");
		if(res){
			bitteWarten();
			$('#f_checkSites').submit();	
		}
	});

	// Listener für Klick in Tabelle zum Auswählen
   	$('#tabelle tbody').on( 'click', 'tr', function () {
		if($(this).attr('data-id')>-1)
			$(this).toggleClass('selected');
			// Auswahl für Edit mach nur Sinn, wenn die Daten bereits in der DB sind (ID>-1)
	});

	if(<?=$filtered?> >= 0) $('#filteropt<?=$filtered?>').attr('selected',true);

} );

function bitteWarten(){

	$('#bitteWarten').addClass("alert-success");
	$('#bitteWarten').text("Der gewünschte Vorgang wird ausgeführt. Bitte warten.");
	
	

}
function saveToDB(data){

	let datensatz = {
		'data': data,
		'sourceHash': "<?=$sourceHash?>"
    };

	window.fetch ("./../ajax/sitescan_db_replace.php", {
	method: 'post',
	headers: {'Content-Type' : 'application/json'},
	body: JSON.stringify(datensatz)

	})
	.then ( (text) => {
		site_reload();
	})
	.catch ((error) => {
		console.log ("Error: ", error)
	})
}
function deleteFromDB(id){

	let datensatz = {
		'id': id,
		'sourceHash': "<?=$sourceHash?>"
    };

		
	window.fetch ("./../ajax/sitescan_db_delete.php", {
	method: 'post',
	headers: {'Content-Type' : 'application/json'},
	body: JSON.stringify(datensatz)
	})
	.then ( (text) => {
		site_reload();
	})
	.catch ((error) => {
		console.log ("Error: ", error)
	})
}
function site_reload(){

	window.location = window.location.href; 
	
}
</script>
<!-------------------------------------------------------------------->
</head>
<body>

<!-------------------------------------------------------------------->
<?php include_once(__DIR__."/../components/navbar_config.php");?>
<!-------------------------------------------------------------------->
<?php 
// Rollen einlesen
$args[] = "1";
//$rollen=$dbm->query("SELECT bit, name from y_roles WHERE role_active=?",$args);

$rollen=$dbm->query("SELECT bit, name from y_roles order by bit asc");
$rollenangebot = "";
$rollenangebot_filter = "";

foreach($rollen as $rolle){

	$rbit = $rolle['bit'];
	$rname = $rolle['name'];
	$id1 = "rolopt$rbit";
	$id2 = "filteropt$rbit";

	$rollenangebot .= "<option class='rollenoptionen' id=$id1 value='$rbit'>#$rbit: $rname</option>";
	$rollenangebot_filter .= "<option class='rollenoptionennoid' id=$id2 value='$rbit'>#$rbit: $rname</option>";
}

function getRollenFuerTooltip($val){

	global $dbm;
	$args[] = $val;

	$rows = (object) $dbm->query("SELECT bit, name FROM y_roles WHERE ( POWER(2, bit) & ?) > 0", $args);
	$ret = "";

	foreach($rows as $row){
		$name = $row['name'];
		//$bit = pow(2,$row['bit']);
		$bit = $row['bit'];
		if($name=="") $name = "<i>unbenannt</i>";
		$ret .= "$bit: $name <br>";
	}


	return $ret;
}

//$aFileSammler = array();
function files_rekursiv($verzeichnis, $tiefe, $tabdirekt=true) {
	
	global $tabelle;
	//global $aFileSammler;
	$aFileSammler = array();

	$verzeichnis = realpath(trim($verzeichnis));
	$verzeichnis .= (substr($verzeichnis, -1) == DIRECTORY_SEPARATOR ? '' : DIRECTORY_SEPARATOR);
	if(is_dir($verzeichnis)){
		@$handle =  opendir($verzeichnis);
		if(!empty($handle)){
			while ($datei = readdir($handle)) 
			{
				if (($datei != '.') && ($datei != '..')) 
				{
					$file = str_replace(DIRECTORY_SEPARATOR, "/", $verzeichnis.$datei);
					if (is_dir($file)) // Wenn Verzeichniseintrag ein Verzeichnis ist
					{
							// Erneuter Funktionsaufruf, um das aktuelle Verzeichnis auszulesen
							if($tiefe>0) 
							{
								if($tabdirekt){ 
									files_rekursiv($file.DIRECTORY_SEPARATOR, ($tiefe-1));
								}else{ 
									$ret = files_rekursiv($file.DIRECTORY_SEPARATOR, ($tiefe-1),false);
									foreach($ret as $newElement){
										$aFileSammler[] = $newElement;
									}
									
								}
							} 
					} else {
							if (substr($file, -4) == ".php") 
							if($tabdirekt) $tabelle.= createRow($file);
							else $aFileSammler[] = $file;
							
					} 
				}
			}
		}
		@closedir($handle); 
	}
	if(!$tabdirekt) return $aFileSammler;
}

function dir_rekursiv($verzeichnis, $tiefe) {

	$verzeichnis = realpath(trim($verzeichnis));
	if($verzeichnis){
		global $tabelle;

		$verzeichnis .= (substr($verzeichnis, -1) == DIRECTORY_SEPARATOR ? '' : DIRECTORY_SEPARATOR);

		if(is_dir($verzeichnis)){	
		
			@$handle =  opendir($verzeichnis);
			while ($datei = readdir($handle)) 
			{
					if (($datei != '.') && ($datei != '..')) 
					{
						//$file = str_replace(DIRECTORY_SEPARATOR, "/",strtolower ($verzeichnis.$datei));
						$file = str_replace(DIRECTORY_SEPARATOR, "/",$verzeichnis.$datei);
							if (is_dir($file)) // Wenn Verzeichniseintrag ein Verzeichnis ist
							{
								// Erneuter Funktionsaufruf, um das aktuelle Verzeichnis auszulesen
								$tabelle.= createRow($file);
								if($tiefe>0) dir_rekursiv($file.DIRECTORY_SEPARATOR, ($tiefe-1)); 
							}
					}
			}
			@closedir($handle); 
			
		}
	}
}

function createRow($file){

	global $dbm;
	$isDir = is_dir($file);
	$args[] = $file;
	$row=$dbm->query("select id, dir, roles from y_sites where dir=?", $args); 
	if($row){
		return getTR_inDB($row[0]);
	
	}else{
		return getTR_not_inDB($file, $isDir);
	}
}

function hasYpum($file){

	
	$file = getWebdirectory($file);

	$postparam= array();
	$postparam['seccheck'] = "seccheck";
	$result = curl_post($file, $postparam);	
	
	// Leerzeilen verzeihen:
	$result = trim($result);

	if (strcmp("159874",$result)==0) return NO_YPUM;
	elseif (strcmp("254965",$result)==0) return YPUM_OK;
	else return false;
	
}

function curl_post($url, array $post = NULL, array $options = array())
{
    $defaults = array(
        CURLOPT_POST => 1,
        CURLOPT_HEADER => 0,
        CURLOPT_URL => $url,
        CURLOPT_FRESH_CONNECT => 1,
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_FORBID_REUSE => 1,
        CURLOPT_TIMEOUT => 4,
        CURLOPT_POSTFIELDS => http_build_query($post)
    );

    $ch = curl_init();
    curl_setopt_array($ch, ($options + $defaults));
    if( ! $result = curl_exec($ch))
    {
		//trigger_error(curl_error($ch)); 
        return false;
    }
    curl_close($ch);
    return $result;
} 

function getWebdirectory($file){

	$prefixe =  json_decode(file_get_contents(__DIR__."/../../yconf/prefix.json"),true);
	$s_pre = $prefixe['srv_prefix'];
	$w_pre = $prefixe['web_prefix'];
	return str_replace($s_pre, $w_pre, $file);

}

function getTR_inDB($row){

	global $checkSites;

	$trashIcon = "<svg width='1em' height='1em' viewBox='0 0 16 16' class='bi bi-trash' fill='currentColor' xmlns='http://www.w3.org/2000/svg'>";
	$trashIcon .= "<path d='M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0V6z'/>";
	$trashIcon .= "<path fill-rule='evenodd' d='M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1v1zM4.118 4L4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4H4.118zM2.5 3V2h11v1h-11z'/>";
	$trashIcon .= "</svg>";
	/*
	NO_YPUM = 1;
	YPUM_OK = 2; 
	*/
	$id = $row['id'];
	$file = $row['dir'];
	$roles = $row['roles'];
	if(file_exists($file)){
		if(is_file($file)){
			if($checkSites){
				//$file = strtolower($file);
				if(hasYpum($file)>0){
					if(hasYpum($file)==YPUM_OK){
						$farbe = "#abffc1"; // GRÜN -> Die Seite hat ypum eingebunden
						$roles = "OK";
					}else{
						$farbe = "#aaffeb"; // Grünblau -> Die Seite hat ypum absichtlich nicht eingebunden
						$roles = "OK (Seite bewusst nicht eingebunden)";
					}
				}else{
					$farbe = "#F8B195"; // ROT -> Die Seite hat ypum NICHT eingebunden, obwohl sie in der DB steht
					$roles = "YPUM ist ungewollt nicht eingebunden";
				}
			}else{
				$farbe = "#faffab"; // GELB -> Kein Check -> Es kann keine Aussage getroffen werden

			}
		}else{
			$farbe = "#faffab"; // GELB -> Es ist ein Verzeichnis. Nur Dateien können direkt in ypum eingebunden werden.
			if($checkSites){$roles = "Keine PHP-Datei";}
		}

	}else{
		$farbe = "#c9c9c9"; // GRAU -> Die Seite gibt es nicht mehr
		if($checkSites){$roles = "Die Seite gibt es nicht mehr";}
	}
	$rowStyle = "style = 'background-color: $farbe;'";

	$bttn="";
	if($id>0)
		$bttn = "<button type='button' class='btn btn-outline-danger' onclick='deleteFromDB($id);'>$trashIcon</button>";
		
	return generateTR($rowStyle, $bttn, $file, $roles, $id);
}

function getTR_not_inDB($file, $isDir){

	$diricon  = "<svg width='1em' height='1em' viewBox='0 0 16 16' class='bi bi-folder-plus' fill='currentColor' xmlns='http://www.w3.org/2000/svg'>";
	$diricon .= "<path fill-rule='evenodd' d='M9.828 4H2.19a1 1 0 0 0-.996 1.09l.637 7a1 1 0 0 0 .995.91H9v1H2.826a2 2 0 0 1-1.991-1.819l-.637-7a1.99 1.99 0 0 1 .342-1.31L.5 3a2 2 0 0 1 2-2h3.672a2 2 0 0 1 1.414.586l.828.828A2 2 0 0 0 9.828 3h3.982a2 2 0 0 1 1.992 2.181L15.546 8H14.54l.265-2.91A1 1 0 0 0 13.81 4H9.828zm-2.95-1.707L7.587 3H2.19c-.24 0-.47.042-.684.12L1.5 2.98a1 1 0 0 1 1-.98h3.672a1 1 0 0 1 .707.293z'/>";
	$diricon .= "<path fill-rule='evenodd' d='M13.5 10a.5.5 0 0 1 .5.5V12h1.5a.5.5 0 0 1 0 1H14v1.5a.5.5 0 0 1-1 0V13h-1.5a.5.5 0 0 1 0-1H13v-1.5a.5.5 0 0 1 .5-.5z'/>";
	$diricon .= "</svg>";

	$fileicon  = "<svg width='1em' height='1em' viewBox='0 0 16 16' class='bi bi-file-earmark-plus' fill='currentColor' xmlns='http://www.w3.org/2000/svg'>";
	$fileicon .= "<path d='M4 0h5.5v1H4a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1V4.5h1V14a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V2a2 2 0 0 1 2-2z'/>";
	$fileicon .= "<path d='M9.5 3V0L14 4.5h-3A1.5 1.5 0 0 1 9.5 3z'/>";
	$fileicon .= "<path fill-rule='evenodd' d='M8 6.5a.5.5 0 0 1 .5.5v1.5H10a.5.5 0 0 1 0 1H8.5V11a.5.5 0 0 1-1 0V9.5H6a.5.5 0 0 1 0-1h1.5V7a.5.5 0 0 1 .5-.5z'/>";
	$fileicon .= "</svg>";

	if ($isDir){
		$addicon = $diricon; 
	}else{  
		$addicon = $fileicon;
	}

	$roles = "";
	$rowStyle = "";
	$id = -1;
	$bttn = "<button type='button' class='btn btn-outline-success' onclick='saveToDB(\"$file\");'>$addicon</button>";

	return generateTR($rowStyle, $bttn, $file, $roles, $id);
}

function generateTR($rowStyle, $bttn, $file, $roles, $id){

	$ret = "<tr data-id=$id data-file=$file data-role=$roles>";
	$ret .="<td  class='p-0 text-center'>$bttn</td>";
	$ret .="<td $rowStyle >$file</td>";
	$tooltip = getRollenFuerTooltip($roles);
	
	$ret .="<td data-toggle='tooltip' data-html='true' title='$tooltip'>$roles</td>";
	
	$ret .="</tr>";

	return $ret;
}

if($checkSites) $spaltenHeader = "Authentifizierungsmodul";
else			$spaltenHeader = "Genehmigt f&uuml;r diese Rollen";
?>
<!--------------------------------------------------------------------> 


<div class="container">
	<p>
		
		<button class="btn btn-outline-primary m-1" data-toggle="collapse" data-target="#f_scan" role="button" aria-expanded="false" aria-controls="collapse">
		Scannen</button>

		<button type="submit" class="btn btn-warning m-1" id="unscan">
		Gespeicherte Seiten</button>

		<button type="button" class="btn btn-warning m-1" id="checkauth">
		Pr&uumlfe Seiteneinbindung (kann einige Zeit dauern)</button>

		<button class="btn btn-outline-primary m-1" data-toggle="collapse" data-target="#f_rollenfilter" role="button" aria-expanded="false" aria-controls="collapse">
		Filter nach Rolle</button>

		<button type="button" class="btn btn-primary m-1" id="editDialog">
		Markierte Rollen bearbeiten</button>

	</p>
	<!-- Pseudoformular, um SCAN-WEG zu erfassen -->
	<form id="f_unscan" method="post" >
		<input type="hidden" name="MACH_SCAN_WEG" value="1">
	</form>

	<!-- Pseudoformular, um den SITE-CHECK einzuleiten -->
	<form id="f_checkSites" method="post" >
		<input type="hidden" name="MACH_SITE_CHECK" value="1">
	</form>
	


	<!-- Collapse-Formular "FILTER" -->
	<form class="collapse mb-5" id="f_rollenfilter" method="post">        
		<div class="row form-group">
			<label for="rootdir" class="col col-12 col-sm-4 col-form-label col-form-label-lg m-auto">Zeige nur Seiten mit der Berechtigung:</label>
			<div class="col col-12 col-sm-6">
				<div class="form-group">
					<select name='filterbit' id="f_rollenfilter_drop" class="form-control">
						<option value="" disabled selected hidden>Bitte w&auml;hlen...</option>
						<?=$rollenangebot_filter?>
					</select>
				</div>
			</div>
			<div class="col col-12 col-sm-2">
				<button name='filter_reset' id='filter_reset' class="btn btn-primary" >Reset</button>
			</div>
		</div>
		<input type="hidden" name="MACH_FILTER"/>
	</form>

	<!-- Collapse-Formular "SCAN" -->
	<form class="collapse mb-5" id="f_scan" method="post">        
		<div class="row form-group">
				<label for="rootdir" class="col-sm-2 col-form-label col-form-label-lg m-auto">Basisverzeichnis:</label>
				<div class="col col-12 col-sm-10">
					<input id="rootdir" class="form-control" type="text" name="rootdir" value="<?=$f1_rootdir?>"/>
				</div>
		</div>
		<div class="row mt-1">

			<div class="col col-12 col-sm-2"> 
				<button class="btn btn-primary w-100 h-80" id="scan_now" type="submit" name="MACH_SCAN" onclick='bitteWarten();'>Scan</button>
			</div>

			<div class="col col-12 col-sm-3">       
				<div class="form-check">
					<input class="form-check-input" type="radio" name="type" id="dir" value="dir" <?=$f1_dir?>>
					<label class="form-check-label" for="exampleRadios1">
					Nur Verzeichnisse
					</label>
				</div>
				<div class="form-check">
					<input class="form-check-input" type="radio" name="type" id="file" value="file" <?=$f1_file?>>
					<label class="form-check-label" for="exampleRadios2">
					Nur php-Dateien
					</label>
				</div>
				<div class="form-check">
					<input class="form-check-input" type="radio" name="type" id="dirfile" value="dirfile" <?=$f1_dirfile?>>
					<label class="form-check-label" for="exampleRadios2">
					Verzeichnisse und php-Dateien
					</label>
				</div>        
			</div>

			<div class="col col-12 col-sm-7"> 
				<label for="tiefe">Max. Tiefe:</label>
				<input id="tiefe"  class="form-control w-25" type="number" name="tiefe" value="<?=$f1_tiefe?>"/>
			</div>

		</div>
	</form>
</div>
<!------------------------------------------------------------------ -->   
<div id="bitteWarten" class ="alert" role="alert">

</div>
<!------------------------------------------------------------------ -->   
<div class="container-fluid">
<h2 ><?=$ueberschrift?> Pfade und Dateien</h2>
	<table id="tabelle" class="display" style="cursor: pointer;">
		<thead><tr>
			<th></th>
			<th>Verzeichnis- / Dateiname</th>
			<th><?=$spaltenHeader?></th>
		</tr></thead>
		<tbody>
			<?=$tabelle?>
		<tbody>
		
	</table>
</div>
<div id="tabbttns"></div>
<!-------------------------------------------------------------------->  
<?php 

require_once(__DIR__."/../include/code/sitescan_dialog.php"); 
?>
<!-------------------------------------------------------------------->   
<?php $conf->getFooter();?>
</body>
</html>

