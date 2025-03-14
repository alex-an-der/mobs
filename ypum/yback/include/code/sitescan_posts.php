<?php
namespace ypum;
if(isset($ypum))if(!empty($ypum->getDB())) $dbm = $ypum->getDB();

if(isset($_POST['MACH_EDIT'])){
	// Berechne Berechtigungszahl
	$gesamtrolle = 0;

	if(isset($_POST['rollen'])){
		$rollen = $_POST['rollen'];
		foreach($rollen as $rolle){
			$gesamtrolle += pow(2,$rolle);
		}	
	}else{
		$rollen = 1;
	}

	$aselectedIDs = explode (",",$_POST['ids']);

	foreach($aselectedIDs as $ID){
		$values = array();
		
		$values[] = $gesamtrolle;
		$values[] = $ID;

		$dbm->query("UPDATE y_sites set roles=? WHERE ID=?", $values, false);
	}
}

/* *****************  START SCAN ODER NICHT-SCAN ?    ********************* */

// Noch kein Modus festgestellt? 
if(!isset($_SESSION['ypum_scanned_results'])){
	$_SESSION['ypum_scanned_results']=0;
	if(!isset($_SESSION['ypum_rootdir'])) $_SESSION['ypum_rootdir'] = __DIR__;
	$_SESSION['ypum_tiefe'] = 0;
	$_SESSION['ypum_ueberschrift']="Gespeicherte ";
	$_SESSION['ypum_dir'] = "checked";
	$_SESSION['ypum_file'] = "";
	$_SESSION['ypum_dirfile'] = "";	
}

if(isset($_POST['MACH_SCAN_WEG']) || isset($_POST['MACH_SITE_CHECK'])){
	$_SESSION['ypum_scanned_results']=0;
}

if(isset($_POST['MACH_SITE_CHECK'])){
	$checkSites = true;
}else{
	$checkSites = false;
}



// Frischer Scan? Formulardaten in der Session speichern
if(isset($_POST['MACH_SCAN'])){
	$_SESSION['ypum_scanned_results']=1;
	
	if(empty($_POST['rootdir'])){
	
		$yBasis = $conf->getYpumRoot(false);
		if (realpath($yBasis."/../")){
			$_SESSION['ypum_rootdir'] = realpath($yBasis."/../");
		}else{
			$_SESSION['ypum_rootdir'] =  __DIR__;
		}
	}else{
		$_SESSION['ypum_rootdir'] = $_POST['rootdir'];
	}
	
	$_SESSION['ypum_tiefe'] = $_POST['tiefe'];
	$_SESSION['ypum_ueberschrift']="Gefundene ";
	$typ = $_POST['type'];
	$_SESSION['ypum_dir'] = "";
	$_SESSION['ypum_file'] = "";
	$_SESSION['ypum_dirfile'] = "";
	if (strcmp($typ,"dir")==0) $_SESSION['ypum_dir'] = "checked";
	elseif (strcmp($typ,"file")==0) $_SESSION['ypum_file'] = "checked";
	elseif (strcmp($typ,"dirfile")==0) $_SESSION['ypum_dirfile'] = "checked";
}

