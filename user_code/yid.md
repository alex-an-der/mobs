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
        
        -- Zähle die Mitglieder mit Stamm-BSG
        INSERT INTO adm_usercount (timestamp, Anzahl)
        SELECT NOW(), COUNT(*) FROM b_mitglieder WHERE BSG IS NOT NULL;
    END IF;
END



# Mitgliederkonten zusammenführen mit Soft-Delete-Strategie

Dieses Dokument beschreibt, wie du in deinem System Mitgliederkonten zusammenführen kannst, indem du einen Soft-Delete-Mechanismus verwendest. Ziel ist es, dass beim Versuch, eine bereits vergebene `y_id` einem anderen Mitglied zuzuweisen, das alte Konto als gelöscht markiert wird, ohne es physisch zu entfernen.

---

## 1. Spalte für Soft-Delete anlegen

Füge in der Tabelle `b_mitglieder` eine Spalte hinzu, die anzeigt, ob ein Datensatz als gelöscht markiert ist:

```sql
ALTER TABLE b_mitglieder ADD COLUMN `deleted` TINYINT(1) NOT NULL DEFAULT 0;
```

---

## 2. Trigger zum Zusammenführen der Konten

Lege einen Trigger an, der beim Update der `y_id` prüft, ob diese bereits vergeben ist. Falls ja, wird der alte Datensatz auf `deleted=1` gesetzt:

```sql
DELIMITER //

CREATE TRIGGER trg_before_update_y_id
BEFORE UPDATE ON b_mitglieder
FOR EACH ROW
BEGIN
    IF NEW.y_id IS NOT NULL AND NEW.y_id <> OLD.y_id THEN
        UPDATE b_mitglieder
        SET deleted = 1
        WHERE y_id = NEW.y_id AND id <> OLD.id AND deleted = 0;
    END IF;
END;
//

DELIMITER ;
```

---

## 3. Abfragen anpassen

Passe alle SELECT-Statements in deinem Code an, sodass nur nicht-gelöschte Mitglieder angezeigt werden.  
Füge überall, wo du auf `b_mitglieder` zugreifst, die Bedingung `AND deleted = 0` hinzu.

**Beispiel:**
```sql
SELECT * FROM b_mitglieder WHERE deleted = 0;
```

In deinem PHP-Array (z.B. in `lvl_50_bsg.php`):

```php
"query" => "SELECT 
    m.id as id, 
    m.y_id, 
    concat(Vorname, ' ', Nachname) as info:Name,  
    DATE_FORMAT(m.Geburtsdatum, '%d.%m.%Y') as info:Geburtsdatum
FROM b_mitglieder as m
WHERE 
    deleted = 0
    AND (
        FIND_IN_SET(BSG, berechtigte_elemente($uid, 'BSG')) > 0 or 
        (BSG IS NULL AND FIND_IN_SET(m.id, berechtigte_elemente($uid, 'individuelle_mitglieder')) > 0)
    )
    AND m.BSG IS NOT NULL
ORDER by BSG, Vorname desc;"
```

---

## 4. Optional: Physisches Löschen per Event

Wenn du die als gelöscht markierten Datensätze regelmäßig endgültig entfernen möchtest, kannst du einen MySQL-Event einrichten:

```sql
SET GLOBAL event_scheduler = ON;

CREATE EVENT IF NOT EXISTS delete_marked_mitglieder
ON SCHEDULE EVERY 1 DAY STARTS '2025-04-29 02:00:00'
DO
  DELETE FROM b_mitglieder WHERE deleted = 1;
```

---

## 5. Hinweise

- **Referenzielle Integrität:** Da der Datensatz nicht sofort gelöscht wird, bleiben Foreign Keys erhalten. Prüfe ggf. abhängige Tabellen.
- **Wiederherstellung:** Du kannst versehentlich gelöschte Konten wiederherstellen, indem du `deleted` auf `0` setzt.
- **Performance:** Ein Index auf `deleted` kann Abfragen beschleunigen:
  ```sql
  CREATE INDEX idx_b_mitglieder_deleted ON b_mitglieder(deleted);
  ```

---

## 6. Zusammenfassung

