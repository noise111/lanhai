<?php
/**
 * Created by njlanniu.com.
 * User: 蓝牛科技
 * Date: 2018/9/18 0018
 * Time: 下午 2:19
 */
/**
 * @param $deviceid 设备编号
 * @param $sock 插座号
 * @param $pushtime 时间
 * @return 充电桩上电
 */
function pushPower($deviceid, $sock, $pushtime)
{
    global $_W;
    $url = "https://charger2.avive.cn/devin/charging.php";
    $data = array(
        'device_id' => $deviceid,
        'Control_Channel' => $sock,
        'Control_Sata' => 1,
        'Control_Time' => $pushtime
    );
    load()->func('communication');
    $result = ihttp_post($url,$data);
    return $result;
}
/**
 * @param $deviceid 设备编号
 * @param $sock 插座号
 * @param $pushtime 时间
 * @return 充电桩断电
 */
function downPower($deviceid, $sock, $pushtime)
{
    global $_W;
    $url = "https://charger2.avive.cn/devin/charging.php";
    $data = array(
        'device_id' => $deviceid,
        'Control_Channel' => $sock,
        'Control_Sata' => 0,
        'Control_Time' => $pushtime
    );
    load()->func('communication');
    $result = ihttp_post($url,$data);
    return $result;
}