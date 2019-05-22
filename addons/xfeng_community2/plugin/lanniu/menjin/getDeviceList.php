<?php
/**
 * Created by njlanniu.com
 * User: 蓝牛科技
 * Time: 2018/12/20 0020 下午 5:04
 */
require_once './function.php';
require '../../../../../framework/bootstrap.inc.php';
require '../../../../../addons/xfeng_community/model.php';
/**
 * 获取门禁设备的在线状态
 */
$devices = pdo_getall('xcommunity_building_device', array(), array('id', 'device_code', 'category'));
if ($devices) {
    foreach ($devices as $key => $item) {
        $doorstatus = 0;
        if ($item['category'] == 1) {
            $url = "http://door.njlanniu.com/cooperation/openlock/servlet.jspx?action=getConnectTime&id=" . $item['device_code'];
            $content = ihttp_get($url);
            $doorstatus = $content['content'] == 'no' ? 2 : 1;
        }
        else {
            $param = array(
                'identity' => array($item['device_code']),
            );
            $param = json_encode($param);
            $result = http_post('http://122.114.58.8:8018/dp/dev/pullDevstatus.ext', $param);
            $result = @json_decode($result['content'], true);
            $doorstatus = $result['status'][0] == 1 ? 1 : 2;
        }
        pdo_update('xcommunity_building_device', array('doorstatus' => $doorstatus), array('id' => $item['id']));
    }
}