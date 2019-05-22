2<?php
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */
define('IN_MOBILE', true);
require '../../../../framework/bootstrap.inc.php';
$input = file_get_contents('php://input');
if (!empty($input)) {
    $obj = json_decode($input, true);
//    file_put_contents('../../../../addons/xfeng_community/payment/hsyunfu/test1.txt', $obj);
    if (empty($obj)) {
        $result = array(
            'return_code' => 'FAIL',
            'return_msg'  => ''
        );
        echo array2xml($result);
        exit;
    }
    if (!empty($obj['result_code']) && !empty($obj['status'])) {
        $result = array(
            'return_code' => 'FAIL',
            'return_msg'  => ''
        );
        echo array2xml($result);
        exit;
    }
    $sql = 'SELECT * FROM ' . tablename('core_paylog') . ' WHERE `tid`=:tid';
    $params = array();
    $params[':tid'] = $obj['out_trade_no'];
    $log = pdo_fetch($sql, $params);
    if (!empty($log) && $log['status'] == '0' && (($obj['total_fee'] / 100) == $log['card_fee'])) {
        $log['tag'] = iunserializer($log['tag']);
        $log['tag']['transaction_id'] = $obj['out_transaction_id'];
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
                $ret['transaction_id'] = $log['tag']['transaction_id'];
                $ret['trade_type'] = $obj['trade_type'];
                $ret['follow'] = $obj['is_subscribe'] == 'Y' ? 1 : 0;
                $ret['user'] = empty($obj['openid']) ? $log['openid'] : $obj['openid'];
                $ret['fee'] = $log['fee'];
                $ret['tag'] = $log['tag'];
                $ret['is_usecard'] = $log['is_usecard'];
                $ret['card_type'] = $log['card_type'];
                $ret['card_fee'] = $log['card_fee'];
                $ret['card_id'] = $log['card_id'];
                if (!empty($obj['time_end'])) {
                    $ret['paytime'] = strtotime($obj['time_end']);
                }
                $site->$method($ret);
                if ($isxml) {
                    $result = array(
                        'return_code' => 'SUCCESS',
                        'return_msg'  => 'OK'
                    );
                    echo array2xml($result);
                    exit;
                }
                else {
                    exit('success');
                }
            }
        }
    }
}
else {
    exit('fail');
}


