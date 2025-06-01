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


# Rechnungserstellung
- Rechnungserzeugung? PDF??
- Rechnungen können in der Cloud abgelegt werden - Link kann gespeichert werden

## Nächste Schritte


## In der Prod-DB einfügen 
- TRIGGER (s.open Issues)
- Autoinkrement auf Mitgliedsnummern umschwenken 
```
SET @new_id = 100000;
UPDATE b_mitglieder SET id = (@new_id := @new_id + 1) ORDER BY id;
SELECT MAX(id) + 1 AS neuer_wert FROM b_mitglieder;
ALTER TABLE b_mitglieder AUTO_INCREMENT = <MAX+1_hier_eintragen>;
(z.B. ALTER TABLE b_mitglieder AUTO_INCREMENT = 100043;)
```
- CRONJOB einrichten (prod und local und ggf. qs)
- YPUM-Anpassung nicht vergessen! (s.open Issues)


SET FOREIGN_KEY_CHECKS = 0;
TRUNCATE TABLE `adm_issues`;
TRUNCATE TABLE `adm_log`;
TRUNCATE TABLE `adm_rollback`;
TRUNCATE TABLE `adm_usercount`;
TRUNCATE TABLE `b___an_aus`;
TRUNCATE TABLE `b___geschlecht`;
TRUNCATE TABLE `b___sportart`;
TRUNCATE TABLE `b_bsg`;
TRUNCATE TABLE `b_bsg_deleted`;
TRUNCATE TABLE `b_bsg_rechte`;
TRUNCATE TABLE `b_bsg_wechselantrag`;
TRUNCATE TABLE `b_forderungen`;
TRUNCATE TABLE `b_individuelle_berechtigungen`;
TRUNCATE TABLE `b_mitglieder`;
TRUNCATE TABLE `b_mitglieder_deleted`;
TRUNCATE TABLE `b_mitglieder_in_sparten`;
TRUNCATE TABLE `b_regionalverband`;
TRUNCATE TABLE `b_regionalverband_rechte`;
TRUNCATE TABLE `b_sparte`;
TRUNCATE TABLE `b_zahlungseingaenge`;
TRUNCATE TABLE `y_deleted_users`;
TRUNCATE TABLE `y_roles`;
TRUNCATE TABLE `y_sites`;
TRUNCATE TABLE `y_user`;
TRUNCATE TABLE `y_user_details`;
TRUNCATE TABLE `y_user_fields`;
SET FOREIGN_KEY_CHECKS = 1;

Änderungen
Spartenanmeldung
Spartenabmeldung
Stammdaten ändern

Tabellen
----------
CREATE TABLE `b_mitglieder_historie` ( 
  `id` BIGINT UNSIGNED AUTO_INCREMENT NOT NULL,
  `Timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
  `MNr` BIGINT UNSIGNED NOT NULL,
  `Aktion` VARCHAR(500) NOT NULL,
   PRIMARY KEY (`id`),
  CONSTRAINT `FK_historie_mNr_betroffenes_Mitglied` FOREIGN KEY (`MNr`) REFERENCES `b_mitglieder` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE
)
ENGINE = InnoDB
COMMENT = 'Der Akteur kann über das Rollback-Log ermittelt werden.';
CREATE INDEX `FK_historie_mNr_betroffenes_Mitglied` 
ON `b_mitglieder_historie` (
  `MNr` ASC
);

DROP TABLE IF EXISTS `b_meldeliste`; -- Muss zuerst gedroppt werden, da es eine FK-Verknüpfung gibt
DROP TABLE IF EXISTS `b___beitragszuordnungen`;
CREATE TABLE `b___beitragszuordnungen` ( 
  `id` INT UNSIGNED NOT NULL,
  `Zweck` VARCHAR(200) NOT NULL,
   PRIMARY KEY (`id`)
)
ENGINE = InnoDB;

INSERT INTO `b___beitragszuordnungen` (`id`, `Zweck`) VALUES (1, 'Verbandsbeitrag');
INSERT INTO `b___beitragszuordnungen` (`id`, `Zweck`) VALUES (2, 'Spartenbeitrag');

DROP TABLE IF EXISTS `b_meldeliste`;
CREATE TABLE `b_meldeliste` ( 
  `id` BIGINT UNSIGNED AUTO_INCREMENT NOT NULL,
  `Timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
  `Beitragsjahr` YEAR NOT NULL,
  `MNr` BIGINT UNSIGNED NOT NULL,
  `BSG` BIGINT UNSIGNED NOT NULL,
  `Zuordnung` INT UNSIGNED NULL,
  `Zuordnung_ID` BIGINT NULL COMMENT 'Wenn der Zweck eine ID erfordert' ,
  `Betrag` DECIMAL(10,2) NULL DEFAULT 0.00 ,
   PRIMARY KEY (`id`),
  CONSTRAINT `FK_medleliste_beitragszuordnungen` FOREIGN KEY (`Zuordnung`) REFERENCES `b___beitragszuordnungen` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `FK_meldeliste_BSG` FOREIGN KEY (`BSG`) REFERENCES `b_bsg` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `FK_meldeliste_mitglieder` FOREIGN KEY (`MNr`) REFERENCES `b_mitglieder` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `unique_kombinationen` UNIQUE (`MNr`, `Beitragsjahr`, `Zuordnung`, `Zuordnung_ID`)
)
ENGINE = InnoDB;
CREATE INDEX `FK_medleliste_beitragszuordnungen` 
ON `b_meldeliste` (
  `Zuordnung` ASC
);
CREATE INDEX `FK_meldeliste_BSG` 
ON `b_meldeliste` (
  `BSG` ASC
);


