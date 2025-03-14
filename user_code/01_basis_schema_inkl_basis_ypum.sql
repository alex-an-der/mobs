-- MySQL dump 10.13  Distrib 8.0.40, for Linux (x86_64)
--
-- Host: localhost    Database: db_441127_14
-- ------------------------------------------------------
-- Server version	8.0.39-30

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
DROP TABLE IF EXISTS `b___an_aus`;
CREATE TABLE `b___an_aus` ( 
  `id` TINYINT UNSIGNED AUTO_INCREMENT NOT NULL,
  `wert` VARCHAR(250) NULL,
   PRIMARY KEY (`id`)
)
ENGINE = InnoDB;



--
-- Table structure for table `b___geschlecht`
--

DROP TABLE IF EXISTS `b___geschlecht`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `b___geschlecht` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `auswahl` varchar(15) COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b___sportart`
--

DROP TABLE IF EXISTS `b___sportart`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `b___sportart` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `Sportart` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `Sportart_Nr` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=111 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Sportarten des deutschen BSV';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_bsg`
--

DROP TABLE IF EXISTS `b_bsg`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `b_bsg` ( 
  `id` BIGINT UNSIGNED AUTO_INCREMENT NOT NULL,
  `Verband` BIGINT UNSIGNED NOT NULL,
  `BSG` VARCHAR(100) NOT NULL DEFAULT 'NEU' ,
  `Ansprechpartner` BIGINT UNSIGNED NULL,
  `RE_Name` VARCHAR(100) NULL,
  `RE_Name2` VARCHAR(100) NULL,
  `RE_Strasse_Nr` VARCHAR(100) NULL,
  `RE_Strasse2` VARCHAR(100) NULL,
  `RE_PLZ_Ort` VARCHAR(100) NULL,
  `VKZ` SMALLINT UNSIGNED NULL,
   PRIMARY KEY (`id`),
  CONSTRAINT `FK_bsg_verband` FOREIGN KEY (`Verband`) REFERENCES `b_regionalverband` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `VKZ_pro_Verband_unique` UNIQUE (`Verband`, `VKZ`)
)
ENGINE = InnoDB;
/*!40101 SET character_set_client = @saved_cs_client */;

DROP TABLE IF EXISTS `b_bsg_deleted`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `b_bsg_deleted` ( 
  `id` BIGINT UNSIGNED AUTO_INCREMENT NOT NULL,
  `Verband` BIGINT UNSIGNED NOT NULL,
  `BSG` VARCHAR(100) NOT NULL DEFAULT 'NEU' ,
  `Ansprechpartner` BIGINT UNSIGNED NULL,
  `RE_Name` VARCHAR(100) NULL,
  `RE_Name2` VARCHAR(100) NULL,
  `RE_Strasse_Nr` VARCHAR(100) NULL,
  `RE_Strasse2` VARCHAR(100) NULL,
  `RE_PLZ_Ort` VARCHAR(100) NULL,
  `VKZ` SMALLINT UNSIGNED NULL,
  `delete_date` DATETIME NULL,
   PRIMARY KEY (`id`)
)
ENGINE = InnoDB;
/*!40101 SET character_set_client = @saved_cs_client */;


--
-- Table structure for table `b_bsg_rechte`
--

DROP TABLE IF EXISTS `b_bsg_rechte`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `b_bsg_rechte` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `Nutzer` bigint unsigned NOT NULL,
  `BSG` bigint unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_bsgrechte_nutzer` (`Nutzer`),
  KEY `FK_bsgrechte_bsg` (`BSG`),
  CONSTRAINT `FK_bsgrechte_bsg` FOREIGN KEY (`BSG`) REFERENCES `b_bsg` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_bsgrechte_nutzer` FOREIGN KEY (`Nutzer`) REFERENCES `y_user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_individuelle_berechtigungen`
--

