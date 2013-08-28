--
-- Database `bсtodomessages`
--

CREATE SCHEMA IF NOT EXISTS `bctodomessages` DEFAULT CHARACTER SET utf8 COLLATE=utf8_general_ci;
USE `bctodomessages` ;

-- --------------------------------------------------------

--
-- Table `todoitems`
--
DROP TABLE IF EXISTS `todoitems`;
CREATE TABLE IF NOT EXISTS `todoitems` (
  `itemid` int(11) NOT NULL,
  `itemhash` varchar(32) NOT NULL COMMENT 'Hash item',
  `idmsg` int(11) COMMENT 'ID message',
  PRIMARY KEY (`itemid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci COMMENT='todo items';
-- --------------------------------------------------------