Trigger
-------

DROP TRIGGER IF EXISTS trg_b_mitglieder_update_historie;
DELIMITER $$
CREATE TRIGGER trg_b_mitglieder_update_historie
AFTER UPDATE ON b_mitglieder
FOR EACH ROW
BEGIN
    DECLARE alter_wert VARCHAR(100);
    DECLARE neuer_wert VARCHAR(100);

    -- Für jede relevante Spalte prüfen, ob sich der Wert geändert hat
    IF NOT (OLD.Vorname <=> NEW.Vorname) THEN
        INSERT INTO b_mitglieder_historie (MNr, Aktion)
        VALUES (OLD.id, CONCAT('Änderung Vorname von ''', IFNULL(OLD.Vorname, ''), ''' zu ''', IFNULL(NEW.Vorname, ''), ''''));
    END IF;

    IF NOT (OLD.Nachname <=> NEW.Nachname) THEN
        INSERT INTO b_mitglieder_historie (MNr, Aktion)
        VALUES (OLD.id, CONCAT('Änderung Nachname von ''', IFNULL(OLD.Nachname, ''), ''' zu ''', IFNULL(NEW.Nachname, ''), ''''));
    END IF;

    IF NOT (OLD.BSG <=> NEW.BSG) THEN
        SELECT concat(BSG, ' (VKZ ', VKZ, ')') INTO alter_wert FROM b_bsg WHERE id = OLD.BSG;
        SELECT concat(BSG, ' (VKZ ', VKZ, ')') INTO neuer_wert FROM b_bsg WHERE id = NEW.BSG;
        INSERT INTO b_mitglieder_historie (MNr, Aktion)
        VALUES (OLD.id, CONCAT('Änderung BSG von ''', IFNULL(alter_wert, ''), ''' zu ''', IFNULL(neuer_wert, ''), ''''));
    END IF;

    IF NOT (OLD.Mail <=> NEW.Mail) THEN
        INSERT INTO b_mitglieder_historie (MNr, Aktion)
        VALUES (OLD.id, CONCAT('Änderung Mail von ''', IFNULL(OLD.Mail, ''), ''' zu ''', IFNULL(NEW.Mail, ''), ''''));
    END IF;

    IF NOT (OLD.aktiv <=> NEW.aktiv) THEN
        SELECT wert INTO alter_wert FROM b___an_aus WHERE id = OLD.aktiv;
        SELECT wert INTO neuer_wert FROM b___an_aus WHERE id = NEW.aktiv;
        INSERT INTO b_mitglieder_historie (MNr, Aktion)
        VALUES (OLD.id, CONCAT('Änderung aktiv von ''', IFNULL(alter_wert, ''), ''' zu ''', IFNULL(neuer_wert, ''), ''''));
    END IF;
    
    IF NOT (OLD.Geburtsdatum <=> NEW.Geburtsdatum) THEN
        INSERT INTO b_mitglieder_historie (MNr, Aktion)
        VALUES (
            OLD.id,
            CONCAT(
                'Änderung Geburtsdatum von ''',
                IFNULL(DATE_FORMAT(OLD.Geburtsdatum, '%d.%m.%Y'), ''),
                ''' zu ''',
                IFNULL(DATE_FORMAT(NEW.Geburtsdatum, '%d.%m.%Y'), ''),
                ''''
            )
        );
    END IF;

    IF NOT (OLD.Mailbenachrichtigung <=> NEW.Mailbenachrichtigung) THEN
        SELECT wert INTO alter_wert FROM b___an_aus WHERE id = OLD.Mailbenachrichtigung;
        SELECT wert INTO neuer_wert FROM b___an_aus WHERE id = NEW.Mailbenachrichtigung;
        INSERT INTO b_mitglieder_historie (MNr, Aktion)
        VALUES (OLD.id, CONCAT('Änderung Mailbenachrichtigung von ''', IFNULL(alter_wert, ''), ''' zu ''', IFNULL(neuer_wert, ''), ''''));
    END IF;

    IF NOT (OLD.Geschlecht <=> NEW.Geschlecht) THEN
        SELECT auswahl INTO alter_wert FROM b___geschlecht WHERE id = OLD.Geschlecht;
        SELECT auswahl INTO neuer_wert FROM b___geschlecht WHERE id = NEW.Geschlecht;
        INSERT INTO b_mitglieder_historie (MNr, Aktion)
        VALUES (OLD.id, CONCAT('Änderung Geschlecht von ''', IFNULL(alter_wert, ''), ''' zu ''', IFNULL(neuer_wert, ''), ''''));
    END IF;

    IF NOT (OLD.Stammmitglied_seit <=> NEW.Stammmitglied_seit) THEN
        INSERT INTO b_mitglieder_historie (MNr, Aktion)
        VALUES (OLD.id, CONCAT('Änderung Stammmitglied_seit von ''', IFNULL(OLD.Stammmitglied_seit, ''), ''' zu ''', IFNULL(NEW.Stammmitglied_seit, ''), ''''));
    END IF;

    IF NOT (OLD.y_id <=> NEW.y_id) THEN
        INSERT INTO b_mitglieder_historie (MNr, Aktion)
        VALUES (OLD.id, CONCAT('Änderung y_id von ''', IFNULL(OLD.y_id, ''), ''' zu ''', IFNULL(NEW.y_id, ''), ''''));
    END IF;

END$$

DELIMITER ;


DROP TRIGGER IF EXISTS trg_b_mitglieder_in_sparten_insert_historie;
DELIMITER $$

CREATE TRIGGER trg_b_mitglieder_in_sparten_insert_historie
AFTER INSERT ON b_mitglieder_in_sparten
FOR EACH ROW
BEGIN
    DECLARE spartenname VARCHAR(255);
    DECLARE bsgname VARCHAR(255);

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
END$$

DELIMITER ;

-- Trigger für Anmeldung in einer Sparte
DROP TRIGGER IF EXISTS trg_b_mitglieder_in_sparten_insert_historie;
DELIMITER $$

CREATE TRIGGER trg_b_mitglieder_in_sparten_insert_historie
AFTER INSERT ON b_mitglieder_in_sparten
FOR EACH ROW
BEGIN
    DECLARE spartenname VARCHAR(255);
    DECLARE bsgname VARCHAR(255);

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
END$$

-- Trigger für Abmeldung aus einer Sparte
DROP TRIGGER IF EXISTS trg_b_mitglieder_in_sparten_delete_historie;
DELIMITER $$

#####################################################################################

CREATE TRIGGER trg_b_mitglieder_in_sparten_delete_historie
AFTER DELETE ON b_mitglieder_in_sparten
FOR EACH ROW
BEGIN
    DECLARE spartenname VARCHAR(255);
    DECLARE bsgname VARCHAR(255);

    -- Spartenname inkl. Verband holen
    SELECT CONCAT(s.Sparte, ' (', r.Kurzname, ')')
      INTO spartenname
      FROM b_sparte AS s
      JOIN b_regionalverband AS r ON r.id = s.Verband
     WHERE s.id = OLD.Sparte;

    -- BSG-Name holen
    SELECT BSG INTO bsgname FROM b_bsg WHERE id = OLD.BSG;

    -- Eintrag in Historie
    INSERT INTO b_mitglieder_historie (MNr, Aktion)
    VALUES (
        OLD.Mitglied,
        CONCAT('Abmeldung von der Sparte ', IFNULL(spartenname, ''), ' für die BSG ', IFNULL(bsgname, ''))
    );
END$$

DELIMITER ;

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






DROP VIEW IF EXISTS b_v_meldeliste_dieses_jahr;
CREATE VIEW b_v_meldeliste_dieses_jahr AS 
(SELECT
l.id,
l.Timestamp as Erfasst_am,
l.Beitragsjahr,
concat(m.Vorname, ' ',m.Nachname, ' (',m.id, ')') as Mitglied,
concat (b.BSG, ' (',b.VKZ,')') as Zahlungspflichtige_BSG,
z.Zweck as Zuordnung,
r.Kurzname as Beschreibung,
r.Basisbeitrag as Betrag
FROM b_meldeliste            as l
JOIN b_mitglieder            as m on m.id = l.MNr
JOIN b_bsg                   as b on b.id = l.BSG
JOIN b___beitragszuordnungen as z on z.id = l.Zuordnung
JOIN b_regionalverband       as r on r.id = l.Zuordnung_ID
WHERE Zuordnung = 1 AND Beitragsjahr = YEAR(CURDATE())

UNION 

SELECT
l.id,
l.Timestamp as Erfasst_am,
l.Beitragsjahr,
concat(m.Vorname, ' ',m.Nachname, ' (',m.id, ')') as Mitglied,
concat (b.BSG, ' (',b.VKZ,')') as Zahlungspflichtige_BSG,
z.Zweck as Zuordnung,
s.Sparte as Beschreibung,
s.Spartenbeitrag as Betrag
FROM b_meldeliste            as l
JOIN b_mitglieder            as m on m.id = l.MNr
JOIN b_bsg                   as b on b.id = l.BSG
JOIN b___beitragszuordnungen as z on z.id = l.Zuordnung
JOIN b_sparte                as s on s.id = l.Zuordnung_ID
WHERE Zuordnung = 2 AND Beitragsjahr = YEAR(CURDATE())
);


DROP VIEW IF EXISTS b_v_meldeliste_letztes_jahr;
CREATE VIEW b_v_meldeliste_letztes_jahr AS 
(SELECT
l.id,
l.Timestamp as Erfasst_am,
l.Beitragsjahr,
concat(m.Vorname, ' ',m.Nachname, ' (',m.id, ')') as Mitglied,
concat (b.BSG, ' (',b.VKZ,')') as Zahlungspflichtige_BSG,
z.Zweck as Zuordnung,
r.Kurzname as Beschreibung,
r.Basisbeitrag as Betrag
FROM b_meldeliste            as l
JOIN b_mitglieder            as m on m.id = l.MNr
JOIN b_bsg                   as b on b.id = l.BSG
JOIN b___beitragszuordnungen as z on z.id = l.Zuordnung
JOIN b_regionalverband       as r on r.id = l.Zuordnung_ID
WHERE Zuordnung = 1 AND Beitragsjahr = YEAR(CURDATE()) -1

UNION 

SELECT
l.id,
l.Timestamp as Erfasst_am,
l.Beitragsjahr,
concat(m.Vorname, ' ',m.Nachname, ' (',m.id, ')') as Mitglied,
concat (b.BSG, ' (',b.VKZ,')') as Zahlungspflichtige_BSG,
z.Zweck as Zuordnung,
s.Sparte as Beschreibung,
s.Spartenbeitrag as Betrag
FROM b_meldeliste            as l
JOIN b_mitglieder            as m on m.id = l.MNr
JOIN b_bsg                   as b on b.id = l.BSG
JOIN b___beitragszuordnungen as z on z.id = l.Zuordnung
JOIN b_sparte                as s on s.id = l.Zuordnung_ID
WHERE Zuordnung = 2 AND Beitragsjahr = YEAR(CURDATE())-1 
);



Bau das noch ein:
ALTER DATABASE <DATENBANK>
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_0900_ai_ci;

  SELECT CONCAT('ALTER TABLE `', TABLE_NAME, '` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;')
FROM information_schema.TABLES
WHERE TABLE_SCHEMA = 'MOBS_local_DEV';




UNIQUE VKZ !!


SONNTAG
- Ansicht der b_v - Views einbauen (ACHTUNG - Leserechte beachten! Ebene Regionalverband (aber Achtung - was ist,
 wenn Mitglied wechselt? Es müsste immer
die zahlungspflichtige BSG sein - aber was ist, wenn NULL und wie kann das überhaupt passieren?
Wie ist es JETZT geregelt?
LÖSUNG: Keine BSG = kein Basisbeitrag, aber auch keine Sparten möglich -> passt

- SOLL-Berechnung auf Grundlage der Meldeliste stellen

- QS - System einstellen

- Alle DB-Operationen (hier) bei der prod $ QS-DB machen

- Noch nicht geklärt: Soll in den configs in den Suchqueries das "info:" rein oder nicht? Ausprobieren!
