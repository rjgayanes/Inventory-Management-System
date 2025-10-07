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
) ENGINE=InnoDB AUTO_INCREMENT=649 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `activity_log`
--

LOCK TABLES `activity_log` WRITE;
/*!40000 ALTER TABLE `activity_log` DISABLE KEYS */;
INSERT INTO `activity_log` VALUES (351,'Updated profile information','2025-10-02 23:40:13',1),(352,'Searched item units by name (len=0)','2025-10-02 23:48:09',1),(353,'Searched item units by name (len=0)','2025-10-02 23:48:09',1),(354,'Searched item units by name (len=0)','2025-10-02 23:48:21',1),(355,'Updated password','2025-10-03 00:06:21',1),(356,'User logged in','2025-10-05 22:04:03',1),(357,'Searched item units by name (len=0)','2025-10-05 22:04:43',1),(358,'Searched item units by name (len=0)','2025-10-05 22:05:06',1),(359,'Searched item units by name (len=0)','2025-10-05 22:05:07',1),(360,'Searched items by barcode (len=2)','2025-10-05 22:05:25',1),(361,'Assigned unit #117 to person #8','2025-10-05 22:05:37',1),(362,'Assigned unit #115 to person #8','2025-10-05 22:05:37',1),(363,'Searched item units by name (len=0)','2025-10-05 22:05:37',1),(364,'Assigned unit #114 to person #8','2025-10-05 22:06:19',1),(365,'Searched item units by name (len=0)','2025-10-05 22:06:19',1),(366,'Searched items by barcode (len=3)','2025-10-05 22:06:30',1),(367,'Searched items by barcode (len=2)','2025-10-05 22:06:47',1),(368,'Transferred unit #114 to person #6','2025-10-05 22:07:03',1),(369,'Assigned unit #9 to person #6','2025-10-05 22:07:03',1),(370,'Searched item units by name (len=0)','2025-10-05 22:07:03',1),(371,'Searched item units by name (len=0)','2025-10-05 22:07:08',1),(372,'Exported SEP inventory to PDF','2025-10-05 22:08:57',1),(373,'Exported SEP inventory to PDF','2025-10-05 22:54:07',1),(374,'Exported SEP inventory to PDF','2025-10-05 23:07:23',1),(375,'Exported SEP inventory to PDF','2025-10-05 23:09:05',1),(376,'Exported SEP inventory to PDF','2025-10-05 23:09:17',1),(377,'Exported SEP inventory to PDF','2025-10-05 23:10:14',1),(378,'Exported SEP inventory to PDF','2025-10-05 23:10:54',1),(379,'Exported SEP inventory to PDF','2025-10-05 23:11:44',1),(380,'Exported SEP inventory to PDF','2025-10-05 23:12:21',1),(381,'Exported SEP inventory to PDF','2025-10-05 23:25:36',1),(382,'Exported SEP inventory to PDF','2025-10-05 23:26:28',1),(383,'Exported SEP inventory to PDF','2025-10-05 23:27:37',1),(384,'Exported SEP inventory to PDF','2025-10-05 23:28:14',1),(385,'Exported SEP inventory to PDF','2025-10-05 23:28:35',1),(386,'Exported SEP inventory to PDF','2025-10-05 23:29:06',1),(387,'Exported SEP inventory to PDF','2025-10-05 23:29:36',1),(388,'Exported SEP inventory to PDF','2025-10-05 23:30:03',1),(389,'Exported SEP inventory to PDF','2025-10-05 23:34:08',1),(390,'Exported SEP inventory to PDF','2025-10-05 23:34:24',1),(391,'Exported SEP inventory to PDF','2025-10-05 23:35:08',1),(392,'Exported SEP inventory to PDF','2025-10-05 23:35:39',1),(393,'Exported SEP inventory to PDF','2025-10-05 23:35:46',1),(394,'Exported SEP inventory to PDF','2025-10-05 23:37:11',1),(395,'Exported SEP inventory to PDF','2025-10-05 23:37:43',1),(396,'Exported SEP inventory to PDF','2025-10-05 23:38:54',1),(397,'Exported SEP inventory to PDF','2025-10-05 23:39:32',1),(398,'Exported SEP inventory to PDF','2025-10-05 23:39:46',1),(399,'Exported SEP inventory to PDF','2025-10-05 23:40:14',1),(400,'Exported SEP inventory to PDF','2025-10-05 23:40:40',1),(401,'Exported SEP inventory to PDF','2025-10-05 23:41:06',1),(402,'Exported SEP inventory to PDF','2025-10-05 23:41:17',1),(403,'Exported SEP inventory to PDF','2025-10-05 23:41:36',1),(404,'Exported SEP inventory to PDF','2025-10-05 23:41:56',1),(405,'Exported SEP inventory to PDF','2025-10-05 23:42:14',1),(406,'Exported SEP inventory to PDF','2025-10-05 23:42:26',1),(407,'Exported SEP inventory to PDF','2025-10-05 23:42:34',1),(408,'Exported SEP inventory to PDF','2025-10-05 23:42:47',1),(409,'Exported SEP inventory to PDF','2025-10-05 23:43:04',1),(410,'Exported SEP inventory to PDF','2025-10-05 23:43:31',1),(411,'Exported SEP inventory to PDF','2025-10-05 23:44:07',1),(412,'Exported SEP inventory to PDF','2025-10-05 23:45:03',1),(413,'Exported SEP inventory to PDF','2025-10-05 23:45:17',1),(414,'Exported SEP inventory to PDF','2025-10-05 23:45:36',1),(415,'Exported SEP inventory to PDF','2025-10-05 23:45:45',1),(416,'Exported SEP inventory to PDF','2025-10-05 23:46:06',1),(417,'Exported SEP inventory to PDF','2025-10-05 23:48:12',1),(418,'Exported SEP inventory to PDF','2025-10-05 23:54:12',1),(419,'Exported SEP inventory to PDF','2025-10-06 00:03:33',1),(420,'Exported SEP inventory to PDF','2025-10-06 00:04:33',1),(421,'Exported SEP inventory to PDF','2025-10-06 00:04:55',1),(422,'Exported SEP inventory to PDF','2025-10-06 00:07:10',1),(423,'Exported SEP inventory to PDF','2025-10-06 00:08:27',1),(424,'Exported SEP inventory to PDF','2025-10-06 00:12:40',1),(425,'Exported SEP inventory to PDF','2025-10-06 00:12:56',1),(426,'Exported SEP inventory to PDF','2025-10-06 00:13:33',1),(427,'Exported SEP inventory to PDF','2025-10-06 00:15:13',1),(428,'Exported SEP inventory to PDF','2025-10-06 00:15:33',1),(429,'Exported SEP inventory to PDF','2025-10-06 00:16:57',1),(430,'Exported SEP inventory to PDF','2025-10-06 00:17:40',1),(431,'Exported SEP inventory to PDF','2025-10-06 00:18:11',1),(432,'Exported SEP inventory to PDF','2025-10-06 00:18:39',1),(433,'Exported SEP inventory to PDF','2025-10-06 00:19:00',1),(434,'Exported SEP inventory to PDF','2025-10-06 00:20:53',1),(435,'Exported SEP inventory to PDF','2025-10-06 00:21:31',1),(436,'Exported SEP inventory to Excel','2025-10-06 00:27:56',1),(437,'Exported unserviceable SEP inventory to PDF','2025-10-06 00:43:18',1),(438,'Exported SEP inventory to PDF','2025-10-06 00:44:09',1),(439,'Exported PPE inventory to PDF','2025-10-06 00:51:50',1),(440,'Exported SEP inventory to PDF','2025-10-06 01:07:36',1),(441,'User logged in','2025-10-06 01:08:48',1),(442,'Searched item units by name (len=0)','2025-10-06 01:20:09',1),(443,'Created item #16 - effwf','2025-10-06 01:20:46',1),(444,'Generated 2 barcodes for item_ID 16','2025-10-06 01:20:47',1),(445,'Searched item units by name (len=0)','2025-10-06 01:20:51',1),(446,'Searched item units by name (len=0)','2025-10-06 01:20:55',1),(447,'Searched items by barcode (len=2)','2025-10-06 01:21:07',1),(448,'Searched items by barcode (len=19)','2025-10-06 01:21:17',1),(449,'Assigned unit #120 to person #8','2025-10-06 01:21:29',1),(450,'Transferred unit #120 to person #8','2025-10-06 01:21:29',1),(451,'Searched item units by name (len=0)','2025-10-06 01:21:29',1),(452,'Assigned unit #119 to person #10','2025-10-06 01:21:41',1),(453,'Searched item units by name (len=0)','2025-10-06 01:21:41',1),(454,'Searched item units by name (len=0)','2025-10-06 01:31:50',1),(455,'Searched item units by name (len=0)','2025-10-06 01:31:53',1),(456,'Exported unserviceable PPE inventory to PDF','2025-10-06 01:35:00',1),(457,'Exported SEP inventory to PDF','2025-10-06 01:46:00',1),(458,'Exported SEP inventory to PDF','2025-10-06 01:46:36',1),(459,'Exported SEP inventory to PDF','2025-10-06 01:47:33',1),(460,'Exported SEP inventory to PDF','2025-10-06 01:48:11',1),(461,'Exported SEP inventory to PDF','2025-10-06 01:48:38',1),(462,'Exported SEP inventory to PDF','2025-10-06 01:54:17',1),(463,'Exported SEP inventory to PDF','2025-10-06 01:56:55',1),(464,'Exported SEP inventory to PDF','2025-10-06 01:57:39',1),(465,'Exported SEP inventory to PDF','2025-10-06 01:59:49',1),(466,'Exported SEP inventory to PDF','2025-10-06 02:03:06',1),(467,'Exported SEP inventory to PDF','2025-10-06 02:10:08',1),(468,'Exported PPE inventory to Excel','2025-10-06 02:14:05',1),(469,'Exported SEP inventory to PDF','2025-10-06 02:18:32',1),(470,'Exported PPE inventory to Excel','2025-10-06 02:23:56',1),(471,'Exported PPE inventory to PDF','2025-10-06 02:26:17',1),(472,'Exported unserviceable PPE inventory to PDF','2025-10-06 02:27:16',1),(473,'User logged in','2025-10-06 12:21:37',1),(474,'Searched item units by name (len=0)','2025-10-06 12:21:53',1),(475,'Searched items by barcode (len=19)','2025-10-06 12:22:11',1),(476,'Assigned unit #112 to person #2','2025-10-06 12:22:44',1),(477,'Assigned unit #113 to person #2','2025-10-06 12:22:45',1),(478,'Searched item units by name (len=0)','2025-10-06 12:22:45',1),(479,'Assigned unit #111 to person #7','2025-10-06 12:23:19',1),(480,'Searched item units by name (len=0)','2025-10-06 12:23:19',1),(481,'Exported SEP inventory to PDF','2025-10-06 12:24:16',1),(482,'Exported SEP inventory to PDF','2025-10-06 12:25:06',1),(483,'Exported SEP inventory to Excel','2025-10-06 12:25:24',1),(484,'Searched item units by name (len=0)','2025-10-06 12:25:54',1),(485,'Searched item units by name (len=0)','2025-10-06 12:25:59',1),(486,'Updated profile information','2025-10-06 12:49:40',1),(487,'Exported SEP inventory to PDF','2025-10-06 12:50:11',1),(488,'Exported SEP inventory to PDF','2025-10-06 12:50:23',1),(489,'Searched item units by name (len=0)','2025-10-06 19:28:46',1),(490,'Exported unserviceable SEP inventory to PDF','2025-10-06 19:46:11',1),(491,'Exported unserviceable SEP inventory to PDF','2025-10-06 19:50:23',1),(492,'Exported unserviceable SEP inventory to PDF','2025-10-06 19:51:14',1),(493,'Exported unserviceable SEP inventory to PDF','2025-10-06 19:53:16',1),(494,'Exported unserviceable SEP inventory to PDF','2025-10-06 19:56:51',1),(495,'Exported unserviceable PPE inventory to PDF','2025-10-06 19:57:04',1),(496,'Exported unserviceable SEP inventory to PDF','2025-10-06 19:57:31',1),(497,'Exported unserviceable SEP inventory to PDF','2025-10-06 19:58:59',1),(498,'Exported unserviceable SEP inventory to PDF','2025-10-06 20:00:02',1),(499,'Exported unserviceable SEP inventory to PDF','2025-10-06 20:00:29',1),(500,'Exported unserviceable SEP inventory to PDF','2025-10-06 20:01:40',1),(501,'Exported unserviceable SEP inventory to PDF','2025-10-06 20:01:52',1),(502,'Exported unserviceable SEP inventory to PDF','2025-10-06 20:03:02',1),(503,'Exported unserviceable SEP inventory to PDF','2025-10-06 20:03:33',1),(504,'Exported unserviceable SEP inventory to PDF','2025-10-06 20:03:49',1),(505,'Exported unserviceable SEP inventory to PDF','2025-10-06 20:04:17',1),(506,'Exported unserviceable SEP inventory to PDF','2025-10-06 20:04:42',1),(507,'Exported unserviceable SEP inventory to PDF','2025-10-06 20:05:30',1),(508,'Exported unserviceable SEP inventory to PDF','2025-10-06 20:08:03',1),(509,'Exported unserviceable SEP inventory to PDF','2025-10-06 20:08:26',1),(510,'Exported unserviceable SEP inventory to PDF','2025-10-06 20:09:15',1),(511,'Exported unserviceable SEP inventory to PDF','2025-10-06 20:09:59',1),(512,'Exported unserviceable SEP inventory to PDF','2025-10-06 20:10:34',1),(513,'Exported unserviceable SEP inventory to PDF','2025-10-06 20:12:34',1),(514,'Exported unserviceable SEP inventory to PDF','2025-10-06 20:14:52',1),(515,'Exported unserviceable SEP inventory to PDF','2025-10-06 20:15:58',1),(516,'Exported unserviceable SEP inventory to PDF','2025-10-06 20:16:11',1),(517,'Exported unserviceable SEP inventory to PDF','2025-10-06 20:16:24',1),(518,'Exported unserviceable SEP inventory to PDF','2025-10-06 20:16:38',1),(519,'Exported unserviceable SEP inventory to PDF','2025-10-06 20:17:23',1),(520,'Exported unserviceable SEP inventory to PDF','2025-10-06 20:18:17',1),(521,'Exported unserviceable SEP inventory to PDF','2025-10-06 20:18:39',1),(522,'Exported unserviceable SEP inventory to PDF','2025-10-06 20:20:49',1),(523,'Exported unserviceable SEP inventory to PDF','2025-10-06 20:21:13',1),(524,'Exported unserviceable SEP inventory to PDF','2025-10-06 20:27:15',1),(525,'Exported unserviceable SEP inventory to PDF','2025-10-06 20:27:58',1),(526,'Exported unserviceable SEP inventory to PDF','2025-10-06 20:28:15',1),(527,'Exported unserviceable SEP inventory to PDF','2025-10-06 20:29:17',1),(528,'Exported unserviceable SEP inventory to PDF','2025-10-06 20:29:40',1),(529,'Exported unserviceable SEP inventory to PDF','2025-10-06 20:31:00',1),(530,'Exported unserviceable SEP inventory to PDF','2025-10-06 20:31:21',1),(531,'Exported unserviceable SEP inventory to PDF','2025-10-06 20:31:45',1),(532,'Exported unserviceable SEP inventory to PDF','2025-10-06 20:32:23',1),(533,'Exported unserviceable SEP inventory to PDF','2025-10-06 20:33:16',1),(534,'Exported unserviceable SEP inventory to PDF','2025-10-06 20:33:45',1),(535,'Exported unserviceable SEP inventory to PDF','2025-10-06 20:34:26',1),(536,'Exported unserviceable SEP inventory to PDF','2025-10-06 20:35:31',1),(537,'Exported unserviceable SEP inventory to PDF','2025-10-06 20:36:13',1),(538,'Exported unserviceable SEP inventory to PDF','2025-10-06 20:36:37',1),(539,'Exported unserviceable SEP inventory to PDF','2025-10-06 20:36:56',1),(540,'Exported unserviceable SEP inventory to PDF','2025-10-06 21:06:57',1),(541,'Exported unserviceable SEP inventory to PDF','2025-10-06 21:16:43',1),(542,'Exported unserviceable SEP inventory to PDF','2025-10-06 21:17:00',1),(543,'Exported unserviceable SEP inventory to PDF','2025-10-06 21:25:15',1),(544,'Exported unserviceable SEP inventory to PDF','2025-10-06 21:26:21',1),(545,'Exported unserviceable SEP inventory to PDF','2025-10-06 21:29:02',1),(546,'Exported unserviceable SEP inventory to PDF','2025-10-06 21:30:30',1),(547,'Exported unserviceable SEP inventory to PDF','2025-10-06 21:30:48',1),(548,'Exported unserviceable SEP inventory to PDF','2025-10-06 21:33:57',1),(549,'Searched item units by name (len=0)','2025-10-06 21:42:34',1),(550,'Searched item units by name (len=2)','2025-10-06 21:42:37',1),(551,'Exported unserviceable PPE inventory to PDF','2025-10-06 22:27:40',1),(552,'Exported unserviceable SEP inventory to PDF','2025-10-06 22:28:18',1),(553,'Exported unserviceable SEP inventory to PDF','2025-10-06 22:41:51',1),(554,'Exported unserviceable PPE inventory to PDF','2025-10-06 22:45:50',1),(555,'Exported unserviceable SEP inventory to PDF','2025-10-06 22:46:16',1),(556,'Exported unserviceable SEP inventory to PDF','2025-10-06 22:46:34',1),(557,'Exported SEP inventory to PDF','2025-10-06 22:47:40',1),(558,'Exported SEP inventory to PDF','2025-10-06 22:48:51',1),(559,'Exported SEP inventory to PDF','2025-10-06 22:50:16',1),(560,'Searched item units by name (len=0)','2025-10-06 23:01:01',1),(561,'Searched item units by name (len=0)','2025-10-06 23:02:37',1),(562,'Searched item units by name (len=0)','2025-10-06 23:05:53',1),(563,'Exported unserviceable SEP inventory to PDF','2025-10-06 23:23:50',1),(564,'Searched item units by name (len=0)','2025-10-06 23:33:24',1),(565,'Exported unserviceable SEP inventory to PDF','2025-10-06 23:38:05',1),(566,'Exported unserviceable SEP inventory to PDF','2025-10-06 23:57:22',1),(567,'Exported unserviceable SEP inventory to PDF','2025-10-07 00:00:48',1),(568,'Exported unserviceable SEP inventory to PDF','2025-10-07 00:01:37',1),(569,'Exported unserviceable SEP inventory to PDF','2025-10-07 00:13:36',1),(570,'Updated person #1 (Benjamin Ambrose)','2025-10-07 00:17:37',1),(571,'Exported unserviceable SEP inventory to PDF','2025-10-07 00:18:06',1),(572,'Exported unserviceable SEP inventory to PDF','2025-10-07 00:19:12',1),(573,'Exported unserviceable SEP inventory to PDF','2025-10-07 00:20:40',1),(574,'Updated profile information','2025-10-07 00:21:06',1),(575,'Exported unserviceable SEP inventory to PDF','2025-10-07 00:21:24',1),(576,'Exported unserviceable SEP inventory to PDF','2025-10-07 00:22:16',1),(577,'Updated profile information','2025-10-07 00:22:32',1),(578,'Searched item units by name (len=0)','2025-10-07 00:22:37',1),(579,'Exported unserviceable SEP inventory to PDF','2025-10-07 00:22:40',1),(580,'Exported unserviceable SEP inventory to PDF','2025-10-07 00:23:03',1),(581,'User logged in','2025-10-07 00:26:14',1),(582,'Exported unserviceable PPE inventory to PDF','2025-10-07 00:26:31',1),(583,'Exported unserviceable PPE inventory to PDF','2025-10-07 00:29:20',1),(584,'Exported unserviceable SEP inventory to PDF','2025-10-07 00:29:29',1),(585,'Exported unserviceable SEP inventory to Excel','2025-10-07 00:34:12',1),(586,'Exported unserviceable SEP inventory to PDF','2025-10-07 00:37:02',1),(587,'Exported unserviceable SEP inventory to PDF','2025-10-07 00:39:08',1),(588,'Exported unserviceable PPE inventory to PDF','2025-10-07 00:42:28',1),(589,'Exported unserviceable PPE inventory to PDF','2025-10-07 00:50:27',1),(590,'Exported unserviceable PPE inventory to PDF','2025-10-07 00:53:18',1),(591,'Exported unserviceable SEP inventory to Excel','2025-10-07 00:53:27',1),(592,'Exported unserviceable PPE inventory to Excel','2025-10-07 01:01:02',1),(593,'Exported unserviceable PPE inventory to Excel','2025-10-07 01:01:56',1),(594,'Exported unserviceable PPE inventory to Excel','2025-10-07 01:04:13',1),(595,'Exported unserviceable PPE inventory to Excel','2025-10-07 01:05:10',1),(596,'Exported Unserviceable PPE Inventory to Excel','2025-10-07 01:21:00',1),(597,'Exported Unserviceable PPE Inventory to Excel','2025-10-07 01:28:50',1),(598,'Exported Unserviceable PPE Inventory to Excel','2025-10-07 01:31:54',1),(599,'Exported unserviceable PPE inventory to Excel','2025-10-07 01:31:58',1),(600,'Exported unserviceable PPE inventory to PDF','2025-10-07 01:34:49',1),(601,'Exported Unserviceable PPE Inventory to Excel','2025-10-07 01:38:25',1),(602,'Exported unserviceable PPE inventory to Excel','2025-10-07 01:40:28',1),(603,'Exported unserviceable PPE inventory to Excel','2025-10-07 01:43:42',1),(604,'Exported unserviceable PPE inventory to Excel','2025-10-07 01:54:10',1),(605,'Exported unserviceable PPE inventory to Excel','2025-10-07 01:58:37',1),(606,'Exported unserviceable PPE inventory to PDF','2025-10-07 02:02:42',1),(607,'Exported unserviceable PPE inventory to PDF','2025-10-07 02:04:40',1),(608,'Exported unserviceable PPE inventory to PDF','2025-10-07 02:05:24',1),(609,'Exported unserviceable PPE inventory to Excel','2025-10-07 02:14:28',1),(610,'Exported unserviceable PPE inventory to Excel','2025-10-07 02:19:42',1),(611,'Exported unserviceable PPE inventory to Excel','2025-10-07 02:22:38',1),(612,'Exported unserviceable PPE inventory to Excel','2025-10-07 02:28:35',1),(613,'Exported unserviceable PPE inventory to Excel','2025-10-07 02:37:37',1),(614,'Exported unserviceable PPE inventory to Excel','2025-10-07 02:46:03',1),(615,'Exported unserviceable PPE inventory to Excel','2025-10-07 02:58:29',1),(616,'Exported unserviceable PPE inventory to Excel','2025-10-07 03:04:44',1),(617,'Exported unserviceable PPE inventory to Excel','2025-10-07 03:08:32',1),(618,'Exported unserviceable PPE inventory to Excel','2025-10-07 03:09:35',1),(619,'Exported unserviceable PPE inventory to Excel','2025-10-07 03:18:56',1),(620,'User logged in','2025-10-07 10:12:26',1),(621,'User logged in','2025-10-07 10:14:33',1),(622,'Searched item units by name (len=0)','2025-10-07 10:36:11',1),(623,'Searched item units by name (len=0)','2025-10-07 10:37:17',1),(624,'Updated profile information','2025-10-07 10:37:38',1),(625,'Added person #12 - Benjamin Ambrose','2025-10-07 10:41:57',1),(626,'Searched item units by name (len=0)','2025-10-07 10:45:19',1),(627,'Searched item units by name (len=0)','2025-10-07 10:48:47',1),(628,'Exported PPE inventory to PDF','2025-10-07 10:49:02',1),(629,'Exported PPE inventory to Excel','2025-10-07 10:49:19',1),(630,'Exported PPE inventory to PDF','2025-10-07 10:50:03',1),(631,'Searched item units by name (len=0)','2025-10-07 10:51:09',1),(632,'Searched item units by name (len=0)','2025-10-07 10:51:51',1),(633,'Searched item units by name (len=0)','2025-10-07 10:55:09',1),(634,'Searched item units by name (len=0)','2025-10-07 10:55:18',1),(635,'Searched items by barcode (len=19)','2025-10-07 10:56:02',1),(636,'Searched items by barcode (len=18)','2025-10-07 10:56:31',1),(637,'Searched item units by name (len=0)','2025-10-07 10:57:25',1),(638,'Downloaded barcode image for unit_ID 120','2025-10-07 10:57:35',1),(639,'Searched item units by name (len=2)','2025-10-07 10:57:59',1),(640,'Searched item units by name (len=3)','2025-10-07 10:58:01',1),(641,'Searched item units by name (len=2)','2025-10-07 10:58:06',1),(642,'Searched item units by name (len=0)','2025-10-07 10:58:06',1),(643,'Searched item units by name (len=2)','2025-10-07 10:58:07',1),(644,'Searched item units by name (len=3)','2025-10-07 10:58:09',1),(645,'Searched item units by name (len=5)','2025-10-07 10:58:09',1),(646,'Searched item units by name (len=4)','2025-10-07 10:58:13',1),(647,'Searched item units by name (len=0)','2025-10-07 10:58:14',1),(648,'Searched items by barcode (len=19)','2025-10-07 10:58:51',1);
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
  `fund_abbreviation` varchar(255) DEFAULT NULL,
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
INSERT INTO `fund_sources` VALUES (1,'Maintenance and Other Operating Expenses','MOOE','FUND 101 - REGULAR'),(2,'Special Trust Fund','STF','FUND 164 - STF'),(3,'Income Generating Project','IGP','FUND 164 - IGP');
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
) ENGINE=InnoDB AUTO_INCREMENT=121 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `item_units`
--

