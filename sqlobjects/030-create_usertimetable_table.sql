CREATE TABLE `usertimetable` (
  `usertimetableid` int(11) NOT NULL AUTO_INCREMENT,
  `lastrowupdate` datetime DEFAULT NULL,
  `username` varchar(50) DEFAULT NULL,
  `isloggedon` tinyint(1) DEFAULT NULL,
  `lastlogon` datetime DEFAULT NULL,
  `lastheartbeat` datetime DEFAULT NULL,
  `timelimitminutes` decimal(6,2) DEFAULT NULL,
  `timeleftminutes` decimal(6,2) DEFAULT NULL,
  `bonustimeminutes` decimal(6,2) DEFAULT NULL,
  `computername` varchar(20) DEFAULT NULL,
  `bonuscounters` int(11) DEFAULT NULL,
  PRIMARY KEY (`usertimetableid`)
) /*ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4*/;
