

DROP TRIGGER IF EXISTS tr_user_first_login;
DELIMITER //
CREATE TRIGGER tr_user_first_login 
AFTER UPDATE ON y_user
FOR EACH ROW
BEGIN
    DECLARE mid BIGINT;
    DECLARE bsg_value INT;
    DECLARE wechselantrag_count INT DEFAULT 0;

    -- Ermittle die Mitglieds-ID (mid) anhand der y_id
    SELECT id INTO mid FROM b_mitglieder WHERE y_id = NEW.id LIMIT 1;

    -- Hole den BSG-Wert aus b_mitglieder
    IF mid IS NOT NULL THEN
        SELECT BSG INTO bsg_value FROM b_mitglieder WHERE id = mid LIMIT 1;

        -- Prüfe, ob es bereits einen Wechselantrag für dieses Mitglied gibt
        SELECT COUNT(*) INTO wechselantrag_count
        FROM b_bsg_wechselantrag
        WHERE m_id = mid;

        -- Wenn nicht vorhanden und BSG vorhanden, füge einen Wechselantrag ein
        IF wechselantrag_count = 0 AND bsg_value IS NOT NULL THEN
            INSERT INTO b_bsg_wechselantrag (m_id, Ziel_BSG)
            VALUES (mid, bsg_value);
        END IF;
    END IF;

    -- Optional: Erster Login → Mitglied anlegen (wie bisher)
    IF OLD.lastlogin IS NULL AND NEW.lastlogin IS NOT NULL AND NEW.run_trigger = 1 THEN
        INSERT INTO b_mitglieder (y_id, Mail, Vorname, Nachname, Geschlecht, Geburtsdatum, Mailbenachrichtigung, BSG)
            SELECT 
                NEW.id,
                NEW.mail,
                ud.vname,
                ud.nname,
                (SELECT fieldvalue FROM y_user_details AS d JOIN y_user_fields AS f ON d.fieldID = f.ID WHERE userID = NEW.id AND fieldname = 'geschlecht' LIMIT 1),
                (SELECT fieldvalue FROM y_user_details AS d JOIN y_user_fields AS f ON d.fieldID = f.ID WHERE userID = NEW.id AND fieldname = 'gebdatum' LIMIT 1),
                (SELECT fieldvalue FROM y_user_details AS d JOIN y_user_fields AS f ON d.fieldID = f.ID WHERE userID = NEW.id AND fieldname = 'okformail' LIMIT 1),
                (SELECT fieldvalue FROM y_user_details AS d JOIN y_user_fields AS f ON d.fieldID = f.ID WHERE userID = NEW.id AND fieldname = 'bsg' LIMIT 1)
            FROM y_v_userdata AS ud
            WHERE ud.userID = NEW.id;

        -- Aktualisiere mid und bsg_value nach dem Insert
        SELECT id, BSG INTO mid, bsg_value FROM b_mitglieder WHERE y_id = NEW.id LIMIT 1;

        -- Füge individuelle Berechtigung hinzu, falls BSG vorhanden
        IF bsg_value IS NOT NULL THEN
            INSERT INTO b_individuelle_berechtigungen (Mitglied, BSG)
            VALUES (mid, bsg_value);
        END IF;

        -- Füge Wechselantrag hinzu, falls noch nicht vorhanden
        SELECT COUNT(*) INTO wechselantrag_count
        FROM b_bsg_wechselantrag
        WHERE m_id = mid;

        IF wechselantrag_count = 0 AND bsg_value IS NOT NULL THEN
            INSERT INTO b_bsg_wechselantrag (m_id, Ziel_BSG)
            VALUES (mid, bsg_value);
        END IF;

        -- Zähle die Mitglieder mit Stamm-BSG
        INSERT INTO `adm_usercount` (timestamp, Anzahl)
        SELECT NOW(), COUNT(*) FROM b_mitglieder WHERE BSG IS NOT NULL;
    END IF;
