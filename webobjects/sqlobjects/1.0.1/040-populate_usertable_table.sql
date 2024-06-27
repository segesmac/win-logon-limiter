INSERT INTO `usertable` (`usertimetableid`,`username`) 
  SELECT `usertimetableid`, `username` 
  FROM `usertimetable`
  /*ENGINE=InnoDB DEFAULT CHARSET=utf8mb4*/;