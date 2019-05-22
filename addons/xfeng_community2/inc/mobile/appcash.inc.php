<?php
/**
 * Created by njlanniu.
 * User: njlanniu
 * Time: 2018/3/22 上午8:53
 */
defined('IN_IA') or exit('Access Denied');
global $_W, $_GPC;
$params = @json_decode(base64_decode($_GPC['params']), true);
if (empty($params)) {
    message('访问错误.');
}
$setting = uni_setting($_W['uniacid'], 'payment');
if (empty($setting['payment']['wechat']['switch'])) {
    message('支付方式错误,请联系管理员.');
}

$sql = 'SELECT * FROM ' . tablename('core_paylog') . ' WHERE `uniacid`=:uniacid AND `module`=:module AND `tid`=:tid';
$pars = array();
$pars[':uniacid'] = $_W['uniacid'];
$pars[':module'] = $params['module'];
$pars[':tid'] = $params['tid'];
$log = pdo_fetch($sql, $pars);
if (!empty($log) && (!empty($_GPC['notify'])) && $log['status'] != '0') {
    message('这个订单已经支付成功, 不需要重复支付.');
}
$update_card_log = array(
    'is_usecard' => '0',
    'card_type'  => '0',
    'card_id'    => '0',
    'card_fee'   => $log['fee'],
    'type'       => $type,
);
pdo_update('core_paylog', $update_card_log, array('plid' => $log['plid']));
$log['is_usecard'] = '0';
$log['card_type'] = '0';
$log['card_id'] = '0';
$log['card_fee'] = $log['fee'];

$moduleid = pdo_fetchcolumn("SELECT mid FROM " . tablename('modules') . " WHERE name = :name", array(':name' => $params['module']));
$moduleid = empty($moduleid) ? '000000' : sprintf("%06d", $moduleid);

$record = array();
$record['type'] = $type;
if (empty($log['uniontid'])) {
    $record['uniontid'] = $log['uniontid'] = date('YmdHis') . $moduleid . random(8, 1);
}

if (empty($log)) {
    message('系统支付错误, 请稍后重试.');
}
else {
    pdo_update('core_paylog', $record, array('plid' => $log['plid']));
    if (!empty($log['uniontid']) && $record['card_fee']) {
        $log['card_fee'] = $record['card_fee'];
        $log['card_id'] = $record['card_id'];
        $log['card_type'] = $record['card_type'];
        $log['is_usecard'] = $record['is_usecard'];
    }
}

$ps = array();
$ps['tid'] = $log['plid'];
$ps['uniontid'] = $log['uniontid'];
$ps['user'] = $_W['fans']['from_user'];
$ps['fee'] = $log['card_fee'];
$ps['title'] = $params['title'];

$payopenid = $_GPC['payopenid'];
$setting = uni_setting($_W['uniacid'], array('payment', 'recharge'));
if ((intval($setting['payment']['wechat']['switch']) == 2 || intval($setting['payment']['wechat']['switch']) == 3) && empty($payopenid)) {
    $uniacid = !empty($setting['payment']['wechat']['service']) ? $setting['payment']['wechat']['service'] : $setting['payment']['wechat']['borrow'];
    $acid = pdo_getcolumn('uni_account', array('uniacid' => $uniacid), 'default_acid');
    $from = $_GPC['params'];
    $url = $_W['siteroot'] . 'app' . str_replace('./', '/', murl('auth/oauth'));
    $callback = urlencode($url);
    $oauth_account = WeAccount::create($acid);
    $_SESSION['pay_params'] = $from;
    $state = 'we7sid-' . $_W['session_id'];
    $forward = $oauth_account->getOauthCodeUrl($callback, $state);
    header('Location: ' . $forward);
    exit();
}
unset($_SESSION['pay_params']);
if (!empty($plid)) {
    $tag = array();
    $tag['acid'] = $_W['acid'];
    $tag['uid'] = $_W['member']['uid'];
    pdo_update('core_paylog', array('openid' => $_W['openid'], 'tag' => iserializer($tag)), array('plid' => $plid));
}
$ps['title'] = urlencode($params['title']);
load()->model('payment');
load()->func('communication');
$sl = base64_encode(json_encode($ps));
$auth = sha1($sl . $_W['uniacid'] . $_W['config']['setting']['authkey']);
header("Location:../payment/wechat/pay.php?i={$_W['uniacid']}&auth={$auth}&ps={$sl}&payopenid={$payopenid}");
exit();