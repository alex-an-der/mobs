

# D.U.M.M. - Datenbasierter universeller Mitgliedermanager
## Installation
### Datenbank
#### Zugangsdaten
Die Zugangsdaten müssen in der config.php bereitgestellt werden. Details dazu siehe 'Konfigurationseinstellungen'.
#### Log-Tabelle
In der Datenbank muss es eine Log-Tabelle geben. Diese kann so angelegt werden:
```
CREATE TABLE `log` ( 
`ID` BIGINT AUTO_INCREMENT NOT NULL,
`zeit` DATETIME NULL DEFAULT CURRENT_TIMESTAMP ,
`eintrag` VARCHAR(1000) NULL,
    PRIMARY KEY (`ID`)
)
ENGINE = InnoDB;
INSERT INTO log (eintrag) VALUES ('Herzlich Willkommen!');
```

### PHP-Module  
Für den Export (außer nach CSV) benötigt PHP dieser Module:
```
composer require tecnickcom/tcpdf
composer require phpoffice/phpspreadsheet
```
## Die Konfigurationsdatei config.php
Über die Konfigurationsdatei werden die Darstellungen festgelegt. Es ist weniger ein Setzen von Parametern als ein 'Programmieren in SQL'. Grundsätzlich hat es sich als effizient erwiesen, die SQL-Queries zunächst in einem SQL-Client zu erstellen und die fertigen Queries dann in die Konfigurationsdatei zu übernehmen.

Die Datei wird von den php-Seiten über ein require_once eingebunden. Andersherum lassen sich in der config.php natürlich andere Seiten z.B. eines Berechtigungsmanagements über diesen Mechanismus einbinden.

### Datenbankzugang 
```
define("DB_NAME", "entenhausen");
define("DB_HOST", "user12.lima-db.de");
define("DB_USER", "db_user_734");
define("DB_PASS", "PW123456");
```
### Seitentitel
Der _TITEL_ wird in den angezeigten php-Seiten als Titel übernommen.
```
define("TITEL", "Mietgliederverwaltung Entenhausener Gänseclub e.V.");
```
### Angezeigte Tabellen
Dies ist der Hauptteil der Konfiguration. Über das Array `$anzuzeigendeDaten = array();` werden die angezeigten Daten gesteuert. Jeder Eintrag in diesem Array ist eine Ansicht, die in der Auswahlliste erscheint und ausgewählt werden kann. Ein kompletter Satz mit allen optionalen Parameter könnte so aussehen:

```php
$anzuzeigendeDaten[] = array(
    "tabellenname" => "mitglieder",
    "auswahltext" => "Mitglieder Stammdaten",
    "query" => "SELECT Name, Vorname, Geschlecht, Unternehmen from mitglieder order by id desc;",
    "referenzqueries" => array(
        "Geschlecht" => "SELECT id, geschlecht as anzeige from geschlechter order by geschlecht desc;",
        "Unternehmen" => "SELECT id, CONCAT(Name, ', ', Stadt) as anzeige from unternehmen order by Name;"
    ),
    "spaltenbreiten" => array(
        "Vorname"       => "120px",
        "Nachname"      => "120px",
        "Straße"        => "200px",
        "PLZ"           => "120px",
        "Wohnort"       => "200px",
        "Geschlecht"    => "40px"
    ),

);
```


        <?php if (!isset($anzuzeigendeDaten[$selectedTableID]['import']) || $anzuzeigendeDaten[$selectedTableID]['import'] !== false): ?>
            <a href="importeur.php?tab=<?= $selectedTableID ?>" class="btn btn-info mb-2">Daten importieren</a>
        <?php endif; ?>


        

#### tabellenname
#### auswahltext
#### writeaccess
#### import
NEIN: Import, einfügen
JA: Löschen,
ToDo: Sparte
#### query
#### referenzqueries
#### spaltenbreiten
#### Sonderfall: READ.ONLY
### Statistische Auswertungen
Über das Array `$statistik = array();` können statistische Auswertungen festgelegt werden. Diese sind dann unter `Exportieren -> Statistiken` erreichbar.

```php
$statistik[] = array(
    "titel" => "Mitglieder in Sparten",
    "query" => "SELECT s.Sparte, count(mis.Mitglied) as Mitglieder
                from b_mitglieder_in_sparten as mis
                join b_sparte as s on s.id = mis.Sparte
                join v_verbands_berechtigte_sparte as r on r.Sparte = s.id 
                where r.Verbandsberechtigter = $uid
                group by s.Sparte
                ",
    "typ"   => "torte"
);
```

# LOP ab hier



# tabellenname => Nur hierein kann in dieser Ansicht ein insert oder update ausgeführt werden.
#              => Basistabelle für Referenzierung in anderen Tabellen
# query        => Es muss eine Spalte mit dem Namen "id" angefordert werden, die als eindeutiger Schlüssel verwendet wird.
#              => Die Spalte "id" wird nicht angezeigt. 
#              => Soll die ID des Datensatzes angezeigt werden, muss diese ein zweites Mal angefordert werden (z.B. SELECT id, id as LfdNr. from ...)
#              => Es können nur Spalten bearbeitet werden, die nicht mit einem Alias angefordert werden. Beispiel: SELECT Nachname, vName as Vorname -> nur Nachname kann bearbeitet werden.
#
# KOMPLETTBEISPIEL:
# ----------------
#
/*

# auch noch LOP


## Berechtigungsstruktur
Zwei Berechtigungen:
- Daten über Landes- und Regionalverband
- Ansichten über YPUM



Es gilt der Grundsatz, dass immer eine Ebene nach unten berechtigt wird.

### Definition
V := Regionalverband (Berechtigung in b_regionalverband_rechte), im folgenden auch 'Verband' genannt
B := Betriebssportgemeinschaft (b_bsg_rechte)
S := Sparten
M := Mitglieder


Dann gilt:
(1) V => B
(2)      B => M
(3) V => S
(4) Als Sonderfall: M => M
offen (5) B => M->S

(1) Nur der Verband kann seine BSG anlegen und (z.B. Ansprechpartner) ändern.
(2) Nur (außer (5)) BSG kann seine Mitglieder anlegen und ändern.
(3) Nur der Verband kann seine Sparten anlegen oder ändern.
(4) Als Sonderfall kann ein Mitglied seine eigenen Daten ändern.
(5) Nur die BSG kann ihre Mitglieder Sparten zuweisen
... Sollten sich Mitglieder selbst Sparten zuweisen/anmelden können? Bislang nur über BSG (die dann auch die Rechnung zahlt...)

# LoP
## Berechtigungen weitermachen
## fix: Spaltenbreite ist nicht ausreichend implementiert.
## Wenn M vorhanden:
BSG-Vewrbandsansicht, Ansprechpartner verbinden

## ####################################
Suche nicht nur in den Datenfeldern des Queries, sondern erweitere die Felder auf * (from und joins bleiben natürlich)
## ####################################

