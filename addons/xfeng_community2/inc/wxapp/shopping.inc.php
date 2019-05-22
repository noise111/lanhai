<?php
/**
 * Created by xiaoqu.
 * User: zhoufeng
 * Time: 2017/12/10 下午10:41
 */
global $_GPC, $_W;
$ops = array('list', 'detail', 'confirm', 'order', 'myorder', 'update', 'cart', 'mycart', 'remove', 'delete', 'recancell', 'finish', 'applist', 'apporder', 'add', 'cashlog', 'appdel', 'orderdetail', 'change', 'checkgood');
$op = trim($_GPC['op']);
if (!in_array($op, $ops)) {
    util::send_error(-1, '参数错误');
}
$regionid = $_SESSION['community']['regionid'];
$uid = $_W['member']['uid'];
/**
 * 超市的商品列表
 */
if ($op == 'list') {
    $pindex = max(1, intval($_GPC['page']));
    $psize = max(20, intval($_GPC['psize']));
    $condition = 't1.uniacid=:uniacid and t1.status=1 and t1.type= 1 and t1.isshow=0 ';
    $params[':uniacid'] = $_W['uniacid'];
    $keyword = trim($_GPC['keyword']);
    if ($keyword) {
        $condition .= " and t1.title like :keyword";
        $params[':keyword'] = "%{$_GPC['keyword']}%";
    }
//    if (!empty($_GPC['keywords'])) {
//        $condition .= " and t1.title LIKE '%{$_GPC['keywords']}%'";
//    }
    if ($regionid) {
        $condition .= " and t2.regionid=:regionid";
        $params[':regionid'] = $regionid;
    }
    $category = intval($_GPC['cid']);
    if ($category) {
        $condition .= " and t1.pcate=:pcate";
        $params[':pcate'] = $category;
    }
    $sql = "select distinct t1.id,t1.id,t1.title,t1.thumb,t1.marketprice,t1.productprice,t1.thumb_url,t1.wlinks from" . tablename('xcommunity_goods') . "t1 left join" . tablename('xcommunity_goods_region') . "t2 on t1.id=t2.gid where $condition order by t1.displayorder asc limit " . ($pindex - 1) * $psize . ',' . $psize;
    $list = pdo_fetchall($sql, $params);
    foreach ($list as $k => $v) {
        $imgs = explode(',', $v['thumb_url']);
        $thumb = $v['thumb'] ? tomedia($v['thumb']) : tomedia($imgs[0]);
        $list[$k]['thumb'] = $thumb ? $thumb : MODULE_URL . 'template/mobile/default2/static/images/icon-zanwu.png';
        if ($v['wlinks']) {
            $list[$k]['link'] = $v['wlinks'];
        }
        else {
            $list[$k]['link'] = $this->createMobileUrl('shopping', array('op' => 'detail', 'id' => $v['id']));
        }
    }
    $data = array();
    $data['list'] = $list;
    $data['hstatus'] = set('p96') ? 1 : 0;
    $data['carttotal'] = model_shop::getCartTotal($regionid, 1);
    util::send_result($data);


}
/**
 * 商品详情
 */
