/* 人人商城店员角色表 */
CREATE TABLE `ims_ewei_shop_saler_role` (
	`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`uniacid` INT UNSIGNED NOT NULL DEFAULT '0' COMMENT '公众号id',
	`name` VARCHAR(50) NOT NULL DEFAULT '0' COMMENT '角色名',
	`merchid` INT UNSIGNED NOT NULL DEFAULT '0' COMMENT '商户id',
	PRIMARY KEY (`id`)
)
COLLATE='utf8_general_ci'
;

/*---------------20181129--------------------*/
/*新增店员通过审核时间*/
ALTER TABLE `ims_ewei_shop_saler`
	CHANGE COLUMN `pass` `pass` TINYINT(1) NULL DEFAULT '0' COMMENT '审核状态' AFTER `salerrole`,
	ADD COLUMN `passtime` INT NULL DEFAULT '0' COMMENT '通过审核时间' AFTER `pass`;

ALTER TABLE `ims_ewei_shop_goods_qrcode_log`
	ADD COLUMN `openid` VARCHAR(100) NULL DEFAULT '0' COMMENT '用户openid' AFTER `uniacid`,
	ADD COLUMN `saleropenid` VARCHAR(100) NULL DEFAULT '0' COMMENT '导购openid' AFTER `openid`;

/*---------------20181130--------------------*/
/*店员角色新增3种权限*/
ALTER TABLE `ims_ewei_shop_saler_role`
	ADD COLUMN `shoppingguide` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '是否有导购功能' AFTER `merchid`,
	ADD COLUMN `storemanager` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '是否是店长' AFTER `shoppingguide`,
	ADD COLUMN `verify` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '是否可以核销' AFTER `storemanager`;

/*---------------20181203--------------------*/
/*新增分润记录表*/
CREATE TABLE `ims_ewei_shop_order_benefit_log` (
	`id` INT UNSIGNED NOT NULL,
	`uniacid` INT UNSIGNED NOT NULL,
	`openid` VARCHAR(100) NOT NULL,
	`storeid` INT NOT NULL,
	`storebenefit` DECIMAL(10,3) NOT NULL,
	`saleropenid` VARCHAR(100) NOT NULL,
	`salerbenefit` DECIMAL(10,2) NOT NULL,
	`agentid` INT NOT NULL,
	`agentbenefit` DECIMAL(10,2) NOT NULL,
	`team` VARCHAR(100) NOT NULL,
	`teambenefit` DECIMAL(10,2) NOT NULL,
	`createtime` DECIMAL(10,2) NOT NULL
)
COMMENT='订单分润记录'
COLLATE='utf8_general_ci'
;

ALTER TABLE `ims_ewei_shop_order_benefit_log`
	CHANGE COLUMN `storeid` `storeid` INT(11) NULL AFTER `openid`,
	CHANGE COLUMN `storebenefit` `storebenefit` DECIMAL(10,2) NULL DEFAULT '0' AFTER `storeid`,
	CHANGE COLUMN `saleropenid` `saleropenid` VARCHAR(100) NULL AFTER `storebenefit`,
	CHANGE COLUMN `salerbenefit` `salerbenefit` DECIMAL(10,2) NULL DEFAULT '0' AFTER `saleropenid`,
	CHANGE COLUMN `agentid` `agentid` INT(11) NULL AFTER `salerbenefit`,
	CHANGE COLUMN `agentbenefit` `agentbenefit` DECIMAL(10,2) NULL DEFAULT '0' AFTER `agentid`,
	CHANGE COLUMN `team` `team` VARCHAR(100) NULL AFTER `agentbenefit`,
	CHANGE COLUMN `teambenefit` `teambenefit` DECIMAL(10,2) NULL DEFAULT '0' AFTER `team`,
	CHANGE COLUMN `createtime` `createtime` INT NULL DEFAULT '0' AFTER `teambenefit`;
ALTER TABLE `ims_ewei_shop_order_benefit_log`
	CHANGE COLUMN `agentid` `agentopenid` INT(11) NULL DEFAULT NULL AFTER `salerbenefit`;
ALTER TABLE `ims_ewei_shop_order_benefit_log`
	CHANGE COLUMN `id` `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT FIRST,
	ADD PRIMARY KEY (`id`);

CREATE TABLE `ims_ewei_shop_coupon_benefit_log` (
	`id` INT UNSIGNED NOT NULL,
	`openid` VARCHAR(100) NOT NULL COMMENT 'sender openid',
	`couponid` INT NOT NULL COMMENT '优惠券id',
	`customeropenid` INT NOT NULL COMMENT '用户openid',
	`type` TINYINT(1) UNSIGNED NOT NULL COMMENT '返佣类型',
	`number` DECIMAL(10,2) NOT NULL COMMENT '返佣金额',
	`createtime` INT NOT NULL COMMENT '创建时间'
)
COMMENT='优惠券分润日志表'
COLLATE='utf8_general_ci'
;

ALTER TABLE `ims_ewei_shop_coupon_benefit_log`
	ALTER `type` DROP DEFAULT,
	ALTER `number` DROP DEFAULT,
	ALTER `createtime` DROP DEFAULT;
ALTER TABLE `ims_ewei_shop_coupon_benefit_log`
	ADD COLUMN `orderid` INT(11) NOT NULL COMMENT '订单id' AFTER `couponid`,
	CHANGE COLUMN `type` `type` TINYINT(1) UNSIGNED NULL COMMENT '返佣类型' AFTER `customeropenid`,
	CHANGE COLUMN `number` `number` DECIMAL(10,2) NULL COMMENT '返佣金额' AFTER `type`,
	CHANGE COLUMN `createtime` `createtime` INT(11) NULL COMMENT '创建时间' AFTER `number`;

ALTER TABLE `ims_ewei_shop_coupon_benefit_log`
	CHANGE COLUMN `id` `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT FIRST,
	ADD PRIMARY KEY (`id`);


/*---------------20181212--------------------*/
/*member表新增unionid*/
ALTER TABLE `ims_ewei_shop_member`
	ADD COLUMN `unionid` VARCHAR(50) NULL DEFAULT '' AFTER `openid`;
ALTER TABLE `ims_ewei_shop_member`
	ADD INDEX `idx_unionid` (`unionid`) USING BTREE;
ALTER TABLE `ims_ewei_shop_member`
	CHANGE COLUMN `openid` `openid` VARCHAR(50) NULL DEFAULT '' AFTER `agentid`,
	ADD COLUMN `ori_openid` VARCHAR(50) NULL DEFAULT '' COMMENT '公众号openid' AFTER `openid`,
	ADD INDEX `idx_oriopenid` (`ori_openid`) USING BTREE;

/*---------------20181212--------------------*/
/*coupon_data表新建索引*/
ALTER TABLE `ims_ewei_shop_coupon_data`
	ADD INDEX `idx_sender` (`sender`),
	ADD INDEX `idx_openid` (`openid`);
/*groups_goods表删除重复索引*/
ALTER TABLE `ims_ewei_shop_groups_goods`
	DROP INDEX `idx_type`;

CREATE TABLE `ims_ewei_shop_unionid_log` (
	`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`openid` VARCHAR(50) NULL COMMENT '原openid',
	`unionid` VARCHAR(50) NULL COMMENT '新unionid',
	`table` VARCHAR(50) NULL COMMENT '表名',
	`field` VARCHAR(50) NULL COMMENT '字段名',
	`create_time` VARCHAR(11) NOT NULL COMMENT '更新时间',
	PRIMARY KEY (`id`)
)
COLLATE='utf8_general_ci'
;

