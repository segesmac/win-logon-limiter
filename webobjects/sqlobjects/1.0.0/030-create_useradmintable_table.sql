CREATE TABLE `useradmintable` (
  `useradmintableid` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) DEFAULT NULL,
  `isloggedon` tinyint(1) DEFAULT NULL,
  `lastlogon` datetime DEFAULT NULL,
  `salt` varchar(50) DEFAULT NULL,
  `passwordhash` varchar(128) DEFAULT NULL,
  PRIMARY KEY (`useradmintableid`)
) /*ENGINE=InnoDB DEFAULT CHARSET=utf8mb4*/;
