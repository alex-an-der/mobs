-- ALTER TABLE `b_mitglieder_in_sparten` ADD CONSTRAINT `Mitglied_in_Sparte` UNIQUE (`Sparte`, `Mitglied`);
-- ALTER TABLE `b_bsg` CHANGE COLUMN `VKZ` `VKZ` VARCHAR(8) NULL;
-- ALTER TABLE `adm_issues` ADD  `version` VARCHAR(50) NULL;
-- ALTER TABLE `adm_issues` CHANGE COLUMN `Kommentar` `Kommentar` VARCHAR(2000) NULL;
-- ALTER TABLE `adm_issues` CHANGE COLUMN `Issue` `Issue` VARCHAR(2000) NULL;
-- ALTER TABLE `b_regionalverband` ADD  `Basisbeitrag` DECIMAL(10,2) NOT NULL DEFAULT 0.00 ;
-- ALTER TABLE `b_sparte` ADD  `Spartenbeitrag` DECIMAL(10,2) NOT NULL DEFAULT 0.00 ;
-- ALTER TABLE b_mitglieder AUTO_INCREMENT = 100001;



-- ####################
-- Nachträglich:
-- SET @new_id = 100000;
-- UPDATE b_mitglieder SET id = (@new_id := @new_id + 1) ORDER BY id;
-- SELECT MAX(id) + 1 AS neuer_wert FROM b_mitglieder;
-- ALTER TABLE b_mitglieder AUTO_INCREMENT = <<Ergebnis aus dem SELECT>>;