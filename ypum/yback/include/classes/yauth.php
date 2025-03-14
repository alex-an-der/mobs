<?php 
namespace ypum;

class yauth{

    protected $allerollen;
    protected $dbm;


    function __construct($dbm, $conf, $configdaten, $im, $noypum=false) {

        $this->dbm = $dbm;
        $this->allerollen = $configdaten['allerollen'];

        if(!$noypum){
            

            if($im || (isset($_SESSION['uroles']) && isset($_SESSION['uid']))){
            
                $thisFile = $_SERVER['SCRIPT_FILENAME'];
                
                $q  =   "SELECT roles, CHAR_LENGTH(dir) AS len FROM y_sites ";
                $q .=   "WHERE INSTR('$thisFile', dir) ";
                $q .=   "ORDER BY len DESC LIMIT 1;";
                
                $res = $dbm->query($q, array(), true);
                
                if(!empty($res)){
                    $seitenRolle = $res[0]['roles'];
                    
                    if (!$im && !$this->isBerechtigt($seitenRolle)){
                        $conf->redirect($conf->getPathToKeineRechte());
                        die();
                    }
                }
            }else{    
                $conf->redirect($conf->getPathToKeineRechte());
                die();
            }
                $_SESSION['lastSite'] = $_SERVER['SCRIPT_FILENAME'];
        

        }

    }

    function getDB(){
        return $this->dbm;
    }

    function isBerechtigt($seitenRolle){


        $ALLE = $this->allerollen;
        $besucherRolle = $_SESSION['uroles'];

        if($ALLE) return (intval($seitenRolle) - (intval($besucherRolle) & intval($seitenRolle))) ==0;
        else return (intval($besucherRolle) & intval($seitenRolle)) > 0;

        
    }

    // userID auf bestimmtes Rollenset prüfen
    function isUserBerechtigt($userID, $geforderteRolle){

        $dbm = $this->dbm;
        $ALLE = $this->allerollen;

        $args = array();
        $args[] = $userID;
        $args[] = 0;
        $res = $dbm->queryOne("select roles from y_user where id=? and locked=?",$args,true);
        if(!$res) return 0; // Fehlerhafter user / USer gibt es nicht in y_user
        $besucherRolle = $res['roles'];

        if($ALLE) return (intval($geforderteRolle) - (intval($besucherRolle) & intval($geforderteRolle))) ==0;
        else return (intval($besucherRolle) & intval($geforderteRolle)) > 0;
    }

    // userID auf bestimmtes Rollenset prüfen
    function isRolleBerechtigt($anfragendeRolle, $geforderteRolle){

        $ALLE = $this->allerollen;

        if($ALLE) return (intval($geforderteRolle) - (intval($anfragendeRolle) & intval($geforderteRolle))) ==0;
        else return (intval($anfragendeRolle) & intval($geforderteRolle)) > 0;
    }

    // Daten des aktuellen Nutzers liefern
    function getUserData(){
        
        $uid = $_SESSION['uid'];
        $userdata = array();
        $userdata['id'] = $uid;

        $args = array();
        $args[] = $uid;

        $stammdaten = $this->dbm->queryOne("SELECT mail, roles, lastlogin, created, validated, tokencreated from y_user where id =?",$args,true);
        foreach($stammdaten as $key=>$value){

            $userdata[$key]=$value;
        }
        
        $zusatzdaten = $this->dbm->query("SELECT uf_name, fieldname, fieldvalue FROM y_v_userfields WHERE userID=?",$args,true);
        foreach($zusatzdaten as $data){

            $userdata[$data['fieldname']]=$data['fieldvalue'];
        }

        return $userdata;

    }
}

?>
