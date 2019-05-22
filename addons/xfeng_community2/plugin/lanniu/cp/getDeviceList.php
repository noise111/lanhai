<?php
/**
 * Created by njlanniu.com.
 * User: 蓝牛科技
 * Date: 2018/9/27 0027
 * Time: 下午 4:49
 */
/**
 * 实时同步车辆通行记录
 */
require_once './function.php';
require '../../../../../framework/bootstrap.inc.php';
require '../../../../../addons/xfeng_community/model.php';
/**
 * 查所有的车场设备
 */
$parks = pdo_getall('xcommunity_parks_device',array(),array());
if ($parks){
    foreach ($parks as $k => $v){
        $status = getLnStatus(array($v['identity']));
        $enable = $status == 1 ? 1 : 2;
        pdo_update('xcommunity_parks_device',array('enable' => $enable),array('id' => $v['id']));
    }
}