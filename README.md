# D.U.M.M. - Datenbasierter universeller Mitgliedermanager
## Installieren
- In der Datenbank muss es eine Log-Tabelle geben. Diese kann so angelegt werden:
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
  
  - In einer Tabelle muss zunächst mindestens ein Datensatz existieren, bevor die Darstellung dieser Tabelle korrekt funktioniert.

## Berechtigungsstruktur
Es gilt der Grundsatz, dass immer eine Ebene nach unten berechtigt wird.

### Definition
V := Regionalverband (Berechtigung in b_regionalverband_rechte), im folgenden auch 'Verband' genannt
B := Betroebssportgemeinschaft (b_bsg_rechte)
S := Sparten
M := Mitglieder

## ACHTUNG ÄNDERUNG! DEBITOREN KEINE GESONDERTE TABELLE, SONDERN RECHNUNGSEMPFÄNGERANSCHRIFT DIREKT ZUR BSG mit zus. Feldern (für "z.Hd. Sportausschuss o.ä.)
D := Debitoren (Rechnungsempänger eine oder mehrere BSG)
#####

Dann gilt:
offen (1) V => B
offen (2)      B => M
## offen (3)      B => D
(4) V => S
offen (5) Als Sonderfall: M => M

(1) Nur der Verband kann seine BSG anlegen und (z.B. ANsprechpartner) ändern.
(2) Nur (außer (5)) BSG kann seine Mitglieder anlegen und ändern.
(3) Nur BSG kann seinen Debitor ändern.
(4) Nur der Verband kann seine Sparten anlegen oder ändern.
(5) Als Sonderfall kann ein Mitglied seine eigenen Daten ändern.