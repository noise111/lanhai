<?php
/**
 * Created by njlanniu.com.
 * User: 蓝牛科技
 * Date: 2018/9/27 0027
 * Time: 下午 2:35
 */
/**
 * 实时同步车辆通行记录
 */
require_once './function.php';
require '../../../../../framework/bootstrap.inc.php';
require '../../../../../addons/xfeng_community/model.php';
/**
 * 查所有的通行记录
 */
$passlogs = pdo_getall('xcommunity_parks_passlog',array(),array());
$passlogs_ids = _array_column($passlogs,NULL ,'logid');
/**
 * 查所有的车场设备
 */
$devices = pdo_getall('xcommunity_parks_device',array(),array());
$devices_ids = _array_column($devices,NULL, 'identity');
$content = getPassRecords(50);
if ($content['isFinish'] == 1){
    $list = $content['list'];
    foreach ($list as $k => $v){
        $data = array(
            'uniacid'   => $devices_ids[$v['identity']]['uniacid'],
            'identity'  => $v['identity'],
            'createtime'    => strtotime($v['time']),
            'carno'     => $v['carno'],
            'idx'       => $v['idx'],
            'type'      => $v['type'],
            'logid'     => $v['id'],
            'parkid'    => $devices_ids[$v['identity']]['parkid']
        );
        if (empty($passlogs_ids[$v['id']])){
            pdo_insert('xcommunity_parks_passlog',$data);
        }
    }
}