LOCK TABLES `item_units` WRITE;
/*!40000 ALTER TABLE `item_units` DISABLE KEYS */;
INSERT INTO `item_units` VALUES (3,8,'ITEM-LM-FFS-2509-0009','./uploads/barcodes/barcode_ITEM-LM-FFS-2509-0009.png',NULL,1,NULL,'Assigned','Good Condition','2025-09-27 20:59:34','2025-09-27 21:00:42'),(4,8,'ITEM-LM-FFS-2509-0010','./uploads/barcodes/barcode_ITEM-LM-FFS-2509-0010.png','./uploads/unit_images/68d7fa36c754f_unit.png',1,'','Assigned','Defective','2025-09-27 22:54:21','2025-09-27 22:54:21'),(5,9,'ITEM--CES-2509-0010','./uploads/barcodes/barcode_ITEM--CES-2509-0010.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-27 21:24:16','2025-09-27 21:24:16'),(6,9,'ITEM--CES-2509-0011','./uploads/barcodes/barcode_ITEM--CES-2509-0011.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-27 21:24:16','2025-09-27 21:24:16'),(7,9,'ITEM--CES-2509-0012','./uploads/barcodes/barcode_ITEM--CES-2509-0012.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-27 21:24:16','2025-09-27 21:24:16'),(8,10,'ITEM--ICT-2509-0011','./uploads/barcodes/barcode_ITEM--ICT-2509-0011.png','./uploads/unit_images/68d7fa6a8e923_unit.png',2,'social hall','Assigned','Defective','2025-09-27 22:53:30','2025-09-27 22:53:30'),(9,10,'ITEM--ICT-2509-0012','./uploads/barcodes/barcode_ITEM--ICT-2509-0012.png',NULL,6,'fdfgdfg','Assigned','Good Condition','2025-10-05 22:07:03','2025-10-05 22:07:03'),(10,11,'ITEM--FFS-2509-0012','./uploads/barcodes/barcode_ITEM--FFS-2509-0012.png',NULL,2,'','Assigned','Unserviceable','2025-10-01 00:19:59','2025-10-01 00:19:59'),(11,11,'ITEM--FFS-2509-0013','./uploads/barcodes/barcode_ITEM--FFS-2509-0013.png','./uploads/unit_images/68d7ef7b15d0d_unit.png',1,'social hall','Assigned','Good Condition','2025-09-27 22:06:51','2025-09-27 22:06:51'),(12,11,'ITEM--FFS-2509-0014','./uploads/barcodes/barcode_ITEM--FFS-2509-0014.png','./uploads/unit_images/68d7fa0bade68_unit.png',1,'social hall','Assigned','Defective','2025-09-27 22:51:55','2025-09-27 22:51:55'),(13,11,'ITEM--FFS-2509-0015','./uploads/barcodes/barcode_ITEM--FFS-2509-0015.png',NULL,2,'','Assigned','Good Condition','2025-09-30 22:56:56','2025-09-30 22:56:56'),(14,11,'ITEM--FFS-2509-0016','./uploads/barcodes/barcode_ITEM--FFS-2509-0016.png','./uploads/unit_images/68dbef801ef29_unit.png',1,'grand stand','Assigned','Good Condition','2025-09-30 22:56:00','2025-09-30 22:56:00'),(15,12,'ITEM--BSE-2509-0013','./uploads/barcodes/barcode_ITEM--BSE-2509-0013.png','./uploads/unit_images/68da2b52876fb_unit.png',1,'ccb avr','Assigned','Good Condition','2025-09-29 14:46:42','2025-09-29 14:46:42'),(16,12,'ITEM--BSE-2509-0014','./uploads/barcodes/barcode_ITEM--BSE-2509-0014.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:37','2025-09-29 14:43:37'),(17,12,'ITEM--BSE-2509-0015','./uploads/barcodes/barcode_ITEM--BSE-2509-0015.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:37','2025-09-29 14:43:37'),(18,12,'ITEM--BSE-2509-0016','./uploads/barcodes/barcode_ITEM--BSE-2509-0016.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:37','2025-09-29 14:43:37'),(19,12,'ITEM--BSE-2509-0017','./uploads/barcodes/barcode_ITEM--BSE-2509-0017.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:37','2025-09-29 14:43:37'),(20,12,'ITEM--BSE-2509-0018','./uploads/barcodes/barcode_ITEM--BSE-2509-0018.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:37','2025-09-29 14:43:37'),(21,12,'ITEM--BSE-2509-0019','./uploads/barcodes/barcode_ITEM--BSE-2509-0019.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:37','2025-09-29 14:43:37'),(22,12,'ITEM--BSE-2509-0020','./uploads/barcodes/barcode_ITEM--BSE-2509-0020.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:37','2025-09-29 14:43:37'),(23,12,'ITEM--BSE-2509-0021','./uploads/barcodes/barcode_ITEM--BSE-2509-0021.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:37','2025-09-29 14:43:37'),(24,12,'ITEM--BSE-2509-0022','./uploads/barcodes/barcode_ITEM--BSE-2509-0022.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:37','2025-09-29 14:43:37'),(25,12,'ITEM--BSE-2509-0023','./uploads/barcodes/barcode_ITEM--BSE-2509-0023.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:37','2025-09-29 14:43:37'),(26,12,'ITEM--BSE-2509-0024','./uploads/barcodes/barcode_ITEM--BSE-2509-0024.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:37','2025-09-29 14:43:37'),(27,12,'ITEM--BSE-2509-0025','./uploads/barcodes/barcode_ITEM--BSE-2509-0025.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:37','2025-09-29 14:43:37'),(28,12,'ITEM--BSE-2509-0026','./uploads/barcodes/barcode_ITEM--BSE-2509-0026.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:37','2025-09-29 14:43:37'),(29,12,'ITEM--BSE-2509-0027','./uploads/barcodes/barcode_ITEM--BSE-2509-0027.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:37','2025-09-29 14:43:37'),(30,12,'ITEM--BSE-2509-0028','./uploads/barcodes/barcode_ITEM--BSE-2509-0028.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:37','2025-09-29 14:43:37'),(31,12,'ITEM--BSE-2509-0029','./uploads/barcodes/barcode_ITEM--BSE-2509-0029.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:37','2025-09-29 14:43:37'),(32,12,'ITEM--BSE-2509-0030','./uploads/barcodes/barcode_ITEM--BSE-2509-0030.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:37','2025-09-29 14:43:37'),(33,12,'ITEM--BSE-2509-0031','./uploads/barcodes/barcode_ITEM--BSE-2509-0031.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:37','2025-09-29 14:43:37'),(34,12,'ITEM--BSE-2509-0032','./uploads/barcodes/barcode_ITEM--BSE-2509-0032.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:37','2025-09-29 14:43:37'),(35,12,'ITEM--BSE-2509-0033','./uploads/barcodes/barcode_ITEM--BSE-2509-0033.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:37','2025-09-29 14:43:37'),(36,12,'ITEM--BSE-2509-0034','./uploads/barcodes/barcode_ITEM--BSE-2509-0034.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:37','2025-09-29 14:43:37'),(37,12,'ITEM--BSE-2509-0035','./uploads/barcodes/barcode_ITEM--BSE-2509-0035.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:37','2025-09-29 14:43:37'),(38,12,'ITEM--BSE-2509-0036','./uploads/barcodes/barcode_ITEM--BSE-2509-0036.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:37','2025-09-29 14:43:37'),(39,12,'ITEM--BSE-2509-0037','./uploads/barcodes/barcode_ITEM--BSE-2509-0037.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:37','2025-09-29 14:43:37'),(40,12,'ITEM--BSE-2509-0038','./uploads/barcodes/barcode_ITEM--BSE-2509-0038.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:37','2025-09-29 14:43:37'),(41,12,'ITEM--BSE-2509-0039','./uploads/barcodes/barcode_ITEM--BSE-2509-0039.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:38','2025-09-29 14:43:38'),(42,12,'ITEM--BSE-2509-0040','./uploads/barcodes/barcode_ITEM--BSE-2509-0040.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:38','2025-09-29 14:43:38'),(43,12,'ITEM--BSE-2509-0041','./uploads/barcodes/barcode_ITEM--BSE-2509-0041.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:38','2025-09-29 14:43:38'),(44,12,'ITEM--BSE-2509-0042','./uploads/barcodes/barcode_ITEM--BSE-2509-0042.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:38','2025-09-29 14:43:38'),(45,12,'ITEM--BSE-2509-0043','./uploads/barcodes/barcode_ITEM--BSE-2509-0043.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:38','2025-09-29 14:43:38'),(46,12,'ITEM--BSE-2509-0044','./uploads/barcodes/barcode_ITEM--BSE-2509-0044.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:38','2025-09-29 14:43:38'),(47,12,'ITEM--BSE-2509-0045','./uploads/barcodes/barcode_ITEM--BSE-2509-0045.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:38','2025-09-29 14:43:38'),(48,12,'ITEM--BSE-2509-0046','./uploads/barcodes/barcode_ITEM--BSE-2509-0046.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:38','2025-09-29 14:43:38'),(49,12,'ITEM--BSE-2509-0047','./uploads/barcodes/barcode_ITEM--BSE-2509-0047.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:38','2025-09-29 14:43:38'),(50,12,'ITEM--BSE-2509-0048','./uploads/barcodes/barcode_ITEM--BSE-2509-0048.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:38','2025-09-29 14:43:38'),(51,12,'ITEM--BSE-2509-0049','./uploads/barcodes/barcode_ITEM--BSE-2509-0049.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:38','2025-09-29 14:43:38'),(52,12,'ITEM--BSE-2509-0050','./uploads/barcodes/barcode_ITEM--BSE-2509-0050.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:38','2025-09-29 14:43:38'),(53,12,'ITEM--BSE-2509-0051','./uploads/barcodes/barcode_ITEM--BSE-2509-0051.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:38','2025-09-29 14:43:38'),(54,12,'ITEM--BSE-2509-0052','./uploads/barcodes/barcode_ITEM--BSE-2509-0052.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:38','2025-09-29 14:43:38'),(55,12,'ITEM--BSE-2509-0053','./uploads/barcodes/barcode_ITEM--BSE-2509-0053.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:38','2025-09-29 14:43:38'),(56,12,'ITEM--BSE-2509-0054','./uploads/barcodes/barcode_ITEM--BSE-2509-0054.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:38','2025-09-29 14:43:38'),(57,12,'ITEM--BSE-2509-0055','./uploads/barcodes/barcode_ITEM--BSE-2509-0055.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:38','2025-09-29 14:43:38'),(58,12,'ITEM--BSE-2509-0056','./uploads/barcodes/barcode_ITEM--BSE-2509-0056.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:38','2025-09-29 14:43:38'),(59,12,'ITEM--BSE-2509-0057','./uploads/barcodes/barcode_ITEM--BSE-2509-0057.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:38','2025-09-29 14:43:38'),(60,12,'ITEM--BSE-2509-0058','./uploads/barcodes/barcode_ITEM--BSE-2509-0058.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:38','2025-09-29 14:43:38'),(61,12,'ITEM--BSE-2509-0059','./uploads/barcodes/barcode_ITEM--BSE-2509-0059.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:38','2025-09-29 14:43:38'),(62,12,'ITEM--BSE-2509-0060','./uploads/barcodes/barcode_ITEM--BSE-2509-0060.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:38','2025-09-29 14:43:38'),(63,12,'ITEM--BSE-2509-0061','./uploads/barcodes/barcode_ITEM--BSE-2509-0061.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:38','2025-09-29 14:43:38'),(64,12,'ITEM--BSE-2509-0062','./uploads/barcodes/barcode_ITEM--BSE-2509-0062.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:38','2025-09-29 14:43:38'),(65,12,'ITEM--BSE-2509-0063','./uploads/barcodes/barcode_ITEM--BSE-2509-0063.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:38','2025-09-29 14:43:38'),(66,12,'ITEM--BSE-2509-0064','./uploads/barcodes/barcode_ITEM--BSE-2509-0064.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:38','2025-09-29 14:43:38'),(67,12,'ITEM--BSE-2509-0065','./uploads/barcodes/barcode_ITEM--BSE-2509-0065.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:38','2025-09-29 14:43:38'),(68,12,'ITEM--BSE-2509-0066','./uploads/barcodes/barcode_ITEM--BSE-2509-0066.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:38','2025-09-29 14:43:38'),(69,12,'ITEM--BSE-2509-0067','./uploads/barcodes/barcode_ITEM--BSE-2509-0067.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:38','2025-09-29 14:43:38'),(70,12,'ITEM--BSE-2509-0068','./uploads/barcodes/barcode_ITEM--BSE-2509-0068.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:38','2025-09-29 14:43:38'),(71,12,'ITEM--BSE-2509-0069','./uploads/barcodes/barcode_ITEM--BSE-2509-0069.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:38','2025-09-29 14:43:38'),(72,12,'ITEM--BSE-2509-0070','./uploads/barcodes/barcode_ITEM--BSE-2509-0070.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:38','2025-09-29 14:43:38'),(73,12,'ITEM--BSE-2509-0071','./uploads/barcodes/barcode_ITEM--BSE-2509-0071.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:38','2025-09-29 14:43:38'),(74,12,'ITEM--BSE-2509-0072','./uploads/barcodes/barcode_ITEM--BSE-2509-0072.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:38','2025-09-29 14:43:38'),(75,12,'ITEM--BSE-2509-0073','./uploads/barcodes/barcode_ITEM--BSE-2509-0073.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:38','2025-09-29 14:43:38'),(76,12,'ITEM--BSE-2509-0074','./uploads/barcodes/barcode_ITEM--BSE-2509-0074.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:38','2025-09-29 14:43:38'),(77,12,'ITEM--BSE-2509-0075','./uploads/barcodes/barcode_ITEM--BSE-2509-0075.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:38','2025-09-29 14:43:38'),(78,12,'ITEM--BSE-2509-0076','./uploads/barcodes/barcode_ITEM--BSE-2509-0076.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:38','2025-09-29 14:43:38'),(79,12,'ITEM--BSE-2509-0077','./uploads/barcodes/barcode_ITEM--BSE-2509-0077.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:38','2025-09-29 14:43:38'),(80,12,'ITEM--BSE-2509-0078','./uploads/barcodes/barcode_ITEM--BSE-2509-0078.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:38','2025-09-29 14:43:38'),(81,12,'ITEM--BSE-2509-0079','./uploads/barcodes/barcode_ITEM--BSE-2509-0079.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:38','2025-09-29 14:43:38'),(82,12,'ITEM--BSE-2509-0080','./uploads/barcodes/barcode_ITEM--BSE-2509-0080.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:38','2025-09-29 14:43:38'),(83,12,'ITEM--BSE-2509-0081','./uploads/barcodes/barcode_ITEM--BSE-2509-0081.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:38','2025-09-29 14:43:38'),(84,12,'ITEM--BSE-2509-0082','./uploads/barcodes/barcode_ITEM--BSE-2509-0082.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:38','2025-09-29 14:43:38'),(85,12,'ITEM--BSE-2509-0083','./uploads/barcodes/barcode_ITEM--BSE-2509-0083.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:38','2025-09-29 14:43:38'),(86,12,'ITEM--BSE-2509-0084','./uploads/barcodes/barcode_ITEM--BSE-2509-0084.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:38','2025-09-29 14:43:38'),(87,12,'ITEM--BSE-2509-0085','./uploads/barcodes/barcode_ITEM--BSE-2509-0085.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:38','2025-09-29 14:43:38'),(88,12,'ITEM--BSE-2509-0086','./uploads/barcodes/barcode_ITEM--BSE-2509-0086.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:38','2025-09-29 14:43:38'),(89,12,'ITEM--BSE-2509-0087','./uploads/barcodes/barcode_ITEM--BSE-2509-0087.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:38','2025-09-29 14:43:38'),(90,12,'ITEM--BSE-2509-0088','./uploads/barcodes/barcode_ITEM--BSE-2509-0088.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:38','2025-09-29 14:43:38'),(91,12,'ITEM--BSE-2509-0089','./uploads/barcodes/barcode_ITEM--BSE-2509-0089.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:38','2025-09-29 14:43:38'),(92,12,'ITEM--BSE-2509-0090','./uploads/barcodes/barcode_ITEM--BSE-2509-0090.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:38','2025-09-29 14:43:38'),(93,12,'ITEM--BSE-2509-0091','./uploads/barcodes/barcode_ITEM--BSE-2509-0091.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:38','2025-09-29 14:43:38'),(94,12,'ITEM--BSE-2509-0092','./uploads/barcodes/barcode_ITEM--BSE-2509-0092.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:38','2025-09-29 14:43:38'),(95,12,'ITEM--BSE-2509-0093','./uploads/barcodes/barcode_ITEM--BSE-2509-0093.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:38','2025-09-29 14:43:38'),(96,12,'ITEM--BSE-2509-0094','./uploads/barcodes/barcode_ITEM--BSE-2509-0094.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:38','2025-09-29 14:43:38'),(97,12,'ITEM--BSE-2509-0095','./uploads/barcodes/barcode_ITEM--BSE-2509-0095.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:38','2025-09-29 14:43:38'),(98,12,'ITEM--BSE-2509-0096','./uploads/barcodes/barcode_ITEM--BSE-2509-0096.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:38','2025-09-29 14:43:38'),(99,12,'ITEM--BSE-2509-0097','./uploads/barcodes/barcode_ITEM--BSE-2509-0097.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:38','2025-09-29 14:43:38'),(100,12,'ITEM--BSE-2509-0098','./uploads/barcodes/barcode_ITEM--BSE-2509-0098.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:38','2025-09-29 14:43:38'),(101,12,'ITEM--BSE-2509-0099','./uploads/barcodes/barcode_ITEM--BSE-2509-0099.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:38','2025-09-29 14:43:38'),(102,12,'ITEM--BSE-2509-0100','./uploads/barcodes/barcode_ITEM--BSE-2509-0100.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:38','2025-09-29 14:43:38'),(103,12,'ITEM--BSE-2509-0101','./uploads/barcodes/barcode_ITEM--BSE-2509-0101.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:38','2025-09-29 14:43:38'),(104,12,'ITEM--BSE-2509-0102','./uploads/barcodes/barcode_ITEM--BSE-2509-0102.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:38','2025-09-29 14:43:38'),(105,12,'ITEM--BSE-2509-0103','./uploads/barcodes/barcode_ITEM--BSE-2509-0103.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:38','2025-09-29 14:43:38'),(106,12,'ITEM--BSE-2509-0104','./uploads/barcodes/barcode_ITEM--BSE-2509-0104.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:38','2025-09-29 14:43:38'),(107,12,'ITEM--BSE-2509-0105','./uploads/barcodes/barcode_ITEM--BSE-2509-0105.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:38','2025-09-29 14:43:38'),(108,12,'ITEM--BSE-2509-0106','./uploads/barcodes/barcode_ITEM--BSE-2509-0106.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:38','2025-09-29 14:43:38'),(109,12,'ITEM--BSE-2509-0107','./uploads/barcodes/barcode_ITEM--BSE-2509-0107.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:38','2025-09-29 14:43:38'),(110,12,'ITEM--BSE-2509-0108','./uploads/barcodes/barcode_ITEM--BSE-2509-0108.png',NULL,NULL,NULL,'Available','Good Condition','2025-09-29 14:43:38','2025-09-29 14:43:38'),(111,12,'ITEM--BSE-2509-0109','./uploads/barcodes/barcode_ITEM--BSE-2509-0109.png',NULL,7,'Admin Aide Office','Assigned','Unserviceable','2025-10-06 12:23:19','2025-10-06 12:23:19'),(112,12,'ITEM--BSE-2509-0110','./uploads/barcodes/barcode_ITEM--BSE-2509-0110.png',NULL,2,'','Assigned','Good Condition','2025-10-06 12:22:44','2025-10-06 12:22:44'),(113,12,'ITEM--BSE-2509-0111','./uploads/barcodes/barcode_ITEM--BSE-2509-0111.png',NULL,2,'','Assigned','Good Condition','2025-10-06 12:22:45','2025-10-06 12:22:45'),(114,12,'ITEM--BSE-2509-0112','./uploads/barcodes/barcode_ITEM--BSE-2509-0112.png',NULL,6,'fdfgdfg','Assigned','Good Condition','2025-10-05 22:07:03','2025-10-05 22:07:03'),(115,14,'ITEM-A-OES-2510-0015','./uploads/barcodes/barcode_ITEM-A-OES-2510-0015.png',NULL,8,'','Assigned','Good Condition','2025-10-05 22:05:37','2025-10-05 22:05:37'),(116,14,'ITEM-A-OES-2510-0016','./uploads/barcodes/barcode_ITEM-A-OES-2510-0016.png','./uploads/unit_images/68dd38aec3c4a_unit.png',1,'ccb avr','Assigned','Good Condition','2025-10-01 22:20:30','2025-10-01 22:20:30'),(117,15,'ITEM-LM-FFS-2510-0016','./uploads/barcodes/barcode_ITEM-LM-FFS-2510-0016.png',NULL,8,'','Assigned','Good Condition','2025-10-05 22:05:37','2025-10-05 22:05:37'),(118,15,'ITEM-LM-FFS-2510-0017','./uploads/barcodes/barcode_ITEM-LM-FFS-2510-0017.png',NULL,9,'Admin Aide Office','Assigned','Good Condition','2025-10-02 22:54:09','2025-10-02 22:54:09'),(119,16,'ITEM--OTH-2510-0017','./uploads/barcodes/barcode_ITEM--OTH-2510-0017.png',NULL,10,'','Assigned','Good Condition','2025-10-06 01:21:41','2025-10-06 01:21:41'),(120,16,'ITEM--OTH-2510-0018','./uploads/barcodes/barcode_ITEM--OTH-2510-0018.png',NULL,8,'social hall','Assigned','Unserviceable','2025-10-06 01:21:29','2025-10-06 01:21:29');
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
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `items`
--

