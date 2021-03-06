<?php
/**
 * Created by njlanniu.com.
 * User: 蓝牛科技
 * Time: 2018/9/24 下午3:17
 */
require '../../../../framework/bootstrap.inc.php';
/**
 * 车牌心跳
 */
function getLnStatus($deviceid)
{
    $param = array(
        'identity' => $deviceid,
    );
    $param = json_encode($param);
    $result = http_post('http://122.114.58.8:8018/cp/dev/pullDevstatus.ext', $param);
    $result = @json_decode($result['content'], true);
    return $result['status'][0];
}

/**
 *  设置车牌号白名单
 * $deviceid 设备编号
 * $carnos 车牌号数组
 */
function addWhiteCarnos($deviceid, $carnos)
{
    $params = array(
        'identity' => $deviceid,
        'type' => "2",
        'use' => "1",
        'oper' => "1",
        'list' => $carnos,
    );
    $result = http_post('http://122.114.58.8:8018/cp/ly/setList.ext', json_encode($params));
    $result = @json_decode($result['content'], true);
}

/**
 * 设置车牌号黑名单
 * $deviceid 设备编号
 * $carnos 车牌号数组
 */
function addBlackCarnos($deviceid, $carnos)
{
    $params = array(
        'identity' => $deviceid,
        'type' => "2",
        'use' => "2",
        'oper' => "1",
        'list' => $carnos,
    );
    $result = http_post('http://122.114.58.8:8018/cp/ly/setList.ext', json_encode($params));
    $result = @json_decode($result['content'], true);
}

/**
 * 删除车牌号
 * $deviceid 设备编号
 * $carnos 车牌号数组
 * $use = 1 白名单 $use =2 黑名单
 */
function delCarnos($deviceid, $carnos, $use)
{
    $params = array(
        'identity' => $deviceid,
        'type' => "2",
        'use' => $use,
        'oper' => "3",
        'list' => $carnos,
    );
    $result = http_post('http://122.114.58.8:8018/cp/ly/setList.ext', json_encode($params));
    $result = @json_decode($result['content'], true);
}

/**
 * 获取车牌通行记录
 * limit
 */
function getPassRecords($limit)
{
    $params = array(
        'limit' => $limit,
    );
    $result = http_post('http://122.114.58.8:8018/cp/tcc/pullPassRecords.ext', json_encode($params));
    $result = @json_decode($result['content'], true);
}

/**
 * 设置播放声音
 */
function setPlayVoice($deviceid, $list)
{
    $params = array(
        'identity' => $deviceid,
        'list' => $list,
    );
    $result = http_post('http://122.114.58.8:8018/cp/tcc/.ext', json_encode($params));
    $result = @json_decode($result['content'], true);
}

/**
 * 设置灯箱显示
 */
function setShowText($deviceid, $list)
{
    $params = array(
        'identity' => $deviceid,
        'list' => $list,
    );
    $result = http_post('http://122.114.58.8:8018/cp/tcc/showText.ext', json_encode($params));
    $result = @json_decode($result['content'], true);
}

/**
 * 控制抬杆
 * "list":[{"idx": "1",//第几路，从1开始。"open":"1",//抬杆标志，1抬杆，0落杆}]
 */
function openPole($deviceid, $list)
{
    $params = array(
        'identity' => $deviceid,
        'list' => $list,
    );
    $result = http_post('http://122.114.58.8:8018/cp/tcc/ctrlOpen.ext', json_encode($params));
    $result = @json_decode($result['content'], true);
}

/**
 * 设置摄像机参数
 * "list":[{"idx": "1",//第几路，从1开始。"autoopen":"1",//临时自动抬杆，1自动抬杆，0不自动抬杆}]
 */
function setCamera($deviceid,$list){
    $params = array(
        'identity' => $deviceid,
        'list' => $list,
    );
    $result = http_post('http://122.114.58.8:8018/cp/tcc/setParam.ext', json_encode($params));
    $result = @json_decode($result['content'], true);
}

/**
 * @param $url
 * @param http请求
 * @return mixed
 */
function http_post($url, $data)
{
    $headers = array('Content-Type' => 'text/plain;charset=utf8');
    load()->func('communication');
    return ihttp_request($url, $data, $headers);
}