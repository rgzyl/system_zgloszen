DROP TABLE IF EXISTS `str_user`;
CREATE TABLE IF NOT EXISTS `str_user` (
  `IdUser` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(100) COLLATE utf8_polish_ci DEFAULT NULL,
  `password` varchar(255) COLLATE utf8_polish_ci DEFAULT NULL,
  `name` varchar(100) COLLATE utf8_polish_ci DEFAULT NULL,
  `surname` varchar(100) COLLATE utf8_polish_ci DEFAULT NULL,
  `email` varchar(100) COLLATE utf8_polish_ci DEFAULT NULL,
  `role` varchar(10) COLLATE utf8_polish_ci DEFAULT NULL,
  `2fa_secret` varchar(255) COLLATE utf8_polish_ci DEFAULT NULL,
  `data` datetime DEFAULT CURRENT_TIMESTAMP,
  `attempt` int(11) NOT NULL DEFAULT '0',
  `data_attempt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ip` varchar(15) COLLATE utf8_polish_ci DEFAULT NULL,
  `status` varchar(1) COLLATE utf8_polish_ci DEFAULT NULL,
  PRIMARY KEY (`IdUser`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci;


INSERT INTO `str_user` (`IdUser`, `username`, `password`, `name`, `surname`, `email`, `role`, `2fa_secret`, `ip`, `status`) VALUES
(1, 'system', '-', '-', '-', '-', 'system', '-', '-', '-');


DROP TABLE IF EXISTS `str_user_check`;
CREATE TABLE IF NOT EXISTS `str_user_check` (
  `IdUserCheck` int(11) NOT NULL AUTO_INCREMENT,
  `opis` text COLLATE utf8_polish_ci,
  `ip` varchar(15) COLLATE utf8_polish_ci DEFAULT NULL,
  `data` datetime DEFAULT CURRENT_TIMESTAMP,
  `kolor` varchar(10) COLLATE utf8_polish_ci DEFAULT NULL,
  `IdUser` int(11) DEFAULT NULL,
  PRIMARY KEY (`IdUserCheck`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci;


DROP TABLE IF EXISTS `str_user_tokens`;
CREATE TABLE IF NOT EXISTS `str_user_tokens` (
  `IdUser` int(11) NOT NULL,
  `token` varchar(64) COLLATE utf8_polish_ci NOT NULL,
  `expiry` datetime NOT NULL,
  PRIMARY KEY (`IdUser`,`token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci;


DROP TABLE IF EXISTS `str_config`;
CREATE TABLE IF NOT EXISTS `str_config` (
  `IdConfig` int(11) NOT NULL AUTO_INCREMENT,
  `nazwa` varchar(255) COLLATE utf8_polish_ci DEFAULT NULL,
  `opis` text COLLATE utf8_polish_ci,
  `status` varchar(1) COLLATE utf8_polish_ci DEFAULT NULL,
  PRIMARY KEY (`IdConfig`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci;


INSERT INTO `str_config` (`IdConfig`, `nazwa`, `opis`, `status`) VALUES
(1, 'Tymczasowo zamknięte', 'Strona jest tymczasowo niedostępna. Zapraszamy wkrótce.', '3');


DROP TABLE IF EXISTS `str_wies`;
CREATE TABLE IF NOT EXISTS `str_wies` (
  `IdWies` int(11) NOT NULL AUTO_INCREMENT,
  `nazwa` varchar(100) COLLATE utf8_polish_ci DEFAULT NULL,
  PRIMARY KEY (`IdWies`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci;


INSERT INTO `str_wies` (`IdWies`, `nazwa`) VALUES
(1, 'Strzegom'),
(2, 'Bartoszówek'),
(3, 'Goczałków'),
(4, 'Goczałków Górny'),
(5, 'Godzieszówek'),
(6, 'Granica'),
(7, 'Graniczna'),
(8, 'Grochotów'),
(9, 'Jaroszów'),
(10, 'Kostrza'),
(11, 'Międzyrzecze'),
(12, 'Modlęcin'),
(13, 'Morawa'),
(14, 'Olszany'),
(15, 'Rogoźnica'),
(16, 'Rusko'),
(17, 'Skarżyce'),
(18, 'Stanowice'),
(19, 'Stawiska'),
(20, 'Tomkowice'),
(21, 'Wieśnica'),
(22, 'Żelazów'),
(23, 'Żółkiewka');


DROP TABLE IF EXISTS `str_zdjecia`;
CREATE TABLE IF NOT EXISTS `str_zdjecia` (
  `IdZdjecia` int(11) NOT NULL AUTO_INCREMENT,
  `nazwa` text COLLATE utf8_polish_ci,
  `IdZgloszenie` int(11) DEFAULT '0',
  PRIMARY KEY (`IdZdjecia`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci;


DROP TABLE IF EXISTS `str_zgloszenie`;
CREATE TABLE IF NOT EXISTS `str_zgloszenie` (
  `IdZgloszenie` int(11) NOT NULL AUTO_INCREMENT,
  `imie` varchar(100) COLLATE utf8_polish_ci DEFAULT NULL,
  `nazwisko` varchar(100) COLLATE utf8_polish_ci DEFAULT NULL,
  `adres` varchar(100) COLLATE utf8_polish_ci DEFAULT NULL,
  `kod` varchar(6) COLLATE utf8_polish_ci DEFAULT NULL,
  `IdWies` int(11) DEFAULT NULL,
  `telefon` varchar(12) COLLATE utf8_polish_ci DEFAULT NULL,
  `mail` varchar(100) COLLATE utf8_polish_ci DEFAULT NULL,
  `opis` text COLLATE utf8_polish_ci,
  `data` datetime DEFAULT CURRENT_TIMESTAMP,
  `ip` varchar(15) COLLATE utf8_polish_ci DEFAULT NULL,
  `stat` varchar(1) COLLATE utf8_polish_ci NOT NULL DEFAULT '0',
  PRIMARY KEY (`IdZgloszenie`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci;