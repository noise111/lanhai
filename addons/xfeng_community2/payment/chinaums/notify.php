<?php
/**
 * Created by xqms.cn.
 * User: 蓝牛科技
 * Time: 2017/8/22 下午9:38
 */
define('IN_MOBILE', true);
require '../../../../framework/bootstrap.inc.php';
if(!empty($_POST)){
    if($_POST['status'] == 'TRADE_SUCCESS'){
        $sql = 'SELECT * FROM ' . tablename('core_paylog') . ' WHERE `uniontid`=:uniontid';
        $params = array();
        $params[':uniontid'] = $_POST['attachedData'];
        $log = pdo_fetch($sql, $params);
        if (!empty($log) && $log['status'] == '0' && (($_POST['totalAmount'] / 100) == $log['card_fee'])) {
            $log['tag'] = iunserializer($log['tag']);
            $log['tag']['transaction_id'] = $_POST['targetOrderId'];
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
exit('fail');