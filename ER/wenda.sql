SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES';

DROP SCHEMA IF EXISTS `wenda` ;
CREATE SCHEMA IF NOT EXISTS `wenda` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci ;
USE `wenda` ;

-- -----------------------------------------------------
-- Table `wenda`.`ask_user`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `wenda`.`ask_user` ;

CREATE TABLE IF NOT EXISTS `wenda`.`ask_user` (
  `uid` INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '用户id',
  `username` VARCHAR(45) NOT NULL DEFAULT '' COMMENT '用户名',
  `passwd` CHAR(32) NOT NULL DEFAULT '' COMMENT '密码',
  `ask` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '提问数',
  `answer` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '回答数',
  `accept` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '采纳数',
  `point` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '金币',
  `exp` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '经验值',
  `face` VARCHAR(60) NOT NULL DEFAULT '' COMMENT '头像',
  `restime` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '注册时间',
  `logintime` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '登录时间',
  `loginip` VARCHAR(45) NOT NULL DEFAULT 0 COMMENT '登录ip',
  `lock` TINYINT UNSIGNED NOT NULL DEFAULT 0 COMMENT '0状态没有被锁定     1  已经锁定',
  PRIMARY KEY (`uid`))
ENGINE = MyISAM
COMMENT = '前台用户表';


-- -----------------------------------------------------
-- Table `wenda`.`ask_admin`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `wenda`.`ask_admin` ;

CREATE TABLE IF NOT EXISTS `wenda`.`ask_admin` (
  `aid` INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '管理员id',
  `username` VARCHAR(45) NOT NULL DEFAULT '' COMMENT '管理员名',
  `passwd` CHAR(32) NOT NULL DEFAULT '' COMMENT '管理员密码',
  `logintime` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '登录时间',
  `loginip` VARCHAR(45) NOT NULL DEFAULT '' COMMENT '登录ip',
  `lock` TINYINT UNSIGNED NOT NULL DEFAULT 0 COMMENT '0 没有锁定  1代表锁定',
  PRIMARY KEY (`aid`))
ENGINE = MyISAM
COMMENT = '后台用户';


-- -----------------------------------------------------
-- Table `wenda`.`ask_category`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `wenda`.`ask_category` ;

CREATE TABLE IF NOT EXISTS `wenda`.`ask_category` (
  `cid` INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '分类ID',
  `title` VARCHAR(45) NOT NULL DEFAULT '' COMMENT '分类名称',
  `pid` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '父级ID',
  PRIMARY KEY (`cid`))
ENGINE = MyISAM
COMMENT = '分类 表';


-- -----------------------------------------------------
-- Table `wenda`.`ask_ask`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `wenda`.`ask_ask` ;

CREATE TABLE IF NOT EXISTS `wenda`.`ask_ask` (
  `asid` INT UNSIGNED NOT NULL COMMENT '问题id',
  `content` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '问题内容',
  `time` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '问题时间',
  `reward` SMALLINT UNSIGNED NOT NULL DEFAULT 0 COMMENT '悬赏金额',
  `answer` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '回答人数',
  `solve` TINYINT UNSIGNED NOT NULL DEFAULT 0 COMMENT '是否被解决   0 没有被解决  1被解决',
  `uid` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '所属用户ID',
  `cid` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '所属分类ID',
  PRIMARY KEY (`asid`),
  INDEX `fk_ask_ask_ask_user1_idx` (`uid` ASC),
  INDEX `fk_ask_ask_ask_category1_idx` (`cid` ASC))
ENGINE = MyISAM
COMMENT = '问题详情';


-- -----------------------------------------------------
-- Table `wenda`.`ask_answer`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `wenda`.`ask_answer` ;

CREATE TABLE IF NOT EXISTS `wenda`.`ask_answer` (
  `anid` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `content` VARCHAR(45) NOT NULL DEFAULT '',
  `time` INT UNSIGNED NOT NULL DEFAULT 0,
  `accept` TINYINT UNSIGNED NOT NULL DEFAULT 0 COMMENT '0没有被采纳 1被采纳',
  `uid` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '所属用户ID',
  `asid` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '所属问题ID',
  PRIMARY KEY (`anid`),
  INDEX `fk_ask_answer_ask_user_idx` (`uid` ASC),
  INDEX `fk_ask_answer_ask_ask1_idx` (`asid` ASC))
ENGINE = MyISAM
COMMENT = '回答表';


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