if ($op == 'detail') {
    //商品详情
    $goodsid = intval($_GPC['id']);
    $goods = pdo_fetch("SELECT * FROM " . tablename('xcommunity_goods') . " WHERE id = :id and isshow=0", array(':id' => $goodsid));
    if (empty($goods)) {
        util::send_error(-1, '商品不存在');
    }
    $sql = "select t1.id,t1.title from" . tablename('xcommunity_region') . "t1 left join" . tablename('xcommunity_goods_region') . "t2 on t1.id=t2.regionid where t2.gid=:gid";
    $regions = pdo_fetchall($sql, array(':gid' => $goodsid));
    $regionids = '';
    $regiontitle = '';
    foreach ($regions as $k => $v) {
        $regiontitle .= $v['title'] . ',';
        $regionids .= $v['id'] . ',';
    }
    $regiontitle = xtrim($regiontitle);
    $regionids = xtrim($regionids);
    //展示幻灯多图
    $piclist = array();
    if ($goods['thumb_url']) {
        $thumbs = explode(',', $goods['thumb_url']);
        if ($thumbs) {
            foreach ($thumbs as $key => $value) {
//                $piclist[]['img'] = tomedia($value);
                $piclist[] = array(
                    'src'  => tomedia($value),
                    'msrc' => tomedia($value),
                );
            }
        }
    }
    //展示多图
    $images = array();
    if ($goods['thumbs']) {
        $imgs = explode(',', $goods['thumbs']);
        if ($imgs) {
            foreach ($imgs as $key => $val) {
//                $images[] = tomedia($val);
                $images[] = array(
                    'src'  => tomedia($val),
                    'msrc' => tomedia($val),
                );
            }
        }
    }
    $data = array();
//    $data = $goods;
    $data['thumbs'] = $images;
    $data['marketprice'] = $goods['marketprice'];
    $data['productprice'] = $goods['productprice'];
    $data['stock'] = $goods['total'];
    $data['carttotal'] = model_shop::getCartTotal($regionid, 1);
    $data['piclist'] = $piclist;
    $data['title'] = $goods['title'];
    $data['sold'] = $goods['sold'];
    $data['content'] = $goods['content'];
    $data['thumb'] = tomedia($goods['thumb']);
    $data['credit_swith'] = set('p12') ? 1 : 0;
    $data['credit'] = $goods['credit'];
    $data['region'] = $regiontitle;
    $data['regionids'] = $regionids;
    $data['hstatus'] = set('p96') ? 1 : 0;
    $data['desc'] = strip_tags(str_replace(array('<br>', '&nbsp;'), array("\n", ' '), $goods['content']), '<a>');
    $data['recommand'] = $goods['recommand'];
    util::send_result($data);
}
/**
 * 超市的提交订单信息
 */
if ($op == 'order') {
    // 商品的积分
    $zscredit = '0.00';
    $totalprice = 0;
    //结算商品信息
    $allgoods = array();
    //商品id
    $id = intval($_GPC['id']);
    $direct = false; //是否是直接购买
    //获取当前用户的信息
    if (!empty($id)) {
        //商品信息
        $item = pdo_fetch("select * from " . tablename("xcommunity_goods") . " where id=:id limit 1", array(":id" => $id));
        $thumbs = explode(',', $item['thumb_url']);
        $thumb = tomedia($item['thumb']) ? tomedia($item['thumb']) : tomedia($thumbs[0]);
        $item['thumb'] = $thumb ? $thumb : MODULE_URL . 'template/mobile/default2/static/images/icon-zanwu.png';
        $item['stock'] = $item['total'];
        $item['total'] = 1;
        $item['totalprice'] = $item['total'] * $item['marketprice'];
        $allgoods[] = $item;
        $totalprice += $item['totalprice'];
        $zscredit += $item['total'] * (float)$item['credit'];
    }
    else {
        $direct = true;
    }

    if ($direct) {
        //如果不是直接购买（从购物车购买）
        $goodids = ltrim(rtrim($_GPC['goodids'], ','), ',');
        $condition = empty($goodids) ? '' : 'AND id IN (' . $goodids . ")";
        $sql = "SELECT * FROM " . tablename('xcommunity_cart') . " WHERE  uniacid =:uniacid AND uid=:uid AND regionid=:regionid AND type=1 {$condition}";
        $params[':uniacid'] = $_W['uniacid'];
        $params[':uid'] = $_W['member']['uid'];
        $params[':regionid'] = $regionid;
        $list = pdo_fetchall($sql, $params);
        if (!empty($list)) {
            foreach ($list as &$g) {
                $item = pdo_fetch("select * from " . tablename("xcommunity_goods") . " where id=:id limit 1", array(":id" => $g['goodsid']));
                //属性
                $thumbs = explode(',', $item['thumb_url']);
                $thumb = tomedia($item['thumb']) ? tomedia($item['thumb']) : tomedia($thumbs[0]);
                $item['thumb'] = $thumb ? $thumb : MODULE_URL . 'template/mobile/default2/static/images/icon-zanwu.png';
                $item['stock'] = $item['total'];
                $item['total'] = $g['total'];
                $item['totalprice'] = $g['total'] * $item['marketprice'];
                $allgoods[] = $item;
                $totalprice += $item['totalprice'];
                $zscredit += $g['total'] * (float)$item['credit'];//赠送积分总数量
            }
            unset($g);
        }
    }

    $data = array();
    $data['list'] = $allgoods;

    $data['exp'] = set('p77') && ($totalprice < set('p79')) ? set('p78') : 0;
    $data['price'] = $totalprice;//商品总价
    $data['totalprice'] = $totalprice + $data['exp'];//应付价格

    $data['send_time_status'] = intval(set('p88'));
    $send_time = explode('|', set('p85'));
    $data['send_time'] = $send_time;
    $send_way = explode('|', set('p92'));
    $data['send_way'] = $send_way;
    $data['send_way_status'] = intval(set('p93'));
    $data['count'] = count($allgoods);

    $data['hstatus'] = set('p96') ? 1 : 0;
    $data['title'] = '订单详情';
    //是否开启货柜投放
    $data['counterStatus'] = intval(set('p105'));
    //赠送积分开关
    $data['zscreditSwith'] = set('p12') ? 1 : 0;
    //赠送积分总数量
    $data['zscredit'] = $zscredit;
    /**
     * 计算抵扣积分
     */
    //是否开启积分抵扣
    $data['dkcreditSwith'] = set('p156') ? 1 : 0;
    //抵扣积分数量
    $dkcredit = 0;
    if ($data['dkcreditSwith']) {
        $dkcredit = set('p158') ? set('p158') : 0;
    }
    $data['dkcredit'] = $dkcredit < $_W['member']['credit1'] ? $dkcredit : $_W['member']['credit1'];
    //积分抵扣比例
    $data['dkrate'] = set('p157') ? set('p157') : '0';
    util::send_result($data);
}
/**
 * 超市订单的提交
 */
