SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

CREATE TABLE `hesk_attachments` (
  `att_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `ticket_id` varchar(10) NOT NULL DEFAULT '',
  `saved_name` varchar(255) NOT NULL DEFAULT '',
  `real_name` varchar(255) NOT NULL DEFAULT '',
  `size` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`att_id`),
  KEY `ticket_id` (`ticket_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

CREATE TABLE `hesk_categories` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(60) NOT NULL DEFAULT '',
  `cat_order` smallint(5) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

CREATE TABLE `hesk_kb_articles` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `catid` smallint(5) unsigned NOT NULL,
  `dt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `author` smallint(5) unsigned NOT NULL,
  `subject` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `rating` float NOT NULL DEFAULT '0',
  `votes` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `views` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `type` enum('0','1','2') NOT NULL DEFAULT '0',
  `html` enum('0','1') NOT NULL DEFAULT '0',
  `art_order` smallint(5) unsigned NOT NULL DEFAULT '0',
  `history` text NOT NULL,
  `attachments` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `catid` (`catid`),
  KEY `type` (`type`),
  FULLTEXT KEY `subject` (`subject`,`content`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `hesk_kb_attachments` (
  `att_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `saved_name` varchar(255) NOT NULL DEFAULT '',
  `real_name` varchar(255) NOT NULL DEFAULT '',
  `size` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`att_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `hesk_kb_categories` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `parent` smallint(5) unsigned NOT NULL,
  `articles` smallint(5) unsigned NOT NULL,
  `cat_order` smallint(5) unsigned NOT NULL,
  `type` enum('0','1') NOT NULL,
  PRIMARY KEY (`id`),
  KEY `type` (`type`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

CREATE TABLE `hesk_notes` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `ticket` mediumint(8) unsigned NOT NULL,
  `who` smallint(5) unsigned NOT NULL,
  `dt` datetime NOT NULL,
  `message` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `ticketid` (`ticket`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

CREATE TABLE `hesk_replies` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `replyto` varchar(10) NOT NULL DEFAULT '',
  `name` varchar(50) NOT NULL DEFAULT '',
  `message` text NOT NULL,
  `dt` datetime DEFAULT NULL,
  `attachments` text,
  `staffid` smallint(5) unsigned NOT NULL DEFAULT '0',
  `rating` enum('1','5') DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `replyto` (`replyto`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

CREATE TABLE `hesk_std_replies` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(100) NOT NULL DEFAULT '',
  `message` text NOT NULL,
  `reply_order` smallint(5) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

CREATE TABLE `hesk_tickets` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `trackid` varchar(10) NOT NULL DEFAULT '',
  `name` varchar(50) NOT NULL DEFAULT '',
  `email` varchar(50) NOT NULL DEFAULT '',
  `category` smallint(5) unsigned NOT NULL DEFAULT '1',
  `priority` enum('1','2','3') NOT NULL DEFAULT '3',
  `subject` varchar(70) NOT NULL DEFAULT '',
  `message` text NOT NULL,
  `dt` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `lastchange` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `ip` varchar(20) NOT NULL DEFAULT '',
  `status` enum('0','1','2','3') DEFAULT '1',
  `lastreplier` enum('0','1') NOT NULL DEFAULT '0',
  `archive` enum('0','1') NOT NULL DEFAULT '0',
  `attachments` text,
  `custom1` text NOT NULL,
  `custom2` text NOT NULL,
  `custom3` text NOT NULL,
  `custom4` text NOT NULL,
  `custom5` text NOT NULL,
  `custom6` text NOT NULL,
  `custom7` text NOT NULL,
  `custom8` text NOT NULL,
  `custom9` text NOT NULL,
  `custom10` text NOT NULL,
  `custom11` text NOT NULL,
  `custom12` text NOT NULL,
  `custom13` text NOT NULL,
  `custom14` text NOT NULL,
  `custom15` text NOT NULL,
  `custom16` text NOT NULL,
  `custom17` text NOT NULL,
  `custom18` text NOT NULL,
  `custom19` text NOT NULL,
  `custom20` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `trackid` (`trackid`),
  KEY `archive` (`archive`),
  KEY `categories` (`category`),
  KEY `statuses` (`status`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

CREATE TABLE `hesk_users` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `user` varchar(20) NOT NULL DEFAULT '',
  `pass` char(40) NOT NULL,
  `isadmin` enum('0','1') NOT NULL DEFAULT '0',
  `name` varchar(50) NOT NULL DEFAULT '',
  `email` varchar(255) NOT NULL DEFAULT '',
  `signature` varchar(255) NOT NULL DEFAULT '',
  `categories` varchar(255) NOT NULL DEFAULT '',
  `afterreply` enum('0','1','2') NOT NULL DEFAULT '0',
  `notify` enum('0','1') NOT NULL DEFAULT '1',
  `heskprivileges` varchar(255) NOT NULL DEFAULT '',
  `ratingneg` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `ratingpos` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `rating` float NOT NULL DEFAULT '0',
  `replies` mediumint(8) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

INSERT INTO `hesk_categories` (`id`, `name`, `cat_order`) VALUES
(1, '-Выберите тип запроса-', 10),
(8, 'Проблемы со входом на сайт', 20),
(9, 'Проблемы с почтой', 30),
(10, 'Опечатка на странице', 80),
(11, 'Ошибка', 90),
(12, 'Проблемы с доской объявлений', 40),
(13, 'другое', 100),
(14, 'Раздел Работа', 60),
(15, 'Проблемы со справочником товаров', 50),
(16, 'Разделы: Банки, Авто, Еда, Отели и др.', 70);
