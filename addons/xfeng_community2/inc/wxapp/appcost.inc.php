<?php
/**
 * Created by njlanniu.com.
 * User: 蓝牛科技
 * Time: 2018/4/17 下午1:44
 */
global $_GPC, $_W;
$ops = array('list', 'detail', 'display', 'order', 'costdetail', 'del', 'costdel', 'costadd', 'change', 'auth', 'orderdel', 'update', 'print');
$op = trim($_GPC['op']);
if (!in_array($op, $ops)) {
    util::send_error(-1, '参数错误');
}
/**
 * 费用管理列表
 */
if ($op == 'list') {
    $pindex = max(1, intval($_GPC['page']));
    $psize = max(20, intval($_GPC['psize']));
    $condition = ' t1.uniacid =:uniacid';
    $params[':uniacid'] = $_SESSION['appuniacid'];
    if ($_SESSION['apptype'] == 2) {
        //普通管理员
        $condition .= " and t1.uid =:uid";
        $params[':uid'] = $_SESSION['appuid'];
    }
    if ($_SESSION['apptype'] == 3) {
        //小区管理员
        $condition .= " and t1.regionid in({$_SESSION['appregionids']})";
    }
    if ($_SESSION['apptype'] == 4 || $_SESSION['apptype'] == 5) {
        $list = array();
        util::send_result($list);
    }
    $list = pdo_fetchall("SELECT t1.* , t2.title,t2.city,t2.dist  FROM" . tablename('xcommunity_cost') . "as t1 left join" . tablename('xcommunity_region') . "as t2 on t1.regionid = t2.id WHERE $condition order by t1.createtime desc LIMIT " . ($pindex - 1) * $psize . ',' . $psize, $params);
    foreach ($list as $k => $v) {
        $list[$k]['url'] = $this->createMobileUrl('xqsys', array('op' => 'cost', 'p' => 'display', 'id' => $v['id']));
        $list[$k]['createtime'] = date('Y-m-d H:i', $v['createtime']);
    }
    util::send_result($list);
}
/**
 * 费用列表的详情
 */
if ($op == 'detail') {
    $id = intval($_GPC['id']);
    if ($id) {
        $item = pdo_fetch("SELECT t1.* , t2.title,t2.city,t2.dist  FROM" . tablename('xcommunity_cost') . "as t1 left join" . tablename('xcommunity_region') . "as t2 on t1.regionid = t2.id WHERE t1.uniacid=:uniacid and t1.id =:id order by t1.createtime desc ", array(':uniacid' => $_W['uniacid'], ':id' => $id));
        $item['createtime'] = date('Y-m-d H:i', $item['createtime']);
        $item['url'] = $this->createMobileUrl('xqsys', array('op' => 'cost', 'p' => 'display', 'id' => $item['id']));
        $item['ourl'] = $this->createMobileUrl('xqsys', array('op' => 'cost', 'p' => 'order', 'id' => $item['id']));
        $item['authtime'] = date('Y-m-d H:i', $item['authtime']);
        util::send_result($item);
    }
}
/**
 * 费用数据的列表
 */
if ($op == 'display') {
    $condition = 't1.uniacid =:uniacid ';
    $params[':uniacid'] = $_SESSION['appuniacid'];
//    if ($_GPC['mobile']) {
//        $condition .= "AND t1.mobile like '%{$_GPC['mobile']}%'";
//    }
//    if ($_GPC['username']) {
//        $condition .= " AND t1.username like '%{$_GPC['username']}%'";
//    }
//    if ($_GPC['homenumber']) {
//        $condition .= " AND t1.homenumber = '{$_GPC['homenumber']}'";
//    }
//    $regionid = intval($_GPC['regionid']);
//    if ($regionid) {
//        $condition .= "and t1.regionid=:regionid ";
//        $params[':regionid'] = intval($_GPC['regionid']);
//    }
    $id = intval($_GPC['id']);
    if ($id) {
        $condition .= "and t1.cid=:id ";
        $params[':id'] = intval($_GPC['id']);
    }
    $pindex = max(1, intval($_GPC['page']));
    $psize = max(10, intval($_GPC['psize']));
    $sql = "SELECT t1.*,t2.title FROM" . tablename('xcommunity_cost_list') . "t1 left join" . tablename('xcommunity_region') . "t2 on t1.regionid=t2.id where $condition LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
    $cost = pdo_fetchall($sql, $params);
    $list = array();
    foreach ($cost as $k => $v) {
//        $list[$k]['url'] = $this->createMobileUrl('xqsys', array('op' => 'cost', 'p' => 'costdetail', 'id' => $v['id']));
        $list[] = array(
            'id' => $v['id'],
            'costtime' => $v['costtime'],
            'mobile' => $v['mobile'],
            'username' => $v['username'],
            'title' => $v['title'],
            'homenumber' => $v['homenumber'],
            'total' => $v['total'],
            'status' => $v['status'],
            'paytype' => $v['paytype'],
            'paytime' => date('Y-m-d H:i', $v['paytime']),
            'remark' => $v['remark'],
        );
    }
    util::send_result($list);
}
/**
 * 费用订单的列表
 */
