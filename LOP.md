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