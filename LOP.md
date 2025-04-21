# Rechnungserstellung
- Rechnungserzeugung? PDF??
- Rechnungen können in der Cloud abgelegt werden - Link kann gespeichert werden

## Nächste Schritte


## In der Prod-DB einfügen
```
DROP TABLE IF EXISTS `b_zahlungseingaenge`;
CREATE TABLE `b_zahlungseingaenge` ( 
  `id` BIGINT UNSIGNED AUTO_INCREMENT NOT NULL,
  `BSG` BIGINT UNSIGNED NOT NULL,
  `Abrechnungsjahr` YEAR NOT NULL,
  `Haben` DECIMAL(10,2) NOT NULL,
  `Eingangsdatum` DATE NOT NULL,
   PRIMARY KEY (`id`),
  CONSTRAINT `FK_zahlungseingaenge_bsg` FOREIGN KEY (`BSG`) REFERENCES `b_bsg` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE
)
ENGINE = InnoDB;

CREATE INDEX `FK_zahlungseingaenge_bsg` 
ON `b_zahlungseingaenge` (
  `BSG` ASC
);

DROP TABLE IF EXISTS `b_forderungen`;
CREATE TABLE `b_forderungen` ( 
  `id` BIGINT UNSIGNED AUTO_INCREMENT NOT NULL,
  `Datum` DATE NULL,
  `BSG` BIGINT UNSIGNED NOT NULL,
  `Beschreibung` VARCHAR(2000) NULL,
  `Soll` DECIMAL(10,2) NULL,
   PRIMARY KEY (`id`),
  CONSTRAINT `FK_forderungen_bsg` FOREIGN KEY (`BSG`) REFERENCES `b_bsg` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE
)
ENGINE = InnoDB;
CREATE INDEX `FK_forderungen_bsg` 
ON `b_forderungen` (
  `BSG` ASC
);

```