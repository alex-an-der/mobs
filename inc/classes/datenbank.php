<?php
class Datenbank {
    
    private $pdo;

    public function __construct($sync = false){
        
        
        $dbname = DB_NAME;
        $dbhost = DB_HOST;
        $dbuser = DB_USER;
        $dbpass = DB_PASS;
    
        try {
            $dsn = "mysql:host=$dbhost;dbname=$dbname;charset=utf8";
            $this->pdo = new PDO($dsn, $dbuser, $dbpass);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            echo 'Connection to 96-Database failed: ' . $e->getMessage();
        }
    }


    // Führt pdo-Query aus und liefert das Ergebnis als Array zurück
    public function query($query, $arguments = array()) {
        require_once(__DIR__ . "/../../mods/before_sending_query.php");

        $stmt = $this->pdo->prepare($query);
        try{
            $success = $stmt->execute($arguments);
        } catch (PDOException $e) {
            $this->log("Query error in ".__FILE__.": " . $e->getMessage());
            return false;
        }
        // Prüfen, ob die Abfrage ein SELECT oder SHOW ist

        if (stripos(trim($query), 'SELECT') === 0 || stripos(trim($query), 'SHOW') === 0) {
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            // DBI
            if ($result === false) {
                // Ein Fehler ist aufgetreten
                return ['error' => 'Ein Fehler ist aufgetreten'];
            } elseif (empty($result)) {
                // Keine Daten gefunden
                return ['message' => 'Keine Daten für Ihre Berechtigungseinstellungen vorhanden.'];
                
            } else {
                // Daten erfolgreich abgerufen
                return $result;
            }
        }
        
        // UPDATE, INSERT, DELETE – Anzahl der betroffenen Zeilen zurückgeben
        foreach ($arguments as $argument) {
            $query = preg_replace('/\?/', $argument, $query, 1);
        }
        $this->log( $query);
        return $success ? $stmt->rowCount() : false;
    }

    public function log($eintrag) {
        require_once(__DIR__ . "/../../mods/before_logging.php");
        try {
            $query = "INSERT INTO log (eintrag) VALUES (:eintrag)";
            $stmt = $this->pdo->prepare($query);
            $stmt->bindParam(':eintrag', $eintrag, PDO::PARAM_STR);
            $stmt->execute();
        } catch (PDOException $e) {
            error_log("Log error: " . $e->getMessage());
        }
    }

    public function errorInfo() {
        return $this->pdo->errorInfo();
    }
        

}
?>