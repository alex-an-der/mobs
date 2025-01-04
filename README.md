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
2. In das /youm/-Verzeichnis wechseln und die Installation beginnen.