if ($op == 'confirm') {
    $totalprice = 0;
    //结算商品信息
    $allgoods = array();

    //购买商品数量
    $total = intval($_GPC['total']);
    if ((empty($total)) || ($total < 1)) {
        $total = 1;
    }
    $direct = false; //是否是直接购买
    //获取当前用户的信息
    $id = intval($_GPC['id']);
    if (!empty($id)) {
        //商品信息
        $item = pdo_fetch("select * from " . tablename("xcommunity_goods") . " where id=:id limit 1", array(":id" => $id));
        $item['thumb'] = tomedia($item['thumb']);
        $item['stock'] = $item['total'];
        $item['total'] = $total;
        $item['totalprice'] = $item['total'] * $item['marketprice'];
        $allgoods[] = $item;
        $totalprice += $item['totalprice'];
    }
    else {
        $direct = true;
    }
    $regionid = $_SESSION['community']['regionid'];
    if ($direct) {
        //如果不是直接购买（从购物车购买）
        $goodids = xtrim(trim($_GPC['goodids']));
        $condition = empty($goodids) ? '' : 'AND id IN (' . $goodids . ")";
        $list = pdo_fetchall("SELECT * FROM " . tablename('xcommunity_cart') . " WHERE  uniacid =:uniacid AND uid=:uid AND regionid=:regionid AND type=1 $condition", array(':uniacid' => $_W['uniacid'], ':uid' => $_W['member']['uid'], ':regionid' => $regionid));

        if (!empty($list)) {
            foreach ($list as &$g) {
                $item = pdo_fetch("select * from " . tablename("xcommunity_goods") . " where id=:id limit 1", array(":id" => $g['goodsid']));
                //属性
                $item['thumb'] = tomedia($item['thumb']);
                $item['stock'] = $item['total'];
                $item['total'] = $g['total'];
                $item['totalprice'] = $g['total'] * $item['marketprice'];
                $allgoods[] = $item;
                $totalprice += $item['totalprice'];
            }
            unset($g);
        }
    }
    //是否开启积分抵扣
//    $creditChecked = intval($_GPC['creditChecked']);//判断是否启用
//    $totalprices = trim($_GPC['tprice']);
//    if ($creditChecked) {
//        $credit = trim($_GPC['credit']);//抵扣积分默认0
//        $creditTotal = trim($_GPC['creditTotal']);//积分抵扣费用
//        $totalprices = $totalprices + $creditTotal;
//    }
    /**
     * 处理传过来的金额、积分、减免
     */
    //支付金额
    $totalprice = $_GPC['totalprice'];//应付款
    $price = $totalprice;//实际付款
    //是否开启积分抵扣
    $creditChecked = intval($_GPC['creditChecked']);//判断是否启用
    if ($creditChecked) {
        $dkcredit = trim($_GPC['dkcredit']);//抵扣积分默认0
        $creditTotal = trim($_GPC['creditTotal']);//积分抵扣费用
        //$status = $price == $creditTotal ? 1 : 0;
        $totalprice = $totalprice + $creditTotal;
    }
    $data = array(
        'uniacid'     => $_W['uniacid'],
        'uid'         => $_W['member']['uid'],
        'ordersn'     => 'LN' . date('YmdHi') . random(10, 1),
        'price'       => $price,
        'status'      => !empty($price) ? 0 : 1,
        'remark'      => trim($_GPC['remark']),
        'createtime'  => TIMESTAMP,
        'regionid'    => $regionid,
        'type'        => 'shopping',
        'addressid'   => intval($_GPC['addressid']),
        'delivery'    => trim($_GPC['delivery']),
        'send_way'    => trim($_GPC['send_way']),
        'enable'      => 1,
        'littleid'    => intval($_GPC['littleid']),
        'credit'      => floatval($dkcredit),
        'total'       => floatval($totalprice),
        'offsetprice' => $creditTotal,
        'openid'      => $_W['openid']
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
            'total'      => $row['total'],
            'price'      => $row['marketprice'],
            'createtime' => TIMESTAMP,

        );
        pdo_insert('xcommunity_order_goods', $d);
        //删除购物车
        if ($direct) {
            pdo_delete("xcommunity_cart", array('uid' => $_W['member']['uid'], 'goodsid' => $row['id']));
        }
    }
    // 下单减少库存
    model_shop::setOrderStock($orderid);
    /**
     * 是否全额抵扣
     */
    if (empty($price)) {
        //抵扣积分

        //记录用户积分的操作日志
        //pdo_update('mc_members', array('credit1 -=' => $order['credit']), array('uid' => $_W['member']['uid']));
        mc_credit_update($data['uid'], 'credit1', -$data['credit'], array($data['uid'], '购买超市商品抵扣积分'));
        $creditdata1 = array(
            'uid'          => $data['uid'],
            'uniacid'      => $_W['uniacid'],
            'realname'     => $_W['member']['realname'],
            'mobile'       => $_W['member']['mobile'],
            'content'      => "订单号：" . $data['ordersn'] . ",减少积分",
            'credit'       => $data['credit'],
            'creditstatus' => 2,
            'createtime'   => TIMESTAMP,
            'type'         => 3,
            'typeid'       => $list[0]['shopid'] ? $list[0]['shopid'] : $item['shopid'],
            'category'     => 1,
            'usename'      => '系统'
        );
        pdo_insert('xcommunity_credit', $creditdata1);
        pdo_update('xcommunity_order', array('status' => 1, 'paytype' => 7), array('id' => $orderid));
        $content = '您本次共消费' . $totalprice . '元,' . '实际支付0元,消费' . $dkcredit . '积分。';

        $ret = util::sendnotice($content);
        $url = $this->createMobileUrl('home');
    }
    else {
        $url = $this->createMobileUrl('paycenter', array('type' => 1, 'orderid' => $orderid));
    }
    $data = array();
    $data['url'] = $url;
    util::send_result($data);
}
/**
 * 我的订单列表
 */
