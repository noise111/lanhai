<?php
/*/**
 * Created by njlanniu.com.
 * User: 蓝牛科技
 * Time: 2018/9/11 上午9:49
 */
/**
 * 实时同步充电桩的信号和状态
 */

require_once './function.php';
require '../../../../framework/bootstrap.inc.php';
$list = pdo_getall('xcommunity_charging_station', array('type' => 1), array());
foreach ($list as $item) {
    $content = getRealtime($item['appid'], $item['appsecret'], $item['code']);
    $rssi = $content['data']['cs'][$item['code']]['rt']['rssi'];
    $online = $content['data']['cs'][$item['code']]['online'];
    /**
     * 更新信号 ，不合理
     */
    pdo_update('xcommunity_charging_station', array('rssi' => $rssi, 'enable' => $online), array('id' => $item['id']));
}
/**
 * 更新信号
 */
//pdo_update('xcommunity_charging_station', array('rssi' => $rt['rssi'], 'enable' => $online), array('id' => $item['id']));*/
