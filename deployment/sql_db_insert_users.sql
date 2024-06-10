USE winlogonlimiter;
INSERT INTO usertimetable (lastrowupdate, username, isloggedon, lastlogon, lastheartbeat, timelimitminutes, timeleftminutes, bonustimeminutes, computername, bonuscounters)
VALUES 
    ('2024-06-08 18:22:14', 'jason', 0, NULL, '2024-06-08 18:22:14', 60.00, 0.00, 66.12, NULL, 29.00),
    ('2024-06-08 19:40:37', 'melody', 0, NULL, '2024-06-08 19:40:37', 60.00, 0.00, 0.00, NULL, 24.00),
    ('2024-06-08 17:29:45', 'zachary', 0, NULL, '2024-06-08 17:29:45', 60.00, 0.00, 0.00, NULL, 21.00),
    (NULL, 'segesmac', 1, NULL, NULL, -1.00, -1.00, 0.00, NULL, 0.00),
    (NULL, 'furball', 0, NULL, NULL, -1.00, -1.00, 0.00, NULL, 0.00),
    (NULL, 'loginguest', 0, NULL, NULL, -1.00, -1.00, 0.00, NULL, 0.00),
    (NULL, 'Rebecca', 0, NULL, NULL, -1.00, -1.00, 0.00, NULL, 0.00),
    (NULL, 'Abecca', 0, NULL, NULL, -1.00, -1.00, 0.00, NULL, 0.00),
    (NULL, 'abeccable', 0, NULL, NULL, -1.00, -1.00, 0.00, NULL, 0.00),
    ('2024-06-08 19:44:03', 'harmony', 1, NULL, '2024-06-08 19:44:03', 60.00,  0.00, 48.28, 'DESKTOP-HARMONY', 12.00),
    (NULL, 'furba', 0, NULL, NULL, -1.00, -1.00,  0.00, NULL,  0.00),
    ('2024-05-29 20:24:59', 'kidsguest', 0, NULL, '2024-05-29 20:24:59', 60.00, 60.00,  0.00, NULL,  0.00);