<?php
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */
define('IN_MOBILE', true);
global $_W, $_GPC;
require_once IA_ROOT . '/framework/bootstrap.inc.php';
require_once IA_ROOT . '/app/common/bootstrap.app.inc.php';
load()->app('common');
load()->app('template');

$sl = $_GPC['ps'];
$params = @json_decode(base64_decode($sl), true);

if ($_GPC['done'] == '1') {
    $sql = 'SELECT * FROM ' . tablename('core_paylog') . ' WHERE `plid`=:plid';
    $pars = array();
    $pars[':plid'] = $params['tid'];
    $log = pdo_fetch($sql, $pars);
    if (!empty($log) && !empty($log['status'])) {
        if (!empty($log['tag'])) {
            $tag = iunserializer($log['tag']);
            $log['uid'] = $tag['uid'];
        }
        $site = WeUtility::createModuleSite($log['module']);

        if (!is_error($site)) {
            $method = 'payResult';
            if (method_exists($site, $method)) {
                $ret = array();
                $ret['uniacid'] = $log['uniacid'];
                $ret['uniacid'] = $log['uniacid'];
                $ret['result'] = 'success';
                $ret['type'] = $log['type'];
                $ret['from'] = 'return';
                $ret['tid'] = $log['tid'];
                $ret['uniontid'] = $log['uniontid'];
                $ret['user'] = $log['openid'];
                $ret['fee'] = $log['fee'];
                $ret['tag'] = $tag;
                $ret['is_usecard'] = $log['is_usecard'];
                $ret['card_type'] = $log['card_type'];
                $ret['card_fee'] = $log['card_fee'];
                $ret['card_id'] = $log['card_id'];
                exit($site->$method($ret));
            }
        }
    }
}
$sql = 'SELECT * FROM ' . tablename('core_paylog') . ' WHERE `plid`=:plid';
$log = pdo_fetch($sql, array(':plid' => $params['tid']));
if (!empty($log) && $log['status'] != '0') {
    exit('这个订单已经支付成功, 不需要重复支付.');
}
$auth = sha1($sl . $log['uniacid'] . $_W['config']['setting']['authkey']);
if ($auth != $_GPC['auth']) {
    exit('参数传输错误.');
}
load()->model('payment');
$_W['uniacid'] = intval($log['uniacid']);
$_W['fans']['from_user'] = intval($log['openid']);
$type = trim($_GPC['type']);
if ($type == 'wechat') {
    //借用微信支付

    $api = pdo_get('xcommunity_wechat', array('userid' => $params['userid'],'type' => $params['type']), array());
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
    $wechat['openid'] = $params['payopenid'];
    $wechat['partner'] = '';
    $wechat['key'] = '';
    $wechat['borrow'] = 3;
    $wechat['sub_mch_id'] = '';
    $params = array(
        'tid'      => $log['tid'],
        'fee'      => $log['card_fee'],
        'user'     => $log['openid'],
        'title'    => urldecode($params['title']),
        'uniontid' => $log['uniontid'],
    );
    $wOpt = xqwechat_build($params, $wechat);
}
if($type == 'sub') {
    $sql = 'SELECT `key`,`secret` FROM ' . tablename('account_wechats') . ' WHERE `acid`=:acid';
    $row = pdo_fetch($sql, array(':acid' => $_W['uniacid']));
    $wechat['account'] = $_W['uniacid'];
    $wechat['switch'] = 1;
    $wechat['version'] = 2;
    $wechat['openid'] = $params['payopenid'];
    $api = pdo_get('xcommunity_service_data', array('userid' => $params['userid'],'type' => $params['type']), array());
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
    $params = array(
        'tid'      => $log['tid'],
        'fee'      => $log['card_fee'],
        'user'     => $log['openid'],
        'title'    => urldecode($params['title']),
        'uniontid' => $log['uniontid'],
    );
    $wOpt = xqwechat_build($params, $wechat);

}
if ($type == 'syswechat') {
    $wechat = $setting['payment']['wechat'];
    $sql = 'SELECT `key`,`secret` FROM ' . tablename('account_wechats') . ' WHERE `acid`=:acid';
    $row = pdo_fetch($sql, array(':acid' => $wechat['account']));
    $wechat['appid'] = $row['key'];
    $wechat['secret'] = $row['secret'];
    $wechat['openid'] = $payopenid;
    $params = array(
        'tid' => $log['tid'],
        'fee' => $log['card_fee'],
        'user' => $log['openid'],
        'title' => urldecode($params['title']),
        'uniontid' => $log['uniontid'],
    );
    unset($wechat['sub_mch_id']);
    $wOpt = wechat_build($params, $wechat);
}


if (is_error($wOpt)) {
    if ($wOpt['message'] == 'invalid out_trade_no' || $wOpt['message'] == 'OUT_TRADE_NO_USED') {
        $id = date('YmdH');
        pdo_update('core_paylog', array('plid' => $id), array('plid' => $log['plid']));
        pdo_query("ALTER TABLE " . tablename('core_paylog') . " auto_increment = " . ($id + 1) . ";");
        itoast("抱歉，发起支付失败，系统已经修复此问题，请重新尝试支付。");
    }
    itoast("抱歉，发起支付失败，具体原因为：“{$wOpt['errno']}:{$wOpt['itoast']}”。请及时联系站点管理员。");
    exit;
}
?>
<script type="text/javascript">
    document.addEventListener('WeixinJSBridgeReady', function onBridgeReady() {
        WeixinJSBridge.invoke('getBrandWCPayRequest', {
            'appId': '<?php echo $wOpt['appId'];?>',
            'timeStamp': '<?php echo $wOpt['timeStamp'];?>',
            'nonceStr': '<?php echo $wOpt['nonceStr'];?>',
            'package': '<?php echo $wOpt['package'];?>',
            'signType': '<?php echo $wOpt['signType'];?>',
            'paySign': '<?php echo $wOpt['paySign'];?>'
        }, function (res) {
            if (res.err_msg == 'get_brand_wcpay_request:ok') {
                // alert(window.location.href );return false;

//                location.search += '&done=1';
                window.location.href += '&done=1';
            } else {
                //alert('启动微信支付失败, 请检查你的支付参数. 详细错误为: ' + res.err_msg);
                history.go(-1);
            }
        });
    }, false);
</script>