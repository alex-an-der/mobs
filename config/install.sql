-- System Error Manager Tabelle
DROP TABLE IF EXISTS `sys_error_manager`;
CREATE TABLE `sys_error_manager` ( 
  `id` BIGINT UNSIGNED AUTO_INCREMENT NOT NULL,
  `raw_message` VARCHAR(1000) NOT NULL,
  `sql_error_code` INT UNSIGNED NULL,
  `description` VARCHAR(500) NULL,
  `user_message` VARCHAR(1000) NULL,
  `source` VARCHAR(50) NULL,
  `add_fulltext_constraint` VARCHAR(200) NULL,
  `error_log_id` BIGINT UNSIGNED NULL,
   PRIMARY KEY (`id`)
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
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
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
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;