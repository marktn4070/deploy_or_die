CREATE TABLE `access` (
	`user_id` int(10) unsigned NOT NULL,
	`permission` varchar(30) NOT NULL,
	PRIMARY KEY (`user_id`,`permission`),
	FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_uca1400_danish_ai_ci;

CREATE TABLE `area` (
	`illustration_id` int(10) unsigned NOT NULL,
	`id` varchar(20) NOT NULL,
	`element` varchar(20) NOT NULL,
	`attribute` longtext NOT NULL,
	`name` varchar(40) NOT NULL DEFAULT '',
	PRIMARY KEY (`illustration_id`,`id`),
	FOREIGN KEY (`illustration_id`) REFERENCES `illustration` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_uca1400_danish_ai_ci;

CREATE TABLE `brand` (
	`id` char(3) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
	`name` varchar(30) NOT NULL,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_uca1400_danish_ai_ci;

CREATE TABLE `case` (
	`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
	`status` enum('new','ass','ong','pri','pho','rev','mis','apr','pst','snt','ctl','dne') NOT NULL DEFAULT 'new',
	`client_id` int(10) unsigned NOT NULL,
	`insurance_id` char(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
	`user_id` int(10) unsigned NOT NULL,
	`responsible_id` int(10) unsigned DEFAULT NULL,
	`done_id` int(10) unsigned DEFAULT NULL,
	`plate` varchar(7) NOT NULL,
	`attention` tinyint(1) unsigned NOT NULL DEFAULT 0,
	`note` text NOT NULL,
	`create` datetime NOT NULL DEFAULT current_timestamp(),
	`deadline` datetime DEFAULT NULL,
	`complete` datetime DEFAULT NULL,
	`invoice` date DEFAULT NULL,
	PRIMARY KEY (`id`),
	KEY `client_id` (`client_id`),
	KEY `status` (`status`),
	KEY `plate` (`plate`),
	KEY `invoice` (`invoice`),
	KEY `responsible_id` (`responsible_id`),
	FOREIGN KEY (`client_id`) REFERENCES `client` (`id`),
	FOREIGN KEY (`insurance_id`) REFERENCES `insurance` (`id`),
	FOREIGN KEY (`responsible_id`) REFERENCES `user` (`id`),
	FOREIGN KEY (`done_id`) REFERENCES `user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_uca1400_danish_ai_ci;

CREATE TABLE `case_comment` (
	`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
	`case_id` int(10) unsigned NOT NULL,
	`create_time` datetime NOT NULL DEFAULT current_timestamp(),
	`create_by` int(10) unsigned NOT NULL,
	`content` text NOT NULL,
	PRIMARY KEY (`id`),
	KEY `case_id` (`case_id`),
	FOREIGN KEY (`case_id`) REFERENCES `case` (`id`),
	FOREIGN KEY (`create_by`) REFERENCES `user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_uca1400_danish_ai_ci;

CREATE TABLE `case_file` (
	`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
	`case_id` int(10) unsigned NOT NULL,
	`name` varchar(100) NOT NULL,
	`size` int(10) unsigned NOT NULL,
	`mime` varchar(128) NOT NULL,
	`checksum` char(64) NOT NULL,
	PRIMARY KEY (`id`),
	KEY `case_id` (`case_id`),
	FOREIGN KEY (`case_id`) REFERENCES `case` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_uca1400_danish_ai_ci;

CREATE TABLE `client` (
	`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
	`name` varchar(40) NOT NULL,
	`workshop` varchar(6) NOT NULL DEFAULT '',
	`debitor` varchar(10) NOT NULL DEFAULT '',
	`phone` varchar(8) NOT NULL DEFAULT '',
	`processing` int(5) NOT NULL DEFAULT 50,
	`refresh_token` text DEFAULT NULL,
	`agreement` text DEFAULT NULL,
	`body` double unsigned NOT NULL DEFAULT 0,
	`paint` double unsigned NOT NULL DEFAULT 0,
	`sparepart` double unsigned NOT NULL DEFAULT 0,
	`fixed` double unsigned NOT NULL DEFAULT 0,
	`batch` tinyint(1) unsigned NOT NULL DEFAULT 0,
	`disable` tinyint(1) unsigned NOT NULL DEFAULT 0,
	`archive` tinyint(1) unsigned NOT NULL DEFAULT 0,
	PRIMARY KEY (`id`),
	KEY `disable` (`disable`),
	KEY `archive` (`archive`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_uca1400_danish_ai_ci;

CREATE TABLE `client_email` (
	`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
	`client_id` int(10) unsigned NOT NULL,
	`email` varchar(80) NOT NULL,
	PRIMARY KEY (`id`),
	KEY `client_id` (`client_id`),
	FOREIGN KEY (`client_id`) REFERENCES `client` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_uca1400_danish_ai_ci;

CREATE TABLE `client_group` (
	`client_id` int(10) unsigned NOT NULL,
	`group_id` int(10) unsigned NOT NULL,
	PRIMARY KEY (`client_id`,`group_id`),
	FOREIGN KEY (`client_id`) REFERENCES `client` (`id`),
	FOREIGN KEY (`group_id`) REFERENCES `group` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_uca1400_danish_ai_ci;

CREATE TABLE `client_organization` (
	`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
	`client_id` int(10) unsigned NOT NULL,
	`organization_id` int(10) unsigned DEFAULT NULL,
	`guide` text NOT NULL,
	`priority` enum('light','primary','warning','danger') NOT NULL DEFAULT 'primary',
	`url` varchar(100) NOT NULL,
	PRIMARY KEY (`id`),
	KEY `client_id` (`client_id`),
	FOREIGN KEY (`client_id`) REFERENCES `client` (`id`),
	FOREIGN KEY (`organization_id`) REFERENCES `organization` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_uca1400_danish_ai_ci;

CREATE TABLE `damage` (
	`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
	`case_id` int(10) unsigned NOT NULL,
	`state` varchar(45) NOT NULL DEFAULT '',
	`number` varchar(13) NOT NULL,
	`notice` text DEFAULT NULL,
	`body` double unsigned NOT NULL DEFAULT 0,
	`paint` double unsigned NOT NULL DEFAULT 0,
	`sparepart` double unsigned NOT NULL DEFAULT 0,
	`fixed` double unsigned NOT NULL DEFAULT 0,
	`void` tinyint(1) unsigned NOT NULL DEFAULT 0,
	`report_init_checksum` char(64) DEFAULT NULL,
	`report_sent_checksum` char(64) DEFAULT NULL,
	PRIMARY KEY (`id`),
	KEY `case_id` (`case_id`),
	KEY `number` (`number`),
	FOREIGN KEY (`case_id`) REFERENCES `case` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_uca1400_danish_ai_ci;

CREATE TABLE `group` (
	`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
	`name` varchar(25) NOT NULL,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_uca1400_danish_ai_ci;

CREATE TABLE `group_organization` (
	`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
	`group_id` int(10) unsigned NOT NULL,
	`organization_id` int(10) unsigned DEFAULT NULL,
	`guide` text NOT NULL,
	`priority` enum('light','primary','warning','danger') NOT NULL DEFAULT 'primary',
	`url` varchar(100) NOT NULL,
	PRIMARY KEY (`id`),
	KEY `group_id` (`group_id`),
	FOREIGN KEY (`group_id`) REFERENCES `group` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT,
	FOREIGN KEY (`organization_id`) REFERENCES `organization` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_uca1400_danish_ai_ci;

CREATE TABLE `group_quicklist` (
	`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
	`group_id` int(10) unsigned NOT NULL,
	`line` varchar(100) NOT NULL,
	PRIMARY KEY (`id`),
	KEY `group_id` (`group_id`),
	FOREIGN KEY (`group_id`) REFERENCES `group` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_uca1400_danish_ai_ci;

CREATE TABLE `illustration` (
	`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
	`name` varchar(40) NOT NULL,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_uca1400_danish_ai_ci;

CREATE TABLE `insurance` (
	`id` char(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
	`name` varchar(25) NOT NULL,
	`organization_id` int(10) unsigned NOT NULL,
	PRIMARY KEY (`id`),
	FOREIGN KEY (`organization_id`) REFERENCES `organization` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_uca1400_danish_ai_ci;

CREATE TABLE `model` (
	`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
	`brand_id` char(3) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
	`model` varchar(60) NOT NULL,
	`start` year(4) DEFAULT NULL,
	`stop` year(4) DEFAULT NULL,
	`illustration_id` int(10) unsigned DEFAULT NULL,
	PRIMARY KEY (`id`),
	KEY `brand_id` (`brand_id`),
	FOREIGN KEY (`brand_id`) REFERENCES `brand` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_uca1400_danish_ai_ci;

CREATE TABLE `organization` (
	`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
	`name` varchar(30) NOT NULL,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_uca1400_danish_ai_ci;

CREATE TABLE `point` (
	`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
	`model_id` int(10) unsigned NOT NULL,
	`area_id` varchar(20) NOT NULL,
	`method` varchar(20) NOT NULL,
	`note` text DEFAULT NULL,
	PRIMARY KEY (`id`),
	KEY `model_id` (`model_id`,`area_id`),
	FOREIGN KEY (`model_id`) REFERENCES `model` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_uca1400_danish_ai_ci;

CREATE TABLE `point_file` (
	`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
	`point_id` int(10) unsigned NOT NULL,
	`name` varchar(100) NOT NULL DEFAULT '',
	`mime` varchar(128) NOT NULL DEFAULT '',
	`size` int(10) unsigned NOT NULL DEFAULT 0,
	`checksum` char(64) NOT NULL DEFAULT '',
	PRIMARY KEY (`id`),
	KEY `point_id` (`point_id`),
	FOREIGN KEY (`point_id`) REFERENCES `point` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_uca1400_danish_ai_ci;

CREATE TABLE `report` (
	`client_id` int(10) unsigned NOT NULL,
	`uniq` varchar(13) NOT NULL,
	`version` varchar(2) NOT NULL,
	`datetime_approve` datetime NOT NULL,
	`price_work` float unsigned NOT NULL,
	`price_part` float unsigned NOT NULL,
	`price_paint` float unsigned NOT NULL,
	`price_mat` float unsigned NOT NULL,
	PRIMARY KEY (`client_id`,`uniq`),
	KEY `datetime_approve` (`datetime_approve`),
	FOREIGN KEY (`client_id`) REFERENCES `client` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_uca1400_danish_ai_ci;

CREATE TABLE `token` (
	`user_id` int(10) UNSIGNED NOT NULL,
	`token` varchar(44) NOT NULL,
	`expires` DATETIME NOT NULL,
	PRIMARY KEY (`token`),
	KEY `user_id` (`user_id`),
	FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_uca1400_danish_ai_ci;

CREATE TABLE `user` (
	`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
	`name` varchar(25) NOT NULL DEFAULT '',
	`phone` varchar(8) NOT NULL DEFAULT '',
	`username` varchar(50) NOT NULL,
	`password` varchar(255) NOT NULL DEFAULT '',
	`confirmation` varchar(255) NOT NULL DEFAULT '',
	`darktheme` tinyint(1) unsigned NOT NULL DEFAULT 0,
	PRIMARY KEY (`id`),
	UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_uca1400_danish_ai_ci;

CREATE TABLE `user_client` (
	`user_id` int(10) unsigned NOT NULL,
	`client_id` int(10) unsigned NOT NULL,
	`bearer` char(64) DEFAULT NULL,
	PRIMARY KEY (`user_id`,`client_id`),
	KEY `client_id` (`client_id`),
	KEY `bearer` (`bearer`),
	FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE,
	FOREIGN KEY (`client_id`) REFERENCES `client` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_uca1400_danish_ai_ci;

CREATE TABLE `user_insurance` (
	`user_id` int(10) unsigned NOT NULL,
	`insurance_id` char(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
	PRIMARY KEY (`user_id`,`insurance_id`),
	FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE,
	FOREIGN KEY (`insurance_id`) REFERENCES `insurance` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_uca1400_danish_ai_ci;

GRANT DELETE, INSERT, SELECT, UPDATE ON `takseringshjaelp`.* TO `takseringshjaelp`@`localhost`;