END;
//
DELIMITER ;
-- -----------------------------------------------------------------------------------
DROP TRIGGER IF EXISTS tr_manuelles_mitglied_anlegen;
-- Mitglied ohne y_id anlegen: Setze Berechtigung für Stamm-BSG
DELIMITER //
CREATE TRIGGER tr_manuelles_mitglied_anlegen
AFTER INSERT ON b_mitglieder
FOR EACH ROW
BEGIN
    IF NEW.y_id IS NULL AND NEW.BSG IS NOT NULL THEN
            INSERT INTO b_individuelle_berechtigungen (Mitglied, BSG)
            VALUES (NEW.id, NEW.BSG);

        -- Zähle die Mitglieder mit Stamm-BSG
        INSERT INTO `adm_usercount` (timestamp, Anzahl)
        SELECT NOW(), COUNT(*) FROM b_mitglieder WHERE BSG IS NOT NULL;

    END IF;
END;
//
DELIMITER ;



-- -----------------------------------------------------------------------------------

DROP TRIGGER IF EXISTS before_delete_y_user;
DELIMITER //
CREATE TRIGGER before_delete_y_user
BEFORE DELETE ON `y_user`
FOR EACH ROW
BEGIN
    -- Declare variables for error handling
    DECLARE success BOOLEAN DEFAULT TRUE;
    DECLARE error_msg VARCHAR(255);
    DECLARE member_exists INT DEFAULT 0;
    
    -- Use exception handler
    DECLARE CONTINUE HANDLER FOR SQLEXCEPTION
    BEGIN
        -- Set error flag and message
        SET success = FALSE;
        SET error_msg = CONCAT('Error occurred while backing up user data for deletion. User ID: ', OLD.id);
        
        -- Log the error to the existing adm_log table
        INSERT INTO `adm_log` (`zeit`, `eintrag`) 
        VALUES (NOW(), error_msg);
        
        -- Signal to prevent the delete operation
        SIGNAL SQLSTATE '45000' 
        SET MESSAGE_TEXT = error_msg;
    END;
    
    -- Try to insert into deleted users table
    INSERT INTO `y_deleted_users` (`y_id`, `mail`, `delete_date`)
    VALUES (OLD.id, OLD.mail, NOW());
    
    -- Check if there's a corresponding record in b_mitglieder
    -- The y_id in b_mitglieder corresponds to the id in y_user
    SELECT COUNT(*) INTO member_exists FROM `b_mitglieder` WHERE `y_id` = OLD.id;
    
    -- If member record exists, backup to b_mitglieder_deleted
    IF member_exists > 0 THEN
        INSERT INTO `b_mitglieder_deleted` (
            `id`, `y_id`, `BSG`, `Vorname`, `Nachname`, `Mail`, 
            `Geschlecht`, `Geburtsdatum`, `Mailbenachrichtigung`, `delete_date`
        )
        SELECT 
            `id`, `y_id`, `BSG`, `Vorname`, `Nachname`, `Mail`, 
            `Geschlecht`, `Geburtsdatum`, `Mailbenachrichtigung`, NOW()
        FROM 
            `b_mitglieder` 
        WHERE 
            `y_id` = OLD.id;
            
        -- Log member data backup
        INSERT INTO `adm_log` (`zeit`, `eintrag`)
        VALUES (NOW(), CONCAT('Member data for user ID: ', OLD.id, ' has been backed up to b_mitglieder_deleted and y_deleted_user.'));
    END IF;
    
    -- Log successful backup before deletion
    INSERT INTO `adm_log` (`zeit`, `eintrag`)
    VALUES (NOW(), CONCAT('User with ID: ', OLD.id, ' and email: ', OLD.mail, ' will be deleted. Data backed up.'));
    
    -- Zähle die Mitglieder mit Stamm-BSG
    INSERT INTO `adm_usercount` (timestamp, Anzahl)
    SELECT NOW(), COUNT(*) FROM b_mitglieder WHERE BSG IS NOT NULL;

