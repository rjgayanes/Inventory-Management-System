-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: sorsu-bc_ims
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
-- Table structure for table `activity_log`
--

DROP TABLE IF EXISTS `activity_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `activity_log` (
  `log_ID` int(11) NOT NULL AUTO_INCREMENT,
  `action` varchar(255) NOT NULL,
  `timestamp` datetime DEFAULT current_timestamp(),
  `user_ID` int(11) DEFAULT NULL,
  PRIMARY KEY (`log_ID`),
  KEY `user_ID` (`user_ID`),
  CONSTRAINT `activity_log_ibfk_1` FOREIGN KEY (`user_ID`) REFERENCES `users` (`user_ID`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=355 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `activity_log`
--

LOCK TABLES `activity_log` WRITE;
/*!40000 ALTER TABLE `activity_log` DISABLE KEYS */;
INSERT INTO `activity_log` VALUES (351,'Updated profile information','2025-10-02 23:40:13',1),(352,'Searched item units by name (len=0)','2025-10-02 23:48:09',1),(353,'Searched item units by name (len=0)','2025-10-02 23:48:09',1),(354,'Searched item units by name (len=0)','2025-10-02 23:48:21',1);
/*!40000 ALTER TABLE `activity_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `equipment_types`
--

DROP TABLE IF EXISTS `equipment_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `equipment_types` (
  `type_ID` int(11) NOT NULL AUTO_INCREMENT,
  `type_name` varchar(150) NOT NULL,
  `classification` enum('Property Plant and Equipment','Semi-Expendable Property') NOT NULL,
  PRIMARY KEY (`type_ID`),
  UNIQUE KEY `type_name` (`type_name`)
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `equipment_types`
--

LOCK TABLES `equipment_types` WRITE;
/*!40000 ALTER TABLE `equipment_types` DISABLE KEYS */;
INSERT INTO `equipment_types` VALUES (1,'Office Equipment (PPE)','Property Plant and Equipment'),(2,'Furniture and Fixture (PPE)','Property Plant and Equipment'),(3,'Communication Equipment (PPE)','Property Plant and Equipment'),(4,'Information and Communication Technology Equipment (PPE)','Property Plant and Equipment'),(5,'Medical and Dental Equipment (PPE)','Property Plant and Equipment'),(6,'Sports Equipment (PPE)','Property Plant and Equipment'),(7,'Books (PPE)','Property Plant and Equipment'),(8,'Other Materials and Equipment (PPE)','Property Plant and Equipment'),(9,'Office Equipment (Semi-Expendable)','Semi-Expendable Property'),(10,'Furniture and Fixture (Semi-Expendable)','Semi-Expendable Property'),(11,'Communication Equipment (Semi-Expendable)','Semi-Expendable Property'),(12,'Information and Communication Technology Equipment (Semi-Expendable)','Semi-Expendable Property'),(13,'Medical and Dental Equipment (Semi-Expendable)','Semi-Expendable Property'),(14,'Sports Equipment (Semi-Expendable)','Semi-Expendable Property'),(15,'Books (Semi-Expendable)','Semi-Expendable Property'),(16,'Other Materials and Equipment (Semi-Expendable)','Semi-Expendable Property'),(17,'LAND (PPE)','Property Plant and Equipment'),(18,'LAND IMPROVEMENT (PPE)','Property Plant and Equipment'),(19,'BUILDINGS (PPE)','Property Plant and Equipment'),(20,'SCHOOL BUILDINGS (PPE)','Property Plant and Equipment'),(21,'OTHER STRUCTURE (PPE)','Property Plant and Equipment'),(22,'HOSTELS AND DORMITORIES (PPE)','Property Plant and Equipment'),(23,'POWER SUPPLY SYSTEM (PPE)','Property Plant and Equipment'),(24,'ROADNETWORKS (PPE)','Property Plant and Equipment'),(25,'OTHER MACHINERY AND EQUIPMENT (PPE)','Property Plant and Equipment'),(26,'MOTOR VEHICLE (PPE)','Property Plant and Equipment'),(27,'MEDICAL EQUIPMENT (PPE)','Property Plant and Equipment'),(28,'OTHER PROPERTY PLANT AND EQUIPMENT (PPE)','Property Plant and Equipment'),(29,'WATER SYSTEM (PPE)','Property Plant and Equipment');
/*!40000 ALTER TABLE `equipment_types` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `fund_sources`
--

DROP TABLE IF EXISTS `fund_sources`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fund_sources` (
  `fund_ID` int(11) NOT NULL AUTO_INCREMENT,
  `fund_name` varchar(100) NOT NULL,
  `fund_code` varchar(20) NOT NULL,
  PRIMARY KEY (`fund_ID`),
  UNIQUE KEY `fund_code` (`fund_code`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `fund_sources`
--

LOCK TABLES `fund_sources` WRITE;
/*!40000 ALTER TABLE `fund_sources` DISABLE KEYS */;
INSERT INTO `fund_sources` VALUES (1,'Maintenance and Other Operating Expenses','FUND 101 - REGULAR'),(2,'Special Trust Fund','FUND 164 - STF'),(3,'Income Generating Project','FUND 164 - IGP');
/*!40000 ALTER TABLE `fund_sources` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `item_units`
--

DROP TABLE IF EXISTS `item_units`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `item_units` (
  `unit_ID` int(11) NOT NULL AUTO_INCREMENT,
  `item_ID` int(11) NOT NULL,
  `barcode` varchar(50) NOT NULL,
  `barcode_image` varchar(255) DEFAULT NULL,
  `unit_image` varchar(255) DEFAULT NULL,
  `assign_to` int(11) DEFAULT NULL,
  `item_whereabouts` varchar(255) DEFAULT NULL,
  `status` enum('Available','Assigned') DEFAULT 'Available',
  `item_condition` enum('Good Condition','Defective','Unserviceable') DEFAULT 'Good Condition',
  `condition_updated_at` datetime DEFAULT current_timestamp(),
  `status_updated_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`unit_ID`),
  UNIQUE KEY `barcode` (`barcode`),
  KEY `item_ID` (`item_ID`),
  KEY `assign_to` (`assign_to`),
  CONSTRAINT `item_units_ibfk_1` FOREIGN KEY (`item_ID`) REFERENCES `items` (`item_ID`) ON DELETE CASCADE,
  CONSTRAINT `item_units_ibfk_2` FOREIGN KEY (`assign_to`) REFERENCES `persons` (`person_ID`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=119 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `item_units`
--

LOCK TABLES `item_units` WRITE;
/*!40000 ALTER TABLE `item_units` DISABLE KEYS */;
INSERT INTO `item_units` VALUES (3,8,'ITEM-LM-FFS-2509-0009','./uploads/barcodes/barcode_ITEM-LM-FFS-2509-0009.png',NULL,1,NULL,'Assigned','Good Condition','2025-09-27 20:59:34','2025-09-27 21:00:42'),(4,8,'ITEM-LM-FFS-2509-0010','./uploads/barcodes/barcode_ITEM-LM-FFS-2509-0010.png','./uploads/unit_images/68d7fa36c754f_unit.png',1,'','Assigned','Defective','2025-09-27 22:54:21','2025-09-27 22:54:21'),(5,9,'ITEM--CES-2509-0010','./uploads/barcodes/barcode_ITEM--CES-2509-0010.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-27 21:24:16','2025-09-27 21:24:16'),(6,9,'ITEM--CES-2509-0011','./uploads/barcodes/barcode_ITEM--CES-2509-0011.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-27 21:24:16','2025-09-27 21:24:16'),(7,9,'ITEM--CES-2509-0012','./uploads/barcodes/barcode_ITEM--CES-2509-0012.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-27 21:24:16','2025-09-27 21:24:16'),(8,10,'ITEM--ICT-2509-0011','./uploads/barcodes/barcode_ITEM--ICT-2509-0011.png','./uploads/unit_images/68d7fa6a8e923_unit.png',2,'social hall','Assigned','Defective','2025-09-27 22:53:30','2025-09-27 22:53:30'),(9,10,'ITEM--ICT-2509-0012','./uploads/barcodes/barcode_ITEM--ICT-2509-0012.png',NULL,NULL,'football field','Available','Good Condition','2025-09-27 21:40:18','2025-09-27 21:40:18'),(10,11,'ITEM--FFS-2509-0012','./uploads/barcodes/barcode_ITEM--FFS-2509-0012.png',NULL,2,'','Assigned','Unserviceable','2025-10-01 00:19:59','2025-10-01 00:19:59'),(11,11,'ITEM--FFS-2509-0013','./uploads/barcodes/barcode_ITEM--FFS-2509-0013.png','./uploads/unit_images/68d7ef7b15d0d_unit.png',1,'social hall','Assigned','Good Condition','2025-09-27 22:06:51','2025-09-27 22:06:51'),(12,11,'ITEM--FFS-2509-0014','./uploads/barcodes/barcode_ITEM--FFS-2509-0014.png','./uploads/unit_images/68d7fa0bade68_unit.png',1,'social hall','Assigned','Defective','2025-09-27 22:51:55','2025-09-27 22:51:55'),(13,11,'ITEM--FFS-2509-0015','./uploads/barcodes/barcode_ITEM--FFS-2509-0015.png',NULL,2,'','Assigned','Good Condition','2025-09-30 22:56:56','2025-09-30 22:56:56'),(14,11,'ITEM--FFS-2509-0016','./uploads/barcodes/barcode_ITEM--FFS-2509-0016.png','./uploads/unit_images/68dbef801ef29_unit.png',1,'grand stand','Assigned','Good Condition','2025-09-30 22:56:00','2025-09-30 22:56:00'),(15,12,'ITEM--BSE-2509-0013','./uploads/barcodes/barcode_ITEM--BSE-2509-0013.png','./uploads/unit_images/68da2b52876fb_unit.png',1,'ccb avr','Assigned','Good Condition','2025-09-29 14:46:42','2025-09-29 14:46:42'),(16,12,'ITEM--BSE-2509-0014','./uploads/barcodes/barcode_ITEM--BSE-2509-0014.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:37','2025-09-29 14:43:37'),(17,12,'ITEM--BSE-2509-0015','./uploads/barcodes/barcode_ITEM--BSE-2509-0015.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:37','2025-09-29 14:43:37'),(18,12,'ITEM--BSE-2509-0016','./uploads/barcodes/barcode_ITEM--BSE-2509-0016.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:37','2025-09-29 14:43:37'),(19,12,'ITEM--BSE-2509-0017','./uploads/barcodes/barcode_ITEM--BSE-2509-0017.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:37','2025-09-29 14:43:37'),(20,12,'ITEM--BSE-2509-0018','./uploads/barcodes/barcode_ITEM--BSE-2509-0018.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:37','2025-09-29 14:43:37'),(21,12,'ITEM--BSE-2509-0019','./uploads/barcodes/barcode_ITEM--BSE-2509-0019.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:37','2025-09-29 14:43:37'),(22,12,'ITEM--BSE-2509-0020','./uploads/barcodes/barcode_ITEM--BSE-2509-0020.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:37','2025-09-29 14:43:37'),(23,12,'ITEM--BSE-2509-0021','./uploads/barcodes/barcode_ITEM--BSE-2509-0021.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:37','2025-09-29 14:43:37'),(24,12,'ITEM--BSE-2509-0022','./uploads/barcodes/barcode_ITEM--BSE-2509-0022.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:37','2025-09-29 14:43:37'),(25,12,'ITEM--BSE-2509-0023','./uploads/barcodes/barcode_ITEM--BSE-2509-0023.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:37','2025-09-29 14:43:37'),(26,12,'ITEM--BSE-2509-0024','./uploads/barcodes/barcode_ITEM--BSE-2509-0024.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:37','2025-09-29 14:43:37'),(27,12,'ITEM--BSE-2509-0025','./uploads/barcodes/barcode_ITEM--BSE-2509-0025.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:37','2025-09-29 14:43:37'),(28,12,'ITEM--BSE-2509-0026','./uploads/barcodes/barcode_ITEM--BSE-2509-0026.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:37','2025-09-29 14:43:37'),(29,12,'ITEM--BSE-2509-0027','./uploads/barcodes/barcode_ITEM--BSE-2509-0027.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:37','2025-09-29 14:43:37'),(30,12,'ITEM--BSE-2509-0028','./uploads/barcodes/barcode_ITEM--BSE-2509-0028.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:37','2025-09-29 14:43:37'),(31,12,'ITEM--BSE-2509-0029','./uploads/barcodes/barcode_ITEM--BSE-2509-0029.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:37','2025-09-29 14:43:37'),(32,12,'ITEM--BSE-2509-0030','./uploads/barcodes/barcode_ITEM--BSE-2509-0030.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:37','2025-09-29 14:43:37'),(33,12,'ITEM--BSE-2509-0031','./uploads/barcodes/barcode_ITEM--BSE-2509-0031.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:37','2025-09-29 14:43:37'),(34,12,'ITEM--BSE-2509-0032','./uploads/barcodes/barcode_ITEM--BSE-2509-0032.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:37','2025-09-29 14:43:37'),(35,12,'ITEM--BSE-2509-0033','./uploads/barcodes/barcode_ITEM--BSE-2509-0033.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:37','2025-09-29 14:43:37'),(36,12,'ITEM--BSE-2509-0034','./uploads/barcodes/barcode_ITEM--BSE-2509-0034.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:37','2025-09-29 14:43:37'),(37,12,'ITEM--BSE-2509-0035','./uploads/barcodes/barcode_ITEM--BSE-2509-0035.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:37','2025-09-29 14:43:37'),(38,12,'ITEM--BSE-2509-0036','./uploads/barcodes/barcode_ITEM--BSE-2509-0036.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:37','2025-09-29 14:43:37'),(39,12,'ITEM--BSE-2509-0037','./uploads/barcodes/barcode_ITEM--BSE-2509-0037.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:37','2025-09-29 14:43:37'),(40,12,'ITEM--BSE-2509-0038','./uploads/barcodes/barcode_ITEM--BSE-2509-0038.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:37','2025-09-29 14:43:37'),(41,12,'ITEM--BSE-2509-0039','./uploads/barcodes/barcode_ITEM--BSE-2509-0039.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:38','2025-09-29 14:43:38'),(42,12,'ITEM--BSE-2509-0040','./uploads/barcodes/barcode_ITEM--BSE-2509-0040.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:38','2025-09-29 14:43:38'),(43,12,'ITEM--BSE-2509-0041','./uploads/barcodes/barcode_ITEM--BSE-2509-0041.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:38','2025-09-29 14:43:38'),(44,12,'ITEM--BSE-2509-0042','./uploads/barcodes/barcode_ITEM--BSE-2509-0042.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:38','2025-09-29 14:43:38'),(45,12,'ITEM--BSE-2509-0043','./uploads/barcodes/barcode_ITEM--BSE-2509-0043.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:38','2025-09-29 14:43:38'),(46,12,'ITEM--BSE-2509-0044','./uploads/barcodes/barcode_ITEM--BSE-2509-0044.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:38','2025-09-29 14:43:38'),(47,12,'ITEM--BSE-2509-0045','./uploads/barcodes/barcode_ITEM--BSE-2509-0045.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:38','2025-09-29 14:43:38'),(48,12,'ITEM--BSE-2509-0046','./uploads/barcodes/barcode_ITEM--BSE-2509-0046.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:38','2025-09-29 14:43:38'),(49,12,'ITEM--BSE-2509-0047','./uploads/barcodes/barcode_ITEM--BSE-2509-0047.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:38','2025-09-29 14:43:38'),(50,12,'ITEM--BSE-2509-0048','./uploads/barcodes/barcode_ITEM--BSE-2509-0048.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:38','2025-09-29 14:43:38'),(51,12,'ITEM--BSE-2509-0049','./uploads/barcodes/barcode_ITEM--BSE-2509-0049.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:38','2025-09-29 14:43:38'),(52,12,'ITEM--BSE-2509-0050','./uploads/barcodes/barcode_ITEM--BSE-2509-0050.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:38','2025-09-29 14:43:38'),(53,12,'ITEM--BSE-2509-0051','./uploads/barcodes/barcode_ITEM--BSE-2509-0051.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:38','2025-09-29 14:43:38'),(54,12,'ITEM--BSE-2509-0052','./uploads/barcodes/barcode_ITEM--BSE-2509-0052.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:38','2025-09-29 14:43:38'),(55,12,'ITEM--BSE-2509-0053','./uploads/barcodes/barcode_ITEM--BSE-2509-0053.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:38','2025-09-29 14:43:38'),(56,12,'ITEM--BSE-2509-0054','./uploads/barcodes/barcode_ITEM--BSE-2509-0054.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:38','2025-09-29 14:43:38'),(57,12,'ITEM--BSE-2509-0055','./uploads/barcodes/barcode_ITEM--BSE-2509-0055.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:38','2025-09-29 14:43:38'),(58,12,'ITEM--BSE-2509-0056','./uploads/barcodes/barcode_ITEM--BSE-2509-0056.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:38','2025-09-29 14:43:38'),(59,12,'ITEM--BSE-2509-0057','./uploads/barcodes/barcode_ITEM--BSE-2509-0057.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:38','2025-09-29 14:43:38'),(60,12,'ITEM--BSE-2509-0058','./uploads/barcodes/barcode_ITEM--BSE-2509-0058.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:38','2025-09-29 14:43:38'),(61,12,'ITEM--BSE-2509-0059','./uploads/barcodes/barcode_ITEM--BSE-2509-0059.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:38','2025-09-29 14:43:38'),(62,12,'ITEM--BSE-2509-0060','./uploads/barcodes/barcode_ITEM--BSE-2509-0060.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:38','2025-09-29 14:43:38'),(63,12,'ITEM--BSE-2509-0061','./uploads/barcodes/barcode_ITEM--BSE-2509-0061.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:38','2025-09-29 14:43:38'),(64,12,'ITEM--BSE-2509-0062','./uploads/barcodes/barcode_ITEM--BSE-2509-0062.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:38','2025-09-29 14:43:38'),(65,12,'ITEM--BSE-2509-0063','./uploads/barcodes/barcode_ITEM--BSE-2509-0063.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:38','2025-09-29 14:43:38'),(66,12,'ITEM--BSE-2509-0064','./uploads/barcodes/barcode_ITEM--BSE-2509-0064.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:38','2025-09-29 14:43:38'),(67,12,'ITEM--BSE-2509-0065','./uploads/barcodes/barcode_ITEM--BSE-2509-0065.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:38','2025-09-29 14:43:38'),(68,12,'ITEM--BSE-2509-0066','./uploads/barcodes/barcode_ITEM--BSE-2509-0066.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:38','2025-09-29 14:43:38'),(69,12,'ITEM--BSE-2509-0067','./uploads/barcodes/barcode_ITEM--BSE-2509-0067.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:38','2025-09-29 14:43:38'),(70,12,'ITEM--BSE-2509-0068','./uploads/barcodes/barcode_ITEM--BSE-2509-0068.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:38','2025-09-29 14:43:38'),(71,12,'ITEM--BSE-2509-0069','./uploads/barcodes/barcode_ITEM--BSE-2509-0069.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:38','2025-09-29 14:43:38'),(72,12,'ITEM--BSE-2509-0070','./uploads/barcodes/barcode_ITEM--BSE-2509-0070.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:38','2025-09-29 14:43:38'),(73,12,'ITEM--BSE-2509-0071','./uploads/barcodes/barcode_ITEM--BSE-2509-0071.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:38','2025-09-29 14:43:38'),(74,12,'ITEM--BSE-2509-0072','./uploads/barcodes/barcode_ITEM--BSE-2509-0072.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:38','2025-09-29 14:43:38'),(75,12,'ITEM--BSE-2509-0073','./uploads/barcodes/barcode_ITEM--BSE-2509-0073.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:38','2025-09-29 14:43:38'),(76,12,'ITEM--BSE-2509-0074','./uploads/barcodes/barcode_ITEM--BSE-2509-0074.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:38','2025-09-29 14:43:38'),(77,12,'ITEM--BSE-2509-0075','./uploads/barcodes/barcode_ITEM--BSE-2509-0075.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:38','2025-09-29 14:43:38'),(78,12,'ITEM--BSE-2509-0076','./uploads/barcodes/barcode_ITEM--BSE-2509-0076.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:38','2025-09-29 14:43:38'),(79,12,'ITEM--BSE-2509-0077','./uploads/barcodes/barcode_ITEM--BSE-2509-0077.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:38','2025-09-29 14:43:38'),(80,12,'ITEM--BSE-2509-0078','./uploads/barcodes/barcode_ITEM--BSE-2509-0078.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:38','2025-09-29 14:43:38'),(81,12,'ITEM--BSE-2509-0079','./uploads/barcodes/barcode_ITEM--BSE-2509-0079.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:38','2025-09-29 14:43:38'),(82,12,'ITEM--BSE-2509-0080','./uploads/barcodes/barcode_ITEM--BSE-2509-0080.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:38','2025-09-29 14:43:38'),(83,12,'ITEM--BSE-2509-0081','./uploads/barcodes/barcode_ITEM--BSE-2509-0081.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:38','2025-09-29 14:43:38'),(84,12,'ITEM--BSE-2509-0082','./uploads/barcodes/barcode_ITEM--BSE-2509-0082.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:38','2025-09-29 14:43:38'),(85,12,'ITEM--BSE-2509-0083','./uploads/barcodes/barcode_ITEM--BSE-2509-0083.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:38','2025-09-29 14:43:38'),(86,12,'ITEM--BSE-2509-0084','./uploads/barcodes/barcode_ITEM--BSE-2509-0084.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:38','2025-09-29 14:43:38'),(87,12,'ITEM--BSE-2509-0085','./uploads/barcodes/barcode_ITEM--BSE-2509-0085.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:38','2025-09-29 14:43:38'),(88,12,'ITEM--BSE-2509-0086','./uploads/barcodes/barcode_ITEM--BSE-2509-0086.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:38','2025-09-29 14:43:38'),(89,12,'ITEM--BSE-2509-0087','./uploads/barcodes/barcode_ITEM--BSE-2509-0087.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:38','2025-09-29 14:43:38'),(90,12,'ITEM--BSE-2509-0088','./uploads/barcodes/barcode_ITEM--BSE-2509-0088.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:38','2025-09-29 14:43:38'),(91,12,'ITEM--BSE-2509-0089','./uploads/barcodes/barcode_ITEM--BSE-2509-0089.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:38','2025-09-29 14:43:38'),(92,12,'ITEM--BSE-2509-0090','./uploads/barcodes/barcode_ITEM--BSE-2509-0090.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:38','2025-09-29 14:43:38'),(93,12,'ITEM--BSE-2509-0091','./uploads/barcodes/barcode_ITEM--BSE-2509-0091.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:38','2025-09-29 14:43:38'),(94,12,'ITEM--BSE-2509-0092','./uploads/barcodes/barcode_ITEM--BSE-2509-0092.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:38','2025-09-29 14:43:38'),(95,12,'ITEM--BSE-2509-0093','./uploads/barcodes/barcode_ITEM--BSE-2509-0093.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:38','2025-09-29 14:43:38'),(96,12,'ITEM--BSE-2509-0094','./uploads/barcodes/barcode_ITEM--BSE-2509-0094.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:38','2025-09-29 14:43:38'),(97,12,'ITEM--BSE-2509-0095','./uploads/barcodes/barcode_ITEM--BSE-2509-0095.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:38','2025-09-29 14:43:38'),(98,12,'ITEM--BSE-2509-0096','./uploads/barcodes/barcode_ITEM--BSE-2509-0096.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:38','2025-09-29 14:43:38'),(99,12,'ITEM--BSE-2509-0097','./uploads/barcodes/barcode_ITEM--BSE-2509-0097.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:38','2025-09-29 14:43:38'),(100,12,'ITEM--BSE-2509-0098','./uploads/barcodes/barcode_ITEM--BSE-2509-0098.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:38','2025-09-29 14:43:38'),(101,12,'ITEM--BSE-2509-0099','./uploads/barcodes/barcode_ITEM--BSE-2509-0099.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:38','2025-09-29 14:43:38'),(102,12,'ITEM--BSE-2509-0100','./uploads/barcodes/barcode_ITEM--BSE-2509-0100.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:38','2025-09-29 14:43:38'),(103,12,'ITEM--BSE-2509-0101','./uploads/barcodes/barcode_ITEM--BSE-2509-0101.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:38','2025-09-29 14:43:38'),(104,12,'ITEM--BSE-2509-0102','./uploads/barcodes/barcode_ITEM--BSE-2509-0102.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:38','2025-09-29 14:43:38'),(105,12,'ITEM--BSE-2509-0103','./uploads/barcodes/barcode_ITEM--BSE-2509-0103.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:38','2025-09-29 14:43:38'),(106,12,'ITEM--BSE-2509-0104','./uploads/barcodes/barcode_ITEM--BSE-2509-0104.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:38','2025-09-29 14:43:38'),(107,12,'ITEM--BSE-2509-0105','./uploads/barcodes/barcode_ITEM--BSE-2509-0105.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:38','2025-09-29 14:43:38'),(108,12,'ITEM--BSE-2509-0106','./uploads/barcodes/barcode_ITEM--BSE-2509-0106.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:38','2025-09-29 14:43:38'),(109,12,'ITEM--BSE-2509-0107','./uploads/barcodes/barcode_ITEM--BSE-2509-0107.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:38','2025-09-29 14:43:38'),(110,12,'ITEM--BSE-2509-0108','./uploads/barcodes/barcode_ITEM--BSE-2509-0108.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:38','2025-09-29 14:43:38'),(111,12,'ITEM--BSE-2509-0109','./uploads/barcodes/barcode_ITEM--BSE-2509-0109.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:38','2025-09-29 14:43:38'),(112,12,'ITEM--BSE-2509-0110','./uploads/barcodes/barcode_ITEM--BSE-2509-0110.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:38','2025-09-29 14:43:38'),(113,12,'ITEM--BSE-2509-0111','./uploads/barcodes/barcode_ITEM--BSE-2509-0111.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:38','2025-09-29 14:43:38'),(114,12,'ITEM--BSE-2509-0112','./uploads/barcodes/barcode_ITEM--BSE-2509-0112.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:38','2025-09-29 14:43:38'),(115,14,'ITEM-A-OES-2510-0015','./uploads/barcodes/barcode_ITEM-A-OES-2510-0015.png',NULL,NULL,NULL,'Available','Good Condition','2025-10-01 09:30:50','2025-10-01 09:30:50'),(116,14,'ITEM-A-OES-2510-0016','./uploads/barcodes/barcode_ITEM-A-OES-2510-0016.png','./uploads/unit_images/68dd38aec3c4a_unit.png',1,'ccb avr','Assigned','Good Condition','2025-10-01 22:20:30','2025-10-01 22:20:30'),(117,15,'ITEM-LM-FFS-2510-0016','./uploads/barcodes/barcode_ITEM-LM-FFS-2510-0016.png',NULL,NULL,NULL,'Available','Good Condition','2025-10-02 22:52:13','2025-10-02 22:52:13'),(118,15,'ITEM-LM-FFS-2510-0017','./uploads/barcodes/barcode_ITEM-LM-FFS-2510-0017.png',NULL,9,'Admin Aide Office','Assigned','Good Condition','2025-10-02 22:54:09','2025-10-02 22:54:09');
/*!40000 ALTER TABLE `item_units` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `items`
--

DROP TABLE IF EXISTS `items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `items` (
  `item_ID` int(11) NOT NULL AUTO_INCREMENT,
  `item_name` varchar(150) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `unit_of_measure` varchar(50) DEFAULT NULL,
  `unit_cost` decimal(12,2) DEFAULT NULL,
  `unit_quantity` decimal(12,2) DEFAULT 0.00,
  `unit_total_cost` decimal(14,2) DEFAULT 0.00,
  `property_number` varchar(100) DEFAULT NULL,
  `item_classification` varchar(50) NOT NULL,
  `fund_ID` int(11) DEFAULT NULL,
  `type_ID` int(11) DEFAULT NULL,
  `acquisition_date` date DEFAULT NULL,
  `estimated_useful_life` varchar(100) DEFAULT NULL,
  `item_image` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`item_ID`),
  KEY `fund_ID` (`fund_ID`),
  KEY `type_ID` (`type_ID`),
  CONSTRAINT `items_ibfk_1` FOREIGN KEY (`fund_ID`) REFERENCES `fund_sources` (`fund_ID`) ON DELETE SET NULL,
  CONSTRAINT `items_ibfk_2` FOREIGN KEY (`type_ID`) REFERENCES `equipment_types` (`type_ID`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `items`
--

LOCK TABLES `items` WRITE;
/*!40000 ALTER TABLE `items` DISABLE KEYS */;
INSERT INTO `items` VALUES (8,'Laminating Machine','heavy duty','unit',1500.00,2.00,3000.00,'2025-002','Semi-Expendable Property',3,10,'2025-09-01','3 yrs','/Inventory_Management_System/public/uploads/item_images/68d7dfb538792_barcode.jpg','2025-09-27 20:59:33'),(9,'sewing machine','asascascca','unit',4500.00,3.00,13500.00,'221-2024-001-002','Semi-Expendable Property',1,11,'2025-09-23','3 yrs','/Inventory_Management_System/public/uploads/item_images/68d7e57fda573_IMG_20240307_110509_577.jpg','2025-09-27 21:24:15'),(10,'land mine','csaadvda','unit',1500.00,2.00,3000.00,'2024-002','Semi-Expendable Property',1,12,'2025-09-18','3 yrs','/Inventory_Management_System/public/uploads/item_images/68d7e92249b0a_barcode.jpg','2025-09-27 21:39:46'),(11,'industrial fan','dzvsdvdsvs','unit',1500.00,5.00,7500.00,'221-2025-005','Semi-Expendable Property',1,10,'2025-09-24','3 yrs','/Inventory_Management_System/public/uploads/item_images/68d7ea599919f_IMG_20240307_110752_016.jpg','2025-09-27 21:44:57'),(12,'water dispenser','library books','pc',500.00,100.00,50000.00,'221-2024-001-002','Semi-Expendable Property',1,15,'2024-09-16','10 yrs.','/Inventory_Management_System/public/uploads/item_images/68da2a98911ff_Screenshot 2025-09-09 232941.png','2025-09-29 14:43:36'),(13,'builduing','building for ict','unit',1442444.00,1.00,1442444.00,'05-215-01','Property Plant and Equipment',2,19,'2025-09-10','','/Inventory_Management_System/public/uploads/item_images/68dbf1610aeb6_551876066_731073779973130_7052705692278240968_n.png','2025-09-30 23:04:01'),(14,'Aircondition','2HP, split type, inverter','unit',49850.00,2.00,99700.00,'221-2024-014','Semi-Expendable Property',2,9,'2025-10-01','3 yrs','/Inventory_Management_System/public/uploads/item_images/68dc8449858c9_Screenshot 2025-09-19 105824.png','2025-10-01 09:30:49'),(15,'Laminating Machine','sdsfewefw','unit',1500.00,2.00,3000.00,'221-2025-013','Semi-Expendable Property',3,10,'2024-10-01','3 yrs',NULL,'2025-10-02 22:52:12');
/*!40000 ALTER TABLE `items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `persons`
--

DROP TABLE IF EXISTS `persons`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `persons` (
  `person_ID` int(11) NOT NULL AUTO_INCREMENT,
  `first_name` varchar(150) NOT NULL,
  `last_name` varchar(150) NOT NULL,
  `professional_designations` varchar(50) DEFAULT NULL,
  `office_name` varchar(100) DEFAULT NULL,
  `role` varchar(50) DEFAULT NULL,
  `status` enum('Active','Inactive') DEFAULT 'Active',
  `profile_image` varchar(255) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`person_ID`),
  KEY `created_by` (`created_by`),
  CONSTRAINT `persons_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`user_ID`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `persons`
--

LOCK TABLES `persons` WRITE;
/*!40000 ALTER TABLE `persons` DISABLE KEYS */;
INSERT INTO `persons` VALUES (1,'Benjamin','Ambrose','','BME Dean\'s Office','Instructor I','Active','68d7f2e17f15e.jpg',1),(2,'Ramil','Hufancia','','Supply and Property Office','Supply Officer','Active','68d7f2c636942.png',1),(3,'Denniz','Malonzo',NULL,'Admin Aide & Admin Asst','Admin Aide II','Active',NULL,1),(4,'Giovanie Jennel','Cielo',NULL,'Admin Aide & Admin Asst','Admin Aide I','Active',NULL,1),(5,'Jefrel','Botalon',NULL,'Admin Aide & Admin Asst','Admin Aide I','Active',NULL,1),(6,'Noel','Goyal',NULL,'Admin Aide & Admin Asst','Admin Aide I','Active',NULL,1),(7,'Oliver','Gimoro',NULL,'Admin Aide & Admin Asst','N/A','Active',NULL,1),(8,'Oscar','Carinola',NULL,'Admin Aide & Admin Asst','Admin Aide III','Active',NULL,1),(9,'Salvador','Gohar',NULL,'Admin Aide & Admin Asst','Admin Aide IV','Active',NULL,1),(10,'Abegail','Fulgar','','BME Dean\'s Office','Instructor I  - Program Chair, BSE','Active',NULL,1),(11,'Michael','Bongalonta','','BME Dean\'s Office','Associate Professor III','Active',NULL,1);
/*!40000 ALTER TABLE `persons` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `registered_barcode`
--

DROP TABLE IF EXISTS `registered_barcode`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `registered_barcode` (
  `registered_ID` int(11) NOT NULL AUTO_INCREMENT,
  `item_name` varchar(255) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `unit_cost` decimal(12,2) DEFAULT NULL,
  `unit_quantity` decimal(12,2) DEFAULT 0.00,
  `unit_total_cost` decimal(14,2) DEFAULT 0.00,
  `property_number` varchar(100) DEFAULT NULL,
  `item_classification` varchar(50) NOT NULL,
  `fund_ID` int(11) DEFAULT NULL,
  `type_ID` int(11) DEFAULT NULL,
  `acquisition_date` date DEFAULT NULL,
  `estimated_useful_life` varchar(100) DEFAULT NULL,
  `item_image` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`registered_ID`),
  KEY `fund_ID` (`fund_ID`),
  KEY `type_ID` (`type_ID`),
  CONSTRAINT `registered_barcode_ibfk_1` FOREIGN KEY (`fund_ID`) REFERENCES `fund_sources` (`fund_ID`) ON DELETE SET NULL,
  CONSTRAINT `registered_barcode_ibfk_2` FOREIGN KEY (`type_ID`) REFERENCES `equipment_types` (`type_ID`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `registered_barcode`
--

LOCK TABLES `registered_barcode` WRITE;
/*!40000 ALTER TABLE `registered_barcode` DISABLE KEYS */;
/*!40000 ALTER TABLE `registered_barcode` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `security_question`
--

DROP TABLE IF EXISTS `security_question`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `security_question` (
  `question_ID` int(11) NOT NULL AUTO_INCREMENT,
  `question_text` varchar(255) NOT NULL,
  PRIMARY KEY (`question_ID`),
  UNIQUE KEY `question_text` (`question_text`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `security_question`
--

LOCK TABLES `security_question` WRITE;
/*!40000 ALTER TABLE `security_question` DISABLE KEYS */;
INSERT INTO `security_question` VALUES (3,'What is the name of the town where you were born?'),(9,'What is the name of your best childhood friend?'),(10,'What is your father’s middle name?'),(8,'What is your favorite book?'),(5,'What is your favorite food?'),(7,'What is your favorite movie?'),(1,'What is your mother’s maiden name?'),(6,'What was the make of your first car?'),(2,'What was the name of your first pet?'),(4,'What was your first school’s name?');
/*!40000 ALTER TABLE `security_question` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `task_manager`
--

DROP TABLE IF EXISTS `task_manager`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `task_manager` (
  `task_ID` int(11) NOT NULL AUTO_INCREMENT,
  `user_ID` int(11) DEFAULT NULL,
  `classification` enum('Notes','Reminders','Checklist') DEFAULT NULL,
  `title` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `status` enum('Pending','Completed','Cancelled') DEFAULT 'Pending',
  `due_date` date DEFAULT NULL,
  `due_time` time DEFAULT NULL,
  `created_time` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`task_ID`),
  KEY `user_ID` (`user_ID`),
  CONSTRAINT `task_manager_ibfk_1` FOREIGN KEY (`user_ID`) REFERENCES `users` (`user_ID`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=53 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `task_manager`
--

LOCK TABLES `task_manager` WRITE;
/*!40000 ALTER TABLE `task_manager` DISABLE KEYS */;
INSERT INTO `task_manager` VALUES (46,1,'Checklist','aas','asasas','Completed','2025-10-02','00:46:00','2025-10-02 00:46:55'),(47,1,'Notes','rgdfgdf','dfdfgdfg','Completed',NULL,NULL,'2025-10-02 17:44:05'),(48,1,'Reminders','fdfgg','dfdfgdfg','Cancelled','2025-10-02','00:52:00','2025-10-02 00:47:49'),(49,1,'Notes','gfghhg','bgv hghg','Completed',NULL,NULL,'2025-10-02 01:05:45'),(51,1,'Notes','ssfsa','faasfas','Completed',NULL,NULL,'2025-10-02 01:26:24'),(52,1,'Notes','asdasdds','sdsdasd','Cancelled',NULL,NULL,'2025-10-02 17:27:47');
/*!40000 ALTER TABLE `task_manager` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_security_answer`
--

DROP TABLE IF EXISTS `user_security_answer`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_security_answer` (
  `answer_ID` int(11) NOT NULL AUTO_INCREMENT,
  `user_ID` int(11) NOT NULL,
  `question_ID` int(11) NOT NULL,
  `answer_hash` varchar(255) NOT NULL,
  PRIMARY KEY (`answer_ID`),
  KEY `user_ID` (`user_ID`),
  KEY `question_ID` (`question_ID`),
  CONSTRAINT `user_security_answer_ibfk_1` FOREIGN KEY (`user_ID`) REFERENCES `users` (`user_ID`) ON DELETE CASCADE,
  CONSTRAINT `user_security_answer_ibfk_2` FOREIGN KEY (`question_ID`) REFERENCES `security_question` (`question_ID`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_security_answer`
--

LOCK TABLES `user_security_answer` WRITE;
/*!40000 ALTER TABLE `user_security_answer` DISABLE KEYS */;
INSERT INTO `user_security_answer` VALUES (1,1,3,'$2y$10$N.MQya8KdRr/5u/9ZUEH6OsrNJfUklU.mEPr9k9juSIgQHDHukGIG');
/*!40000 ALTER TABLE `user_security_answer` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `user_ID` int(11) NOT NULL AUTO_INCREMENT,
  `user_name` varchar(100) NOT NULL,
  `user_email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `first_name` varchar(150) NOT NULL,
  `last_name` varchar(150) NOT NULL,
  `professional_designation` varchar(50) DEFAULT NULL,
  `role` enum('Supply Officer') NOT NULL DEFAULT 'Supply Officer',
  `profile_image` varchar(255) DEFAULT NULL,
  `remember_token` varchar(64) DEFAULT NULL,
  PRIMARY KEY (`user_ID`),
  UNIQUE KEY `user_name` (`user_name`),
  UNIQUE KEY `user_email` (`user_email`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'Admin','admin@gmail.com','$2y$10$mAvnTpV2lIY8gKCBEENVYe1ViQ8ZD2r4KNqnQPhdQOcJNBW39/qn.','Arjay','Gayanes','MIT','Supply Officer','../public/uploads/profile_images/profile_68de9cddde19e0.08396396.png','c604d44a8f5689f49978ee33078c134d');
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

-- Dump completed on 2025-10-02 23:57:10
