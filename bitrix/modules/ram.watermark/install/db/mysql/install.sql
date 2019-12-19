CREATE TABLE ram_watermark_mark
(
	`ID` int(11) NOT NULL AUTO_INCREMENT,
	`NAME` varchar(255) NOT NULL,
	`PARAMS` longtext NOT NULL,
	`ACTIVE` char(1) NOT NULL DEFAULT 'N',
	PRIMARY KEY (`ID`)
);

CREATE TABLE ram_watermark_filter
(
	`ID` int(11) NOT NULL AUTO_INCREMENT,
	`WMID` int(11) NOT NULL,
	`MODULE` varchar(50) NOT NULL,
	`FIELD` varchar(50) DEFAULT NULL,
	`TYPE` varchar(7) DEFAULT NULL,
	`GROUP` int(11) DEFAULT NULL,
	`OBJECT` varchar(50) DEFAULT NULL,
	`ENTITY` varchar(50) DEFAULT NULL,
	PRIMARY KEY (`ID`)
);

CREATE TABLE ram_watermark_image
(
	`ID` int(11) NOT NULL AUTO_INCREMENT,
	`IMAGEID` int(11) NOT NULL,
	`WIDTH` int(11) DEFAULT NULL,
	`HEIGHT` int(11) DEFAULT NULL,
	`TYPE` varchar(50) DEFAULT NULL,
	`MODULE` varchar(50) DEFAULT NULL,
	`ENTITY` int(11) DEFAULT NULL,
	`FIELD` varchar(50) DEFAULT NULL,
	`DATE` datetime DEFAULT NULL,
	`HASH` varchar(50) DEFAULT NULL,
	`ITEM` varchar(50) DEFAULT NULL,
	`OBJECT` int(11) DEFAULT NULL,
	`TAG` varchar(50) DEFAULT NULL,
	PRIMARY KEY (`ID`)
);