# Name
Framework: FUDA - Framework for univerlsal data applications
BSV: MOBS

Was ist der Unterschied individuell und BSG? Kann man das nicht über BSG-Check machen?
Beide checken die BSG-Berechtigungen. Der Unterschied ist lediglich der Eingangsparameter:
FIND_IN_SET(BSG, berechtigte_elemente($uid, 'BSG')) > 0 => Darf $uid die BSG sehen?
FIND_IN_SET(m.id, berechtigte_elemente($uid, 'individuelle_mitglieder')) > 0 => Darf $uid diese m.id sehen?
Den Rest macht dann eben die SQL-Function

In der DB ist das anders dargestellt: (daher ggf. etwas verwirrend, aber das hat 2 verschiedene POV)
RV und BSG-Rechte: Einem Nutzer werden Rechte gewährt (Alex -> Nutzis := Alex darf alle Nutzis sehen)
indiv. Rechte:     Ein Nutzer gewährt einer BSG die Rechte (Martin -> Nutzis := Die Nutzis dürfen die Daten von Martin sehen)
Beides in Kombination: Alex darf die Daten von Martin sehen und das funktioniert immer über berechtigte_elemente, egal mit welchem Eingangsparameter.

INSERT INTO b_mitglieder (Vorname,Nachname,BSG,Stammmitglied_seit,Mail,Geschlecht,Geburtsdatum,aktiv) 
VALUES ('Tommy Manuell','Nocker',1,'1966-06-06','NeueMail@Nocker.de',3,'1966-06-06',1);

# Offene Sofort-Issues

```php
    $spaltenfilter = [];
    $filteredByGET = false;
    foreach ($_GET as $key => $value) {
        if (preg_match('/^s\d+$/', $key)) {
            $spaltenfilter[(int)substr($key, 1)] = $value;
            $filteredByGET = true;
        }
}

    ?>

    <script>
        var php_tab             = <?=json_encode($tab)?>;
        var php_selectedTableID = <?= json_encode($selectedTableID)?>;
        var php_PLEASE_CHOOSE   = <?= json_encode(PLEASE_CHOOSE)?>;
        var php_tabelle         = <?= json_encode($tabelle)?>;
        var php_DB_ERROR        = <?= json_encode(DB_ERROR)?>;
        var php_selectedTableID = <?= json_encode($selectedTableID)?>;
        var php_spaltenfilter   = <?= json_encode($spaltenfilter) ?>;
        var filteredByGET       = <?= json_encode($filteredByGET) ?>;
    </script>
```



## Nächste Schritte
Bitte prüfe, ob die ob_clean();-Anweisung im ajax richtig ist, oder ob die Ausgaben dann an den Browser gesammelt gesendet werden sollen?
ob_clean LÖSCHT den Output-Buffer. Was wird da gelöscht? Funktioniert ypum dann noch? Scanne code nach ob_clean, ob das woanders (wahrscheinlich!) auch genutzt wird - gleiche Fragestellung.

### Sicherheit!
- **ACHTUNG ## Den Meldelisteneintrag schützen (aus dem docRoot raus). Das darf nicht vor dem 15.2. ausgelöst werden und kann auch für DoS genutzt werden. ## ACHTUNG**

- Auch yconf rausholen! Zumindest die dbconnect.

### Bootstrap usw. statisch einbinden (Dateien selbst hosten)

# In der Prod-DB einfügen und neue Version v0.1.9-qa.x




# ERLEDIGT (sollte eigentlich)

```

SET @new_id = 100000;
UPDATE b_mitglieder SET id = (@new_id := @new_id + 1) ORDER BY id;
SELECT MAX(id) + 1 AS neuer_wert FROM b_mitglieder;
ALTER TABLE b_mitglieder AUTO_INCREMENT = <neuer_wert_hier_eintragen>;
(z.B. ALTER TABLE b_mitglieder AUTO_INCREMENT = 100043;)

```
- CRONJOB einrichten (prod und local und ggf. qs)
- YPUM-Anpassung nicht vergessen! (s.open Issues)



#####################################################################################

ABRUF über CRONJOB
------------------
INSERT IGNORE INTO b_meldeliste
    (MNr, BSG, Zuordnung, Zuordnung_ID, Betrag, Beitragsjahr)
SELECT
    m.id               AS MNr,
    m.BSG              AS BSG,
    1                  AS Zuordnung,
    b.Verband          AS Zuordnung_ID,
    r.Basisbeitrag     AS Betrag,
    YEAR(CURDATE())    AS Beitragsjahr
