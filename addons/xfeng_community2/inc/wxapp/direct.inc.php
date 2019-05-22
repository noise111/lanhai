<?php
/**
 * Created by xiaoqu.
 * User: zhoufeng
 * Time: 2017/12/10 下午10:41
 */
global $_GPC, $_W;
$ops = array('list', 'detail', 'confirm', 'order', 'myorder', 'update', 'cart', 'mycart', 'remove', 'delete', 'recancell', 'finish');
$op = trim($_GPC['op']);
if (!in_array($op, $ops)) {
    util::send_error(-1, '参数错误');
}
$regionid = $_SESSION['community']['regionid'];
if ($op == 'list') {
    $pindex = max(1, intval($_GPC['page']));
    $psize = max(20, intval($_GPC['psize']));
    $condition = 't1.uniacid=:uniacid and t1.status=1 and t1.type= 3 ';
    $params[':uniacid'] = $_W['uniacid'];
    $keyword = trim($_GPC['keyword']);
    if ($keyword) {
        $condition .= " and t1.title like :keyword";
        $params[':keyword'] = "%{$_GPC['keyword']}%";
    }
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
            $list[$k]['link'] = $this->createMobileUrl('direct', array('op' => 'detail', 'id' => $v['id']));
        }
    }
    $data = array();
    $data['list'] = $list;
    $data['hstatus'] = set('p96') ? 1 : 0;
    $data['carttotal'] = model_shop::getCartTotal($regionid, 2);
    util::send_result($data);
}
elseif ($op == 'detail') {
    //商品详情
    $goodsid = intval($_GPC['id']);
    $goods = pdo_fetch("SELECT * FROM " . tablename('xcommunity_goods') . " WHERE id = :id", array(':id' => $goodsid));
    if (empty($goods)) {
        util::send_error(-1, '参数错误');
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
    $data = $goods;
    $data['thumbs'] = $images;
    $data['marketprice'] = $goods['marketprice'];
    $data['productprice'] = $goods['productprice'];
    $data['stock'] = $goods['total'];
    $data['carttotal'] = model_shop::getCartTotal($regionid, 2);
    $data['piclist'] = $piclist;
    $data['title'] = $goods['title'];
    $data['sold'] = $goods['sold'];
    $data['content'] = $goods['content'];
    $data['thumb'] = tomedia($goods['thumb']);
    $data['credit_swith'] = set('p128') ? 1 : 0;
    $credit = set('p129');
    $data['credit'] = $data['marketprice'] * (float)$credit;
    $data['region'] = $regiontitle;
    $data['regionids'] = $regionids;
    $data['hstatus'] = set('p96') ? 1 : 0;
    $data['desc'] = strip_tags(str_replace(array('<br>', '&nbsp;'), array("\n", ' '), $goods['content']), '<a>');
    $data['recommand'] = $goods['recommand'];
    util::send_result($data);
}
elseif ($op == 'order') {
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
    }
    else {
        $direct = true;
    }
    if ($direct) {
        //如果不是直接购买（从购物车购买）
        $goodids = ltrim(rtrim($_GPC['goodids'], ','), ',');
        $condition = empty($goodids) ? '' : 'AND id IN (' . $goodids . ")";
        $sql = "SELECT * FROM " . tablename('xcommunity_cart') . " WHERE  uniacid =:uniacid AND uid=:uid AND regionid=:regionid AND type=2 {$condition}";
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
            }
            unset($g);
        }
    }

    $data = array();
    $data['list'] = $allgoods;

    $data['exp'] = set('p130') && ($totalprice < set('p132')) ? set('p131') : 0;
    $data['price'] = $totalprice;
    $data['totalprice'] = $totalprice + $data['exp'];
    $data['credit'] = $totalprice * (float)set('p129');
    $data['send_time_status'] = set('p133');
    $send_time = explode('|', set('p134'));
    $data['send_time'] = $send_time;
    $send_way = explode('|', set('p136'));
    $data['send_way'] = $send_way;
    $data['send_way_status'] = set('p135');
    $data['count'] = count($allgoods);
    $data['credit_swith'] = set('p128') ? 1 : 0;
    $data['hstatus'] = set('p96') ? 1 : 0;
    $data['title'] = '订单详情';
    util::send_result($data);
}
elseif ($op == 'confirm') {
    $totalprice = 0;
    //结算商品信息
    $allgoods = array();
    //商品id
    $id = intval($_GPC['id']);
    //购买商品数量
    $total = intval($_GPC['total']);
    if ((empty($total)) || ($total < 1)) {
        $total = 1;
    }
    $direct = false; //是否是直接购买
    //获取当前用户的信息
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
        $list = pdo_fetchall("SELECT * FROM " . tablename('xcommunity_cart') . " WHERE  uniacid =:uniacid AND uid=:uid AND regionid=:regionid AND type=2 $condition", array(':uniacid' => $_W['uniacid'], ':uid' => $_W['member']['uid'], ':regionid' => $regionid));

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
    $data = array(
        'uniacid'    => $_W['uniacid'],
        'uid'        => $_W['member']['uid'],
        'ordersn'    => 'LN' . date('YmdHi') . random(10, 1),
        'price'      => trim($_GPC['tprice']),
        'status'     => 0,
        'remark'     => trim($_GPC['remark']),
        'createtime' => TIMESTAMP,
        'regionid'   => $regionid,
        'type'       => 'direct',
        'addressid'  => intval($_GPC['addressid']),
        'delivery'   => trim($_GPC['delivery']),
        'send_way'   => trim($_GPC['send_way']),
        'enable'     => 1
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

    $url = $this->createMobileUrl('paycenter', array('type' => 8, 'orderid' => $orderid));
    $data = array();
    $data['url'] = $url;
    util::send_result($data);
}
elseif ($op == 'myorder') {
    $pindex = max(1, intval($_GPC['page']));
    $psize = max(20, intval($_GPC['psize']));
    $status = intval($_GPC['status']);
    $where = " uniacid = :uniacid and uid=:uid and regionid=:regionid and status =:status and enable=1 ";
    $params[':uniacid'] = $_W['uniacid'];
    $params[':uid'] = $_W['member']['uid'];
    $params[':regionid'] = $regionid;
    $params[':status'] = $status;
    $sql = "SELECT * FROM " . tablename('xcommunity_order') . " WHERE $where AND type='direct' ORDER BY id DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
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

    util::send_result($data);
}
elseif ($op == 'update') {
    $goodsid = intval($_GPC['id']);//商品id
    $total = intval($_GPC['total']);
    $total = empty($total) ? 1 : $total;
    $goods = pdo_fetch("SELECT id,total,marketprice FROM " . tablename('xcommunity_goods') . " WHERE id = :id", array(':id' => $goodsid));
    if (empty($goods) || empty($goods['total'])) {
        util::send_error(-1, '库存不足,请购买其他产品!');
        exit();
    }

    $cartid = intval($_GPC['cartid']);
    if ($cartid) {
        if ($total >= $goods['total']) {
            util::send_error(-1, '库存不足,请购买其他产品!');
            exit();
        }
        //更新购物车
        pdo_update('xcommunity_cart', array('total' => $total, 'type' => 2), array('id' => $cartid));
        exit();
    }
    $marketprice = $goods['marketprice'];
    $regionid = $_SESSION['community']['regionid'];
    $row = pdo_fetch("SELECT id,total FROM " . tablename('xcommunity_cart') . " WHERE uid=:uid AND regionid=:regionid and uniacid = :uniacid AND goodsid = :goodsid and type=:type ", array(':uid' => $_W['member']['uid'], ':goodsid' => $goodsid, ':uniacid' => $_W['uniacid'], ':regionid' => $regionid,'type' => 2));

    if ($row) {
        //判断是否超过库存
        $t = $total + $row['total'];
        if ($t > $goods['total']) {
            $result = array(
                'result' => 0,
                'maxbuy' => $goods['total']
            );
            util::send_result($result);
//            die(json_encode($result));
            exit();
        }
    }

    if (empty($row)) {
        //不存在
        $data = array(
            'uniacid'     => $_W['uniacid'],
            'goodsid'     => $goodsid,
            'marketprice' => $marketprice,
            'total'       => $total,
            'uid'         => $_W['member']['uid'],
            'regionid'    => $regionid,
            'type'        => 2
        );
        pdo_insert('xcommunity_cart', $data);
    }
    else {
        //累加最多限制购买数量
        $t = $total + $row['total'];
        // if (!empty($goods['maxbuy'])) {
        // 	if ($t > $goods['maxbuy']) {
        // 		$t = $goods['maxbuy'];
        // 	}
        // }
        if (empty($t)) {
            pdo_delete('xcommunity_cart', array('id' => $row['id']));
        }
        else {
            //存在
            $data = array(
                'marketprice' => $marketprice,
                'total'       => $t,
            );
            pdo_update('xcommunity_cart', $data, array('id' => $row['id']));
        }

    }
    //获取购物车数量
    $carttotal = model_shop::getCartTotal($regionid, 2);
    $result = array(
        'result' => 1,
        'total'  => $carttotal
    );
    util::send_result($result);
}
elseif ($op == 'cart') {
    $carttotal = model_shop::getCartTotal($regionid, 2);
    $data = array();
    $data['total'] = $carttotal;
    util::send_result($data);
}
elseif ($op == 'mycart') {
    //显示购物车中商品
    $pindex = max(1, intval($_GPC['page']));
    $psize = max(20, intval($_GPC['psize']));
    $list = pdo_fetchall("SELECT * FROM " . tablename('xcommunity_cart') . " WHERE  uniacid = :uniacid AND uid=:uid AND regionid=:regionid and type=2 limit " . ($pindex - 1) * $psize . ',' . $psize, array(':uniacid' => $_W['uniacid'], ':uid' => $_W['member']['uid'], ':regionid' => $_SESSION['community']['regionid']));
    $totalprice = 0;
    if (!empty($list)) {
        foreach ($list as &$item) {
            $goods = pdo_fetch("SELECT  id,title, thumb, marketprice, unit, total as goodtotal,productprice,thumb_url FROM " . tablename('xcommunity_goods') . " WHERE id=:id limit 1", array(":id" => $item['goodsid']));
            $item['goods'] = $goods;
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
elseif ($op == 'remove') {
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
elseif ($op == 'delete') {
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
elseif ($op == 'recancell') {
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
elseif ($op == 'finish') {
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

