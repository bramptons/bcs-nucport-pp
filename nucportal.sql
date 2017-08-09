-- MySQL dump 10.13  Distrib 5.6.23, for Win64 (x86_64)
--
-- Host: localhost    Database: nucportal
-- ------------------------------------------------------
-- Server version	5.5.43

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `tblapicache`
--

DROP TABLE IF EXISTS `tblapicache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tblapicache` (
  `CacheID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Created` datetime NOT NULL,
  `Expires` datetime NOT NULL,
  `Script` varchar(20) NOT NULL,
  `Fname` varchar(30) NOT NULL,
  `RequestHash` varchar(64) DEFAULT NULL,
  `Param1Key` varchar(30) DEFAULT NULL,
  `Param1Value` varchar(4) DEFAULT NULL,
  `Param2Key` varchar(30) DEFAULT NULL,
  `Param2Value` varchar(4) DEFAULT NULL,
  `Param3Key` varchar(30) DEFAULT NULL,
  `Param3Value` varchar(40) DEFAULT NULL,
  `Response` longblob,
  PRIMARY KEY (`CacheID`),
  UNIQUE KEY `idxEntry` (`Script`,`Fname`,`Param1Key`,`Param1Value`,`Param2Key`,`Param2Value`,`Param3Key`,`Param3Value`,`RequestHash`) USING BTREE,
  KEY `idxScript` (`Script`) USING BTREE,
  KEY `idxFname` (`Fname`) USING BTREE,
  KEY `idxParams` (`Param1Key`,`Param1Value`,`Param2Key`,`Param2Value`,`Param3Key`,`Param3Value`) USING BTREE,
  KEY `idxCreated` (`Created`) USING BTREE,
  KEY `idxExpires` (`Expires`) USING BTREE,
  KEY `idxHash` (`RequestHash`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tblapisessions`
--

DROP TABLE IF EXISTS `tblapisessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tblapisessions` (
  `SessionID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Token` varchar(128) NOT NULL,
  `PersonID` int(10) unsigned NOT NULL,
  `Data` longblob,
  `Expiry` datetime DEFAULT NULL,
  PRIMARY KEY (`SessionID`),
  UNIQUE KEY `idxIdentifier` (`Token`,`PersonID`) USING BTREE,
  KEY `idxExpiry` (`Expiry`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=164 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tblarticles`
--

DROP TABLE IF EXISTS `tblarticles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tblarticles` (
  `ArticleID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `BuiltIn` varchar(25) DEFAULT NULL,
  `Menu` varchar(45) DEFAULT NULL,
  `MenuOrder` smallint(5) unsigned DEFAULT '0',
  `Contents` longtext,
  `Visible` tinyint(1) unsigned DEFAULT '0',
  `Title` varchar(512) NOT NULL,
  `TemplateID` int(10) unsigned DEFAULT NULL,
  `Permissions` set('authenticated','member') DEFAULT NULL,
  PRIMARY KEY (`ArticleID`),
  KEY `idxTitle` (`Title`(333)) USING BTREE,
  KEY `idxTemplate` (`TemplateID`) USING BTREE,
  KEY `idxPermissions` (`Permissions`) USING BTREE,
  KEY `idxBuiltIn` (`BuiltIn`) USING BTREE,
  KEY `idxMenu` (`Menu`) USING BTREE,
  KEY `idxMenuOrder` (`MenuOrder`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=37 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tblpaymentlog`
--

DROP TABLE IF EXISTS `tblpaymentlog`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tblpaymentlog` (
  `PaymentLogID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Sender` varchar(45) NOT NULL,
  `Reference` varchar(45) NOT NULL,
  `TransactionTime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `Data` text,
  `APIRequest` text,
  `APIResponse` text,
  PRIMARY KEY (`PaymentLogID`,`Sender`),
  KEY `idxReference` (`Reference`),
  KEY `idxSender` (`Sender`),
  KEY `idxTime` (`TransactionTime`)
) ENGINE=InnoDB AUTO_INCREMENT=41 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tblsbmessage`
--

DROP TABLE IF EXISTS `tblsbmessage`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tblsbmessage` (
  `MessageID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `SessionID` int(10) unsigned NOT NULL,
  `Updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `Type` enum('success','info','warning','danger','error') NOT NULL DEFAULT 'info',
  `Expires` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`MessageID`),
  KEY `idxSession` (`SessionID`) USING BTREE,
  KEY `idxUpdated` (`Updated`) USING BTREE,
  KEY `idxExpires` (`Expires`) USING BTREE,
  CONSTRAINT `tblsbmessage_ibfk_1` FOREIGN KEY (`SessionID`) REFERENCES `tblapisessions` (`SessionID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tblsbmessageitem`
--

DROP TABLE IF EXISTS `tblsbmessageitem`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tblsbmessageitem` (
  `MessageItemID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `MessageID` int(10) unsigned NOT NULL,
  `Caption` tinytext NOT NULL,
  `URL` varchar(1024) DEFAULT NULL,
  `Script` varchar(1024) DEFAULT NULL,
  `Target` varchar(15) DEFAULT NULL,
  `Icon` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`MessageItemID`),
  KEY `idxMessage` (`MessageID`) USING BTREE,
  CONSTRAINT `tblsbmessageitem_ibfk_1` FOREIGN KEY (`MessageID`) REFERENCES `tblsbmessage` (`MessageID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tblsessiondata`
--

DROP TABLE IF EXISTS `tblsessiondata`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tblsessiondata` (
  `SessionDataID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `SessionID` int(10) unsigned NOT NULL,
  `DataKey` varchar(45) NOT NULL,
  `DataValue` text,
  PRIMARY KEY (`SessionDataID`),
  UNIQUE KEY `idxSessionKey` (`SessionID`,`DataKey`),
  KEY `idxKey` (`DataKey`),
  KEY `idxSession` (`SessionID`),
  CONSTRAINT `fkSessionDataSession` FOREIGN KEY (`SessionID`) REFERENCES `tblapisessions` (`SessionID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1822 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tbltemplates`
--

DROP TABLE IF EXISTS `tbltemplates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbltemplates` (
  `TemplateID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Description` varchar(128) NOT NULL,
  `HTML` longtext NOT NULL,
  `Javascript` longtext,
  PRIMARY KEY (`TemplateID`),
  KEY `idxDescription` (`Description`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2017-08-09 13:07:47
