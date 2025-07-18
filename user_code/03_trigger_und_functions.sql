-- -----------------------------------------------------------------------------------

DROP TRIGGER IF EXISTS tr_manuelles_mitglied_anlegen;
-- Mitglied ohne y_id anlegen: Setze Berechtigung für Stamm-BSG
DELIMITER //
CREATE TRIGGER tr_manuelles_mitglied_anlegen
AFTER INSERT ON b_mitglieder
FOR EACH ROW
BEGIN
    -- Prüfe, ob BSG NULL ist
    IF NEW.BSG IS NULL THEN
        -- Fehler ausgeben und Einfügen verhindern
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Beim manuellen Einfügen von Mitgliedern darf die BSG nicht leer gelassen werden.';
    END IF;

    -- Originalcode bleibt erhalten
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
        
        -- Log the error to the existing sys_log table
        INSERT INTO `sys_log` (`zeit`, `eintrag`) 
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
        INSERT INTO `sys_log` (`zeit`, `eintrag`)
        VALUES (NOW(), CONCAT('Member data for user ID: ', OLD.id, ' has been backed up to b_mitglieder_deleted and y_deleted_user.'));
    END IF;
    
    -- Log successful backup before deletion
    INSERT INTO `sys_log` (`zeit`, `eintrag`)
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
        
        -- Log the error to the sys_log table
        INSERT INTO `sys_log` (`zeit`, `eintrag`) 
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
    INSERT INTO `sys_log` (`zeit`, `eintrag`)
    VALUES (NOW(), CONCAT('BSG with ID: ', OLD.id, ' (', OLD.BSG, ') will be deleted. Data backed up.'));
    
    -- Zähle die Mitglieder mit Stamm-BSG
    INSERT INTO `adm_usercount` (timestamp, Anzahl)
    SELECT NOW(), COUNT(*) FROM b_mitglieder WHERE BSG IS NOT NULL;

END //
DELIMITER ;

-- -----------------------------------------------------------------------------------

-- Feste temporäre Tabellen für Berechtigungen (ohne dynamisches SQL)
DROP PROCEDURE IF EXISTS create_temp_berechtigung_verband;
DELIMITER //
CREATE PROCEDURE create_temp_berechtigung_verband(IN uid INT)
BEGIN
    DROP TEMPORARY TABLE IF EXISTS temp_berechtigung_verband;
    CREATE TEMPORARY TABLE temp_berechtigung_verband (element_id INT PRIMARY KEY);
    
    INSERT IGNORE INTO temp_berechtigung_verband (element_id) 
    SELECT DISTINCT v.id 
    FROM b_regionalverband as v
    JOIN b_regionalverband_rechte as r on r.Verband = v.id 
    WHERE r.Nutzer = uid;
END //
DELIMITER ;

DROP PROCEDURE IF EXISTS create_temp_berechtigung_bsg;
DELIMITER //
CREATE PROCEDURE create_temp_berechtigung_bsg(IN uid INT)
BEGIN
    DROP TEMPORARY TABLE IF EXISTS temp_berechtigung_bsg;
    CREATE TEMPORARY TABLE temp_berechtigung_bsg (element_id INT PRIMARY KEY);
    
    INSERT IGNORE INTO temp_berechtigung_bsg (element_id) 
    SELECT DISTINCT b.id 
    FROM b_bsg as b
    LEFT JOIN b_bsg_rechte as br ON b.id = br.BSG
    JOIN y_user as y ON Nutzer = y.id
    WHERE y.id = uid;
END //
DELIMITER ;

DROP PROCEDURE IF EXISTS create_temp_berechtigung_sparte;
DELIMITER //
CREATE PROCEDURE create_temp_berechtigung_sparte(IN uid INT)
BEGIN
    DROP TEMPORARY TABLE IF EXISTS temp_berechtigung_sparte;
    CREATE TEMPORARY TABLE temp_berechtigung_sparte (element_id INT PRIMARY KEY);
    
    INSERT IGNORE INTO temp_berechtigung_sparte (element_id) 
    SELECT DISTINCT s.id 
    FROM b_sparte as s
    JOIN b_regionalverband_rechte r on s.Verband = r.Verband
    WHERE r.Nutzer = uid;
END //
DELIMITER ;

