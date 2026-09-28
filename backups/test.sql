-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: alumni_management
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `academic_levels`
--

DROP TABLE IF EXISTS `academic_levels`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `academic_levels` (
  `level_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `section_id` int(10) unsigned NOT NULL,
  `specialization_id` int(10) unsigned DEFAULT NULL,
  `level` tinyint(3) unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`level_id`),
  UNIQUE KEY `uq_academic_level` (`section_id`,`specialization_id`,`level`),
  KEY `idx_academic_levels_section` (`section_id`),
  KEY `idx_academic_levels_specialization` (`specialization_id`),
  CONSTRAINT `fk_academic_levels_section` FOREIGN KEY (`section_id`) REFERENCES `sections` (`section_id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_academic_levels_specialization` FOREIGN KEY (`specialization_id`) REFERENCES `specializations` (`specialization_id`) ON UPDATE CASCADE,
  CONSTRAINT `chk_academic_level` CHECK (`level` between 1 and 5)
) ENGINE=InnoDB AUTO_INCREMENT=171 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `academic_levels`
--

LOCK TABLES `academic_levels` WRITE;
/*!40000 ALTER TABLE `academic_levels` DISABLE KEYS */;
INSERT INTO `academic_levels` VALUES (1,1,NULL,1,'2026-09-22 00:12:49'),(2,1,NULL,2,'2026-09-22 00:12:49'),(3,1,NULL,3,'2026-09-22 00:12:49'),(4,1,NULL,4,'2026-09-22 00:12:49'),(5,1,NULL,5,'2026-09-22 00:12:49'),(6,2,NULL,1,'2026-09-22 00:12:49'),(7,2,NULL,2,'2026-09-22 00:12:49'),(8,2,NULL,3,'2026-09-22 00:12:49'),(9,2,NULL,4,'2026-09-22 00:12:49'),(10,2,NULL,5,'2026-09-22 00:12:49'),(11,3,NULL,1,'2026-09-22 00:12:49'),(12,3,NULL,2,'2026-09-22 00:12:49'),(13,3,NULL,3,'2026-09-22 00:12:49'),(14,3,NULL,4,'2026-09-22 00:12:49'),(15,3,NULL,5,'2026-09-22 00:12:49'),(16,4,NULL,1,'2026-09-22 00:12:49'),(17,4,NULL,2,'2026-09-22 00:12:49'),(18,4,NULL,3,'2026-09-22 00:12:49'),(19,4,NULL,4,'2026-09-22 00:12:49'),(20,4,NULL,5,'2026-09-22 00:12:49'),(21,5,NULL,1,'2026-09-22 00:12:49'),(22,5,NULL,2,'2026-09-22 00:12:49'),(23,5,NULL,3,'2026-09-22 00:12:49'),(24,5,NULL,4,'2026-09-22 00:12:49'),(25,5,NULL,5,'2026-09-22 00:12:49'),(26,6,NULL,1,'2026-09-22 00:12:49'),(27,6,NULL,2,'2026-09-22 00:12:49'),(28,6,NULL,3,'2026-09-22 00:12:49'),(29,6,NULL,4,'2026-09-22 00:12:49'),(30,6,NULL,5,'2026-09-22 00:12:49'),(31,7,NULL,1,'2026-09-22 00:12:49'),(32,7,NULL,2,'2026-09-22 00:12:49'),(33,7,NULL,3,'2026-09-22 00:12:49'),(34,7,NULL,4,'2026-09-22 00:12:49'),(35,7,NULL,5,'2026-09-22 00:12:49'),(36,8,NULL,1,'2026-09-22 00:12:49'),(37,8,NULL,2,'2026-09-22 00:12:49'),(38,8,NULL,3,'2026-09-22 00:12:49'),(39,8,NULL,4,'2026-09-22 00:12:49'),(40,8,NULL,5,'2026-09-22 00:12:49'),(41,9,NULL,1,'2026-09-22 00:12:49'),(42,9,NULL,2,'2026-09-22 00:12:49'),(43,9,NULL,3,'2026-09-22 00:12:49'),(44,9,NULL,4,'2026-09-22 00:12:49'),(45,9,NULL,5,'2026-09-22 00:12:49'),(46,10,NULL,1,'2026-09-22 00:12:49'),(47,10,NULL,2,'2026-09-22 00:12:49'),(48,10,NULL,3,'2026-09-22 00:12:49'),(49,10,NULL,4,'2026-09-22 00:12:49'),(50,10,NULL,5,'2026-09-22 00:12:49'),(51,11,NULL,1,'2026-09-22 00:12:49'),(52,11,NULL,2,'2026-09-22 00:12:49'),(53,11,NULL,3,'2026-09-22 00:12:49'),(54,12,NULL,1,'2026-09-22 00:12:49'),(55,12,NULL,2,'2026-09-22 00:12:49'),(56,12,NULL,3,'2026-09-22 00:12:49'),(57,13,NULL,1,'2026-09-22 00:12:49'),(58,13,NULL,2,'2026-09-22 00:12:49'),(59,13,NULL,3,'2026-09-22 00:12:49'),(60,14,NULL,1,'2026-09-22 00:12:49'),(61,14,NULL,2,'2026-09-22 00:12:49'),(62,14,NULL,3,'2026-09-22 00:12:49'),(63,15,NULL,4,'2026-09-22 00:12:49'),(64,16,NULL,4,'2026-09-22 00:12:49'),(65,17,NULL,4,'2026-09-22 00:12:49'),(66,18,NULL,4,'2026-09-22 00:12:49'),(67,19,NULL,5,'2026-09-22 00:12:49'),(68,20,NULL,5,'2026-09-22 00:12:49'),(69,21,NULL,1,'2026-09-22 00:12:49'),(70,21,NULL,2,'2026-09-22 00:12:49'),(71,21,NULL,3,'2026-09-22 00:12:49'),(72,21,NULL,4,'2026-09-22 00:12:49'),(73,21,NULL,5,'2026-09-22 00:12:49'),(74,22,NULL,1,'2026-09-22 00:12:49'),(75,22,NULL,2,'2026-09-22 00:12:49'),(76,22,NULL,3,'2026-09-22 00:12:49'),(77,22,NULL,4,'2026-09-22 00:12:49'),(78,23,NULL,1,'2026-09-22 00:12:49'),(79,23,NULL,2,'2026-09-22 00:12:49'),(80,23,NULL,3,'2026-09-22 00:12:49'),(81,23,NULL,4,'2026-09-22 00:12:49'),(82,24,NULL,1,'2026-09-22 00:12:49'),(83,24,NULL,2,'2026-09-22 00:12:49'),(84,24,NULL,3,'2026-09-22 00:12:49'),(85,24,NULL,4,'2026-09-22 00:12:49'),(86,24,NULL,5,'2026-09-22 00:12:49'),(87,25,NULL,1,'2026-09-22 00:12:49'),(88,25,NULL,2,'2026-09-22 00:12:49'),(89,25,NULL,3,'2026-09-22 00:12:49'),(90,25,NULL,4,'2026-09-22 00:12:49'),(91,25,NULL,5,'2026-09-22 00:12:49'),(92,26,NULL,1,'2026-09-22 00:12:49'),(93,26,NULL,2,'2026-09-22 00:12:49'),(94,26,NULL,3,'2026-09-22 00:12:49'),(95,26,NULL,4,'2026-09-22 00:12:49'),(96,26,NULL,5,'2026-09-22 00:12:49'),(97,27,NULL,1,'2026-09-22 00:12:49'),(98,27,NULL,2,'2026-09-22 00:12:49'),(99,27,NULL,3,'2026-09-22 00:12:49'),(100,27,NULL,4,'2026-09-22 00:12:49'),(101,27,NULL,5,'2026-09-22 00:12:49'),(102,28,NULL,1,'2026-09-22 00:12:49'),(103,28,NULL,2,'2026-09-22 00:12:49'),(104,28,NULL,3,'2026-09-22 00:12:49'),(105,28,NULL,4,'2026-09-22 00:12:49'),(106,28,NULL,5,'2026-09-22 00:12:49'),(107,29,NULL,1,'2026-09-22 00:12:49'),(108,29,NULL,2,'2026-09-22 00:12:49'),(109,29,NULL,3,'2026-09-22 00:12:49'),(110,29,NULL,4,'2026-09-22 00:12:49'),(111,29,NULL,5,'2026-09-22 00:12:49'),(112,30,NULL,1,'2026-09-22 00:12:49'),(113,30,NULL,2,'2026-09-22 00:12:49'),(114,30,NULL,3,'2026-09-22 00:12:49'),(115,30,NULL,4,'2026-09-22 00:12:49'),(116,30,NULL,5,'2026-09-22 00:12:49'),(117,31,NULL,1,'2026-09-22 00:12:49'),(118,31,NULL,2,'2026-09-22 00:12:49'),(119,31,NULL,3,'2026-09-22 00:12:49'),(120,31,NULL,4,'2026-09-22 00:12:49'),(121,31,NULL,5,'2026-09-22 00:12:49'),(122,32,NULL,1,'2026-09-22 00:12:49'),(123,32,NULL,2,'2026-09-22 00:12:49'),(124,32,NULL,3,'2026-09-22 00:12:49'),(125,32,NULL,4,'2026-09-22 00:12:49'),(126,32,NULL,5,'2026-09-22 00:12:49'),(127,33,1,3,'2026-09-22 00:12:49'),(128,33,1,4,'2026-09-22 00:12:49'),(129,33,2,3,'2026-09-22 00:12:49'),(130,33,2,4,'2026-09-22 00:12:49'),(131,33,3,3,'2026-09-22 00:12:49'),(132,33,3,4,'2026-09-22 00:12:49'),(133,33,4,3,'2026-09-22 00:12:49'),(134,33,4,4,'2026-09-22 00:12:49'),(135,33,5,3,'2026-09-22 00:12:49'),(136,33,5,4,'2026-09-22 00:12:49'),(137,33,6,3,'2026-09-22 00:12:49'),(138,33,6,4,'2026-09-22 00:12:49'),(139,33,7,3,'2026-09-22 00:12:49'),(140,33,7,4,'2026-09-22 00:12:49'),(141,33,8,3,'2026-09-22 00:12:49'),(142,33,8,4,'2026-09-22 00:12:49'),(143,33,9,3,'2026-09-22 00:12:49'),(144,33,9,4,'2026-09-22 00:12:49'),(145,34,NULL,3,'2026-09-22 00:12:49'),(146,34,NULL,4,'2026-09-22 00:12:49'),(147,35,10,3,'2026-09-22 00:12:49'),(148,35,10,4,'2026-09-22 00:12:49'),(149,35,11,3,'2026-09-22 00:12:49'),(150,35,11,4,'2026-09-22 00:12:49'),(151,35,12,3,'2026-09-22 00:12:49'),(152,35,12,4,'2026-09-22 00:12:49'),(153,36,13,3,'2026-09-22 00:12:49'),(154,36,13,4,'2026-09-22 00:12:49'),(155,36,14,3,'2026-09-22 00:12:49'),(156,36,14,4,'2026-09-22 00:12:49'),(157,36,15,3,'2026-09-22 00:12:49'),(158,36,15,4,'2026-09-22 00:12:49'),(159,37,16,1,'2026-09-22 00:12:49'),(160,37,16,2,'2026-09-22 00:12:49'),(161,37,16,3,'2026-09-22 00:12:49'),(162,37,16,4,'2026-09-22 00:12:49'),(163,37,17,1,'2026-09-22 00:12:49'),(164,37,17,2,'2026-09-22 00:12:49'),(165,37,17,3,'2026-09-22 00:12:49'),(166,37,17,4,'2026-09-22 00:12:49'),(167,37,18,1,'2026-09-22 00:12:49'),(168,37,18,2,'2026-09-22 00:12:49'),(169,37,18,3,'2026-09-22 00:12:49'),(170,37,18,4,'2026-09-22 00:12:49');
/*!40000 ALTER TABLE `academic_levels` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `alumni`
--

DROP TABLE IF EXISTS `alumni`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `alumni` (
  `alumni_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `department_id` int(10) unsigned NOT NULL,
  `section_id` int(10) unsigned DEFAULT NULL,
  `specialization_id` int(10) unsigned DEFAULT NULL,
  `level` tinyint(3) unsigned DEFAULT NULL,
  `college_id_number` varchar(100) DEFAULT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `gender` enum('male','female','other') DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `graduation_year` year(4) NOT NULL,
  `bio` text DEFAULT NULL,
  `profile_photo` varchar(255) DEFAULT NULL,
  `show_profile` tinyint(1) NOT NULL DEFAULT 1,
  `show_profession` tinyint(1) NOT NULL DEFAULT 1,
  `show_skills` tinyint(1) NOT NULL DEFAULT 1,
  `show_email` tinyint(1) NOT NULL DEFAULT 0,
  `show_phone` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`alumni_id`),
  UNIQUE KEY `user_id` (`user_id`),
  UNIQUE KEY `alumni_id_number` (`college_id_number`),
  KEY `fk_alumni_department` (`department_id`),
  KEY `fk_alumni_section` (`section_id`),
  KEY `fk_alumni_specialization` (`specialization_id`),
  CONSTRAINT `fk_alumni_department` FOREIGN KEY (`department_id`) REFERENCES `departments` (`department_id`),
  CONSTRAINT `fk_alumni_section` FOREIGN KEY (`section_id`) REFERENCES `sections` (`section_id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_alumni_specialization` FOREIGN KEY (`specialization_id`) REFERENCES `specializations` (`specialization_id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_alumni_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `alumni`
--

LOCK TABLES `alumni` WRITE;
/*!40000 ALTER TABLE `alumni` DISABLE KEYS */;
INSERT INTO `alumni` VALUES (12,16,1,NULL,NULL,NULL,'it/015','Senper','Elias','female','2026-09-01','0962328402','',2018,'',NULL,1,1,1,1,1,'2026-09-03 21:56:19','2026-09-08 10:00:36'),(13,17,8,NULL,NULL,NULL,'011','blen','Elias','female','2026-09-10','','',2015,'',NULL,1,1,1,1,1,'2026-09-04 07:53:24','2026-09-04 07:53:24'),(20,33,1,1,NULL,5,'','bereket','bura','male','2026-09-23','','',2015,'',NULL,1,1,1,1,1,'2026-09-22 06:06:56','2026-09-22 06:06:56');
/*!40000 ALTER TABLE `alumni` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `alumni_registrations`
--

DROP TABLE IF EXISTS `alumni_registrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `alumni_registrations` (
  `registration_id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `email_verified_at` datetime DEFAULT NULL,
  `email_verification_token_hash` varchar(255) DEFAULT NULL,
  `email_verification_expires_at` datetime DEFAULT NULL,
  `password_hash` varchar(255) NOT NULL,
  `college_id_number` varchar(100) DEFAULT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `gender` varchar(20) NOT NULL,
  `date_of_birth` date DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `department_id` int(11) NOT NULL,
  `section_id` int(10) unsigned DEFAULT NULL,
  `specialization_id` int(10) unsigned DEFAULT NULL,
  `level` tinyint(3) unsigned DEFAULT NULL,
  `graduation_year` year(4) NOT NULL,
  `bio` text DEFAULT NULL,
  `profile_photo` varchar(255) DEFAULT NULL,
  `verification_document` varchar(255) DEFAULT NULL,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `submitted_by` int(10) unsigned DEFAULT NULL,
  `verification_notes` text DEFAULT NULL,
  `verified_by` int(11) DEFAULT NULL,
  `verified_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`registration_id`),
  KEY `fk_registration_submitted_by` (`submitted_by`),
  KEY `fk_registrations_section` (`section_id`),
  KEY `fk_registrations_specialization` (`specialization_id`),
  CONSTRAINT `fk_registration_submitted_by` FOREIGN KEY (`submitted_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_registrations_section` FOREIGN KEY (`section_id`) REFERENCES `sections` (`section_id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_registrations_specialization` FOREIGN KEY (`specialization_id`) REFERENCES `specializations` (`specialization_id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `alumni_registrations`
--

LOCK TABLES `alumni_registrations` WRITE;
/*!40000 ALTER TABLE `alumni_registrations` DISABLE KEYS */;
INSERT INTO `alumni_registrations` VALUES (9,'blen@gmail.com',NULL,NULL,NULL,'$2y$10$7gPtnUMWZUWjlSJTuuhI6O0H8/6yImGe6UUCGu18zCGllIHyui73W','011','blen','Elias','Female','2026-09-10','','',8,NULL,NULL,NULL,2015,'',NULL,NULL,'approved',NULL,NULL,8,'2026-09-04 10:53:24','2026-09-04 10:52:25','2026-09-04 10:53:24'),(10,'gedion@gmail.com',NULL,NULL,NULL,'$2y$10$CEIdwmVNJwaWWA6dpp5bze/K8VlAVFxVMV5s64gjs2AAvxpDdFYb.','013','gedion','abenezer','Female','2026-09-19','0911000000','Addis Ababa',5,NULL,NULL,NULL,2015,'',NULL,NULL,'approved',NULL,NULL,8,'2026-09-04 11:32:36','2026-09-04 11:31:59','2026-09-04 11:32:36'),(11,'yasub@gmail.com',NULL,NULL,NULL,'$2y$10$xNbX9N3Ol.lec6taXKV.6eci0qDWWAvVHpxd3geJdl3m2wwKMY9Du','087','yasub','elias','Male','2026-09-08','','',1,NULL,NULL,NULL,2014,'',NULL,NULL,'approved',NULL,NULL,8,'2026-09-04 22:26:37','2026-09-04 22:26:13','2026-09-04 22:26:37'),(12,'elodia@gmail.com',NULL,NULL,NULL,'$2y$10$XamwRC874UzbkXQtjuWZ5.bNjWiKcdDbxSug0GzfaBPffNYN04IDy','it/011','Elodia','Bekele','Female','2026-09-03','0940545337','',10,NULL,NULL,NULL,2023,'',NULL,NULL,'approved',20,NULL,8,'2026-09-07 23:00:47','2026-09-07 23:00:25','2026-09-07 23:00:47'),(13,'elias@gmail.com',NULL,NULL,NULL,'$2y$10$IdP5nYKywdIIKrHU1kuPu.AhPsFLIlHx5qfB1Vy90Ym5KFX3w7NEu','012','elias','tesfaye','Male','2026-09-10','','',1,NULL,NULL,NULL,2014,'',NULL,NULL,'approved',20,NULL,8,'2026-09-08 13:49:43','2026-09-08 13:49:29','2026-09-08 13:49:43'),(14,'eliass@gmail.com',NULL,NULL,NULL,'$2y$10$Nz.tK.tiTJ2FY/ebFDZ7vOOEiaTpvacnYOBaroFFhAvD4XVRZdyZu','0122','elias','tesfaye','Male','2026-09-10','','',9,NULL,NULL,NULL,2014,'',NULL,NULL,'rejected',20,'it doesnt match',8,'2026-09-08 14:09:45','2026-09-08 13:54:20','2026-09-08 14:09:45'),(15,'milka@gmail.com',NULL,NULL,NULL,'$2y$10$HMVd1NkQb9io8bVSBCdhcOV37cDBirxGbmMCOhGMAigdh00nX1va2',NULL,'Milka','Bekele','Female','2026-09-16','','',10,NULL,NULL,NULL,2015,'',NULL,NULL,'approved',NULL,NULL,8,'2026-09-08 15:21:49','2026-09-08 15:07:11','2026-09-08 15:21:49'),(16,'milkaa@gmail.com',NULL,NULL,NULL,'$2y$10$Z21Cf3hbhwxBJeGQJa2haufpDa.Muk0Iwc2F.PekyIMp97d68ZXIO',NULL,'Milka','Bekele','Male','2026-09-16','','',10,NULL,NULL,NULL,2015,'',NULL,NULL,'pending',NULL,NULL,NULL,NULL,'2026-09-09 14:19:56','2026-09-09 14:19:56'),(17,'edlawit@gmail.com',NULL,NULL,NULL,'$2y$10$IFPvO91eSJnfBtPrTcDCVOnR84DnWRW/k4o8YODh6N5JD4khgN7Gq',NULL,'edlawit','Bekele','Female','2026-09-23','','',10,NULL,NULL,NULL,2015,'',NULL,'885436fc4ce03104b630ef1ac575ffcc.pdf','pending',NULL,NULL,NULL,NULL,'2026-09-09 14:33:39','2026-09-09 14:33:39'),(18,'edlawitt@gmail.com',NULL,NULL,NULL,'$2y$10$0IxvHy.5bYYoUyEnnrQyFOm53M1IVDl.BF1V.ZNuws.OS3g0bBake',NULL,'edlawit','Bekele','Female','2026-09-23','','eman@gmail.com',4,NULL,NULL,NULL,2015,'',NULL,NULL,'pending',NULL,NULL,NULL,NULL,'2026-09-09 14:47:55','2026-09-09 14:47:55'),(19,'edlawitbekele@gmail.com',NULL,NULL,NULL,'$2y$10$sVY8knVVUEwP7knT08YEBe.aInggQhEeLBh.b8pxF37aTLTFYMa76',NULL,'edlawit','Bekele','Female','2026-09-23','','edbekele@gmail.com',3,NULL,NULL,NULL,2015,'',NULL,'8fc4fc544bee445173555957dbe214a8.pdf','approved',NULL,NULL,8,'2026-09-18 15:01:59','2026-09-14 12:25:33','2026-09-18 15:01:59'),(20,'bemnet@gmail.com',NULL,NULL,NULL,'$2y$10$EvhTrP9X1VH5GIcfuCPwKumpM103Tg.lQ6I7qLAFsNS.ykPKPK1Bu',NULL,'bemnet','temesgen','Female','2026-09-17','','',1,1,NULL,4,2019,'',NULL,'339971d26c98bb2ab1fc6fe6700a6810.pdf','pending',NULL,NULL,NULL,NULL,'2026-09-22 04:29:30','2026-09-22 04:29:30'),(21,'bereket@gmail.com',NULL,NULL,NULL,'$2y$10$sMSZw4hOk3hH4UbWhaydj.SlJV8Gb/6z38YqygqWFpmUSIMNSADXq','','bereket','bura','Male','2026-09-23','','',1,1,NULL,5,2015,'',NULL,NULL,'approved',31,NULL,8,'2026-09-22 09:06:56','2026-09-22 09:05:50','2026-09-22 09:06:56');
/*!40000 ALTER TABLE `alumni_registrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `announcements`
--

DROP TABLE IF EXISTS `announcements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `announcements` (
  `announcement_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(200) NOT NULL,
  `content` text NOT NULL,
  `category` enum('general','alumni','career','events','college_news') NOT NULL DEFAULT 'general',
  `audience` enum('public','alumni') NOT NULL DEFAULT 'public',
  `status` enum('draft','published','archived') NOT NULL DEFAULT 'draft',
  `created_by` int(10) unsigned NOT NULL,
  `published_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`announcement_id`),
  KEY `created_by` (`created_by`),
  CONSTRAINT `announcements_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `announcements`
--

LOCK TABLES `announcements` WRITE;
/*!40000 ALTER TABLE `announcements` DISABLE KEYS */;
/*!40000 ALTER TABLE `announcements` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `audit_logs`
--

DROP TABLE IF EXISTS `audit_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `audit_logs` (
  `audit_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `action` varchar(100) NOT NULL,
  `table_name` varchar(100) NOT NULL,
  `record_id` bigint(20) unsigned DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`audit_id`),
  KEY `idx_audit_user` (`user_id`),
  KEY `idx_audit_table_record` (`table_name`,`record_id`),
  KEY `idx_audit_created` (`created_at`),
  CONSTRAINT `audit_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=108 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `audit_logs`
--

LOCK TABLES `audit_logs` WRITE;
/*!40000 ALTER TABLE `audit_logs` DISABLE KEYS */;
INSERT INTO `audit_logs` VALUES (1,1,'TEST','users',1,'Audit logging test','2026-09-07 21:20:44'),(2,20,'RESET_PASSWORD','users',16,'Student Representative reset the password for alumni account: Senper Elias','2026-09-07 21:51:26'),(3,8,'APPROVE','alumni_registrations',13,'Registrar approved alumni registration.','2026-09-08 10:49:43'),(4,8,'REJECT','alumni_registrations',14,'Registrar rejected alumni registration. Reason: it doesnt match','2026-09-08 11:09:45'),(5,8,'APPROVE','alumni_registrations',15,'Registrar approved alumni registration.','2026-09-08 12:21:49'),(6,20,'RESET_PASSWORD','users',17,'Student Representative reset the password for alumni account: blen Elias','2026-09-08 13:22:54'),(7,20,'CREATE','users',24,'Created user account: eman@gmail.com','2026-09-08 17:34:20'),(8,20,'DEACTIVATE','users',24,'User account deactivated.','2026-09-08 17:51:03'),(9,20,'DEACTIVATE','users',24,'User account deactivated.','2026-09-08 17:51:41'),(10,20,'DEACTIVATE','users',24,'User account deactivated.','2026-09-08 17:51:43'),(11,20,'DEACTIVATE','users',23,'User account deactivated.','2026-09-08 17:56:57'),(12,20,'DEACTIVATE','users',23,'User account deactivated.','2026-09-08 17:58:01'),(13,20,'DEACTIVATE','users',23,'User account deactivated.','2026-09-08 17:58:08'),(14,20,'DEACTIVATE','users',23,'User account deactivated.','2026-09-08 17:58:11'),(15,20,'DEACTIVATE','users',23,'User account deactivated.','2026-09-08 17:58:20'),(16,20,'DEACTIVATE','users',24,'User account deactivated.','2026-09-08 17:58:34'),(17,20,'DEACTIVATE','users',23,'User account deactivated.','2026-09-08 17:58:54'),(18,20,'ACTIVATE','users',23,'User account activated.','2026-09-08 18:00:31'),(19,20,'ACTIVATE','users',24,'User account activated.','2026-09-08 18:45:01'),(20,20,'UPDATE','users',24,'User role changed from alumni to Registrar.','2026-09-08 18:56:28'),(21,20,'DEACTIVATE','users',24,'User account deactivated.','2026-09-08 18:56:34'),(22,20,'UPDATE','users',24,'User role changed from Registrar to Alumni President.','2026-09-08 19:03:30'),(23,20,'CREATE','users',25,'Created user account: emann@gmail.com','2026-09-08 19:45:03'),(24,20,'DEACTIVATE','users',25,'User account deactivated.','2026-09-08 19:46:16'),(25,20,'ACTIVATE','users',25,'User account activated.','2026-09-08 19:46:19'),(26,20,'CREATE','users',26,'Created user account: emannn@gmail.com','2026-09-08 19:46:40'),(27,20,'CREATE','users',27,'Created user account: mita@gmail.com','2026-09-08 20:00:07'),(28,20,'UPDATE','users',27,'User role changed from Registrar to Alumni President.','2026-09-08 20:02:41'),(29,20,'UPDATE','users',27,'System Administrator reset the user\'s password. A temporary password was generated and the user is required to change it at next login.','2026-09-08 20:15:19'),(30,20,'UPDATE','users',27,'System Administrator reset the user\'s password. A temporary password was generated and the user is required to change it at next login.','2026-09-08 20:15:54'),(31,20,'UPDATE','users',27,'System Administrator reset the user\'s password. A temporary password was generated and the user is required to change it at next login.','2026-09-08 20:22:52'),(32,20,'UPDATE','users',27,'System Administrator reset the user\'s password. A temporary password was generated and the user is required to change it at next login.','2026-09-08 20:23:35'),(33,20,'UPDATE','users',27,'System Administrator reset the user\'s password. A temporary password was generated and the user is required to change it at next login.','2026-09-08 20:24:10'),(34,20,'UPDATE','users',27,'System Administrator reset the user\'s password. A temporary password was generated and the user is required to change it at next login.','2026-09-08 20:25:38'),(35,20,'UPDATE','users',27,'System Administrator reset the user\'s password. A temporary password was generated and the user is required to change it at next login.','2026-09-08 20:27:04'),(36,20,'UPDATE','users',27,'System Administrator reset the user\'s password. A temporary password was generated and the user is required to change it at next login.','2026-09-08 20:28:52'),(37,20,'UPDATE','users',27,'System Administrator reset the user\'s password. A temporary password was generated and the user is required to change it at next login.','2026-09-08 20:30:05'),(38,20,'UPDATE','users',27,'System Administrator reset the user\'s password. A temporary password was generated and the user is required to change it at next login.','2026-09-08 20:34:31'),(39,20,'UPDATE','users',27,'System Administrator reset the user\'s password. A temporary password was generated and the user is required to change it at next login.','2026-09-08 20:35:28'),(40,20,'UPDATE','users',27,'System Administrator reset the user\'s password. A temporary password was generated and the user is required to change it at next login.','2026-09-08 20:37:03'),(41,20,'UPDATE','users',27,'System Administrator reset the user\'s password. A temporary password was generated and the user is required to change it at next login.','2026-09-08 20:39:35'),(42,20,'UPDATE','users',27,'System Administrator reset the user\'s password. A temporary password was generated and the user is required to change it at next login.','2026-09-08 20:40:43'),(43,20,'DEACTIVATE','users',27,'User account deactivated.','2026-09-08 22:03:29'),(44,20,'UPDATE','users',21,'System Administrator reset the user\'s password. A temporary password was generated and the user is required to change it at next login.','2026-09-14 14:27:59'),(45,20,'CREATE','users',29,'Created user account: pwd@gmail.com','2026-09-14 20:56:51'),(46,8,'APPROVE','alumni_registrations',19,'Registrar approved alumni registration.','2026-09-18 12:01:59'),(47,20,'DEACTIVATE','users',30,'User account deactivated.','2026-09-18 12:04:14'),(48,20,'UPDATE','users',1,'User role changed from Alumni President to System Administrator.','2026-09-18 12:05:04'),(49,1,'ACTIVATE','users',30,'User account activated.','2026-09-18 12:13:53'),(50,1,'CREATE','users',31,'Created user account: almuniadmin@gmail.com','2026-09-18 12:20:03'),(51,1,'CREATE','users',32,'Created user account: almunipres@gmail.com','2026-09-18 12:23:19'),(52,8,'APPROVE','alumni_registrations',21,'Registrar approved alumni registration.','2026-09-22 06:06:56'),(53,20,'UPDATE','users',33,'System Administrator reset the user\'s password. A temporary password was generated and the user is required to change it at next login.','2026-09-25 14:33:47'),(54,20,'UPDATE','users',17,'System Administrator reset the user\'s password. A temporary password was generated and the user is required to change it at next login.','2026-09-25 20:51:29'),(55,20,'UPDATE','users',17,'System Administrator reset the user\'s password. A temporary password was generated and the user is required to change it at next login.','2026-09-25 20:52:46'),(56,20,'CREATE','users',34,'Created user account: test@gmail.com','2026-09-25 20:55:42'),(57,20,'CREATE','users',35,'Created user account: testt@gmail.com','2026-09-25 20:57:49'),(58,20,'UPDATE','users',35,'System Administrator reset the user\'s password. A temporary password was generated and the user is required to change it at next login.','2026-09-25 21:03:41'),(59,20,'UPDATE','users',35,'System Administrator reset the user\'s password. A temporary password was generated and the user is required to change it at next login.','2026-09-25 21:06:44'),(60,20,'UPDATE','users',35,'System Administrator reset the user\'s password. A temporary password was generated and the user is required to change it at next login.','2026-09-25 21:07:31'),(61,20,'UPDATE','users',34,'System Administrator reset the user\'s password. A temporary password was generated and the user is required to change it at next login.','2026-09-25 21:08:08'),(62,20,'CREATE','users',36,'Created user account: dave@gmail.com','2026-09-25 21:20:55'),(63,20,'CREATE','users',37,'Created user account: system@gamil.com','2026-09-27 12:09:46'),(64,20,'UPDATE','users',37,'System Administrator reset the user\'s password. A temporary password was generated and the user is required to change it at next login.','2026-09-27 12:20:26'),(65,20,'LOGIN_SUCCESS','users',20,'User logged in successfully.','2026-09-27 13:42:21'),(66,17,'LOGIN_FAILED','users',17,'Failed login attempt.','2026-09-27 15:08:29'),(67,17,'LOGIN_FAILED','users',17,'Failed login attempt.','2026-09-27 15:08:46'),(68,20,'LOGIN_SUCCESS','users',20,'User logged in successfully.','2026-09-27 15:08:51'),(69,33,'LOGIN_FAILED','users',33,'Failed login attempt.','2026-09-27 15:09:21'),(70,20,'LOGIN_SUCCESS','users',20,'User logged in successfully.','2026-09-27 15:09:32'),(71,20,'LOGIN_SUCCESS','users',20,'User logged in successfully.','2026-09-27 15:09:33'),(72,33,'LOGIN_FAILED','users',33,'Failed login attempt.','2026-09-27 15:09:57'),(73,8,'LOGIN_SUCCESS','users',8,'User logged in successfully.','2026-09-27 15:10:36'),(74,32,'LOGIN_SUCCESS','users',32,'User logged in successfully.','2026-09-27 15:11:29'),(75,20,'LOGIN_SUCCESS','users',20,'User logged in successfully.','2026-09-27 15:56:22'),(76,32,'LOGIN_FAILED','users',32,'Failed login attempt.','2026-09-27 15:57:00'),(77,20,'LOGIN_SUCCESS','users',20,'User logged in successfully.','2026-09-27 15:57:04'),(78,20,'LOGIN_SUCCESS','users',20,'User logged in successfully.','2026-09-27 16:00:36'),(79,20,'LOGIN_SUCCESS','users',20,'User logged in successfully.','2026-09-27 16:19:38'),(80,20,'LOGIN_SUCCESS','users',20,'User logged in successfully.','2026-09-27 16:27:29'),(81,20,'LOGIN_SUCCESS','users',20,'User logged in successfully.','2026-09-27 20:05:05'),(82,20,'LOGIN_SUCCESS','users',20,'User logged in successfully.','2026-09-28 06:08:50'),(83,20,'UPDATE','system_settings',0,'System Administrator updated system configuration.','2026-09-28 07:13:08'),(84,20,'UPDATE','system_settings',0,'System Administrator updated system configuration.','2026-09-28 07:14:24'),(85,20,'UPDATE','system_settings',0,'System Administrator updated system and security configuration.','2026-09-28 07:31:00'),(86,20,'UPDATE','system_settings',0,'System Administrator updated system and security configuration.','2026-09-28 07:32:50'),(87,20,'LOGIN_SUCCESS','users',20,'User logged in successfully.','2026-09-28 07:33:30'),(88,20,'UPDATE','system_settings',0,'System Administrator updated security policies.','2026-09-28 09:09:29'),(89,20,'UPDATE','system_settings',0,'System Administrator updated security policies.','2026-09-28 09:14:04'),(90,20,'UPDATE','system_settings',0,'System Administrator updated security policies.','2026-09-28 09:14:08'),(91,20,'LOGIN_SUCCESS','users',20,'User logged in successfully.','2026-09-28 09:14:16'),(92,20,'LOGIN_FAILED','users',20,'Failed login attempt.','2026-09-28 09:19:59'),(93,20,'LOGIN_FAILED','users',20,'Failed login attempt.','2026-09-28 09:20:00'),(94,20,'LOGIN_FAILED','users',20,'Failed login attempt.','2026-09-28 09:20:03'),(95,20,'LOGIN_FAILED','users',20,'Failed login attempt.','2026-09-28 09:20:07'),(96,20,'ACCOUNT_LOCKED','users',20,'Account locked after 5 consecutive failed login attempts.','2026-09-28 09:20:11'),(97,16,'LOGIN_SUCCESS','users',16,'User logged in successfully.','2026-09-28 09:21:11'),(98,20,'LOGIN_SUCCESS','users',20,'User logged in successfully.','2026-09-28 09:40:26'),(99,20,'DELETE','database_backup',0,'System Administrator deleted database backup: alumni_management_backup_2026-09-28_11-50-40.sql','2026-09-28 09:52:32'),(100,20,'DELETE','database_backup',0,'System Administrator deleted database backup: alumni_management_backup_2026-09-28_11-52-30.sql','2026-09-28 09:52:37'),(101,20,'DELETE','database_backup',0,'System Administrator deleted database backup: alumni_management_backup_2026-09-28_11-42-23.sql','2026-09-28 09:52:43'),(102,20,'DELETE','database_backup',0,'System Administrator deleted database backup: alumni_management_backup_2026-09-28_11-53-26.sql','2026-09-28 10:00:37'),(103,20,'DELETE','database_backup',0,'System Administrator deleted database backup: alumni_management_backup_2026-09-28_12-01-54.sql','2026-09-28 10:02:00'),(104,20,'CREATE','database_backup',0,'System Administrator created database backup: alumni_management_backup_2026-09-28_15-07-51.sql','2026-09-28 13:07:51'),(105,20,'CREATE','database_backup',0,'System Administrator created database backup: alumni_management_backup_2026-09-28_15-07-56.sql','2026-09-28 13:08:00'),(106,20,'DELETE','database_backup',0,'System Administrator deleted database backup: alumni_management_backup_2026-09-28_15-07-56.sql','2026-09-28 13:09:28'),(107,20,'DELETE','database_backup',0,'System Administrator deleted database backup: alumni_management_backup_2026-09-28_15-07-51.sql','2026-09-28 13:09:31');
/*!40000 ALTER TABLE `audit_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `contact_inquiries`
--

DROP TABLE IF EXISTS `contact_inquiries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `contact_inquiries` (
  `inquiry_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `subject` varchar(200) NOT NULL,
  `message` text NOT NULL,
  `status` enum('new','read','responded','closed') NOT NULL DEFAULT 'new',
  `admin_response` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `responded_at` datetime DEFAULT NULL,
  PRIMARY KEY (`inquiry_id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `contact_inquiries`
--

LOCK TABLES `contact_inquiries` WRITE;
/*!40000 ALTER TABLE `contact_inquiries` DISABLE KEYS */;
INSERT INTO `contact_inquiries` VALUES (8,'senper','bemnet@gmail.com','heyyyy','heyyyyyy','responded','heyyyy','2026-09-04 07:49:11','2026-09-04 10:49:44'),(9,'gedion','gedion@gmail.com','issue registeribg','i cant register','closed','........','2026-09-04 08:49:19','2026-09-04 11:49:44');
/*!40000 ALTER TABLE `contact_inquiries` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `contributions`
--

DROP TABLE IF EXISTS `contributions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `contributions` (
  `contribution_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `alumni_id` int(10) unsigned NOT NULL,
  `contribution_type` enum('financial_donation','equipment_donation','training_support','internship_support','other') NOT NULL,
  `description` text DEFAULT NULL,
  `amount` decimal(12,2) DEFAULT NULL,
  `contribution_date` date DEFAULT NULL,
  `purpose` varchar(255) DEFAULT NULL,
  `status` enum('pending','verified','rejected') NOT NULL DEFAULT 'pending',
  `verified_by` int(10) unsigned DEFAULT NULL,
  `verified_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`contribution_id`),
  KEY `alumni_id` (`alumni_id`),
  KEY `verified_by` (`verified_by`),
  CONSTRAINT `contributions_ibfk_1` FOREIGN KEY (`alumni_id`) REFERENCES `alumni` (`alumni_id`) ON DELETE CASCADE,
  CONSTRAINT `contributions_ibfk_2` FOREIGN KEY (`verified_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `contributions`
--

LOCK TABLES `contributions` WRITE;
/*!40000 ALTER TABLE `contributions` DISABLE KEYS */;
/*!40000 ALTER TABLE `contributions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `departments`
--

DROP TABLE IF EXISTS `departments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `departments` (
  `department_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `department_name` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`department_id`),
  UNIQUE KEY `department_name` (`department_name`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `departments`
--

LOCK TABLES `departments` WRITE;
/*!40000 ALTER TABLE `departments` DISABLE KEYS */;
INSERT INTO `departments` VALUES (1,'Information and Communication Technology (ICT)',NULL,'active','2026-07-30 20:27:07'),(2,'Business and Finance',NULL,'active','2026-07-30 20:27:07'),(3,'Electrical and Electronics Technology',NULL,'active','2026-07-30 20:27:07'),(4,'Automotive Technology',NULL,'active','2026-07-30 20:27:07'),(5,'Construction Technology',NULL,'active','2026-07-30 20:27:07'),(6,'Drafting and Surveying',NULL,'active','2026-07-30 20:27:07'),(7,'Textile Technology',NULL,'active','2026-07-30 20:27:07'),(8,'Hotel and Tourism Management',NULL,'active','2026-07-30 20:27:07'),(9,'Aesthetics and Beauty Technology',NULL,'active','2026-07-30 20:27:07'),(10,'Metal Manufacturing Technology',NULL,'active','2026-07-30 20:27:07');
/*!40000 ALTER TABLE `departments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `employment`
--

DROP TABLE IF EXISTS `employment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `employment` (
  `employment_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `alumni_id` int(10) unsigned NOT NULL,
  `employment_status` enum('employed','unemployed','self_employed','continuing_education') NOT NULL,
  `company_name` varchar(150) DEFAULT NULL,
  `job_position` varchar(150) DEFAULT NULL,
  `work_location` varchar(150) DEFAULT NULL,
  `industry` varchar(150) DEFAULT NULL,
  `education_institution` varchar(255) DEFAULT NULL,
  `education_program` varchar(255) DEFAULT NULL,
  `education_level` varchar(100) DEFAULT NULL,
  `expected_completion_date` date DEFAULT NULL,
  `employment_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `verification_status` enum('self_reported','pending','verified','rejected') NOT NULL DEFAULT 'self_reported',
  `verification_notes` text DEFAULT NULL,
  `verification_document` varchar(255) DEFAULT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`employment_id`),
  KEY `fk_employment_updated_by` (`updated_by`),
  KEY `idx_employment_status` (`employment_status`),
  KEY `idx_employment_alumni` (`alumni_id`),
  CONSTRAINT `fk_employment_alumni` FOREIGN KEY (`alumni_id`) REFERENCES `alumni` (`alumni_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_employment_updated_by` FOREIGN KEY (`updated_by`) REFERENCES `users` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=40 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `employment`
--

LOCK TABLES `employment` WRITE;
/*!40000 ALTER TABLE `employment` DISABLE KEYS */;
/*!40000 ALTER TABLE `employment` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `event_registrations`
--

DROP TABLE IF EXISTS `event_registrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `event_registrations` (
  `registration_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `event_id` int(10) unsigned NOT NULL,
  `alumni_id` int(10) unsigned NOT NULL,
  `registration_status` enum('registered','cancelled','attended','absent') NOT NULL DEFAULT 'registered',
  `registered_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `cancelled_at` datetime DEFAULT NULL,
  PRIMARY KEY (`registration_id`),
  UNIQUE KEY `unique_event_registration` (`event_id`,`alumni_id`),
  KEY `alumni_id` (`alumni_id`),
  CONSTRAINT `event_registrations_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `events` (`event_id`) ON DELETE CASCADE,
  CONSTRAINT `event_registrations_ibfk_2` FOREIGN KEY (`alumni_id`) REFERENCES `alumni` (`alumni_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `event_registrations`
--

LOCK TABLES `event_registrations` WRITE;
/*!40000 ALTER TABLE `event_registrations` DISABLE KEYS */;
INSERT INTO `event_registrations` VALUES (18,15,13,'cancelled','2026-09-04 08:10:13','2026-09-14 17:32:54');
/*!40000 ALTER TABLE `event_registrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `events`
--

DROP TABLE IF EXISTS `events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `events` (
  `event_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(200) NOT NULL,
  `description` text NOT NULL,
  `event_date` date NOT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `location` varchar(200) DEFAULT NULL,
  `registration_deadline` date DEFAULT NULL,
  `max_capacity` int(10) unsigned DEFAULT NULL,
  `status` enum('draft','published','completed','cancelled') NOT NULL DEFAULT 'draft',
  `created_by` int(10) unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`event_id`),
  KEY `created_by` (`created_by`),
  CONSTRAINT `events_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `events`
--

LOCK TABLES `events` WRITE;
/*!40000 ALTER TABLE `events` DISABLE KEYS */;
INSERT INTO `events` VALUES (15,'job fair','','2026-09-15','04:06:00','11:12:00','tms college','2026-09-09',1,'published',1,'2026-09-04 08:07:47','2026-09-04 08:07:47');
/*!40000 ALTER TABLE `events` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `gallery`
--

DROP TABLE IF EXISTS `gallery`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gallery` (
  `gallery_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `image_path` varchar(255) NOT NULL,
  `status` enum('draft','published','archived') NOT NULL DEFAULT 'draft',
  `uploaded_by` int(10) unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`gallery_id`),
  KEY `uploaded_by` (`uploaded_by`),
  CONSTRAINT `gallery_ibfk_1` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `gallery`
--

LOCK TABLES `gallery` WRITE;
/*!40000 ALTER TABLE `gallery` DISABLE KEYS */;
INSERT INTO `gallery` VALUES (1,'college alumni event ','alumni gathering community ','','archived',1,'2026-08-29 19:58:31','2026-08-29 19:59:57'),(2,'tms college','a photo','data:image/webp;base64,UklGRhI6AABXRUJQVlA4IAY6AAAQ8ACdASp3AQ4BPp1Cm0mlo6ItKxXsoaATiU29eRXAGyOIwP1IFG4bm/5uf7Ty6uS/LeMRhVdHZv/tPqk/LH1A7iDzg+aVzx3Xo+jB02lqItlddP5P+K9qz91zR9s2pr4L51v77wd+ZuoW+D8t/wfQU97/vfni/jeb38R/tvYD80vBy/Heob5RP/F5fv2P/m+wp03UO0cngtd5','published',1,'2026-08-30 22:27:16','2026-08-30 22:32:49');
/*!40000 ALTER TABLE `gallery` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mentor_profiles`
--

DROP TABLE IF EXISTS `mentor_profiles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mentor_profiles` (
  `mentor_profile_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `alumni_id` int(10) unsigned NOT NULL,
  `expertise` varchar(255) DEFAULT NULL,
  `skills` text DEFAULT NULL,
  `experience_years` int(10) unsigned DEFAULT 0,
  `biography` text DEFAULT NULL,
  `availability` varchar(150) DEFAULT NULL,
  `status` enum('active','inactive','pending') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`mentor_profile_id`),
  UNIQUE KEY `alumni_id` (`alumni_id`),
  CONSTRAINT `mentor_profiles_ibfk_1` FOREIGN KEY (`alumni_id`) REFERENCES `alumni` (`alumni_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mentor_profiles`
--

LOCK TABLES `mentor_profiles` WRITE;
/*!40000 ALTER TABLE `mentor_profiles` DISABLE KEYS */;
/*!40000 ALTER TABLE `mentor_profiles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mentorship_requests`
--

DROP TABLE IF EXISTS `mentorship_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mentorship_requests` (
  `request_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `mentor_id` int(10) unsigned NOT NULL,
  `mentee_id` int(10) unsigned NOT NULL,
  `message` text DEFAULT NULL,
  `status` enum('pending','accepted','rejected','completed','cancelled') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `responded_at` datetime DEFAULT NULL,
  PRIMARY KEY (`request_id`),
  KEY `idx_mentor_requests_mentor` (`mentor_id`),
  KEY `idx_mentor_requests_mentee` (`mentee_id`),
  CONSTRAINT `mentorship_requests_ibfk_1` FOREIGN KEY (`mentor_id`) REFERENCES `alumni` (`alumni_id`) ON DELETE CASCADE,
  CONSTRAINT `mentorship_requests_ibfk_2` FOREIGN KEY (`mentee_id`) REFERENCES `alumni` (`alumni_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mentorship_requests`
--

LOCK TABLES `mentorship_requests` WRITE;
/*!40000 ALTER TABLE `mentorship_requests` DISABLE KEYS */;
/*!40000 ALTER TABLE `mentorship_requests` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `notifications` (
  `notification_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `title` varchar(200) NOT NULL,
  `message` text NOT NULL,
  `type` varchar(50) DEFAULT NULL,
  `opportunity_id` int(11) DEFAULT NULL,
  `event_id` int(11) DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`notification_id`),
  KEY `idx_notifications_user` (`user_id`,`is_read`),
  CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=194 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notifications`
--

LOCK TABLES `notifications` WRITE;
/*!40000 ALTER TABLE `notifications` DISABLE KEYS */;
INSERT INTO `notifications` VALUES (174,16,'Employment Information Verified','Your employment information has been verified by the administrator.','employment',NULL,NULL,0,'2026-09-03 22:02:08'),(175,17,'Registration Approved','Your alumni registration has been approved. You can now log in to your alumni account.','system',NULL,NULL,1,'2026-09-04 07:53:24'),(177,16,'New Training Opportunity','A new training opportunity \"Graphics designing\" is now available.','opportunity',22,NULL,0,'2026-09-04 08:05:55'),(178,17,'New Training Opportunity','A new training opportunity \"Graphics designing\" is now available.','opportunity',22,NULL,1,'2026-09-04 08:05:55'),(180,16,'New Event','A new event \"job fair\" has been published.','event',NULL,15,0,'2026-09-04 08:07:47'),(181,17,'New Event','A new event \"job fair\" has been published.','event',NULL,15,1,'2026-09-04 08:07:47'),(186,16,'Employment Information Verified','Your employment information has been verified by the administrator.','employment',NULL,NULL,0,'2026-09-08 10:15:11'),(189,17,'Employment Information Rejected','Your employment information was rejected. Reason: Employment information rejected by admin.','employment',NULL,NULL,0,'2026-09-14 21:31:18'),(190,17,'Employment Information Rejected','Your employment information was rejected. Reason: Employment information rejected by admin.','employment',NULL,NULL,0,'2026-09-14 21:33:12'),(191,17,'Employment Information Rejected','Your employment information was rejected. Reason: Employment information rejected by admin.','employment',NULL,NULL,0,'2026-09-14 21:33:35'),(193,33,'Registration Approved','Your alumni registration has been approved. You can now log in to your alumni account.','system',NULL,NULL,0,'2026-09-22 06:06:56');
/*!40000 ALTER TABLE `notifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `opportunities`
--

DROP TABLE IF EXISTS `opportunities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `opportunities` (
  `opportunity_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `created_by` int(10) unsigned NOT NULL,
  `type` enum('job','internship','training') NOT NULL,
  `title` varchar(200) NOT NULL,
  `company_name` varchar(150) DEFAULT NULL,
  `description` text NOT NULL,
  `requirements` text DEFAULT NULL,
  `location` varchar(150) DEFAULT NULL,
  `contact_information` text DEFAULT NULL,
  `deadline` date DEFAULT NULL,
  `status` enum('draft','pending','approved','rejected','expired') NOT NULL DEFAULT 'pending',
  `reviewed_by` int(10) unsigned DEFAULT NULL,
  `reviewed_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`opportunity_id`),
  KEY `created_by` (`created_by`),
  KEY `reviewed_by` (`reviewed_by`),
  CONSTRAINT `opportunities_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`user_id`),
  CONSTRAINT `opportunities_ibfk_2` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `opportunities`
--

LOCK TABLES `opportunities` WRITE;
/*!40000 ALTER TABLE `opportunities` DISABLE KEYS */;
INSERT INTO `opportunities` VALUES (22,1,'training','Graphics designing','tms college','we are giving a 3 month training on graphics design','willing to learn','addis abeba tms college','','2026-09-25','approved',1,'2026-09-04 11:05:55','2026-09-04 08:05:36','2026-09-04 08:05:55');
/*!40000 ALTER TABLE `opportunities` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `project_members`
--

DROP TABLE IF EXISTS `project_members`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `project_members` (
  `project_member_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `project_id` int(10) unsigned NOT NULL,
  `alumni_id` int(10) unsigned NOT NULL,
  `role` varchar(100) DEFAULT NULL,
  `status` enum('invited','active','left') NOT NULL DEFAULT 'invited',
  `joined_at` datetime DEFAULT NULL,
  PRIMARY KEY (`project_member_id`),
  UNIQUE KEY `unique_project_member` (`project_id`,`alumni_id`),
  KEY `alumni_id` (`alumni_id`),
  CONSTRAINT `project_members_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `projects` (`project_id`) ON DELETE CASCADE,
  CONSTRAINT `project_members_ibfk_2` FOREIGN KEY (`alumni_id`) REFERENCES `alumni` (`alumni_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `project_members`
--

LOCK TABLES `project_members` WRITE;
/*!40000 ALTER TABLE `project_members` DISABLE KEYS */;
/*!40000 ALTER TABLE `project_members` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `projects`
--

DROP TABLE IF EXISTS `projects`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `projects` (
  `project_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `created_by` int(10) unsigned NOT NULL,
  `title` varchar(200) NOT NULL,
  `category` varchar(100) DEFAULT NULL,
  `description` text NOT NULL,
  `proposal_document` varchar(255) DEFAULT NULL,
  `required_skills` text DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `status` enum('draft','active','completed','cancelled') NOT NULL DEFAULT 'draft',
  `approval_status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `reviewed_by` int(10) unsigned DEFAULT NULL,
  `reviewed_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`project_id`),
  KEY `created_by` (`created_by`),
  KEY `reviewed_by` (`reviewed_by`),
  CONSTRAINT `projects_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`user_id`),
  CONSTRAINT `projects_ibfk_2` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `projects`
--

LOCK TABLES `projects` WRITE;
/*!40000 ALTER TABLE `projects` DISABLE KEYS */;
INSERT INTO `projects` VALUES (3,17,'building a system for registrar','software','we are thinking to build a registrartion system for tafari mekonnen college',NULL,'senior developers','2026-09-05','2026-09-25','draft','approved',1,'2026-09-04 11:08:23','2026-09-04 07:58:55','2026-09-04 08:08:23'),(4,17,'djsnklf','hhsdbsh','dchgd','e1eaffaf53a6f7b3ff455c461e403463.pdf','php','2026-09-25','2026-09-30','active','pending',NULL,NULL,'2026-09-18 11:30:13','2026-09-18 11:30:13'),(5,17,'alumni carrer mentot ship','web development','php javascript','f2214a47180c239b2071b94634ee9792.pdf','php','2026-09-04','2026-09-17','draft','approved',32,'2026-09-18 15:24:44','2026-09-18 12:00:36','2026-09-18 12:24:44'),(6,17,'jobfair','web development','heyy there wanna come','aa67bea9e5a957f53f7eb24641a839a7.pdf','php,mysql','2026-09-22','2026-09-26','active','approved',32,'2026-09-22 01:43:25','2026-09-21 22:09:19','2026-09-21 22:43:25');
/*!40000 ALTER TABLE `projects` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sections`
--

DROP TABLE IF EXISTS `sections`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sections` (
  `section_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `department_id` int(10) unsigned NOT NULL,
  `section_name` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`section_id`),
  UNIQUE KEY `uq_department_section` (`department_id`,`section_name`),
  KEY `idx_sections_department` (`department_id`),
  CONSTRAINT `fk_sections_department` FOREIGN KEY (`department_id`) REFERENCES `departments` (`department_id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=38 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sections`
--

LOCK TABLES `sections` WRITE;
/*!40000 ALTER TABLE `sections` DISABLE KEYS */;
INSERT INTO `sections` VALUES (1,1,'Web Development and Database Administration',NULL,'active','2026-09-22 00:06:14'),(2,1,'Hardware and Networking',NULL,'active','2026-09-22 00:06:14'),(3,2,'Accounting and Finance',NULL,'active','2026-09-22 00:06:14'),(4,2,'Marketing and Sales Management',NULL,'active','2026-09-22 00:06:14'),(5,2,'Secretarial and Office Administration',NULL,'active','2026-09-22 00:06:14'),(6,3,'Industrial Electrical, Electronics and Control',NULL,'active','2026-09-22 00:06:14'),(7,3,'Building Electrical Installation',NULL,'active','2026-09-22 00:06:14'),(8,3,'Electrical and Electronics Equipment Service',NULL,'active','2026-09-22 00:06:14'),(9,4,'Automotive Mechanics',NULL,'active','2026-09-22 00:06:14'),(10,4,'Automotive Electrical and Electronics',NULL,'active','2026-09-22 00:06:14'),(11,5,'Structural Construction Works',NULL,'active','2026-09-22 00:06:14'),(12,5,'Finishing Construction Works',NULL,'active','2026-09-22 00:06:14'),(13,5,'Installation Construction Works',NULL,'active','2026-09-22 00:06:14'),(14,5,'Road Construction Works',NULL,'active','2026-09-22 00:06:14'),(15,5,'On-Site Structural Construction',NULL,'active','2026-09-22 00:06:14'),(16,5,'On-Site Finishing Construction',NULL,'active','2026-09-22 00:06:14'),(17,5,'On-Site Installation Construction',NULL,'active','2026-09-22 00:06:14'),(18,5,'On-Site Road Construction',NULL,'active','2026-09-22 00:06:14'),(19,5,'Building Construction Management',NULL,'active','2026-09-22 00:06:14'),(20,5,'Road Construction Management',NULL,'active','2026-09-22 00:06:14'),(21,7,'Garment',NULL,'active','2026-09-22 00:06:14'),(22,7,'Textile',NULL,'active','2026-09-22 00:06:14'),(23,7,'Leather Products',NULL,'active','2026-09-22 00:06:14'),(24,8,'Food and Beverage Service',NULL,'active','2026-09-22 00:06:14'),(25,8,'Front Office Service',NULL,'active','2026-09-22 00:06:14'),(26,8,'Food and Beverage Control',NULL,'active','2026-09-22 00:06:14'),(27,8,'Housekeeping and Laundry Service',NULL,'active','2026-09-22 00:06:14'),(28,8,'Tour Guiding',NULL,'active','2026-09-22 00:06:14'),(29,8,'Tour Operation',NULL,'active','2026-09-22 00:06:14'),(30,8,'Confectionery, Baking and Pastry Making',NULL,'active','2026-09-22 00:06:14'),(31,8,'Culinary Art / Food Preparation',NULL,'active','2026-09-22 00:06:14'),(32,8,'Hair Dressing and Beautification',NULL,'active','2026-09-22 00:06:14'),(33,9,'Music Instrument Playing',NULL,'active','2026-09-22 00:06:14'),(34,9,'Vocal Performance',NULL,'active','2026-09-22 00:06:14'),(35,9,'Fine Arts',NULL,'active','2026-09-22 00:06:14'),(36,9,'Theatrical Arts',NULL,'active','2026-09-22 00:06:14'),(37,9,'Sport',NULL,'active','2026-09-22 00:06:14');
/*!40000 ALTER TABLE `sections` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `specializations`
--

DROP TABLE IF EXISTS `specializations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `specializations` (
  `specialization_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `section_id` int(10) unsigned NOT NULL,
  `specialization_name` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`specialization_id`),
  UNIQUE KEY `uq_section_specialization` (`section_id`,`specialization_name`),
  KEY `idx_specializations_section` (`section_id`),
  CONSTRAINT `fk_specializations_section` FOREIGN KEY (`section_id`) REFERENCES `sections` (`section_id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `specializations`
--

LOCK TABLES `specializations` WRITE;
/*!40000 ALTER TABLE `specializations` DISABLE KEYS */;
INSERT INTO `specializations` VALUES (1,33,'Keyboard / Piano',NULL,'active','2026-09-22 00:09:47'),(2,33,'Tenor and Alto Saxophone',NULL,'active','2026-09-22 00:09:47'),(3,33,'Drum',NULL,'active','2026-09-22 00:09:47'),(4,33,'Bass Guitar',NULL,'active','2026-09-22 00:09:47'),(5,33,'Lead Guitar',NULL,'active','2026-09-22 00:09:47'),(6,33,'Trumpet',NULL,'active','2026-09-22 00:09:47'),(7,33,'Flute',NULL,'active','2026-09-22 00:09:47'),(8,33,'Kirrar',NULL,'active','2026-09-22 00:09:47'),(9,33,'Masinko',NULL,'active','2026-09-22 00:09:47'),(10,35,'Graphics Design',NULL,'active','2026-09-22 00:09:47'),(11,35,'Painting',NULL,'active','2026-09-22 00:09:47'),(12,35,'Sculpture',NULL,'active','2026-09-22 00:09:47'),(13,36,'Acting',NULL,'active','2026-09-22 00:09:47'),(14,36,'Directing',NULL,'active','2026-09-22 00:09:47'),(15,36,'Play Writing',NULL,'active','2026-09-22 00:09:47'),(16,37,'Athletics',NULL,'active','2026-09-22 00:09:47'),(17,37,'Middle Distance Running',NULL,'active','2026-09-22 00:09:47'),(18,37,'Football Coaching',NULL,'active','2026-09-22 00:09:47');
/*!40000 ALTER TABLE `specializations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `success_stories`
--

DROP TABLE IF EXISTS `success_stories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `success_stories` (
  `story_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `alumni_id` int(10) unsigned NOT NULL,
  `title` varchar(200) NOT NULL,
  `achievement` varchar(255) DEFAULT NULL,
  `story_content` text NOT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `reviewed_by` int(10) unsigned DEFAULT NULL,
  `reviewed_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`story_id`),
  KEY `alumni_id` (`alumni_id`),
  KEY `reviewed_by` (`reviewed_by`),
  CONSTRAINT `success_stories_ibfk_1` FOREIGN KEY (`alumni_id`) REFERENCES `alumni` (`alumni_id`) ON DELETE CASCADE,
  CONSTRAINT `success_stories_ibfk_2` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `success_stories`
--

LOCK TABLES `success_stories` WRITE;
/*!40000 ALTER TABLE `success_stories` DISABLE KEYS */;
/*!40000 ALTER TABLE `success_stories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `system_settings`
--

DROP TABLE IF EXISTS `system_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `system_settings` (
  `setting_id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `updated_by` int(10) unsigned DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`setting_id`),
  UNIQUE KEY `setting_key` (`setting_key`),
  KEY `fk_system_settings_user` (`updated_by`),
  CONSTRAINT `fk_system_settings_user` FOREIGN KEY (`updated_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `system_settings`
--

LOCK TABLES `system_settings` WRITE;
/*!40000 ALTER TABLE `system_settings` DISABLE KEYS */;
INSERT INTO `system_settings` VALUES (1,'site_name','Alumni Management System',20,'2026-09-28 07:13:08'),(2,'institution_name','Taferi Mekonnen Polytechnic Technical College',20,'2026-09-28 07:13:08'),(3,'site_email','tms@college.edu',20,'2026-09-28 07:14:24'),(10,'session_timeout','5',20,'2026-09-28 09:14:08'),(11,'max_failed_login_attempts','5',20,'2026-09-28 07:31:00'),(12,'lockout_duration','5',20,'2026-09-28 09:23:15');
/*!40000 ALTER TABLE `system_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `user_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(150) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('admin','alumni','registrar','student_rep','system_admin') NOT NULL,
  `account_status` enum('active','inactive','suspended') NOT NULL DEFAULT 'active',
  `must_change_password` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `failed_login_attempts` int(11) NOT NULL DEFAULT 0,
  `locked_until` datetime DEFAULT NULL,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=38 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'admin@college.edu','$2y$10$FjH5UXYChCioa381e0Cg9udRmH.r/0f3O1Sk8f01SMhVr40VXoh5C','system_admin','active',0,'2026-07-31 18:08:18','2026-09-18 12:05:04',0,NULL),(8,'registrar@gmail.com','$2y$10$vSs8Shb2rS22mJ1nF.fANetrS06mvnUUQ1hG10E0YH.IyNLavLhde','registrar','active',0,'2026-08-31 11:50:20','2026-08-31 11:50:20',0,NULL),(16,'senperelias@gmail.com','$2y$10$yy/cYpKpENAKQQydvhRCV.9veEL2RU.guokjtnnT/TzgV/xEsCSd6','student_rep','active',0,'2026-09-03 21:56:19','2026-09-14 20:59:07',0,NULL),(17,'blen@gmail.com','$2y$10$pqkW35O0MBs/oUhrWEnUxei/9F0n3Vsh8d0pSvTl/oisTue5.9E.u','alumni','active',1,'2026-09-04 07:53:24','2026-09-27 15:08:46',2,NULL),(20,'studentrep@gmail.com','$2y$10$.ROuHHN48MXYLh.fB.0p.eNWzQeB5frucMINbPlzfA8X1ljelvwme','system_admin','active',0,'2026-09-04 21:20:53','2026-09-28 09:40:26',0,NULL),(31,'almuniadmin@gmail.com','$2y$10$zoT59N2mpZg88e4jx.qo4uLSQO.RMzRswmIQ8QpBluDpEQMKVwQvy','student_rep','active',0,'2026-09-18 12:20:03','2026-09-18 12:20:43',0,NULL),(32,'almunipres@gmail.com','$2y$10$h732mE3rjIDZz6zLMk3EXegYL2uEFOtI82d3AtW.Kta/dHNMtlsaO','admin','active',0,'2026-09-18 12:23:19','2026-09-27 15:57:00',1,NULL),(33,'bereket@gmail.com','$2y$10$4E7Xv7y.LS/Id8e.B7SOk.Zz5A5N46xMNiQcI1PHertiwgWcWVOzC','alumni','active',1,'2026-09-22 06:06:56','2026-09-27 15:09:57',2,NULL),(34,'test@gmail.com','$2y$10$aX6JTqgvO4hvdbO6os.wYuJZ//5vg/Oj.CXLObwyuePMWXhRbuePq','registrar','active',1,'2026-09-25 20:55:42','2026-09-27 13:00:06',5,'2026-09-27 15:15:06'),(35,'testt@gmail.com','$2y$10$U5/Krm2rMS1/zcfWC5vcEe43mKYc/yt8J8hgxX9fX94bfvaC.lsoC','registrar','active',1,'2026-09-25 20:57:49','2026-09-25 21:07:31',0,NULL),(36,'dave@gmail.com','$2y$10$h8DDNxg1Rzf8Ej8v6D.LmeIJsERMfIYnYOcX8k8RUNUTtcNFBsdJu','registrar','active',1,'2026-09-25 21:20:55','2026-09-25 21:20:55',0,NULL),(37,'system@gamil.com','$2y$10$R8WtNYccKX/OPkWNYWvhEOE7xIVa5tFbiGs/XRd.UrTYWiE4oC5ne','system_admin','active',1,'2026-09-27 12:09:46','2026-09-27 12:20:26',0,NULL);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-09-28 16:12:08
