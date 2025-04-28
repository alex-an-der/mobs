/*
DROP TRIGGER IF EXISTS tr_user_first_login;
DELIMITER //
CREATE TRIGGER tr_user_first_login 
AFTER UPDATE ON y_user
FOR EACH ROW
BEGIN
    DECLARE new_member_id BIGINT;
    DECLARE bsg_value INT;
    
    -- Prüfe Bedingungen für den Trigger
    IF OLD.lastlogin IS NULL AND NEW.lastlogin IS NOT NULL AND NEW.run_trigger = 1 THEN
        -- Füge Mitglied in b_mitglieder ein
        INSERT INTO b_mitglieder (y_id, Mail, Vorname, Nachname, Geschlecht, Geburtsdatum, Mailbenachrichtigung, BSG)
            SELECT 
                NEW.id,
                NEW.mail,
                ud.vname,
                ud.nname,
                (SELECT fieldvalue
                FROM y_user_details AS d
                JOIN y_user_fields AS f ON d.fieldID = f.ID 
                WHERE userID = NEW.id AND fieldname = 'geschlecht'),
                (SELECT fieldvalue
                FROM y_user_details AS d
                JOIN y_user_fields AS f ON d.fieldID = f.ID 
                WHERE userID = NEW.id AND fieldname = 'gebdatum'),
                (SELECT fieldvalue
                FROM y_user_details AS d
                JOIN y_user_fields AS f ON d.fieldID = f.ID 
                WHERE userID = NEW.id AND fieldname = 'okformail'),
                (SELECT fieldvalue
                FROM y_user_details AS d
                JOIN y_user_fields AS f ON d.fieldID = f.ID 
                WHERE userID = NEW.id AND fieldname = 'bsg')
            FROM y_v_userdata AS ud
            WHERE ud.userID = NEW.id;
            
        -- Speichere die ID des neu erstellten Mitglieds
        SET new_member_id = LAST_INSERT_ID();
        
        -- Hole den BSG-Wert aus den Benutzerdetails
        SELECT CAST(fieldvalue AS UNSIGNED) INTO bsg_value
        FROM y_user_details AS d
        JOIN y_user_fields AS f ON d.fieldID = f.ID 
        WHERE userID = NEW.id AND fieldname = 'bsg';
        
        -- Füge den Datensatz in b_individuelle_berechtigungen ein, wenn BSG-Wert existiert
        IF bsg_value IS NOT NULL THEN
            INSERT INTO b_individuelle_berechtigungen (Mitglied, BSG)
            VALUES (new_member_id, bsg_value);
        END IF;
        
        -- Zähle die Mitglieder mit Stamm-BSG
        INSERT INTO `adm_usercount` (timestamp, Anzahl)
        SELECT NOW(), COUNT(*) FROM b_mitglieder WHERE BSG IS NOT NULL;
    END IF;
END;//
DELIMITER ;
*/



/*
DROP TRIGGER IF EXISTS tr_user_first_login;
DELIMITER //
CREATE TRIGGER tr_user_first_login 
AFTER UPDATE ON y_user
FOR EACH ROW
BEGIN
    DECLARE new_member_id BIGINT;
    DECLARE bsg_value INT;
    
    -- Prüfe Bedingungen für den Trigger
    IF OLD.lastlogin IS NULL AND NEW.lastlogin IS NOT NULL AND NEW.run_trigger = 1 THEN
        -- Füge Mitglied in b_mitglieder ein
        INSERT INTO b_mitglieder (y_id, Mail, Vorname, Nachname, Geschlecht, Geburtsdatum, Mailbenachrichtigung, BSG)
            SELECT 
                NEW.id,
                NEW.mail,
                ud.vname,
                ud.nname,
                (SELECT fieldvalue
                FROM y_user_details AS d
                JOIN y_user_fields AS f ON d.fieldID = f.ID 
                WHERE userID = NEW.id AND fieldname = 'geschlecht'),
                (SELECT fieldvalue
                FROM y_user_details AS d
                JOIN y_user_fields AS f ON d.fieldID = f.ID 
                WHERE userID = NEW.id AND fieldname = 'gebdatum'),
                (SELECT fieldvalue
                FROM y_user_details AS d
                JOIN y_user_fields AS f ON d.fieldID = f.ID 
                WHERE userID = NEW.id AND fieldname = 'okformail'),
                (SELECT fieldvalue
                FROM y_user_details AS d
                JOIN y_user_fields AS f ON d.fieldID = f.ID 
                WHERE userID = NEW.id AND fieldname = 'bsg')
            FROM y_v_userdata AS ud
            WHERE ud.userID = NEW.id;
            
        -- Speichere die ID des neu erstellten Mitglieds
        SET new_member_id = LAST_INSERT_ID();
        
        -- Hole den BSG-Wert aus den Benutzerdetails
        SELECT CAST(fieldvalue AS UNSIGNED) INTO bsg_value
        FROM y_user_details AS d
        JOIN y_user_fields AS f ON d.fieldID = f.ID 
        WHERE userID = NEW.id AND fieldname = 'bsg';
        
        
        -- Füge den Datensatz in b_individuelle_berechtigungen ein, wenn BSG-Wert existiert
        IF bsg_value IS NOT NULL THEN
            INSERT INTO b_individuelle_berechtigungen (Mitglied, BSG)
            VALUES (new_member_id, bsg_value);
        END IF;
        
        -- Füge einen Wechelantrag hinzu (einer muss immer existieren)
        IF bsg_value IS NOT NULL THEN
            INSERT INTO b_bsg_wechselantrag (m_id, Ziel_BSG)
            VALUES (new_member_id, bsg_value);
        END IF;

        -- Zähle die Mitglieder mit Stamm-BSG
        INSERT INTO `adm_usercount` (timestamp, Anzahl)
        SELECT NOW(), COUNT(*) FROM b_mitglieder WHERE BSG IS NOT NULL;
    END IF;
END;//
DELIMITER ;
*/

