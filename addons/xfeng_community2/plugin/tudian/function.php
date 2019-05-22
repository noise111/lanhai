<?php
/**
 * Created by njlanniu.
 * User: njlanniu
 * Time: 2018/9/8 上午1:08
 */
/**
 * @param $appid
 * @param $secret
 * @param $csid
 * @return 获取充电桩实时信息
 */
function getRealtime($appid, $secret, $csid)
{
    //充电桩获取信息
    $tm = time();
    $sign = createChargeSign($csid, $appid, $secret, $type = 4);

    $url = "http://api.dpc.jguwen.com/info/realtime?csid=" . $csid . "&appid=" . $appid . "&tm=" . $tm . "&debug=1&sign=" . $sign;
    load()->func('communication');
    $result = ihttp_get($url);
    $content = json_decode($result['content'], true);
    return $content;
}

/**
 * 上电操作
 */
function getPowerUp($appid, $secret, $pid, $seconds)
{
    //充电桩上电
    global $_W;
    $tm = time();
    $sign = createChargeSign($pid, $appid, $secret, $type = 2, $seconds);
    $url = "http://api.dpc.jguwen.com/action/power_up?pid=" . $pid . "&seconds=" . $seconds . "&appid=" . $appid . "&tm=" . $tm . "&debug=1&sign=" . $sign;
    load()->func('communication');
    $result = ihttp_get($url);
    $content = json_decode($result['content'], true);
    $cuid = $content['data']['cuid'];
    $time = date('Ymd', time());
    $path = "../addons/xfeng_community/data/log/charge/temp_" . $_W['uniacid'] . "/" . $time . "/";
    if (!is_dir($path)) {
        load()->func('file');
        mkdirs($path, '0777');
    }
    $file = $path . $cuid . ".txt";
    file_put_contents($file, $result);
    return $cuid;
}

/**
 * @param $appid
 * @param $secret
 * @param $cuid
 * @param 修改充电桩剩余时间
 */
function updatePower($appid, $secret, $cuid, $seconds)
{
    //修改充电剩余时间
    $tm = time();
    $sign = createChargeSign($cuid, $appid, $secret, $type = 1, $seconds);
    $url = "http://api.dpc.jguwen.com/action/change_charge_seconds?cuid=" . $cuid . "&seconds=" . $seconds . "&appid=" . $appid . "&tm=" . $tm . "&debug=1&sign=" . $sign;
    load()->func('communication');
    $result = ihttp_get($url);
    $content = json_decode($result['content'], true);
}

/**
 * @param $appid
 * @param $secret
 * @param $cuid
 * @return 充电桩断电操作
 */
function getPowerDown($appid, $secret, $cuid)
{
    //充电桩断电
    load()->func('communication');
    $tm = TIMESTAMP;
    $sign = createChargeSign($cuid, $appid, $secret, $type = 3);
    $url = "http://api.dpc.jguwen.com/action/power_down?cuid=" . $cuid . "&appid=" . $appid . "&tm=" . $tm . "&debug=1&sign=" . $sign;
    load()->func('communication');
    $result = ihttp_get($url);
    $content = json_decode($result['content'], true);
    $cuid = $content['data']['cuid'];
    return $cuid;
}

/**
 * @param $appid
 * @param $secret
 * @param $cuid
 * @return 获取充电实时详情
 */
function getPowerDetail($appid, $secret, $cuid)
{
    //获取充电详情
    load()->func('communication');
    $tm = TIMESTAMP;
    $sign = createChargeSign($cuid, $appid, $secret, $type = 3);
    $url = "http://api.dpc.jguwen.com/info/get_charge_unit?cuid=" . $cuid . "&appid=" . $appid . "&tm=" . $tm . "&debug=1&sign=" . $sign;
    load()->func('communication');
    $result = ihttp_get($url);
    $content = json_decode($result['content'], true);
    return $content['data'];
}

/**
 * @param $xml
 * @return 生成充电桩签名
 */
function createChargeSign($pid, $appid, $secret, $type, $seconds = 0)
{
    $tm = time();
    $data = array(
        'appid' => $appid,
        'tm' => $tm,
        'debug' => 1
    );
    if ($type == 1) {
        //修改充电剩余时间
        $data['cuid'] = $pid;
        $data['seconds'] = $seconds;
    } elseif ($type == 2) {
        //上电
        $data['pid'] = $pid;
        $data['seconds'] = $seconds;
    } elseif ($type == 3) {
        //断电/获取充电详情
        $data['cuid'] = $pid;
    } elseif ($type == 4) {
        //充电站详情
        $data['csid'] = $pid;
    }
    $sign = createSign3($data, $secret);
    return $sign;
}

/**
 *创建md5摘要,规则是:按参数名称a-z排序,遇到空值的参数不参加签名。
 */
function createSign3($data, $key)
{
    $signPars = "";
    ksort($data);
    foreach ($data as $k => $v) {
        if ("sign" != $k) {
            $signPars .= $k . "=" . $v . "&";
        }
    }
    $signPars = rtrim($signPars, '&');
    $signPars = $signPars . $key;
    $sign = md5($signPars);
    return $sign;
}