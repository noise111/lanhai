<?php
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */
define('IN_MOBILE', true);
require '../../../../framework/bootstrap.inc.php';
$input = file_get_contents('php://input');
$isxml = true;
if (!empty($input) && empty($_GET['out_trade_no'])) {
    $obj = isimplexml_load_string($input, 'SimpleXMLElement', LIBXML_NOCDATA);
    $data = json_decode(json_encode($obj), true);
    if (empty($data)) {
        $result = array(
            'return_code' => 'FAIL',
            'return_msg' => ''
        );
        echo array2xml($result);
        exit;
    }
    if ($data['result_code'] != 'SUCCESS' || $data['return_code'] != 'SUCCESS') {
        $result = array(
            'return_code' => 'FAIL',
            'return_msg' => empty($data['return_msg']) ? $data['err_code_des'] : $data['return_msg']
        );
        echo array2xml($result);
        exit;
    }
    $get = $data;
} else {
    $isxml = false;
    $get = $_GET;
}
load()->web('common');
load()->classs('coupon');
$_W['uniacid'] = $_W['uniacid'] = intval($get['attach']);
$_W['uniaccount'] = $_W['account'] = uni_fetch($_W['uniacid']);
$_W['acid'] = $_W['uniaccount']['acid'];

$sql = 'SELECT * FROM ' . tablename('core_paylog') . ' WHERE `uniontid`=:uniontid';
$pars = array();
$pars[':uniontid'] = $get['out_trade_no'];
$paylog = pdo_fetch($sql, $pars);
$tag = iunserializer($paylog['tag']);
if ($tag['_type']=='wechat') {
    //借用微信支付
    $api = pdo_get('xcommunity_wechat', array('userid' => $tag['userid'],'type' => $tag['type']), array());
    if (empty($api)) {
        exit('未设置支付账号');
        exit();
    }
    $wechat['appid'] = $api['appid'];
    $wechat['secret'] = $api['appsecret'];
    $wechat['mchid'] = $api['mchid'];
    $wechat['signkey'] = $api['apikey'];
    $wechat['apikey'] = $api['apikey'];
    $wechat['switch'] = 2;
    $wechat['account'] = $_W['uniacid'];
    $wechat['version'] = 2;
    $wechat['partner'] = '';
    $wechat['key'] = '';
    $wechat['borrow'] = 3;
    $wechat['sub_mch_id'] = '';
} else {
    $sql = 'SELECT `key`,`secret` FROM ' . tablename('account_wechats') . ' WHERE `acid`=:acid';
    $row = pdo_fetch($sql, array(':acid' => $_W['uniacid']));
    $wechat['account'] = $_W['uniacid'];

    $api = pdo_get('xcommunity_service_data', array('userid' => $tag['userid'],'type' => $tag['type']), array());
    if (empty($api)) {
        exit('未设置支付账号');
        exit();
    }
    if (empty($api['sub_mch_id'])) {
        itoast('子商户未设置', referer(), 'error');
        exit();
    }
    $wechat['appid'] = $api['appid'] ? $api['appid'] : $row['key'];
    $wechat['secret'] = $api['appsecret'] ? $api['appsecret'] : $row['secret'];
    $wechat['sub_mch_id'] = trim($api['sub_mch_id']);

    $wechat['signkey'] = trim($api['apikey']);
    $wechat['mchid'] = trim($api['sub_id']);
}
WeUtility::logging('pay', var_export($get, true));
if (!empty($wechat)) {
    ksort($get);
    $string1 = '';
    foreach ($get as $k => $v) {
        if ($v != '' && $k != 'sign') {
            $string1 .= "{$k}={$v}&";
        }
    }
    $sign = strtoupper(md5($string1 . "key={$wechat['signkey']}"));
    if ($sign == $get['sign']) {
        $sql = 'SELECT * FROM ' . tablename('core_paylog') . ' WHERE `uniontid`=:uniontid';
        $params = array();
        $params[':uniontid'] = $get['out_trade_no'];
        $log = pdo_fetch($sql, $params);
        if (!empty($log) && $log['status'] == '0' && (($get['total_fee'] / 100) == $log['card_fee'])) {
            $log['tag'] = iunserializer($log['tag']);
            $log['tag']['transaction_id'] = $get['transaction_id'];
            $log['uid'] = $log['tag']['uid'];
            $record = array();
            $record['status'] = '1';
            $record['tag'] = iserializer($log['tag']);
            pdo_update('core_paylog', $record, array('plid' => $log['plid']));
            if ($log['is_usecard'] == 1 && !empty($log['encrypt_code'])) {
                $coupon_info = pdo_get('coupon', array('id' => $log['card_id']), array('id'));
                $coupon_record = pdo_get('coupon_record', array('code' => $log['encrypt_code'], 'status' => '1'));
                load()->model('activity');
                $status = activity_coupon_use($coupon_info['id'], $coupon_record['id'], $log['module']);
            }

            $site = WeUtility::createModuleSite($log['module']);
            if (!is_error($site)) {
                $method = 'payResult';
                if (method_exists($site, $method)) {
                    $ret = array();
                    $ret['uniacid'] = $log['uniacid'];
                    $ret['uniacid'] = $log['uniacid'];
                    $ret['acid'] = $log['acid'];
                    $ret['result'] = 'success';
                    $ret['type'] = $log['type'];
                    $ret['from'] = 'notify';
                    $ret['tid'] = $log['tid'];
                    $ret['uniontid'] = $log['uniontid'];
                    $ret['transaction_id'] = $log['transaction_id'];
                    $ret['trade_type'] = $get['trade_type'];
                    $ret['follow'] = $get['is_subscribe'] == 'Y' ? 1 : 0;
                    $ret['user'] = empty($get['openid']) ? $log['openid'] : $get['openid'];
                    $ret['fee'] = $log['fee'];
                    $ret['tag'] = $log['tag'];
                    $ret['is_usecard'] = $log['is_usecard'];
                    $ret['card_type'] = $log['card_type'];
                    $ret['card_fee'] = $log['card_fee'];
                    $ret['card_id'] = $log['card_id'];
                    if (!empty($get['time_end'])) {
                        $ret['paytime'] = strtotime($get['time_end']);
                    }
                    $site->$method($ret);
                    if ($isxml) {
                        $result = array(
                            'return_code' => 'SUCCESS',
                            'return_msg' => 'OK'
                        );
                        echo array2xml($result);
                        exit;
                    } else {
                        exit('success');
                    }
                }
            }
        }
    }
}

if ($isxml) {
    $result = array(
        'return_code' => 'FAIL',
        'return_msg' => ''
    );
    echo array2xml($result);
    exit;
} else {
    exit('fail');
}