/*社交券*/
ALTER TABLE `ims_ewei_shop_coupon`
	ADD COLUMN `limitnewmember` TINYINT(1) UNSIGNED NULL DEFAULT '0' COMMENT '是否仅适用于新用户使用' AFTER `templateid`,
	ADD COLUMN `commissiontype` TINYINT(3) NULL DEFAULT '0' COMMENT '返佣方式' AFTER `limitnewmember`,
	ADD COLUMN `backcommission` DECIMAL(10,2) NULL DEFAULT '0.00' COMMENT '返佣金额' AFTER `commissiontype`,
	ADD COLUMN `autotarget` TINYINT(3) UNSIGNED NULL DEFAULT '0' COMMENT '自动发放对象' AFTER `backcommission`,
	ADD COLUMN `autonumber` TINYINT(5) UNSIGNED NULL DEFAULT '0' COMMENT '自动发放数量' AFTER `autotarget`,
	ADD COLUMN `isnewpass` TINYINT(1) UNSIGNED NULL DEFAULT '0' COMMENT '新注册并通过审核' AFTER `autonumber`,
	ADD COLUMN `finishorder` TINYINT(1) UNSIGNED NULL DEFAULT '0' COMMENT '完成订单' AFTER `isnewpass`;

ALTER TABLE `ims_ewei_shop_coupon_data`
	ADD COLUMN `sender` VARCHAR(255) NULL DEFAULT '' COMMENT '优惠券发放人openid' AFTER `uniacid`,
	ADD COLUMN `sharebatch` VARCHAR(100) NULL DEFAULT NULL COMMENT '优惠券批次' AFTER `textkey`;