END //
DELIMITER ;

-- -----------------------------------------------------------------------------------

DROP TRIGGER IF EXISTS before_delete_b_bsg;
DELIMITER //
CREATE TRIGGER before_delete_b_bsg
BEFORE DELETE ON `b_bsg`
FOR EACH ROW
BEGIN
    -- Declare variables for error handling
    DECLARE success BOOLEAN DEFAULT TRUE;
    DECLARE error_msg VARCHAR(255);
    
    -- Use exception handler
    DECLARE CONTINUE HANDLER FOR SQLEXCEPTION
    BEGIN
        -- Set error flag and message
        SET success = FALSE;
        SET error_msg = CONCAT('Error occurred while backing up BSG data for deletion. BSG ID: ', OLD.id);
        
        -- Log the error to the adm_log table
        INSERT INTO `adm_log` (`zeit`, `eintrag`) 
        VALUES (NOW(), error_msg);
        
        -- Signal to prevent the delete operation
        SIGNAL SQLSTATE '45000' 
        SET MESSAGE_TEXT = error_msg;
    END;
    
    -- Try to insert into deleted BSG table
    INSERT INTO `b_bsg_deleted` (
        `id`, `Verband`, `BSG`, `Ansprechpartner`, 
        `RE_Name`, `RE_Name2`, `RE_Strasse_Nr`, 
        `RE_Strasse2`, `RE_PLZ_Ort`, `VKZ`, `delete_date`
    )
    VALUES (
        OLD.id, OLD.Verband, OLD.BSG, OLD.Ansprechpartner,
        OLD.RE_Name, OLD.RE_Name2, OLD.RE_Strasse_Nr,
        OLD.RE_Strasse2, OLD.RE_PLZ_Ort, OLD.VKZ, NOW()
    );
    
    -- Log successful backup before deletion
    INSERT INTO `adm_log` (`zeit`, `eintrag`)
    VALUES (NOW(), CONCAT('BSG with ID: ', OLD.id, ' (', OLD.BSG, ') will be deleted. Data backed up.'));
    
    -- Zähle die Mitglieder mit Stamm-BSG
    INSERT INTO `adm_usercount` (timestamp, Anzahl)
    SELECT NOW(), COUNT(*) FROM b_mitglieder WHERE BSG IS NOT NULL;

END //
DELIMITER ;

-- -----------------------------------------------------------------------------------

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

-- -----------------------------------------------------------------------------------

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

DELIMITER ;

-- -----------------------------------------------------------------------------------


-- Trigger für Abmeldung aus einer Sparte
DROP TRIGGER IF EXISTS trg_b_mitglieder_in_sparten_delete_historie;
DELIMITER $$

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
        CONCAT('Abmeldung aus der Sparte ', IFNULL(spartenname, ''), ' für die BSG ', IFNULL(bsgname, ''))
    );
END$$

DELIMITER ;

-- -----------------------------------------------------------------------------------

DROP FUNCTION IF EXISTS berechtigte_elemente;
DELIMITER //

