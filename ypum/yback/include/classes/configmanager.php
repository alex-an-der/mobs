<?php 
namespace ypum;
class configmanager{

    private $secDir;

    function __construct(){
        $this->secDir = realpath(__DIR__."/../../../yconf");

    }

//  GET    //////////////////////////////////////////////////////////

    function getSecDir(){
        return $this->secDir;
    }
    
    function getPathToLogin($webdir = true){
        $data = $this->load("dbconfig");
        $loginRelPath = "/yfront/login.php";
        if($webdir) return $data['ypumWEBpath'].$loginRelPath;
        else        return $data['ypumSRVpath'].$loginRelPath;
    }

    function getPathToKeineRechte($webdir = true){
        $data = $this->load("dbconfig");
        $loginRelPath = "/yfront/keine_berechtigung.php";
        if($webdir) return $data['ypumWEBpath'].$loginRelPath;
        else        return $data['ypumSRVpath'].$loginRelPath;
    }

    function getYpumRoot($webdir = true){
        $data = $this->load("dbconfig");
        if($webdir) return $data['ypumWEBpath'];
        else        return $data['ypumSRVpath'];
    }

    function getRandKey(){
        $bytes = random_bytes(37);
        return bin2hex($bytes);
    }

    function getFooter(){
        /**
        **  Setzt das Nav-Element (ID) mit dem Namen der angezeignten Datei aktiv
        **  Die ID des Nav-Elements muss dem Dateinamen entsprechen!
        **
        **  Beispiel:
        **      <li id="datenbank" class="nav-link"><a class="nav-link" href="#">Datenbank</a></li>
        **  und
        **      //localhost/00_DEV/YPUM/core/datenbank.php
        **
        **/
        echo("<script>");
        echo("pathname = window.location.href;");
        echo("fullfile = pathname.substr(pathname.lastIndexOf('/')+1);");
        echo("fileprex = fullfile.substr(0,fullfile.length-4);");
        echo("if(fileprex.length==0) {fileprex='index';}");
        echo("if(fileprex.substring(0, 5)=='form_') {fileprex='formulare';}");
        echo("if(fileprex.substring(0, 5)=='mail_') {fileprex='mails';}");
        echo("$('#'+fileprex).addClass('active');");
        echo("</script>");
      }


//  IS     //////////////////////////////////////////////////////////

    function isInstallmodus(){ 
        $data = $this->load("lock");
        return boolval($data['installmodus']);
    }

    function isRightKey($sourceKey, $sourceHash){
        if(password_verify ($sourceKey , $sourceHash ) ) return true;
        return false;
    }

    function isValidHttpFile($file){ 
        $headers="";
        $ok=false;
        @$headers = get_headers($file);
        if(isset($headers[0])) $ok = !(preg_match('/^HTTP\\/\\d+\\.\\d+\\s+4\\d\\d\\s+.*$/',$headers[0]));
        return $ok;
       
    }


//  SET    //////////////////////////////////////////////////////////


//  TOOLS  //////////////////////////////////////////////////////////

    function load($dateiprefix){
        $datei = $this->secDir."/".$dateiprefix.".json";
        @$data= file_get_contents($datei);
        if(empty($data)){
            return false;
        }else{
            return json_decode($data,true);
        }
    }
    function save($dateiprefix,$daten,$jsonformat=false){
        $datei = $this->secDir."/".$dateiprefix.".json";
        if(!$jsonformat) $daten = json_encode($daten);
        file_put_contents($datei,$daten);
    }
    function redirect($url, $arg=""){
        if(strlen($arg)>1) $url .= "?lasturi=$arg";
        echo '<script type="text/javascript">';
        echo 'window.location.href="'.$url.'";';
        echo '</script>';
        exit();
    }
    

}
?>