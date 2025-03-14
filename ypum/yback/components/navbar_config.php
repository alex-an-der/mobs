
<link href="./../css/navbar.css" rel="stylesheet">
<link href="./../lib/bootstrap_toggles/css/bootstrap4-toggle.css" rel="stylesheet">
<script src="./../lib/bootstrap_toggles/js/bootstrap4-toggle.js"></script>

<?php
  $install_status = $conf->isInstallmodus();
  $toggler = "$('#im_aktiv').bootstrapToggle('on');";
  if($install_status) $toggler = "$('#im_aktiv').bootstrapToggle('off');";
  $_SESSION['ypum_sourcekeyinst'] = $conf->getRandKey();
  $sourceHash = password_hash($_SESSION['ypum_sourcekeyinst'], PASSWORD_DEFAULT);

  /*-----------------
  Freischalten einzelner Stufen, um Fehlermeldung bei Miss-Konfiguration (trotzdem) zu vermeiden.
  -----------------*/
  $status_disabled = "class='nav-link disabled' aria-disabled='true'";
  $status_enabled = "class='nav-link'";

  $navstatus_scanner = $status_disabled;
  if (file_exists(__DIR__."/../../yconf/prefix.json")) $navstatus_scanner = $status_enabled;


  

?>

<script>

  $(document).ready(function() {
    <?=$toggler?>
 
    // Listener Toggle (Installationsmodus)
    $("#im_aktiv").change(function(){
      
      var request;
      if (request) {
          request.abort();
      }

      var datensatz = {
          'installmodus': !(document.getElementById('im_aktiv').checked),
          'sourceHash': "<?=$sourceHash?>"
      };

      request = $.ajax({
          url: "./../ajax/inst_mode_ajax.php",
          type: "post",
          data: JSON.stringify(datensatz)
      });

      request.done(function (response, textStatus, jqXHR){
          //console.log(response);
          //window.location.reload();
          //alert($("#im_aktiv").value);
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



  }); // doc_ready

</script>
<nav class="navbar navbar-expand-lg navbar-light bg-light">

      <a class="navbar-brand" href="index.php">
        Konfiguration
        <img src="./../img/logo.png" width="50" height="50" class="d-inline-block align-top" alt="">
        
      </a>
      <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" 
         aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button> 
      
      <div class="collapse navbar-collapse" id="navbarSupportedContent">


        <ul class="navbar-nav mr-auto">
          <li id="rollen" class="nav-link"><a class="nav-link" href="./../y-config/rollen.php">Rollen</a></li>
          <li id="diverses" class="nav-link"><a class="nav-link" href="./../y-config/diverses.php">Einstellungen</a></li>
          
          <li id="userdata" class="nav-link"><a class="nav-link" href="./../y-config/userdata.php">Nutzerdaten</a></li>

          <li id="mails" class="nav-link dropdown">
            <a class="nav-link dropdown-toggle" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">Mailverkehr</a>
            <div class="dropdown-menu">
              <a class="nav-link" href="./../y-config/mail_neu.php">Neuer Nutzer</a>
              <a class="nav-link" href="./../y-config/mail_vergessen.php">Passwort vergessen</a>
            </div>
          </li>
          <li id="formulare" class="nav-link dropdown">  
            <a class="nav-link" href="./../y-config/form_dummy.php">Formulare</a>
          </li>

          <li id="sitescan" <?=$navstatus_scanner?>><a class="nav-link" href="./../y-config/sitescan.php">Scanner</a></li>
          <li id="lizenzen" class="nav-link"><a class="nav-link" href="./../y-config/lizenzen.php">Lizenzen</a></li>

          <li id="bereiche" class="nav-link dropdown">
            <a class="nav-link dropdown-toggle" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">Bereiche</a>
            <div class="dropdown-menu">
              <a class="nav-link" href="./../y-install/index.php">Installation</a>
              <a class="nav-link aktiverbereich">Konfiguration</a>
              <a class="nav-link " href="./../y-admin/index.php">Verwaltung</a>
              <a class="nav-link " target="_blank" href="./../../ydemo/demo.php">Demo-Tab</a>
              <a class="nav-link " href="./../../yfront/login.php">Log-In</a>
              <a class="nav-link " href="./../../yfront/logout.php">Log-Out</a>
            </div>
          </li>
        </ul>

<form method='post' id='imForm' class="form-inlin e my-2 my-lg-0">
<input 
class="form-control b-2" type="checkbox" id="im_aktiv" name="instModus" data-toggle="toggle" 
data-onstyle="outline-success" data-offstyle="outline-danger" data-on="Aktiv" data-off="Installation"/>
</form>

      </div>

    </nav>