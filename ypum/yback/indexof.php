<?php 
namespace ypum;
// ypum später. Zuerst die Header...
@session_start();
$dataFormat = "Y-m-d H:i:s"; 
if(isset($_GET['f']) && isset($_GET['hash']) && isset($_SESSION['ypum_sourcekeyidx'])){
    
    $datei = $dir."/".urldecode($_GET['f']);
    $hash = urldecode($_GET['hash']);
    
    if (file_exists($datei) && password_verify ($_SESSION['ypum_sourcekeyidx'] , $hash ) ) {

        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="'.basename($datei).'"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($datei));
        readfile($datei);
    }else{
        require_once(__DIR__."/ypum.php");
        $_SESSION['ypum_sourcekeyidx'] = bin2hex(random_bytes(37));
        $sourceHash = password_hash($_SESSION['ypum_sourcekeyidx'], PASSWORD_DEFAULT);
    }

}else{
    require_once(__DIR__."/ypum.php");
    $_SESSION['ypum_sourcekeyidx'] = bin2hex(random_bytes(37));
    $sourceHash = password_hash($_SESSION['ypum_sourcekeyidx'], PASSWORD_DEFAULT);
}
?>


<!DOCTYPE html>
<html lang="de">
<head>


<?php $titel = "...".substr($dir, strlen($dir)-30, 30); ?>
<title><?=$titel?></title>

<script>
$(document).ready(function() {

    var table = $('#tabelle').DataTable({
		stateSave: true,
		"order": [[ 0, "asc" ]]
    });

    // Listener für den Tabellen-Klick (GET-Request senden)
    $('#tabelle tbody').on( 'click', 'tr', function () {
        
        file_enc  = encodeURIComponent($(this).attr('data-dateiname'));
        hash_enc  = encodeURIComponent("<?=$sourceHash?>");
        window.location.href = "?f=" + file_enc + "&hash=" + hash_enc; 
    });  	

});
</script>
</head>
<body>
<?php
$tab = "";

if ( is_dir ( $dir ))
{
    if ( $handle = opendir($dir) )
    {
        $id=1000;
        while (($file = readdir($handle)) !== false)
        {
            if((strcmp(substr($file,0,1),".")!=0) && !(is_dir($dir."/".$file))){

                $id++;

                $stat = stat($dir."/".$file);
                $size = $stat['size'];
                $einheit = array("Byte", "kB", "MB", "GB", "TB", "PB");
                $tausender = 0;
                while($size>1000){
                    $size =  $size/1000;
                    $tausender++;
                }
                $size = number_format ($size ,0 ,",", ".");
                $spalte3 =  $size." ".$einheit[$tausender]."<br>";
                $spalte4 = gmdate($dataFormat, $stat['mtime']+date("Z"));
        
                $typTrennerPos = strrpos($file, '.');
                $spalte1 = $file;
                $spalte2 = "";
                if($typTrennerPos>0){
                    $spalte1 = substr($file, 0, $typTrennerPos);
                    $spalte2 = substr($file, $typTrennerPos, strlen($file)-$typTrennerPos);
                }
                $tab .= "<tr data-dateiname='$file'><td>$spalte1</td></form>";
                $tab .= "<td>$spalte2</td><td>$spalte3</td><td>$spalte4</td></tr>";
            }
        }
        closedir($handle);
    }
}


?>
<div class="container">

    <table id="tabelle" class="display" style="cursor: pointer;">
    <thead><tr>
        <th>Dateiname</th>
        <th>Typ</th>
        <th>Gr&ouml;&szlig;e</th>
        <th>Ge&auml;ndert</th>
    </tr></thead>
    <tbody>
        <?=$tab?>
    <tbody>
    </table>
</div>

</body>
</html>