if ($op == 'order') {
    $pindex = max(1, intval($_GPC['page']));
    $psize = max(20, intval($_GPC['psize']));
    $condition = "t1.uniacid=:uniacid and t1.type='pfree'";
    $params[':uniacid'] = $_SESSION['appuniacid'];
    if (!empty($_GPC['keyword'])) {
        $condition .= " AND (t5.realname = :keyword or t5.mobile = :keyword or t3.homenumber = :keyword)";
        $params[':keyword'] = $_GPC['keyword'];
    }

    if ($_SESSION['apptype'] == 2) {
        //普通管理员
        $condition .= " and t1.uid =:uid";
        $params[':uid'] = $_SESSION['appuid'];
    }
    if ($_SESSION['apptype'] == 3) {
        //小区管理员
        $condition .= " and t1.regionid in({$_SESSION['appregionids']})";
    }
    if (intval($_GPC['id'])) {
        $condition .= " and t3.cid=:id";
        $params[':id'] = intval($_GPC['id']);
    }
    $sql = "select t1.price,t2.price as goodsprice,t1.credit,t1.createtime,t1.status,t1.transid,t1.id,t5.realname,t6.title,t1.ordersn,t4.address,t1.offsetprice,t1.paytype from" . tablename('xcommunity_order') . "t1 left join" . tablename('xcommunity_order_goods') . "t2 on t1.id=t2.orderid left join" . tablename('xcommunity_cost_list') . "t3 on t2.goodsid = t3.id left join" . tablename('xcommunity_member_room') . "t4 on t4.id=t1.addressid left join" . tablename('mc_members') . "t5 on t1.uid=t5.uid left join" . tablename('xcommunity_region') . "t6 on t6.id=t1.regionid where $condition group by t1.id order by t1.createtime desc limit " . ($pindex - 1) * $psize . ',' . $psize;
    $list = pdo_fetchall($sql, $params);
    foreach ($list as $key => $value) {
        $list[$key]['cctime'] = date('Y-m-d H:i', $value['createtime']);
    }
    util::send_result($list);
}
/**
 * 费用数据的详情
 */
if ($op == 'costdetail') {
    $id = intval($_GPC['id']);
    if ($id) {
        $item = pdo_fetch("SELECT t1.*  FROM" . tablename('xcommunity_cost_list') . "as t1 WHERE t1.uniacid=:uniacid and t1.id =:id ", array(':uniacid' => $_W['uniacid'], ':id' => $id));
        $item['createtime'] = date('Y-m-d H:i', $item['createtime']);
        $item['url'] = $this->createMobileUrl('xqsys', array('op' => 'cost', 'p' => 'costadd', 'id' => $item['id']));
        $category = pdo_fetch("SELECT * FROM" . tablename('xcommunity_category') . "WHERE regionid=:regionid AND type =7 AND uniacid=:uniacid", array(':regionid' => $item['regionid'], ':uniacid' => $_W['uniacid']));
        $cate = explode('|', $category['name']);
        if ($item['fee']) {
            $fee = explode('|', $item['fee']);
        }
        $item['cate'] = $cate;
        $item['fee'] = $fee;
        util::send_result($item);
    }
}
/**
 * 费用列表的删除
 */
if ($op == 'del') {
    $id = intval($_GPC['id']);
    $item = pdo_getcolumn('xcommunity_cost', array('id' => $id), 'id');
    if (empty($item)) {
        util::send_error(-1, '参数错误');
    }
    if (pdo_delete('xcommunity_cost', array('id' => $id))) {
        pdo_delete('xcommunity_cost_list', array('cid' => $id));
        util::send_result();
    }
}
/**
 * 费用数据的删除
 */
if ($op == 'costdel') {
    $id = intval($_GPC['id']);
    $item = pdo_getcolumn('xcommunity_cost_list', array('id' => $id), 'id');
    if (empty($item)) {
        util::send_error(-1, '参数错误');
    }
    if (pdo_delete('xcommunity_cost_list', array('id' => $id))) {
        util::send_result();
    }
}
/**
 * 费用数据的编辑
 */
