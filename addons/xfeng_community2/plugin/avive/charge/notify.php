<?php
/**
 * Created by njlanniu.com.
 * User: 蓝牛科技
 * Date: 2018/9/18 0018
 * Time: 下午 2:03
 */
define('IN_MOBILE', true);
require '../../../../../framework/bootstrap.inc.php';
require_once IA_ROOT . '/addons/xfeng_community/model.php';
require_once './function.php';
/**
 * 充电桩信息
 */
$post = json_encode($_POST);
$result = json_decode($post, true);
$type = $result['notifyType'];
$d_code = $result['deviceId'];
if ($type == 'deviceDatasChanged') {
    $version = $result['services'][0]['data']['C_VRRP'];
    $updatetime = strtotime($result['services'][0]['eventTime']);
    pdo_update('xcommunity_charging_station', array('version' => $version, 'updatetime' => $updatetime), array('code' => $d_code));
}
if ($type == 'deviceDataChanged') {
    $status = $result['service']['data']['ChargingPile_Sata'];
    $stop = $result['service']['data']['ChargingPile_StopCode'];
    $s_code = $result['service']['data']['ChargingPile_Channel'];
    $power = $result['service']['data']['ChargingPile_W'] ? $result['service']['data']['ChargingPile_W'] : 0;
    $sign = $result['service']['data']['RSSI'];
    $cptime = $result['service']['data']['ChargingPile_time'] > 10000 ? 0 : $result['service']['data']['ChargingPile_time'];
    $eventTime = strtotime($result['service']['eventTime']);
}
$socket = $s_code - 1;
/**
 * 查询充电桩订单信息
 */
$order = pdo_fetch("select t1.starttime,t1.id,t1.price,t1.cdtime,t1.uniacid,t1.createtime,t1.uid,t1.type,t2.openid,t3.id as stationid,t3.address,t3.line,t4.quanrule,t1.isedit,t5.chargecredit,t1.code,t1.socket,t1.orderid,t1.regionid,t3.zscode from" . tablename('xcommunity_charging_order') . "t1 left join" . tablename('mc_mapping_fans') . "t2 on t1.uid=t2.uid left join" . tablename('xcommunity_charging_station') . "t3 on t1.code=t3.code left join" . tablename('xcommunity_charging_throw') . "t4 on t4.id=t3.tid left join" . tablename('mc_members') . "t5 on t5.uid=t1.uid where t1.code=:code and t1.socket=:socket order by t1.createtime desc", array(':code' => $d_code, ':socket' => $socket));
$uniacid = $order['uniacid'];
$openid = $order['openid'];
if ($sign) {
    /**
     * 修改充电桩信号
     */
    pdo_update('xcommunity_charging_station', array('rssi' => $sign), array('code' => $d_code));
}
/**
 * 上电成功
 */
