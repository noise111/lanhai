<?php
/**
 * Created by njlanniu.com.
 * User: 蓝牛科技
 * Date: 2018/9/29 0029
 * Time: 下午 5:46
 */
/**
 * 删除终端过期的门禁发卡
 */
require_once './function.php';
require '../../../../../framework/bootstrap.inc.php';
require '../../../../../addons/xfeng_community/model.php';
$time = time();
//$list = pdo_fetchall("select t1.cardno,t3.device_code,t1.id from" . tablename('xcommunity_building_device_cards') . "t1 left join" . tablename('xcommunity_building_device_cards_category') . "t2 on t1.id=t2.cardid left join" . tablename('xcommunity_building_device') . "t3 on t3.id = t2.categoryid where t1.enddate < :endtime ",array(':endtime' => $time));
//print_r($list);
$condition['enddate <'] = $time;
$list = pdo_getslice('xcommunity_building_device_cards', $condition,array('device_code','cardno','id'));
//print_r($list);
foreach ($list as $k => $v){
    $devices= unserialize($v['device_code']);

    foreach ($devices as $val){
        //删除白名单
        $params = array(
            'identity' => trim($val['device_code']),
            'type' => '1',
            'use' => '1',
            'oper' => '3',
            'list' => array($v['cardno']),
        );
        $r1 = http_post('http://122.114.58.8:8018/dp/ly/setList.ext', json_encode($params));
        //删除黑名单
        $par = array(
            'identity' => trim($val['device_code']),
            'type' => '1',
            'use' => '2',
            'oper' => '3',
            'list' => array($v['cardno']),
        );
        $r2 = http_post('http://122.114.58.8:8018/dp/ly/setList.ext', json_encode($par));
    }

    pdo_update('xcommunity_building_device_cards', array('status' => 2), array('id' => $v['id']));
}
