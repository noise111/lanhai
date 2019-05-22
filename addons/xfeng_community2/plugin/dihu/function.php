<?php
/**
 * Created by njlanniu.com.
 * User: 蓝牛科技
 * Time: 2019/1/19 下午12:19
 */
/**
 * $secret_key //应用的 secret key
 * $method //POST
 * $url url
 * $arrContent //请求参数(包括 POST 的所有参数，不含计算的 sign) * return $sign string
 **/
function genSign($secretkey, $method, $url, $arrContent)
{
    $gather = $method . $url;
    ksort($arrContent);
    foreach ($arrContent as $key => $value) {
        $gather .= $key . '=' . $value;
    }
    $gather .= $secretkey;
    $sign = md5(urlencode($gather));
    return $sign;
}

/**
 * 获取物业ID
 */
function getAccountid($apikey, $secretkey, $username, $password)
{
    global $_W;
    $tm = time();
    $params = array(
        'apikey'    => $apikey,
        'timestamp' => $tm,
        'method'    => 'loginWuyePlatform',
        'username'  => $username,
        'password'  => $password,
    );
    $method = 'POST';
    $url = 'http://wuye.dd121.com/dd/wuye_api/2.0/';
    $sign = genSign($secretkey, $method, $url, $params);
    $params['sign'] = $sign;

    load()->func('communication');
    $result = ihttp_post($url, $params);
    $result = @json_decode($result['content'], true);
    $accountid = $result['response_params']['accountid'];
    return $accountid;

}

/**
 *  添加虚拟小区
 */
function addVillage($apikey, $secretkey, $title, $accountid)
{
    $tm = time();
    $params = array(
        'apikey'      => $apikey,
        'timestamp'   => $tm,
        'method'      => 'addVillage',
        'id'          => $accountid,
        'addr'        => 'abc',
        'detailaddr'  => 'def',
        'villagename' => $title
    );
    $method = 'POST';
    $url = 'http://wuye.dd121.com/dd/wuye_api/2.0/';
    $sign = genSign($secretkey, $method, $url, $params);
    $params['sign'] = $sign;
    load()->func('communication');
    $result = ihttp_post($url, $params);
    $result = @json_decode($result['content'], true);
    $villageid = $result['response_params']['villageid'];
    return $villageid;


}

/**
 * 添加设备
 */
function addDhDevice($apikey, $secretkey, $sn, $devicename, $accountid, $villageid)
{
    $tm = time();
    $params = array(
        'apikey'     => $apikey,
        'timestamp'  => $tm,
        'method'     => 'addDevice',
        'id'         => $accountid,
        'villageid'  => $villageid,
        'devicename' => $devicename,
        'sn'         => $sn
    );
    $method = 'POST';
    $url = 'http://wuye.dd121.com/dd/wuye_api/2.0/';
    $sign = genSign($secretkey, $method, $url, $params);
    $params['sign'] = $sign;
    load()->func('communication');
    $result = ihttp_post($url, $params);
    $result = @json_decode($result['content'], true);
    $deviceid = $result['response_params']['deviceid'];
    load()->func('logging');//记录文本日志
    logging_run(json_encode($result), 'trace', 'xxfeng_community_dihu');
    return $deviceid;
}

/**
 * 添加用户
 */
function addMember($apikey, $secretkey, $deviceid, $roomnumber, $membername, $idnumber, $mobilephone, $accountid)
{
    $tm = time();
    $params = array(
        'apikey'      => $apikey,
        'timestamp'   => $tm,
        'method'      => 'addMember',
        'id'          => $accountid,
        'deviceid'    => $deviceid,
        'roomnumber'  => $roomnumber,
        'membername'  => $membername,
        'idnumber'    => $idnumber,
        'mobilephone' => $mobilephone
    );
    $method = 'POST';
    $url = 'http://wuye.dd121.com/dd/wuye_api/2.0/';
    $sign = genSign($secretkey, $method, $url, $params);
    $params['sign'] = $sign;
    load()->func('communication');
    $result = ihttp_post($url, $params);
    $result = @json_decode($result['content'], true);
    $roomid = $result['response_params']['memberid']['roomid'];
    return $roomid;
}

/**
 * 添加卡
 */
