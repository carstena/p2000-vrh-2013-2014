== Install

CREATE TABLE IF NOT EXISTS `calls` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `hash` varchar(255) NOT NULL,
  `capcode` int(11) NOT NULL,
  `body` varchar(255) NOT NULL,
  `timestamp` datetime NOT NULL,
  `address` varchar(255) NOT NULL,
  `location` varchar(255) NOT NULL,
  `longitude` float NOT NULL,
  `latitude` float NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `hash` (`hash`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;