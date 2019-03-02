-- -----------------------------
-- 表结构 `muucmf_articles`
-- -----------------------------
CREATE TABLE IF NOT EXISTS `muucmf_articles` (
  `id` int(11) unsigned NOT NULL COMMENT '自增ID',
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
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='文章';


-- -----------------------------
-- 表结构 `muucmf_articles_category`
-- -----------------------------
CREATE TABLE IF NOT EXISTS `muucmf_articles_category` (
  `id` int(11) unsigned NOT NULL COMMENT '自增ID',
  `title` varchar(20) NOT NULL,
  `pid` int(11) NOT NULL,
  `can_post` tinyint(4) NOT NULL COMMENT '前台可投稿',
  `need_audit` tinyint(4) NOT NULL COMMENT '前台投稿是否需要审核',
  `sort` tinyint(4) unsigned NOT NULL DEFAULT '0',
  `status` tinyint(4) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='文章分类';


-- -----------------------------
-- 表结构 `muucmf_articles_detail`
-- -----------------------------
CREATE TABLE IF NOT EXISTS `muucmf_articles_detail` (
  `articles_id` int(11) NOT NULL,
  `content` text NOT NULL COMMENT '内容',
  `template` varchar(50) NOT NULL COMMENT '模板',
  PRIMARY KEY (`articles_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='文章详情';

-- -----------------------------
-- 表内记录 `muucmf_articles_category`
-- -----------------------------
INSERT INTO `muucmf_articles_category` VALUES ('8', 'test2', '0', '1', '1', '2', '-1');
-- -----------------------------
-- 表内记录 `muucmf_articles_detail`
-- -----------------------------
INSERT INTO `muucmf_articles_detail` VALUES ('35', '<p>9 月 28 日，微信发文宣布「功能直达」能力开放，这一能力将让用户更便捷地找到所需服务，拉近商家与用户的距离。</p><p>接下来，知晓程序（zxcx0101）将为你做一个微信「功能直达」的全面剖析。</p><h3>「功能直达」是什么？</h3><p>早在 2017 年 8 月左右，微信官方就邀请了部分小程序内测「功能直达」；3 月 2 日，小程序「功能直达」再度扩大内测范围，保持着几乎每个月都有微小调整；时隔一年后，这项功能终于正式上线。  <br></p><p><br></p><p>微信官方对「功能直达」的定义是「满足微信用户快捷找到功能的搜索产品」。简单来说，当用户在微信「搜一搜」或小程序搜索框中搜索特定关键词（例如机票、电影名称等），搜索页面将呈现相关服务的小程序，点击搜索结果，可直达小程序相关服务页面。以搜索「日历」为例，将呈现由「朝夕万年历」提供的日历查询服务，点击后即可进入「朝夕万年历」小程序。  <br></p><p><br></p><p><br></p><h3>「功能直达」有用吗？</h3><p>「功能直达」听上去很强，但它对于小程序的引流效果到底如何呢？知晓程序也为此采访了两个参与了「功能直达」内测的小程序——「小睡眠」和「递名片」。</p><p>小睡眠：</p><blockquote><p>「小睡眠」小程序在开通「功能直达」内测后，表示「功能直达」每天带来的新增曝光量在 3000 左右。</p>而曝光量大小和设置服务有关联性，小睡眠设置的服务是「睡眠」，这个词不是大热词，「小睡眠」开发团队认为像机票、火车票之类的曝光量应该会非常大。</blockquote>', '');