/*---------------20181227--------------------*/
/*分润的代理设置 只有线上购买总部发货的情形*/
ALTER TABLE `ims_ewei_shop_goods`
	DROP COLUMN `agenthome`;

ALTER TABLE `ims_ewei_shop_member`
	ADD COLUMN `agent_area_code` VARCHAR(50) NULL DEFAULT NULL COMMENT '合伙人地区代码' AFTER `benefitbalance`;
ALTER TABLE `ims_ewei_shop_member`
	CHANGE COLUMN `agent_area_code` `agent_area_code` VARCHAR(50) NULL DEFAULT NULL COMMENT '代理商地区代码' AFTER `benefitbalance`;

/*---------------20190105--------------------*/
ALTER TABLE `ims_ewei_shop_coupon`
	ADD COLUMN `autogoodsid` VARCHAR(100) NULL DEFAULT '' COMMENT '购买指定商品送券';

ALTER TABLE `ims_ewei_shop_saler`
	ADD COLUMN `is_delete` TINYINT(1) NULL DEFAULT '0' COMMENT '是否已删除' AFTER `passtime`;

/*---------------20190107--------------------*/
ALTER TABLE `ims_ewei_shop_coupon_relation`
	ALTER `saleropenid` DROP DEFAULT,
	ALTER `parentopenid` DROP DEFAULT;

ALTER TABLE `ims_ewei_shop_order_benefit_log`
	ADD COLUMN `orderid` INT(10) UNSIGNED NOT NULL AFTER `uniacid`;

/*---------------20190108--------------------*/
ALTER TABLE `ims_ewei_shop_member`
	DROP INDEX `idx_openid`,
	ADD UNIQUE INDEX `idx_openid` (`openid`);

ALTER TABLE `ims_ewei_shop_coupon`
	ADD COLUMN `autotype` TINYINT(1) NULL DEFAULT '1' COMMENT '1：每订单；2：每个商品' AFTER `autogoodsid`;


ALTER TABLE `ims_ewei_shop_member_address`
	ADD COLUMN `province_code` VARCHAR(10) NULL DEFAULT '' AFTER `lat`,
	ADD COLUMN `city_code` VARCHAR(10) NULL DEFAULT '' AFTER `province_code`,
	ADD COLUMN `area_code` VARCHAR(10) NULL DEFAULT '' AFTER `city_code`;

ALTER TABLE `ims_ewei_shop_member`
	ADD COLUMN `agent_province_code` VARCHAR(50) NULL DEFAULT NULL COMMENT '代理商省代码' AFTER `benefitbalance`,
	ADD COLUMN `agent_city_code` VARCHAR(50) NULL DEFAULT NULL COMMENT '代理商市代码' AFTER `agent_province_code`,
    ADD COLUMN `agent_area_code` VARCHAR(50) NULL DEFAULT NULL COMMENT '代理商区域代码' AFTER `agent_city_code`;