LOCK TABLES `items` WRITE;
/*!40000 ALTER TABLE `items` DISABLE KEYS */;
INSERT INTO `items` VALUES (8,'Laminating Machine','heavy duty','unit',1500.00,2.00,3000.00,'2025-002','Semi-Expendable Property',3,10,'2025-09-01','3 yrs','/Inventory_Management_System/public/uploads/item_images/68d7dfb538792_barcode.jpg','2025-09-27 20:59:33'),(9,'sewing machine','asascascca','unit',4500.00,3.00,13500.00,'221-2024-001-002','Semi-Expendable Property',1,11,'2025-09-23','3 yrs','/Inventory_Management_System/public/uploads/item_images/68d7e57fda573_IMG_20240307_110509_577.jpg','2025-09-27 21:24:15'),(10,'land mine','csaadvda','unit',1500.00,2.00,3000.00,'2024-002','Semi-Expendable Property',1,12,'2025-09-18','3 yrs','/Inventory_Management_System/public/uploads/item_images/68d7e92249b0a_barcode.jpg','2025-09-27 21:39:46'),(11,'industrial fan','dzvsdvdsvs','unit',1500.00,5.00,7500.00,'221-2025-005','Semi-Expendable Property',1,10,'2025-09-24','3 yrs','/Inventory_Management_System/public/uploads/item_images/68d7ea599919f_IMG_20240307_110752_016.jpg','2025-09-27 21:44:57'),(12,'water dispenser','library books','pc',500.00,100.00,50000.00,'221-2024-001-002','Semi-Expendable Property',1,15,'2024-09-16','10 yrs.','/Inventory_Management_System/public/uploads/item_images/68da2a98911ff_Screenshot 2025-09-09 232941.png','2025-09-29 14:43:36'),(13,'builduing','building for ict','unit',1442444.00,1.00,1442444.00,'05-215-01','Property Plant and Equipment',2,19,'2025-09-10','','/Inventory_Management_System/public/uploads/item_images/68dbf1610aeb6_551876066_731073779973130_7052705692278240968_n.png','2025-09-30 23:04:01'),(14,'Aircondition','2HP, split type, inverter','unit',49850.00,2.00,99700.00,'221-2024-014','Semi-Expendable Property',2,9,'2025-10-01','3 yrs','/Inventory_Management_System/public/uploads/item_images/68dc8449858c9_Screenshot 2025-09-19 105824.png','2025-10-01 09:30:49'),(15,'Laminating Machine','sdsfewefw','unit',1500.00,2.00,3000.00,'221-2025-013','Semi-Expendable Property',3,10,'2024-10-01','3 yrs',NULL,'2025-10-02 22:52:12'),(16,'effwf','efwefw','unit',13700.00,2.00,27400.00,'221-2025-001-002','Property Plant and Equipment',2,25,'2025-10-05','3 yrs',NULL,'2025-10-06 01:20:46');
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
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `persons`
--

