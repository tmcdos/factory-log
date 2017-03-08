# HeidiSQL Dump 
#
# --------------------------------------------------------
# Database:             joblog
# Server version:       5.5.44-MariaDB-log
# Server OS:            Linux
# Target-Compatibility: Same as source server (MySQL 5.5.44-MariaDB-log)
# max_allowed_packet:   10485760
# HeidiSQL version:     3.2 Revision: 1129
# --------------------------------------------------------

/*!40100 SET CHARACTER SET cp1251*/;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0*/;


DROP TABLE IF EXISTS `brigada`;

#
# Table structure for table 'brigada'
#

CREATE TABLE `brigada` (
  `ID` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `NAME` varchar(24) NOT NULL,
  `WRITER` tinyint(3) unsigned NOT NULL,
  `CREATED` datetime NOT NULL,
  `CHANGER` tinyint(3) unsigned NOT NULL,
  `CHANGED` datetime NOT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `ID` (`ID`,`NAME`),
  KEY `ID_2` (`ID`)
) ENGINE=InnoDB /*!40100 DEFAULT CHARSET=cp1251*/;



DROP TABLE IF EXISTS `operation`;

#
# Table structure for table 'operation'
#

CREATE TABLE `operation` (
  `ID` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `NAME` varchar(50) NOT NULL,
  `PARI` float unsigned NOT NULL DEFAULT '0',
  `ACTIVE` tinyint(3) unsigned NOT NULL DEFAULT '1',
  `WRITER` tinyint(3) unsigned NOT NULL,
  `CREATED` datetime NOT NULL,
  `CHANGER` tinyint(3) unsigned NOT NULL,
  `CHANGED` datetime NOT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `ID` (`ID`,`NAME`),
  KEY `ID_2` (`ID`)
) ENGINE=InnoDB /*!40100 DEFAULT CHARSET=cp1251*/;



DROP TABLE IF EXISTS `orders`;

#
# Table structure for table 'orders'
#

CREATE TABLE `orders` (
  `ID` mediumint(8) unsigned NOT NULL,
  `PROJECT` varchar(50) NOT NULL,
  `COUNTRY` varchar(35) NOT NULL,
  `ACTIVE` tinyint(3) unsigned NOT NULL DEFAULT '1',
  `WRITER` tinyint(3) unsigned NOT NULL,
  `CREATED` datetime NOT NULL,
  `CHANGER` tinyint(3) unsigned NOT NULL,
  `CHANGED` datetime NOT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `ID` (`ID`,`PROJECT`),
  KEY `ID_2` (`ID`)
) ENGINE=InnoDB /*!40100 DEFAULT CHARSET=cp1251*/;



DROP TABLE IF EXISTS `person`;

#
# Table structure for table 'person'
#

CREATE TABLE `person` (
  `ID` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `NAME` varchar(80) NOT NULL,
  `PREKOR` varchar(20) NOT NULL,
  `BRIGADA` tinyint(3) unsigned NOT NULL DEFAULT '1',
  `ACTIVE` tinyint(3) unsigned NOT NULL DEFAULT '1',
  `WRITER` tinyint(3) unsigned NOT NULL,
  `CREATED` datetime NOT NULL,
  `CHANGER` tinyint(3) unsigned NOT NULL,
  `CHANGED` datetime NOT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `ID` (`ID`,`NAME`,`PREKOR`),
  KEY `ID_2` (`ID`,`BRIGADA`),
  KEY `BRIGADA` (`BRIGADA`),
  CONSTRAINT `person_brigada_fk` FOREIGN KEY (`BRIGADA`) REFERENCES `brigada` (`ID`) ON UPDATE CASCADE
) ENGINE=InnoDB /*!40100 DEFAULT CHARSET=cp1251*/;



DROP TABLE IF EXISTS `rabota`;

#
# Table structure for table 'rabota'
#

CREATE TABLE `rabota` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `NOMER` mediumint(5) unsigned NOT NULL,
  `DATA` date NOT NULL,
  `OPERAT` smallint(5) unsigned NOT NULL,
  `BROI` float unsigned NOT NULL,
  `ORDERID` mediumint(8) unsigned NOT NULL,
  `WRITER` tinyint(3) unsigned NOT NULL,
  `CREATED` datetime NOT NULL,
  `CHANGER` tinyint(3) unsigned NOT NULL,
  `CHANGED` datetime NOT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `ID` (`ID`),
  UNIQUE KEY `ID_2` (`NOMER`,`DATA`,`OPERAT`,`ORDERID`),
  KEY `DATUM` (`DATA`)
) ENGINE=InnoDB /*!40100 DEFAULT CHARSET=cp1251*/;



DROP TABLE IF EXISTS `user`;

#
# Table structure for table 'user'
#

CREATE TABLE `user` (
  `ID` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `LOGIN` varchar(16) CHARACTER SET cp1251 COLLATE cp1251_bin NOT NULL,
  `PASS` varchar(16) CHARACTER SET cp1251 COLLATE cp1251_bin NOT NULL,
  `NAME` varchar(50) NOT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `ID` (`LOGIN`,`PASS`),
) ENGINE=InnoDB /*!40100 DEFAULT CHARSET=cp1251*/;

/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS*/;
