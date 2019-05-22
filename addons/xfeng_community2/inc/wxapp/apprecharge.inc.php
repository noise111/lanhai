<?php
global $_GPC, $_W;

$fee = $_GPC['fee'];
$orderid = 'LN' . date('YmdHi') . random(10, 1);
$data = array(
    'uniacid'    => $_W['uniacid'],
    'fee'        => $fee,
    'orderid'    => $orderid,
    'createtime' => TIMESTAMP
);
$r = pdo_insert('xcommunity_recharge', $data);
$orderid = pdo_insertid();
$url = $this->createMobileUrl('apppay', array('orderid' => $orderid));
util::send_result(array('url' =>$url));