LOCK TABLES `persons` WRITE;
/*!40000 ALTER TABLE `persons` DISABLE KEYS */;
INSERT INTO `persons` VALUES (1,'Benjamin','Ambrose','MSIT','BME Dean\'s Office','Instructor I','Active','68d7f2e17f15e.jpg',1),(2,'Ramil','Hufancia','','Supply and Property Office','Supply Officer','Active','68d7f2c636942.png',1),(3,'Denniz','Malonzo',NULL,'Admin Aide & Admin Asst','Admin Aide II','Active',NULL,1),(4,'Giovanie Jennel','Cielo',NULL,'Admin Aide & Admin Asst','Admin Aide I','Active',NULL,1),(5,'Jefrel','Botalon',NULL,'Admin Aide & Admin Asst','Admin Aide I','Active',NULL,1),(6,'Noel','Goyal',NULL,'Admin Aide & Admin Asst','Admin Aide I','Active',NULL,1),(7,'Oliver','Gimoro',NULL,'Admin Aide & Admin Asst','N/A','Active',NULL,1),(8,'Oscar','Carinola',NULL,'Admin Aide & Admin Asst','Admin Aide III','Active',NULL,1),(9,'Salvador','Gohar',NULL,'Admin Aide & Admin Asst','Admin Aide IV','Active',NULL,1),(10,'Abegail','Fulgar','','BME Dean\'s Office','Instructor I  - Program Chair, BSE','Active',NULL,1),(11,'Michael','Bongalonta','','BME Dean\'s Office','Associate Professor III','Active',NULL,1),(12,'Benjamin','Ambrose','MSIT','BME Dean\'s Office','Instructor I','Active',NULL,1);
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
) ENGINE=InnoDB AUTO_INCREMENT=62 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `task_manager`
--

