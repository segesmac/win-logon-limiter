CREATE TABLE `usertimetable` (
  `usertimetableid` int(11) NOT NULL AUTO_INCREMENT,
  `lastrowupdate` datetime DEFAULT NULL,
  `username` varchar(50) DEFAULT NULL,
  `isloggedon` tinyint(1) DEFAULT NULL,
  `lastlogon` datetime DEFAULT NULL,
  `lastheartbeat` datetime DEFAULT NULL,
  `timelimitminutes` decimal(6,2) DEFAULT 60.0,
  `timeleftminutes` decimal(6,2) DEFAULT 0.0,
  `bonustimeminutes` decimal(6,2) DEFAULT 0.0,
  `computername` varchar(20) DEFAULT NULL,
  `bonuscounters` decimal(6,2) DEFAULT 0.0,
  PRIMARY KEY (`usertimetableid`)
) /*ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4*/;
