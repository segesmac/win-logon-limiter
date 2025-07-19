CREATE TABLE `logtable` (
  `logid` INT AUTO_INCREMENT PRIMARY KEY,
  `logdatetime` datetime DEFAULT CURRENT_TIMESTAMP,
  `logmessage` TEXT NOT NULL,
  `usertableid` INT NOT NULL,
  FOREIGN KEY (`usertableid`) REFERENCES `usertable`(`usertableid`)
) /*ENGINE=InnoDB DEFAULT CHARSET=utf8mb4*/;

