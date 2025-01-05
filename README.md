# D.U.M.M. - Datenbasierter universeller Mitgliedermanager
## Installieren
1. In der Datenbank muss es eine Log-Tabelle geben. Diese kann so angelegt werden:
```
CREATE TABLE `log` ( 
`ID` BIGINT AUTO_INCREMENT NOT NULL,
`zeit` DATETIME NULL DEFAULT CURRENT_TIMESTAMP ,
`eintrag` VARCHAR(1000) NULL,
    PRIMARY KEY (`ID`)
)
ENGINE = InnoDB;
INSERT INTO log (eintrag) VALUES ('Herzlich Willkommen!');
```
  
  1. In einer Tabelle muss zunächst mindestens ein Datensatz existieren, bevor die Darstellung dieser Tabelle korrekt funktioniert.

# YPUM
## Installation
1. /yconf/lock.json: von `"installmodus":false` auf `"installmodus":true` stellen.
2. In das /youm/-Verzeichnis wechseln und die Installation und Konfiguration beginnen.
3. Einen Admin-User unter 'Formulare/register' anlegen.
4. Diesem Admin-User in der Verwaltung die entsprechenden Rechte zuweisen.
5. Am Ende den Installationsmodus verlassen (Toggle oben rechts)

## Welche Dateien muss ich wie schützen
Prinzipiell durch EIinbinden (require_once) des in den Einstelllungen genannten YPUM-Links.
- Normale php-Dateien: Ganz oben vor jedweiliger Ausgabe (es wird eine Session geöffnet). Ein zusätzliches Öffnen einer Session kann zu einer Fehler meldung führen.
- Ajax Dateien: Ebenfalls oben einbinden. Darauf achten, dass die Ausgaben unterdrückt werden:
``` 
ob_start(); 
echo "Diese Ausgabe wird gepuffert und später weggeworfen. Stört also nicht die Ajax-Antwort";
ob_end_clean();
- Klassen müssen nicht geschützt werden, da diese nur innerhalb des gleichen Servers angesprochen werden können, wenn sie nicht explizit als API zur Verfügung gestellt werden. 