if ($status == '1') {
    /**
     * 修改充电时间
     */
    if ($order['type'] == 1 && $order['isedit'] == 0) {
        $usetime = sprintf("%.2f", ($cptime / 60));
        $rules = unserialize($order['quanrule']);
        $fee = 0;//计费费用
        $minfee = 0;// 最低消费
        $mintime = 0;// 最低消费时间
        if ($power <= $rules[1]['power']) {
            $fee = $rules[1]['price'];
            $minfee = $rules[1]['minprice'] ? $rules[1]['minprice'] : 0;
            $mintime = $rules[1]['mintime'] ? $rules[1]['mintime'] : 0;
        } elseif ($power > $rules[1]['power'] && $power <= $rules[2]['power']) {
            $fee = $rules[2]['price'];
            $minfee = $rules[2]['minprice'] ? $rules[2]['minprice'] : 0;
            $mintime = $rules[2]['mintime'] ? $rules[2]['mintime'] : 0;
        } elseif ($power > $rules[2]['power'] && $power <= $rules[3]['power']) {
            $fee = $rules[3]['price'];
            $minfee = $rules[3]['minprice'] ? $rules[3]['minprice'] : 0;
            $mintime = $rules[3]['mintime'] ? $rules[3]['mintime'] : 0;
        }
        if ($minfee > 0 && $mintime) {
            if ($usetime <= ($mintime * 60)) {
                $sjfee = $minfee;
            } else {
                $difftime = $usetime - $mintime * 60;
                $sjfee = $minfee + $difftime * ($fee / 60);
            }
        } else {
            $sjfee = $usetime * ($fee / 60);//实际消费费用
        }
        if ($order['chargecredit'] <= 0) {
            //判断是否欠费,欠费断电
            $socket = (string)$order['socket'] + 1;
            downPower($order['code'], $socket, 0);
            $message = array(
                'msgtype' => 'text',
                'text' => array('content' => '您已欠费，请先充值后，在充电。'),
                'touser' => $order['openid'],
            );
            $account_api = WeAccount::create();
            $status = $account_api->sendCustomNotice($message);
            exit();
        }
        if ($order['chargecredit'] < $sjfee) {
            //修改实际充电时间
            if ($minfee > 0) {
                if ($order['chargecredit'] <= $minfee) {
                    $seconds = $mintime * 60;
                } else {
                    $difffee = $order['chargecredit'] - $minfee;
                    $seconds = $mintime * 60 + ($difffee / $fee) * 60;
                }
            } else {
                $seconds = ($order['chargecredit'] / $fee) * 60;
            }

            $socket = (string)$order['socket'] + 1;
            pdo_update('xcommunity_charging_order', array('isedit' => 2, 'power' => $power, 'cdtime' => $seconds, 'price' => $order['chargecredit']), array('id' => $order['id']));
            $resdown = downPower($order['code'], $socket, 0);
            if ($resdown['status'] == 'OK') {
                pushPower($order['code'], $socket, $seconds);
            }
            exit();
        }
    }
    /**
     * 修改插座状态为占用
     */
    pdo_update('xcommunity_charging_socket', array('enable' => 1), array('lock' => $socket, 'stationid' => $order['stationid']));
    pdo_update('xcommunity_charging_order', array('starttime' => $eventTime, 'power' => $power, 'isedit' => 1), array('id' => $order['id']));
    $tplid = set('p144', '', $uniacid);
    if($order['type']==1){
        //按量计费
        $first ='【开始充电】 本次充电套餐：按量扣费，充满或预存不足自停，'. $order['cdtime'] . '分钟后未充满则自动停止充电与扣费。';
    }else{
        $first = '【开始充电】 本次充电套餐：' . $order['price'] . '元' . $order['cdtime'] . '分钟';
    }
    $k1 = $order['address'];
    $d_code = $order['zscode'] ? $order['zscode']:$d_code;
    $k2 = $d_code . '_' . $s_code;
    $k3 = $s_code . '号插座';
    $k4 = date('Y年m月d日H:i', $order['createtime']);
    $remark = '【温馨提示】 充电过程中，出行下列情况可能会停止充电:  1.充电器被拔出  2.电池已充满  3.充电功率过大  4.充电时间过长  5.系统检测到火灾';
    sendTpl($uniacid, $openid, $tplid, $first, $k1, $k2, $k3, $k4, $remark);
}
/**
 * 充电结束
 */
