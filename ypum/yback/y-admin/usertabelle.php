<?php 
require_once(__DIR__."/../ypum.php");
$dataFormat = "Y-m-d H:i:s"; 
?>
<!DOCTYPE html>
<html lang="de">
<head>



<title>Angemeldete Nutzer</title>
<style>
    /* Chrome, Safari, Edge, Opera */
    input::-webkit-outer-spin-button,
    input::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
    }

    /* Firefox */
    input[type=number] {
    -moz-appearance: textfield;
    }
</style>
</head>
<body>
<!-------------------------------------------------------------------->
<?php 
include_once(__DIR__."/../components/navbar_userverwaltung.php");

$_SESSION['ypum_sourcekeyuser'] = $conf->getRandKey();
$sourceHash = password_hash($_SESSION['ypum_sourcekeyuser'], PASSWORD_DEFAULT);

?>
<!-------------------------------------------------------------------->

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

function filterLostFocus(){
        location.reload();
    }

$(document).ready(function() {




    var table = $('#tabelle').DataTable({
        stateSave: true,
        colReorder: true
    });

    new $.fn.dataTable.Buttons( table, {
		buttons: [
			{
                extend: 'copy',
                title: 'Angemeldete Nutzer'
            },
            {
                extend: 'csv',
                title: 'Angemeldete Nutzer'
            },
            {
                extend: 'excel',
                title: 'Angemeldete Nutzer'
            },
			{
                extend: 'pdf',
                title: 'Angemeldete Nutzer'
            },
			{
                extend: 'print',
                title: 'Angemeldete Nutzer'
            }
        ]
    } );


	let buttons = table.buttons();
	buttons.each(function(index){
		index.node.className = "btn btn-info";
	});
	buttons.container()[0].className = "ml-5";


	$('#tabbttns').html(table.buttons().container());


    // Listener für User-Löschen-Button
    $("#bttn_loeschen").click(function(){
        anzahl = $("#tabelle .selected").length;
        if (anzahl>0){
            if (anzahl>1){
                res = confirm("Achtung!\n\nMit OK löschen Sie unwiderruflich die " + anzahl + " ausgewählten Nutzerkonten.");
            }else{
                res = confirm("Achtung!\n\nMit OK löschen Sie unwiderruflich das ausgewählte Nutzerkonto.");
            }
        }else{
            alert("Sie müssen ein oder mehrere Nutzerkonten wählen, um diese zu löschen.");
        }

        // Anfrage per Ajax starten
        if(res){

            $('#tabelle .selected').each(function () {
            
                var request;
                if (request) {
                    request.abort();
                }

                var datensatz = {
                    'action' : "DELETE",
                    'uid': $(this).attr('data-uid'),
                    'sourceHash': "<?=$sourceHash?>"
                };

                request = $.ajax({
                    url: "./../ajax/usertabelle_ajax.php",
                    type: "post",
                    data: JSON.stringify(datensatz)
                });

                request.done(function (response, textStatus, jqXHR){

                    window.location.reload();

                });

                // Callback handler that will be called on failure
                request.fail(function (jqXHR, textStatus, errorThrown){
                // Log the error to the console
                    console.error(
                        "The following error occurred: "+
                        textStatus, errorThrown
                    );
                });
            });
        }
	});

    // Listener für Farben-Reset-Button
	$("#bttn_color_reset").click(function(){
        window.location.reload();
	})

    // Listener für Filter-Reset-Button
	$("#bttn_filter_reset").click(function(){
        $('#tabelle tfoot input').val('');
        $('#tabelle tfoot input').change();
	});

    // Listener für Tabelle-Reset-Button
	$("#bttn_tabelle_reset").click(function(){
		table.state.clear();
        window.location.reload();
	});

    // Listener für Tabelle-Reset-Button
	$("#bttn_tog_sel").click(function(){
		$('#tabelle tr').each(function () {
            $(this).toggleClass('selected');
	    });
	});

    // Listener, wenn ein Fokus verloren wird (Filter setzen)
    $("input").blur(function(){
        filterLostFocus();
    }); 

    // Toggle-Links
    setzeTogglerNamen(); 

    // Listener bei Spalten-Reihenfolge-Änderung
    table.on( 'column-reorder', setzeTogglerNamen);
   
    

    // Filterfelder in der Fußleiste anzeigen
    $('#tabelle tfoot th').each( function () {
        var title = $('#tabelle tfoot th').eq( $(this).index() ).text();
        $(this).html( '<input onBlur="filterLostFocus();" type="text" placeholder="Filtere '+title+'"  />' );
        
    } );

    

    // Fußleisten-Filter anwenden
    $("#tabelle tfoot input").on( 'keyup change', function () {
        table
            .column( $(this).parent().index()+':visible' )
            .search( this.value )
            .draw();
    } );

    // Spalten ein- und ausblenden
    $('a.toggle-vis').on( 'click', function (e) {
    e.preventDefault();
    // Get the column API object
    var column = table.column( $(this).attr('data-column') );
    // Toggle the visibility
    column.visible( ! column.visible() );
    if(column.visible()) $(this).css("color", "green");
    else $(this).css("color", "red");
    });

    // Listener für TD mit den Rollen / der Rollengruppe
    $( '.td_roles').focus(function() {
        zeigeRollen($(this).html());
    });


    // Listener fuer die Bearbeitung von Tabellenzellen
    $('#tabelle td').on( 'input', function () {
        
        // TD der Rollengruppe?
        if($(this).hasClass( "td_roles")){

            wert = $(this).html();
            wert = wert.replace('<br>','');
            wert = wert.replace('<div>','');
            wert = wert.replace('</div>','');
            zeigeRollen(wert);
        }
        
        $(this).css("background-color","#F3F781");
    });

    // Doppelklick auf Zelle => Speichern
    $('#tabelle td').on( 'dblclick', function () {

    let alle = false;
    selectierteZeilen = $('#tabelle .selected').length;

    if (selectierteZeilen > 1){

        if (confirm('Möchten Sie die Änderung auf alle ' + selectierteZeilen + ' Zeilen anwenden?')) {
            alle = true;
        }
    }

    neuerWert = $(this).html();

    if(alle){
        aktuelleSpalte = this.cellIndex; 
        $('#tabelle .selected').each( function (index) {
            speichereZellinhalt(this.childNodes[aktuelleSpalte], neuerWert);
        });
    }else{
        speichereZellinhalt(this, neuerWert);
    }

    });

    // Listener für Klick in Tabelle zum Auswählen
    $('#tabelle tbody').on( 'click', 'tr', function () {
    $(this).toggleClass('selected');

    });

    function setzeTogglerNamen(){
        let column = $('#tabelle').dataTable().api().columns();
        let spaltenanzahl = column.header().length;
        for(i=0;i<spaltenanzahl;i++){
            Titel = $('#tabelle').dataTable().api().columns().header()[i]['textContent'];
            JQelementID = "#togvis" + i;
            $(JQelementID).html(Titel);
            
            if(column.visible()[i]) $(JQelementID).css("color", "green");
            else $(JQelementID).css("color", "red");

        }
    }

    function zeigeRollen(wert){
        $('#rollencode').val(wert);
        rollenButtonsAktualisieren();
    }

    function speichereZellinhalt(that, thatvalue){

        var request;
        if (request) {
            request.abort();
        }
        
        var datensatz = {
            'action' : "EDIT",
            'uid': $(that).attr('data-uid'),
            'fid': $(that).attr('data-fid'),
            'typ': $(that).attr('data-typ'),
            'neuerwert': thatvalue,
            'sourceHash': "<?=$sourceHash?>"
        };

        request = $.ajax({
            url: "./../ajax/usertabelle_ajax.php",
            type: "post",
            data: JSON.stringify(datensatz)
        });

        request.done(function (response, textStatus, jqXHR){
           
            response = JSON.parse(response);
    
            $(that).html(response.erg);
            if(response.res) {
                $(that).css("background-color","#81F781");
            }else{         
                $(that).css("background-color","#F7819F");
            }
        });

        // Callback handler that will be called on failure
        request.fail(function (jqXHR, textStatus, errorThrown){
            // Log the error to the console
            console.error(
                "The following error occurred: "+
                textStatus, errorThrown
            );
        });
    }

      /////////////////////
     /// ROLLEN-LEISTE ///
    /////////////////////

    aktiveBttnKlasse = "btn btn-success m-1 rollenbttn";
    passiveBttnKlasse = "btn btn-outline-success m-1 rollenbttn";

    // Einheitliche Größe festlegen
    maxw=0;
    maxh=0;
    $('.rollenbttn').each(function () {
        w = $(this).width();
        if(w>maxw) maxw=w;

        h = $(this).height();
        if(h>maxh) maxh=h;
	});
    $('.rollenbttn').each(function () {
            $(this).width(maxw);
            $(this).height(maxh);
	});
 

    // Listener für Bttn-CLick
    $('.rollenbttn').on( 'click', function () {
        //Toggle diesen Bttn
        role_active = $(this).attr("data-role_active");
        if(role_active==1) $(this).attr("data-role_active",0);
        else  $(this).attr("data-role_active",1);

        // Bttns durchscannen und:
        // 1. Aktive Bttns ohne Outline anzeigen (und passive mit)
        // 2. Werte (2^bit) von allen aktiven einsammeln
        rollencode=0;
        $('.rollenbttn').each(function () {
            role_active = $(this).attr('data-role_active');
            bit = $(this).attr('data-bit');
            if(role_active==1){
                $(this).attr('class', aktiveBttnKlasse);
                rollencode = rollencode + Math.pow(2, bit);
            }else{
                $(this).attr('class', passiveBttnKlasse);
            }
	    });
        $('#rollencode').val(rollencode);

        
	});

    // Listener für Inputbox-Änderungen
    $('#rollencode').on( 'input', rollenButtonsAktualisieren);

    // Listener für Übernehmen-Button
    $('#bttn_applayRole').on( 'click', function () {

        $('#tabelle .selected .td_roles').html($('#rollencode').val());
        $('#tabelle .selected .td_roles').css("background-color","#F3F781");
       

    });

    function rollenButtonsAktualisieren(){
        rc = $('#rollencode').val();
        if(rc=="") rc=0;
        console.log(rc);

        for(bit=32; bit>=0; bit--){

            id = "#rbttn" + bit;
            bitwert = Math.pow(2,bit);
            if(rc-bitwert >=0){
                $(id).attr('data-role_active',1);
                $(id).attr('class', aktiveBttnKlasse);
                rc = rc-bitwert;
            }else{
                $(id).attr('data-role_active',0);
                $(id).attr('class', passiveBttnKlasse);
            }
        }
    }
    

});



