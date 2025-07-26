# Konfigurationsdokumentation

## 1. Stage-Konfiguration

Die Stage-Konfiguration erfolgt über die Variable `$DEBUG` in der `config.php`:

```php
$DEBUG = 1;
```

Bei aktiviertem Debug-Modus (`$DEBUG = 1`) werden PHP-Fehlermeldungen aktiviert:
- `display_errors` = '1'
- `display_startup_errors` = '1'
- `error_reporting` = E_ALL

## 2. Konstanten und ihre Bedeutung

| Konstante | Standardwert | Beschreibung |
|-----------|--------------|--------------|
| `TITEL` | "LBSV Nds. Mitgliederverwaltung" | Titel der Anwendung |
| `PLEASE_CHOOSE` | "Bitte auswählen..." | Text für leere Auswahlfelder |
| `NULL_WERT` | "---" | Anzeige für NULL-Werte in der Datenbank |
| `NULL_BUT_NOT_NULLABLE` | "Diese Liste ist noch leer. Bitte zunächst Auswahlmöglichkeiten eintragen." | Fehlermeldung für leere Pflicht-Auswahllisten |
| `DB_ERROR` | "Die Datenbank kann die Daten so nicht speichern..." | Fehlermeldung bei Datenbankfehlern (enthält Platzhalter #FEHLERID#) |
| `SRV_ERROR` | "Es kam zu einen allgemeinen Fehler..." | Fehlermeldung bei Serverfehlern (enthält Platzhalter #FEHLERID#) |

## 3. Das $anzuzeigendeDaten Array

Das `$anzuzeigendeDaten` Array ist das Herzstück der Anwendungskonfiguration. Es definiert alle anzeigbaren Datentabellen und ihre Eigenschaften.

### 3.1 Grundstruktur

```php
$anzuzeigendeDaten[] = array(
    "tabellenname" => "tabellen_name",
    "auswahltext" => "Beschreibender Text für die Auswahl",
    "query" => "SELECT ...",
    // Weitere optionale Felder...
);
```

### 3.2 Pflichtfelder

| Feld | Typ | Beschreibung |
|------|-----|--------------|
| `tabellenname` | String | Name der Datenbanktabelle |
| `auswahltext` | String | Angezeigter Text in der Tabellenauswahl |
| `query` | String | SQL-Query zum Laden der Daten |

### 3.3 Optionale Felder

| Feld | Typ | Standard | Beschreibung |
|------|-----|----------|--------------|
| `writeaccess` | Boolean | `false` | Berechtigung zum Bearbeiten der Daten |
| `import` | Boolean | `false` | Möglichkeit zum Datenimport aktivieren |
| `deleteanyway` | Boolean | `false` | Löschen auch bei referenzierten Datensätzen erlauben |
| `hinweis` | String | - | Informationstext für den Benutzer (HTML möglich) |
| `spaltenbreiten` | Array | - | Breite der Tabellenspalten in Pixeln |
| `referenzqueries` | Array | - | Queries für Fremdschlüssel-Auswahlfelder |
| `suchqueries` | Array | - | Erweiterte Suchqueries für komplexe Auswahlfelder |
| `ajaxfile` | String | - | PHP-Datei für AJAX-Operationen |

### 3.4 Spezielle Spaltentypen

#### Info-Spalten (`info:`)
```php
"query" => "SELECT id, name as 'info:Name', ..."
```
- Prefix `info:` markiert Spalten als nicht bearbeitbar
- Werden nur zur Anzeige verwendet
- Hilfreich für berechnete Werte oder Referenzinformationen

#### AJAX-Spalten (`ajax:`)
```php
"query" => "SELECT id, field as 'ajax:field', ..."
"ajaxfile" => "custom_ajax_handler.php"
```
- Prefix `ajax:` markiert Spalten für AJAX-Bearbeitung
- Erfordert entsprechende `ajaxfile` Konfiguration
- Ermöglicht komplexe Bearbeitungslogik

### 3.5 Referenzqueries

Definieren Auswahlmöglichkeiten für Fremdschlüssel:

```php
"referenzqueries" => array(
    "spaltenname" => "SELECT id, anzeige_text as anzeige FROM referenz_tabelle ORDER BY anzeige;",
    "andere_spalte" => "SELECT id, beschreibung as anzeige FROM andere_tabelle;"
)
```

**Wichtige Regeln:**
- Muss immer `id` und `anzeige` Spalten zurückgeben
- `id` = Wert der in der Datenbank gespeichert wird
- `anzeige` = Text der dem Benutzer angezeigt wird

### 3.6 Suchqueries

Erweiterte Suche für komplexe Auswahlfelder (hauptsächlich im Import verwendet):

```php
"suchqueries" => array(
    "spaltenname" => "SELECT id, name, zusatz_info FROM tabelle WHERE bedingung"
)
```

- Ermöglicht mehrspaltige Suchergebnisse
- Unterstützt komplexere Suchlogik als Referenzqueries

### 3.7 Spaltenbreiten

```php
"spaltenbreiten" => array(
    "spalte1" => "200",
    "spalte2" => "150",
    "info:anzeige_spalte" => "300"
)
```

- Angaben in Pixeln als String
- Auch für `info:` und `ajax:` Spalten möglich

### 3.8 Trenner

Zur visuellen Gruppierung der Tabellenauswahl:

```php
$anzuzeigendeDaten[] = array("trenner" => "-");
```

### 3.9 Vollständiges Beispiel

```php
$anzuzeigendeDaten[] = array(
    "tabellenname" => "b_mitglieder",
    "auswahltext" => "Mitglieder verwalten",
    "writeaccess" => true,
    "import" => true,
    "hinweis" => "Hier können <b>Mitgliederdaten</b> bearbeitet werden.",
    "query" => "SELECT 
                    m.id as id, 
                    m.id as 'info:Nr',
                    m.Vorname, 
                    m.Nachname, 
                    m.BSG,
                    m.aktiv
                FROM b_mitglieder as m 
                WHERE berechtigung_pruefen(m.id)
                ORDER BY m.Nachname, m.Vorname",
    "referenzqueries" => array(
        "BSG" => "SELECT id, name as anzeige FROM b_bsg ORDER BY name",
        "aktiv" => "SELECT id, wert as anzeige FROM b___an_aus"
    ),
    "spaltenbreiten" => array(
        "info:Nr" => "50",
        "Vorname" => "150",
        "Nachname" => "150", 
        "BSG" => "300",
        "aktiv" => "100"
    )
);
```

## 4. Einschränkungen und Besonderheiten

### 4.1 SQL-Query Anforderungen
- Erste Spalte muss immer `id` heißen (Primärschlüssel)
- Bei `writeaccess=true`: Tabelle muss UPDATE/INSERT/DELETE unterstützen
- Bei Verwendung von JOINs: Primärtabelle muss eindeutig identifizierbar sein

### 4.2 Berechtigung und Sicherheit
- Queries sollten immer Berechtigungsprüfungen enthalten
- Verwendung von `berechtigte_elemente()` Funktion empfohlen
- Nutzervariable `$uid` steht zur Verfügung

### 4.3 Import-Funktionalität
- Nur verfügbar wenn `import=true`
- Benötigt konfigurierte `referenzqueries` oder `suchqueries`
- Unterstützt CSV-Import mit automatischer Feldzuordnung

### 4.4 Datentypen und Validierung
- Automatische Erkennung von Fremdschlüsseln durch `referenzqueries`
- Datums-/Zeitfelder werden automatisch erkannt
- Boolesche Werte werden als Auswahllisten dargestellt

---

## Anhang: Modifikationen für MOBS24

Die folgenden Komponenten wurden aus der Dokumentation ausgenommen, da sie MOBS24-spezifische Erweiterungen sind:

### YPUM-Komponenten (Benutzerrechte-System)
- `$ypum->isBerechtigt()` Aufrufe
- `$uid` Benutzervariable aus Session
- `berechtigte_elemente()` Datenbankfunktion
- Rollenbasierte Konfigurationsdateien (`lvl_*.php`)

### Spezifische Variablen
- `$salden` Array für Finanzauswertungen
- `$mitgliederauswahl` Query für Mitgliederauswahl
- `$mitgliederconcat` String für Mitgliedernamen-Formatierung
- `$bericht` Prefix für Berichts-Tabellen

### Dynamische Konfiguration
- Bedingte Include-Anweisungen basierend auf Benutzerrechten
- Mehrschichtige Konfigurationsdateien je Berechtigungsebene
- Automatische Rechteverwaltung und -übersicht

### MOBS24-spezifische Datenbankstrukturen
- Alle `b_*` Tabellen (LBSV-spezifisch)
- Verbands- und BSG-Strukturen
- Sparten- und Mitgliederverwaltung

Diese Komponenten sind vollständig in das System integriert, gehören aber nicht zur Standard-Konfiguration einer generischen Tabellenverwaltung.