DROP PROCEDURE IF EXISTS create_temp_berechtigung_mitglied;
DELIMITER //
CREATE PROCEDURE create_temp_berechtigung_mitglied(IN uid INT)
BEGIN
    DROP TEMPORARY TABLE IF EXISTS temp_berechtigung_mitglied;
    CREATE TEMPORARY TABLE temp_berechtigung_mitglied (element_id INT PRIMARY KEY);
    
    -- Erst BSG und Verband Berechtigungen erstellen
    CALL create_temp_berechtigung_bsg(uid);
    CALL create_temp_berechtigung_verband(uid);
    
    INSERT IGNORE INTO temp_berechtigung_mitglied (element_id) 
    SELECT DISTINCT member_bsg.id 
    FROM (
        SELECT mis.Mitglied as id, mis.BSG as bsg
        FROM b_mitglieder_in_sparten as mis
        UNION
        SELECT m.id as id, m.BSG as bsg
        FROM b_mitglieder as m
    ) member_bsg
    JOIN b_bsg on b_bsg.id = member_bsg.bsg
    JOIN b_regionalverband as v on v.id = b_bsg.Verband
    WHERE EXISTS (SELECT 1 FROM temp_berechtigung_bsg WHERE element_id = member_bsg.bsg)
       OR EXISTS (SELECT 1 FROM temp_berechtigung_verband WHERE element_id = v.id);
END //
DELIMITER ;

DROP PROCEDURE IF EXISTS create_temp_berechtigung_individuelle_mitglieder;
DELIMITER //
CREATE PROCEDURE create_temp_berechtigung_individuelle_mitglieder(IN uid INT)
BEGIN
    DROP TEMPORARY TABLE IF EXISTS temp_berechtigung_individuelle_mitglieder;
    CREATE TEMPORARY TABLE temp_berechtigung_individuelle_mitglieder (element_id INT PRIMARY KEY);
    
    INSERT IGNORE INTO temp_berechtigung_individuelle_mitglieder (element_id) 
    SELECT DISTINCT ir.Mitglied 
    FROM b_bsg as b
    LEFT JOIN b_bsg_rechte as br ON b.id = br.BSG
    JOIN y_user as y ON Nutzer = y.id
    JOIN b_individuelle_berechtigungen as ir on b.id = ir.BSG 
    WHERE y.id = uid;
END //
DELIMITER ;

-- #####################################################################

DROP FUNCTION IF EXISTS berechtigte_elemente;
DELIMITER //