FROM b_mitglieder AS m
JOIN b_bsg AS b ON b.id = m.BSG
JOIN b_regionalverband AS r ON r.id = b.Verband
WHERE m.BSG IS NOT NULL;

INSERT IGNORE INTO b_meldeliste
    (MNr, BSG, Zuordnung, Zuordnung_ID, Betrag, Beitragsjahr)
SELECT 
    mis.Mitglied      AS MNr,
    mis.BSG           AS BSG,
    2                 AS Zuordnung,
    mis.Sparte        AS Zuordnung_ID,
    s.Spartenbeitrag  AS Betrag,
    YEAR(CURDATE())   AS Beitragsjahr
FROM b_mitglieder_in_sparten AS mis
JOIN b_sparte AS s ON s.id = mis.Sparte;








Bau das noch ein:
ALTER DATABASE <DATENBANK>
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_0900_ai_ci;

  SELECT CONCAT('ALTER TABLE `', TABLE_NAME, '` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;')
FROM information_schema.TABLES
WHERE TABLE_SCHEMA = 'MOBS_local_DEV';




select DATE_FORMAT(Timestamp, '%d.%m.%y') AS Erfasst_am, ml.Mitglied, ml.BSG, bz.Zweck, rv.Verband as Empfänger
from b_meldeliste as ml
join b___beitragszuordnungen as bz on ml.Zuordnung = bz.id
join b_regionalverband as rv on rv.id = ml.Zuordnung_ID
WHERE bz.id = 1;

select DATE_FORMAT(Timestamp, '%d.%m.%y') AS Erfasst_am, ml.Mitglied, ml.BSG, bz.Zweck, sp.Sparte as Empfänger
from b_meldeliste as ml
join b___beitragszuordnungen as bz on ml.Zuordnung = bz.id
join b_sparte as sp on sp.id = ml.Zuordnung_ID
WHERE bz.id = 2;

select * from b_regionalverband;

truncate TABLE b_meldeliste;






## FUDA Framework-Ideen
Fehlermeldungen in eines sys_errormsg - Tabelle sammeln und dort einen Anzeigetext hinterlegen lassen.
Beispiel: unique-constraint "Spieler_Sparte" verletzt => Fehler: Der SPieler ist dieser Sparte bereits zugewiesen.


## Query für FUDA - nicht user_code, sondern FUDA-core! (jetzt in  config/install.sql)
DROP TABLE IF EXISTS `sys_error_manager`;
CREATE TABLE `sys_error_manager` ( 
  `id` BIGINT UNSIGNED AUTO_INCREMENT NOT NULL,
  `error_log_id` BIGINT UNSIGNED NULL,
  `raw_message` VARCHAR(500) NOT NULL,
  `sql_error_code` INT UNSIGNED NULL,
  `description` VARCHAR(1000) NULL,
  `user_message` VARCHAR(500) NULL,
  `source` VARCHAR(100) NULL,
  `add_fulltext_constraint` VARCHAR(50) NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `unique_code_plus_text` UNIQUE (`sql_error_code`, `add_fulltext_constraint`)
) 
ENGINE = InnoDB;

``` sql
-- Definiere die Datenbank als Variable
SET @database_name = 'db_445253_7';

-- Ändere den Zeichensatz und die Collation der gesamten Datenbank
-- Dieser Befehl muss direkt ausgeführt werden, da ALTER DATABASE nicht dynamisch funktioniert
ALTER DATABASE db_445253_7 CHARACTER SET utf8mb4 COLLATE utf8mb4_german2_ci;

-- Generiere SQL-Befehle, um den Zeichensatz und die Collation aller Tabellen zu ändern
SELECT CONCAT(
    'ALTER TABLE ', @database_name, '.', TABLE_NAME, 
    ' CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_german2_ci;'
) AS sql_command
FROM INFORMATION_SCHEMA.TABLES
WHERE TABLE_SCHEMA = @database_name;

-- Generiere SQL-Befehle, um den Zeichensatz und die Collation aller Spalten zu ändern
SELECT CONCAT(
    'ALTER TABLE ', @database_name, '.', TABLE_NAME, 
    ' MODIFY ', COLUMN_NAME, ' ', COLUMN_TYPE, 
    ' CHARACTER SET utf8mb4 COLLATE utf8mb4_german2_ci;'
) AS sql_command
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = @database_name
  AND CHARACTER_SET_NAME IS NOT NULL;

-- Setze die globalen Standardwerte für Zeichensatz und Collation (falls möglich)
SET GLOBAL character_set_server = 'utf8mb4';
SET GLOBAL collation_server = 'utf8mb4_german2_ci';

-- Überprüfe den Zeichensatz und die Collation der Datenbank
SELECT SCHEMA_NAME, DEFAULT_CHARACTER_SET_NAME, DEFAULT_COLLATION_NAME
FROM INFORMATION_SCHEMA.SCHEMATA
WHERE SCHEMA_NAME = @database_name;

-- Überprüfe den Zeichensatz und die Collation aller Tabellen
SELECT TABLE_NAME, TABLE_COLLATION
FROM INFORMATION_SCHEMA.TABLES
WHERE TABLE_SCHEMA = @database_name;

-- Überprüfe den Zeichensatz und die Collation aller Spalten
SELECT TABLE_NAME, COLUMN_NAME, CHARACTER_SET_NAME, COLLATION_NAME
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = @database_name;

-- Überprüfe die Verbindungseinstellungen
SHOW VARIABLES LIKE 'character_set_connection';
SHOW VARIABLES LIKE 'collation_connection';
```


