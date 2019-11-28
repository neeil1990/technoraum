CREATE TABLE IF NOT EXISTS `b_dwstroy_pricechanger` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `NAME` text,
  `ACTIVE` char(1) DEFAULT 'Y',
  `PERIOD` char(1) DEFAULT 'N',
  `INTERVAL` int(18) DEFAULT '86400',
  `NEXT_EXEC` datetime DEFAULT NULL,
  `SORT` int(18) NOT NULL DEFAULT '100',
  `COUNT` int(18) NOT NULL DEFAULT '25',
  `DATE_CHANGE` datetime DEFAULT NULL,
  `DATE_EXEC` datetime DEFAULT NULL,
  `SITES` text,
  `RULE` text,
  `ACTIONS` text,
  PRIMARY KEY (`ID`)
);
CREATE TABLE IF NOT EXISTS `b_dwstroy_pricechanger_runtime` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `COND_ID` int(11) NOT NULL,
  `RULES` text,
  `ACTIONS`  text,
  `PAGES` int(18) NOT NULL DEFAULT '1',
  `PAGE` int(18) NOT NULL DEFAULT '1',
  `CNT` int(18) NOT NULL DEFAULT '25',
  `RUNNING` char(1) DEFAULT 'Y',
  PRIMARY KEY (`ID`)
);