CREATE FUNCTION berechtigte_elemente(uid INT, target VARCHAR(50))
RETURNS TEXT
DETERMINISTIC
READS SQL DATA
BEGIN
    DECLARE result TEXT DEFAULT '';
    DECLARE current_id INT;
    DECLARE done INT DEFAULT FALSE;
    
    -- Cursor für verschiedene Targets
    DECLARE verband_cursor CURSOR FOR 
        SELECT DISTINCT v.id
        FROM b_regionalverband as v
        JOIN b_regionalverband_rechte as r on r.Verband = v.id 
        WHERE r.Nutzer = uid
        ORDER BY v.id;

    DECLARE verband_erweitert_cursor CURSOR FOR 
        SELECT DISTINCT v.id
        FROM b_regionalverband as v
        JOIN b_regionalverband_rechte as r on r.Verband = v.id 
        JOIN b___an_aus as aa on aa.id = r.erweiterte_Rechte
        WHERE r.Nutzer = uid and aa.bool > 0 
        ORDER BY v.id;
        
    DECLARE bsg_cursor CURSOR FOR 
        SELECT DISTINCT b.id
        FROM b_bsg as b
        LEFT JOIN b_bsg_rechte as br ON b.id = br.BSG
        JOIN y_user as y ON Nutzer = y.id
        WHERE y.id = uid
        ORDER BY b.id;
        
    DECLARE sparte_cursor CURSOR FOR 
        SELECT DISTINCT s.id
        FROM b_sparte as s
        JOIN b_regionalverband_rechte r on s.Verband = r.Verband
        WHERE r.Nutzer = uid
        ORDER BY s.id;
        
    DECLARE mitglied_cursor CURSOR FOR 
        SELECT DISTINCT member_bsg.id
        FROM (
            SELECT mis.Mitglied as id, mis.BSG as bsg
            FROM b_mitglieder_in_sparten as mis
            UNION
            SELECT m.id as id, m.BSG as bsg
            FROM b_mitglieder as m
        ) member_bsg
        JOIN b_bsg on b_bsg.id = member_bsg.bsg
        JOIN b_regionalverband as v on v.id = b_bsg.Verband
        WHERE EXISTS (
            SELECT 1 FROM b_bsg as b2
            LEFT JOIN b_bsg_rechte as br2 ON b2.id = br2.BSG
            JOIN y_user as y2 ON Nutzer = y2.id
            WHERE y2.id = uid AND b2.id = member_bsg.bsg
        )
        OR EXISTS (
            SELECT 1 FROM b_regionalverband as v2
            JOIN b_regionalverband_rechte as r2 on r2.Verband = v2.id 
            WHERE r2.Nutzer = uid AND v2.id = v.id
        )
        ORDER BY member_bsg.id;
        
    DECLARE individuelle_cursor CURSOR FOR 
        SELECT DISTINCT ir.Mitglied
        FROM b_bsg as b
        LEFT JOIN b_bsg_rechte as br ON b.id = br.BSG
        JOIN y_user as y ON Nutzer = y.id
        JOIN b_individuelle_berechtigungen as ir on b.id = ir.BSG 
        WHERE y.id = uid
        ORDER BY ir.Mitglied;
    
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;
    
    CASE target
        WHEN 'verband' THEN
            OPEN verband_cursor;
            read_loop: LOOP
                FETCH verband_cursor INTO current_id;
                IF done THEN LEAVE read_loop; END IF;
                
                IF result = '' THEN
                    SET result = CAST(current_id AS CHAR);
                ELSE
                    SET result = CONCAT(result, ',', current_id);
                END IF;
            END LOOP;
            CLOSE verband_cursor;

        WHEN 'verband_erweitert' THEN
            OPEN verband_erweitert_cursor;
            read_loop: LOOP
                FETCH verband_erweitert_cursor INTO current_id;
                IF done THEN LEAVE read_loop; END IF;
                
                IF result = '' THEN
                    SET result = CAST(current_id AS CHAR);
                ELSE
                    SET result = CONCAT(result, ',', current_id);
                END IF;
            END LOOP;
            CLOSE verband_erweitert_cursor;
            
        WHEN 'bsg' THEN
            OPEN bsg_cursor;
            read_loop: LOOP
                FETCH bsg_cursor INTO current_id;
                IF done THEN LEAVE read_loop; END IF;
                
                IF result = '' THEN
                    SET result = CAST(current_id AS CHAR);
                ELSE
                    SET result = CONCAT(result, ',', current_id);
                END IF;
            END LOOP;
            CLOSE bsg_cursor;
            
        WHEN 'sparte' THEN
            OPEN sparte_cursor;
            read_loop: LOOP
                FETCH sparte_cursor INTO current_id;
                IF done THEN LEAVE read_loop; END IF;
                
                IF result = '' THEN
                    SET result = CAST(current_id AS CHAR);
                ELSE
                    SET result = CONCAT(result, ',', current_id);
                END IF;
            END LOOP;
            CLOSE sparte_cursor;
            
        WHEN 'mitglied' THEN
            OPEN mitglied_cursor;
            read_loop: LOOP
                FETCH mitglied_cursor INTO current_id;
                IF done THEN LEAVE read_loop; END IF;
                
                IF result = '' THEN
                    SET result = CAST(current_id AS CHAR);
                ELSE
                    SET result = CONCAT(result, ',', current_id);
                END IF;
            END LOOP;
            CLOSE mitglied_cursor;
            
        WHEN 'individuelle_mitglieder' THEN
            OPEN individuelle_cursor;
            read_loop: LOOP
                FETCH individuelle_cursor INTO current_id;
                IF done THEN LEAVE read_loop; END IF;
                
                IF result = '' THEN
                    SET result = CAST(current_id AS CHAR);
                ELSE
                    SET result = CONCAT(result, ',', current_id);
                END IF;
            END LOOP;
            CLOSE individuelle_cursor;
            
        ELSE
            SET result = '';
    END CASE;
    
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

