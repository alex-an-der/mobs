/*DROP TRIGGER IF EXISTS tr_user_first_login;
DELIMITER //
CREATE TRIGGER tr_user_first_login 
AFTER UPDATE ON y_user
FOR EACH ROW
BEGIN
    IF OLD.lastlogin IS NULL AND NEW.lastlogin IS NOT NULL AND NEW.run_trigger = 1 THEN
        INSERT INTO b_mitglieder (y_id, Mail, Vorname, Nachname)
        SELECT 
            NEW.id,
            NEW.mail,
            ud.vname,
            ud.nname
        FROM y_v_userdata ud
        WHERE ud.userID = NEW.id;
    END IF;
END;//
DELIMITER ;*/

DROP TRIGGER IF EXISTS tr_user_first_login;
DELIMITER //
CREATE TRIGGER tr_user_first_login 
AFTER UPDATE ON y_user
FOR EACH ROW
BEGIN
    IF OLD.lastlogin IS NULL AND NEW.lastlogin IS NOT NULL AND NEW.run_trigger = 1 THEN
        INSERT INTO b_mitglieder (y_id, Mail, Vorname, Nachname, Geschlecht, Geburtsdatum)
            SELECT 
                NEW.id,
                NEW.mail,
                ud.vname,
                ud.nname,
                (select fieldvalue
                from y_user_details as d
                join y_user_fields as f on d.fieldID = f.ID 
                WHERE userID = NEW.id and fieldname = 'geschlecht'),
                (select fieldvalue
                from y_user_details as d
                join y_user_fields as f on d.fieldID = f.ID 
                WHERE userID = NEW.id and fieldname = 'gebdatum')
            FROM y_v_userdata ud
            WHERE ud.userID = NEW.id;
    END IF;
END;//
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
        -- WHEN 'stammmitglied' THEN (
        --     SELECT GROUP_CONCAT(DISTINCT ID)
        --     FROM (
        --         SELECT member_bsg.id as ID, member_bsg.bsg as BSG, v.id as Verband 
        --         FROM(
        --             SELECT m.id as id, m.BSG as bsg
        --             FROM b_mitglieder as m
        --         ) member_bsg
        --         JOIN b_bsg on b_bsg.id = member_bsg.bsg
        --         JOIN b_regionalverband as v on v.id = b_bsg.Verband
        --     ) b_und_v
        --     WHERE (FIND_IN_SET(b_und_v.BSG, berechtigte_elemente_sub1(uid, 'BSG')) > 0) 
        --     OR (FIND_IN_SET(b_und_v.Verband, berechtigte_elemente_sub1(uid, 'verband')) > 0)
        -- )
        ELSE ''
    END INTO result;
    
    RETURN COALESCE(result, '');
END //

DELIMITER ;



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
        -- WHEN 'stammmitglied' THEN (
        --     SELECT GROUP_CONCAT(DISTINCT ID)
        --     FROM (
        --         SELECT member_bsg.id as ID, member_bsg.bsg as BSG, v.id as Verband 
        --         FROM(
        --             SELECT m.id as id, m.BSG as bsg
        --             FROM b_mitglieder as m
        --         ) member_bsg
        --         JOIN b_bsg on b_bsg.id = member_bsg.bsg
        --         JOIN b_regionalverband as v on v.id = b_bsg.Verband
        --     ) b_und_v
        --     WHERE (FIND_IN_SET(b_und_v.BSG, berechtigte_elemente_sub1(uid, 'BSG')) > 0) 
        --     OR (FIND_IN_SET(b_und_v.Verband, berechtigte_elemente_sub1(uid, 'verband')) > 0)
        -- )
        ELSE ''
    END INTO result;
    
    RETURN COALESCE(result, '');
END //

DELIMITER ;
