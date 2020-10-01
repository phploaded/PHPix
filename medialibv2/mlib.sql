-- phpMyAdmin SQL Dump
-- version 3.2.4
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: May 04, 2015 at 11:39 AM
-- Server version: 5.1.41
-- PHP Version: 5.3.1

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `mlib`
--

-- --------------------------------------------------------

--
-- Table structure for table `phpix_import`
--

CREATE TABLE IF NOT EXISTS `phpix_import` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(5000) NOT NULL,
  `content` varchar(5000) NOT NULL,
  `time` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=10 ;

--
-- Dumping data for table `phpix_import`
--

INSERT INTO `phpix_import` (`id`, `title`, `content`, `time`) VALUES
(2, 'Full URL for multiple lines', '%%url%% [%%fullsize%%]&lt;br /&gt;', 1420712613),
(3, 'Non image files as downloads', '&lt;p class=&quot;demo-download&quot;&gt;&lt;img src=&quot;%%thumb%%&quot; /&gt; &lt;b&gt;%%title%% (%%fullsize%%, %%type%% file)&lt;/b&gt; &lt;a href=&quot;%%url%%&quot;&gt;DOWNLOAD&lt;/a&gt;&lt;/p&gt;', 1420714475),
(6, 'thumbs', '&lt;a href=&quot;%%url%%&quot; target=&quot;_blank&quot;&gt;&lt;img src=&quot;%%thumb%%&quot; /&gt;&lt;/a&gt;', 1420784828),
(8, 'Full img tag', '&lt;img src=&quot;%%url%%&quot;&gt;', 1421235450),
(9, 'Thumbnail img tag', '&lt;img src=&quot;%%thumb%%&quot;&gt;', 1421494938);

-- --------------------------------------------------------

--
-- Table structure for table `phpix_uploads`
--

CREATE TABLE IF NOT EXISTS `phpix_uploads` (
  `id` varchar(500) NOT NULL,
  `type` varchar(50) NOT NULL,
  `title` varchar(500) NOT NULL,
  `caption` varchar(5000) NOT NULL,
  `url` varchar(5000) NOT NULL,
  `thumb` varchar(5000) NOT NULL,
  `time` int(11) NOT NULL,
  `uid` varchar(1000) NOT NULL,
  `size` int(20) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `phpix_uploads`
--


/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
