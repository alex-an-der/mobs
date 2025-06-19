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

## LÖSCHEN VON MITGLIEDER ????   
=> Auf RV-Ebene: Liste zeigen, MG entfernen?

## Registrieren melder direkt in BSG? Besser: ANtrag und dann aufnehmen und erst dann LogIn möglich
1. Antrag stellen
2. Beim LogIn:
     - Wie bisher in b_mitglieder aufnehmen (wie ist das jetzt?)
     -  Aber BSG nicht automatisch übertragen
     -  Generelle Abfrage: BSG IS NULL -> Kein LogIn
     -  SQL: NOT NULL wieder rausnehmen








## Nächste Schritte
### Sicherheit!
- **ACHTUNG ## Den Meldelisteneintrag schützen (aus dem docRoot raus). Das darf nicht vor dem 15.2. ausgelöst werden und kann auch für DoS genutzt werden. ## ACHTUNG**

- Auch yconf rausholen! Zumindest die dbconnect.

 
# In der Prod-DB einfügen und neue Version v0.1.9-qa.x

### b_mitglieder.BSG: NULL -> NOT NULL  (nicht mehr nullable).
Dazu müssen zuerst die FK angepasst werden. Wenn rollback, dann muss das auch wieder geradegezogen werden:

#### FK_mitglieder_bsg
**JETZT:** FK_mitglieder_bsg, ON DELETE:  SET NULL
Was passiert, wenn eine nicht leere BSG gelöscht wird?
RESTRICT verhindert, dass eine BSG gelöscht wird, wenn es Mitglieder gibt.
**NEU:**   FK_mitglieder_bsg, ON DELETE:  RESTRICT
ALTER TABLE `b_mitglieder` DROP FOREIGN KEY `FK_mitglieder_bsg`;
ALTER TABLE `b_mitglieder` ADD CONSTRAINT `FK_mitglieder_bsg` FOREIGN KEY (`BSG`) REFERENCES `b_bsg` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE;

#### FK_mitglieder_bsg
Wie oben...
**JETZT:** FK_b_mitglieder_b___an_aus__aktiv, ON DELETE:  SET NULL
**NEU:**   FK_b_mitglieder_b___an_aus__aktiv, ON DELETE:  RESTRICT
ALTER TABLE `b_mitglieder` DROP FOREIGN KEY `FK_b_mitglieder_b___an_aus__aktiv`;
ALTER TABLE `b_mitglieder` ADD CONSTRAINT `FK_b_mitglieder_b___an_aus__aktiv` FOREIGN KEY (`aktiv`) REFERENCES `b___an_aus` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE;

#### NULL verbieten:
**Achtung - es dürfen keine NULL-Einträge gespeichert sein!**
ALTER TABLE `b_mitglieder` CHANGE COLUMN `BSG` `BSG` BIGINT UNSIGNED NOT NULL;

### Bemerkungsfeld
ALTER TABLE `b_mitglieder` ADD  `Bemerkung` VARCHAR(1000) NULL;

### Meldeliste
Meldeliste nicht auf Mitglieder referenzieren, sondern Daten (Vn, Nn, Geb., MNr) direkt eintragen. Sonst kann niemend unterjährig gelöscht werden!
(QS-open issues)

#### Neue Tabelle
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

#### DROP VIEWS
DROP VIEW `b_v_meldeliste_letztes_jahr`;
DROP VIEW `b_v_meldeliste_dieses_jahr`;


### Stammmitglied seit und seit entfernen
ALTER TABLE `b_mitglieder` DROP COLUMN `Stammmitglied_seit`;
ALTER TABLE `b_mitglieder_in_sparten` DROP COLUMN `seit`;
DROP TRIGGER IF EXISTS `update_stammmitglied_seit`;


### ALTER TABLE `sys_log`
ALTER TABLE `sys_log` CHANGE COLUMN `ID` `ID` BIGINT UNSIGNED AUTO_INCREMENT NOT NULL;


### Trigger, um das Löschen der indiv. Berechtigung zu verhindern, wenn Mitglied noch eingeschrieben ist.
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


## adm_* => sys_*
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