-- Verbesserte Sub-Funktion ohne dynamisches SQL
DROP FUNCTION IF EXISTS berechtigte_elemente_sub1;
DELIMITER //

CREATE FUNCTION berechtigte_elemente_sub1(uid INT, target VARCHAR(50))
RETURNS TEXT
DETERMINISTIC
READS SQL DATA
BEGIN
    -- Diese Funktion ruft einfach die Hauptfunktion auf
    RETURN berechtigte_elemente(uid, target);
END //

DELIMITER ;


-- ------------------------------------------------------------
-- v0.1.7
-- ------------------------------------------------------------

DROP TRIGGER IF EXISTS trg_b_mitglieder_update_historie;
DROP TRIGGER IF EXISTS trg_b_mitglieder_update;
DELIMITER $$
CREATE TRIGGER trg_b_mitglieder_update
AFTER UPDATE ON b_mitglieder
FOR EACH ROW
BEGIN
    DECLARE alter_wert VARCHAR(100);
    DECLARE neuer_wert VARCHAR(100);
    DECLARE cnt INT DEFAULT 0;

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

        -- Prüfe, ob die Kombination Mitglied und BSG schon existiert und trage dann die BSG in die indiv. Mitgliederberechtigungen
        SELECT COUNT(*) INTO cnt
        FROM b_individuelle_berechtigungen
        WHERE Mitglied = NEW.id AND BSG = NEW.BSG;
        IF cnt = 0 THEN
            INSERT INTO b_individuelle_berechtigungen (Mitglied, BSG)
            VALUES (NEW.id, NEW.BSG);
        END IF;
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

    IF NOT (OLD.y_id <=> NEW.y_id) THEN
        INSERT INTO b_mitglieder_historie (MNr, Aktion)
        VALUES (OLD.id, CONCAT('Änderung y_id von ''', IFNULL(OLD.y_id, ''), ''' zu ''', IFNULL(NEW.y_id, ''), ''''));
    END IF;

END$$

DELIMITER ;

/*
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

    IF NOT (OLD.y_id <=> NEW.y_id) THEN
        INSERT INTO b_mitglieder_historie (MNr, Aktion)
        VALUES (OLD.id, CONCAT('Änderung y_id von ''', IFNULL(OLD.y_id, ''), ''' zu ''', IFNULL(NEW.y_id, ''), ''''));
    END IF;

END$$

DELIMITER ;
*/
-- -----------------------------------------------------------------------------------

-- Trigger für Anmeldung in einer Sparte
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



/*
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
*/
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

DROP TRIGGER IF EXISTS tr_before_delete_individuelle_berechtigungen;

DELIMITER //

CREATE TRIGGER tr_before_delete_individuelle_berechtigungen
BEFORE DELETE ON b_individuelle_berechtigungen
FOR EACH ROW
BEGIN
    DECLARE v_count_mitglieder_in_sparten INT;
    DECLARE v_count_mitglieder INT;
    DECLARE v_count_wechselantrag INT;
    
    -- Prüfung 1: Ist das Mitglied noch über diese BSG in einer Sparte angemeldet?
    SELECT COUNT(*) INTO v_count_mitglieder_in_sparten
    FROM b_mitglieder_in_sparten
    WHERE Mitglied = OLD.Mitglied AND BSG = OLD.BSG;
    
    -- Prüfung 2: Ist das Mitglied noch in dieser BSG angemeldet?
    SELECT COUNT(*) INTO v_count_mitglieder
    FROM b_mitglieder
    WHERE id = OLD.Mitglied AND BSG = OLD.BSG;
    
    -- Prüfung 3: Liegt ein Wechselantrag für dieses Mitglied zu dieser BSG vor?
    SELECT COUNT(*) INTO v_count_wechselantrag
    FROM b_bsg_wechselantrag
    WHERE m_id = OLD.Mitglied AND Ziel_BSG = OLD.BSG;
    
    -- Prüfung aller Fälle
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
    ELSEIF v_count_wechselantrag > 0 THEN
        -- Fall 4: Wechselantrag liegt vor (eigenständiger Fall, keine Kombination)
        SIGNAL SQLSTATE '45000' 
        SET MESSAGE_TEXT = '_WECHSELBSG_ Ein Wechselantrag zu dieser BSG liegt vor';
    END IF;
END //

DELIMITER ;