Mit dieser Methode kannst du Mitgliederkonten zusammenführen, ohne Daten zu verlieren oder Foreign-Key-Probleme zu riskieren.  
Alle Änderungen erfolgen rein auf SQL-Ebene, ohne Anpassungen am PHP-Code.

---
```# Mitgliederkonten zusammenführen mit Soft-Delete-Strategie

Dieses Dokument beschreibt, wie du in deinem System Mitgliederkonten zusammenführen kannst, indem du einen Soft-Delete-Mechanismus verwendest. Ziel ist es, dass beim Versuch, eine bereits vergebene `y_id` einem anderen Mitglied zuzuweisen, das alte Konto als gelöscht markiert wird, ohne es physisch zu entfernen.

---

## 1. Spalte für Soft-Delete anlegen

Füge in der Tabelle `b_mitglieder` eine Spalte hinzu, die anzeigt, ob ein Datensatz als gelöscht markiert ist:

```sql
ALTER TABLE b_mitglieder ADD COLUMN `deleted` TINYINT(1) NOT NULL DEFAULT 0;
```

---

## 2. Trigger zum Zusammenführen der Konten

Lege einen Trigger an, der beim Update der `y_id` prüft, ob diese bereits vergeben ist. Falls ja, wird der alte Datensatz auf `deleted=1` gesetzt:

```sql
DELIMITER //

CREATE TRIGGER trg_before_update_y_id
BEFORE UPDATE ON b_mitglieder
FOR EACH ROW
BEGIN
    IF NEW.y_id IS NOT NULL AND NEW.y_id <> OLD.y_id THEN
        UPDATE b_mitglieder
        SET deleted = 1
        WHERE y_id = NEW.y_id AND id <> OLD.id AND deleted = 0;
    END IF;
END;
//

DELIMITER ;
```

---

## 3. Abfragen anpassen

Passe alle SELECT-Statements in deinem Code an, sodass nur nicht-gelöschte Mitglieder angezeigt werden.  
Füge überall, wo du auf `b_mitglieder` zugreifst, die Bedingung `AND deleted = 0` hinzu.

**Beispiel:**
```sql
SELECT * FROM b_mitglieder WHERE deleted = 0;
```

In deinem PHP-Array (z.B. in `lvl_50_bsg.php`):

```php
"query" => "SELECT 
    m.id as id, 
    m.y_id, 
    concat(Vorname, ' ', Nachname) as info:Name,  
    DATE_FORMAT(m.Geburtsdatum, '%d.%m.%Y') as info:Geburtsdatum
FROM b_mitglieder as m
WHERE 
    deleted = 0
    AND (
        FIND_IN_SET(BSG, berechtigte_elemente($uid, 'BSG')) > 0 or 
        (BSG IS NULL AND FIND_IN_SET(m.id, berechtigte_elemente($uid, 'individuelle_mitglieder')) > 0)
    )
    AND m.BSG IS NOT NULL
ORDER by BSG, Vorname desc;"
```

---

## 4. Optional: Physisches Löschen per Event

Wenn du die als gelöscht markierten Datensätze regelmäßig endgültig entfernen möchtest, kannst du einen MySQL-Event einrichten:

```sql
SET GLOBAL event_scheduler = ON;

CREATE EVENT IF NOT EXISTS delete_marked_mitglieder
ON SCHEDULE EVERY 1 DAY STARTS '2025-04-29 02:00:00'
DO
  DELETE FROM b_mitglieder WHERE deleted = 1;
```

---

## 5. Hinweise

- **Referenzielle Integrität:** Da der Datensatz nicht sofort gelöscht wird, bleiben Foreign Keys erhalten. Prüfe ggf. abhängige Tabellen.
- **Wiederherstellung:** Du kannst versehentlich gelöschte Konten wiederherstellen, indem du `deleted` auf `0` setzt.
- **Performance:** Ein Index auf `deleted` kann Abfragen beschleunigen:
  ```sql
  CREATE INDEX idx_b_mitglieder_deleted ON b_mitglieder(deleted);
  ```

---

## 6. Zusammenfassung

Mit dieser Methode kannst du Mitgliederkonten zusammenführen, ohne Daten zu verlieren oder Foreign-Key-Probleme zu riskieren.  
Alle Änderungen erfolgen rein auf SQL-Ebene, ohne Anpassungen am PHP-Code.

---