/*---------------20190115--------------------*/
ALTER TABLE `ims_ewei_shop_merch_user`
	ADD COLUMN `benefitbalance` DECIMAL(10,2) NULL DEFAULT '0.00' COMMENT '分润余额' AFTER `iscreditmoney`;

/*---------------20190121--------------------*/
ALTER TABLE `ims_ewei_shop_merch_user`
	ADD COLUMN `agentid` INT NULL DEFAULT '0' COMMENT '代理id' AFTER `benefitbalance`;
ALTER TABLE `ims_ewei_shop_order_benefit_log`
	CHANGE COLUMN `agentopenid` `agentid` INT(11) NULL DEFAULT NULL AFTER `salerbenefit`;

/*---------------20190125--------------------*/
ALTER TABLE `ims_ewei_shop_member_message_template_type`
	CHANGE COLUMN `id` `id` INT(11) NOT NULL AUTO_INCREMENT FIRST,
	DROP INDEX `id`,
	ADD PRIMARY KEY (`id`) USING BTREE;
/*---------------20190129--------------------*/
ALTER TABLE `ims_ewei_shop_saler_role`
	ADD COLUMN `deliver` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '是否可以发货' AFTER `verify`;

/*---------------20190214--------------------*/
ALTER TABLE `ims_ewei_shop_goods_qrcode_log`
	ADD UNIQUE INDEX `qid_no` (`qid`, `no`);

/*---------------20190215--------------------*/
ALTER TABLE `ims_ewei_shop_merch_bill`
	ADD COLUMN `member_type` TINYINT(1) NOT NULL DEFAULT '1' COMMENT '用户类型 1：经销商；2：代理；3：用户' AFTER `isbillcredit`,
	ADD COLUMN `bill_type` TINYINT(1) NOT NULL DEFAULT '2' COMMENT '账单类型 1：积分；2：余额；3：分润' AFTER `member_type`;

ALTER TABLE `ims_ewei_shop_merch_user`
	ENGINE=InnoDB;
ALTER TABLE `ims_ewei_shop_merch_bill`
	ENGINE=InnoDB;
ALTER TABLE `ims_ewei_shop_store`
	ENGINE=InnoDB;
ALTER TABLE `ims_ewei_shop_member`
	ENGINE=InnoDB;

ALTER TABLE `ims_ewei_shop_merch_bill`
	ADD COLUMN `pid` INT NOT NULL DEFAULT '0' COMMENT 'id' AFTER `bill_type`;

/*---------------20190220--------------------*/
CREATE TABLE `ims_ewei_shop_agent_account` (
	`id` INT NOT NULL AUTO_INCREMENT,
	`uniacid` INT NOT NULL,
	`openid` VARCHAR(100) NOT NULL COMMENT 'openid',
	`agentid` INT NOT NULL DEFAULT '0' COMMENT '代理商id',
	`status` TINYINT(1) NOT NULL DEFAULT '1' COMMENT '状态',
	PRIMARY KEY (`id`)
)
COMMENT='代理商子账号表'
COLLATE='utf8_unicode_ci'
ENGINE=InnoDB
;
/*---------------20190222--------------------*/
ALTER TABLE `ims_ewei_shop_member`
	ADD COLUMN `agent_payopenid` VARCHAR(50) NULL DEFAULT NULL COMMENT '代理商收款openid' AFTER `agent_area_code`;

/*---------------20190225--------------------*/
INSERT INTO `kafei`.`ims_ewei_shop_member_message_template_type` (`name`, `typecode`, `templatename`, `typegroup`, `groupname`) VALUES ('零售商/代理增加子账号提醒', 'child_account_message', '业务处理通知', 'sale_system', '门店经销商代理');

/*---------------20190227--------------------*/
ALTER TABLE `ims_ewei_shop_order_benefit_log`
	ADD COLUMN `agent_plus` INT NULL DEFAULT '0' AFTER `agentbenefit`,
	ADD COLUMN `agent_plus_benefit` DECIMAL(10,2) NULL DEFAULT '0.00' AFTER `agent_plus`;

