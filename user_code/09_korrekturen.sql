ALTER TABLE `b_individuelle_berechtigungen` ADD CONSTRAINT `UNIQUE_Mitglied_BSG` UNIQUE (`Mitglied`, `BSG`);
ALTER TABLE `b_mitglieder_in_sparten` ADD CONSTRAINT `UNIQUE_Mitglied_Sparte` UNIQUE (`Mitglied`, `Sparte`);


-- ####################
-- Nachträglich - nur falls ID-Anpassung nötig:
-- SET @new_id = 100000;
-- UPDATE b_mitglieder SET id = (@new_id := @new_id + 1) ORDER BY id;
-- SELECT MAX(id) + 1 AS neuer_wert FROM b_mitglieder;
-- ALTER TABLE b_mitglieder AUTO_INCREMENT = <<Ergebnis aus dem SELECT>>;