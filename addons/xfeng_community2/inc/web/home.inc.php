<?php
/**
 * 小区秘书模块
 *
 * [蓝牛科技] Copyright (c) 2013 xqms.cn
 */
/**
 * 后台主页
 */
global $_W, $_GPC;
$user = util::xquser($_W['uid']);
$menus =array();
if($user){
    $menus = explode(',', $user['menus']);
}
$set = pdo_getall('xcommunity_setting',array('uniacid' => $_W['uniacid']),array(),'xqkey',array());
if ($_W['isajax']){
    if ($set['p99']['value']){
        $res = pdo_getcolumn('xcommunity_report',array('status' => 2,'type' => 1,'uniacid' => $_W['uniacid']),'id');
        if($res){
            echo json_encode(array('err_code' => 0));exit();
        }
    }
    echo json_encode(array('err_code' => 1));exit();
}
$start = strtotime(date('Ymd'));
$end = strtotime(date('Ymd')) + 86400 - 1;//晚上11:59
$condition = " t2.uniacid=:uniacid and t2.createtime between {$start} and {$end}";
$par[':uniacid'] = $_W['uniacid'];
if ($user&$user[type] == 3) {
    //普通管理员
    $condition .= " and t2.regionid in({$user['regionid']})";
}
$count_log = pdo_fetchcolumn("select count(distinct t2.uid) from".tablename('xcommunity_member_visit_log')."t2 where $condition",$par);
$count_user = pdo_fetchcolumn("select count(distinct t2.id) from".tablename('xcommunity_member')."t2 left join".tablename('xcommunity_member_bind')."t1 on t2.id =t1.memberid where $condition ",$par);
$count_cost = pdo_fetchcolumn("select count(*) from".tablename('xcommunity_order')."t2 where $condition and t2.type in('pfree', 'bill') and t2.status = 1",$par);
$count_fee = pdo_getcolumn('xcommunity_fee_order', array('uniacid' => $_W['uniacid'], 'status' => 1, 'createtime >=' => $start, 'createtime <=' => $end), 'count(id)');
$count_cost = $count_cost + $count_fee;
$count_open = pdo_fetchcolumn("select count(*) from".tablename('xcommunity_open_log')."t2 where $condition ",$par);
$con ='t2.uniacid=:uniacid';
$params[':uniacid'] =$_W['uniacid'];
if($user&$user['type'] == 3){
    $con .= " and t2.id in({$user['regionid']})";
}
$pcon='';
if($user&$user['type'] == 3){
    $pcon .= " and t2.id in({$user['regionid']})";
}
$count_property = pdo_fetchcolumn("select count(distinct t1.id) from".tablename('xcommunity_property')."t1 left join".tablename('xcommunity_region')."t2 on t1.id = t2.pid where t1.uniacid=:uniacid $pcon",array(':uniacid' => $_W['uniacid']));

$count_region = pdo_fetchcolumn("select count(*) from".tablename('xcommunity_region')."t2 where $con",$params);
$cont ='uniacid=:uniacid';
$param[':uniacid'] =$_W['uniacid'];
if($user&$user['type'] == 3){
    $cont .= " and regionid in({$user['regionid']})";
}
$count_build = pdo_fetchcolumn("select count(*) from".tablename('xcommunity_build')."where $cont",$param);
$count_room = pdo_fetchcolumn("select count(*) from".tablename('xcommunity_member_room')."where $cont",$param);
$count_park = pdo_fetchcolumn("select count(*) from".tablename('xcommunity_parking')."where $cont",$param);
//$count_car = pdo_fetchcolumn("select count(*) from".tablename('xcommunity_xqcars')."where $cont",$param);
// 查询停车的月租车数量
$con_parks = array();
$con_parks['uniacid'] = $_W['uniacid'];
$con_parks['type'] = 1;
if($user && $user['type'] == 3){
    $regionids = explode(',', $user['regionid']);
    $parks = pdo_getall('xcommunity_parks', array('regionid' => $regionids), array('id'));
    $parks_ids = _array_column($parks, 'id');
    $con_parks['parkid'] = $parks_ids;
}
$count_car = pdo_getcolumn('xcommunity_parks_cars', $con_parks, 'count(id)');
$cond = " t2.uniacid=:uniacid ";
$pars[':uniacid'] = $_W['uniacid'];
if ($user&$user[type] == 3) {
    //普通管理员
    $cond .= " and t1.regionid in({$user['regionid']})";
}
//$count_member = pdo_fetchcolumn("select count(*) from".tablename('xcommunity_member')."t2 where $cond and t2.visit=0",$pars);
//$tsql = "select count(*) from" . tablename('mc_members') . "as t2 left join" . tablename('xcommunity_member') . "as t1 on t1.uid=t2.uid left join" . tablename('xcommunity_region') . "as t3 on t1.regionid=t3.id left join" . tablename('xcommunity_member_bind') . "t5 on t5.memberid = t1.id where $cond and t2.mobile <> '' and t5.id <> '' order by t1.id desc ";
unset($condition);
$pindex = max(1, intval($_GPC['page']));
$psize = 20;
$condition['uniacid'] = $_W['uniacid'];
$condition['visit'] = 0;
if ($user&$user[type] == 3) {
    //普通管理员
    $condition['regionid'] = explode(',',$user['regionid']);
}
pdo_getslice('xcommunity_member', $condition, array($pindex, $psize), $count_member, array(), '', array('id DESC'));
//$members = pdo_getall('xcommunity_member',$condition,array('id'));
//$count_member = count($members);
$count_cost_y = pdo_fetchcolumn("select count(*) from".tablename('xcommunity_cost_list')."where $cont and status = 1 ",$param);
$count_cost_w = pdo_fetchcolumn("select count(*) from".tablename('xcommunity_cost_list')."where $cont and status = 2 ",$param);

