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


# Rechnungserstellung
- Rechnungserzeugung? PDF??
- Rechnungen können in der Cloud abgelegt werden - Link kann gespeichert werden

# FUDA Framework-Ideen
Fehlermeldungen in eines sys_errormsg - Tabelle sammeln und dort einen Anzeigetext hinterlegen lassen.
Beispiel: unique-constraint "Spieler_Sparte" verletzt => Fehler: Der SPieler ist dieser Sparte bereits zugewiesen.



## Nächste Schritte




## In der Prod-DB einfügen und neue Version v0.1.9-qa.1

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


**ACHTUNG ## Den Meldelisteneintrag schützen (aus dem docRoot raus). Das darf nicht vor dem 15.2. ausgelöst werden und kann auch für DoS genutzt werden. ## ACHTUNG**









## ERLEDIGT (sollte eigentlich)

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



SONNTAG
- Ansicht der b_v - Views einbauen (ACHTUNG - Leserechte beachten! Ebene Regionalverband (aber Achtung - was ist,
 wenn Mitglied wechselt? Es müsste immer
die zahlungspflichtige BSG sein - aber was ist, wenn NULL und wie kann das überhaupt passieren?
Wie ist es JETZT geregelt?
LÖSUNG: Keine BSG = kein Basisbeitrag, aber auch keine Sparten möglich -> passt


- Alle DB-Operationen (hier) bei der prod $ QS-DB machen














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