if ($op == 'costadd') {
    $id = intval($_GPC['id']);
    $data = array(
        'username'   => trim($_GPC['username']),
        'mobile'     => trim($_GPC['mobile']),
        'homenumber' => trim($_GPC['homenumber']),
        'area'       => $_GPC['area'],
        'costtime'   => trim($_GPC['costtime']),
        'total'      => $_GPC['total'],
        'fee'        => $_GPC['fee']
    );
    if ($id) {
        pdo_update('xcommunity_cost_list', $data, array('id' => $id));
    } else {
        $data['createtime'] = TIMESTAMP;
        pdo_insert('xcommunity_cost_list', $data);
    }
    util::send_result();
}
/**
 * 改变费用列表的状态 费用显示 在线支付
 */
if ($op == 'change') {
    $type = trim($_GPC['type']);
    $id = intval($_GPC['id']);
    $item = pdo_get('xcommunity_cost', array('id' => $id));
    if ($item) {
        $num = $item[$type] == 1 ? 0 : 1;
        pdo_update('xcommunity_cost', array($type => $num), array('id' => $id));
        util::send_result();
    }
}
/**
 * 费用列表的支付期限
 */
if ($op == 'auth') {
    $id = intval($_GPC['id']);
    $item = pdo_get('xcommunity_cost', array('id' => $id));
    if ($item) {
        $data = array(
            'auth'     => intval($_GPC['auth']),
            'authtime' => strtotime($_GPC['authtime'])
        );
        pdo_update('xcommunity_cost', $data, array('id' => $id));
        util::send_result();
    }

}
/**
 * 费用订单的删除
 */
if ($op == 'orderdel') {
    $id = intval($_GPC['id']);
    $item = pdo_getcolumn('xcommunity_order', array('id' => $id), 'id');
    if (empty($item)) {
        util::send_error(-1, '参数错误');
    }
    if (pdo_delete('xcommunity_order', array('id' => $id))) {
        util::send_result();
    }
}
/**
 * 修改费用为已缴费
 */
if ($op == 'update') {
    $id = intval($_GPC['id']);
//    $status = $_GPC['status'];
    $status = 1;
    $item = pdo_get("xcommunity_cost_list", array("id" => $id), array());
    if (empty($item)) {
        util::send_error(-1, '信息不存在或已经删除');
    }
    $data = array(
        'status' => $status,
        'remark' => $_GPC['remark'],
        'paytype' => intval($_GPC['paytype']),
        'paytime' => TIMESTAMP
    );
    $r = pdo_update("xcommunity_cost_list", $data, array("id" => $id));
    util::permlog('', '费用id:' . $costid . '修改状态');
    if ($r) {
        util::send_result();
    }
}
/**
 * 费用数据小票打印
 */
if ($op == 'print') {
    $id = intval($_GPC['id']);
    if ($id) {
        $item = pdo_get('xcommunity_cost_list', array('id' => $id), array('username', 'homenumber', 'total', 'regionid'));
        if (empty($item)) {
            util::send_error(-1, '信息不存在或已经删除');
        }
        $api = set('d2', '', $_SESSION['appuniacid']);
        $createtime = date("Y-m-d H:i", TIMESTAMP);
        $yl = "^N1^F1\n";
        $yl .= "^B2 收费小票\n";
        $yl .= "姓名：" . $item['username'] . "\n";
        $yl .= "房号：" . $item['homenumber'] . "\n";
        $yl .= "费用：" . $item['total'] . '元' . "\n";
        $yl .= "时间：" . $createtime;
        $fy = array(
            'msgDetail' =>
                '
                        收费小票

                姓名：' . $item['username'] . '
                -------------------------

                房号：' . $item['homenumber'] . '
                费用：' . $item['total'] . '
                时间：' . $createtime . '
                ',
        );
        if ($api) {
            //独立接口
            $type = set('d1', '', $_SESSION['appuniacid']) == 1 ? 'yl' : 'fy';
            $content = set('d1', '', $_SESSION['appuniacid']) == 1 ? $yl : $fy;
            xq_print::xqprint($type, 1, $content);
        } else {
            $type = set('x26', $item['regionid'], $_SESSION['appuniacid']) == 1 ? 'yl' : 'fy';
            $content = set('x26', $item['regionid'], $_SESSION['appuniacid']) == 1 ? $yl : $fy;
            xq_print::xqprint($type, 2, $content, $item['regionid']);
        }
        util::permlog('', '打印缴费信息ID:' . $id);
        util::send_result();
    }
}