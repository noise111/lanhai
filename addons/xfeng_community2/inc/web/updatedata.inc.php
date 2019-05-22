<?php
/**
 * Created by njlanniu.com.
 * User: 蓝牛科技
 * Time: 2017/10/22 下午3:51
 */
global $_W, $_GPC;
set_time_limit(0);
$ops = array('list', 'reset', 'sql', 'menu');
$op = !empty($_GPC['op']) ? $_GPC['op'] : 'list';
if (!in_array($op, $ops)) {
    message('该方法不存在(op:' . $op . ')');
}
// 操作员
$user = util::xquser($_W['uid']);
if ($user && $user['menu_ops']) {
    $menu_opss = explode(',', $user['menu_ops']);
}
/**
 * 数据维护
 */
if ($op == 'list') {
    /**
     * 一键清空房屋
     */
    if (checksubmit('update_room')) {
        pdo_query("delete from" . tablename('xcommunity_member_room') . " where uniacid = :uniacid", array(':uniacid' => $_W['uniacid']));
        pdo_query("delete from" . tablename('xcommunity_member_log') . " where uniacid = :uniacid", array(':uniacid' => $_W['uniacid']));
        itoast('操作成功', referer(), 'success', true);
    }
    /**
     * 一键清空区域
     */
    if (checksubmit('update_area')) {
        pdo_query("delete from" . tablename('xcommunity_area') . " where uniacid = :uniacid", array(':uniacid' => $_W['uniacid']));
        itoast('操作成功', referer(), 'success', true);
    }
    /**
     * 一键清空楼宇单元
     */
    if (checksubmit('update_build')) {
        pdo_query("delete from" . tablename('xcommunity_build') . " where uniacid = :uniacid", array(':uniacid' => $_W['uniacid']));
        pdo_query("delete from" . tablename('xcommunity_unit') . " where uniacid = :uniacid", array(':uniacid' => $_W['uniacid']));
        itoast('操作成功', referer(), 'success', true);
    }
    /**
     * 优化房屋数据
     */
    if (checksubmit('update_house')) {
        $rooms = pdo_getall('xcommunity_member_room', array('uniacid' => $_W['uniacid']), array('id', 'area', 'build', 'unit', 'regionid',));
        foreach ($rooms as $k => $v) {
            $condition = ' uniacid = :uniacid and regionid=:regionid and buildtitle=:buildtitle ';
            $params[':uniacid'] = $_W['uniacid'];
            $params[':regionid'] = $v['regionid'];
            $params[':buildtitle'] = trim($v['build']);
            if (!empty($v['area'])) {
                $areaid = pdo_getcolumn('xcommunity_area', array('uniacid' => $_W['uniacid'], 'regionid' => $v['regionid'], 'title' => trim($v['area'])), 'id');
                if (empty($areaid)) {
                    pdo_insert('xcommunity_area', array('uniacid' => $_W['uniacid'], 'regionid' => $v['regionid'], 'title' => trim($v['area']), 'uid' => $_W['uid']));
                    $areaid = pdo_insertid();
                }
                $condition .= " and areaid=:areaid";
                $params[':areaid'] = $areaid;
            } else {
                $condition .= " and areaid=:areaid";
                $params[':areaid'] = '';
            }
            $build = pdo_fetch("select id from" . tablename('xcommunity_build') . " where $condition", $params);
            $buildid = $build['id'];
            if (empty($buildid)) {
                $data = array(
                    'uniacid'    => $_W['uniacid'],
                    'regionid'   => $v['regionid'],
                    'buildtitle' => trim($v['build']),
                    'uid'        => $_W['uid'],
                );
                if ($v['area']) {
                    $data['areaid'] = $areaid;
                }
                pdo_insert('xcommunity_build', $data);
                $buildid = pdo_insertid();
            }
            $unitid = pdo_getcolumn('xcommunity_unit', array('uniacid' => $_W['uniacid'], 'buildid' => $buildid, 'unit' => trim($v['unit']), 'regionid' => $v['regionid']), 'id');
            if (empty($unitid)) {
                pdo_insert('xcommunity_unit', array('uniacid' => $_W['uniacid'], 'buildid' => $buildid, 'uid' => $_W['uid'], 'unit' => trim($v['unit']), 'regionid' => $v['regionid']));
                $unitid = pdo_insertid();
            }
            if (empty($v['area'])) {
                $areaid = '';
            }
            pdo_update('xcommunity_member_room', array('areaid' => $areaid, 'buildid' => $buildid, 'unitid' => $unitid), array('id' => $v['id']));
        }
        itoast('操作成功', referer(), 'success', true);
    }
}
/**
 * 菜单重置
 */
if ($op == 'reset') {
    /**
     * 微信端菜单重置
     */
    if (checksubmit('update_nav')) {
        pdo_query("delete from" . tablename('xcommunity_nav') . "where uniacid=:uniacid", array(':uniacid' => $_W['uniacid']));
        itoast('操作成功', referer(), 'success', true);
    }
    /**
     * 微信端菜单生成
     */
    if (checksubmit('update_nav_make')) {
        nav();
        itoast('操作成功', referer(), 'success', true);
    }
    /**
     * 住户中心菜单重置
     */
    if (checksubmit('update_housenav')) {
        pdo_query("delete from" . tablename('xcommunity_housecenter') . "where uniacid=:uniacid", array(':uniacid' => $_W['uniacid']));
        itoast('操作成功', referer(), 'success', true);
    }
    /**
     * 住户中心菜单生成
     */
    if (checksubmit('update_housenav_make')) {
        housecenter();
        itoast('操作成功', referer(), 'success', true);
    }
    /**
     * 手机端管理菜单重置
     */
    if (checksubmit('update_xqappmenu')) {
        pdo_query("delete from" . tablename('xcommunity_appmenu') . "where uniacid=:uniacid", array(':uniacid' => $_W['uniacid']));
        itoast('操作成功', referer(), 'success', true);
    }
    /**
     * 生成手机管理端菜单
     */
    if (checksubmit('update_appmenu')) {
        appmenu();
        itoast('操作成功', referer(), 'success', true);
    }
    /**
     * 底部菜单重置
     */
    if (checksubmit('update_xqfootermenu')) {
        $foots = pdo_getall('xcommunity_footmenu', array('uniacid' => $_W['uniacid']), array('id'));
        $foots_ids = _array_column($foots, 'id');
        pdo_delete('xcommunity_footmenu_region', array('fid' => $foots_ids));
        pdo_query("delete from" . tablename('xcommunity_footmenu') . "where uniacid=:uniacid", array(':uniacid' => $_W['uniacid']));
        itoast('操作成功', referer(), 'success', true);
    }
    /**
     * 底部菜单生成
     */
    if (checksubmit('update_footermenu')) {
        footer();
        itoast('操作成功', referer(), 'success', true);
    }
    /**
     * 角色操作权限的菜单重置
     */
    if (checksubmit('update_role')) {
        if (pdo_tableexists('xcommunity_menu_operation')) {
            pdo_query("delete from" . tablename('xcommunity_menu_operation'));
            itoast('操作成功', referer(), 'success', true);
        }
    }
    /**
     * 角色操作权限的菜单生成
     */
    if (checksubmit('update_role_make')) {
        if (pdo_tableexists('xcommunity_menu_operation')) {
            xqmenuop();
            itoast('操作成功', referer(), 'success', true);
        }
    }
    /**
     * 重置主页排版
     */
    if (checksubmit('update_xqsort')) {
        pdo_query("delete from" . tablename('xcommunity_home') . "where uniacid=:uniacid", array(':uniacid' => $_W['uniacid']));
        itoast('操作成功', referer(), 'success', true);
    }
}
/**
 * 修复字段、数据
 */