function addCard($apikey, $secretkey, $deviceid, $roomnumber, $password, $cardnumber, $accountid)
{
    $tm = time();
    $params = array(
        'apikey'     => $apikey,
        'timestamp'  => $tm,
        'method'     => 'addCard',
        'id'         => $accountid,
        'deviceid'   => $deviceid,
        'roomnumber' => $roomnumber,
        'password'   => $password,
        'cardnumber' => $cardnumber,
        'cardtype'   => 0
    );
    $method = 'POST';
    $url = 'http://wuye.dd121.com/dd/wuye_api/2.0/';
    $sign = genSign($secretkey, $method, $url, $params);
    $params['sign'] = $sign;
    load()->func('communication');
    $result = ihttp_post($url, $params);
    $result = @json_decode($result['content'], true);
    $cardid = $result['response_params']['cardid'];
    $content = array();
    $content['success'] = $cardid ? 1 : 2;
    $content['cardid'] = $cardid;
    return $content;

}

/**
 * 删除卡
 */
function delCard($apikey, $secretkey, $deviceid, $roomid, $cardid, $accountid)
{
    $tm = time();
    $params = array(
        'apikey'    => $apikey,
        'timestamp' => $tm,
        'method'    => 'delCard',
        'id'        => $accountid,
        'deviceid'  => $deviceid,
        'roomid'    => $roomid,
        'cardid'    => $cardid,
    );
    $method = 'POST';
    $url = 'http://wuye.dd121.com/dd/wuye_api/2.0/';
    $sign = genSign($secretkey, $method, $url, $params);
    $params['sign'] = $sign;
    load()->func('communication');
    $result = ihttp_post($url, $params);
    $result = @json_decode($result['content'], true);
}

/**
 * 远程开门
 */
function open($apikey, $secretkey, $userId, $deviceId)
{
    $tm = time();
    $params = array(
        'apikey'    => $apikey,
        'timestamp' => $tm,
        'id'        => $userId,
        'method'    => 'deviceUnlocking',
        'userId'    => $userId,
        'deviceId'  => $deviceId,
    );
    $method = 'POST';
    $url = 'http://wuye.dd121.com/dd/wuye_api/2.0/';
    $sign = genSign($secretkey, $method, $url, $params);
    $params['sign'] = $sign;
    load()->func('communication');
    $result = ihttp_post($url, $params);
    $result = @json_decode($result['content'], true);
    if ($result['response_params']['result'] == 1) {
        $content = array(
            'code'   => 0,
            'info'   => '成功开门',
            'status' => 'ok'
        );
    }
    else {
        $content = array(
            'code'   => 1,
            'info'   => '设备离线',
            'status' => 'no'
        );
    }
    return $content;
}
/**
 * 获取设备在线
 */
function pullDevice($apikey, $secretkey, $deviceid, $villageid, $accountid)
{
    $tm = time();
    $params = array(
        'apikey'    => $apikey,
        'timestamp' => $tm,
        'method'    => 'getDevice',
        'id'        => $accountid,
        'deviceid'  => $deviceid,
        'villageid' => $villageid
    );
    $method = 'POST';
    $url = 'http://wuye.dd121.com/dd/wuye_api/2.0/';
    $sign = genSign($secretkey, $method, $url, $params);
    $params['sign'] = $sign;
    load()->func('communication');
    $result = ihttp_post($url, $params);
    $result = @json_decode($result['content'], true);
    $online = $result['response_params']['online'];
    return $online;
}
/**
 * 删除设备
 */
function delDevice($apikey, $secretkey, $deviceid, $villageid, $accountid)
{
    $tm = time();
    $params = array(
        'apikey'    => $apikey,
        'timestamp' => $tm,
        'method'    => 'delDevice',
        'id'        => $accountid,
        'deviceid'  => $deviceid,
        'villageid' => $villageid
    );
    $method = 'POST';
    $url = 'http://wuye.dd121.com/dd/wuye_api/2.0/';
    $sign = genSign($secretkey, $method, $url, $params);
    $params['sign'] = $sign;
    load()->func('communication');
    $result = ihttp_post($url, $params);
    $result = @json_decode($result['content'], true);
}
/**
 * 获取小区
 */
function getVillage($apikey, $secretkey, $villageid, $accountid)
{
    $tm = time();
    $params = array(
        'apikey'    => $apikey,
        'timestamp' => $tm,
        'method'    => 'getVillage',
        'id'        => $accountid,
        'villageid' => $villageid
    );
    $method = 'POST';
    $url = 'http://wuye.dd121.com/dd/wuye_api/2.0/';
    $sign = genSign($secretkey, $method, $url, $params);
    $params['sign'] = $sign;
    load()->func('communication');
    $result = ihttp_post($url, $params);
    $result = @json_decode($result['content'], true);
    $villageid = $result['response_params']['villageid'];
    return $villageid;
}