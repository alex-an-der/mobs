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
