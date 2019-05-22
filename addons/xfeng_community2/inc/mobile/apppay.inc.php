<?php
/**
 * Created by njlanniu.
 * User: njlanniu
 * Time: 2018/3/22 上午8:50
 */
global $_GPC,$_W;
$orderid = intval($_GPC['orderid']);
//$tid = date('YmdHi') . random(8, 1);
//$fee = $_GPC['fee'];
$order = pdo_get('xcommunity_order',array('id'=>$orderid),array('price','ordersn'));
$tid = $order['ordersn'];
$fee = $order['price'];
$params = array(
    'tid'     => $tid,      //充值模块中的订单号，此号码用于业务模块中区分订单，交易的识别码
    'ordersn' => $tid,      //收银台中显示的订单号
    'title'   => '小区商家充值购买积分', //收银台中显示的标题
    'fee'     => $fee,       //收银台中显示需要支付的金额,只能大于 0
    'user'    => $_SESSION['appuid'],     //付款用户, 付款的用户名(选填项)
    'module'  => $this->module['name']
);
//$this->pay($params);

$sql = 'SELECT * FROM ' . tablename('core_paylog') . ' WHERE `uniacid`=:uniacid AND `module`=:module AND `tid`=:tid';
$pars = array();
$pars[':uniacid'] = $_W['uniacid'];
$pars[':module'] = $params['module'];
$pars[':tid'] = $params['tid'];
$log = pdo_fetch($sql, $pars);
if (!empty($log) && $log['status'] == '1') {
    message('这个订单已经支付成功, 不需要重复支付.');
}
$setting = uni_setting($_W['uniacid'], array('payment', 'creditbehaviors'));
if (!is_array($setting['payment'])) {
    message('没有有效的支付方式, 请联系网站管理员.');
}
$log = pdo_get('core_paylog', array('uniacid' => $_W['uniacid'], 'module' => $params['module'], 'tid' => $params['tid']));
if (empty($log)) {
    $log = array(
        'uniacid'    => $_W['uniacid'],
        'acid'       => $_W['acid'],
        'openid'     => $_SESSION['appuid'],
        'module'     => $this->module['name'], 'tid' => $params['tid'],
        'fee'        => $params['fee'],
        'card_fee'   => $params['fee'],
        'status'     => '0',
        'is_usecard' => '0',
    );
    pdo_insert('core_paylog', $log);
}
$params = base64_encode(json_encode($params));
$url = $this->createMobileUrl('cash', array( 'params' => $params,'op'=>'syswechat'));
@header("Location: " . $url);
