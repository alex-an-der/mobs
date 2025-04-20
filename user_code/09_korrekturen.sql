ALTER TABLE `b_mitglieder_in_sparten` ADD CONSTRAINT `Mitglied_in_Sparte` UNIQUE (`Sparte`, `Mitglied`);
ALTER TABLE `b_bsg` CHANGE COLUMN `VKZ` `VKZ` VARCHAR(8) NULL;
ALTER TABLE `adm_issues` ADD  `version` VARCHAR(50) NULL;
ALTER TABLE `adm_issues` CHANGE COLUMN `Kommentar` `Kommentar` VARCHAR(2000) NULL;
ALTER TABLE `adm_issues` CHANGE COLUMN `Issue` `Issue` VARCHAR(2000) NULL;

-- #############################################################################
ALTER TABLE `b_mitglieder`
ADD COLUMN `Stammmitglied_seit` DATE DEFAULT (CURRENT_DATE);


DELIMITER //
CREATE TRIGGER `update_stammmitglied_seit` 
BEFORE UPDATE ON `b_mitglieder`
FOR EACH ROW
BEGIN
    IF NEW.BSG <> OLD.BSG OR 
       (OLD.BSG IS NULL AND NEW.BSG IS NOT NULL) OR 
       (OLD.BSG IS NOT NULL AND NEW.BSG IS NULL) THEN
        SET NEW.Stammmitglied_seit = CURRENT_TIMESTAMP;
    END IF;
END//
DELIMITER ;

ALTER TABLE `b_mitglieder_in_sparten`
ADD COLUMN `seit` DATE DEFAULT (CURRENT_DATE);

ALTER TABLE `b_regionalverband` ADD  `Basisbeitrag` DECIMAL(10,2) NOT NULL DEFAULT 0.00 ;
ALTER TABLE `b_sparte` ADD  `Spartenbeitrag` DECIMAL(10,2) NOT NULL DEFAULT 0.00 ;
