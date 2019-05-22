<?php
/**
 * Created by njlanniu.
 * User: njlanniu
 * Time: 2018/1/13 下午12:13
 */
global $_GPC, $_W;
$ops = array('goods', 'check', 'confirm', 'home', 'lock','update');
$op = trim($_GPC['op']);
if (!in_array($op, $ops)) {
    util::send_error(-1, '参数错误');
}
if ($op == 'goods') {
    //1已付款，
    $code = trim($_GPC['code']);
    $orderid = trim($_GPC['orderid']);
    $payfee = trim($_GPC['payfee']);
    $createtime = $_POST['createtime'];
    if (empty($code) || empty($orderid) || empty($payfee)) {
        util::send_error(-1, '缺少参数');
        exit();
    }
    $goods = trim($_GPC['data']);
    $status = intval($_GPC['status']) ? intval($_GPC['status']) : 0;
    $data = array(
        'uniacid'    => $_GPC['i'],
        'ordersn'    => $orderid,
        'price'      => $payfee,
        'goods'      => ltrim(rtrim($goods, '|'), '|'),
        'status'     => $status,
        'createtime' => $createtime,
        'code'       => $code
    );
    $condition = "orderid=:orderid";
    $params[':orderid'] = $orderid;
    $sql = "select * from" . tablename('xcommunity_supermark_order') . "where $condition";
    $item = pdo_fetch($sql, $params);
    if (empty($item)) {
        if (pdo_insert('xcommunity_supermark_order', $data)) {
            util::send_result(array('status' => 1));
        }
        else {
            util::send_error(-1, '数据插入失败');
        }
    }
    else {
        if ($status == 1) {
            if (pdo_update('xcommunity_supermark_order', array('status' => 1), array('orderid' => $orderid))) {
                util::send_result(array('status' => 1));
            }
        }
        else {
            util::send_error(-1, '订单号重复');
        }
    }

}
elseif ($op == 'update') {
    $orderid = trim($_POST['orderid']);
    $status = intval($_POST['status']);
    $item = pdo_get('xcommunity_supermark_order', array('ordersn' => $orderid), array('id', 'status'));
    if(empty($item)){
        util::send_error(-1, '非法订单');
    }
    if(pdo_update('xcommunity_supermark_order', array('status' => $status), array('ordersn' => $orderid))){
        util::send_result(array('status' => 1));
    }
}
elseif ($op == 'confirm') {
    $code = trim($_GPC['code']);
    $sql = "select * from" . tablename('xcommunity_supermark_order') . "where uniacid=:uniacid and status =0 and code=:code order by id desc limit 1 ";
    $params = array();
    $params[':uniacid'] = $_W['uniacid'];
    $params[':code'] = $code;
    $item = pdo_fetch($sql, $params);
   
    if ($item) {
        $goods = explode('|', $item['goods']);
        $good = array();
        foreach ($goods as $k => $v) {
            if ($v) {
                $good[] = explode(',', $v);
            }
        }
        $data = array();
        $data['payfee'] = $item['price'];
        $data['goods'] = $good;
        $data['orderid'] = $item['id'];
        util::send_result($data);
    }
    else
    {
        util::send_error(-1, '无商品');
    }


}
elseif ($op == 'home') {
    $code = trim($_GPC['code']);
    if (empty($code)) {
        util::send_error(-1, '非法操作');
    }
    $item = pdo_get('xcommunity_supermark', array('device_code' => $code), array());
    $data = array();
    $data['title'] = $item['title'];
    $data['copy'] = $item['copy'];
    $data['tel'] = $item['tel'];
    $data['id'] = $item['id'];
    $orders = pdo_getall('xcommunity_supermark_order', array('device_code' => $code, 'uid' => $_W['member']['uid']), array('ordersn', 'createtime', 'price'));
    foreach ($orders as $k => $v) {
        $orders[$k]['createtime'] = date('Y-m-d H:i', $v['createtime']);
    }
    $data['orders'] = $orders;
    util::send_result($data);
}
elseif ($op == 'lock') {
    $id = intval($_GPC['id']);
    if (empty($id)) {
        util::send_error(-1, '非法操作');
    }
    $item = pdo_get('xcommunity_supermark', array('id' => $id), array('device_code'));
    if ($item['device_code']) {
        $data = array(
            'id'     => $item['device_code'],
            'action' => 'open',
            't'      => TIMESTAMP
        );
        $url = "http://door.njlanniu.com/cooperation/openlock/servlet.jspx";
        $resp = ihttp_post($url, $data);
        $resp = $resp['content'];
        if ($resp == 'ok') {
            $content = array(
                'code'   => 0,
                'info'   => '成功开门',
                'status' => 'ok'
            );
        }
        else {
            $content = array(
                'code'   => 1,
                'info'   => '设备离线',
                'status' => 'no'
            );
        }
        $data = array();
        $data['content'] = $content['info'];
        util::send_result($data);
    }
}
