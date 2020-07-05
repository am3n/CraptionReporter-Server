CREATE TABLE IF NOT EXISTS `reports` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cid` int(11) NOT NULL, 
  `timestamp` varchar(20) DEFAULT NULL,
  `occur_date` varchar(20) DEFAULT NULL,
  `debug` tinyint(1) NOT NULL DEFAULT '1',
  `fatal` tinyint(1) NOT NULL DEFAULT '0',
  `solved` tinyint(1) NOT NULL DEFAULT '0',
  `note` varchar(512) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `stack_trace` text,
  PRIMARY KEY (`id`)
);