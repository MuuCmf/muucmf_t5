-- -----------------------------
-- 表结构 `muucmf_articles`
-- -----------------------------
CREATE TABLE IF NOT EXISTS `muucmf_articles` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `uid` int(11) NOT NULL,
  `title` varchar(50) NOT NULL COMMENT '标题',
  `keywords` varchar(255) NOT NULL COMMENT '关键字，多个用,分割',
  `description` varchar(200) NOT NULL COMMENT '描述',
  `category` int(11) NOT NULL COMMENT '分类',
  `status` tinyint(2) NOT NULL COMMENT '状态',
  `reason` varchar(100) NOT NULL COMMENT '审核失败原因',
  `sort` int(5) NOT NULL COMMENT '排序',
  `position` int(4) NOT NULL COMMENT '定位，展示位',
  `cover` int(11) NOT NULL COMMENT '封面',
  `view` int(10) NOT NULL COMMENT '阅读量',
  `comment` int(10) NOT NULL COMMENT '评论量',
  `collection` int(10) NOT NULL COMMENT '收藏量',
  `source` varchar(200) NOT NULL COMMENT '来源url',
  `create_time` int(11) NOT NULL,
  `update_time` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=16 DEFAULT CHARSET=utf8 COMMENT='文章';


-- -----------------------------
-- 表结构 `muucmf_articles_category`
-- -----------------------------
CREATE TABLE IF NOT EXISTS `muucmf_articles_category` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `title` varchar(20) NOT NULL,
  `pid` int(11) NOT NULL,
  `can_post` tinyint(4) NOT NULL COMMENT '前台可投稿',
  `need_audit` tinyint(4) NOT NULL COMMENT '前台投稿是否需要审核',
  `sort` tinyint(4) NOT NULL,
  `status` tinyint(4) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COMMENT='文章分类';


-- -----------------------------
-- 表结构 `muucmf_articles_detail`
-- -----------------------------
CREATE TABLE IF NOT EXISTS `muucmf_articles_detail` (
  `articles_id` int(11) NOT NULL,
  `content` text NOT NULL COMMENT '内容',
  `template` varchar(50) NOT NULL COMMENT '模板'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='文章详情';

-- -----------------------------
-- 表内记录 `muucmf_articles_category`
-- -----------------------------
INSERT INTO `muucmf_articles_category` VALUES ('1', '早期创业', '0', '1', '1', '1', '1');
INSERT INTO `muucmf_articles_category` VALUES ('2', '独角兽', '0', '1', '1', '3', '1');
INSERT INTO `muucmf_articles_category` VALUES ('3', '投融资', '0', '1', '1', '2', '1');
INSERT INTO `muucmf_articles_category` VALUES ('4', '火木动态', '0', '1', '1', '0', '1');
INSERT INTO `muucmf_articles_category` VALUES ('5', '锐观察', '0', '1', '1', '4', '1');
INSERT INTO `muucmf_articles_category` VALUES ('6', '技术创新', '0', '1', '1', '5', '1');
