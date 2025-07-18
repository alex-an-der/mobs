<?php
class Datenbank {
    
    private $pdo;

    public function __construct($sync = false){
        

        $dbname = DB_NAME;
        $dbhost = DB_HOST;
        $dbuser = DB_USER;
        $dbpass = DB_PASS;
    
        try {
            $dsn = "mysql:host=$dbhost;dbname=$dbname;charset=utf8mb4";
            $this->pdo = new PDO($dsn, $dbuser, $dbpass);
            $this->pdo->exec("SET NAMES 'utf8mb4' COLLATE 'utf8mb4_german2_ci'");
     
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            echo 'Connection to Database failed: ' . $e->getMessage();
        }
    }


    // Führt pdo-Query aus und liefert das Ergebnis als Array zurück
    public function query($query, $arguments = array(), $rollback=true) { 
        require_once(__DIR__ . "/../../user_includes/before_sending_query.php");

        // Handle info: columns by correctly escaping them in MySQL
        // This allows using "info:" prefix in column aliases
        $query = preg_replace('/\bAS\s+`?info:([^`\s,)]+)`?/i', 'AS `info:$1`', $query);
        $query = preg_replace('/\bAS\s+info:([^\s,)]+)/i', 'AS `info:$1`', $query);

        $stmt = $this->pdo->prepare($query);

        // Handle NULL values
        foreach ($arguments as $key => $value) {
            if ($value == "NULL") {
                $stmt->bindValue($key+1, NULL, PDO::PARAM_NULL);
            }else{
                $stmt->bindValue($key+1, $value);
            }
        }

        try{
            $success = $stmt->execute(); 
        } catch (PDOException $e) {
            $errmsg = $e->getMessage();
            $errorInfo = $e->errorInfo;
            $errorCode = $errorInfo[1] ?? $e->getCode();
    
            $errorID = $this->log($query." with the arguments: ".json_encode($arguments)." led to a query error in ".__FILE__.": " . $errmsg);    
            // errorId ist der entsprechende Eintrag im Error-Log
            // errorCode ist der 'offizielle' SQL-Fehlercode (z.B. 1365 (='Field 'xyz' doesn't have a default value')

            return ['error' => $errorID, 'message' => $errmsg, 'errorcode' => $errorCode];
        }
        // Prüfen, ob die Abfrage ein SELECT oder SHOW ist - dann mit return raus
        if (stripos(trim($query), 'SELECT') === 0 || stripos(trim($query), 'SHOW') === 0) {
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
           
            if ($result === false) {
                // Ein Fehler ist aufgetreten
                return ['error' => 'Ein unbekannter Fehler ist aufgetreten'];
            } elseif (empty($result)) {
                // Keine Daten gefunden
                return ['message' => 'Keine Daten für Ihre Berechtigungseinstellungen vorhanden.'];
                
            } else {
                // Daten erfolgreich abgerufen
                return ['data' => $result];
            }
        }
        
        // UPDATE, INSERT, DELETE – Anzahl der betroffenen Zeilen zurückgeben
        foreach ($arguments as $argument) {
            // Prepared Statements einsetzen 
            $query = preg_replace('/\?/', $argument, $query, 1);
        }
        // $this->log( $query); // Mit den Fehlermeldungen, damit man das besser nachvollziehen kann
        if($rollback) $this->log_for_rollback( $query); // Nur für Rollbacks (BU einspielen und dann auf den richtigen Stand bringen
        return $success ? $stmt->rowCount() : false;
    }

    public function log($eintrag) {
        require_once(__DIR__ . "/../../user_includes/before_logging.php");
        try {
            $query = "INSERT INTO sys_log (eintrag) VALUES (:eintrag)";
            $stmt = $this->pdo->prepare($query);
            $eintrag = mb_substr($eintrag, 0, 999);
            $stmt->bindParam(':eintrag', $eintrag, PDO::PARAM_STR);
            $stmt->execute();
            return $this->pdo->lastInsertId();
        } catch (PDOException $e) {
            error_log("Log error: " . $e->getMessage());
        }
    }

    public function log_for_rollback($originalquery) {
        $autor = "";
        require_once(__DIR__ . "/../../user_includes/before_log_for_rollback.php");
        try {
            $args = array($autor, $originalquery.";");
            $query = "INSERT INTO sys_rollback (autor, eintrag) VALUES (?, ?)";
            $stmt = $this->pdo->prepare($query);
            $stmt->execute($args);
        } catch (PDOException $e) {
            error_log("Log error: " . $e->getMessage());
        }
    }

    public function errorInfo() {
        return $this->pdo->errorInfo();
    }

    public function getPDO() {
        return $this->pdo;
    }
        

}
?>