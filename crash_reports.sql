CREATE TABLE IF NOT EXISTS `crash_reports` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `timestamp` varchar(20) DEFAULT NULL,
  `solved` tinyint(1) NOT NULL DEFAULT '0',
  `stack_trace` text,
  `occur_date` varchar(20) DEFAULT NULL,
  `user_identification` varchar(50) DEFAULT NULL,
  `app_version_code` int(11) DEFAULT NULL,
  `os_version` varchar(20) DEFAULT NULL,
  `cpu` varchar(30) DEFAULT NULL,
  `device_imei` varchar(16) DEFAULT NULL,
  `device_model` varchar(30) DEFAULT NULL,
  `device_screenclass` varchar(10) DEFAULT NULL,
  `device_dpiclass` varchar(10) DEFAULT NULL,
  `device_screensize` varchar(5) DEFAULT NULL,
  `device_screen_dimensions_dpis` varchar(11) DEFAULT NULL,
  `device_screen_dimensions_pixels` varchar(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
);