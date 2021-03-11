# Host: localhost  (Version: 5.7.26)
# Date: 2021-03-11 10:38:37
# Generator: MySQL-Front 5.3  (Build 4.234)

/*!40101 SET NAMES utf8 */;

#
# Structure for table "hd_admin"
#

DROP TABLE IF EXISTS `hd_admin`;
CREATE TABLE `hd_admin` (
  `aid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(20) NOT NULL DEFAULT '' COMMENT '用户名',
  `passwd` char(32) NOT NULL DEFAULT '' COMMENT '密码',
  `logintime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '登录时间',
  `loginip` char(20) NOT NULL DEFAULT '',
  `lock` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '0为没有锁定，1为锁定',
  PRIMARY KEY (`aid`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COMMENT='后台用户表';

#
# Data for table "hd_admin"
#

/*!40000 ALTER TABLE `hd_admin` DISABLE KEYS */;
INSERT INTO `hd_admin` VALUES (1,'admin','21232f297a57a5a743894a0e4a801fc3',1615428090,'0.0.0.0',0);
/*!40000 ALTER TABLE `hd_admin` ENABLE KEYS */;

#
# Structure for table "hd_answer"
#

DROP TABLE IF EXISTS `hd_answer`;
CREATE TABLE `hd_answer` (
  `anid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `content` varchar(255) NOT NULL DEFAULT '' COMMENT '回答内容',
  `time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '回答时间',
  `accept` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '0为没有被采纳，1为已经采纳',
  `uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '所属用户ID',
  `asid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '所属问题ID',
  PRIMARY KEY (`anid`),
  KEY `fk_hd_answer_hd_user_idx` (`uid`),
  KEY `fk_hd_answer_hd_ask1_idx` (`asid`)
) ENGINE=MyISAM AUTO_INCREMENT=35 DEFAULT CHARSET=utf8 COMMENT='回答表';

#
# Data for table "hd_answer"
#

/*!40000 ALTER TABLE `hd_answer` DISABLE KEYS */;
INSERT INTO `hd_answer` VALUES (1,'www.google.com',1615305611,0,0,1),(2,'www.google.com',1615305611,0,2,1),(3,'www.google.com',1615305611,0,2,1),(4,'www.google.com',1615305611,0,2,1),(5,'www.google.com',1615305611,0,2,1),(6,'www.google.com',1615305611,0,2,1),(7,'www.google.com',1615305611,1,2,1),(8,'非常的负责任！',1615305611,1,2,6),(11,'每一个人都可以独立完成！',1615305611,1,2,9),(15,'有开设远程班！',1615305611,0,2,8),(16,'有的！',1615305611,0,2,7),(17,'有的！',1615305611,0,2,4),(18,'有的！！马老师开发的！',1615305611,0,2,5),(19,'就业普遍在5000以上吧，可以去官网去看下。',1615305611,0,2,3),(20,'讲啊，而且前端学完之后，学员作品都十分强大！',1615305611,0,2,24),(21,'当然好找了，有些没有毕业就被就业单位招走了。',1615305611,1,2,21),(22,'住宿挺方便，离上课也不远，而且房租挺便宜的。',1615305611,0,2,16),(23,'当然可以，而且免费视频教程就有非常大的知识含量！',1615305611,0,2,15),(24,'讲的非常的细致，而且进度把握的比较好。尤其是马老师。',1615305611,0,2,14),(25,'https://www.tsinghua.edu.cn/',1615305611,0,2,23),(26,'https://www.tsinghua.edu.cn/',1615305611,0,2,23),(27,'https://www.tsinghua.edu.cn/',1615305611,0,2,22),(28,'小班制，绝不做大锅饭！',1615305611,0,2,20),(29,'没有见过比老师还要负责任的老师。',1615305611,0,2,19),(30,'有的，而且讲的非常之细！',1615305611,0,2,18),(31,'可以办理0学费入学！',1615305611,0,2,17),(32,'有的，而且有好的整站开发的实战视频，可以到https://www.tsinghua.edu.cn/看一下。',1615305611,0,1,13),(33,'未来三天首府晴或多云，最高气温8度，最低气温-2度。',1615382190,0,3,25),(34,'没有！！！！！！',1615426150,0,3,4);
/*!40000 ALTER TABLE `hd_answer` ENABLE KEYS */;

#
# Structure for table "hd_ask"
#

DROP TABLE IF EXISTS `hd_ask`;
CREATE TABLE `hd_ask` (
  `asid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `content` varchar(255) NOT NULL DEFAULT '' COMMENT '提问内容',
  `time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '提问时间',
  `solve` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '0为没有解决，1为已经解决',
  `answer` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '回答数',
  `reward` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT '悬赏金币',
  `uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '所属用户ID',
  `cid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '所属分类ID',
  PRIMARY KEY (`asid`),
  KEY `fk_hd_ask_hd_user1_idx` (`uid`),
  KEY `fk_hd_ask_hd_category1_idx` (`cid`)
) ENGINE=MyISAM AUTO_INCREMENT=26 DEFAULT CHARSET=utf8 COMMENT='提问表';

#
# Data for table "hd_ask"
#

/*!40000 ALTER TABLE `hd_ask` DISABLE KEYS */;
INSERT INTO `hd_ask` VALUES (1,'google的网址是什么？',1615305611,1,7,30,1,206),(3,'清华大学就业怎么样？',1615305611,0,1,100,1,1),(4,'清华大学有实战视频吗？',1615305611,0,2,0,1,1),(5,'清华大学有自主研发的框架吗？',1615305611,0,1,30,1,1),(6,'清华大学的老师负责任吗？',1615305611,1,1,30,1,1),(7,'清华大学有实体培训班吗？',1615305611,0,1,20,1,1),(8,'清华大学有远程班吗？',1615305611,0,1,0,1,1),(9,'在清华大学培训完，独立可以开发出项目吗？',1615305611,1,1,5,1,1),(13,'清华大学有免费高清视频教程吗？',1615305611,0,1,50,2,206),(14,'清华大学老师讲的怎么样呀？',1615305611,0,1,10,1,206),(15,'到清华大学可以学到真正的东西吗？',1615305611,0,1,20,1,20),(16,'去清华大学学习住宿方便吗？',1615305611,0,1,20,1,206),(17,'清华大学可以0学费入学吗？',1615305611,0,1,50,1,206),(18,'清华大学有基础视频教程吗？',1615305611,0,1,50,1,206),(19,'清华大学的老师负责任吗？',1615305611,0,1,50,1,206),(20,'清华大学是小班制吗？',1615305611,0,1,30,1,206),(21,'从清华大学学完之后工作好找吗？',1615305611,1,1,15,1,206),(22,'清华大学的网址是什么？',1615305611,0,1,50,1,206),(23,'清华大学的论坛网址是什么？',1615305611,0,2,50,1,15),(24,'清华大学讲前端课程吗？',1615305611,0,1,10,1,15),(25,'今天天气怎么样？',1615381656,0,1,0,1,35);
/*!40000 ALTER TABLE `hd_ask` ENABLE KEYS */;

#
# Structure for table "hd_category"
#

DROP TABLE IF EXISTS `hd_category`;
CREATE TABLE `hd_category` (
  `cid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(20) NOT NULL DEFAULT '' COMMENT '分类名称',
  `pid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '父级ID',
  PRIMARY KEY (`cid`)
) ENGINE=MyISAM AUTO_INCREMENT=214 DEFAULT CHARSET=utf8 COMMENT='分类表';

#
# Data for table "hd_category"
#

/*!40000 ALTER TABLE `hd_category` DISABLE KEYS */;
INSERT INTO `hd_category` VALUES (1,'电脑/网络',0),(2,'手机/数码',0),(3,'生活',0),(4,'游戏',0),(5,'体育/运动',0),(7,'休闲爱好',0),(8,'文化/艺术',0),(9,'社会民生',0),(10,'教育/科学',0),(15,'电脑知识',1),(16,'互联网',1),(17,'操作系统',1),(18,'软件',1),(19,'硬件',1),(20,'编程开发',1),(21,'电脑安全',1),(22,'资源分享',1),(23,'笔记本电脑',1),(24,'手机/通讯',2),(25,'平板',2),(26,'MP3/MP4',2),(27,'手机品牌',2),(28,'其他数码',2),(29,'手机系统',2),(30,'照相机/摄像机',2),(31,'数码品牌',2),(32,'购物时尚',3),(33,'美容塑身',3),(34,'美食',3),(35,'生活知识',3),(36,'品牌服装',3),(37,'出行旅游',3),(38,'交通',3),(39,'购车保养',3),(40,'购房置业',3),(41,'房屋装饰',3),(42,'风水',3),(43,'家电用品',3),(44,'烹饪',3),(45,'网游',4),(46,'单机游戏',4),(47,'网页游戏',4),(48,'盛大游戏',4),(49,'网易',4),(50,'九城游戏',4),(51,'腾讯游戏',4),(52,'完美游戏',4),(53,'久游游戏',4),(54,'巨人游戏',4),(55,'金山游戏',4),(56,'网龙游戏',4),(57,'电视游戏',4),(58,'足球',5),(59,'篮球',5),(60,'体育明星',5),(61,'综合赛事',5),(62,'田径',5),(63,'跳水游泳',5),(64,'小球运动',5),(65,'赛车赛事',5),(66,'强身健体',5),(67,'运动品牌',5),(68,'电影电视',6),(69,'明星',6),(70,'音乐',6),(71,'动漫',6),(72,'星座',6),(73,'摄影摄像',7),(74,'收藏',7),(75,'宠物',7),(76,'脑筋急转弯',7),(77,'谜语',7),(78,'幽默搞笑',7),(79,'起名',7),(80,'园艺艺术',7),(81,'花鸟鱼虫',7),(82,'茶艺',7),(83,'国内外文学',8),(84,'美术',8),(85,'舞蹈',8),(86,'散文/小说',8),(87,'图书音像',8),(88,'器乐/声乐',8),(89,'小品相声',8),(90,'戏剧戏曲',8),(91,'时事政治',9),(92,'舆论',9),(93,'就业/职场',9),(94,'历史话题',9),(95,'军事国防',9),(96,'节日假期',9),(97,'民族风情',9),(98,'法律知识',9),(99,'宗教',9),(100,'礼仪',9),(101,'学习辅助',10),(102,'考研/考证',10),(103,'外语',10),(104,'菁菁校园',10),(105,'人文学',10),(106,'理工学',10),(107,'公务员',10),(108,'留学/出国',10),(109,'健康知识',11),(110,'孕育/家教',11),(111,'内科',11),(112,'心理健康',11),(113,'外科',11),(114,'妇产科',11),(115,'儿科',11),(116,'皮肤科',11),(117,'五官科',11),(118,'男科',11),(119,'美容整形',11),(120,'中医',11),(121,'药品',11),(122,'心血管科',11),(123,'传染科',11),(124,'其它疾病',11),(125,'健康体检',11),(126,'医院',11),(181,'电脑配置',15),(182,'电脑日常维护',15),(183,'上网问题',16),(184,'新浪',16),(185,'腾讯',16),(186,'Windows XP',17),(187,'windows 7',17),(188,'Windows Vista',17),(189,'Windows 8',17),(190,'办公软件',18),(191,'网络软件',18),(192,'图像处理',18),(193,'系统软件',18),(194,'多媒体软件',18),(195,'硬盘',19),(196,'显示设备',19),(197,'CPU',19),(198,'显卡',19),(199,'内存',19),(200,'主板',19),(201,'键盘/鼠标',19),(202,'HTML',20),(203,'DIV+CSS',20),(204,'JavaScript',20),(205,'jQuery',20),(206,'PHP',20),(207,'MySQL',20),(208,'Linux',20),(209,'Objective-C',20),(210,'Java',20),(211,'C/C++',20),(212,'网络防火墙',1);
/*!40000 ALTER TABLE `hd_category` ENABLE KEYS */;

#
# Structure for table "hd_user"
#

DROP TABLE IF EXISTS `hd_user`;
CREATE TABLE `hd_user` (
  `uid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(20) NOT NULL DEFAULT '' COMMENT '用户名',
  `passwd` char(32) NOT NULL DEFAULT '' COMMENT '密码',
  `ask` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '提问数',
  `answer` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '回答数',
  `accept` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '采纳数',
  `point` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '金币',
  `exp` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '经验',
  `restime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '注册时间',
  `logintime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '登录时间',
  `loginip` char(20) NOT NULL DEFAULT '' COMMENT '登录IP',
  `lock` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '0为没有锁定，1为锁定',
  `face` varchar(100) NOT NULL DEFAULT '' COMMENT 'å¤´åƒ',
  PRIMARY KEY (`uid`),
  UNIQUE KEY `username_UNIQUE` (`username`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COMMENT='前台用户表';

#
# Data for table "hd_user"
#

/*!40000 ALTER TABLE `hd_user` DISABLE KEYS */;
INSERT INTO `hd_user` VALUES (1,'admin','21232f297a57a5a743894a0e4a801fc3',25,1,0,629,65,0,1615428137,'0.0.0.0',0,'.//upload/4361615380533.jpg'),(2,'moyushi','21232f297a57a5a743894a0e4a801fc3',2,30,9,145,127,1377048366,1615380464,'0.0.0.0',0,'.//upload/7061615380477.jpg'),(3,'cn','21232f297a57a5a743894a0e4a801fc3',0,2,0,6,6,1377746148,1615381885,'0.0.0.0',0,'.//upload/9841615380562.jpg');
/*!40000 ALTER TABLE `hd_user` ENABLE KEYS */;