if ($op == 'myorder') {
    $pindex = max(1, intval($_GPC['page']));
    $psize = max(20, intval($_GPC['psize']));
    $status = intval($_GPC['status']);
    $where = " uniacid = :uniacid and uid=:uid and regionid=:regionid and status =:status and enable=1 ";
    $params[':uniacid'] = $_W['uniacid'];
    $params[':uid'] = $_W['member']['uid'];
    $params[':regionid'] = $regionid;
    $params[':status'] = $status;
    $sql = "SELECT * FROM " . tablename('xcommunity_order') . " WHERE $where AND type='shopping' ORDER BY id DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
    $list = pdo_fetchall($sql, $params);

    if (!empty($list)) {
        foreach ($list as &$row) {
            $goodsid = pdo_fetchall("SELECT goodsid,total FROM " . tablename('xcommunity_order_goods') . " WHERE orderid = '{$row['id']}'", array(), 'goodsid');
            $goods = pdo_fetchall("SELECT g.id, g.title, g.thumb, g.unit, g.marketprice,o.total,g.productprice,g.thumb_url FROM " . tablename('xcommunity_order_goods') . " o left join " . tablename('xcommunity_goods') . " g on o.goodsid=g.id "
                . " WHERE o.orderid='{$row['id']}'");
            foreach ($goods as $k => $v) {
                $imgs = explode(',', $v['thumb_url']);
                $goods[$k]['thumb'] = $v['thumb'] ? tomedia($v['thumb']) : tomedia($imgs[0]);
            }
            $row['goods'] = $goods;
            $row['total'] = $goodsid;
            $row['createtime'] = date('Y-m-d H:i', $row['createtime']);
            $row['url'] = $this->createMobileUrl('paycenter', array('type' => 1, 'orderid' => $row['id']));
        }
    }
    $data = array();
    $data['list'] = $list;
    $data['codeStatus'] = set('p163') ? 1 : 0;
    util::send_result($data);
}
/**
 * 购物车数量
 */
