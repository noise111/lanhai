<?php
/**
 * Created by njlanniu.com.
 * User: 蓝牛科技
 * Time: 2018/2/6 下午3:44
 */
global $_GPC, $_W;
$ops = array('list', 'confirm', 'detail');
$op = trim($_GPC['op']);
if (!in_array($op, $ops)) {
    util::send_error(-1, '参数错误');
}
/**
 * 我的账单列表
 */
if ($op == 'list') {
    $roomid = $_SESSION['community']['addressid'];
    if (empty($roomid)) {
        util::send_error(-1, '参数错误');
    }
    $condition = 't1.uniacid =:uniacid and t1.roomid=:roomid and t1.status=:status';
    $params[':uniacid'] = $_W['uniacid'];
    $params[':roomid'] = $roomid;
    $params[':status'] = intval($_GPC['status']) ? intval($_GPC['status']) : 1;
    $sql = "SELECT t1.*,t3.title FROM" . tablename('xcommunity_fee') . "t1 left join" . tablename('xcommunity_member_room') . "t2 on t1.roomid = t2.id left join" . tablename('xcommunity_fee_category') . "t3 on t3.id = t1.categoryid WHERE $condition order by t1.displayorder desc,t1.id desc ";
    $fees = pdo_fetchall($sql, $params);
    $totalprice = '';
    $list = array();
    foreach ($fees as $k => $v) {
        if (set('p161') || $v['price'] > 0) {
            if ($v['type'] == 2) {
                //抄表
                $jftime = $v['starttime'] && $v['endtime'] ? date('Y-m-d', $v['starttime']) . '~' . date('Y-m-d', $v['endtime']) : date('Y-m-d', $v['createtime']);
            } else {
                $jftime = date('Y-m-d', $v['starttime']) . '~' . date('Y-m-d', $v['endtime']);
            }
//            $list[$k]['createtime'] = date('Y-m-d', $v['paytime']);
            $list[] = array(
                'id' => $v['id'],
                'title' => $v['title'],
                'price' => $v['price'],
                'jftime' => $jftime,
                'status' => $v['status'],
                'type' => $v['type']
            );
            $totalprice += $v['price'];
        }
    }
    $data = array();
    $data['list'] = $list;
    $data['totalprice'] = sprintf('%.2f', (float)$totalprice);
    $data['hstatus'] = set('p96') ? 1 : 0;
    util::send_result($data);
}
/**
 * 账单缴费提交
 */
if ($op == 'confirm') {
    $ids = xtrim($_GPC['ids']);
    if (empty($ids)) {
        util::send_error(-1, '缺少参数');
        exit();
    }

    $sql = "select price,id from" . tablename('xcommunity_fee') . "where id in({$ids})";
    $allfee = pdo_fetchall($sql, array(":ids" => $ids));

    $total = '';
    foreach ($allfee as $k => $v) {
        $total += $v['price'];
    }
    $data = array(
        'uniacid'    => $_W['uniacid'],
        'uid'        => $_W['member']['uid'],
        'ordersn'    => 'LN' . date('YmdHi') . random(10, 1),
        'createtime' => TIMESTAMP,
        'price'      => $total,
        'regionid'   => $_SESSION['community']['regionid'],
        'addressid'  => $_SESSION['community']['addressid'],
        'type'       => 'bill',
    );
    pdo_insert('xcommunity_order', $data);
    $orderid = pdo_insertid();

    //插入订单商品
    foreach ($allfee as $row) {
        if (empty($row)) {
            continue;
        }
        $d = array(
            'uniacid'    => $_W['uniacid'],
            'goodsid'    => $row['id'],
            'orderid'    => $orderid,
            'price'      => $row['price'],
            'createtime' => TIMESTAMP,
        );
        pdo_insert('xcommunity_order_goods', $d);
    }
    $url = $this->createMobileUrl('paycenter', array('type' => 2, 'orderid' => $orderid));
    $data = array();
    $data['url'] = $url;
    util::send_result($data);
}
/**
 * 账单的详情
 */
if ($op == 'detail') {
    $id = intval($_GPC['id']);
    $uid = $_W['member']['uid'];
    $item = pdo_get('xcommunity_fee', array('uniacid' => $_W['uniacid'], 'id' => $id), array());
    $category = pdo_get('xcommunity_fee_category', array('id' => $item['categoryid']), array('title', 'price', 'unit'));
    $room = pdo_get('xcommunity_member_room', array('id' => $item['roomid']), array('address', 'regionid', 'square'));
    $region = pdo_get('xcommunity_region', array('id' => $room['regionid']), array('title'));
    $member = pdo_get('mc_members', array('uid' => $uid, 'uniacid' => $_W['uniacid']), array('realname', 'nickname'));
    if ($item['type'] == 2) {
        //抄表
        $jftime = $item['starttime'] && $item['endtime'] ? date('Y-m-d', $item['starttime']) . '~' . date('Y-m-d', $item['endtime']) : date('Y-m-d', $item['createtime']);
    } else {
        $jftime = date('Y-m-d', $item['starttime']) . '~' . date('Y-m-d', $item['endtime']);
    }
    $paytype = array('', '余额', '微信支付', '货到付款', '支付宝', '现金', '银联刷卡');
    $data = array();
    $data['id'] = $item['id'];
    $data['title'] = $category['title'];
    $data['price'] = $category['price'];
    $data['jftime'] = $jftime;
    $data['totalPrice'] = $item['price'];
    $data['address'] = $room['address'];
    $data['status'] = $item['status'];
    $data['regionTitle'] = $region['title'];
    $data['realname'] = $member['realname'] ? $member['realname'] : $member['nickname'];
    $data['type'] = $item['type'];// 1物业费 2抄表
    $data['square'] = $room['square'];// 面积
    $data['unit'] = $category['unit'];// 单位
    $data['oldNum'] = $item['old_num'];// 上期度数
    $data['newNum'] = $item['new_num'];// 本期度数
    $data['paytype'] = $paytype[$item['paytype']];
    $data['paytime'] = $item['paytime'] ? date('Y-m-d H:i', $item['paytime']) : 0;
    util::send_result($data);
}