CREATE FUNCTION berechtigte_elemente(uid INT, target VARCHAR(50))
RETURNS TEXT
DETERMINISTIC
READS SQL DATA
BEGIN
    DECLARE result TEXT DEFAULT '';

    SELECT 
    CASE target
        WHEN 'verband' THEN (
            SELECT GROUP_CONCAT(DISTINCT verband_id)
            FROM (
                SELECT v.id as verband_id, r.Nutzer
                FROM b_regionalverband as v
                JOIN b_regionalverband_rechte as r on r.Verband = v.id 
                WHERE r.Nutzer = uid
            ) berechtigungen
        )
        WHEN 'bsg' THEN (
            SELECT GROUP_CONCAT(DISTINCT bsg_id)
            FROM (
                SELECT b.id as bsg_id, b.BSG, Nutzer
                FROM b_bsg as b
                LEFT JOIN b_bsg_rechte as br ON b.id = br.BSG
                JOIN y_user as y ON Nutzer = y.id
                WHERE y.id = uid
                -- UNION
                -- SELECT b.id as bsg_id, b.BSG, Nutzer
                -- FROM b_bsg as b
                -- LEFT JOIN b_regionalverband_rechte as vr ON b.Verband = vr.Verband
                -- WHERE Nutzer = uid
            ) berechtigungen
        )
        WHEN 'sparte' THEN (
            SELECT GROUP_CONCAT(DISTINCT sparte_id)
            FROM (
                select s.id as sparte_id, s.Sparte, s.Verband
                from b_sparte as s
                join b_regionalverband_rechte r on s.Verband = r.Verband
                where r.Nutzer=uid
            ) berechtigungen
        )
        WHEN 'mitglied' THEN (
            SELECT GROUP_CONCAT(DISTINCT ID)
            FROM (
                SELECT member_bsg.id as ID, member_bsg.bsg as BSG, v.id as Verband 
                FROM(
                    SELECT mis.Mitglied as id , mis.BSG as bsg
                    FROM b_mitglieder_in_sparten as mis
                    union
                    SELECT m.id as id, m.BSG as bsg
                    FROM b_mitglieder as m
                ) member_bsg
                JOIN b_bsg on b_bsg.id = member_bsg.bsg
                JOIN b_regionalverband as v on v.id = b_bsg.Verband
            ) b_und_v
            WHERE (FIND_IN_SET(b_und_v.BSG, berechtigte_elemente_sub1(uid, 'BSG')) > 0) 
            OR (FIND_IN_SET(b_und_v.Verband, berechtigte_elemente_sub1(uid, 'verband')) > 0)
        )
        WHEN 'individuelle_mitglieder' THEN (
        SELECT GROUP_CONCAT(DISTINCT ID)
        FROM(
            SELECT  ir.Mitglied as ID, b.id as bsg_id, b.BSG
                FROM b_bsg as b
                LEFT JOIN b_bsg_rechte as br ON b.id = br.BSG
                JOIN y_user as y ON Nutzer = y.id
                join b_individuelle_berechtigungen as ir on b.id = ir.BSG 
                WHERE y.id = uid
            )indiv_m
        )
        ELSE ''
    END INTO result;
    
    RETURN COALESCE(result, '');
END //

DELIMITER ;

-- -----------------------------------------------------------------------------------

DROP TRIGGER IF EXISTS trg_after_update_bsg_wechselantrag;
DELIMITER //

CREATE TRIGGER trg_after_update_bsg_wechselantrag
AFTER UPDATE ON b_bsg_wechselantrag
FOR EACH ROW
BEGIN
    -- Prüfe, ob sich Ziel_BSG geändert hat und der neue Wert nicht NULL ist
    IF NEW.Ziel_BSG <> OLD.Ziel_BSG AND NEW.Ziel_BSG IS NOT NULL THEN
        -- Prüfe, ob es schon eine Berechtigung gibt
        IF NOT EXISTS (
            SELECT 1 FROM b_individuelle_berechtigungen
            WHERE Mitglied = NEW.m_id AND BSG = NEW.Ziel_BSG
        ) THEN
            -- Füge die Berechtigung ein
            INSERT INTO b_individuelle_berechtigungen (Mitglied, BSG)
            VALUES (NEW.m_id, NEW.Ziel_BSG);
        END IF;
    END IF;
END;
//

DELIMITER ;

-- -----------------------------------------------------------------------------------

DROP TRIGGER IF EXISTS tr_update_mail_in_y_user;
DELIMITER //
CREATE TRIGGER tr_update_mail_in_y_user
AFTER UPDATE ON b_mitglieder
FOR EACH ROW
BEGIN
    IF NEW.Mail <> OLD.Mail THEN
        UPDATE y_user
        SET mail = NEW.Mail
        WHERE id = NEW.y_id;
    END IF;
