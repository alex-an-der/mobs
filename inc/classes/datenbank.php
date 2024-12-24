<?php
class Datenbank {
    
    private $pdo;

    public function __construct($sync = false){

        $dbname = "db_441127_12"; 
        $dbhost = "x96.lima-db.de";
        $dbuser = "USER441127";
        $dbpass = "BallBierBertha42";
    
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
        $stmt = $this->pdo->prepare($query);
        $success = $stmt->execute($arguments);
    
        // Prüfen, ob die Abfrage ein SELECT ist
        if (stripos(trim($query), 'SELECT') === 0) {
            return $stmt->fetchAll(PDO::FETCH_ASSOC);  // SELECT gibt Datensätze zurück
        }
        
        // UPDATE, INSERT, DELETE – Anzahl der betroffenen Zeilen zurückgeben
        return $success ? $stmt->rowCount() : false;
    }

    public function log($eintrag) {
        $query = "INSERT INTO log (eintrag) VALUES (:eintrag)";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':eintrag', $eintrag, PDO::PARAM_STR);
        $stmt->execute();
    }
        

}
?>