CREATE TABLE `sys_error_manager` ( 
  `id` BIGINT UNSIGNED AUTO_INCREMENT NOT NULL,
  `raw_message` VARCHAR(500) NOT NULL,
  `sql_error_code` INT UNSIGNED NULL,
  `description` VARCHAR(1000) NULL,
  `user_message` VARCHAR(500) NULL,
  `source` VARCHAR(100) NULL,
  `add_fulltext_constraint` VARCHAR(50) NULL,
  `error_log_id` BIGINT UNSIGNED NULL,
   PRIMARY KEY (`id`),
  CONSTRAINT `unique_code_plus_text` UNIQUE (`sql_error_code`, `add_fulltext_constraint`)
)
ENGINE = InnoDB;



DROP TABLE IF EXISTS `sys_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sys_log` (
  `ID` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `zeit` datetime DEFAULT CURRENT_TIMESTAMP,
  `eintrag` varchar(1000) COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=34 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;



DROP TABLE IF EXISTS `sys_rollback`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sys_rollback` (
  `ID` bigint NOT NULL AUTO_INCREMENT,
  `zeit` datetime DEFAULT CURRENT_TIMESTAMP,
  `autor` varchar(200) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `eintrag` varchar(1000) COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=357 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;