<?php
    session_start();
    // Es dürfen keine Ausgaben erzeugt werden, damit der Ajax-Return funktioniert
    require_once(__DIR__."/../include/classes/configmanager.php");
    require_once(__DIR__."/../include/classes/databasemanager.php");

    $data = json_decode(file_get_contents('php://input'), true);
    $conf = new ypum\configmanager();
    
    if(!empty($data)){
        if($conf->isRightKey( $_SESSION['ypum_sourcekeyuser'], $data['sourceHash'])){

            $secDir = $conf->getSecDir();
            $dbm = new ypum\databasemanager($secDir);

            $action = $data['action'];

            if(strcmp($action,"EDIT")==0){
                $wert = $data['neuerwert'];
                // contenteditable führt manchmal zu unerwünschen Einträgen, die hier gelöscht werden.
                $wert = str_replace("<br>","",$wert);
                $wert = str_replace("<div>","",$wert);
                $wert = str_replace("</div>","",$wert);
            
                if(strcmp($data['typ'],"basis")==0){
                    $feld = $data['fid'];
                    $args = array();      
                    if(strcmp($feld,"mail")==0) $wert = strtolower($wert);
                    $args[] = $wert;
                    $args[] = $data['uid'];
                    $res = $dbm->query("update y_user set $feld = ? where id=?", $args,false);

                    $args = array();
                    $args[] = $data['uid'];
                    $erg = $dbm->queryOne("select $feld from y_user where id=?", $args,true);   
                    $erg=$erg[$feld];
                }
                elseif(strcmp($data['typ'],"detail")==0){
                    $feld = $data['fid'];
                    $args = array();
                    $args[] = $wert;
                    $args[] = $data['uid'];
                    $args[] = $data['fid'];
                    $res =  $dbm->query("replace into y_user_details (fieldvalue, userID, fieldID) values (?,?,?)",$args,false);

                    $args = array();
                    $args[] = $data['uid'];
                    $args[] = $data['fid'];
                    $erg = $dbm->queryOne("select fieldvalue from y_user_details where userID=? and fieldID=?", $args,true);
                    $erg=$erg['fieldvalue'];
                }

                $return = array("res"=>$res, "erg"=>$erg);
                echo json_encode($return);
            }elseif(strcmp($action,"DELETE")==0){

                $args = array();
                $args[] = $data['uid'];
                $res = $dbm->queryOne("delete from y_user where id=?", $args,false);
                $return = array("res"=>$res);
                
            }
        }
    }
?>