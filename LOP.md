# Rechnungserstellung
- Rechnungserzeugung? PDF??
- Rechnungen können in der Cloud abgelegt werden - Link kann gespeichert werden

## Nächste Schritte


## In der Prod-DB einfügen 
aaaa@a.a
```
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
                
            FROM y_v_user_details AS ud
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
END
```