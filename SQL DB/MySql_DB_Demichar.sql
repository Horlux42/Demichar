-- MySQL dump 10.13  Distrib 8.0.45, for Win64 (x86_64)
--
-- Host: localhost    Database: sys
-- ------------------------------------------------------
-- Server version	9.6.0

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
SET @MYSQLDUMP_TEMP_LOG_BIN = @@SESSION.SQL_LOG_BIN;
SET @@SESSION.SQL_LOG_BIN= 0;

--
-- GTID state at the beginning of the backup 
--

SET @@GLOBAL.GTID_PURGED=/*!80000 '+'*/ '32c72df0-31fc-11f1-921a-0250d104c917:1-633';

--
-- Table structure for table `characters`
--

DROP TABLE IF EXISTS `characters`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `characters` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `name` varchar(100) NOT NULL,
  `data` longtext,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `avatar` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `characters_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=35 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `characters`
--

LOCK TABLES `characters` WRITE;
/*!40000 ALTER TABLE `characters` DISABLE KEYS */;
INSERT INTO `characters` VALUES (1,1,'арбузик','{\"level\":3,\"xp\":67,\"coins\":52,\"hp\":11,\"maxHp\":15,\"modules\":[{\"title\":\"\\u041d\\u043e\\u0432\\u044b\\u0439 \\u043c\\u043e\\u0434\\u0443\\u043b\\u044c\",\"items\":[]}]}','2026-05-08 14:46:11','uploads/avatars/avatar_1_1779436325.jpeg'),(3,12,'мяу мяу',NULL,'2026-05-08 14:48:09',NULL),(4,16,'Эррата',NULL,'2026-05-10 12:55:27',NULL),(27,1,'Всеволод','{\"level\":1,\"xp\":127,\"coins\":250,\"hp\":15,\"maxHp\":15,\"modules\":[{\"title\":\"\\u0418\\u043d\\u0432\\u0435\\u043d\\u0442\\u0430\\u0440\\u044c\",\"items\":[{\"name\":\"\\u0430\\u0440\\u0431\\u0443\\u0437\\u043d\\u044b\\u0435 \\u0441\\u0435\\u043c\\u0435\\u0447\\u043a\\u0438\",\"hasValue\":true,\"value\":\"5000\"},{\"name\":\"\\u0421\\u0438\\u043b\\u0430\",\"hasValue\":true,\"value\":\"12\"},{\"name\":\"\\u0430\\u0440\\u0431\\u0443\\u0437\",\"hasValue\":false}]},{\"title\":\"\\u041d\\u043e\\u0432\\u044b\\u0439 \\u043c\\u043e\\u0434\\u0443\\u043b\\u044c\\u043f\\u0432\\u0430\\u0432\\u0430\\u043f\\u0440\\u0440\\u0432\\u0430\\u043f\\u0440\\u0432\\u0440\\u043f\\u0430\\u0440\\u0432\",\"items\":[{\"name\":\"\\u0440\\u043f\\u043f\\u0440\\u0432\\u0430\\u0432\\u0430\\u0440\\u043f\\u0430\\u043f\\u0432\\u0440\",\"hasValue\":false},{\"name\":\"\\u043f\\u0430\\u0440\\u0440\\u043f\\u0430\\u0432\\u0432\\u0430\\u043f\\u0440\\u043f\\u0430\\u0440\\u0432\",\"hasValue\":true,\"value\":\"\"}]}]}','2026-05-24 17:34:05','uploads/avatars/avatar_27_1779743453.png'),(33,1,'Новый персонаж',NULL,'2026-05-25 04:54:47',NULL),(34,18,'Евгений','{\"level\":1,\"xp\":100,\"coins\":100,\"hp\":16,\"maxHp\":32,\"modules\":[{\"title\":\"\\u041d\\u0430\\u0437\\u0432\\u0430\\u043d\\u0438\\u0435 \\u043c\\u043e\\u0434\\u0443\\u043b\\u044f\",\"items\":[{\"name\":\"\\u0414\\u043e\\u043f\\u0443\\u0441\\u0442\\u0438\\u043c \\u043f\\u0440\\u0435\\u0434\\u043c\\u0435\\u0442\",\"hasValue\":false},{\"name\":\"\\u0434\\u043e\\u043f\\u0443\\u0441\\u0442\\u0438\\u043c \\u043c\\u043d\\u043e\\u0433\\u043e \\u043f\\u0440\\u0435\\u0434\\u043c\\u0435\\u0442\\u043e\\u0432\",\"hasValue\":true,\"value\":\"100\"},{\"name\":\"\",\"hasValue\":false}]},{\"title\":\"\\u041d\\u043e\\u0432\\u044b\\u0439 \\u043c\\u043e\\u0434\\u0443\\u043b\\u044c\",\"items\":[]}]}','2026-05-26 20:29:26','uploads/avatars/avatar_34_1779827504.jpg');
/*!40000 ALTER TABLE `characters` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sys_config`
--

DROP TABLE IF EXISTS `sys_config`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sys_config` (
  `variable` varchar(128) NOT NULL,
  `value` varchar(128) DEFAULT NULL,
  `set_time` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `set_by` varchar(128) DEFAULT NULL,
  PRIMARY KEY (`variable`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sys_config`
--

LOCK TABLES `sys_config` WRITE;
/*!40000 ALTER TABLE `sys_config` DISABLE KEYS */;
INSERT INTO `sys_config` VALUES ('diagnostics.allow_i_s_tables','OFF','2026-04-06 21:04:31',NULL),('diagnostics.include_raw','OFF','2026-04-06 21:04:31',NULL),('ps_thread_trx_info.max_length','65535','2026-04-06 21:04:31',NULL),('statement_performance_analyzer.limit','100','2026-04-06 21:04:31',NULL),('statement_performance_analyzer.view',NULL,'2026-04-06 21:04:31',NULL),('statement_truncate_len','64','2026-04-06 21:04:31',NULL);
/*!40000 ALTER TABLE `sys_config` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `login` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `login_UNIQUE` (`login`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'арбуз','12345'),(7,'test','test123'),(12,'гав гав','мяу мяу'),(13,'555','666'),(14,'bublik','1234567890'),(15,'кириешки-пельмешки','король_бургеров12'),(16,'Диджей','557697'),(17,'ништяк','ништяк1'),(18,'Гость','Гость12345');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
SET @@SESSION.SQL_LOG_BIN = @MYSQLDUMP_TEMP_LOG_BIN;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-05-27  0:12:26
