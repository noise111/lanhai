<?php

//define('IN_MOBILE', true);
//require IA_ROOT . '/framework/bootstrap.inc.php';
require '../../../../framework/bootstrap.inc.php';
//echo $_SERVER["HTTP_REFERER"];exit();
if (!empty($_GET)) {
    $set = array();
    $set['subject'] = $_GET['subject'];
    $set['body'] = $_GET['body'];
    $set['service'] = $_GET['exterface'];
    $set['partner'] = $_GET['seller_id'];
    $set['notify_url'] =  $_W['siteroot'].'/addons/xfeng_community/payment/alipay/notify.php';
    $set['return_url'] =  $_W['siteroot'].'/addons/xfeng_community/payment/alipay/notify.php';
    $set['show_url'] = $_W['siteroot'];
    $set['_input_charset'] = 'utf-8';
    $set['out_trade_no'] = $_GET['out_trade_no'];
    $set['price'] = $_GET['total_fee'];
    $set['quantity'] = 1;
    $set['seller_email'] = $_GET['seller_email'];
    $set['extend_param'] = 'isv^dz11';
    $set['payment_type'] = 1;
    $item = pdo_fetch("SELECT * FROM " . tablename('uni_settings') . " WHERE uniacid = :uniacid", array(':uniacid' => $set['body']));
    $setting = unserialize($item['payment']);
    if (is_array($setting)) {
        $alipay = $setting['alipay'];
        if (!empty($alipay)) {
            ksort($set);
            $sign ='';
            foreach ($set as $key => $val) {
                $sign .= '&' . $key . '=' . $val;
            }
            $sign = substr($sign, 1);
            $sign = md5($sign . $alipay['secret']);
//            print_r($sign);
//            echo '<br/>';
//            echo $_GET['sign'];
//            if ($sign == $_GET['sign']) {
//            //签名不对
                $sql = 'SELECT uid FROM ' . tablename('xcommunity_plugin_adv_order') . ' WHERE ordersn=:ordersn';
                $params = array();
                $params[':ordersn'] = $set['out_trade_no'];
                $order = pdo_fetch($sql, $params);
                pdo_query("update ".tablename('xcommunity_users')."set balance=balance+{$set['price']} where uid=:uid",array(':uid'=> $order['uid']));
                $url = $_SERVER['SERVER_NAME'].'web/index.php?c=site&a=entry&do=advertiser&op=finance&m=xfeng_community';
                header('location: '.$url );
        }
    }
}
exit('fail');