### b_mitglieder.BSG: NULL -> NOT NULL  (nicht mehr nullable).
Dazu müssen zuerst die FK angepasst werden. Wenn rollback, dann muss das auch wieder geradegezogen werden:

#### FK_mitglieder_bsg
**JETZT:** FK_mitglieder_bsg, ON DELETE:  SET NULL
Was passiert, wenn eine nicht leere BSG gelöscht wird?
RESTRICT verhindert, dass eine BSG gelöscht wird, wenn es Mitglieder gibt.
**NEU:**   FK_mitglieder_bsg, ON DELETE:  RESTRICT
```sql
ALTER TABLE `b_mitglieder` DROP FOREIGN KEY `FK_mitglieder_bsg`;
ALTER TABLE `b_mitglieder` ADD CONSTRAINT `FK_mitglieder_bsg` FOREIGN KEY (`BSG`) REFERENCES `b_bsg` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE;
```

#### FK_mitglieder_bsg
Wie oben...
**JETZT:** FK_b_mitglieder_b___an_aus__aktiv, ON DELETE:  SET NULL
**NEU:**   FK_b_mitglieder_b___an_aus__aktiv, ON DELETE:  RESTRICT
```sql
ALTER TABLE `b_mitglieder` DROP FOREIGN KEY `FK_b_mitglieder_b___an_aus__aktiv`;
ALTER TABLE `b_mitglieder` ADD CONSTRAINT `FK_b_mitglieder_b___an_aus__aktiv` FOREIGN KEY (`aktiv`) REFERENCES `b___an_aus` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE;
```
#### NULL verbieten:
**Achtung - es dürfen keine NULL-Einträge gespeichert sein!**
``` sql
ALTER TABLE `b_mitglieder` CHANGE COLUMN `BSG` `BSG` BIGINT UNSIGNED NOT NULL;
```

### Bemerkungsfeld
``` sql
ALTER TABLE `b_mitglieder` ADD  `Bemerkung` VARCHAR(1000) NULL;
```

### Meldeliste
Meldeliste nicht auf Mitglieder referenzieren, sondern Daten (Vn, Nn, Geb., MNr) direkt eintragen. Sonst kann niemend unterjährig gelöscht werden!
(QS-open issues)

#### Neue Tabelle
``` sql
DROP TABLE IF EXISTS `b_meldeliste`;
CREATE TABLE `b_meldeliste` ( 
  `id` BIGINT UNSIGNED AUTO_INCREMENT NOT NULL,
  `Timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
  `Beitragsjahr` YEAR NOT NULL,
  `Mitglied` VARCHAR(500) NOT NULL,
  `BSG` VARCHAR(500) NOT NULL,
  `Zuordnung` INT UNSIGNED NULL,
  `Zuordnung_ID` BIGINT UNSIGNED NULL COMMENT 'Wenn der Zweck eine ID erfordert' ,
  `Betrag` DECIMAL(10,2) NULL DEFAULT 0.00 ,
  `Beitragsstelle` BIGINT UNSIGNED NOT NULL,
   PRIMARY KEY (`id`),
  CONSTRAINT `FK_medleliste_beitragszuordnungen` FOREIGN KEY (`Zuordnung`) REFERENCES `b___beitragszuordnungen` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `unique_kombinationen` UNIQUE (`Mitglied`, `Beitragsjahr`, `Zuordnung`, `Zuordnung_ID`)
)
ENGINE = InnoDB;
CREATE INDEX `FK_medleliste_beitragszuordnungen` 
ON `b_meldeliste` (
  `Zuordnung` ASC
);
```

#### DROP VIEWS
``` sql
DROP VIEW `b_v_meldeliste_letztes_jahr`;
DROP VIEW `b_v_meldeliste_dieses_jahr`;
```

### Stammmitglied seit und seit entfernen
``` sql
ALTER TABLE `b_mitglieder` DROP COLUMN `Stammmitglied_seit`;
ALTER TABLE `b_mitglieder_in_sparten` DROP COLUMN `seit`;
DROP TRIGGER IF EXISTS `update_stammmitglied_seit`;

