<?php
namespace ypum;

class usermanager{

    protected $dbm;
    protected $conf;

    function __construct($dbm, $conf) {
        
        $this->dbm = $dbm;
        $this->conf = $conf;
    }
    
    function readUserData(){

        
        if(isset($_SESSION['uid'])){
            $args = array();
            $args[] = $_SESSION['uid'];
    
            // 1. //
            $thisUser = array();
            $stammdaten =  $this->dbm->queryOne("select mail from y_user where id=?",$args, true);
            $thisUser['mail'] = $stammdaten['mail'];

            $details = $this->dbm->query("select fieldname, fieldvalue from y_v_userfields WHERE userID = ?",$args, true);

            foreach($details as $datensatz){
            
                $thisUser[$datensatz['fieldname']] = $datensatz['fieldvalue'];
            }
            return $thisUser;

        }else{
            return array();
        }
    }

    function writeUserData($dataset, $bearbeitenZulassen = false, $mailsenden=true){

        if(isset($dataset['mail'])){
            if(empty($dataset['mail'])){
            throw new \Exception("Das Formularfeld 'mail' darf nicht leer sein.",101);
            }
        }else{ 
            throw new \Exception("Das Formularfeld 'mail' muss uebermittelt werden.",102);
        }

        // 1. Gibt es den user schon? 
        // 1.1. -> Setze entsprechend created
        // 1.2. -> Insert Daten mit dieser User-ID (UNIQUE)
        // 2. Mail bestätigen und damit ein PW setzen lassen
        

        $args = array();
        $args[] = $dataset['mail'];

        // 1. //
        $neuerUser = false;
        $userdata = $this->dbm->query("select id, mail from y_user where mail=?",$args, true);
        
        if(empty($userdata)){ // Neuer Nutzer

            $mail = $dataset['mail'];
 
            $args = array();
            $args[] = strtolower($mail);
            $this->dbm->query("insert INTO y_user (mail, created) values(?,NOW())", $args, false);
            $args = array();
            $args[] = strtolower($mail);
            $userdata = $this->dbm->query("select id from y_user where mail=?",$args, true);
            $uid = $userdata[0]['id'];
            $neuerUser = true;
            
        }else{
            $uid = $userdata[0]['id'];
            $mail = $userdata[0]['mail'];
            $neuerUser = false;

            if(!$bearbeitenZulassen)
                throw new \Exception("Diese Mailadresse ist bereits registriert.",130);
        }

        // Schreibe Details
        foreach ($dataset as $key => $value){
            $args = array();
            $args[] = $key;
            $field_id = $this->dbm->query("select ID from y_user_fields where fieldname = ?", $args, true);

            if(!empty($field_id)){
                $field_id = $field_id[0]['ID'];
                $args = array();
                $args[] = $uid;
                $args[] = $field_id;
                $args[] = $value;
                $this->dbm->query("replace into y_user_details (userID, fieldID, fieldvalue) values(?,?,?)",$args,false);
            }
        }

        // Sende Validierungsmail (Mail-Adresse korrekt & PW setzen)
        if($mailsenden){
            $this->updateUser($mail, $neuerUser);
        }
    }

    function updateUser($mail, $neuerUser){

        $args = array();
        $args[] = strtolower($mail);
        $rows = $this->dbm->query("select id from y_user where mail=?", $args, true);
        if(isset($rows[0])){
            $row = $rows[0];
            $uid = $row['id'];
            $token =  md5(random_bytes(80));
            $args = array();
            $args[] = $token;
            $args[] = $uid;
            $this->dbm->query("update y_user set token=?, tokencreated=NOW() where id=?", $args, false);

            $res = $this->sendMail($uid, $mail, $token, $neuerUser);

        }        
    }

    function sendMail( $uid, $mailadresse, $token, $neuerUser){

        $pwss  = $this->conf->getYpumRoot()."/yfront/pwss.php?id=$uid&token=$token";
        
        if($neuerUser) $vorlagedatei = "mail_neu";
        else $vorlagedatei = "mail_vergessen";
      
        $aVorlage = $this->conf->load($vorlagedatei);
      
        $empfaenger = $mailadresse;
        $betreff = str_replace('##LINK##',$pwss,$aVorlage['betreff']);
        $mailtext = str_replace('##LINK##',$pwss,$aVorlage['mailtext']);
        $absender = $aVorlage['absendename']."<".$aVorlage['absendeadresse'].">";

        $bHtmlmail = true;
        if(strcmp($aVorlage['txformat'],"text")==0) $bHtmlmail = false;

        $headers   = array();
        $headers[] = "MIME-Version: 1.0";
        if($bHtmlmail){
            $headers[] = "Content-type: text/html; charset=utf-8";
        }else{
            $headers[] = "Content-type: text/plain; charset=utf-8";
        }
        $headers[] = "From: {$absender}";

        $mailres = mail($empfaenger, $betreff, $mailtext,implode("\r\n",$headers));

        return $mailres;
        
      }

    function getUserID($user_mail){
      
        $dbh = $this->dbm;
        $args[] = strtolower($user_mail);
        $row = $dbh->query("select ID from y_user WHERE mail = ?",$args,true);
        if (isset($row[0]['ID'])) return $row[0]['ID'];
        else return false;

    }

    function getUserdata($user_id){

    }

    
    

}
?>