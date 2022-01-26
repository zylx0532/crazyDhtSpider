-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- 主机： localhost
-- 生成日期： 2022-01-26 18:28:56
-- 服务器版本： 5.6.50-log
-- PHP 版本： 8.0.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- 数据库： `dht`
--

-- --------------------------------------------------------

--
-- 表的结构 `bt`
--

CREATE TABLE `bt` (
  `id` int(11) NOT NULL,
  `name` varchar(500) NOT NULL COMMENT '名称',
  `keywords` varchar(250) NOT NULL COMMENT '关键词',
  `length` bigint(20) NOT NULL DEFAULT '0' COMMENT '文件大小',
  `piece_length` int(11) NOT NULL DEFAULT '0' COMMENT '种子大小',
  `infohash` char(40) NOT NULL COMMENT '种子哈希值',
  `files` text NOT NULL COMMENT '文件列表',
  `hits` int(11) NOT NULL DEFAULT '0' COMMENT '点击量',
  `hot` int(11) NOT NULL DEFAULT '1' COMMENT '热度',
  `time` datetime NOT NULL COMMENT '收录时间',
  `lasttime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '最后下载时间'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `history`
--

CREATE TABLE `history` (
  `infohash` char(40) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- 转储表的索引
--

--
-- 表的索引 `bt`
--
ALTER TABLE `bt`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `infohash` (`infohash`) USING BTREE,
  ADD KEY `hot` (`hot`),
  ADD KEY `time` (`time`),
  ADD KEY `hits` (`hits`);

--
-- 表的索引 `history`
--
ALTER TABLE `history`
  ADD PRIMARY KEY (`infohash`),
  ADD UNIQUE KEY `infohash` (`infohash`) USING BTREE;

--
-- 在导出的表使用AUTO_INCREMENT
--

--
-- 使用表AUTO_INCREMENT `bt`
--
ALTER TABLE `bt`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
