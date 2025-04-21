# Rechnungserstellung
- Eine Liste: Aktuelles Konto mit den aktuellen Preisen (Basis & Sparte)
- Rechnungserzeugung?
- Rechnungen können in der Cloud abgelegt werden - Link kann gespeichert werden
- Bei offenen Beträgen müssen diese als Betrag (Rechnung/Rechnungsnummer) in eine separate "Forderungs-"Tabelle eingetragen werden, damit diese konserviert werden, auch wenn sich die Beiträge ändern.

## Nächste Schritte
- Liste der aktuellen Forderungen
- ggf. verschiedene Ansichten: "Dieses Jahr" und "letztes Jahr" - dann ist ein Jahreswechsel nicht so schlimm und es bleibt übersichtlich (immer nur das zu betrachtende Jahr, aber Abrechnung kann dann auch noch im Januar geschehen) - wichtig: VOR der Erhöhung der Beiträge



```
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

```