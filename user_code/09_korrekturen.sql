ALTER TABLE `b_mitglieder_in_sparten` ADD CONSTRAINT `Mitglied_in_Sparte` UNIQUE (`Sparte`, `Mitglied`);
ALTER TABLE `b_bsg` CHANGE COLUMN `VKZ` `VKZ` VARCHAR(8) NULL;
ALTER TABLE `adm_issues` ADD  `version` VARCHAR(50) NULL;
ALTER TABLE `adm_issues` CHANGE COLUMN `Kommentar` `Kommentar` VARCHAR(2000) NULL;
ALTER TABLE `adm_issues` CHANGE COLUMN `Issue` `Issue` VARCHAR(2000) NULL;