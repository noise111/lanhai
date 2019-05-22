<?php
/**
 * Created by xqms.cn.
 * User: 蓝牛科技
 * Time: 2017/8/22 下午9:38
 */
define('IN_MOBILE', true);
require '../../../../framework/bootstrap.inc.php';
$input = file_get_contents('php://input');
libxml_disable_entity_loader(true);
$xmlstring = simplexml_load_string($input, 'SimpleXMLElement', LIBXML_NOCDATA);
$data = json_decode(json_encode($xmlstring), true);
load()->web('common');
load()->classs('coupon');
if(empty($data['status'])){
    $sql = 'SELECT * FROM ' . tablename('core_paylog') . ' WHERE `tid`=:tid';
    $params = array();
    $params[':tid'] = $data['out_trade_no'];
    $log = pdo_fetch($sql, $params);
    $_W['uniacid'] = $_W['uniacid'] = intval($log['uniacid']);
    $_W['uniaccount'] = $_W['account'] = uni_fetch($_W['uniacid']);
    $_W['acid'] = $_W['uniaccount']['acid'];
    if (!empty($log) && empty($log['status']) && (($data['total_fee'] / 100) == $log['card_fee'])) {
        $log['tag'] = iunserializer($log['tag']);
        $log['tag']['transaction_id'] = $data['transaction_id'];
        $log['uid'] = $log['tag']['uid'];
        $record = array();
        $record['status'] = 1;
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
                $ret['transaction_id'] = $data['transaction_id'];
                $ret['trade_type'] = $data['trade_type'];
                $ret['follow'] = $data['is_subscribe'] == 'Y' ? 1 : 0;
                $ret['user'] = empty($data['openid']) ? $log['openid'] : $data['openid'];
                $ret['fee'] = $log['fee'];
                $ret['tag'] = $log['tag'];
                $ret['is_usecard'] = $log['is_usecard'];
                $ret['card_type'] = $log['card_type'];
                $ret['card_fee'] = $log['card_fee'];
                $ret['card_id'] = $log['card_id'];
                if (!empty($data['time_end'])) {
                    $ret['paytime'] = strtotime($data['time_end']);
                }
                $site->$method($ret);
                exit('success');
            }
        }
    }

}
exit('fail');