<?php
namespace ypum;


class databasemanager{
    
    var $pdo;
    var $dbOK;

    function __construct($secDir) {

        $configFile = $secDir."/dbconfig.json";
        $zugangsdaten = file_get_contents($configFile);
        $zugangsdaten = json_decode($zugangsdaten,true);
        $dbhost = '';
        $dbname = '';
        $dbuser = '';
        $dbpass = '';
        if(isset($zugangsdaten['db_hostname'])) $dbhost = $zugangsdaten['db_hostname'];
        if(isset($zugangsdaten['db_dbname'])) $dbname = $zugangsdaten['db_dbname'];
        if(isset($zugangsdaten['db_user'])) $dbuser = $zugangsdaten['db_user'];
        if(isset($zugangsdaten['db_password'])) $dbpass = $zugangsdaten['db_password'];

        $dsn = 'mysql:dbname='.$dbname.';host='.$dbhost;

        $options = [ \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION];  
        try {   
            $this->pdo = new \PDO($dsn, $dbuser, $dbpass,$options);
            $this->dbOK=true;
        } catch (\PDOException $e) {
            echo "<div class='alert alert-danger' role='alert'>";
            echo "<b>Verbindung mit der Datenbank fehlgeschlagen:</b><br>" . $e->getMessage();
            echo "<br>Bitte gehen Sie auf <b>..../ypum/yback/y-install/datenbank.php</b>, um die Datenbankverbindung einzurichten. Sollten Sie die Ersteinrichtung noch nicht durchlaufen haben, beginnen Sie diese bitte unten links bei 'Installieren'.";
           
            echo "</div>";
            //echo "<h2>Bei der Erstinstallation <a href='./yback/y-install/datenbank.php'>hier</a> klicken, um zum Datenbank-Setup zu gelangen.</h2>";
            //die();
            $this->dbOK=false;
        }   
    }
    public function  query($query, $datenarray=array(), $fetchData=true){
        if($this->dbOK){
            $pdo = $this->pdo;
            $stmt = $pdo->prepare($query);
            $datenarray = $this->sanitize($datenarray);
            try{
                $res = $stmt->execute($datenarray);
            }catch(\PDOException $e){
                //echo $e;
                return false;
            }

            if($fetchData){
                try{
                    $res = $stmt->fetchAll(\PDO::FETCH_ASSOC);
                }catch(\PDOException $e){
                    //echo $e;
                    return false;
                }
            }
            return $res;
        }
    }

    public function  queryOne($query, $datenarray=array()){
        if($this->dbOK){
            $pdo = $this->pdo;
            $stmt = $pdo->prepare($query);
            $datenarray = $this->sanitize($datenarray);
            $res = $stmt->execute($datenarray);

            try{
                $res = $stmt->fetch(\PDO::FETCH_ASSOC);
                return $res;
            }catch(\PDOException $e){
                echo $e;
                return null;
            }
        }
        
    }

    public function exists($query, $datenarray=array()){
        if($this->dbOK){
            $pdo = $this->pdo;
            $stmt = $pdo->prepare($query);
            $datenarray = $this->sanitize($datenarray);
            $res = $stmt->execute($datenarray);
            return ($stmt->rowcount() > 0) ? (true) : (false);
        }
    }

    public function updateUserData(){

        // In jedem Fall muss der Detailview auch angepasst werden.
        $res = $this->query("SELECT fieldname FROM y_user_fields",array(), true);
        $sqlSELECT = "SELECT distinct t_uid.userID, y_user.mail ";
        $sqlFROM = "FROM y_v_userfields AS t_uid JOIN y_user ON t_uid.userID = y_user.id ";
        $sqlWHERE = "WHERE y_user.locked = 0;";

        foreach($res As $aFieldname){
            $fieldname = $aFieldname['fieldname'];
            $sqlSELECT .= ", t_$fieldname.fieldvalue AS $fieldname ";
            $sqlFROM .= " LEFT JOIN ";
            $sqlFROM .= " (SELECT userID, fieldvalue  FROM y_v_userfields WHERE fieldname = '$fieldname') t_$fieldname ON t_uid.userID = t_$fieldname.userID ";
        }

        // y_v_userdate
        $res = $this->query("DROP VIEW if EXISTS y_v_userdata", array(), false);
        $res = $this->query("CREATE VIEW y_v_userdata as $sqlSELECT $sqlFROM $sqlWHERE", array(), false);

      
    }

    private function sanitize($datenarray){

        $output = array();

        foreach($datenarray as $item){

            $output[] = htmlentities($item);
        }

        return $output;

    }
}
?>