DROP TABLE IF EXISTS `b_individuelle_berechtigungen`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `b_individuelle_berechtigungen` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `Mitglied` bigint unsigned NOT NULL,
  `BSG` bigint unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_indiv_rechte_mitglied` (`Mitglied`),
  KEY `FK_indiv_rechte_BSG` (`BSG`),
  CONSTRAINT `FK_indiv_rechte_BSG` FOREIGN KEY (`BSG`) REFERENCES `b_bsg` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_indiv_rechte_mitglied` FOREIGN KEY (`Mitglied`) REFERENCES `b_mitglieder` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Mitglieder können BSG freigeben, sie zu sehen um sie zB aufzunehmen.';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_mitglieder`
--

DROP TABLE IF EXISTS `b_mitglieder`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `b_mitglieder` ( 
  `id` BIGINT UNSIGNED AUTO_INCREMENT NOT NULL,
  `y_id` BIGINT UNSIGNED NULL,
  `BSG` BIGINT UNSIGNED NULL,
  `Vorname` VARCHAR(100) NULL,
  `Nachname` VARCHAR(100) NULL,
  `Mail` VARCHAR(50) NULL COMMENT 'y_user - Verknüpfung' ,
  `Geschlecht` INT UNSIGNED NULL,
  `Geburtsdatum` DATE NOT NULL DEFAULT '1980-07-01' ,
  `Mailbenachrichtigung` TINYINT UNSIGNED NULL,
   PRIMARY KEY (`id`),
  CONSTRAINT `FK_mitglieder_bsg` FOREIGN KEY (`BSG`) REFERENCES `b_bsg` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `FK_mitglieder_Mailbenachrichtigung` FOREIGN KEY (`Mailbenachrichtigung`) REFERENCES `b___an_aus` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_mitglieder_yuser` FOREIGN KEY (`y_id`) REFERENCES `y_user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `y_id` UNIQUE (`y_id`)
)
ENGINE = InnoDB;
CREATE INDEX `FK_mitglieder_bsg` 
ON `b_mitglieder` (
  `BSG` ASC
);
CREATE INDEX `FK_mitglieder_Mailbenachrichtigung` 
ON `b_mitglieder` (
  `Mailbenachrichtigung` ASC
);
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_mitglieder_deleted`
--

DROP TABLE IF EXISTS `b_mitglieder_deleted`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `b_mitglieder_deleted` ( 
  `id` BIGINT UNSIGNED NOT NULL,
  `y_id` BIGINT UNSIGNED NULL,
  `BSG` BIGINT UNSIGNED NULL,
  `Vorname` VARCHAR(100) NULL,
  `Nachname` VARCHAR(100) NULL,
  `Mail` VARCHAR(50) NULL,
  `Geschlecht` INT UNSIGNED NULL,
  `Geburtsdatum` DATE NOT NULL,
  `Mailbenachrichtigung` TINYINT UNSIGNED NULL,
  `delete_date` DATETIME NULL,
   PRIMARY KEY (`id`)
)
ENGINE = InnoDB;
/*!40101 SET character_set_client = @saved_cs_client */;


--
-- Table structure for table `b_mitglieder_in_sparten`
--

DROP TABLE IF EXISTS `b_mitglieder_in_sparten`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `b_mitglieder_in_sparten` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `Sparte` bigint unsigned NOT NULL,
  `Mitglied` bigint unsigned NOT NULL,
  `BSG` bigint unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_m_in_s__sparte` (`Sparte`),
  KEY `FK_m_in_s__mitglied` (`Mitglied`),
  KEY `FK_m_in_s__bsg` (`BSG`),
  CONSTRAINT `FK_m_in_s__bsg` FOREIGN KEY (`BSG`) REFERENCES `b_bsg` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_m_in_s__mitglied` FOREIGN KEY (`Mitglied`) REFERENCES `b_mitglieder` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_m_in_s__sparte` FOREIGN KEY (`Sparte`) REFERENCES `b_sparte` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=45 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_regionalverband`
--

DROP TABLE IF EXISTS `b_regionalverband`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `b_regionalverband` ( 
  `id` BIGINT UNSIGNED AUTO_INCREMENT NOT NULL,
  `Verband` VARCHAR(100) NOT NULL DEFAULT 'NEU' ,
  `Kurzname` VARCHAR(50) NULL DEFAULT 'NEU' ,
  `Internetadresse` VARCHAR(100) NULL,
  `BKV` SMALLINT UNSIGNED NULL,
   PRIMARY KEY (`id`),
  CONSTRAINT `BKV_unique` UNIQUE (`BKV`)
)
ENGINE = InnoDB;
/*!40101 SET character_set_client = @saved_cs_client */;


--
-- Table structure for table `b_regionalverband_rechte`
--

DROP TABLE IF EXISTS `b_regionalverband_rechte`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `b_regionalverband_rechte` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `Nutzer` bigint unsigned NOT NULL,
  `Verband` bigint unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_verbandrechte_verband` (`Verband`),
  KEY `idx_nutzer_verband` (`Nutzer`,`Verband`),
  CONSTRAINT `FK_verbandrechte_verband` FOREIGN KEY (`Verband`) REFERENCES `b_regionalverband` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_verbandrechte_yuser` FOREIGN KEY (`Nutzer`) REFERENCES `y_user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b_sparte`
--

DROP TABLE IF EXISTS `b_sparte`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `b_sparte` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `Verband` bigint unsigned NOT NULL,
  `Sparte` varchar(100) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'NEU',
  `Spartenleiter` bigint unsigned DEFAULT NULL,
  `Sportart` int unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `Verband` (`Verband`),
  KEY `FK_sparte_sportart` (`Sportart`),
  CONSTRAINT `FK_sparte_sportart` FOREIGN KEY (`Sportart`) REFERENCES `b___sportart` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `FK_sparte_verband` FOREIGN KEY (`Verband`) REFERENCES `b_regionalverband` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=38 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `log`
--

DROP TABLE IF EXISTS `log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `log` (
  `ID` bigint NOT NULL AUTO_INCREMENT,
  `zeit` datetime DEFAULT CURRENT_TIMESTAMP,
  `eintrag` varchar(1000) COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=34 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `rollback`
--

DROP TABLE IF EXISTS `rollback`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rollback` (
  `ID` bigint NOT NULL AUTO_INCREMENT,
  `zeit` datetime DEFAULT CURRENT_TIMESTAMP,
  `autor` varchar(200) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `eintrag` varchar(1000) COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=357 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `rollback`
--

DROP TABLE IF EXISTS `issues`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `issues` ( 
  `id` BIGINT UNSIGNED AUTO_INCREMENT NOT NULL,
  `Prio` TINYINT UNSIGNED NULL,
  `Kommentar` VARCHAR(100) NULL,
  `Issue` VARCHAR(200) NULL,
   PRIMARY KEY (`id`)
)
ENGINE = InnoDB;
/*!40101 SET character_set_client = @saved_cs_client */;


--
-- Table structure for table `y_roles`
--

DROP TABLE IF EXISTS `y_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `y_roles` (
  `bit` int NOT NULL,
  `name` varchar(80) COLLATE utf8mb4_general_ci DEFAULT '',
  `role_comment` varchar(300) COLLATE utf8mb4_general_ci DEFAULT '',
  `role_active` bit(1) NOT NULL DEFAULT b'1',
  PRIMARY KEY (`bit`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `y_sites`
--

DROP TABLE IF EXISTS `y_sites`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `y_sites` (
  `ID` bigint unsigned NOT NULL AUTO_INCREMENT,
  `dir` varchar(300) COLLATE utf8mb4_general_ci NOT NULL,
  `roles` bigint unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `dir` (`dir`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `y_user`
--

DROP TABLE IF EXISTS `y_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `y_user` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `mail` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `locked` tinyint DEFAULT '0',
  `password` varchar(130) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'ein_default_wert_der_laenger_als_der_hash_ist_kann_nie_stimmen_und_ist_sicherer_als_null_oder_leer_42',
  `roles` int unsigned NOT NULL DEFAULT '0',
  `lastlogin` datetime DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `validated` datetime DEFAULT NULL,
  `token` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `tokencreated` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `mail` (`mail`),
  UNIQUE KEY `token` (`token`)
) ENGINE=InnoDB AUTO_INCREMENT=100 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'IGNORE_SPACE,ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;

/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `y_user_details`
--

DROP TABLE IF EXISTS `y_user_details`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `y_user_details` (
  `userID` bigint unsigned NOT NULL,
  `fieldID` bigint unsigned NOT NULL,
  `fieldvalue` varchar(260) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`userID`,`fieldID`) USING BTREE,
  KEY `FK_y_user_details_y_user` (`userID`) USING BTREE,
  KEY `FK_y_user_details_y_user_fields` (`fieldID`) USING BTREE,
  CONSTRAINT `FK_y_user_details_y_user` FOREIGN KEY (`userID`) REFERENCES `y_user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_y_user_details_y_user_fields` FOREIGN KEY (`fieldID`) REFERENCES `y_user_fields` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `y_user_fields`
--

DROP TABLE IF EXISTS `y_user_fields`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `y_user_fields` (
  `ID` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uf_name` varchar(50) NOT NULL DEFAULT '',
  `fieldname` varchar(50) NOT NULL DEFAULT '',
  PRIMARY KEY (`ID`) USING BTREE,
  UNIQUE KEY `fieldname` (`fieldname`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Temporary view structure for view `y_v_userdata`
--

DROP TABLE IF EXISTS `y_v_userdata`;
/*!50001 DROP VIEW IF EXISTS `y_v_userdata`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `y_v_userdata` AS SELECT 
 1 AS `userID`,
 1 AS `mail`,
 1 AS `nname`,
 1 AS `vname`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary view structure for view `y_v_userfields`
--

DROP TABLE IF EXISTS `y_v_userfields`;
/*!50001 DROP VIEW IF EXISTS `y_v_userfields`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `y_v_userfields` AS SELECT 
 1 AS `fieldID`,
 1 AS `userID`,
 1 AS `uf_name`,
 1 AS `fieldname`,
 1 AS `fieldvalue`*/;
SET character_set_client = @saved_cs_client;

--
-- Final view structure for view `y_v_userdata`
--

/*!50001 DROP VIEW IF EXISTS `y_v_userdata`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50001 VIEW `y_v_userdata` AS select distinct `t_uid`.`userID` AS `userID`,`y_user`.`mail` AS `mail`,`t_nname`.`fieldvalue` AS `nname`,`t_vname`.`fieldvalue` AS `vname` from (((`y_v_userfields` `t_uid` join `y_user` on((`t_uid`.`userID` = `y_user`.`id`))) left join (select `y_v_userfields`.`userID` AS `userID`,`y_v_userfields`.`fieldvalue` AS `fieldvalue` from `y_v_userfields` where (`y_v_userfields`.`fieldname` = 'nname')) `t_nname` on((`t_uid`.`userID` = `t_nname`.`userID`))) left join (select `y_v_userfields`.`userID` AS `userID`,`y_v_userfields`.`fieldvalue` AS `fieldvalue` from `y_v_userfields` where (`y_v_userfields`.`fieldname` = 'vname')) `t_vname` on((`t_uid`.`userID` = `t_vname`.`userID`))) where (`y_user`.`locked` = 0) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `y_v_userfields`
--

/*!50001 DROP VIEW IF EXISTS `y_v_userfields`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50001 VIEW `y_v_userfields` AS select `f`.`ID` AS `fieldID`,`d`.`userID` AS `userID`,`f`.`uf_name` AS `uf_name`,`f`.`fieldname` AS `fieldname`,`d`.`fieldvalue` AS `fieldvalue` from (`y_user_details` `d` left join `y_user_fields` `f` on((`d`.`fieldID` = `f`.`ID`))) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-02-08 11:32:16
