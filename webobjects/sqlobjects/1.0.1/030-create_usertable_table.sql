CREATE TABLE `usertable` (
  `usertableid` int(11) NOT NULL AUTO_INCREMENT,
  `usertimetableid` int(11) NOT NULL,
  `username` varchar(50) DEFAULT NULL,
  `isloggedon` tinyint(1) DEFAULT NULL,
  `lastlogon` datetime DEFAULT NULL,
  `passwordhash` varchar(255) DEFAULT NULL,
  `isadmin` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`usertableid`),
  FOREIGN KEY (`usertimetableid`) REFERENCES `usertimetable`(`usertimetableid`)
) /*ENGINE=InnoDB DEFAULT CHARSET=utf8mb4*/;
