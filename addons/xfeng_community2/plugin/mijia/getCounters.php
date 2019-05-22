<?php
/**
 * Created by njlanniu.com.
 * User: 蓝牛科技
 * Date: 2018/9/21 0021
 * Time: 下午 3:11
 */
/**
 * 实时同步云柜的在线状态
 */
require_once './function.php';
require '../../../../framework/bootstrap.inc.php';
$list = pdo_fetchall("SELECT t1.*,t2.device FROM".tablename('xcommunity_counter_little')."t1 left join" . tablename('xcommunity_counter_main') . "t2 on t1.deviceid=t2.id WHERE 1");
foreach ($list as $k => $v){
    $did = $v['device'].'-'.($v['lock'] + 1);
    $content = getDeviceInfo($did);
    if ($content == 'online'){
        $online = 1;
    }else{
        $online = 0;
    }
    //更新在线状态
    pdo_update('xcommunity_counter_little',array('online' => $online),array('id' => $v['id']));
}