</script>
<style>
a.toggle-vis:hover{
    cursor: pointer;
}
</style>
<!-------------------------------------------------------------------->
<?php


$tabelle = "";
$header = "";
$spaltentoggler = "<p>Ein- und Ausblenden von Spalten: ";
$togglertrenner = "&nbsp;&bull;&nbsp;";
$spaltennummer = 0; 

$alleDetails = $dbm->query("select userID, fieldID, fieldvalue from y_user_details", array(), true);

foreach($alleDetails as $tmpdetail){
    $userdetail[$tmpdetail['userID']][$tmpdetail['fieldID']] = $tmpdetail['fieldvalue'];
}

$alleFelder = $dbm->query("select ID, uf_name, fieldname from y_user_fields", array(), true);

$alleUser = $dbm->query("select id, locked, mail, roles, lastlogin, created, validated from y_user", array(), true);

$ersterlauf = true;
foreach($alleUser as $user){
    
    $uid = $user['id'];
    $tabelle .= "<tr data-uid='$uid'>";
    
    if ($ersterlauf){
        
        $header .= "<tr><th>locked</th><th>Mailadresse</th><th>Rollengruppe</th><th>Letzter Log-In</th><th>Angelegt</th><th>Validiert</th>";
        // Erhoehe Spaltennummer situativ, dann ist es keine magic number, die irgendwo steht.
        $spaltentoggler .= "<a class='toggle-vis' id='togvis0' data-column='0'></a>";               $spaltennummer++;
        $spaltentoggler .= "$togglertrenner<a class='toggle-vis' id='togvis1' data-column='1'></a>";$spaltennummer++;
        $spaltentoggler .= "$togglertrenner<a class='toggle-vis' id='togvis2' data-column='2'></a>";$spaltennummer++;
        $spaltentoggler .= "$togglertrenner<a class='toggle-vis' id='togvis3' data-column='3'></a>";$spaltennummer++;
        $spaltentoggler .= "$togglertrenner<a class='toggle-vis' id='togvis4' data-column='4'></a>";$spaltennummer++;
        $spaltentoggler .= "$togglertrenner<a class='toggle-vis' id='togvis5' data-column='5'></a>";$spaltennummer++;
         
    }

    // Basisdaten //

    $gleicheAtts = "contenteditable='true' data-typ='basis' data-uid='$uid'";
    $tabelle .= "<td $gleicheAtts data-fid='locked'>".$user['locked']."</td>";
    $tabelle .= "<td $gleicheAtts data-fid='mail'>".$user['mail']."</td>";
    $tabelle .= "<td $gleicheAtts class='td_roles' data-fid='roles'>".$user['roles']."</td>";

    $aStatusDaten = ["lastlogin","created","validated"];
    foreach ($aStatusDaten as $sStatusData){
        if(isset($user[$sStatusData])){
            $date = new DateTime($user[$sStatusData]);
            $tabelle .= "<td>".$date->format($dataFormat)."</td>";
        }else{
            $tabelle .= "<td>---</td>";
        }
    }

    // Detaildaten //
    
    foreach($alleFelder as $feld){

        $fid = $feld['ID'];
        $gleicheAtts = "contenteditable='true' data-typ='detail' data-uid='$uid' data-fid=$fid";

        if ($ersterlauf){
            $header .= "<th>".$feld['uf_name']."</th>";
            $spaltentoggler .= "$togglertrenner<a class='toggle-vis' id='togvis$spaltennummer' data-column='$spaltennummer'></a>";
            $spaltennummer ++;
        }
        
        if(isset($userdetail[$uid][$fid])) $tabelle .= "<td $gleicheAtts  class='datenfeld'>".$userdetail[$uid][$fid]."</td>";
            else $tabelle .= "<td $gleicheAtts ></td>";
    }
    $ersterlauf = false;

    $header .= "</tr>";
    $tabelle .= "</tr>";
    
}




      /////////////////////
     /// ROLLEN-LEISTE ///
    /////////////////////

    $rollen = $dbm->query("select bit, name, role_comment from y_roles");
    $rollenbttns = "";
    
    foreach($rollen as $rolle){
        $name = $rolle['name'];
        $bit = $rolle['bit'];
        $disabled = "";
        if (!empty($name)) 
            $rollenbttns .= "<button id='rbttn$bit' $disabled class='btn btn-outline-success rollenbttn mr-2 mb-2' type='button' data-bit='$bit' data-role_active=0 >$name</button>";
    }


