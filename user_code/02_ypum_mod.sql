ALTER TABLE `y_user` ADD  `run_trigger` BOOL NOT NULL DEFAULT 1 ;


DROP TABLE IF EXISTS `y_deleted_users`;
CREATE TABLE `y_deleted_users` ( 
  `id` BIGINT UNSIGNED AUTO_INCREMENT NOT NULL,
  `y_id` BIGINT UNSIGNED NULL,
  `mail` VARCHAR(50) NULL,
  `delete_date` DATETIME NULL,
   PRIMARY KEY (`id`)
)
ENGINE = InnoDB;

-- Userfields müssen definiert werden, BEVOR die y-views erstellt werden
INSERT INTO `y_user_fields` (`ID`, `uf_name`, `fieldname`) VALUES ('4', 'Vorname', 'vname');
INSERT INTO `y_user_fields` (`ID`, `uf_name`, `fieldname`) VALUES ('5', 'Nachname', 'nname');
INSERT INTO `y_user_fields` (`ID`, `uf_name`, `fieldname`) VALUES ('6', 'Mail_OK', 'okformail');
INSERT INTO `y_user_fields` (`ID`, `uf_name`, `fieldname`) VALUES ('7', 'Geschlecht', 'geschlecht');
INSERT INTO `y_user_fields` (`ID`, `uf_name`, `fieldname`) VALUES ('8', 'Geburtsdatum', 'gebdatum');
INSERT INTO `y_user_fields` (`ID`, `uf_name`, `fieldname`) VALUES ('9', 'bsg', 'bsg');