if ($op == 'cart') {
    $carttotal = model_shop::getCartTotal($regionid, 1);
    $data = array();
    $data['total'] = $carttotal;
    util::send_result($data);
}
/**
 * 删除购物车商品
 */
if ($op == 'remove') {
    $cartids = explode(',', $_GPC['cartids']);
    if (!empty($cartids)) {
        foreach ($cartids as $key => $cartid) {
            pdo_delete('xcommunity_cart', array('id' => $cartid));
        }
        $data = array();
        $data['content'] = '删除成功';
        util::send_result($data);
    }
}
/**
 * 删除订单
 */
if ($op == 'delete') {
    $orderid = intval($_GPC['orderid']);
    if ($orderid) {
        $item = pdo_get('xcommunity_order', array('id' => $orderid, 'uid' => $_W['member']['uid']), array());
        if ($item) {
            if (pdo_update('xcommunity_order', array('enable' => 2), array('id' => $orderid, 'uid' => $_W['member']['uid']))) {
                util::permlog('超市订单-删除', '订单号:' . $item['ordersn']);
                $data = array();
                $data['content'] = '删除成功';
                util::send_result($data);
            }
        }
    }
}
/**
 * 取消订单
 */
if ($op == 'recancell') {
    $orderid = intval($_GPC['orderid']);
    if ($orderid) {
        $item = pdo_get('xcommunity_order', array('id' => $orderid, 'uid' => $_W['member']['uid']), array());
        if ($item) {
            if (pdo_update('xcommunity_order', array('status' => 4), array('id' => $orderid, 'uid' => $_W['member']['uid']))) {
                util::permlog('超市订单-取消', '订单号:' . $item['ordersn']);
                $data = array();
                $data['content'] = '取消成功';
                util::send_result($data);
            }
        }
    }
}
/**
 * 完成订单
 */
if ($op == 'finish') {
    $orderid = intval($_GPC['orderid']);
    if ($orderid) {
        $item = pdo_get('xcommunity_order', array('id' => $orderid, 'uid' => $_W['member']['uid']), array());
        if ($item) {
            if (pdo_update('xcommunity_order', array('status' => 3), array('id' => $orderid, 'uid' => $_W['member']['uid']))) {
                util::permlog('超市订单-完成', '订单号:' . $item['ordersn']);
                $data = array();
                $data['content'] = '确认收货';
                util::send_result($data);
            }
        }
    }
}
/**
 * 检查商品的库存
 */
if ($op == 'checkgood') {
    $goods = $_GPC['goods'];
    $goodids = '';
    $content = '';
    foreach ($goods as $k => $good) {
        $item = pdo_get('xcommunity_goods', array('id' => $good['goodsid']), array('total', 'title'));
        if ($good['total'] <= $item['total'] && $item['total'] > 0) {
            $goodids .= $good['cartid'] . ',';
        }
        else {
            $content .= $good['title'] . ',';
        }
    }
    $data = array(
        'goodids' => xtrim($goodids),
        'content' => xtrim($content) . '没有库存了..',
    );
    util::send_result($data);

}