-- Aaron@Habitus.lo

DROP TRIGGER IF EXISTS tr_user_first_login;
DELIMITER //
CREATE TRIGGER tr_user_first_login 
AFTER UPDATE ON y_user
FOR EACH ROW
BEGIN
    DECLARE new_member_id BIGINT;
    DECLARE bsg_value INT;
    
    -- Prüfe Bedingungen für den Trigger
    IF OLD.lastlogin IS NULL AND NEW.lastlogin IS NOT NULL AND NEW.run_trigger = 1 THEN
        -- Füge Mitglied in b_mitglieder ein
        INSERT INTO b_mitglieder (y_id, Mail, Vorname, Nachname, Geschlecht, Geburtsdatum, Mailbenachrichtigung)
            SELECT 
                NEW.id,
                NEW.mail,
                ud.vname,
                ud.nname,
                (SELECT fieldvalue
                FROM y_user_details AS d
                JOIN y_user_fields AS f ON d.fieldID = f.ID 
                WHERE userID = NEW.id AND fieldname = 'geschlecht'),
                (SELECT fieldvalue
                FROM y_user_details AS d
                JOIN y_user_fields AS f ON d.fieldID = f.ID 
                WHERE userID = NEW.id AND fieldname = 'gebdatum'),
                (SELECT fieldvalue
                FROM y_user_details AS d
                JOIN y_user_fields AS f ON d.fieldID = f.ID 
                WHERE userID = NEW.id AND fieldname = 'okformail')
                
            FROM y_v_userdata AS ud
            WHERE ud.userID = NEW.id;
            
        -- Speichere die ID des neu erstellten Mitglieds
        SET new_member_id = LAST_INSERT_ID();
        
        -- Hole den BSG-Wert aus den Benutzerdetails
        SELECT CAST(fieldvalue AS UNSIGNED) INTO bsg_value
        FROM y_user_details AS d
        JOIN y_user_fields AS f ON d.fieldID = f.ID 
        WHERE userID = NEW.id AND fieldname = 'bsg';
        
        
        -- Füge den Datensatz in b_individuelle_berechtigungen ein, wenn BSG-Wert existiert
        IF bsg_value IS NOT NULL THEN
            INSERT INTO b_individuelle_berechtigungen (Mitglied, BSG)
            VALUES (new_member_id, bsg_value);
        END IF;
        
        -- Füge einen Wechelantrag hinzu (einer muss immer existieren)
        IF bsg_value IS NOT NULL THEN
            INSERT INTO b_bsg_wechselantrag (m_id, Ziel_BSG)
            VALUES (new_member_id, bsg_value);
        END IF;

        -- Zähle die Mitglieder mit Stamm-BSG
        INSERT INTO `adm_usercount` (timestamp, Anzahl)
        SELECT NOW(), COUNT(*) FROM b_mitglieder WHERE BSG IS NOT NULL;
    END IF;
END;//
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
