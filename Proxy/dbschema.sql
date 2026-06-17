
CREATE TABLE `company` (
	`id` int unsigned NOT NULL AUTO_INCREMENT,
	`tenant` varchar(16) NOT NULL,
	`location` int unsigned DEFAULT NULL,
	`token` char(41) NOT NULL,
	`bearer` char(64) NOT NULL,
	`lastuse` datetime DEFAULT NULL,
	`active` tinyint unsigned NOT NULL,
	PRIMARY KEY (`id`),
	UNIQUE KEY `tenant` (`tenant`,`token`)
);
