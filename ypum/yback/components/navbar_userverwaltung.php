<link href="./../css/navbar.css" rel="stylesheet">
<nav class="navbar navbar-expand-lg navbar-light bg-light">

      <a class="navbar-brand" href="index.php">
        Verwaltung
        <img src="./../img/logo.png" width="50" height="50" class="d-inline-block align-top" alt="">
      </a>
      <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button> 
      
      <div class="collapse navbar-collapse" id="navbarSupportedContent">
        <ul class="navbar-nav mr-auto">
          <li id="usertabelle" class="nav-link"><a class="nav-link" href="./../y-admin/usertabelle.php">&Uuml;bersicht</a></li>
          <li id="usertabelle" class="nav-link"><a class="nav-link" href="./../y-admin/csv_export.php">Rollen/User-Export</a></li>
          <li id="usertabelle" class="nav-link"><a class="nav-link" href="./../y-admin/berechtigungen.php">Rollenzuweisungen</a></li>
          <!--li id="rollenrechner" class="nav-link"><a class="nav-link" href="./../y-admin/rollenrechner.php">Rollen-Rechner</a></li-->
          <!--li id="register" class="nav-link"><a class="nav-link" href="./../y-admin/register.php">Nutzer manuell anlegen</a></li--> 


          <li id="bereiche" class="nav-link dropdown">
            <a class="nav-link dropdown-toggle" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">Bereiche</a>
            <div class="dropdown-menu">
              <a class="nav-link" href="./../y-install/index.php">Installation</a>
              <a class="nav-link " href="./../y-config/index.php">Konfiguration</a>
              <a class="nav-link aktiverbereich" >Verwaltung</a>
              <a class="nav-link " target="_blank" href="./../../ydemo/demo.php">Demo-Tab</a>
              <a class="nav-link " href="./../../yfront/login.php">Log-In</a>
              <a class="nav-link " href="./../../yfront/logout.php">Log-Out</a>
            </div>
          </li>


        </ul>

      </div>

    </nav>