<?php
/**
 * Created by njlanniu.com
 * User: 蓝牛科技
 * Time: 2018/12/22 0022 下午 4:52
 * xxx.com/addons/xfeng_community/plugin/control.php
 */
require '../../../framework/bootstrap.inc.php';
require '../../../addons/xfeng_community/model.php';
require_once './lanniu/menjin/function.php';
require_once './mijia/function.php';
require_once './tudian/function.php';
require_once './wotu/function.php';
require_once './lanniu/cp/function.php';
load()->func('communication');
/**
 * 获取门禁设备的在线状态
 */
$devices = pdo_getall('xcommunity_building_device', array(), array());
$region = pdo_getall('xcommunity_region',array('villageid <>'=>0),array(),'id');
if ($devices) {
    foreach ($devices as $key => $item) {
        $doorstatus = 0;
        if ($item['category'] == 1) {
            $url = "http://door.njlanniu.com/cooperation/openlock/servlet.jspx?action=getConnectTime&id=" . $item['device_code'];
            $content = ihttp_get($url);
            $doorstatus = $content['content'] == 'no' ? 2 : 1;
        }
        elseif ($item['category'] == 9) {
            $content = getDevice($item['appid'], $item['appkey'], $item['appsecret'], $item['device_code']);
            $doorstatus = $content['data']['status'] == 1 ? 1 : 2;
        }
        elseif ($item['category'] == 10) {
            require_once  './dihu/function.php';
            $online = pullDevice($item['apikey'], $item['secretkey'], $item['deviceid'], $region[$item['regionid']]['villageid'],$item['accountid']);
            $doorstatus = $online == true ? 1 : 2;
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
unset($devices);
/**
 * 删除终端过期的门禁发卡
 */
$time = time();
$condition['enddate <'] = $time;
$list = pdo_getslice('xcommunity_building_device_cards', $condition, array('device_code', 'cardno', 'id'));
if ($list) {
    foreach ($list as $k => $v) {
        $devices = unserialize($v['device_code']);
        foreach ($devices as $val) {
            //删除白名单
            $params = array(
                'identity' => trim($val['device_code']),
                'type'     => '1',
                'use'      => '1',
                'oper'     => '3',
                'list'     => array($v['cardno']),
            );
            $r1 = http_post('http://122.114.58.8:8018/dp/ly/setList.ext', json_encode($params));
            //删除黑名单
            $par = array(
                'identity' => trim($val['device_code']),
                'type'     => '1',
                'use'      => '2',
                'oper'     => '3',
                'list'     => array($v['cardno']),
            );
            $r2 = http_post('http://122.114.58.8:8018/dp/ly/setList.ext', json_encode($par));
        }
        pdo_update('xcommunity_building_device_cards', array('status' => 2), array('id' => $v['id']));
    }
}
unset($list);

/**
 * 到期前6天左右加入黑名单--门禁发卡
 */
$time = time() + 6 * 86400;
$condition['enddate <'] = $time;
$list = pdo_getslice('xcommunity_building_device_cards', $condition, array('device_code', 'cardno', 'id'));
if ($list) {
    foreach ($list as $k => $v) {
        $devices = unserialize($v['device_code']);
        foreach ($devices as $val) {
            //加入黑名单
            $cards[0] = array(
                'content'    => $v['cardno'],
                'expiretime' => ''
            );
            $result = addBlackCards($val['device_code'], $cards);
        }
        pdo_update('xcommunity_building_device_cards', array('use' => 2), array('id' => $v['id']));
    }
}
unset($list);
/**
 * 实时同步云柜的在线状态
 */
//$littles = pdo_getall('xcommunity_counter_little', array(), array('deviceid', 'id', 'lock'));
//$mains = pdo_getall('xcommunity_counter_main', array(), array('device'), 'id');print_r($littles);
//foreach ($littles as $k => $v){
//    $did = $mains[$v['deviceid']]['device'].'-'.($v['lock'] + 1);
//    $content = getDeviceInfo($did);
//    if ($content == 'online'){
//        $online = 1;
//    }else{
//        $online = 0;
//    }
//    //更新在线状态
//    pdo_update('xcommunity_counter_little',array('online' => $online),array('id' => $v['id']));
//}

/**
 * 实时同步充电桩的信号和状态
 */
$list = array();
$list = pdo_getall('xcommunity_charging_station', array(), array('id', 'version', 'updatetime', 'type', 'enable', 'appid', 'appsecret', 'code'));
if ($list) {
    foreach ($list as $item) {
        // 途电
        if ($item['type'] == 1) {
            $content = getRealtime($item['appid'], $item['appsecret'], $item['code']);
            $rssi = $content['data']['cs'][$item['code']]['rt']['rssi'];
            $online = $content['data']['cs'][$item['code']]['online'];
            /**
             * 更新信号 ，不合理
             */
            pdo_update('xcommunity_charging_station', array('rssi' => $rssi, 'enable' => $online), array('id' => $item['id']));
        }
        // 威威充
        if ($item['type'] == 2) {
            $enable = $item['enable'];
            if ($item['version'] == 0) {
                if ($item['updatetime'] < (time() - 7200)) {
                    $enable = 0;
                }
                else {
                    $enable = 1;
                }
            }
            if ($item['version'] == 1) {
                if ($item['updatetime'] < (time() - 600)) {
                    $enable = 0;
                }
                else {
                    $enable = 1;
                }
            }
            pdo_update('xcommunity_charging_station', array('enable' => $enable), array('id' => $item['id']));
        }
    }
}
unset($list);
/**
 * 实时监测功率、查询按量计费
 */
$orders = array();
$sql = "select t1.*,t3.quanrule,t4.chargecredit,t2.appid,t2.appsecret from" . tablename('xcommunity_charging_order') . "t1 left join" . tablename('xcommunity_charging_station') . "t2 on t2.code=t1.code left join" . tablename('xcommunity_charging_throw') . "t3 on t3.id=t2.tid left join" . tablename('mc_members') . "t4 on t4.uid=t1.uid where t1.type=1 and t1.endtime=0";
$orders = pdo_fetchall($sql);
if ($orders) {
    foreach ($orders as $order) {
        //按量计费
        /**
         * 需要判断当前用户余额是否大于用户选择的时间
         */
        //查功率
        $data = getPowerDetail($order['appid'], $order['appsecret'], $order['cuid']);
        $cuid = explode("_", $order['cuid']);
        $csid = strlen($cuid[0]) == 6 ? $cuid[0] : '00' . $cuid[0];
        $cuid = $csid . '_' . $cuid[1] . '_' . $cuid[2];
        $result = $result['cu'][$cuid];
        $powers = $result['powers'];
        $power = $powers[0];//随机功率
        $usetime = sprintf("%.2f", ($result['sec'] / 60));
        $rules = unserialize($order['quanrule']);
        $fee = 0;//计费费用
        if ($power <= $rules[1]['power']) {
            $fee = $rules[1]['price'];
        }
        elseif ($power > $rules[1]['power'] && $power <= $rules[2]['power']) {
            $fee = $rules[2]['price'];
        }
        elseif ($power > $rules[2]['power'] && $power <= $rules[3]['power']) {
            $fee = $rules[3]['price'];
        }
        $sjfee = $usetime * ($fee / 60);//实际消费费用
        if ($order['chargecredit'] <= 0) {
            //判断是否欠费,欠费断电
            getPowerDown($order['appid'], $order['appsecret'], $order['cuid']);
            $message = array(
                'msgtype' => 'text',
                'text'    => array('content' => '您已欠费，请先充值后，在充电。'),
                'touser'  => $order['openid'],
            );
            $account_api = WeAccount::create();
            $status = $account_api->sendCustomNotice($message);
            exit();
        }
        if ($order['chargecredit'] < $sjfee) {
            //修改实际充电时间
            $seconds = ($order['chargecredit'] / $fee) * 3600 - $result['sec'];
            $content = updatePower($order['appid'], $order['appsecret'], $order['cuid'], $seconds);
        }
    }
}
unset($orders);
/**
 * 同步巡更记录
 */
$devices = array();
$devices = pdo_getall('xcommunity_safety', array(), array('id', 'device_code', 'uniacid'), 'id');
$devices_codes = _array_column($devices, 'device_code');
if ($devices) {
    $starttime = date("YmdHis", strtotime("-5 minute"));
    $endtime = date("YmdHis");
    $psize = 100;
    $start = 0;
    foreach ($devices as $key => $val) {
        $data = array(
            'sn'        => $val['device_code'],
            'starttime' => $starttime,
            'endtime'   => $endtime,
            'start'     => $start,
            'limit'     => $psize
        );
        $params = json_encode($data);
        $result = http_post('http://122.114.58.8:8018/dp/djj/listPatrol.ext', $params);
        $result = @json_decode($result['content'], true);
        $list = $result['list'];
        $sn = $result['sn'];// 设备序列号
        if ($list) {
            $values = '';
            $uniacid = $val['uniacid'];
            $safetyid = $val['id'];
            foreach ($list as $k => $v) {
                $lng = $v['longitude'];
                $lat = $v['latitude'];
                $acq_date = strtotime($v['acq_date']);
                $card_num = $v['card_num'];
                $createtime = TIMESTAMP;
                $logid = $v['id'];
                $values .= "($uniacid,$safetyid,'{$lng}','{$lat}',$acq_date,'{$card_num}',$createtime,$logid,'{$sn}'),";
            }
            $sql = "insert into " . tablename('xcommunity_safety_device_log') . "(uniacid,safetyid,lng,lat,acq_date,card_num,createtime,id,device_code) values";
            $sqlInto = rtrim($sql . $values, ',');
            pdo_query($sqlInto);
        }
        unset($data);
    }
}
unset($devices);
unset($list);
/**
 * 同步门禁刷卡记录
 */
$devices = array();
$devices = pdo_getall('xcommunity_building_device', array('category' => array(4, 7)), array('device_code', 'uniacid'));
$doorDevices = _array_column($devices, 'device_code');
if ($devices) {
    $psize = 50;
    $start = 0;
    $starttime = date("YmdHis", strtotime("-1 day"));
    $endtime = date("YmdHis");
    foreach ($devices as $key => $val) {
        $doorData = array(
            'identity'  => $val['device_code'],
            'starttime' => $starttime,
            'endtime'   => $endtime,
            'start'     => $start,
            'limit'     => $psize
        );
        $params = json_encode($doorData);
//        print_r($doorData);
        $result = http_post('http://122.114.58.8:8018/dp/mj/queryOpenRecords.ext ', $params);
//        print_r($result);
    }
//    exit();
}
//$doorData = array(
//    'limit' => $psize
//);
//$result = http_post('http://122.114.58.8:8018/dp/ly/pullOpenrecord.ext ', $params);
//$result = @json_decode($result['content'], true);
//$list = $result['list'];
//foreach ($list as $val) {
//    $data = array(
//        'identity'  => $device['device_code'],
//        'starttime' => date('YmdHis', $starttime),
//        'endtime'   => date('YmdHis', $endtime),
//        'start'     => $start,
//        'limit'     => $psize
//    );
//    $param = json_encode($data);
//    $result = http_post('http://122.114.58.8:8018/cp/mj/queryOpenRecords.ext', $param);
//    $result = @json_decode($result['content'], true);
//    $list = $result['list'];
//    if ($list) {
//        //循环插入到数据表
//        $sql = 'insert into ' . tablename('xcommunity_building_device_log') . ' ("unaicid","device_code","cardno","sktime","createtime","uid") values';
//        $uniacid = $device['uniacid'];
//        $devices_code = $device['device_code'];
//        foreach ($list as $k => $v) {
//            $cardno = $v['cardno'];
//            $sktime = $v['time'];
//            $time = TIMESTAMP;
//            $uid = 0;
//            $sql = $sql . "($uniacid,$devices_code,$cardno,$sktime,$time,$uid),";
//        }
//        pdo_query($sql);
//    }
//    if (in_array($val['identity'], $doorDevices)) {
//        $dat = array(
//            'uniacid'     => $uniacid,
//            'device_code' => $val['identity'],
//            'cardno'      => $val['cardNo'],
//            'type'        => $val['type'],
//            'sktime'      => strtotime($val['time']),
//            'createtime'  => TIMESTAMP
//        );
//        pdo_insert('xcommunity_building_device_log', $dat);
//        unset($dat);
//    }
//}

/**
 * 监控微信公众号粉丝取消关注后删除人脸授权
 */
// 查询是否有开启粉丝取消关注后删除该粉丝人脸授权
$settings = pdo_getall('xcommunity_setting', array('xqkey' => 'p165'), array('uniacid', 'value'), 'uniacid');
if ($settings) {
    // 未关注的粉丝
    $members = array();
    $members = pdo_getall('mc_mapping_fans', array('follow' => 0), array('uid'));
    $members_uids = _array_column($members, 'uid');
    // 未关注粉丝的人脸授权
    $faceUsers = pdo_getall('xcommunity_face_users', array('uid' => $members_uids), array('guid', 'deviceids', 'id', 'uniacid'));
    // 人脸门禁
    $faceDevices = pdo_getall('xcommunity_building_device', array('category' => 9), array('device_code', 'appid', 'appkey', 'appsecret'));
    $appid = $faceDevices[0]['appid'];
    $appkey = $faceDevices[0]['appkey'];
    $appsecret = $faceDevices[0]['appsecret'];
    foreach ($faceUsers as $k => $v) {
        if ($settings[$v['uniacid']]['value'] == 1) {
            deletePerson($appid, $appkey, $appsecret, $v['guid']);// 删除终端的人员信息
            pdo_delete('xcommunity_face_users', array('id' => $v['id']));// 删除数据库人员信息
        }
    }
}
unset($settings);

/**
 * 查所有的车场设备在线状态
 */
$devices = array();
$devices = pdo_getall('xcommunity_parks_device', array(), array());
if ($devices) {
    foreach ($devices as $k => $v) {
        $status = getLnStatus(array($v['identity']));
        $enable = $status == 1 ? 1 : 2;
        pdo_update('xcommunity_parks_device', array('enable' => $enable), array('id' => $v['id']));
    }
    unset($devices);
}

/**
 * 停车超时未支付订单
 */
$orders = pdo_getall('xcommunity_parks_order', array('status' => 0, 'category' => 2), array('createtime', 'identity', 'parkid'));
// 车场
$parks = pdo_getall('xcommunity_parks', array(), array('id', 'setting'), 'id');
if ($orders) {
    foreach ($orders as $k => $v) {
        $setting = unserialize($parks[$v['parkid']]['setting']);
        if ($v['createtime'] > (TIMESTAMP - 60 * $setting['opentime'])) {
            $list = array(
                array('idx' => 2, 'open' => 1)
            );
            $result = openPole($v['identity'], $list);
        }
    }
}
