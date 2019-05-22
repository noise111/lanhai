<?php
/**
 * 小区秘书模块
 *
 * [蓝牛科技] Copyright (c) 2013 xqms.cn
 */
/**
 * 后台小区活动
 */
global $_W, $_GPC;
/**
 * 后台菜单初始化
 */
xqmenu();
/**
 * 后台菜单的操作权限初始化
 */
xqmenuop();
/**
 * 底部菜单初始化
 */
footer();
/**
 * 手机端管理菜单初始化
 */
appmenu();
/**
 * 主页导航初始化
 */
nav();
/**
 * 住户中心初始化
 */
housecenter();
/**
 * 首次进入小区开启注册字段
 */
regfield();
//if (empty(set('b4'))) {
//    pdo_insert('xcommunity_setting', array('uniacid' => $_W['uniacid'], 'xqkey' => 'b4', 'value' => 1));
//}
//if (empty(set('b8'))) {
//    pdo_insert('xcommunity_setting', array('uniacid' => $_W['uniacid'], 'xqkey' => 'b8', 'value' => 1));
//}
//if (empty(set('b12'))) {
//    pdo_insert('xcommunity_setting', array('uniacid' => $_W['uniacid'], 'xqkey' => 'b12', 'value' => 1));
//}
//if (empty(set('b16'))) {
//    pdo_insert('xcommunity_setting', array('uniacid' => $_W['uniacid'], 'xqkey' => 'b16', 'value' => 1));
//}
//if (empty(set('b20'))) {
//    pdo_insert('xcommunity_setting', array('uniacid' => $_W['uniacid'], 'xqkey' => 'b20', 'value' => 1));
//}
//if (empty(set('p55'))) {
//    pdo_insert('xcommunity_setting', array('uniacid' => $_W['uniacid'], 'xqkey' => 'p55', 'value' => 1));
//    if (empty(set('p38'))) {
//        pdo_insert('xcommunity_setting', array('uniacid' => $_W['uniacid'], 'xqkey' => 'p38', 'value' => 1));
//        pdo_insert('xcommunity_setting', array('uniacid' => $_W['uniacid'], 'xqkey' => 'p39', 'value' => '栋'));
//    }
//    if (empty(set('p40'))) {
//        pdo_insert('xcommunity_setting', array('uniacid' => $_W['uniacid'], 'xqkey' => 'p40', 'value' => 1));
//        pdo_insert('xcommunity_setting', array('uniacid' => $_W['uniacid'], 'xqkey' => 'p41', 'value' => '单元'));
//    }
//    if (empty(set('p42'))) {
//        pdo_insert('xcommunity_setting', array('uniacid' => $_W['uniacid'], 'xqkey' => 'p42', 'value' => 1));
//        pdo_insert('xcommunity_setting', array('uniacid' => $_W['uniacid'], 'xqkey' => 'p43', 'value' => '室'));
//    }
//
//}



//导入后台菜单数据
$GLOBALS['frames'] = $this->NavMenu();
include $this->template('web/core/manage');