$count_repair_total = pdo_fetchcolumn("select count(*) from".tablename('xcommunity_report')."where $cont and type =1 ",$param);
$count_repair_w = pdo_fetchcolumn("select count(*) from".tablename('xcommunity_report')."where $cont and type =1 and status = 2",$param);
$count_notice = pdo_fetchcolumn("select count(distinct t2.id) from".tablename('xcommunity_announcement')."t2 left join".tablename('xcommunity_announcement_region')."t1 on t2.id = t1.aid where $cond ",$pars);
$count_home = pdo_fetchcolumn("select count(*) from".tablename('xcommunity_homemaking')."where $cont ",$param);
$count_business = pdo_fetchcolumn("select count(*) from".tablename('xcommunity_dp')."where uniacid=:uniacid ",array(':uniacid' => $_W['uniacid']));
$count_guard = pdo_fetchcolumn("select count(*) from".tablename('xcommunity_building_device')."where $cont ",$param);
$count_fled = pdo_fetchcolumn("select count(*) from".tablename('xcommunity_fled')."where $cont ",$param);
$count_houselease = pdo_fetchcolumn("select count(*) from".tablename('xcommunity_houselease')."where $cont ",$param);
$count_adv = pdo_fetchcolumn("select count(distinct t2.id) from".tablename('xcommunity_slide')."t2 left join".tablename('xcommunity_slide_region')."t1 on t2.id = t1.sid where $cond and (t2.type=2 or t2.type = 3 or t2.type = 4)",$pars);
$count_activity = pdo_fetchcolumn("select count(distinct t2.id) from".tablename('xcommunity_activity')."t2 left join".tablename('xcommunity_activity_region')."t1 on t2.id = t1.activityid where $cond ",$pars);
/**
 * 超市
 */
unset($condition);
if($user['type']==4){
    $condition = " t1.uniacid=:uniacid and t1.type='shopping' and t1.enable=1 and t6.uid=:uid";
    $params[':uniacid'] = $_W['uniacid'];
    $params[':uid'] = $_W['uid'];
    $sql = "select distinct t1.id from" . tablename('xcommunity_order') . "t1 left join" . tablename('xcommunity_order_goods') . "t5 on t5.orderid= t1.id left join" . tablename('xcommunity_goods') . "t6 on t6.id=t5.goodsid where $condition ";
    $orders = pdo_fetchall($sql, $params);
    $count_orders = count($orders);
    $count_goods = pdo_fetchcolumn("select count(*) from".tablename('xcommunity_goods')."where type=1 and uid=:uid",array(':uid'=> $_W['uid']));
}
/**
 * 商家
 */
if($user['type']==5){
    $condition = " t1.uniacid=:uniacid and t1.type='business' and t1.enable=1 and t6.uid=:uid";
    $params[':uniacid'] = $_W['uniacid'];
    $params[':uid'] = $_W['uid'];
    $sql = "select distinct t1.id from" . tablename('xcommunity_order') . "t1 left join" . tablename('xcommunity_order_goods') . "t5 on t5.orderid= t1.id left join" . tablename('xcommunity_goods') . "t6 on t6.id=t5.goodsid where $condition ";
    $orders = pdo_fetchall($sql, $params);
    $count_orders = count($orders);
    $count_goods = pdo_fetchcolumn("select count(*) from".tablename('xcommunity_goods')."where type=2 and uid=:uid",array(':uid'=> $_W['uid']));
}
include $this->template('web/core/home');