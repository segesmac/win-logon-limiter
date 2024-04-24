CREATE USER 'timeuser'@'localhost' IDENTIFIED BY 'WOULDNTYOULIKETOKNOW';
GRANT INSERT,UPDATE,SELECT ON winlogonlimiter.usertimetable TO 'timeuser'@'localhost';