if ($status == '0') {
    /**
     * 修改插座状态为空闲
     */
    $time = TIMESTAMP;
    if ($order['type'] == 1) {
        if ($order['isedit'] == 2) {
            exit();
        }
        //按量计费
        $rules = unserialize($order['quanrule']);
        $fee = 0;//计费费用
        $minfee = 0;// 最低费用
        $mintime = 0;// 最低消费时间
        if ($power <= $rules[1]['power']) {
            $fee = $rules[1]['price'];
            $minfee = $rules[1]['minprice'] ? $rules[1]['minprice'] : 0;
            $mintime = $rules[1]['mintime'] ? $rules[1]['mintime'] : 0;
        } elseif ($power > $rules[1]['power'] && $power <= $rules[2]['power']) {
            $fee = $rules[2]['price'];
            $minfee = $rules[2]['minprice'] ? $rules[2]['minprice'] : 0;
            $mintime = $rules[2]['mintime'] ? $rules[2]['mintime'] : 0;
        } elseif ($power > $rules[2]['power'] && $power <= $rules[3]['power']) {
            $fee = $rules[3]['price'];
            $minfee = $rules[3]['minprice'] ? $rules[3]['minprice'] : 0;
            $mintime = $rules[3]['mintime'] ? $rules[3]['mintime'] : 0;
        }
        $cptime = sprintf("%.2f",($eventTime - $order['starttime']) / 60);
        if ($minfee > 0 && $mintime) {
            if ($cptime <= ($mintime * 60)) {
                $sjfee = $minfee;
            } else {
                $difftime = $cptime - $mintime * 60;
                $sjfee = $minfee + $difftime * ($fee / 60);
            }
        } else {
            $sjfee = $cptime * ($fee / 60);//实际消费费用
        }
        $sjfee = sprintf("%.2f", $sjfee);
        pdo_update('mc_members', array('chargecredit -=' => $sjfee), array('uid' => $order['uid']));//修改用户余额
        $member = pdo_get('mc_members', array('uid' => $order['uid']), array('realname', 'mobile'));
        $ordesn = pdo_getcolumn('xcommunity_order', array('id' => $order['orderid']), 'ordersn');
        $creditdata = array(
            'uid' => $order['uid'],
            'uniacid' => $order['uniacid'],
            'realname' => $member['realname'],
            'mobile' => $member['mobile'],
            'content' => "订单号：" . $ordesn . ",充电桩:" . $order['code'] . "_" . ($order['socket'] + 1),
            'credit' => $sjfee,
            'creditstatus' => 2,
            'createtime' => TIMESTAMP,
            'type' => 7,
            'typeid' => $order['regionid'],
            'category' => 7,
            'usename' => $member['realname']
        );
        pdo_insert('xcommunity_credit', $creditdata);
        pdo_update('xcommunity_order', array('price' => $sjfee, 'status' => 1), array('id' => $order['orderid']));//修改订单金额
    } else {
        $sjfee = $order['price'];
    }
    pdo_update('xcommunity_charging_socket', array('enable' => 0), array('stationid' => $order['stationid'], 'lock' => $socket));
    pdo_update('xcommunity_charging_order', array('endtime' => $eventTime, 'stime' => $cptime, 'power' => $power, 'price' => $sjfee), array('id' => $order['id']));
    if ($stop == '1') {
        $err_msg = '【充电完成】您本次充电已完成！';
        $remark = '充电完成，感谢您的使用！';
    }
    if ($stop == '2') {
        $err_msg = '【充电异常】充电器断开连接或已充满！';
        $remark = '请及时检查，以免影响出行！';
    }
    if ($stop == '3') {
        $err_msg = '【充电异常】充电功率过大！';
        $remark = '请及时检查，以免影响出行！';
    }
    if ($stop == '4') {
        $err_msg = '【充电异常】充电时间过长！';
        $remark = '请及时检查，以免影响出行！';
    }
    if ($stop == '5') {
        $err_msg = '【充电异常】系统检测到火灾！';
        $remark = '请及时检查，以免影响出行！';
    }
    $tplid = set('p145', '', $uniacid);
    $first = $err_msg;
    $k1 = $order['address'];
    $k2 = date('Y-m-d H:i:s', $order['createtime']) . '至' . date('Y-m-d H:i:s', $time);
    $k3 = $cptime . '分钟';
    $k4 = $sjfee . '元';
    sendTpl($uniacid, $openid, $tplid, $first, $k1, $k2, $k3, $k4, $remark);
}
/**
 * 改变插座状态
 */
for ($i = 1; $i <= $order['line']; $i++) {
    $Channel = 'Channel_' . $i;
    if ($type == 'deviceDataChanged') {
        $Channel_status = $result['service']['data'][$Channel];
    } else {
        $Channel_status = $result['services'][0]['data'][$Channel];
    }
    $j = $i - 1;
    if ($Channel_status == '0') {
        pdo_update('xcommunity_charging_socket', array('enable' => 0), array('lock' => $j, 'stationid' => $order['stationid']));
    } elseif ($Channel_status == '2') {
        pdo_update('xcommunity_charging_socket', array('enable' => 2), array('lock' => $j, 'stationid' => $order['stationid']));
    }
}
function sendTpl($uniacid, $openid, $tplid, $first, $k1, $k2, $k3, $k4, $remark)
{
    load()->classs('weixin.account');
    $account = pdo_get('account', array('uniacid' => $uniacid));
    $account_api = WeAccount::create($account['acid']);
    $data = array(
        'first' => array(
            'value' => $first
        ),
        'keyword1' => array(
            'value' => $k1,
        ),
        'keyword2' => array(
            'value' => $k2,
        ),
        'keyword3' => array(
            'value' => $k3,
        ),
        'keyword4' => array(
            'value' => $k4,
        ),
        'remark' => array(
            'value' => $remark,
        )
    );
    $status = $account_api->sendTplNotice($openid, $tplid, $data, $url = '');
}