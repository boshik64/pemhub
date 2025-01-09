-- MySQL dump 10.13  Distrib 9.1.0, for macos15.1 (arm64)
--
-- Host: 10.0.10.93    Database: pemdb
-- ------------------------------------------------------
-- Server version	8.0.36

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

--
-- Table structure for table `cinemas`
--

DROP TABLE IF EXISTS `cinemas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cinemas` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `company_title_id` bigint unsigned DEFAULT NULL,
  `cinema_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `country_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'RU',
  `city_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Moscow',
  `subject_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Moscow',
  `contact_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'i.shakirov@karofilm.ru',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `cinemas_cinema_name_unique` (`cinema_name`),
  KEY `cinemas_company_title_idx` (`company_title_id`),
  CONSTRAINT `cinemas_company_title_fk` FOREIGN KEY (`company_title_id`) REFERENCES `company_titles` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=39 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cinemas`
--

LOCK TABLES `cinemas` WRITE;
/*!40000 ALTER TABLE `cinemas` DISABLE KEYS */;
INSERT INTO `cinemas` VALUES (1,1,'12 October','RU','Moscow','Moscow','i.shakirov@karofilm.ru','2024-04-25 14:26:29','2024-04-26 08:56:24','2024-04-26 08:56:24'),(2,1,'OCTOBER TEST','RU','Moscow','Moscow','i.shakirov@karofilm.ru','2024-04-25 15:04:25','2024-04-26 09:19:13',NULL),(3,2,'11 Okhta','RU','Moscow','Moscow','i.shakirov@karofilm.ru','2024-04-26 08:32:43','2024-04-26 08:56:48','2024-04-26 08:56:48'),(4,1,'КАРО 11 Октябрь','RU','Moscow','Moscow','i.shakirov@karofilm.ru','2024-04-26 09:01:40','2024-04-26 09:01:40',NULL),(5,1,'КАРО 9 Атриум','RU','Moscow','Moscow','i.shakirov@karofilm.ru','2024-04-26 09:01:59','2024-04-26 09:01:59',NULL),(6,1,'КАРО Sky 17 Авиапарк','RU','Moscow','Moscow','i.shakirov@karofilm.ru','2024-04-26 09:02:15','2024-04-26 09:02:15',NULL),(7,1,'КАРО 8 в Капитолий Вернадского','RU','Moscow','Moscow','i.shakirov@karofilm.ru','2024-04-26 09:02:25','2024-04-26 09:02:25',NULL),(8,1,'КАРО 9 Vegas Каширский','RU','Moscow','Moscow','i.shakirov@karofilm.ru','2024-04-26 09:03:10','2024-04-26 09:03:10',NULL),(9,1,'КАРО 10 Щука','RU','Moscow','Moscow','i.shakirov@karofilm.ru','2024-04-26 09:03:17','2024-04-26 09:03:17',NULL),(10,1,'КАРО Vegas 22','RU','Moscow','Moscow','i.shakirov@karofilm.ru','2024-04-26 09:03:26','2024-04-26 09:03:26',NULL),(11,1,'КАРО 10 Реутов','RU','Moscow','Moscow','i.shakirov@karofilm.ru','2024-04-26 09:03:32','2024-04-26 09:03:32',NULL),(12,1,'КАРО 4 Подольск','RU','Moscow','Moscow','i.shakirov@karofilm.ru','2024-04-26 09:03:38','2024-04-26 09:03:38',NULL),(13,1,'КАРО 4 Иридиум','RU','Moscow','Moscow','i.shakirov@karofilm.ru','2024-04-26 09:03:46','2024-04-26 09:03:46',NULL),(14,1,'КАРО 9 Варшавский экспресс','RU','Moscow','Moscow','i.shakirov@karofilm.ru','2024-04-26 09:03:56','2024-04-26 09:03:56',NULL),(15,1,'КАРО 7 на Стачек','RU','Moscow','Moscow','i.shakirov@karofilm.ru','2024-04-26 09:04:07','2024-04-26 09:04:07',NULL),(17,1,'КАРО 9 Континент на Звёздной','RU','Moscow','Moscow','i.shakirov@karofilm.ru','2024-04-26 09:06:17','2024-04-26 09:06:17',NULL),(18,1,'КАРО 7 Атмосфера','RU','Moscow','Moscow','i.shakirov@karofilm.ru','2024-04-26 09:06:27','2024-04-26 09:06:27',NULL),(19,1,'КАРО 5 Невский-2','RU','Moscow','Moscow','i.shakirov@karofilm.ru','2024-04-26 09:06:32','2024-04-26 09:06:32',NULL),(20,1,'КАРО 10 Радуга Парк','RU','Moscow','Moscow','i.shakirov@karofilm.ru','2024-04-26 09:06:37','2024-04-26 09:06:37',NULL),(21,1,'КАРО 7 Калининград Плаза','RU','Moscow','Moscow','i.shakirov@karofilm.ru','2024-04-26 09:06:43','2024-04-26 09:06:43',NULL),(22,1,'КАРО 8 Аура','RU','Moscow','Moscow','i.shakirov@karofilm.ru','2024-04-26 09:06:49','2024-04-26 09:06:49',NULL),(23,1,'КАРО 4 Высота','RU','Moscow','Moscow','i.shakirov@karofilm.ru','2024-04-26 09:06:54','2024-04-26 09:06:54',NULL),(24,1,'КАРО 4 Нева','RU','Moscow','Moscow','i.shakirov@karofilm.ru','2024-04-26 09:07:00','2024-04-26 09:07:00',NULL),(25,1,'КАРО 4 Эльбрус','RU','Moscow','Moscow','i.shakirov@karofilm.ru','2024-04-26 09:07:05','2024-04-26 09:07:05',NULL),(26,1,'КАРО 5 Марс','RU','Moscow','Moscow','i.shakirov@karofilm.ru','2024-04-26 09:07:11','2024-04-26 09:07:11',NULL),(27,1,'КАРО 5 Рассвет','RU','Moscow','Moscow','i.shakirov@karofilm.ru','2024-04-26 09:07:16','2024-04-26 09:07:16',NULL),(28,1,'КАРО 6 Будапешт','RU','Moscow','Moscow','i.shakirov@karofilm.ru','2024-04-26 09:07:21','2024-04-26 09:07:21',NULL),(29,1,'КАРО 10 София','RU','Moscow','Moscow','i.shakirov@karofilm.ru','2024-04-26 09:07:27','2024-04-26 09:07:27',NULL),(30,2,'КАРО 6 Колумб','RU','Moscow','Moscow','i.shakirov@karofilm.ru','2024-04-26 09:07:43','2024-04-26 09:07:43',NULL),(31,2,'КАРО 11 Охта','RU','Moscow','Moscow','i.shakirov@karofilm.ru','2024-04-26 09:07:47','2024-04-26 09:07:47',NULL),(32,2,'КАРО 13 Кунцево','RU','Moscow','Moscow','i.shakirov@karofilm.ru','2024-04-26 09:07:53','2024-04-26 09:07:53',NULL),(33,2,'КАРО 10 Галерея','RU','Moscow','Moscow','i.shakirov@karofilm.ru','2024-04-26 09:08:00','2024-04-26 09:08:00',NULL),(34,1,'КАРО 4 Ангара','RU','Moscow','Moscow','i.shakirov@karofilm.ru','2024-04-26 09:08:25','2024-04-26 09:08:25',NULL),(35,1,'КАРО 8 Саларис','RU','Moscow','Moscow','i.shakirov@karofilm.ru','2024-04-26 09:08:32','2024-04-26 09:08:32',NULL),(36,1,'КАРО 8 Южное Бутово','RU','Moscow','Moscow','i.shakirov@karofilm.ru','2024-04-26 09:09:05','2024-04-26 09:09:05',NULL),(37,2,'Каро 8 Краснодар','RU','Moscow','Moscow','i.shakirov@karofilm.ru','2024-05-21 10:15:41','2024-05-21 10:15:41',NULL),(38,1,'Kinowow','RU','Moscow','Moscow','i.shakirov@karofilm.ru','2024-05-21 10:56:08','2024-05-21 10:56:08',NULL);
/*!40000 ALTER TABLE `cinemas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `company_titles`
--

DROP TABLE IF EXISTS `company_titles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `company_titles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `company_titles`
--

LOCK TABLES `company_titles` WRITE;
/*!40000 ALTER TABLE `company_titles` DISABLE KEYS */;
INSERT INTO `company_titles` VALUES (1,'KARO Film Management, LLC','2024-04-25 14:26:28','2024-04-25 14:26:28',NULL),(2,'LLC KINOTEATRI NOVOGO POKOLENIYA','2024-04-26 08:32:41','2024-04-26 08:32:41',NULL);
/*!40000 ALTER TABLE `company_titles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `failed_jobs`
--

LOCK TABLES `failed_jobs` WRITE;
/*!40000 ALTER TABLE `failed_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `failed_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `merchants`
--

DROP TABLE IF EXISTS `merchants`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `merchants` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `cinema_id` bigint unsigned DEFAULT NULL,
  `mid` bigint unsigned NOT NULL,
  `merchant_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `department_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `next_update` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `workstation_id` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `merchants_mid_unique` (`mid`),
  UNIQUE KEY `merchants_department_name_unique` (`department_name`),
  KEY `cinema_id_idx` (`cinema_id`),
  KEY `merchants_workstation_id_index` (`workstation_id`),
  CONSTRAINT `cinema_id_fk` FOREIGN KEY (`cinema_id`) REFERENCES `cinemas` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=132 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `merchants`
--

LOCK TABLES `merchants` WRITE;
/*!40000 ALTER TABLE `merchants` DISABLE KEYS */;
INSERT INTO `merchants` VALUES (1,34,9295844120,'visa','KARO-WEB-ANGARA','2025-07-09 09:32:29','2024-05-21 10:12:52','2024-07-09 09:36:38','2024-07-09 09:36:38',1),(2,35,9295844146,'visa','KARO-WEB-SALARIS','2025-07-11 08:58:40','2024-05-21 10:13:12','2024-07-11 08:58:41',NULL,1),(3,37,9296758048,'visa','KARO-WEB-KRASNODAR','2025-07-11 08:58:40','2024-05-21 10:16:12','2024-07-11 08:58:41',NULL,1),(4,37,9296758055,'visa','KARO-MB-KRASNODAR','2025-07-11 08:59:53','2024-05-21 10:17:18','2024-07-11 08:59:53',NULL,2),(5,37,9296758063,'pushkin','KARO-WPC-KRASNODAR','2025-07-11 08:58:40','2024-05-21 10:17:41','2024-07-11 08:58:41',NULL,1),(6,37,9296758071,'pushkin','KARO-MPC-KRASNODAR','2025-07-11 08:59:53','2024-05-21 10:18:00','2024-07-11 08:59:53',NULL,2),(7,23,9296181340,'visa','KARO-WEB-VYSOTA','2025-07-11 08:58:40','2024-05-21 10:18:17','2024-07-11 08:58:41',NULL,1),(8,26,9296181365,'visa','KARO-WEB-MARS','2025-07-11 08:58:40','2024-05-21 10:18:45','2024-07-11 08:58:42',NULL,1),(9,24,9296181399,'visa','KARO-WEB-NEVA','2025-07-11 08:58:40','2024-05-21 10:19:08','2024-07-11 08:58:42',NULL,1),(10,27,9296181423,'visa','KARO-WEB-RASSVET','2025-07-11 08:58:40','2024-05-21 10:19:21','2024-07-11 08:58:42',NULL,1),(11,28,9296181449,'visa','KARO-WEB-BUDAPESHT','2025-07-11 08:58:40','2024-05-21 10:19:37','2024-07-11 08:58:42',NULL,1),(12,29,9296181456,'visa','KARO-WEB-SOFIA','2025-07-11 08:58:40','2024-05-21 10:19:56','2024-07-11 08:58:43',NULL,1),(13,25,9296181472,'visa','KARO-WEB-ELBRUS','2025-07-11 08:58:40','2024-05-21 10:20:09','2024-07-11 08:58:43',NULL,1),(14,32,9296187271,'pushkin','KARO-WPC-KUNTSEVO','2025-07-11 08:58:40','2024-05-21 10:20:37','2024-07-11 08:58:43',NULL,1),(15,33,9296187289,'pushkin','KARO-WPC-NOVOSIBIRSK','2025-07-11 08:58:40','2024-05-21 10:20:54','2024-07-11 08:58:44',NULL,1),(16,31,9296187297,'pushkin','KARO-WPC-OKHTA','2025-07-11 08:58:40','2024-05-21 10:21:14','2024-07-11 08:58:44',NULL,1),(17,30,9296187305,'pushkin','KARO-WPC-TYUMEN','2025-07-11 08:58:40','2024-05-21 10:22:08','2024-07-11 08:58:45',NULL,1),(18,6,9296187313,'pushkin','KARO-WPC-AVIAPARK','2025-07-11 08:58:40','2024-05-21 10:22:27','2024-07-11 08:58:45',NULL,1),(19,5,9296187339,'pushkin','KARO-WPC-ATRIUM','2025-07-11 08:58:40','2024-05-21 10:24:53','2024-07-11 08:58:45',NULL,1),(21,14,9296187362,'pushkin','KARO-WPC-VARSHAVSKY','2025-07-11 08:58:40','2024-05-21 10:28:47','2024-07-11 08:58:45',NULL,1),(22,7,9296187370,'pushkin','KARO-WPC-VERNADSKY','2025-07-11 08:58:40','2024-05-21 10:29:06','2024-07-11 08:58:46',NULL,1),(23,20,9296187396,'pushkin','KARO-WPC-EKATERINBURG','2025-07-11 08:58:40','2024-05-21 10:29:52','2024-07-11 08:58:46',NULL,1),(24,17,9296187404,'pushkin','KARO-WPC-ZVEZDNAYA','2025-07-11 08:58:40','2024-05-21 10:30:11','2024-07-11 08:58:47',NULL,1),(25,13,9296187412,'pushkin','KARO-WPC-IRIDIUM','2025-07-11 08:58:40','2024-05-21 10:30:34','2024-07-11 08:58:47',NULL,1),(26,21,9296187420,'pushkin','KARO-WPC-KALININGRAD','2025-07-11 08:58:40','2024-05-21 10:31:00','2024-07-11 08:58:47',NULL,1),(27,19,9296187388,'pushkin','KARO-WPC-DYBENKO','2025-07-11 08:58:40','2024-05-21 10:39:55','2024-07-11 08:58:47',NULL,1),(28,18,9296187438,'pushkin','KARO-WPC-KOMENDANTSKY','2025-07-11 08:58:40','2024-05-21 10:41:37','2024-07-11 08:58:47',NULL,1),(29,8,9296187453,'pushkin','KARO-WPC-VEGASKASHIRSKY','2025-07-11 08:58:40','2024-05-21 10:45:36','2024-07-11 08:58:48',NULL,1),(30,10,9296187461,'pushkin','KARO-WPC-CROCUS','2025-07-11 08:58:40','2024-05-21 10:47:34','2024-07-11 08:58:48',NULL,1),(31,15,9296187446,'pushkin','KARO-WPC-CONTINENT','2025-07-11 08:58:40','2024-05-21 10:48:22','2024-07-11 08:58:49',NULL,1),(32,4,9296187479,'pushkin','KARO-WPC-OKTYABR','2025-07-11 08:58:40','2024-05-21 10:50:12','2024-07-11 08:58:49',NULL,1),(33,12,9296187487,'pushkin','KARO-WPC-PODOLSK','2025-07-11 08:58:40','2024-05-21 10:50:26','2024-07-11 08:58:49',NULL,1),(34,11,9296187495,'pushkin','KARO-WPC-REUTOV','2025-07-11 08:58:40','2024-05-21 10:50:42','2024-07-11 08:58:49',NULL,1),(35,22,9296187529,'pushkin','KARO-WPC-SURGUT','2025-07-11 08:58:40','2024-05-21 10:51:35','2024-07-11 08:58:50',NULL,1),(36,9,9296187552,'pushkin','KARO-WPC-SCHUKINSKY','2025-07-11 08:58:40','2024-05-21 10:52:11','2024-07-11 08:58:50',NULL,1),(37,28,9296187560,'pushkin','KARO-WPC-BUDAPESHT','2025-07-11 08:58:40','2024-05-21 10:52:38','2024-07-11 08:58:50',NULL,1),(38,25,9296187578,'pushkin','KARO-WPC-ELBRUS','2025-07-11 08:58:40','2024-05-21 10:52:52','2024-07-11 08:58:51',NULL,1),(39,26,9296187586,'pushkin','KARO-WPC-MARS','2025-07-11 08:58:40','2024-05-21 10:53:09','2024-07-11 08:58:51',NULL,1),(40,24,9296187594,'pushkin','KARO-WPC-NEVA','2025-07-11 08:58:40','2024-05-21 10:53:58','2024-07-11 08:58:51',NULL,1),(41,27,9296187602,'pushkin','KARO-WPC-RASSVET','2025-07-11 08:58:40','2024-05-21 10:54:13','2024-07-11 08:58:51',NULL,1),(42,29,9296187610,'pushkin','KARO-WPC-SOFIA','2025-07-11 08:58:40','2024-05-21 10:54:31','2024-07-11 08:58:52',NULL,1),(43,23,9296187628,'pushkin','KARO-WPC-VYSOTA','2025-07-11 08:58:40','2024-05-21 10:54:46','2024-07-11 08:58:52',NULL,1),(44,34,9296187651,'pushkin','KARO-WPC-ANGARA','2025-07-09 09:32:29','2024-05-21 10:54:59','2024-07-09 09:36:38','2024-07-09 09:36:38',1),(45,35,9296187669,'pushkin','KARO-WPC-SALARIS','2025-07-11 08:58:40','2024-05-21 10:55:17','2024-07-11 08:58:52',NULL,1),(46,38,9297556656,'visa','KARO-KINOWOW','2025-07-11 09:05:57','2024-05-21 10:57:07','2024-07-11 09:05:57',NULL,1),(47,11,9294432737,'visa','KF-WEB-REUTOV','2025-07-11 08:58:40','2024-05-21 10:57:35','2024-07-11 08:58:52',NULL,1),(48,4,9294433768,'visa','KF-WEB-OCTOBER','2025-07-11 08:58:40','2024-05-21 10:57:50','2024-07-11 08:58:53',NULL,1),(49,12,9294433784,'visa','KF-WEB-PODOLSK','2025-07-11 08:58:40','2024-05-21 10:58:23','2024-07-11 08:58:53',NULL,1),(50,20,9294433818,'visa','KF-WEB-RETAIL PARK','2025-07-11 08:58:40','2024-05-21 11:01:05','2024-07-11 08:58:53',NULL,1),(51,9,9294433826,'visa','KF-WEB-SCHUKINSKY','2025-07-11 08:58:40','2024-05-21 11:01:28','2024-07-11 08:58:54',NULL,1),(52,13,9294433834,'visa','KF-WEB-IRIDIUM','2025-07-11 08:58:40','2024-05-21 11:01:44','2024-07-11 08:58:54',NULL,1),(53,7,9294433867,'visa','KF-WEB-VERNADSKOGO','2025-07-11 08:58:40','2024-05-21 11:02:03','2024-07-11 08:58:54',NULL,1),(54,21,9294433883,'visa','KF-WEB-KALININGRAD','2025-07-11 08:58:40','2024-05-21 11:02:17','2024-07-11 08:58:55',NULL,1),(55,10,9294435854,'visa','KF-WEB-CROCUS CITY','2025-07-11 08:58:40','2024-05-21 11:02:35','2024-07-11 08:58:55',NULL,1),(56,5,9294436589,'visa','KF-WEB ATRIUM','2025-07-11 08:58:40','2024-05-21 11:02:48','2024-07-11 08:58:55',NULL,1),(57,19,9294436654,'visa','KF-WEB DYBENKO','2025-07-11 08:58:40','2024-05-21 11:03:15','2024-07-11 08:58:55',NULL,1),(58,14,9294436662,'visa','KF-WEB-VARSHAVSKY','2025-07-11 08:58:40','2024-05-21 11:03:34','2024-07-11 08:58:56',NULL,1),(59,18,9294436670,'visa','KF-WEBCOMENDANTSKY','2025-07-11 08:58:40','2024-05-21 11:03:58','2024-07-11 08:58:56',NULL,1),(60,15,9294436688,'visa','KF-WEB CONTINENT','2025-07-11 08:58:40','2024-05-21 11:04:25','2024-07-11 08:58:56',NULL,1),(61,17,9294436720,'visa','KF-WEB ZVEZDNAYA','2025-07-11 08:58:40','2024-05-21 11:04:45','2024-07-11 08:58:56',NULL,1),(62,6,9294711650,'visa','KF-WEB-AVIA PARK','2025-07-11 08:58:40','2024-05-21 11:04:59','2024-07-11 08:58:57',NULL,1),(63,31,9295350714,'visa','KF-WEB-OHTA','2025-07-11 08:58:40','2024-05-21 11:05:12','2024-07-11 08:58:57',NULL,1),(64,32,9295350730,'visa','KF-WEB-KUNTSEVO','2025-07-11 08:58:40','2024-05-21 11:05:22','2024-07-11 08:58:57',NULL,1),(65,30,9295534432,'visa','KF-WEB-TYUMEN','2025-07-11 08:58:40','2024-05-21 11:05:43','2024-07-11 08:58:58',NULL,1),(67,13,9296323058,'pushkin','KARO-MPC-IRIDIUM','2025-07-11 08:59:53','2024-05-21 11:06:58','2024-07-11 08:59:53',NULL,2),(68,21,9296323066,'pushkin','KARO-MPC-KALININGRAD','2025-07-11 08:59:53','2024-05-21 11:07:12','2024-07-11 08:59:53',NULL,2),(69,18,9296323074,'pushkin','KARO-MPC-KOMENDANTSKY','2025-07-11 08:59:53','2024-05-21 11:07:34','2024-07-11 08:59:54',NULL,2),(70,15,9296323082,'pushkin','KARO-MPC-CONTINENT','2025-07-11 08:59:53','2024-05-21 11:08:52','2024-07-11 08:59:54',NULL,2),(71,8,9296323090,'pushkin','KARO-MPC-VEGASKASHIRSKY','2025-07-11 08:59:53','2024-05-21 11:09:12','2024-07-11 08:59:55',NULL,2),(72,10,9296323108,'pushkin','KARO-MPC-CROCUS','2025-07-11 08:59:53','2024-05-21 11:09:30','2024-07-11 08:59:55',NULL,2),(73,4,9296323116,'pushkin','KARO-MPC-OKTYABR','2025-07-11 08:59:53','2024-05-21 11:09:52','2024-07-11 08:59:55',NULL,2),(74,12,9296323124,'pushkin','KARO-MPC-PODOLSK','2025-07-11 08:59:53','2024-05-21 11:10:08','2024-07-11 08:59:55',NULL,2),(75,11,9296323132,'pushkin','KARO-MPC-REUTOV','2025-07-11 08:59:53','2024-05-21 11:10:29','2024-07-11 08:59:56',NULL,2),(76,22,9296323140,'pushkin','KARO-MPC-SURGUT','2025-07-11 08:59:53','2024-05-21 11:10:50','2024-07-11 08:59:56',NULL,2),(77,9,9296323157,'pushkin','KARO-MPC-SCHUKINSKY','2025-07-11 08:59:53','2024-05-21 11:11:54','2024-07-11 08:59:56',NULL,2),(78,28,9296323165,'pushkin','KARO-MPC-BUDAPESHT','2025-07-11 08:59:53','2024-05-21 11:12:09','2024-07-11 08:59:56',NULL,2),(79,26,9296323181,'pushkin','KARO-MPC-MARS','2025-07-11 08:59:53','2024-05-21 11:31:28','2024-07-11 08:59:57',NULL,2),(80,24,9296323199,'pushkin','KARO-MPC-NEVA','2025-07-11 08:59:53','2024-05-21 11:32:08','2024-07-11 08:59:57',NULL,2),(81,27,9296323207,'pushkin','KARO-MPC-RASSVET','2025-07-11 08:59:53','2024-05-21 11:32:22','2024-07-11 08:59:57',NULL,2),(82,29,9296323215,'pushkin','KARO-MPC-SOFIA','2025-07-11 08:59:53','2024-05-21 11:32:35','2024-07-11 08:59:58',NULL,2),(83,23,9296323223,'pushkin','KARO-MPC-VYSOTA','2025-07-11 08:59:53','2024-05-21 11:32:55','2024-07-11 08:59:58',NULL,2),(84,34,9296323231,'pushkin','KARO-MPC-ANGARA','2025-07-09 09:32:29','2024-05-21 11:33:09','2024-07-09 09:36:38','2024-07-09 09:36:38',2),(85,35,9296323249,'pushkin','KARO-MPC-SALARIS','2025-07-11 08:59:53','2024-05-21 11:33:22','2024-07-11 08:59:59',NULL,2),(86,33,9296323264,'pushkin','KARO-MPC-NOVOSIBIRSK','2025-07-11 08:59:53','2024-05-21 11:33:42','2024-07-11 08:59:59',NULL,2),(87,31,9296323272,'pushkin','KARO-MPC-OKHTA','2025-07-11 08:59:53','2024-05-21 11:33:57','2024-07-11 08:59:59',NULL,2),(88,30,9296323280,'pushkin','KARO-MPC-TYUMEN','2025-07-11 08:59:53','2024-05-21 11:34:18','2024-07-11 08:59:59',NULL,2),(89,6,9296323298,'pushkin','KARO-MPC-AVIAPARK','2025-07-11 08:59:53','2024-05-21 11:34:32','2024-07-11 09:00:00',NULL,2),(90,5,9296323306,'pushkin','KARO-MPC-ATRIUM','2025-07-11 08:59:53','2024-05-21 11:34:46','2024-07-11 09:00:00',NULL,2),(91,14,9296323322,'pushkin','KARO-MPC-VARSHAVSKY','2025-07-11 08:59:53','2024-05-21 11:35:05','2024-07-11 09:00:00',NULL,2),(92,7,9296323330,'pushkin','KARO-MPC-VERNADSKY','2025-07-11 08:59:53','2024-05-21 11:35:19','2024-07-11 09:00:00',NULL,2),(93,19,9296323348,'pushkin','KARO-MPC-DYBENKO','2025-07-11 08:59:53','2024-05-21 11:35:43','2024-07-11 09:00:00',NULL,2),(94,20,9296323355,'pushkin','KARO-MPC-EKATERINBURG','2025-07-11 08:59:53','2024-05-21 11:36:00','2024-07-11 09:00:01',NULL,2),(95,25,9296186885,'visa','KARO-MB-ELBRUS','2025-07-11 08:59:53','2024-05-21 11:36:20','2024-07-11 09:00:02',NULL,2),(96,17,9296323041,'pushkin','KARO-MPC-ZVEZDNAYA','2025-07-11 08:59:53','2024-05-21 11:36:42','2024-07-11 09:00:02',NULL,2),(97,22,9294433800,'visa','KF-WEB-SURGUT','2025-07-11 08:58:40','2024-05-21 11:37:00','2024-07-11 08:58:58',NULL,1),(98,6,9296181514,'visa','KARO-MB-AVIAPARK','2025-07-11 08:59:53','2024-05-21 11:37:13','2024-07-11 09:00:02',NULL,2),(99,5,9296181530,'visa','KARO-MB-ATRIUM','2025-07-11 08:59:53','2024-05-21 11:37:26','2024-07-11 09:00:03',NULL,2),(100,14,9296181563,'visa','KARO-MB-VARSHAVSKY','2025-07-11 08:59:53','2024-05-21 11:37:38','2024-07-11 09:00:04',NULL,2),(101,7,9296181571,'visa','KARO-MB-VERNADSKY','2025-07-11 08:59:53','2024-05-21 11:37:49','2024-07-11 09:00:04',NULL,2),(102,19,9296181589,'visa','KARO-MB-DYBENKO','2025-07-11 08:59:53','2024-05-21 11:38:06','2024-07-11 09:00:04',NULL,2),(103,20,9296181597,'visa','KARO-MB-EKATERINBURG','2025-07-11 08:59:53','2024-05-21 11:38:30','2024-07-11 09:00:04',NULL,2),(104,17,9296181605,'visa','KARO-MB-ZVEZDNAYA','2025-07-11 08:59:53','2024-05-21 11:38:53','2024-07-11 09:00:04',NULL,2),(105,21,9296186737,'visa','KARO-MB-KALININGRAD','2025-07-11 08:59:53','2024-05-21 11:39:51','2024-07-11 09:00:05',NULL,2),(106,18,9296186745,'visa','KARO-MB-KOMENDANTSKY','2025-07-11 08:59:53','2024-05-21 11:40:09','2024-07-11 09:00:05',NULL,2),(107,15,9296186752,'visa','KARO-MB-CONTINENT','2025-07-11 08:59:53','2024-05-21 11:40:28','2024-07-11 09:00:05',NULL,2),(108,8,9296186760,'visa','KARO-MB-VEGASKASHIRSKY','2025-07-11 08:59:53','2024-05-21 11:40:44','2024-07-11 09:00:05',NULL,2),(109,10,9296186778,'visa','KARO-MB-CROCUS','2025-07-11 08:59:53','2024-05-21 11:40:58','2024-07-11 09:00:06',NULL,2),(110,4,9296186786,'visa','KARO-MB-OKTYABR','2025-07-11 08:59:53','2024-05-21 11:41:19','2024-07-11 09:00:06',NULL,2),(111,12,9296186794,'visa','KARO-MB-PODOLSK','2025-07-11 08:59:53','2024-05-21 11:41:29','2024-07-11 09:00:06',NULL,2),(112,11,9296186802,'visa','KARO-MB-REUTOV','2025-07-11 08:59:53','2024-05-21 11:41:40','2024-07-11 09:00:06',NULL,2),(113,9,9296186869,'visa','KARO-MB-SCHUKINSKY','2025-07-11 08:59:53','2024-05-21 11:41:49','2024-07-11 09:00:07',NULL,2),(114,28,9296186877,'visa','KARO-MB-BUDAPESHT','2025-07-11 08:59:53','2024-05-21 11:42:06','2024-07-11 09:00:07',NULL,2),(115,26,9296186893,'visa','KARO-MB-MARS','2025-07-11 08:59:53','2024-05-21 11:42:19','2024-07-11 09:00:08',NULL,2),(116,24,9296186901,'visa','KARO-MB-NEVA','2025-07-11 08:59:53','2024-05-21 11:42:32','2024-07-11 09:00:08',NULL,2),(117,27,9296186919,'visa','KARO-MB-RASSVET','2025-07-11 08:59:53','2024-05-21 11:42:41','2024-07-11 09:00:08',NULL,2),(118,29,9296186927,'visa','KARO-MB-SOFIA','2025-07-11 08:59:53','2024-05-21 11:42:52','2024-07-11 09:00:09',NULL,2),(119,23,9296186935,'visa','KARO-MB-VYSOTA','2025-07-11 08:59:53','2024-05-21 11:43:01','2024-07-11 09:00:09',NULL,2),(120,34,9296186943,'visa','KARO-MB-ANGARA',NULL,'2024-05-21 11:43:11','2024-07-09 09:36:38','2024-07-09 09:36:38',2),(121,35,9296186950,'visa','KARO-MB-SALARIS','2025-07-11 08:59:53','2024-05-21 11:43:22','2024-07-11 09:00:10',NULL,2),(122,32,9296186968,'visa','KARO-MB-KUNTSEVO','2025-07-11 08:59:53','2024-05-21 11:43:38','2024-07-11 09:00:10',NULL,2),(123,33,9296186976,'visa','KARO-MB-NOVOSIBIRSK','2025-07-11 08:59:53','2024-05-21 11:43:51','2024-07-11 09:00:10',NULL,2),(124,31,9296186984,'visa','KARO-MB-OKHTA','2025-07-11 08:59:53','2024-05-21 11:44:04','2024-07-11 09:00:10',NULL,2),(125,30,9296186992,'visa','KARO-MB-TYUMEN','2025-07-11 08:59:53','2024-05-21 11:44:17','2024-07-11 09:00:11',NULL,2),(126,33,9296048341,'visa','KF-WEB-NOVOSIBIRSK','2025-07-11 08:58:40','2024-05-21 11:44:36','2024-07-11 08:58:58',NULL,1),(127,25,9296323173,'pushkin','KARO-MPC-ELBRUS','2025-07-11 08:59:53','2024-05-21 12:47:45','2024-07-11 09:00:11',NULL,2),(128,13,9296186711,'visa','KARO-MB-IRIDIUM','2025-07-11 08:59:53','2024-05-21 12:49:23','2024-07-11 09:00:11',NULL,2),(129,8,9295579403,'visa','KF-WEB-VEGASKASHIRSKY','2025-07-11 08:58:40','2024-05-21 13:21:22','2024-07-11 08:58:58',NULL,1),(130,22,9296186836,'visa','KARO-MB-SURGUT','2025-07-11 08:59:53','2024-05-21 15:00:36','2024-07-11 09:00:12',NULL,2),(131,32,9296323256,'pushkin','KARO-MPC-KUNTSEVO','2025-07-11 08:59:53','2024-05-21 15:01:39','2024-07-11 09:00:12',NULL,2);
/*!40000 ALTER TABLE `merchants` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'2014_10_12_000000_create_users_table',1),(2,'2014_10_12_100000_create_password_reset_tokens_table',1),(3,'2019_08_19_000000_create_failed_jobs_table',1),(4,'2019_12_14_000001_create_personal_access_tokens_table',1),(5,'2024_04_10_102135_create_merchants_table',1),(6,'2024_04_10_145515_create_cinemas_table',1),(7,'2024_04_12_104449_create_company_titles_table',1),(8,'2024_04_16_171954_change_foreign_for_merchants_table',1),(9,'2024_04_27_132232_add_workstation_id_to_users_table',2),(10,'2024_04_27_144939_create_permission_tables',2),(11,'2024_04_27_153435_delete_workstation_id_from_users',2);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `model_has_permissions`
--

DROP TABLE IF EXISTS `model_has_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `model_has_permissions` (
  `permission_id` bigint unsigned NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `model_has_permissions`
--

LOCK TABLES `model_has_permissions` WRITE;
/*!40000 ALTER TABLE `model_has_permissions` DISABLE KEYS */;
/*!40000 ALTER TABLE `model_has_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `model_has_roles`
--

DROP TABLE IF EXISTS `model_has_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `model_has_roles` (
  `role_id` bigint unsigned NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `model_has_roles`
--

LOCK TABLES `model_has_roles` WRITE;
/*!40000 ALTER TABLE `model_has_roles` DISABLE KEYS */;
INSERT INTO `model_has_roles` VALUES (1,'App\\Models\\User',1),(1,'App\\Models\\User',2);
/*!40000 ALTER TABLE `model_has_roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_reset_tokens`
--

LOCK TABLES `password_reset_tokens` WRITE;
/*!40000 ALTER TABLE `password_reset_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_reset_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `permissions`
--

DROP TABLE IF EXISTS `permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `permissions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB AUTO_INCREMENT=44 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `permissions`
--

LOCK TABLES `permissions` WRITE;
/*!40000 ALTER TABLE `permissions` DISABLE KEYS */;
INSERT INTO `permissions` VALUES (1,'view_cinema','web','2024-04-27 16:02:46','2024-04-27 16:02:46'),(2,'view_any_cinema','web','2024-04-27 16:02:46','2024-04-27 16:02:46'),(3,'create_cinema','web','2024-04-27 16:02:46','2024-04-27 16:02:46'),(4,'update_cinema','web','2024-04-27 16:02:46','2024-04-27 16:02:46'),(5,'restore_cinema','web','2024-04-27 16:02:46','2024-04-27 16:02:46'),(6,'restore_any_cinema','web','2024-04-27 16:02:46','2024-04-27 16:02:46'),(7,'replicate_cinema','web','2024-04-27 16:02:46','2024-04-27 16:02:46'),(8,'reorder_cinema','web','2024-04-27 16:02:46','2024-04-27 16:02:46'),(9,'delete_cinema','web','2024-04-27 16:02:46','2024-04-27 16:02:46'),(10,'delete_any_cinema','web','2024-04-27 16:02:46','2024-04-27 16:02:46'),(11,'force_delete_cinema','web','2024-04-27 16:02:46','2024-04-27 16:02:46'),(12,'force_delete_any_cinema','web','2024-04-27 16:02:46','2024-04-27 16:02:46'),(13,'view_merchant','web','2024-04-27 16:02:46','2024-04-27 16:02:46'),(14,'view_any_merchant','web','2024-04-27 16:02:46','2024-04-27 16:02:46'),(15,'create_merchant','web','2024-04-27 16:02:46','2024-04-27 16:02:46'),(16,'update_merchant','web','2024-04-27 16:02:46','2024-04-27 16:02:46'),(17,'restore_merchant','web','2024-04-27 16:02:46','2024-04-27 16:02:46'),(18,'restore_any_merchant','web','2024-04-27 16:02:46','2024-04-27 16:02:46'),(19,'replicate_merchant','web','2024-04-27 16:02:46','2024-04-27 16:02:46'),(20,'reorder_merchant','web','2024-04-27 16:02:46','2024-04-27 16:02:46'),(21,'delete_merchant','web','2024-04-27 16:02:46','2024-04-27 16:02:46'),(22,'delete_any_merchant','web','2024-04-27 16:02:46','2024-04-27 16:02:46'),(23,'force_delete_merchant','web','2024-04-27 16:02:46','2024-04-27 16:02:46'),(24,'force_delete_any_merchant','web','2024-04-27 16:02:46','2024-04-27 16:02:46'),(25,'view_role','web','2024-04-27 16:02:46','2024-04-27 16:02:46'),(26,'view_any_role','web','2024-04-27 16:02:46','2024-04-27 16:02:46'),(27,'create_role','web','2024-04-27 16:02:46','2024-04-27 16:02:46'),(28,'update_role','web','2024-04-27 16:02:46','2024-04-27 16:02:46'),(29,'delete_role','web','2024-04-27 16:02:46','2024-04-27 16:02:46'),(30,'delete_any_role','web','2024-04-27 16:02:46','2024-04-27 16:02:46'),(31,'view_user','web','2024-04-27 16:02:46','2024-04-27 16:02:46'),(32,'view_any_user','web','2024-04-27 16:02:46','2024-04-27 16:02:46'),(33,'create_user','web','2024-04-27 16:02:46','2024-04-27 16:02:46'),(34,'update_user','web','2024-04-27 16:02:46','2024-04-27 16:02:46'),(35,'restore_user','web','2024-04-27 16:02:46','2024-04-27 16:02:46'),(36,'restore_any_user','web','2024-04-27 16:02:46','2024-04-27 16:02:46'),(37,'replicate_user','web','2024-04-27 16:02:46','2024-04-27 16:02:46'),(38,'reorder_user','web','2024-04-27 16:02:46','2024-04-27 16:02:46'),(39,'delete_user','web','2024-04-27 16:02:46','2024-04-27 16:02:46'),(40,'delete_any_user','web','2024-04-27 16:02:46','2024-04-27 16:02:46'),(41,'force_delete_user','web','2024-04-27 16:02:46','2024-04-27 16:02:46'),(42,'force_delete_any_user','web','2024-04-27 16:02:46','2024-04-27 16:02:46'),(43,'widget_MerchantCount','web','2024-04-27 16:02:46','2024-04-27 16:02:46');
/*!40000 ALTER TABLE `permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `personal_access_tokens`
--

DROP TABLE IF EXISTS `personal_access_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `personal_access_tokens` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `personal_access_tokens`
--

LOCK TABLES `personal_access_tokens` WRITE;
/*!40000 ALTER TABLE `personal_access_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `personal_access_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `role_has_permissions`
--

DROP TABLE IF EXISTS `role_has_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `role_has_permissions` (
  `permission_id` bigint unsigned NOT NULL,
  `role_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`role_id`),
  KEY `role_has_permissions_role_id_foreign` (`role_id`),
  CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `role_has_permissions`
--

LOCK TABLES `role_has_permissions` WRITE;
/*!40000 ALTER TABLE `role_has_permissions` DISABLE KEYS */;
INSERT INTO `role_has_permissions` VALUES (1,1),(2,1),(3,1),(4,1),(5,1),(6,1),(7,1),(8,1),(9,1),(10,1),(11,1),(12,1),(13,1),(14,1),(15,1),(16,1),(17,1),(18,1),(19,1),(20,1),(21,1),(22,1),(23,1),(24,1),(25,1),(26,1),(27,1),(28,1),(29,1),(30,1),(31,1),(32,1),(33,1),(34,1),(35,1),(36,1),(37,1),(38,1),(39,1),(40,1),(41,1),(42,1),(43,1);
/*!40000 ALTER TABLE `role_has_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `roles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` VALUES (1,'super_admin','web','2024-04-27 16:02:46','2024-04-27 16:02:46');
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_workstation`
--

DROP TABLE IF EXISTS `user_workstation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_workstation` (
  `user_id` int unsigned NOT NULL,
  `workstation_id` int unsigned NOT NULL,
  KEY `user_workstation_user_id_index` (`user_id`),
  KEY `user_workstation_workstation_id_index` (`workstation_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_workstation`
--

LOCK TABLES `user_workstation` WRITE;
/*!40000 ALTER TABLE `user_workstation` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_workstation` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'v.ishchenko','v.ishchenko@karofilm.ru',NULL,'$2y$12$W0RKONcsuyJZfNa8tb7M6.9pTP8UaornBYI26f7QeMYdh.ctmJ1HG','jLdkbHpTqhuZDvD8HVfmiBvVsiWR3aAuquZwAPehmsbJOqErsmnsM2ugAehs','2024-04-25 14:25:45','2024-05-03 14:25:01'),(2,'v.finogentov','v.finogentov@karofilm.ru',NULL,'$2y$12$a4Html/IbhNo8fB/jLLwk.1dt3wo11czRdgCr.5Du97DZf85Cx1F.','dPMhdln05FKz9pebQy5B44MjTY3eWYObxX1j9PJfqlp7SHItUyawSABdfYpK','2024-04-25 14:58:44','2024-04-25 14:58:44'),(3,'karosupport','karosupport@karofilm.ru',NULL,'$2y$12$lxh1l1nXmoXNV4piCVTzn.oHTBxh.vXV130QvOpIHSRoxUots1QNe',NULL,'2024-07-11 09:40:38','2024-07-11 09:40:38');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `workstations`
--

DROP TABLE IF EXISTS `workstations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `workstations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `workstations`
--

LOCK TABLES `workstations` WRITE;
/*!40000 ALTER TABLE `workstations` DISABLE KEYS */;
INSERT INTO `workstations` VALUES (1,'WWW','2024-05-03 12:27:32','2024-05-03 12:27:32'),(2,'MOB','2024-05-21 10:16:32','2024-05-21 10:16:32');
/*!40000 ALTER TABLE `workstations` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-01-09 12:33:25