if ($op == 'sql') {
    /**
     * 修复字段
     */
    if (checksubmit('update_field')) {
        if (!pdo_fieldexists('xcommunity_menu', 'cate')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_menu') . " ADD `cate` int(10) default 0;");
        }
        if (!pdo_fieldexists('xcommunity_menu', 'icon')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_menu') . " ADD `icon` varchar(50) ;");
        }
        if (!pdo_fieldexists('xcommunity_member_room', 'regionid')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_member_room') . " ADD `regionid` int(10) ;");
        }
        if (!pdo_fieldexists('xcommunity_member_room', 'uniacid')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_member_room') . " ADD `uniacid` int(10) ;");
        }
        if (!pdo_fieldexists('xcommunity_member_room', 'square')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_member_room') . " ADD `square` varchar(20) ;");
        }
        if (!pdo_fieldexists('xcommunity_member', 'license')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_member') . " ADD `license` varchar(20) ;");
        }
        if (!pdo_fieldexists('xcommunity_member_address', 'uid')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_member_address') . " ADD `uid` int(11) ;");
        }
        if (!pdo_fieldexists('xcommunity_member_family', 'logid')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_member_family') . " ADD `logid` int(11) ;");
        }
        if (!pdo_fieldexists('xcommunity_order', 'credit')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_order') . " ADD `credit` int(11) ;");
        }
        if (!pdo_fieldexists('xcommunity_member', 'visit')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_member') . " ADD `visit` int(1) default 0;");
        }
        if (!pdo_fieldexists('xcommunity_member_log', 'status')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_member_log') . " ADD `status` int(1) default 0;");
        }
        if (!pdo_fieldexists('xcommunity_activity', 'num')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_activity') . " ADD `num` int(11) default 0;");
        }
        if (!pdo_fieldexists('xcommunity_build', 'areaid')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_build') . " ADD `areaid` int(11) default 0;");
        }
        if (!pdo_fieldexists('xcommunity_report', 'remark')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_report') . " ADD `remark` varchar(255) default 0;");
        }
        if (!pdo_fieldexists('xcommunity_report', 'categoryid')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_report') . " ADD `categoryid` int(1) default 0;");
        }
        if (!pdo_fieldexists('xcommunity_plugin_adv', 'num')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_plugin_adv') . " ADD `num` int(11) default 0;");
        }
        if (!pdo_fieldexists('xcommunity_report_log', 'categoryid')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_report_log') . " ADD `categoryid` int(11) default 0;");
        }
        if (!pdo_fieldexists('xcommunity_report_log', 'remark')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_report_log') . " ADD `remark` varchar(255) default 0;");
        }
        if (!pdo_fieldexists('xcommunity_phone', 'address')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_phone') . " ADD `address` varchar(100) default 0;");
        }
        if (!pdo_fieldexists('xcommunity_phone', 'introduction')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_phone') . " ADD `introduction` varchar(255) default 0;");
        }
        if (!pdo_fieldexists('xcommunity_report', 'images')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_report') . " ADD `images` text ;");
        }
        if (!pdo_fieldexists('xcommunity_phone', 'cid')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_phone') . " ADD `cid` int(11) default 0;");
        }
        if (!pdo_fieldexists('xcommunity_houselease', 'images')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_houselease') . " ADD `images` text ;");
        }
        if (!pdo_fieldexists('xcommunity_fled', 'images')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_fled') . " ADD `images` text ;");
        }
        if (!pdo_fieldexists('xcommunity_fled', 'type')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_fled') . " ADD `type` int(1) default 0;");
        }
        if (!pdo_fieldexists('xcommunity_plugin_adv', 'num')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_plugin_adv') . " ADD `num` int(11) default 0;");
        }
        if (!pdo_fieldexists('qrcode', 'enable')) {
            pdo_query("ALTER TABLE " . tablename('qrcode') . " ADD `enable` int(1) default 0;");
        }
        if (!pdo_fieldexists('xcommunity_member_address', 'realname')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_member_address') . " ADD `realname` varchar(50) ;");
        }
        if (!pdo_fieldexists('xcommunity_member_address', 'mobile')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_member_address') . " ADD `mobile` varchar(13) ;");
        }
        if (!pdo_fieldexists('xcommunity_member_address', 'city')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_member_address') . " ADD `city` varchar(100) ;");
        }
        if (!pdo_fieldexists('xcommunity_carpool', 'tj_position')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_carpool') . " ADD `tj_position` varchar(100) ;");
        }
        if (!pdo_fieldexists('xcommunity_unit', 'buildtitle')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_unit') . " ADD `buildtitle` varchar(30) ;");
        }
        if (!pdo_fieldexists('xcommunity_unit', 'regionid')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_unit') . " ADD `regionid` int(11) ;");
        }
        if (!pdo_fieldexists('xcommunity_balance', 'status')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_balance') . " ADD `status` int(1) ;");
        }
        if (!pdo_fieldexists('xcommunity_houselease', 'house_aspect')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_houselease') . " ADD `house_aspect` varchar(30) ;");
        }
        if (!pdo_fieldexists('xcommunity_houselease', 'house_model')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_houselease') . " ADD `house_model` varchar(50) ;");
        }
        if (!pdo_fieldexists('xcommunity_houselease', 'house_floor')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_houselease') . " ADD `house_floor` varchar(50) ;");
        }
        if (!pdo_fieldexists('xcommunity_rank', 'images')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_rank') . " ADD `images` text ;");
        }
        if (!pdo_fieldexists('xcommunity_rank', 'status')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_rank') . " ADD `status` int(1) ;");
        }
        if (!pdo_fieldexists('xcommunity_report', 'categoryid')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_report') . " ADD `categoryid` int(1) ;");
        }
        if (!pdo_fieldexists('xcommunity_stop_park', 'type_code')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_stop_park') . " ADD `type_code` varchar(50) ;");
        }
        if (!pdo_fieldexists('xcommunity_stop_park', 'type_name')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_stop_park') . " ADD `type_name` varchar(50) ;");
        }
        if (!pdo_fieldexists('xcommunity_stop_park', 'year_price')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_stop_park') . " ADD `year_price` decimal(9,2) ;");
        }
        if (!pdo_fieldexists('xcommunity_stop_park', 'month_price')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_stop_park') . " ADD `month_price` decimal(9,2) ;");
        }
        if (!pdo_fieldexists('xcommunity_stop_park', 'day_price')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_stop_park') . " ADD `day_price` decimal(9,2) ;");
        }
        if (!pdo_fieldexists('xcommunity_stop_park', 'power_id')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_stop_park') . " ADD `power_id` int(11) ;");
        }
        if (!pdo_fieldexists('xcommunity_stop_park', 'power_name')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_stop_park') . " ADD `power_name` varchar(50) ;");
        }
        if (!pdo_fieldexists('xcommunity_member', 'idcard')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_member') . " ADD `idcard` varchar(30) ;");
        }
        if (!pdo_fieldexists('xcommunity_member', 'contract')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_member') . " ADD `contract` varchar(30) ;");
        }
        if (!pdo_fieldexists('xcommunity_member', 'opentime')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_member') . " ADD `opentime` int(11) default 0;");
        }
        if (!pdo_fieldexists('qrcode', 'enable')) {
            pdo_query("ALTER TABLE " . tablename('qrcode') . " ADD `enable` int(1) default 0;");
        }
        if (!pdo_fieldexists('xcommunity_houselease', 'house_aspect')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_houselease') . " ADD `house_aspect` varchar(50) default 0;");
        }
        if (!pdo_fieldexists('xcommunity_xqcars', 'addressid')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_xqcars') . " ADD `addressid` int(11) default 0;");
        }
        if (!pdo_fieldexists('xcommunity_cost', 'authtime')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_cost') . " ADD `authtime` int(11) default 0;");
        }
        if (!pdo_fieldexists('xcommunity_res', 'orderid')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_res') . " ADD `orderid` int(11) default 0;");
        }
        if (!pdo_fieldexists('xcommunity_order', 'enable')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_order') . " ADD `enable` int(1) default 1;");
        }
        if (!pdo_fieldexists('xcommunity_order', 'send_way')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_order') . " ADD `send_way` varchar(50) ;");
        }
        if (!pdo_fieldexists('xcommunity_home', 'enable')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_home') . " ADD `enable` int(1) ;");
        }
        if (!pdo_fieldexists('xcommunity_report', 'realname')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_report') . " ADD `realname` varchar(50) ;");
        }
        if (!pdo_fieldexists('xcommunity_report', 'mobile')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_report') . " ADD `mobile` varchar(13) ;");
        }
        if (!pdo_fieldexists('xcommunity_report', 'address')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_report') . " ADD `address` varchar(255) ;");
        }
        if (!pdo_fieldexists('xcommunity_report', 'state')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_report') . " ADD `state` int(1) ;");
        }
        if (pdo_fieldexists('xcommunity_parking', 'status')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_parking') . " modify column status char(30) ;");
        }
        if (!pdo_fieldexists('xcommunity_homemaking_list', 'cid')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_homemaking_list') . " ADD `cid` int(11) ;");
        }
        if (!pdo_fieldexists('xcommunity_building_device', 'thumb')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_building_device') . " ADD `thumb` text ;");
        }
        if (!pdo_fieldexists('xcommunity_building_device', 'category')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_building_device') . " ADD `category` int(1) ;");
        }
        if (!pdo_fieldexists('xcommunity_report', 'price')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_report') . " ADD `price` decimal(12,2) ;");
        }
        if (!pdo_fieldexists('xcommunity_report_log', 'price')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_report_log') . " ADD `price` decimal(12,2) ;");
        }
        if (!pdo_fieldexists('xcommunity_swiftpass', 'appid')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_swiftpass') . " ADD `appid` varchar(50) ;");
        }
        if (!pdo_fieldexists('xcommunity_swiftpass', 'appsecret')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_swiftpass') . " ADD `appsecret` varchar(50) ;");
        }
        if (!pdo_fieldexists('xcommunity_order', 'delivery')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_order') . " ADD `delivery` varchar(50) ;");
        }
        if (!pdo_fieldexists('xcommunity_order', 'pay')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_order') . " ADD `pay` varchar(50) ;");
        }
        if (!pdo_fieldexists('xcommunity_stop_park_cars', 'status')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_stop_park_cars') . " ADD `status` int(1) ;");
        }
        if (!pdo_fieldexists('xcommunity_member_room', 'buildid')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_member_room') . " ADD `buildid` int(11) ;");
        }
        if (!pdo_fieldexists('xcommunity_member_room', 'areaid')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_member_room') . " ADD `areaid` int(11) ;");
        }
        if (!pdo_fieldexists('xcommunity_member_room', 'unitid')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_member_room') . " ADD `unitid` int(11) ;");
        }
        if (pdo_fieldexists('xcommunity_supermark_order', 'code')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_supermark_order') . " modify column code varchar(30) ;");
        }
        if (!pdo_fieldexists('xcommunity_send_log', 'regionid')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_send_log') . " modify column regionid int(11) ;");
        }
        if (!pdo_fieldexists('xcommunity_fee', 'rate')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_fee') . " ADD `rate` decimal(12,2)  ;");
        }
        if (!pdo_fieldexists('xcommunity_fee_log', 'endtime')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_fee_log') . " ADD `endtime` int(11)  ;");
        }
        if (!pdo_fieldexists('xcommunity_fee', 'paytype')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_fee') . " ADD `paytype` int(1)  ;");
        }
        if (!pdo_fieldexists('xcommunity_fee', 'createtime')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_fee') . " ADD `createtime` int(11)  ;");
        }
        if (!pdo_fieldexists('xcommunity_balance', 'area')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_balance') . " ADD `area` int(11)  ;");
        }
        if (!pdo_fieldexists('xcommunity_announcement', 'type')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_announcement') . " ADD `type` int(1)  ;");
        }
        if (!pdo_fieldexists('xcommunity_announcement', 'regionid')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_announcement') . " ADD `regionid` int(11)  ;");
        }
        if (!pdo_fieldexists('xcommunity_fee_log', 'endtime')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_fee_log') . " ADD `endtime` int(11)  ;");
        }

        if (!pdo_fieldexists('xcommunity_vote_user_answer', 'type')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_vote_user_answer') . " ADD `type` int(1)  ;");
        }

        if (!pdo_fieldexists('xcommunity_report', 'visit')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_report') . " ADD `visit` int(1)  ;");
        }
        if (!pdo_fieldexists('xcommunity_users', 'credit')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_users') . " ADD `credit` decimal(10,2) ;");
        }
        if (!pdo_fieldexists('xcommunity_report_log', 'status')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_report_log') . " ADD `status` int(1) ;");
        }
        if (!pdo_fieldexists('xcommunity_dp', 'cid')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_dp') . " ADD `cid` int(1) ;");
        }
        if (!pdo_fieldexists('xcommunity_order', 'dpid')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_order') . " ADD `dpid` int(11) ;");
        }
        if (!pdo_fieldexists('xcommunity_report_log', 'cause')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_report_log') . " ADD `cause` text ;");
        }
        if (!pdo_fieldexists('xcommunity_report_log', 'measure')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_report_log') . " ADD `measure` text ;");
        }
        if (!pdo_fieldexists('xcommunity_dp_fee', 'status')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_dp_fee') . " ADD `status` int(1) ;");
        }
        if (!pdo_fieldexists('xcommunity_users', 'creditstatus')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_users') . " ADD `creditstatus` int(1) ;");
        }
        if (!pdo_fieldexists('xcommunity_users', 'integral')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_users') . " ADD `integral` int(3) ;");
        }
        if (!pdo_fieldexists('xcommunity_entery', 'category')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_entery') . " ADD `category` int(10) ;");
        }
        if (!pdo_fieldexists('xcommunity_cost', 'auth')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_cost') . " ADD `auth` int(1) ;");
        }
        if (!pdo_fieldexists('xcommunity_cost', 'title')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_cost') . " ADD `title` varchar(255) ;");
        }
        if (!pdo_fieldexists('xcommunity_goods', 'thumbs')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_goods') . " ADD `thumbs` text ;");
        }
        if (!pdo_fieldexists('xcommunity_report_log', 'rank')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_report_log') . " ADD `rank` varchar(255) ;");
        }
        if (!pdo_fieldexists('xcommunity_cost_list', 'paytime')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_cost_list') . " ADD `paytime` int(10) ;");
        }
        if (!pdo_fieldexists('xcommunity_phone', 'status')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_phone') . " ADD `status` int(1) default '1' ;");
        }
        if (!pdo_fieldexists('xcommunity_goods', 'recommand')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_goods') . " ADD `recommand` int(1) default '0' ;");
        }
        if (!pdo_fieldexists('xcommunity_staff', 'pid')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_staff') . " ADD `pid` int(11) ;");
        }
        if (!pdo_fieldexists('xcommunity_users', 'txpay')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_users') . " ADD `txpay` varchar(100) ;");
        }
        if (!pdo_fieldexists('xcommunity_users', 'txcid')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_users') . " ADD `txcid` varchar(100) ;");
        }
        if (!pdo_fieldexists('xcommunity_order', 'regionid')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_order') . " ADD `regionid` int(11) ;");
        }
        if (!pdo_fieldexists('xcommunity_entery', 'status')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_entery') . " ADD `status` int(1) ;");
        }
        if (!pdo_fieldexists('xcommunity_entery', 'paytype')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_entery') . " ADD `paytype` int(1) ;");
        }
        if (!pdo_fieldexists('xcommunity_entery', 'paytime')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_entery') . " ADD `paytime` int(11) ;");
        }
        if (!pdo_fieldexists('xcommunity_region', 'area')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_region') . " ADD `area` varchar(100) ;");
        }
        if (!pdo_fieldexists('xcommunity_property', 'mark')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_property') . " ADD `mark` int(11) ;");
        }
        if (pdo_fieldexists('xcommunity_cost_list', 'homenumber')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_cost_list') . " modify column homenumber varchar(255) ;");
        }
        if (!pdo_fieldexists('xcommunity_member', 'groupid')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_member') . " ADD `groupid` int(11) ;");
        }
        if (!pdo_fieldexists('xcommunity_bind_door_device', 'groupid')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_bind_door_device') . " ADD `groupid` int(11) ;");
        }
        if (!pdo_fieldexists('xcommunity_property', 'license')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_property') . " ADD `license` varchar(255) ;");
        }
        if (!pdo_fieldexists('xcommunity_property', 'realname')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_property') . " ADD `realname` varchar(100) ;");
        }
        if (!pdo_fieldexists('xcommunity_property', 'address')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_property') . " ADD `address` varchar(255) ;");
        }
        if (!pdo_fieldexists('xcommunity_property', 'reg_content')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_property') . " ADD `reg_content` varchar(255) ;");
        }
        if (!pdo_fieldexists('xcommunity_property', 'mobile')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_property') . " ADD `mobile` varchar(13) ;");
        }
        if (!pdo_fieldexists('xcommunity_property', 'reg_address')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_property') . " ADD `reg_address` varchar(255) ;");
        }
        if (!pdo_fieldexists('xcommunity_property', 'qq')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_property') . " ADD `qq` varchar(20) ;");
        }
        if (!pdo_fieldexists('xcommunity_property', 'code')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_property') . " ADD `code` varchar(100) ;");
        }
        if (!pdo_fieldexists('xcommunity_property', 'manager')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_property') . " ADD `manager` varchar(50) ;");
        }
        if (!pdo_fieldexists('xcommunity_property', 'manager_mobile')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_property') . " ADD `manager_mobile` varchar(13) ;");
        }
        if (!pdo_fieldexists('xcommunity_property', 'staff')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_property') . " ADD `staff` varchar(255) ;");
        }
        if (!pdo_fieldexists('xcommunity_property', 'type')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_property') . " ADD `type` int(1) ;");
        }
        if (!pdo_fieldexists('xcommunity_property', 'company')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_property') . " ADD `company` varchar(255) ;");
        }
        if (!pdo_fieldexists('xcommunity_property', 'total')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_property') . " ADD `total` int(11) ;");
        }
        if (!pdo_fieldexists('xcommunity_vote', 'province')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_vote') . " ADD `province` varchar(50) ;");
        }
        if (!pdo_fieldexists('xcommunity_vote', 'city')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_vote') . " ADD `city` varchar(50) ;");
        }
        if (!pdo_fieldexists('xcommunity_vote', 'dist')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_vote') . " ADD `dist` varchar(50) ;");
        }
        if (!pdo_fieldexists('xcommunity_safety', 'card_num')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_safety') . " ADD `card_num` varchar(50) ;");
        }
        if (!pdo_fieldexists('xcommunity_order', 'realname')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_order') . " ADD `realname` varchar(50) ;");
        }
        if (!pdo_fieldexists('xcommunity_order', 'company')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_order') . " ADD `company` varchar(50) ;");
        }
        if (!pdo_fieldexists('xcommunity_order', 'express')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_order') . " ADD `express` varchar(50) ;");
        }
        if (!pdo_fieldexists('xcommunity_nav', 'show')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_nav') . " ADD `show` int(1) default '1';");
        }
        if (!pdo_fieldexists('xcommunity_goods', 'wlinks')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_goods') . " ADD `wlinks` varchar(255);");
        }
        if (!pdo_fieldexists('xcommunity_goods', 'littleid')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_goods') . " ADD `littleid` int(11);");
        }
        if (!pdo_fieldexists('xcommunity_counter_log', 'status')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_counter_log') . " ADD `status` int(2);");
        }
        if (pdo_fieldexists('xcommunity_counter_log', 'openid')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_counter_log') . " modify column openid varchar(50) ;");
        }
        if (pdo_fieldexists('xcommunity_charging_price', 'cdtime')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_charging_price') . " modify column cdtime decimal(2,2) ;");
        }
        if (!pdo_fieldexists('xcommunity_cart', 'type')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_cart') . " ADD `type` int(1) default '1';");
        }
        if (!pdo_fieldexists('xcommunity_order', 'littleid')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_order') . " ADD `littleid` int(11) ;");
        }
        if (!pdo_fieldexists('xcommunity_category', 'province')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_category') . " ADD `province` varchar(50) ;");
        }
        if (!pdo_fieldexists('xcommunity_category', 'city')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_category') . " ADD `city` varchar(50) ;");
        }
        if (!pdo_fieldexists('xcommunity_category', 'dist')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_category') . " ADD `dist` varchar(50) ;");
        }
        if (!pdo_fieldexists('xcommunity_housecenter', 'enable')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_housecenter') . " ADD `enable` int(1) ;");
        }
        if (!pdo_fieldexists('xcommunity_report', 'delaytime')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_report') . " ADD `delaytime` int(11) ;");
        }
        if (pdo_fieldexists('xcommunity_charging_order', 'lock')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_charging_order') . " CHANGE `lock` `socket` int(11) ;");
        }
        if (pdo_fieldexists('xcommunity_charging_order', 'cdtime')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_charging_order') . " modify column cdtime decimal(10,1) ;");
        }
        if (!pdo_fieldexists('xcommunity_building_device_cards', 'status')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_building_device_cards') . " ADD `status` int(1) ;");
        }
        if (!pdo_fieldexists('xcommunity_member', 'open_door')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_member') . " ADD `open_door` tinyint(1) ;");
        }
        if (!pdo_fieldexists('users', 'maxregion')) {
            pdo_query("ALTER TABLE " . tablename('users') . " ADD `maxregion` int(11) ;");
        }
        if (!pdo_fieldexists('xcommunity_express_linkman', 'uid')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_express_linkman') . " ADD `uid` int(11) ;");
        }
        if (!pdo_fieldexists('xcommunity_express_parcel', 'uid')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_express_parcel') . " ADD `uid` int(11) ;");
        }
        if (pdo_fieldexists('xcommunity_users', 'menus')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_users') . " modify column menus text ;");
        }
        if (!pdo_fieldexists('xcommunity_pay', 'sub')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_pay') . " ADD `sub` int(1) ;");
        }
        if (!pdo_fieldexists('xcommunity_pay', 'swiftpass')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_pay') . " ADD `swiftpass` int(1) ;");
        }
        if (!pdo_fieldexists('xcommunity_pay', 'hsyunfu')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_pay') . " ADD `hsyunfu` int(1) ;");
        }
        if (!pdo_fieldexists('xcommunity_business_print', 'uid')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_business_print') . " ADD `uid` int(11) ;");
        }
        if (!pdo_fieldexists('xcommunity_business_wechat', 'uid')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_business_wechat') . " ADD `uid` int(11) ;");
        }
        if (!pdo_fieldexists('xcommunity_goods', 'shopid')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_goods') . " ADD `shopid` int(11) ;");
        }
        if (!pdo_fieldexists('xcommunity_cost', 'remark')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_cost') . " ADD `remark` text ;");
        }
        if (!pdo_fieldexists('xcommunity_charging_station', 'tid')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_charging_station') . " ADD `tid` int(11) ;");
        }
        if (!pdo_fieldexists('xcommunity_charging_price', 'tid')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_charging_price') . " ADD `tid` int(11) ;");
        }
        if (!pdo_fieldexists('xcommunity_service_data', 'sub_id')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_service_data') . " ADD `sub_id` varchar(50) ;");
        }
        if (!pdo_fieldexists('xcommunity_service_data', 'apikey')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_service_data') . " ADD `apikey` varchar(50) ;");
        }
        if (!pdo_fieldexists('xcommunity_service_data', 'appid')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_service_data') . " ADD `appid` varchar(50) ;");
        }
        if (!pdo_fieldexists('xcommunity_service_data', 'appsecret')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_service_data') . " ADD `appsecret` varchar(50) ;");
        }
        if (!pdo_fieldexists('xcommunity_shop', 'type')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_shop') . " ADD `type` int(1) default 1;");
        }
        if (!pdo_fieldexists('xcommunity_shop_print', 'shoptype')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_shop_print') . " ADD `shoptype` int(1) default 1;");
        }
        if (!pdo_fieldexists('xcommunity_shop_wechat', 'shoptype')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_shop_wechat') . " ADD `shoptype` int(1) default 1;");
        }
        if (!pdo_fieldexists('xcommunity_fee', 'uid')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_fee') . " ADD `uid` int(11);");
        }
        if (!pdo_fieldexists('xcommunity_fee', 'type')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_fee') . " ADD `type` int(1) default 1;");
        }
        if (!pdo_fieldexists('xcommunity_fee', 'old_num')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_fee') . " ADD `old_num` decimal(10,2);");
        }
        if (!pdo_fieldexists('xcommunity_fee', 'new_num')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_fee') . " ADD `new_num` decimal(10,2);");
        }
        if (!pdo_fieldexists('xcommunity_fee', 'status')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_fee') . " ADD `status` int(1);");
        }
        if (!pdo_fieldexists('xcommunity_fee', 'paytime')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_fee') . " ADD `paytime` int(11);");
        }
        if (!pdo_fieldexists('xcommunity_fee', 'username')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_fee') . " ADD `username` varchar(50);");
        }
        if (!pdo_fieldexists('xcommunity_pay', 'chinaums')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_pay') . " ADD `chinaums` int(1);");
        }
        if (!pdo_fieldexists('xcommunity_charging_station', 'address')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_charging_station') . " ADD `address` varchar(255);");
        }
        if (!pdo_fieldexists('xcommunity_charging_station', 'lng')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_charging_station') . " ADD `lng` varchar(10);");
        }
        if (!pdo_fieldexists('xcommunity_charging_station', 'lat')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_charging_station') . " ADD `lat` varchar(10);");
        }
        if (!pdo_fieldexists('mc_members', 'newcredit1')) {
            pdo_query("ALTER TABLE " . tablename('mc_members') . " ADD `newcredit1` decimal(10,2);");
        }
        if (!pdo_fieldexists('xcommunity_fee_category', 'square')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_fee_category') . " ADD `square` int(11);");
        }
        if (!pdo_fieldexists('xcommunity_fee_category', 'type')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_fee_category') . " ADD `type` int(1);");
        }
        if (!pdo_fieldexists('xcommunity_region', 'stamp')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_region') . " ADD `stamp` varchar(255);");
        }
        if (!pdo_fieldexists('xcommunity_app_user', 'license')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_app_user') . " ADD `license` varchar(255);");
        }
        if (!pdo_fieldexists('xcommunity_app_user', 'company')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_app_user') . " ADD `company` varchar(255);");
        }
        if (!pdo_fieldexists('xcommunity_vote_user', 'addressid')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_vote_user') . " ADD `addressid` int(11);");
        }
        if (!pdo_fieldexists('xcommunity_announcement', 'allregion')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_announcement') . " ADD `allregion` int(1);");
        }
        if (!pdo_fieldexists('xcommunity_slide', 'allregion')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_slide') . " ADD `allregion` int(1);");
        }
        if (!pdo_fieldexists('xcommunity_mien', 'allregion')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_mien') . " ADD `allregion` int(1);");
        }
        if (!pdo_fieldexists('xcommunity_goods', 'allregion')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_goods') . " ADD `allregion` int(1);");
        }
        if (!pdo_fieldexists('xcommunity_vote', 'allregion')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_vote') . " ADD `allregion` int(1);");
        }
        if (!pdo_fieldexists('xcommunity_plugin_article_message', 'allregion')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_plugin_article_message') . " ADD `allregion` int(1);");
        }
        if (!pdo_fieldexists('xcommunity_category', 'allregion')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_category') . " ADD `allregion` int(1);");
        }
        if (!pdo_fieldexists('xcommunity_activity', 'allregion')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_activity') . " ADD `allregion` int(1);");
        }
        if (!pdo_fieldexists('xcommunity_search', 'allregion')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_search') . " ADD `allregion` int(1);");
        }
        if (!pdo_fieldexists('xcommunity_phone', 'allregion')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_phone') . " ADD `allregion` int(1);");
        }
        if (!pdo_fieldexists('xcommunity_vote_question', 'maxnum')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_vote_question') . " ADD `maxnum` int(2);");
        }
        if (!pdo_fieldexists('xcommunity_building_device_cards', 'roomid')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_building_device_cards') . " ADD `roomid` int(11);");
        }
        if (!pdo_fieldexists('xcommunity_building_device_cards', 'mobile')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_building_device_cards') . " ADD `mobile` varchar(13);");
        }

        if (!pdo_fieldexists('xcommunity_dp', 'tag')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_dp') . " ADD `tag` varchar(255);");
        }
        if (!pdo_fieldexists('xcommunity_dp', 'creditpay')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_dp') . " ADD `creditpay` int(1);");
        }
        if (!pdo_fieldexists('xcommunity_order', 'randprice')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_order') . " ADD `randprice` decimal(10,2);");
        }
        if (!pdo_fieldexists('xcommunity_report_order', 'orderid')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_report_order') . " ADD `orderid` int(11);");
        }
        if (!pdo_fieldexists('xcommunity_order', 'openid')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_order') . " ADD `openid` varchar(50);");
        }
        if (!pdo_fieldexists('xcommunity_express_linkman', 'area')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_express_linkman') . " ADD `area` varchar(255);");
        }
        if (!pdo_fieldexists('xcommunity_order', 'total')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_order') . " ADD `total` decimal(10,2);");

        }
        if (!pdo_fieldexists('xcommunity_goods', 'counterid')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_goods') . " ADD `counterid` int(11);");
        }
        if (!pdo_fieldexists('xcommunity_open_log', 'isopen')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_open_log') . " ADD `isopen` int(1);");
        }
        if (!pdo_fieldexists('xcommunity_charging_order', 'endtime')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_charging_order') . " ADD `endtime` int(11);");
        }
        if (!pdo_fieldexists('xcommunity_charging_order', 'stime')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_charging_order') . " ADD `stime` int(11);");
        }
        if (!pdo_fieldexists('xcommunity_charging_order', 'power')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_charging_order') . " ADD `power` int(11);");
        }
        if (!pdo_fieldexists('xcommunity_charging_station', 'remark')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_charging_station') . " ADD `remark` text;");
        }
        if (!pdo_fieldexists('xcommunity_goods', 'isshow')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_goods') . " ADD `isshow` int(1) default 0;");
        }

        if (!pdo_fieldexists('users', 'maxregion')) {
            pdo_query("ALTER TABLE " . tablename('users') . " ADD `maxregion` int(11);");

        }
        if (!pdo_fieldexists('xcommunity_charging_price', 'type')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_charging_price') . " ADD `type` int(1) default 1;");
        }
        if (!pdo_fieldexists('xcommunity_charging_price', 'power')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_charging_price') . " ADD `power` int(11);");

        }
        /**
         * 费用增加记录id
         */
        if (!pdo_fieldexists('xcommunity_fee', 'logid')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_fee') . " ADD `logid` int(11);");

        }
        /**
         * 门禁增加是否隐藏状态
         */
        if (!pdo_fieldexists('xcommunity_building_device', 'status')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_building_device') . " ADD `status` tinyint(1) default '1';");
        }
        /**
         * 充电桩增加字段
         */
        if (!pdo_fieldexists('xcommunity_charging_station', 'rssi')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_charging_station') . " ADD `rssi` tinyint(3) ;");
        }
        if (!pdo_fieldexists('xcommunity_charging_station', 'type')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_charging_station') . " ADD `type` tinyint(1) ;");
        }
        if (!pdo_fieldexists('xcommunity_charging_station', 'appid')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_charging_station') . " ADD `appid` varchar(50) ;");
        }
        if (!pdo_fieldexists('xcommunity_charging_station', 'appsecret')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_charging_station') . " ADD `appsecret` varchar(50) ;");
        }
        if (!pdo_fieldexists('xcommunity_charging_throw', 'quanbill')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_charging_throw') . " ADD `quanbill` tinyint(1) ;");
        }
        if (!pdo_fieldexists('xcommunity_charging_throw', 'quanrule')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_charging_throw') . " ADD `quanrule` varchar(255) ;");
        }
        if (!pdo_fieldexists('xcommunity_charging_throw', 'timebill')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_charging_throw') . " ADD `timebill` tinyint(1) ;");
        }
        if (!pdo_fieldexists('xcommunity_charging_throw', 'status')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_charging_throw') . " ADD `status` tinyint(1) default 0;");
        }
        if (!pdo_fieldexists('xcommunity_charging_throw', 'timerule')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_charging_throw') . " ADD `timerule` varchar(255) ;");
        }
        if (!pdo_fieldexists('xcommunity_charging_order', 'type')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_charging_order') . " ADD `type` tinyint(1) ;");
        }
        if (!pdo_fieldexists('xcommunity_charging_order', 'uid')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_charging_order') . " ADD `uid` int(11) ;");
        }
        if (!pdo_fieldexists('xcommunity_charging_throw', 'credit')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_charging_throw') . " ADD `credit`  decimal(10,2) NOT NULL default '0.00' ;");
        }
        /**
         * 微擎会员增加充电桩余额字段
         */
        if (!pdo_fieldexists('mc_members', 'chargecredit')) {
            pdo_query("ALTER TABLE " . tablename('mc_members') . " ADD `chargecredit` decimal(10,2) default '0.00';");
        }
        /**
         * 修改订单TYPE长度
         */
        if (pdo_fieldexists('xcommunity_order', 'type')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_order') . " modify column `type` char(30) ;");
        }
        if (pdo_fieldexists('xcommunity_charging_order', 'stime')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_charging_order') . " modify column `stime` decimal(10,2) ;");
        }
        /**
         * 修改slide url 长度
         */
        if (pdo_fieldexists('xcommunity_slide', 'url')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_slide') . " modify column `url` text ;");
        }
        /**
         * 订单表增加提现类型（1余额提现2佣金提现）
         */
        if (!pdo_fieldexists('xcommunity_order', 'category')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_order') . " ADD `category` int(11) ;");
        }
        /**
         * 小程序打包增加打包标题
         */
        if (!pdo_fieldexists('xcommunity_wxapp_setting', 'packtitle')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_wxapp_setting') . " ADD `packtitle` varchar(50) ;");
        }
        /**
         * 小程序打包增加压缩包名称
         */
        if (!pdo_fieldexists('xcommunity_wxapp_setting', 'packname')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_wxapp_setting') . " ADD `packname` varchar(50) ;");
        }
        /**
         * 小程序打包增加打包时间
         */
        if (!pdo_fieldexists('xcommunity_wxapp_setting', 'createtime')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_wxapp_setting') . " ADD `createtime` int(11) ;");
        }
        /**
         * 货柜增加类型
         */
        if (!pdo_fieldexists('xcommunity_counter_main', 'type')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_counter_main') . " ADD `type` tinyint(1) ;");
        }
        /**
         * 货柜增加是否投放
         */
        if (!pdo_fieldexists('xcommunity_counter_main', 'status')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_counter_main') . " ADD `status` tinyint(1) ;");
        }
        /**
         * 货柜增加appid
         */
        if (!pdo_fieldexists('xcommunity_counter_main', 'appid')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_counter_main') . " ADD `appid` varchar(50) ;");
        }
        /**
         * 货柜增加secret
         */
        if (!pdo_fieldexists('xcommunity_counter_main', 'secret')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_counter_main') . " ADD `secret` varchar(50) ;");
        }
        /**
         * 货柜增加柜子数量amount
         */
        if (!pdo_fieldexists('xcommunity_counter_main', 'amount')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_counter_main') . " ADD `amount` tinyint(2) ;");
        }
        /**
         * 货柜的小柜子名称title
         */
        if (!pdo_fieldexists('xcommunity_counter_little', 'title')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_counter_little') . " ADD `title` varchar(50) ;");
        }
        /**
         * 信息发布-分类图片
         */
        if (!pdo_fieldexists('xcommunity_plugin_article_class', 'pic')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_plugin_article_class') . " ADD `pic` varchar(255) ;");
        }
        /**
         * 修改充电桩订单编号类型
         */
        if (pdo_fieldexists('xcommunity_charging_order', 'code')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_charging_order') . " modify column code varchar(50) ;");
        }
        /**
         * 云柜增加收费、收费规则
         */
        if (!pdo_fieldexists('xcommunity_counter_main', 'stat')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_counter_main') . " ADD `stat` tinyint(1) ;");
        }
        if (!pdo_fieldexists('xcommunity_counter_main', 'rule')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_counter_main') . " ADD `rule` tinyint(1) ;");
        }
        if (!pdo_fieldexists('xcommunity_counter_main', 'price')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_counter_main') . " ADD `price` decimal(10,2) ;");
        }
        /**
         * 云柜管理员添加柜子字段
         */
        if (!pdo_fieldexists('xcommunity_counter_notice', 'littleid')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_counter_notice') . " ADD `littleid` text ;");
        }
        /**
         * 云柜小柜子添加在线状态
         */
        if (!pdo_fieldexists('xcommunity_counter_little', 'online')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_counter_little') . " ADD `online` tinyint(1) default '0' ;");
        }
        /**
         * 智能门禁发卡管理添加房屋地址
         */
        if (!pdo_fieldexists('xcommunity_building_device_cards', 'address')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_building_device_cards') . " ADD `address` varchar(255) ;");
        }
        /**
         * 商家的商品增加限购
         */
        if (!pdo_fieldexists('xcommunity_goods', 'limitnum')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_goods') . " ADD `limitnum` int(11) ;");
        }
        /**
         * 商家增加上线下线开关
         */
        if (!pdo_fieldexists('xcommunity_dp', 'enable')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_dp') . " ADD `enable` tinyint(1) defalut 1 ;");
        }
        if (!pdo_fieldexists('xcommunity_dp', 'setting')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_dp') . " ADD `setting` text ;");
        }
        /**
         * 商家商品增加售卖开始结束时间
         */
        if (!pdo_fieldexists('xcommunity_goods', 'startdate')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_goods') . " ADD `startdate` int(11) ;");
        }
        if (!pdo_fieldexists('xcommunity_goods', 'enddate')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_goods') . " ADD `enddate` int(11) ;");
        }
        /**
         * 底部菜单增加分类
         */
        if (!pdo_fieldexists('xcommunity_appmenu', 'cate')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_appmenu') . " ADD `cate` int(11) default 0 ;");
        }
        /**
         * 增加银行通道
         */
        if (!pdo_fieldexists('xcommunity_swiftpass', 'banktype')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_swiftpass') . " ADD `banktype` tinyint(1) ;");
        }
        if (!pdo_fieldexists('xcommunity_swiftpass', 'private_key')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_swiftpass') . " ADD `private_key` text ;");
        }
        /**
         * 增叫下发呼叫状态
         */
        if (!pdo_fieldexists('xcommunity_member', 'voicestatus')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_member') . " ADD `voicestatus` tinyint(1) default 0 ;");
        }
        /**
         * 充电桩订单添加判断是否修改充电时间--avive
         */
        if (!pdo_fieldexists('xcommunity_charging_order', 'isedit')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_charging_order') . " ADD `isedit` tinyint(1) default 0 ;");
        }
        /**
         * 幻灯增加展示的时间
         */
        if (!pdo_fieldexists('xcommunity_slide', 'starttime')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_slide') . " ADD `starttime` int(10) unsigned default '1508666301';");
        }
        if (!pdo_fieldexists('xcommunity_slide', 'endtime')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_slide') . " ADD `endtime` int(10) unsigned default '1571738301';");
        }
        /**
         * 小区管理员添加选择的超市或商铺
         */
        if (!pdo_fieldexists('xcommunity_users', 'store')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_users') . " ADD `store` text ;");
        }
        /**
         * 小区商家的基本设置
         */
        if (!pdo_fieldexists('xcommunity_dp', 'setting')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_dp') . " ADD `setting` text ;");
        }
        /**
         * 小区商家、超市的分成
         */
        if (!pdo_fieldexists('xcommunity_dp', 'commission')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_dp') . " ADD `commission` varchar(50) default '0,0' ;");
        }
        if (!pdo_fieldexists('xcommunity_shop', 'commission')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_shop') . " ADD `commission` varchar(50) default '0,0' ;");
        }
        /**
         * 充电桩增加排序
         */
        if (!pdo_fieldexists('xcommunity_charging_station', 'displayorder')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_charging_station') . " ADD `displayorder` tinyint(3) default 0 ;");
        }
        /**
         * 门禁增加区域、楼宇、单元字段
         */
        if (!pdo_fieldexists('xcommunity_building_device', 'areaid')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_building_device') . " ADD `areaid` int(11) default '0' ;");
        }
        if (!pdo_fieldexists('xcommunity_building_device', 'buildid')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_building_device') . " ADD `buildid` int(11) default '0' ;");
        }
        if (!pdo_fieldexists('xcommunity_building_device', 'unitid')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_building_device') . " ADD `unitid` int(11) default '0' ;");
        }
        /**
         * 小区超市添加小区的省市区
         */
        if (!pdo_fieldexists('xcommunity_shop_wechat', 'province')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_shop_wechat') . " ADD `province` varchar(50) ;");
        }
        if (!pdo_fieldexists('xcommunity_shop_wechat', 'city')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_shop_wechat') . " ADD `city` varchar(50) ;");
        }
        if (!pdo_fieldexists('xcommunity_shop_wechat', 'dist')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_shop_wechat') . " ADD `dist` varchar(50) ;");
        }
        if (!pdo_fieldexists('xcommunity_shop_wechat', 'allregion')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_shop_wechat') . " ADD `allregion` tinyint(1) default '0' ;");
        }
        /**
         *
         * 账单记录增加开始时间
         */
        if (!pdo_fieldexists('xcommunity_fee_log', 'starttime')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_fee_log') . " ADD `starttime` int(11) ;");
        }
        /*
        * 小区添加积分字段
        */
        if (!pdo_fieldexists('xcommunity_region', 'credit')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_region') . " ADD `credit` decimal(10,2) default '0.00' ;");
        }
        /**
         * 分成添加判断积分、余额字段，增加减少
         */
        if (!pdo_fieldexists('xcommunity_commission_log', 'creditstatus')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_commission_log') . " ADD `creditstatus` tinyint(1) default '1' ;");
        }
        if (!pdo_fieldexists('xcommunity_commission_log', 'category')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_commission_log') . " ADD `category` tinyint(1) default '2' ;");

        }
        /**
         * 订单表添加抵扣的费用
         */
        if (!pdo_fieldexists('xcommunity_order', 'offsetprice')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_order') . " ADD `offsetprice` decimal(10,2) default '0.00' ;");
        }
        /**
         * 商品添加套餐和使用规则
         */
        if (!pdo_fieldexists('xcommunity_goods', 'rules')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_goods') . " ADD `rules` text default '' ;");
        }
        if (!pdo_fieldexists('xcommunity_goods', 'combo')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_goods') . " ADD `combo` text default '' ;");
        }
        /**
         * 云易宝
         */
        if (!pdo_fieldexists('xcommunity_member_room', 'yybstatus')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_member_room') . " ADD `yybstatus` tinyint(1) ;");
        }
        if (!pdo_fieldexists('xcommunity_member_room', 'yybroomid')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_member_room') . " ADD `yybroomid` int(11) ;");
        }
        if (!pdo_fieldexists('xcommunity_member_room', 'action')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_member_room') . " ADD `action` tinyint(1) ;");
        }
        if (!pdo_fieldexists('xcommunity_property', 'yybpropertyid')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_property') . " ADD `yybpropertyid` int(11) ;");
        }
        if (!pdo_fieldexists('xcommunity_property', 'action')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_property') . " ADD `action` tinyint(1) ;");
        }
        if (!pdo_fieldexists('xcommunity_property', 'yybstatus')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_property') . " ADD `yybstatus` tinyint(1) ;");
        }
        if (!pdo_fieldexists('xcommunity_region', 'action')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_region') . " ADD `action` tinyint(1) ;");
        }
        if (!pdo_fieldexists('xcommunity_region', 'yybstatus')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_region') . " ADD `yybstatus` tinyint(1) ;");
        }
        if (!pdo_fieldexists('xcommunity_region', 'yybcommunityid')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_region') . " ADD `yybcommunityid` int(11) ;");
        }
        if (!pdo_fieldexists('xcommunity_build', 'action')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_build') . " ADD `action` tinyint(1) ;");
        }
        if (!pdo_fieldexists('xcommunity_build', 'yybstatus')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_build') . " ADD `yybstatus` tinyint(1) ;");
        }
        if (!pdo_fieldexists('xcommunity_build', 'yybbuildid')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_build') . " ADD `yybbuildid` int(11) ;");
        }
        if (!pdo_fieldexists('xcommunity_unit', 'action')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_unit') . " ADD `action` tinyint(1) ;");
        }
        if (!pdo_fieldexists('xcommunity_unit', 'yybstatus')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_unit') . " ADD `yybstatus` tinyint(1) ;");
        }
        if (!pdo_fieldexists('xcommunity_unit', 'yybunitid')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_unit') . " ADD `yybunitid` int(11) ;");
        }
        /**
         * 费用增加小区
         */
        if (!pdo_fieldexists('xcommunity_fee', 'regionid')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_fee') . " ADD `regionid` int(11) ;");
        }
        /**
         * 修改添加商家外链长度
         */
        if (pdo_fieldexists('xcommunity_dp', 'businessurl')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_dp') . " modify column `businessurl` text ;");
        }
        /**
         * 商家增加删除是隐藏
         */
        if (!pdo_fieldexists('xcommunity_dp', 'status')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_dp') . " ADD `status` tinyint(1) ;");
        }
        pdo_update('xcommunity_dp', array('status' => 1), array());
        /**
         * 超市增加删除是隐藏
         */
        if (!pdo_fieldexists('xcommunity_shop', 'status')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_shop') . " ADD `status` tinyint(1) ;");
        }
        /**
         * 充电桩增加展示编号字段
         */
        if (!pdo_fieldexists('xcommunity_charging_station', 'zscode')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_charging_station') . " ADD `zscode` varchar(50) ;");
        }
        /**
         * 费用管理--费用排序
         */
        if (!pdo_fieldexists('xcommunity_cost_list', 'displayorder')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_cost_list') . " ADD `displayorder` int(10) default '0' ;");
        }
        /**
         * 账单管理--账单排序
         */
        if (!pdo_fieldexists('xcommunity_fee', 'displayorder')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_fee') . " ADD `displayorder` int(10) default '0' ;");
        }
        /**
         * 周边商家--店铺客服链接
         */
        if (!pdo_fieldexists('xcommunity_dp', 'serviceurl')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_dp') . " ADD `serviceurl` text ;");
        }
        /**
         * 下发卡号数据表增加设备号
         */
        if (!pdo_fieldexists('xcommunity_building_device_cards', 'device_code')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_building_device_cards') . " ADD `device_code` text ;");
        }
        /**
         * 房号--楼层
         */
        if (!pdo_fieldexists('xcommunity_member_room', 'floor_num')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_member_room') . " ADD `floor_num` int(11) default '0' ;");
        }
        /**
         * 小区活动--订单
         */
        if (!pdo_fieldexists('xcommunity_res', 'address')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_res') . " ADD `address` varchar(255) ;");
        }
        /**
         * 账单管理--抄表添加抄表员
         */
        if (!pdo_fieldexists('xcommunity_fee', 'readername')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_fee') . " ADD `readername` varchar(50) ;");
        }
        /**
         * 报修--闭单码
         */
        if (!pdo_fieldexists('xcommunity_report', 'code')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_report') . " ADD `code` int(10) default '0' ;");
        }
        /**
         * 订单--闭单码
         */
        if (!pdo_fieldexists('xcommunity_order', 'code')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_order') . " ADD `code` int(10) default '0' ;");
        }
        /**
         * 报修接收员--员工类型
         */
        if (!pdo_fieldexists('xcommunity_notice', 'xqtype')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_notice') . " ADD `xqtype` tinyint(1) default '1' ;");
        }
        /**
         * 门禁设备--在线状态
         */
        if (!pdo_fieldexists('xcommunity_building_device', 'doorstatus')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_building_device') . " ADD `doorstatus` tinyint(1) default '0' ;");
        }
        /**
         * 门禁设备--人脸识别参数
         */
        if (!pdo_fieldexists('xcommunity_building_device', 'appid')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_building_device') . " ADD `appid` varchar(50) ;");
        }
        if (!pdo_fieldexists('xcommunity_building_device', 'appkey')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_building_device') . " ADD `appkey` varchar(50) ;");
        }
        if (!pdo_fieldexists('xcommunity_building_device', 'appsecret')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_building_device') . " ADD `appsecret` varchar(50) ;");
        }
        /**
         * 人脸识别记录--人员附属信息、创建时间
         */
        if (!pdo_fieldexists('xcommunity_face_logs', 'subdata')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_face_logs') . " ADD `subdata` text ;");
        }
        if (!pdo_fieldexists('xcommunity_face_logs', 'createtime')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_face_logs') . " ADD `createtime` int(11) default '0' ;");
        }

        /**
         * 充电桩--广告图片
         */
        if (!pdo_fieldexists('xcommunity_charging_station', 'advpic')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_charging_station') . " ADD `advpic` text ;");
        }
        //投票增加投票人数
        if (!pdo_fieldexists('xcommunity_vote', 'num')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_vote') . " ADD `num` int(11) default '0' ;");
        }
        // 充电桩运营商--充电说明
        if (!pdo_fieldexists('xcommunity_charging_throw', 'desc')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_charging_throw') . " ADD `desc` varchar(255);");
        }
        // 订单--评价
        if (!pdo_fieldexists('xcommunity_order', 'rank_status')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_order') . " ADD `rank_status` tinyint(1) default '0';");
        }
        // 充电桩--版本号、更新时间
        if (!pdo_fieldexists('xcommunity_charging_station', 'version')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_charging_station') . " ADD `version` tinyint(1) default '0';");
        }
        if (!pdo_fieldexists('xcommunity_charging_station', 'updatetime')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_charging_station') . " ADD `updatetime` int(10) default '1545926400';");
        }
        // 问卷提交--实名、匿名提交
        if (!pdo_fieldexists('xcommunity_vote_user', 'enable')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_vote_user') . " ADD `enable` tinyint(1) default '0';");
        }
        // 修改充电桩计费规则
        if (pdo_fieldexists('xcommunity_charging_throw', 'quanrule')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_charging_throw') . " modify column quanrule text ;");
        }
        // 门禁--幻灯图片
        if (!pdo_fieldexists('xcommunity_building_device', 'photos')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_building_device') . " ADD `photos` text;");
        }
        // 首页推荐--租赁、二手、活动
        if (!pdo_fieldexists('xcommunity_houselease', 'recommand')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_houselease') . " ADD `recommand` tinyint(1) default '0';");
        }
        if (!pdo_fieldexists('xcommunity_fled', 'recommand')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_fled') . " ADD `recommand` tinyint(1) default '0';");
        }
        if (!pdo_fieldexists('xcommunity_activity', 'recommand')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_activity') . " ADD `recommand` tinyint(1) default '0';");
        }
        //底部菜单增加字段
        if (!pdo_fieldexists('xcommunity_footmenu', 'click_icon')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_footmenu') . " ADD `click_icon` varchar(250) ;");
        }
        // 发卡的截止时间
        if (!pdo_fieldexists('xcommunity_building_device_cards', 'enddate')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_building_device_cards') . " ADD `enddate` int(11) default '0';");
        }
        // 账单添加备注
        if (!pdo_fieldexists('xcommunity_fee', 'remark')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_fee') . " ADD `remark` text;");
        }
        // 商品的售卖时间和使用时间的开关
        if (!pdo_fieldexists('xcommunity_goods', 'common')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_goods') . " ADD `common` text;");
        }
        // 操作员添加角色
        if (!pdo_fieldexists('xcommunity_users', 'roleid')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_users') . " ADD `roleid` int(11);");
        }
        // 周边商家添加证件链接、活动链接
        if (!pdo_fieldexists('xcommunity_dp', 'cardurl')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_dp') . " ADD `cardurl` text;");
        }
        if (!pdo_fieldexists('xcommunity_dp', 'activityurl')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_dp') . " ADD `activityurl` text;");
        }
        // 报修、建议接收员添加处理报修、建议的次数及id
        if (!pdo_fieldexists('xcommunity_notice', 'grab_num')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_notice') . " ADD `grab_num` int(11);");
        }
        if (!pdo_fieldexists('xcommunity_notice', 'grab_ids')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_notice') . " ADD `grab_ids` text;");
        }
        // 停车车场表的更新
        if (!pdo_fieldexists('xcommunity_parks', 'uid')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_parks') . " ADD `uid` int(11);");
        }
        if (!pdo_fieldexists('xcommunity_parks', 'type')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_parks') . " ADD `type` tinyint(1) default '0';");
        }
        if (!pdo_fieldexists('xcommunity_parks', 'setting')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_parks') . " ADD `setting` text;");
        }
        if (pdo_fieldexists('xcommunity_parks', 'rule')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_parks') . " modify column rule text ;");
        }
        if (!pdo_fieldexists('xcommunity_parks', 'management')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_parks') . " ADD `management` varchar(50);");
        }
        if (!pdo_fieldexists('xcommunity_parks', 'exitus')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_parks') . " ADD `exitus` tinyint(1);");
        }
        if (!pdo_fieldexists('xcommunity_parks', 'cars_num')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_parks') . " ADD `cars_num` int(11);");
        }
        if (!pdo_fieldexists('xcommunity_parks', 'password')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_parks') . " ADD `password` varchar(200);");
        }
        if (!pdo_fieldexists('xcommunity_parks', 'month_type')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_parks') . " ADD `month_type` tinyint(1);");
        }
        if (!pdo_fieldexists('xcommunity_parks', 'month_num')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_parks') . " ADD `month_num` int(11);");
        }
        if (!pdo_fieldexists('xcommunity_parks', 'short_num')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_parks') . " ADD `short_num` int(11);");
        }
        if (!pdo_fieldexists('xcommunity_parks', 'rule_id')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_parks') . " ADD `rule_id` int(11);");
        }
        if (!pdo_fieldexists('xcommunity_parks', 'leave_space')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_parks') . " ADD `leave_space` int(11);");
        }
        if (!pdo_fieldexists('xcommunity_parks', 'temrule_id')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_parks') . " ADD `temrule_id` int(11);");
        }
        if (!pdo_fieldexists('xcommunity_parks', 'over_month_num')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_parks') . " ADD `over_month_num` int(11);");
        }
        if (!pdo_fieldexists('xcommunity_parks', 'over_short_num')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_parks') . " ADD `over_short_num` int(11);");
        }
        if (!pdo_fieldexists('xcommunity_parks', 'qr_status')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_parks') . " ADD `qr_status` tinyint(1);");
        }
        // 停车设备表的更新
        if (!pdo_fieldexists('xcommunity_parks_device', 'uid')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_parks_device') . " ADD `uid` int(11);");
        }
        if (!pdo_fieldexists('xcommunity_parks_device', 'createtime')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_parks_device') . " ADD `createtime` int(11);");
        }
        if (!pdo_fieldexists('xcommunity_parks_device', 'title')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_parks_device') . " ADD `title` varchar(50);");
        }
        if (!pdo_fieldexists('xcommunity_parks_device', 'setting')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_parks_device') . " ADD `setting` text;");
        }
        if (!pdo_fieldexists('xcommunity_parks_device', 'cardid')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_parks_device') . " ADD `cardid` varchar(50);");
        }
        if (!pdo_fieldexists('xcommunity_parks_device', 'exit_type')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_parks_device') . " ADD `exit_type` tinyint(1);");
        }
        if (!pdo_fieldexists('xcommunity_parks_device', 'type')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_parks_device') . " ADD `type` tinyint(1);");
        }
        // 停车车辆表的更新
        if (!pdo_fieldexists('xcommunity_parks_cars', 'status')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_parks_cars') . " ADD `status` tinyint(1);");
        }
        if (!pdo_fieldexists('xcommunity_parks_cars', 'remark')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_parks_cars') . " ADD `remark` varchar(255);");
        }
        if (!pdo_fieldexists('xcommunity_parks_cars', 'rule_id')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_parks_cars') . " ADD `rule_id` int(11);");
        }
        if (!pdo_fieldexists('xcommunity_parks_cars', 'businessid')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_parks_cars') . " ADD `businessid` int(11);");
        }
        if (!pdo_fieldexists('xcommunity_parks_cars', 'realname')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_parks_cars') . " ADD `realname` varchar(30);");
        }
        if (!pdo_fieldexists('xcommunity_parks_cars', 'mobile')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_parks_cars') . " ADD `mobile` varchar(30);");
        }
        if (!pdo_fieldexists('xcommunity_parks_cars', 'verity')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_parks_cars') . " ADD `verity` tinyint(1);");
        }
        if (!pdo_fieldexists('xcommunity_parks_cars', 'parking_id')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_parks_cars') . " ADD `parking_id` int(11);");
        }
        // 停车订单表的更新
        if (!pdo_fieldexists('xcommunity_parks_order', 'parkid')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_parks_order') . " ADD `parkid` int(11);");
        }
        if (!pdo_fieldexists('xcommunity_parks_order', 'uid')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_parks_order') . " ADD `uid` int(11);");
        }
        if (!pdo_fieldexists('xcommunity_parks_order', 'transid')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_parks_order') . " ADD `transid` varchar(50);");
        }
        if (!pdo_fieldexists('xcommunity_parks_order', 'rodtype')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_parks_order') . " ADD `rodtype` tinyint(1);");
        }
        if (!pdo_fieldexists('xcommunity_parks_order', 'identity')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_parks_order') . " ADD `identity` varchar(50);");
        }
        if (!pdo_fieldexists('xcommunity_parks_order', 'paytime')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_parks_order') . " ADD `paytime` int(11);");
        }
        if (!pdo_fieldexists('xcommunity_parks_order', 'logid')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_parks_order') . " ADD `logid` int(11);");
        }
        if (!pdo_fieldexists('xcommunity_parks_order', 'pay_status')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_parks_order') . " ADD `pay_status` tinyint(1);");
        }
        if (!pdo_fieldexists('xcommunity_parks_order', 'endtime')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_parks_order') . " ADD `endtime` int(11);");
        }
        if (!pdo_fieldexists('xcommunity_parks_order', 'display')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_parks_order') . " ADD `display` tinyint(1) default '1';");
        }
        if (!pdo_fieldexists('xcommunity_parks_order', 'orderid')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_parks_order') . " ADD `orderid` int(11);");
        }
//        pdo_update('xcommunity_shop', array('status' => 1), array());
//        itoast('修复成功', referer(), 'success', true);
        $url = './index.php?i=' . $_W['uniacid'] . '&c=entry&do=opendoor&op=key&m=' . MODULE_NAME;
        pdo_update('xcommunity_housecenter_region', array('url' => $url), array('title' => '临时钥匙'));
        // 门禁添加2个字段
        if (!pdo_fieldexists('xcommunity_building_device', 'username')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_building_device') . " ADD `username` varchar(50);");
        }
        if (!pdo_fieldexists('xcommunity_building_device', 'password')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_building_device') . " ADD `password` varchar(50);");
        }
        if (!pdo_fieldexists('xcommunity_building_device', 'apikey')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_building_device') . " ADD `apikey` varchar(50);");
        }
        if (!pdo_fieldexists('xcommunity_building_device', 'deviceid')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_building_device') . " ADD `deviceid` int(10);");
        }
        if (!pdo_fieldexists('xcommunity_building_device', 'secretkey')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_building_device') . " ADD `secretkey` varchar(50);");
        }
        if (!pdo_fieldexists('xcommunity_region', 'accountid')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_region') . " ADD `accountid` int(11);");
        }
        if (!pdo_fieldexists('xcommunity_region', 'villageid')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_region') . " ADD `villageid` int(11);");
        }
        if (!pdo_fieldexists('xcommunity_building_device', 'accountid')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_building_device') . " ADD `accountid` int(11);");
        }
        // 拼车添加完成状态的字段
        if (!pdo_fieldexists('xcommunity_carpool', 'status')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_carpool') . " ADD `status` tinyint(1) default '0';");
        }
        // dihu门禁
        if (!pdo_fieldexists('xcommunity_building_device_cards', 'cardids')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_building_device_cards') . " ADD `cardids` text;");
        }
        if (!pdo_fieldexists('xcommunity_member_room', 'roomid')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_member_room') . " ADD `roomid` int(11);");
        }
        // 删除导航的小区停车
        $zhparkid = pdo_getcolumn('xcommunity_nav', array('do' => 'zhpark'), 'id');
        if ($zhparkid) {
            pdo_query("DELETE FROM " . tablename('xcommunity_nav') . " WHERE do='zhpark' ;");
        }
        // 巡更记录logid、设备号
        if (!pdo_fieldexists('xcommunity_safety_device_log', 'logid')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_safety_device_log') . " ADD `logid` int(11);");
        }
        if (!pdo_fieldexists('xcommunity_safety_device_log', 'device_code')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_safety_device_log') . " ADD `device_code` varchar(50);");
        }
        // 费用数据催缴打印
        if (pdo_tableexists('xcommunity_menu_operation')) {
            $r = pdo_getcolumn('xcommunity_menu_operation', array('id' => 217), 'id');
            if (empty($r)) {
                pdo_query("INSERT INTO" . tablename('xcommunity_menu_operation') . "(`id`, `pcate`, `cate`, `title`, `do`, `method`) VALUE (217,96,0,'催缴打印','cost','cost_detailCall')");
            }
        }
        // 操作日志字段类型
        if (pdo_fieldexists('xcommunity_users_log', 'op')) {
            pdo_query("ALTER TABLE " . tablename('xcommunity_users_log') . " modify column op text ;");
        }
        itoast('操作成功', referer(), 'success', true);
    }
}
/**
 * 后台菜单修复
 */
if ($op == 'menu') {
    if (checksubmit('update_menu')) {
        pdo_query("delete from" . tablename('xcommunity_menu'));
        itoast('操作成功', referer(), 'success', true);
    }
}
include $this->template('web/core/updatedata');