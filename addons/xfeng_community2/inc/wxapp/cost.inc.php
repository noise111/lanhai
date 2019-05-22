<?php
/**
 * Created by njlanniu.com.
 * User: 蓝牛科技
 * Time: 2017/12/5 下午4:18
 */
global $_GPC, $_W;
$ops = array('list', 'detail', 'confirm');
$op = trim($_GPC['op']);
if (!in_array($op, $ops)) {
    util::send_error(-1, '参数错误');
}
/**
 * 费用列表
 */
if ($op == 'list') {
    $pindex = max(1, intval($_GPC['page']));
    $psize = max(20, intval($_GPC['psize']));
    $regionid = $_SESSION['community']['regionid'];
    $address = $_SESSION['community']['address'];
    //enable =1 展示显示的费用
    $condition = "t1.homenumber=:addr and t1.regionid=:regionid and t2.enable = 1 ";
    $params[':addr'] = $address;
    $params[':regionid'] = $regionid;
    //status=2 未交费用， status=1 已缴费
    $status = intval($_GPC['status']) ? intval($_GPC['status']) : 2;
    $condition .= " and t1.status=:status";
    $params[':status'] = $status;
    //从支付端传递过来，支付页面需要判断是否开启已支付和超过授权支付的时间
    $type = intval($_GPC['type']);
    $time = TIMESTAMP;
    // 查询相关的费用列表
    $con = array();
    $con['uniacid'] = $_W['uniacid'];
    $con['enable'] = 1;
    $con['regionid'] = $regionid;
    if ($type == 1) {
        //进入支付页面，查询展示开启支付的账单
        $condition .= " and t2.status=1 ";
        $psize = 1000;
        $con['status'] = 1;
    }
    $sql = "select t1.costtime,t1.total,t1.id,t1.status,t2.auth,t2.authtime,t2.title from" . tablename('xcommunity_cost_list') . "t1 left join" . tablename('xcommunity_cost') . "t2 on t1.cid=t2.id where $condition order by t1.displayorder desc,t1.createtime desc limit " . ($pindex - 1) * $psize . ',' . $psize;
    $row = pdo_fetchall($sql, $params);
    $price = '0.00';
    $list = array();
    foreach ($row as $k => $v) {
        if ($type) {
            //进入支付
            if ($v['auth']) {
                //开启支付截止时间
                if ($v['authtime'] > $time) {
                    $list[] = array(
                        'costtime' => $v['costtime'],
                        'total'    => $v['total'],
                        'id'       => $v['id'],
                        'status'   => $v['status'],
                        'url'      => app_url('cost', array('op' => 'detail', 'id' => $v['id'])),
                        'title'    => $v['title']
                    );
                    $price += $v['total'];
                }
            }
            else {
                $list[] = array(
                    'costtime' => $v['costtime'],
                    'total'    => $v['total'],
                    'id'       => $v['id'],
                    'status'   => $v['status'],
                    'url'      => app_url('cost', array('op' => 'detail', 'id' => $v['id'])),
                    'title'    => $v['title']
                );
                $price += $v['total'];
            }

        }
        else {
            $row[$k]['url'] = app_url('cost', array('op' => 'detail', 'id' => $v['id']));
            $list = $row;
            $price += $v['total'];
        }

    }
    $data = array();
    $data['list'] = $list;
    $data['p'] = $status;
    // 计算总价格
    $costs = pdo_getall('xcommunity_cost', $con, array('id', 'auth', 'authtime'));
    if ($costs) {
        $costids = array();
        foreach ($costs as $k => $v) {
            if ($type && $v['auth']) {
                if ($v['authtime'] > $time) {
                    $costids[] = $v['id'];
                }
            }
            else {
                $costids[] = $v['id'];
            }
        }
        $price = pdo_getcolumn('xcommunity_cost_list', array('uniacid' => $_W['uniacid'], 'regionid' => $regionid, 'homenumber' => $address, 'status' => $status, 'cid' => $costids), 'sum(total)');
    }
    $data['price'] = $price > 0 ? sprintf('%.2f', (float)$price) : '0.00';
    $data['address'] = $_SESSION['community']['title'] . $_SESSION['community']['address'];
    /**
     * 计算抵扣积分
     */
    $data['creditSwith'] = set('p16') ? 1 : 0;//积分抵扣开关
    $credit = set('p148') ? set('p148') : '0.00';//最高抵扣积分
    $credit2 = set('p159');//最低抵扣积分
    if ($_W['member']['credit1'] < $credit2) {
        //判断用户积分是否是小于最低抵扣积分
        $data['credit'] = 0;
    }
    else {
        $data['credit'] = $credit < $_W['member']['credit1'] ? $credit : $_W['member']['credit1'];
    }
    $data['rate'] = set('p17'); //抵扣比例
    /**/
    $data['hstatus'] = set('p96') ? 1 : 0;
    $data['costimg'] = tomedia(set('p124')) ? tomedia(set('p124')) : MODULE_URL . 'template/mobile/default2/static/img/cheng.png';
    util::send_result($data);

}
/**
 * 详情
 */