// Scanmodus oder nicht? Anzeige entsprechend:
$filtered = -1;
$tabelle= "";
if(isset($_POST['filterbit'])){
	$filtered = intval($_POST['filterbit']);
	if(isset($_POST['filter_reset'])){
		$filtered = -1;
		$res = $dbm->query("SELECT id, dir, roles from y_sites");
	}else{
		$argy[] = $_POST['filterbit'];
		$res = $dbm->query("SELECT id, dir, roles from y_sites WHERE ( POWER(2, ?) & roles) > 0", $argy);
	}
	foreach ($res as $row){
		$tabelle .= getTR_inDB($row);
	}

}elseif($_SESSION['ypum_scanned_results']){
	// Scan - SESSIONDATREN werden bei "MACH_SCAN" gesetzt -> hier nur Anzeige
	if ($_SESSION['ypum_dir']=="checked") 		{dir_rekursiv	($_SESSION['ypum_rootdir'],$_SESSION['ypum_tiefe']);}
	if ($_SESSION['ypum_file']=="checked") 		{files_rekursiv	($_SESSION['ypum_rootdir'],$_SESSION['ypum_tiefe']);}
	if ($_SESSION['ypum_dirfile']=="checked") 	{dir_rekursiv	($_SESSION['ypum_rootdir'],$_SESSION['ypum_tiefe']);files_rekursiv($_SESSION['ypum_rootdir'],$_SESSION['ypum_tiefe']);}
}else{
	// Kein Scan - SESSION zurücksetzen...

	$yBasis = $conf->getYpumRoot(false);
	if (realpath($yBasis."/../")){
		$basispafad = realpath($yBasis."/../");
	}else{
		$basispafad =  __DIR__;
	}

	$_SESSION['ypum_rootdir'] =$basispafad;
	$_SESSION['ypum_tiefe'] = 0;
	$_SESSION['ypum_ueberschrift']="Gespeicherte ";
	$_SESSION['ypum_dir'] = "checked";
	$_SESSION['ypum_file'] = "";
	$_SESSION['ypum_dirfile'] = "";
	//...und Tabelle frisch anzeigen
	$res = $dbm->query("select id, dir, roles from y_sites");
	
	
	if(!$checkSites){
		// Direkt verwerten (Anzeige in Tabelle)
		foreach ($res as $row){
			$tabelle .= getTR_inDB($row);
		}
	}else{
		
		$aFiles = array();

		// RES durchgehen und wenn ROW ein Verzeichnis ist, dann auch die Untereinträge hinzufügen
		// Das ganze kann rekursiv werden, daher gesonderte Funktionverwenden.
		foreach ($res as $row){
			// Um die PRüfung nicht allzu lange dauert, die anderen Parameter außer DIR gleichsetzen, damit das AUssortieren der 
			// Dubletten dann schneller geht. Sonst muss die "richtige" Dublette entferne werden. Zudem muss das
			// Bttn-Handling überarbeitet werden, wenn wirklich aus den SUchergebnissen Aktionen (hinzu/delete) ermöglicht werden sollen.
			// Der Fokus ist ja hier auch, dass die ypum überall eingebunden ist. Auch kann die Rollenspalte so umgenutzt werden.
			//$row['id'] = -1;
			//$row['roles'] = "";

			$aFiles = addFiles(trim($row['dir']), $aFiles);
		}
		// Dubletten aussortieren
		// Nach Pfad aufsteigend sortieren
		//$dir  = array_column($aGespeicherteRows	, 'dir');
		//$id = array_column($aGespeicherteRows	, 'id');
		//array_multisort($dir, SORT_ASC, $id, SORT_DESC, $aGespeicherteRows);

		$aFiles = array_unique ($aFiles);


		foreach ($aFiles as $file){
			$row = array();
			$row['id'] = -1;
			$row['roles'] = "";
			$row['dir'] = $file;
			$tabelle .= getTR_inDB($row);
		}		
	}

}


if($_SESSION['ypum_scanned_results']) $ueberschrift = "Gescannte ";
	else $ueberschrift = "Gespeicherte ";

$f1_dirfile = $_SESSION['ypum_dirfile'];
$f1_file = $_SESSION['ypum_file'];
$f1_dir = $_SESSION['ypum_dir'];
$f1_rootdir = realpath($_SESSION['ypum_rootdir']);
$f1_tiefe = $_SESSION['ypum_tiefe'];

function addFiles($file, $aFiles){

	$aFiles[] = $file;

	if(is_dir($file)){
	
		// Directoty nach Files auflösen
		$ret = files_rekursiv($file, 99, false);
	
		foreach($ret as $unterDatei){
			$aFiles[] = $unterDatei;
		}
		
	}
	
	return $aFiles;

}
?>