```


### Trigger, um das Löschen der indiv. Berechtigung zu verhindern, wenn Mitglied noch eingeschrieben ist.
``` sql
DROP TRIGGER IF EXISTS tr_before_delete_individuelle_berechtigungen;

DELIMITER //

CREATE TRIGGER tr_before_delete_individuelle_berechtigungen
BEFORE DELETE ON b_individuelle_berechtigungen
FOR EACH ROW
BEGIN
    DECLARE v_count_mitglieder_in_sparten INT;
    DECLARE v_count_mitglieder INT;
    
    -- Prüfung 1: Ist das Mitglied noch über diese BSG in einer Sparte angemeldet?
    SELECT COUNT(*) INTO v_count_mitglieder_in_sparten
    FROM b_mitglieder_in_sparten
    WHERE Mitglied = OLD.Mitglied AND BSG = OLD.BSG;
    
    -- Prüfung 2: Ist das Mitglied noch in dieser BSG angemeldet?
    SELECT COUNT(*) INTO v_count_mitglieder
    FROM b_mitglieder
    WHERE id = OLD.Mitglied AND BSG = OLD.BSG;
    
    -- Prüfung aller drei Fälle
    IF v_count_mitglieder_in_sparten > 0 AND v_count_mitglieder > 0 THEN
        -- Fall 1: Mitglied ist sowohl in BSG als auch in Sparte
        SIGNAL SQLSTATE '45000' 
        SET MESSAGE_TEXT = '_MITGLIEDINBSGUNDSPARTE_ Mitglied ist sowohl in dieser BSG als auch in einer Sparte angemeldet';
    ELSEIF v_count_mitglieder_in_sparten > 0 THEN
        -- Fall 2: Mitglied ist nur in Sparte
        SIGNAL SQLSTATE '45000' 
        SET MESSAGE_TEXT = '_MITGLIEDINSPARTE_ Mitglied ist noch über diese BSG in einer Sparte angemeldet';
    ELSEIF v_count_mitglieder > 0 THEN
        -- Fall 3: Mitglied ist nur in BSG
        SIGNAL SQLSTATE '45000' 
        SET MESSAGE_TEXT = '_MITGLIEDINBSG_ Mitglied ist noch in dieser BSG angemeldet';
    END IF;
END //

