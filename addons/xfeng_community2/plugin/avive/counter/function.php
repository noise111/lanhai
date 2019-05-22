<?php
/**
 * Created by njlanniu.
 * User: njlanniu
 * Time: 2018/9/8 上午8:36
 */
function getCounterToken($appid, $secret, $time)
{
    //获取智能货柜的token
    $once = getNum2();
    $strr = $secret . $once . $appid . $time;
    $sign = md5($strr);
    load()->func('communication');
    $url = "http://apis.avive.cn/api.php?act=init&ts={$time}&appid={$appid}&once={$once}&sign={$sign}";
    $result = ihttp_get($url);
    $content = json_decode($result['content'], true);
    $token = $content['result']['token'];
    return $token;
}

function getCounterInfo($appid, $secret, $time, $token, $deviceid)
{
    //获取智能货柜的柜子状态
    $once = getNum2();
    $strr = $secret . $token . $once . $appid . $time . $deviceid;
    $sign = md5($strr);
    load()->func('communication');
    $url = "http://apis.avive.cn/api.php?act=info&ts={$time}&appid={$appid}&once={$once}&sign={$sign}&token={$token}&deviceId={$deviceid}";
    $result = ihttp_get($url);
    $content = json_decode($result['content'], true);
    return $content;
}

function getCounterUnlock($appid, $secret, $time, $token, $deviceid, $lockid, $duration)
{
    //开启智能货柜的小柜子
    $once = getNum2();
    $strr = $secret . $token . $once . $appid . $time . $duration . $deviceid . $lockid;
    $sign = md5($strr);
    load()->func('communication');
    $url = "http://apis.avive.cn/api.php?act=unlock&ts={$time}&appid={$appid}&once={$once}&sign={$sign}&token={$token}&deviceId={$deviceid}&lockId={$lockid}&duration={$duration}";
    $result = ihttp_get($url);
//    $content = json_decode($result['content'], true);
//    return $content;
}

function getCounterLock($appid, $secret, $time, $token, $deviceid, $lockid)
{
    //关闭智能货柜的小柜子
    $once = getNum2();
    $strr = $secret . $token . $once . $appid . $time . $deviceid . $lockid;
    $sign = md5($strr);
    load()->func('communication');
    $url = "http://apis.avive.cn/api.php?act=lock&ts={$time}&appid={$appid}&once={$once}&sign={$sign}&token={$token}&deviceId={$deviceid}&lockId={$lockid}";
    $result = ihttp_get($url);
}