if (strlen($header)==0){
    $header="<tr><td></td></tr>";
    $tabelle="<tr><td>Keine Daten vorhanden.</td></tr>";
}

?>
<!-------------------------------------------------------------------->
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col col-12 col-md-6">
            <h2 >Nutzerverwaltung</h2>
            Sie k&ouml;nnen Werte direkt &auml;ndern. Zum Speichern der &Auml;nderung <b>doppelklicken</b> Sie bitte auf die gew&uuml;nschte Zelle.
            <?=$spaltentoggler."</p>"?>

            <button disbled class="btn btn-outline-primary m-1" id="bttn_color_reset">
            Markierungen entfernen</button>

            <button class="btn btn-outline-primary m-1" id="bttn_filter_reset">
            Filter zur&uuml;cksetzen</button>

            <button class="btn btn-outline-primary m-1" id="bttn_tabelle_reset">
            Tabelle zur&uuml;cksetzen</button>

            <button class="btn btn-outline-primary m-1" id="bttn_tog_sel">
            Auswahl invertierern</button>

            <button  class="btn btn-outline-primary m-1" id="bttn_loeschen">
            Nutzer l&ouml;schen</button>


        </div>
        <div class="col col-12 col-md-6">
            <div class="row mb-3">
                <div class="col col-4">
                    <input id='rollencode' type="text" class="form-control" placeholder='Rollencode'>
                    
                </div>
                <div class="col col-8">
                <button class="btn btn-primary " id="bttn_applayRole">&Uuml;bernehmen</button>
                </div>
            </div>

            <div class="row">
                <div class="col col-12">
                    <?=$rollenbttns?>
                </div>
            </div> 
        </div>
    </div>


    <div class="row">
        <div class="col col-12">
            <table id="tabelle" class="display" style="cursor: pointer;">
                <thead>
                    <?=$header?>
                </thead>
                <tbody>
                    <?=$tabelle?>
                </tbody>
                <tfoot>
                    <?=$header?>
                </tfoot>
            </table>
        </div>
    </div>       
</div>
<div id="tabbttns"></div>
<!-------------------------------------------------------------------->   
<?php $conf->getFooter();
?>
</body>
</html>