DELIMITER ;
```

## adm_* => sys_*
``` sql
DROP TABLE IF EXISTS `adm__log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sys_log` (
  `ID` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `zeit` datetime DEFAULT CURRENT_TIMESTAMP,
  `eintrag` varchar(1000) COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=34 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;



DROP TABLE IF EXISTS `adm__rollback`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sys_rollback` (
  `ID` bigint NOT NULL AUTO_INCREMENT,
  `zeit` datetime DEFAULT CURRENT_TIMESTAMP,
  `autor` varchar(200) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `eintrag` varchar(1000) COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=357 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
```

### ALTER TABLE `sys_log`
``` sql
ALTER TABLE `sys_log` CHANGE COLUMN `ID` `ID` BIGINT UNSIGNED AUTO_INCREMENT NOT NULL;
```


## BSG NULL zulassen, aber prüfen.
``` sql
ALTER TABLE `b_mitglieder` CHANGE COLUMN `BSG` `BSG` BIGINT UNSIGNED NULL;
```

## Trigger in der prod nochmal komplett einlesen
Hat ja keine AUswirkungen. Es wurde an vesch. Stellen adm_log zu sys_log geändert.
Datei 03_trigger...sql

## YPUM Verbessern
``` php
return $uid; 
```
im Usermanager (writeUserData) nach dem Senden der Mail einfügen - ganz am Ende..natürlich

Damit kann ich in register (user_code) das b_mitglied eintragen.

Dann auch die alten first-login-trigger droppen
``` sql
DROP TRIGGER IF EXISTS tr_user_insert_create_member;
DROP TRIGGER IF EXISTS tr_user_first_login;
```

### Umstellung RV-Admin auf Frontend
``` sql
ALTER TABLE `b_regionalverband_rechte` ADD  `erweiterte_Rechte` TINYINT UNSIGNED NULL DEFAULT 1 ;
ALTER TABLE `b_regionalverband_rechte` ADD CONSTRAINT `FK_verbandrechte_an_aus` FOREIGN KEY (`erweiterte_Rechte`) REFERENCES `b___an_aus` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;
UPDATE `b_regionalverband_rechte` SET `erweiterte_Rechte` = 1 WHERE `erweiterte_Rechte` IS NULL;
ALTER TABLE `b_regionalverband_rechte` CHANGE COLUMN `erweiterte_Rechte` `erweiterte_Rechte` TINYINT UNSIGNED NOT NULL DEFAULT 1 ;
ALTER TABLE `b___an_aus` ADD COLUMN `bool` TINYINT UNSIGNED NOT NULL DEFAULT 0;
UPDATE `b___an_aus` SET`bool`=1 WHERE `id`=1;
```

### Salden neu
``` sql
ALTER TABLE `b_meldeliste` ADD  `BSG_ID` BIGINT UNSIGNED NOT NULL;
ALTER TABLE `b_zahlungseingaenge` ADD  `Empfaenger` BIGINT UNSIGNED NOT NULL;
-- Vorher benötigt die neue Empfängerspalte gültige Werte:
ALTER TABLE `b_zahlungseingaenge` ADD CONSTRAINT `FK_zahlungseingang_verband` FOREIGN KEY (`Empfaenger`) REFERENCES `b_regionalverband` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;
```


## Berechtigungen setzen (indiv. Berechtigung) bei Spartenanmeldung
1. (bei der Gelegenheit): UNIQUE erweitern. Es kann immer nur eine Kombination geben Mitglied <-> Berechtigte BSG
```sql
ALTER TABLE `b_individuelle_berechtigungen` ADD CONSTRAINT `UNIQUE_Mitglied_BSG` UNIQUE (`Mitglied`, `BSG`);
ALTER TABLE b_mitglieder_in_sparten
ADD CONSTRAINT UNIQUE_Mitglied_Sparte UNIQUE (Mitglied, Sparte);
```

3. Trigger umbenannt, erweitert und in die 03_trigger.sql eingetragen.
Korrekturcode:
```sql
DROP TRIGGER IF EXISTS trg_b_mitglieder_in_sparten_insert_historie;
DROP TRIGGER IF EXISTS trg_b_mitglieder_in_sparten_insert;
DELIMITER $$
CREATE TRIGGER trg_b_mitglieder_in_sparten_insert
AFTER INSERT ON b_mitglieder_in_sparten
FOR EACH ROW
BEGIN
    DECLARE spartenname VARCHAR(255);
    DECLARE bsgname VARCHAR(255);
    DECLARE cnt INT DEFAULT 0;
    -- Spartenname inkl. Verband holen
    SELECT CONCAT(s.Sparte, ' (', r.Kurzname, ')')
      INTO spartenname
      FROM b_sparte AS s
      JOIN b_regionalverband AS r ON r.id = s.Verband
     WHERE s.id = NEW.Sparte;
    -- BSG-Name holen
    SELECT BSG INTO bsgname FROM b_bsg WHERE id = NEW.BSG;
    -- Eintrag in Historie
    INSERT INTO b_mitglieder_historie (MNr, Aktion)
    VALUES (
        NEW.Mitglied,
        CONCAT('Anmeldung in der Sparte ', IFNULL(spartenname, ''), ' für die BSG ', IFNULL(bsgname, ''))
    );
    -- Prüfe, ob die Kombination Mitglied und BSG schon existiert und trage dann die BSG in die indiv. Mitgliederberechtigungen
    SELECT COUNT(*) INTO cnt
    FROM b_individuelle_berechtigungen
    WHERE Mitglied = NEW.Mitglied AND BSG = NEW.BSG;
    IF cnt = 0 THEN
        INSERT INTO b_individuelle_berechtigungen (Mitglied, BSG)
        VALUES (NEW.Mitglied, NEW.BSG);
    END IF;
END$$
DELIMITER ;
```
