<?php
/**
 * Created by njlanniu.com.
 * User: 蓝牛科技
 * Time: 2018/4/16 下午3:37
 */
global $_GPC, $_W;
$uid = $_SESSION['appuid'];
if (!$uid) {
    util::send_error(-1, '参数错误');
}
$params = $_GPC['params'];
$txpay = trim($params['txpay']);
$txcid = trim($params['txcid']);
$data = array(
    'txpay' => $txpay,
    'txcid' => $txcid,
);

if(pdo_update('xcommunity_users',$data,array('uid'=>$uid))){
    util::send_result();
}