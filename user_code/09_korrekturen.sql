ALTER TABLE `b_mitglieder_in_sparten` ADD CONSTRAINT `Mitglied_in_Sparte` UNIQUE (`Sparte`, `Mitglied`);
ALTER TABLE `b_bsg` CHANGE COLUMN `VKZ` `VKZ` VARCHAR(8) NULL;
ALTER TABLE `issues` ADD  `version` VARCHAR(50) NULL;