LOCK TABLES `task_manager` WRITE;
/*!40000 ALTER TABLE `task_manager` DISABLE KEYS */;
INSERT INTO `task_manager` VALUES (46,1,'Checklist','aas','asasas','Completed','2025-10-02','00:46:00','2025-10-02 00:46:55'),(47,1,'Notes','rgdfgdf','dfdfgdfg','Completed',NULL,NULL,'2025-10-02 17:44:05'),(48,1,'Reminders','fdfgg','dfdfgdfg','Cancelled','2025-10-02','00:52:00','2025-10-02 00:47:49'),(49,1,'Notes','gfghhg','bgv hghg','Completed',NULL,NULL,'2025-10-02 01:05:45'),(51,1,'Notes','ssfsa','faasfas','Completed',NULL,NULL,'2025-10-02 01:26:24'),(52,1,'Notes','asdasdds','sdsdasd','Cancelled',NULL,NULL,'2025-10-02 17:27:47'),(53,1,'Notes','asasasd','sddasdsa','Pending',NULL,NULL,'2025-10-05 22:04:29'),(54,1,'Notes','qsqwd','wddqwdqw','Cancelled',NULL,NULL,'2025-10-07 10:20:19'),(55,1,'Reminders','sample','gyjk','Completed',NULL,NULL,'2025-10-07 10:19:58'),(57,1,'Reminders','lnk','k;','Cancelled','2025-10-07','10:21:00','2025-10-07 10:29:35'),(60,1,'Reminders','test','t','Pending','2025-10-07','10:33:00','2025-10-07 10:30:09'),(61,1,'Reminders','test','t','Pending','2025-10-07','10:33:00','2025-10-07 10:30:36');
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
INSERT INTO `users` VALUES (1,'Admin','admin@gmail.com','$2y$10$tf/p9H9UNdvrQwEuytPDL..Ci2wp.6mclHPB3VnFiYc5wDH/Lybf.','Ramil','Hufancia','MIT','Supply Officer','../public/uploads/profile_images/profile_68de9cddde19e0.08396396.png','3fbeeb0b5db1a9ec3a45a69919749df0');
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

-- Dump completed on 2025-10-07 10:59:27