if ($op == 'detail') {
    $id = intval($_GPC['id']);
    if ($id) {
        $regionid = intval($_GPC['regionid']) ? intval($_GPC['regionid']) : $_SESSION['community']['regionid'];
        $category = util::fetch_category_one('', $regionid, 7);
        $c = explode('|', $category['name']);
        $params = array();
        $sql = "select t1.*,t2.status as coststatus,t2.authtime,t1.homenumber from" . tablename('xcommunity_cost_list') . "t1 left join" . tablename('xcommunity_cost') . "t2 on t1.cid = t2.id where t1.uniacid=:uniacid and t1.id=:id";
        $params[':uniacid'] = $_W['uniacid'];
        $params[':id'] = $id;
        $item = pdo_fetch($sql, $params);
        if (empty($item)) {
            util::send_error(-1, '');
            exit();
        }
        if ($item['fee']) {
            $fee = explode('|', $item['fee']);
        }
        $list = array();
        foreach ($c as $k => $v) {
            if ($fee[$k] == '' || $fee[$k] == 0) {
                $enable = 0;
            }
            else {
                $enable = 1;
            }
            if ($fee[$k] && $fee[$k] != '0.00') {
                $list[] = array(
                    'category' => $v,
                    'fee'      => $fee[$k],
                    'enable'   => $enable
                );
            }

        }

        $data = array();
        $data['list'] = $list;
        $data['homenumber'] = $item['homenumber'];
        $data['costtime'] = $item['costtime'];
        $data['total'] = $item['total'];
        $data['status'] = $item['status'];
        $data['credit'] = set('p16'); //是否开启积分抵扣
        if (empty($item['authtime'])) {
            $data['_status'] = $item['coststatus'] ? 1 : 0;
        }
        else {
            $auth = !empty($item['authtime']) && $item['authtime'] > TIMESTAMP ? 1 : 0;
            $data['_status'] = $item['coststatus'] && $auth ? 1 : 0;
        }
        $data['hstatus'] = set('p96') ? 1 : 0;
        $data['title'] = '账单详情';
        $data['costimg'] = tomedia(set('p124')) ? tomedia(set('p124')) : MODULE_URL . 'template/mobile/default2/static/img/cheng.png';
        util::send_result($data);
    }
}
/**
 * 提交订单
 */
