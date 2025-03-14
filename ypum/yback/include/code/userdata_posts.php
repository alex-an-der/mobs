<?php

if(isset($_POST['MACH_SPEICHERN'])){

    
    $id = $_POST['id'];

    $uf_name = $_POST['uf_name'];
    $int_name = $_POST['int_name'];
    
    $args[] = $uf_name;
    $args[] = $int_name;
  
    if(empty($id)){

        $dbm->query("REPLACE INTO y_user_fields (uf_name, fieldname) values(?,?)",$args, false);

    }else{

        $args[] = intval($id);
        $res=$dbm->query("UPDATE y_user_fields set uf_name = ?, fieldname=? where ID = ?",$args, false);
    }
}

if(isset($_POST['MACH_LOESCHEN'])){

    $id = $_POST['id'];

    if(!empty($id)){
        $args[] = $id;
        $res= $dbm->query("DELETE FROM y_user_fields WHERE id = ?",$args,false);
    }

}

// In jedem Fall muss der Detailview auch angepasst werden.
$res = $dbm->query("SELECT fieldname FROM y_user_fields",array(), true);
$sqlSELECT = "SELECT distinct t_uid.userID, y_user.mail ";
$sqlFROM = "FROM y_v_userfields AS t_uid JOIN y_user ON t_uid.userID = y_user.id ";
$sqlWHERE = "WHERE y_user.locked = 0;";

foreach($res As $aFieldname){
    $fieldname = $aFieldname['fieldname'];
    $sqlSELECT .= ", t_$fieldname.fieldvalue AS $fieldname ";
    $sqlFROM .= " JOIN ";
    $sqlFROM .= " (SELECT userID, fieldvalue  FROM y_v_userfields WHERE fieldname = '$fieldname') t_$fieldname ON t_uid.userID = t_$fieldname.userID ";
}

$dbm->updateUserData();

?>