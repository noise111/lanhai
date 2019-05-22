<?php
/**
 * Created by njlanniu.
 * User: njlanniu
 * Time: 2018/9/19 上午10:15
 */
/**
 * 蓝牛微信开门门禁 3代含以上设备 心跳 1在线 0离线
 */
function getLnStatus3($deviceid)
{
    $data = array(
        'identity' => $deviceid,
    );
    $result = http_post('http://122.114.58.8:8018/dp/dev/pullDevstatus.ext',json_encode($data));
    $result = @json_decode($result['content'], true);
    return $result['status'][0];
}

/**
 * 蓝牛微信开门2代含以下设备 心跳 ok 在线
 */
function getLnStatus2($deviceid)
{
    $url = "http://door.njlanniu.com/cooperation/openlock/servlet.jspx?action=getConnectTime&id=" . $deviceid;
    $content = ihttp_get($url);
    return $content['content'];
}

/**
 * 蓝牛微信开门门禁 3代含以上设备 远程开门
 */
function openLn3($deviceid)
{
    $data = array(
        'open' => 1,
        'identity' => $deviceid
    );
    $result = http_post('http://122.114.58.8:8018/dp/ly/remoteOpenDoor.ext', json_encode($data));
    $result = @json_decode($result['content'], true);
    if ($result['success'] == 1) {
        $content = array(
            'code' => 0,
            'info' => '成功开门',
            'status' => 'ok'
        );
    } else {
        $content = array(
            'code' => 1,
            'info' => '设备离线',
            'status' => 'no'
        );
    }
    return $content;
}

/**
 * 蓝牛微信开门2代含以下设备 远程开门
 */
function openLn2($deviceid)
{
    $data = array(
        'id' => $deviceid,
        'action' => 'open',
        't' => TIMESTAMP
    );
    $url = "http://door.njlanniu.com/cooperation/openlock/servlet.jspx";
    $resp = ihttp_post($url, $data);
    $resp = $resp['content'];
    if ($resp == 'ok') {
        $content = array(
            'code' => 0,
            'info' => '成功开门',
            'status' => 'ok'
        );
    } else {
        $content = array(
            'code' => 1,
            'info' => '设备离线',
            'status' => 'no'
        );
    }
    return $content;
}

/**
 * 新中安微信开门
 */
function openLn4($deviceid)
{
    //使用时需要设置默认的1001房号
    $result = ihttp_request('http://api.njlanniu.cn/addons/lanniu/api.php', array('type' => 'auth', 'deviceid' => $deviceid), null, 5);
    $result = @json_decode($result['content'], true);
    $content = $result['data'];
    return $content;
}

/**
 * NB-IOT微信开门
 */
function openLn5($deviceid)
{
    $data = array(
        'type' => 'door',
        'deviceid' => $deviceid
    );
    $result = ihttp_post('http://193.112.16.186/addons/lanniu/api.php', $data);
    $result = @json_decode($result['content'], true);
    $content = $result['data'];
}

/**
 *  设置卡白名单
 * $deviceid 设备编号
 * $cards 卡号数组
 */
function addWhiteCards($deviceid, $cards)
{
    $data = array(
        'identity' => $deviceid,
        'type' => "1",
        'use' => "1",
        'oper' => "1",
        'list' => $cards,
    );
    $result = http_post('http://122.114.58.8:8018/dp/ly/setList.ext', json_encode($data));
    $result = @json_decode($result['content'], true);
    return $result;
}

/**
 * 设置卡黑名单
 * $deviceid 设备编号
 * $cards 卡号数组
 */
function addBlackCards($deviceid, $cards)
{
    $data = array(
        'identity' => $deviceid,
        'type' => "1",
        'use' => "2",
        'oper' => "1",
        'list' => $cards,
    );
    $result = http_post('http://122.114.58.8:8018/dp/ly/setList.ext', json_encode($data));
    $result = @json_decode($result['content'], true);
    return $result;
}

/**
 * 删除卡号
 * $deviceid 设备编号
 * $cards 卡号数组
 * $use = 1 白名单 $use =2 黑名单
 */
function delCards($deviceid, $cards, $use)
{
    $data = array(
        'identity' => $deviceid,
        'type' => "1",
        'use' => $use,
        'oper' => "3",
        'list' => $cards,
    );
    $result = http_post('http://122.114.58.8:8018/dp/ly/setList.ext', json_encode($data));
    $result = @json_decode($result['content'], true);
    return $result;
}

/**
 * 获取巡更对讲信息
 * limit
 */
function getPatrols($deviceid, $starttime, $endtime, $start, $psize)
{
    $data = array(
        'sn' => $deviceid,
        'starttime' => date('Ymdhms', $starttime),
        'endtime' => date('Ymdhms', $endtime),
        'start' => $start,
        'limit' => $psize
    );
    $params = json_encode($data);
    $result = http_post('http://122.114.58.8:8018/dp/djj/listPatrol.ext', $params);
    $result = @json_decode($result['content'], true);
}

/**
 * 设置楼宇对讲缩位号
 */
function setAbbrnum($deviceid,$room,$mobiles)
{
    $data = array(
        "identity" => $deviceid,
        "flag" => 1,
        "list" => array('abbrnum'=>$room,'tel'=>$mobiles)
    );
    $params = json_encode($data);
    $result = http_post('http://122.114.58.8:8018/dp/ly/setAbbrnum.ext', $params);
    $result = @json_decode($result['content'], true);
}

/**
 * @param $url
 * @param http请求
 * @return mixed
 */
//function http_post($url, $data)
//{
//    $headers = array('Content-Type' => 'text/plain;charset=utf8');
//    load()->func('communication');
//    return ihttp_request($url, $data, $headers);
//}