CREATE TABLE IF NOT EXISTS `log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cid` int(11) NOT NULL, 
  `timestamp` varchar(20) DEFAULT NULL,
  `note` varchar(512) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `body` text,
  PRIMARY KEY (`id`)
);