END;
//
DELIMITER ;

-- -----------------------------------------------------------------------------------

DROP TRIGGER IF EXISTS tr_bsg_change_set_stammmitglied_seit;
-- Wenn sich die BSG eines Mitglieds ändert, setze Stammmitglied_seit auf das aktuelle Datum
DELIMITER //
CREATE TRIGGER tr_bsg_change_set_stammmitglied_seit
BEFORE UPDATE ON b_mitglieder
FOR EACH ROW
BEGIN
    IF NEW.BSG <> OLD.BSG THEN
        SET NEW.Stammmitglied_seit = NOW();
    END IF;
END;
//
DELIMITER ;

-- -----------------------------------------------------------------------------------

DROP FUNCTION IF EXISTS berechtigte_elemente_sub1;
DELIMITER //

CREATE FUNCTION berechtigte_elemente_sub1(uid INT, target VARCHAR(50))
RETURNS TEXT
DETERMINISTIC
READS SQL DATA
BEGIN
    DECLARE result TEXT DEFAULT '';

    SELECT 
    CASE target
        WHEN 'verband' THEN (
            SELECT GROUP_CONCAT(DISTINCT verband_id)
            FROM (
                SELECT v.id as verband_id, r.Nutzer
                FROM b_regionalverband as v
                JOIN b_regionalverband_rechte as r on r.Verband = v.id 
                WHERE r.Nutzer = uid
            ) berechtigungen
        )
        WHEN 'bsg' THEN (
            SELECT GROUP_CONCAT(DISTINCT bsg_id)
            FROM (
                SELECT b.id as bsg_id, b.BSG, Nutzer
                FROM b_bsg as b
                LEFT JOIN b_bsg_rechte as br ON b.id = br.BSG
                JOIN y_user as y ON Nutzer = y.id
                WHERE y.id = uid
            ) berechtigungen
        )
        WHEN 'sparte' THEN (
            SELECT GROUP_CONCAT(DISTINCT sparte_id)
            FROM (
                select s.id as sparte_id, s.Sparte, s.Verband
                from b_sparte as s
                join b_regionalverband_rechte r on s.Verband = r.Verband
                where r.Nutzer=uid
            ) berechtigungen
        )
        WHEN 'mitglied' THEN (
            SELECT GROUP_CONCAT(DISTINCT ID)
            FROM (
                SELECT member_bsg.id as ID, member_bsg.bsg as BSG, v.id as Verband 
                FROM(
                    SELECT mis.Mitglied as id , mis.BSG as bsg
                    FROM b_mitglieder_in_sparten as mis
                    union
                    SELECT m.id as id, m.BSG as bsg
                    FROM b_mitglieder as m
                ) member_bsg
                JOIN b_bsg on b_bsg.id = member_bsg.bsg
                JOIN b_regionalverband as v on v.id = b_bsg.Verband
            ) b_und_v
            WHERE (FIND_IN_SET(b_und_v.BSG, berechtigte_elemente_sub1(uid, 'BSG')) > 0) 
            OR (FIND_IN_SET(b_und_v.Verband, berechtigte_elemente_sub1(uid, 'verband')) > 0)
        )
        WHEN 'individuelle_mitglieder' THEN (
        SELECT GROUP_CONCAT(DISTINCT ID)
        FROM(
            SELECT  ir.Mitglied as ID, b.id as bsg_id, b.BSG
                FROM b_bsg as b
                LEFT JOIN b_bsg_rechte as br ON b.id = br.BSG
                JOIN y_user as y ON Nutzer = y.id
                join b_individuelle_berechtigungen as ir on b.id = ir.BSG 
                WHERE y.id = uid
            )indiv_m
        )
        ELSE ''
    END INTO result;
    
    RETURN COALESCE(result, '');
END //

DELIMITER ;
