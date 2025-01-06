<?php
# Hier können Sie den Query manipulieren, bevor er an die Datenbank gesendet wird.

# $query = Der Query (select, insert, update, delete), der per PDO an die Datenbank gesendet wird.
# $arguments = Array von Argumenten, die in den Query eingefügt werden (prepared Statements)
# $pdo ist ein PDO-Objekt für die aktuelle Datenbank
# $this ist die Datenbankklasse (inc/classes/datenbank.php)

# Beispiel
# $query = select user where id = ?
# $arguments = (30,219);
# ergibt "select user where ager = 30 and id = 219"
?>

<?php
// Wenn die Tabelle bsv_1_verband als from im Query ist, nutze den auth_key "verband".
if (isFromTab ($query, "bsv_1_verband")) $scope = "verband";


function isFromTab($query, $tab){

    //RexEx: "from $tab" oder auch "from    $tab" - sonst aber nichts
    if (preg_match('/\bfrom\s+' . preg_quote($tab, '/') . '\b/i', $query)) {
        return true;
    }
    return false;
}


# Ergänzen des Querys um den auth_key
# !!! Es wird mit LIKE gearbeitet. Das bedeutet für den authKey:
# "BSV_HANNOVER" zeigt nur den Datensatz mit dem authKey "BSV_HANNOVER"
# "BSV%" zeigt alle Datensätze, die mit "BSV" beginnen
# "%HANN%" zeigt alle Datensätze, die "HANN" enthalten
# "%" zeigt alle Datensätze 

global $ypum;
$userData = $ypum->getUserData();
$authKey = $userData[$scope];

$placeholders = [];
$count = 0;
$query = preg_replace_callback('/\?/', function($matches) use (&$placeholders, &$count) {
    $placeholder = ':param' . $count++;
    $placeholders[] = $placeholder;
    return $placeholder;
}, $query);

$newArguments = [];
foreach ($arguments as $key => $value) {
    $newArguments[$placeholders[$key]] = $value;
}
$arguments = $newArguments;
if (stripos($query, "where") !== false) {
    $query = preg_replace('/where/i', 'WHERE auth_key LIKE :auth_key AND', $query, 1);
    $arguments[':auth_key'] = $authKey;
} else {
    if (stripos($query, "order by") !== false || 
        stripos($query, "group by") !== false || 
        stripos($query, "having") !== false || 
        stripos($query, "limit") !== false || 
        stripos($query, "offset") !== false || 
        stripos($query, "join") !== false) {
        $query = preg_replace('/(order by|group by|having|limit|offset|join)/i', 'WHERE auth_key LIKE :auth_key $1', $query, 1);
        $arguments[':auth_key'] = $authKey;

    } else {
        $query .= " WHERE auth_key LIKE :auth_key";
        $arguments[':auth_key'] = $authKey;
    }
}

$this->log("Modifizierter Query: $query");
?>