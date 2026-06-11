DROP DATABASE IF EXISTS `takseringshjaelp_(proxy)`;
CREATE DATABASE `takseringshjaelp_(proxy)`;
USE `takseringshjaelp_(proxy)`;

CREATE TABLE `company` (
	`id` int unsigned NOT NULL AUTO_INCREMENT,
	`tenant` varchar(16) NOT NULL,
	`location` int unsigned DEFAULT NULL,
	`token` char(41) NOT NULL,
	`bearer` char(64) NOT NULL,
	`lastuse` datetime DEFAULT NULL,
	`active` tinyint(1) unsigned NOT NULL,
	PRIMARY KEY (`id`),
	UNIQUE KEY `tenant` (`tenant`,`token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_uca1400_ai_ci;

GRANT SELECT, INSERT, UPDATE, DELETE ON `takseringshjaelp`.* TO 'takseringshjaelp'@'localhost';