/**
 * 更新购物车
 */
if ($op == 'update') {
    $goodsid = intval($_GPC['id']);
    $goods = pdo_fetch("SELECT id,total,marketprice FROM " . tablename('xcommunity_goods') . " WHERE id = :id", array(':id' => $goodsid));
    if (empty($goods) || empty($goods['total'])) {
        util::send_error(-1, '库存不足,请购买其他产品!');
        exit();
    }
    $total = intval($_GPC['total']);
    $total = empty($total) ? 1 : $total;
    $total = $total > $goods['total'] ? $goods['total'] : $total;
    $cartid = intval($_GPC['cartid']);
    if ($cartid) {
        //更新购物车
        pdo_update('xcommunity_cart', array('total' => $total, 'type' => 1), array('id' => $cartid));
    }
    else {
        $marketprice = $goods['marketprice'];
        $regionid = $_SESSION['community']['regionid'];
        $row = pdo_fetch("SELECT id,total FROM " . tablename('xcommunity_cart') . " WHERE uid=:uid AND regionid=:regionid and uniacid = :uniacid AND goodsid = :goodsid  and type=:type", array(':uid' => $_W['member']['uid'], ':goodsid' => $goodsid, ':uniacid' => $_W['uniacid'], ':regionid' => $regionid, ':type' => 1));
        if ($row) {
            //判断是否超过库存
            $t = $total + $row['total'];
            $t = $t > $goods['total'] ? $goods['total'] : $t;
            pdo_update('xcommunity_cart', array('total' => $t), array('id' => $row['id']));
        }
        else {
            //不存在
            $data = array(
                'uniacid'     => $_W['uniacid'],
                'goodsid'     => $goodsid,
                'marketprice' => $marketprice,
                'total'       => $total,
                'uid'         => $_W['member']['uid'],
                'regionid'    => $regionid,
                'type'        => 1
            );
            pdo_insert('xcommunity_cart', $data);
        }
    }
    //获取购物车数量
    $carttotal = model_shop::getCartTotal($regionid, 1);
    $result = array(
        'result' => 1,
        'total'  => $carttotal
    );
    util::send_result($result);
}
/**
 * 我的购物车
 */
if ($op == 'mycart') {
    //显示购物车中商品
    $pindex = max(1, intval($_GPC['page']));
    $psize = max(20, intval($_GPC['psize']));
    $list = pdo_fetchall("SELECT * FROM " . tablename('xcommunity_cart') . " WHERE  uniacid = :uniacid AND uid=:uid AND regionid=:regionid and type=1 limit " . ($pindex - 1) * $psize . ',' . $psize, array(':uniacid' => $_W['uniacid'], ':uid' => $uid, ':regionid' => $regionid));
    $totalprice = 0;
    if (!empty($list)) {
        foreach ($list as &$item) {
            $goods = pdo_fetch("SELECT  id,title, thumb, marketprice, unit, total as goodtotal,productprice,thumb_url FROM " . tablename('xcommunity_goods') . " WHERE id=:id limit 1", array(":id" => $item['goodsid']));
            $item['goods'] = $goods;
            $item['maxtotal'] = $goods['goodtotal'];
            $item['totalprice'] = (floatval($goods['marketprice']) * intval($item['total']));
            $totalprice += $item['totalprice'];
            $imgs = explode(',', $goods['thumb_url']);
            $thumb = $goods['thumb'] ? tomedia($goods['thumb']) : tomedia($imgs[0]);
            $item['pic'] = $thumb ? $thumb : MODULE_URL . 'template/mobile/default2/static/images/icon-zanwu.png';
            $item['title'] = $goods['title'];
            $item['goodsid'] = $goods['id'];
        }
        unset($item);
    }
    $data = array();
    $data['list'] = $list;
    $data['totalprice'] = $totalprice;
    $data['hstatus'] = set('p96') ? 1 : 0;
    util::send_result($data);

}