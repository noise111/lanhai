<?php
global $_GPC, $_W;
$ops = array('list', 'detail', 'add', 'recharge', 'member');
$op = trim($_GPC['op']);
if (!in_array($op, $ops)) {
    util::send_error(-1, '参数错误');
}
if ($op == 'list') {

}
/**
 * 根据账号返回小区
 */
if ($op == 'detail') {
    $type = intval($_GPC['type']);
//    if ($_SESSION['apptype'] == 2) {
//        $condition .= " and t1.uid=:uid";
//        $params[':uid'] = $_SESSION['appuid'];
//    }
//    if ($_SESSION['apptype'] == 3) {
//        $condition .= " and t2.regionid in (:regionid)";
//        $params[':regionid'] = $_SESSION['appregionids'];
//    }
//    if ($_SESSION['apptype'] == 4 || $_SESSION['apptype'] == 5) {
//
//    }
    ///$regions = model_region::region_fetall('', $_SESSION['appregionids']);
    $regionids = explode(',',$_SESSION['appregionids']);
    $regions = pdo_getall('xcommunity_region',array('id'=>$regionids));
    foreach ($regions as $k => $v) {
        $regions[$k]['key'] = $v['id'];
        $regions[$k]['value'] = $v['title'] . '-佣金' . round($v['commission'], 2) . '元';
    }
    util::send_result($regions);
}
/**
 * 账号提现
 */
if ($op == 'add') {
    $type = intval($_GPC['type']);
    if ($_GPC['fee'] <= 0) {
        util::send_error(-1, '输入金额不正确,请重新输入');
    }
    $data = array(
        'uniacid'    => $_W['uniacid'],
        'ordersn'    => 'LN' . date('YmdHi') . random(10, 1),
        'price'      => $_GPC['fee'],
        'type'       => 'cash',
        'pay'        => $_GPC['account'],
        'createtime' => TIMESTAMP,
        'uid' => $_SESSION['appuid'],
    );
    if ($type == 1) {
        $regionid = intval($_GPC['regionid']);
        $region = model_region::region_check($regionid);
        if ($_GPC['fee'] > $region['commission']) {
            util::send_error(-1, '佣金不足，无法提现');
        }
        $data['regionid'] = $regionid;
    } else {
        $users = pdo_fetch("SELECT * FROM" . tablename('xcommunity_users') . "WHERE uid=:uid", array(':uid' => $_SESSION['appuid']));
        if ($_GPC['fee'] > $users['balance']) {
            util::send_error(-1, '余额不足，无法提现');
        }
    }

    $r = pdo_insert('xcommunity_order', $data);
    $orderid = pdo_insertid();
    if ($r) {
        if ($type == 1) {
            pdo_update('xcommunity_region', array('commission -=' => $_GPC['fee']), array('id' => $regionid));
            // 小区提现
            $d1 = array(
                'uniacid'    => $_SESSION['appuniacid'],
                'uid'        => $_SESSION['appuid'],
                'realname'   => $_SESSION['username'],
                'type'       => 4,
                'regionid'   => $regionid,
                'createtime' => TIMESTAMP,
                'orderid'    => $orderid,
                'good'       => "小区的佣金提现",
                'cid'        => 1,
                'price'      => $_GPC['fee'],
                'category' => 2,
                'creditstatus' => 2
            );
            pdo_insert('xcommunity_commission_log', $d1);
        } else {
            pdo_update('xcommunity_users', array('balance -=' => $_GPC['fee']), array('id' => $users['id']));
            $balance = $users['balance'] - $_GPC['fee'];
            $_SESSION['balance'] = $balance;
            // 账号提现增加记录
            $staff = pdo_get('xcommunity_staff', array('id' => $users['staffid']), array('mobile'));
            $creditdata = array(
                'uid' => $_SESSION['appuid'],
                'uniacid' => $_SESSION['appuniacid'],
                'realname' => $staff['mobile'],
                'mobile' => $staff['mobile'],
                'content' => "订单号：" . $data['ordersn'] . ",提现",
                'credit' => $_GPC['fee'],
                'creditstatus' => 2,
                'createtime' => TIMESTAMP,
                'type' => 11,
                'typeid' => $_SESSION['appuid'],
                'category' => 4,
                'usename' => $_SESSION['username']
            );
            pdo_insert('xcommunity_credit', $creditdata);
        }
        util::send_result();
    }
}
/**
 * 给用户充值余额，积分
 */
if ($op == 'recharge') {
    $price = $_GPC['price'];
    $type = intval($_GPC['type']);
    $keyword = trim($_GPC['keyword']);
    $member = pdo_fetch("select uid from" . tablename('mc_members') . " where uid=:keyword or mobile=:keyword and uniacid=:uniacid", array(':keyword' => $keyword, ':uniacid' => $_SESSION['appuniacid']));
    if (empty($member)) {
        util::send_error(-1, '信息不存在');
    }
    if ($type == 1) {
        //记录用户积分的操作日志
        mc_credit_update($member['uid'], 'credit1', $price, array($member['uid'], '系统充值积分'));
        $category = 1;
        $content = "系统后台:操作会员(uid:" . $member['uid'] . ")积分";
    }
    if ($type == 2) {
        //记录用户余额的操作日志
        mc_credit_update($member['uid'], 'credit2', $price, array($member['uid'], '系统充值余额'));
        $category = 2;
        $content = "系统后台:操作会员(uid:" . $member['uid'] . ")余额";
    }
    if ($price >= 0) {
        $creditstatus = 1;
    } elseif ($price < 0) {
        $creditstatus = 2;
    }
    $creditdata = array(
        'uid' => $member['uid'],
        'uniacid' => $_SESSION['appuniacid'],
        'realname' => $_SESSION['username'],
        'mobile' => $_SESSION['username'],
        'content' => $content,
        'credit' => abs($price),
        'creditstatus' => $creditstatus,
        'createtime' => TIMESTAMP,
        'type' => 9,
        'typeid' => $member['uid'],
        'category' => $category,
        'usename' => $_SESSION['username']
    );
    pdo_insert('xcommunity_credit', $creditdata);// 记录操作用户积分、余额的记录
    util::send_result();
}
/**
 * 搜索的用户余额积分
 */
if ($op == 'member') {
    $type = intval($_GPC['type']);
    $keyword = trim($_GPC['keyword']);
    $uniacid = $_SESSION['appuniacid'];
    $member = pdo_fetch("select uid,credit1,credit2 from" . tablename('mc_members') . " where uid=:keyword or mobile=:keyword and uniacid=:uniacid", array(':keyword' => $keyword, ':uniacid' => $_SESSION['appuniacid']));
    if (empty($member)) {
        util::send_error(-1, '信息不存在');
    }
    $data = array();
    if ($type == 1) {
        $data['credit'] = $member['credit1'];
    }
    if ($type == 2) {
        $data['credit'] = $member['credit2'];
    }
    util::send_result($data);
}