ALTER TABLE `ims_ewei_shop_member`
	CHANGE COLUMN `openid` `openid` VARCHAR(50) NULL DEFAULT '' COLLATE 'utf8_general_ci' AFTER `agentid`;
ALTER TABLE `ims_ewei_shop_agent_account`
	CHANGE COLUMN `openid` `openid` VARCHAR(100) NOT NULL COMMENT 'openid' COLLATE 'utf8_general_ci' AFTER `uniacid`;

ALTER TABLE `ims_ewei_shop_member`
	CHANGE COLUMN `ori_openid` `ori_openid` VARCHAR(50) NULL DEFAULT NULL COMMENT '公众号openid' AFTER `openid`;

/*---------------20190302--------------------*/
ALTER TABLE `ims_ewei_shop_agent_account`
	ADD UNIQUE INDEX `openid_agentid` (`openid`, `agentid`) USING BTREE;

ALTER TABLE `ims_ewei_shop_merch_user`
	ADD COLUMN `agent_account_id` INT(11) NULL DEFAULT '0' COMMENT '代理子账号id' AFTER `agentid`;

/*---------------20190312 排行榜数据表--------------------*/
CREATE TABLE `ims_ewei_shop_rank` (
	`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`uniacid` INT UNSIGNED NULL,
	`name` VARCHAR(255) NOT NULL COMMENT '榜单名称',
	`cateid` INT NOT NULL DEFAULT '0' COMMENT '榜单分类',
	`data_target` TINYINT(2) NOT NULL DEFAULT '1' COMMENT '数据对象',
	`data_type` TINYINT(2) NOT NULL DEFAULT '1' COMMENT '榜单指标',
	`range` TINYINT(2) NOT NULL DEFAULT '1' COMMENT '适用范围',
	`starttime` VARCHAR(20) NULL DEFAULT '0' COMMENT '开始时间',
	`endtime` VARCHAR(20) NULL DEFAULT '0' COMMENT '结束时间',
	`long_time` TINYINT(1) NULL DEFAULT '1' COMMENT '是否长期开启',
	`status` TINYINT(1) NULL DEFAULT '1' COMMENT '是否启用',
	`is_show` TINYINT(1) NULL DEFAULT '1' COMMENT '是否在榜单中心显示',
	`createtime` VARCHAR(20) NOT NULL DEFAULT '0' COMMENT '创建时间',
	PRIMARY KEY (`id`),
	INDEX `name` (`name`)
)
COMMENT='排行榜'
COLLATE='utf8_unicode_ci'
;

ALTER TABLE `ims_ewei_shop_rank`
	ADD COLUMN `displayorder` INT NULL DEFAULT '99' AFTER `is_show`;
ALTER TABLE `ims_ewei_shop_rank`
	ADD COLUMN `range_id` INT NOT NULL DEFAULT '0' COMMENT '区域id' AFTER `range`;

/*---------------20190315 排行榜数据表--------------------*/
ALTER TABLE `ims_ewei_shop_rank`
	ADD COLUMN `goodsid` INT(11) NOT NULL DEFAULT '0' COMMENT '关联商品' AFTER `cateid`;

/*---------------20190316 --------------------*/
ALTER TABLE `ims_ewei_shop_goods_qrcode`
	ADD COLUMN `batch_log` TINYINT(1) NULL DEFAULT '0' COMMENT '是否已批量生成log记录' AFTER `finishmake`;
ALTER TABLE `ims_ewei_shop_goods_qrcode`
	ADD COLUMN `createtime` VARCHAR(20) NULL DEFAULT '0' COMMENT '创建时间' AFTER `batch_log`;
ALTER TABLE `ims_ewei_shop_goods_qrcode_log`
	ADD COLUMN `updatetime` INT(11) UNSIGNED NOT NULL DEFAULT '0' COMMENT '更新时间' AFTER `createtime`;

/*---------------20190320 --------------------*/
ALTER TABLE `ims_ewei_shop_merch_user`
	ADD COLUMN `agent_account_id` INT(11) NULL DEFAULT '0' COMMENT '代理子账号id' AFTER `agentid`;

