<!DOCTYPE html>
<html lang='de'>
<head>
 
<title><?=$titel?></title>

<script>

function submitit(){
    $('#js_submit_platzhalter').attr('name', 'MACH_SAVE');
    $('#form01').submit();
}

$( document ).ready(function() {
    
    document.querySelectorAll('.autosave').forEach(item => {

        item.addEventListener("blur", function() {
            submitit();
    });

    })

});

</script>
</head>
<body>
<!-------------------------------------------------------------------->
<form id='form01' method='post'>
<!-------------------------------------------------------------------->
<div class="container">
    <!-------------------------------------------------------------------->
    <div class='row'>
        <div class="col col-6">
            <!-------------------------------------------------------------------->   
            <div class="row mb-1">
                <div class='col col-4'>
                    <span class='input-group-text' id='absendeadresselabel'>Adresse (von)</span>
                </div>
                <div class='col col-8'>
                    <input type='text' class='autosave form-control' name='absendeadresse' value='<?=$absendeadresse?>' aria-describedby='basic-addon3'>
                </div>
            </div>
            <!-------------------------------------------------------------------->   
            <div class="row mb-1">
                <div class='col col-4'>
                    <span class='input-group-text' id='absendeadresselabel'>Name (von)</span>
                </div>
                <div class='col col-8'>
                    <input type='text' class='autosave form-control' name='absendename' value='<?=$absendename?>' aria-describedby='basic-addon3'>
                </div>
            </div>
            <!-------------------------------------------------------------------->   
            <div class="row">
                <div class='col col-4'>
                    <span class='input-group-text' id='absendeadresselabel'>Betreff</span>
                </div>
                <div class='col col-8'>
                    <input type='text' class='autosave form-control' name='betreff' value='<?=$betreff?>' aria-describedby='basic-addon3'>
                </div>
            </div>
            <!-------------------------------------------------------------------->   
            <div class="row mt-3 align-items-end">
                <div class='col col-6'>
                    <button class='btn btn-primary btn-block' name='MACH_TESTMAIL'>Sende diese Mail zum Test an:</button>
                </div>
                <div class='col col-6'>
                    <input class='autosave form-control pl-auto' name='mailan' value='<?=$empfaengertest?>'>  
                </div>
            </div>
            <!--------------------------------------------------------------------> 
        </div> <!-- col 1 -->
        <!-------------------------------------------------------------------->
        <div class="col col-6">
            <!-------------------------------------------------------------------->   
            <div class="row mb-1">
                <div class='col col-4'>
                <span class='input-group-text' id='absendeadresselabel'><?=$titel?></span>  
                </div>
                <div class='col col-4'>
                    <input <?=$chckd?> name='txformat' class='form-control' type='checkbox' id='txformat'  data-width='100'  data-toggle='toggle' data-onstyle='outline-success' data-offstyle='outline-danger' data-on='HTML' data-off='Nur Text' onchange='submitit();'/>   
                </div>
                <div class='col col-4 pl-auto'>
                    <button class='btn btn-primary btn-block' name='MACH_SAVE'>Speichern</button>
                    <input type='hidden' id='js_submit_platzhalter' name='js_submit_platzhalter'/>
                </div>
            </div>
            <!-------------------------------------------------------------------->   
            <div class="row mt-3">
                <div class="col pl-auto">
                    <div class='alert alert-primary small' role='alert'>
                        Bitte verwenden Sie den Platzhalter <b>##LINK##</b> f&uuml;r den automatisch erzeugten Link zur Erstellung/&Auml;nderung des Passwortes. 
                    </div>
                </div>
            </div>
            <!-------------------------------------------------------------------->   
            <div class="row align-items-end ">
                <div class="col pl-auto">
                    <div class='alert alert-<?=$alertcolor?> small' role='alert'>
                        <?=$alerttext?>
                    </div>
                </div>
            </div>
            <!--------------------------------------------------------------------> 
        </div> <!-- col 2 -->
        <!-------------------------------------------------------------------->
    </div> <!-- row 1 -->
    <!-------------------------------------------------------------------->

</div> <!-- container -->
<!-------------------------------------------------------------------->
<!-------------------------------------------------------------------->
<!-------------------------------------------------------------------->
<hr/>
<div class="container mt-3">
    <div class="row">
        <div class="col col-6">
            <div class="row">
                <textarea  rows=20 class='autosave form-control w-100' name='mailtext'><?=$sourcecode?></textarea>
            </div>
        </div>
        <div class="col col-6">
            <?=$vorschau?>
        </div>
    </div>
</div>
<!-------------------------------------------------------------------->
</form>
<!--------------------------------------------------------------------> 