if ($op == 'confirm') {
    $ids = xtrim($_GPC['ids']);
    if (empty($ids)) {
        util::send_error(-1, '缺少参数');
        exit();
    }
    $sql = "select total,id from" . tablename('xcommunity_cost_list') . "where id in({$ids})";
    $allgoods = pdo_fetchall($sql, array(":ids" => $ids));
    $total = '';//应缴费用
//    $stotal = '';
    foreach ($allgoods as $k => $v) {
        $total += $v['total'];
//        $stotal += $v['total'];
    }
//    $total = trim($_GPC['total']);
    $status = 0;//订单状态

    $price = $total;//	实缴费用

//    if (set('p16') && $_W['member']['credit1'] > 0) {
//
//        //是否开启积分抵扣物业费
//        $maxcredit = $total / set('p17');//总金额最高抵扣积分
//        //积分抵扣开关,设置的最高积分数量$_W['member']['credit1']
//        $credit = set('p148') < $_W['member']['credit1'] ? set('p148') : $_W['member']['credit1'];//平台设置最高积分小于业主积分
//        //判断金额
//        if ($credit < $maxcredit) {
//            $price = $total - $credit * set('p17');//实际支付金额
//        }
//        else {
//            //全额抵扣
//            $credit = $maxcredit;//抵扣积分
//            $price = 0;//实际付款
//            $status = 1;
//        }
//    }
//是否开启积分抵扣

    $creditChecked = $_GPC['creditChecked'];//判断是否启用
    if ($creditChecked) {
        $credit = trim($_GPC['credit']);//抵扣积分默认0
        $creditTotal = trim($_GPC['creditTotal']);//积分抵扣费用
        $status = $price == $creditTotal ? 1 : 0;
        $price = $price - $creditTotal;
    }
    $data = array(
        'uniacid'     => $_W['uniacid'],
        'uid'         => $_W['member']['uid'],
        'openid'      => $_W['fans']['from_user'],
        'ordersn'     => 'LN' . date('YmdHi') . random(10, 1),
        'createtime'  => TIMESTAMP,
        'price'       => $price,
        'regionid'    => $_SESSION['community']['regionid'],
        'addressid'   => $_SESSION['community']['addressid'],
        'type'        => 'pfree',
        'status'      => $status,
        'credit'      => $credit,
        'total'       => $total,
        'offsetprice' => $creditTotal
    );
    pdo_insert('xcommunity_order', $data);
    $orderid = pdo_insertid();
//插入订单商品
    foreach ($allgoods as $row) {
        if (empty($row)) {
            continue;
        }
        $d = array(
            'uniacid'    => $_W['uniacid'],
            'goodsid'    => $row['id'],
            'orderid'    => $orderid,
            'price'      => $row['total'],
            'createtime' => TIMESTAMP,
        );
        pdo_insert('xcommunity_order_goods', $d);
    }
    if ($status == 1) {
        load()->model('mc');
        mc_credit_update($_W['member']['uid'], 'credit1', -$credit, array($_W['member']['uid'], '扣除物业费抵扣积分'));
        $room = pdo_fetch("select t1.regionid,t1.address,t2.title from" . tablename('xcommunity_member_room') . "t1 left join" . tablename('xcommunity_region') . "t2 on t1.regionid = t2.id where t1.id=:id", array(':id' => $_SESSION['community']['addressid']));
        $member = pdo_get('mc_members', array('uid' => $order['uid']), array('realname', 'mobile'));
        $creditdata = array(
            'uid'          => $_W['member']['uid'],
            'uniacid'      => $_W['uniacid'],
            'realname'     => $member['realname'],
            'mobile'       => $member['mobile'],
            'content'      => "订单号：" . $order['ordersn'] . "," . $room['title'] . $room['address'] . "抵扣积分",
            'credit'       => $credit,
            'creditstatus' => 2,
            'createtime'   => TIMESTAMP,
            'type'         => 1,
            'typeid'       => $room['regionid'],
            'category'     => 1,
            'usename'      => $member['realname']
        );
        pdo_insert('xcommunity_credit', $creditdata);
        $member = pdo_get('mc_members', array('uid' => $_W['member']['uid']), array('realname', 'mobile'));
        foreach ($allgoods as $k => $v) {
            pdo_update('xcommunity_cost_list', array('status' => 1, 'credit1' => $credit, 'paytype' => 1, 'paytime' => TIMESTAMP), array('id' => $v['id']));
        }
        $p51 = set('p51');
        util::sendnotice($p51);
        $t11 = set('t11');
        if ($t11) {
            //微信通知
            $createtime = date('Y-m-d H:i', TIMESTAMP);
            $content = array(
                'first'    => array(
                    'value' => '您好，有新的缴费订单。',
                ),
                'userName' => array(
                    'value' => $member['realname'],
                ),
                'address'  => array(
                    'value' => $room['title'] . $room['address'],
                ),
                'pay'      => array(
                    'value' => '支付0元' . ',使用积分' . $credit . '完全抵扣物业费',
                ),
                'remark'   => array(
                    'value' => '请尽快消单处理，并开具收据发票。缴费时间:' . $createtime,
                ),
            );
            $t12 = set('t12');
            $tplid = $t12;
            util::sendtpl($room['regionid'], $content, ' and t1.cost=1', $url = '', $tplid);
            $cnotice = pdo_fetchall("select t1.* from" . tablename('xcommunity_cost_wechat') . "t1 left join" . tablename('xcommunity_cost_wechat_region') . "t2 on t1.id=t2.cid where t1.enable=1 and t2.regionid=:regionid", array('regionid' => $room['regionid']));
            foreach ($cnotice as $key => $val) {
                if ($val['type'] == 1 || $val['type'] == 3) {
                    util::sendTplNotice($val['openid'], $tplid, $content, $url = '', $topcolor = '#FF683F');
                }
            }
        }
        $url = $this->createMobileUrl('home');
    }
    else {
        $url = $this->createMobileUrl('paycenter', array('type' => 2, 'orderid' => $orderid));
    }
    $data = array();
    $data['url'] = $url;
    util::send_result($data);
}


