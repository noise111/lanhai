<?php
/**
 * Created by njlanniu.com.
 * User: 蓝牛科技
 * Time: 2018/4/17 下午2:37
 */
global $_W, $_GPC;
$ops = array('list', 'detail', 'order', 'add', 'cashlog', 'del', 'orderdetail', 'ordersend', 'shops', 'store', 'storedetail', 'storeadd', 'storedel', 'change');
$op = trim($_GPC['op']);
if (!in_array($op, $ops)) {
    util::send_error(-1, '参数错误');
}
$user = util::xquser($_SESSION['appuid']);
$_SESSION['appregionids'] = $user['regionid'];
$_SESSION['appstore'] = $user['store'];
/**
 * 超市的商品列表
 */
if ($op == 'list') {
    $pindex = max(1, intval($_GPC['page']));
    $psize = max(15, intval($_GPC['psize']));
    $condition = ' uniacid=:uniacid and type = 1 and isshow=0 ';
    $parms[':uniacid'] = $_SESSION['appuniacid'];
    if (!empty($_GPC['keyword'])) {
        $condition .= " AND title LIKE '%{$_GPC['keyword']}%'";
    }
    if ($_SESSION['apptype'] && $_SESSION['apptype'] != 1) {
//        $condition .= " AND uid=:uid";
//        $parms[':uid'] = $_SESSION['appuid'];
        if ($_SESSION['apptype'] == 4) {
            $condition .= " and shopid in({$_SESSION['appstore']})";
        }
    }
    $sql = "SELECT * FROM " . tablename('xcommunity_goods') . " WHERE $condition ORDER BY id DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
    $list = pdo_fetchall($sql, $parms);
    foreach ($list as $k => $v) {
        $list[$k]['createtime'] = date('Y-m-d H:i', $v['createtime']);
        $imgs = explode(',', $v['thumb_url']);
        $thumb = $v['thumb'] ? tomedia($v['thumb']) : tomedia($imgs[0]);
        $list[$k]['src'] = $thumb ? $thumb : MODULE_URL . 'template/mobile/default2/static/images/icon-zanwu.png';
        $list[$k]['link'] = $this->createMobileUrl('xqsys', array('op' => 'shop', 'p' => 'detail', 'id' => $v['id']));

    }
    util::send_result($list);
}
/**
 * 超市的订单
 */
if ($op == 'order') {
    $pindex = max(1, intval($_GPC['page']));
    $psize = max(20, intval($_GPC['psize']));

    $condition = " t1.uniacid = :uniacid and t1.type='shopping' and t1.status=:status and t1.enable=1";
    $parms[':uniacid'] = $_SESSION['appuniacid'];
    $parms[':status'] = intval($_GPC['status']);
    if ($_SESSION['apptype'] != 1) {
//        $condition .= " AND t6.uid=:uid";
//        $parms[':uid'] = $_SESSION['appuid'];
        if ($_SESSION['apptype'] == 4) {
            $condition .= " and t6.shopid in({$_SESSION['appstore']})";
        }
    }
    if (!empty($_GPC['keyword'])) {
        $condition .= " AND (t1.ordersn LIKE '%{$_GPC['keyword']}%' or t3.realname LIKE '%{$_GPC['keyword']}%' or t3.mobile LIKE '%{$_GPC['keyword']}%')";
    }
    $sql = "select distinct t1.ordersn,t1.*,t2.realname,t2.mobile from" . tablename('xcommunity_order') . "t1 left join" . tablename('xcommunity_member_address') . "t2 on t1.addressid=t2.id left join" . tablename('mc_members') . "t4 on t4.uid= t1.uid left join" . tablename('xcommunity_order_goods') . "t5 on t5.orderid= t1.id left join" . tablename('xcommunity_goods') . "t6 on t6.id=t5.goodsid where $condition order by t1.createtime desc LIMIT " . ($pindex - 1) * $psize . ',' . $psize;

    $list = pdo_fetchall($sql, $parms);
    $paytype = array(
        '5' => array('css' => 'default', 'name' => '未支付'),
        '1' => array('css' => 'danger', 'name' => '余额支付'),
        '2' => array('css' => 'info', 'name' => '在线支付'),
        '3' => array('css' => 'warning', 'name' => '货到付款'),
        '4' => array('css' => 'info', 'name' => '后台支付')
    );
    $orderstatus = array(
        '-1' => array('css' => 'default', 'name' => '已关闭'),
        '0'  => array('css' => 'danger', 'name' => '待付款'),
        '1'  => array('css' => 'info', 'name' => '待发货'),
        '2'  => array('css' => 'warning', 'name' => '待收货'),
        '3'  => array('css' => 'success', 'name' => '已完成')
    );
    foreach ($list as $key => $value) {
        $list[$key]['link'] = $this->createMobileUrl('xqsys', array('op' => 'shop', 'p' => 'orderdetail', 'id' => $value['id']));
        $list[$key]['createtime'] = date('Y-m-d H:i', $value['createtime']);
        $s = $value['status'];
        $list[$key]['statuscss'] = $orderstatus[$value['status']]['css'];
        $list[$key]['status'] = $orderstatus[$value['status']]['name'];
        // $value['statuscss'] = $orderstatus[$value['status']]['css'];
        // $value['status'] = $orderstatus[$value['status']]['name'];
        if ($s < 1) {
            $list[$key]['css'] = $paytype[$s]['css'];
            if ($value['paytype'] == 0) {
                $t = 5;
                $list[$key]['paytype'] = $paytype[$t]['name'];
            }
            else {
                $list[$key]['paytype'] = $paytype[$s]['name'];
            }

            // $value['css'] = $paytype[$s]['css'];
            // $value['paytype'] = $paytype[$s]['name'];
            continue;
        }
        $list[$key]['css'] = $paytype[$value['paytype']]['css'];
        // $value['css'] = $paytype[$value['paytype']]['css'];
        if ($value['paytype'] == 2) {
            if (empty($value['transid'])) {
                $list[$key]['paytype'] = '支付宝支付';
                // $value['paytype'] = '支付宝支付';
            }
            else {
                $list[$key]['paytype'] = '微信支付';
                // $value['paytype'] = '微信支付';
            }
        }
        elseif ($value['paytype'] == 0) {
            $t = 5;
            $list[$key]['paytype'] = $paytype[$t]['name'];
        }
        else {
            $list[$key]['paytype'] = $paytype[$value['paytype']]['name'];
            // $value['paytype'] = $paytype[$value['paytype']]['name'];
        }
        $list[$key]['ordersn'] = chunk_split($value['ordersn']);
    }

    util::send_result($list);
}
/**
 * 超市商品的添加
 */
if ($op == 'add') {
    $id = intval($_GPC['id']);
    $data = array(
        'uniacid'      => intval($_W['uniacid']),
        'displayorder' => intval($_GPC['displayorder']),
        'title'        => $_GPC['title'],
        'pcate'        => intval($_GPC['parentid']),
        'child'        => intval($_GPC['childid']),
        'content'      => htmlspecialchars_decode($_GPC['content']),
        'unit'         => $_GPC['unit'],
        'createtime'   => TIMESTAMP,
        'total'        => intval($_GPC['total']),
        'marketprice'  => $_GPC['marketprice'],
        'productprice' => $_GPC['productprice'],
        'credit'       => intval($_GPC['credit']),
        'status'       => $_GPC['status'],
        'type'         => 1,
        'thumb_url'    => $_GPC['morepic'],
        'thumbs'       => $_GPC['moreimg'],
        'shopid'       => intval($_GPC['shopid']),
        'isshow'       => 0
    );
    if (empty($id)) {
        $data['uid'] = $_SESSION['appuid'];
        pdo_insert('xcommunity_goods', $data);
        $id = pdo_insertid();
        util::permlog('超市商品-添加', '信息标题:' . $data['title']);
    }
    else {
        unset($data['createtime']);
        pdo_update('xcommunity_goods', $data, array('id' => $id));
        pdo_delete('xcommunity_goods_region', array('gid' => $id));
        util::permlog('超市商品-修改', '信息标题:' . $data['title']);
    }
    $regionids = explode(',', $_GPC['regionids']);
    foreach ($regionids as $key => $value) {
        $dat = array(
            'gid'      => $id,
            'regionid' => $value,
        );
        pdo_insert('xcommunity_goods_region', $dat);
    }
    util::send_result();
}
/**
 * 超市的提现记录
 */
if ($op == 'cashlog') {
    $condition = " uniacid=:uniacid and type='cash'";
    $parms[':uniacid'] = $_SESSION['appuniacid'];
    if ($_SESSION['apptype'] != 1) {
        $condition .= " and uid=:uid";
        $parms[':uid'] = $_SESSION['appuid'];
    }

    $pindex = max(1, intval($_GPC['page']));
    $psize = max(20, intval($_GPC['psize']));
    $sql = "select * from" . tablename('xcommunity_order') . "where $condition ORDER BY createtime DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
    $list = pdo_fetchall($sql, $parms);
    foreach ($list as $key => $value) {
        $list[$key]['createtime'] = date('Y-m-d H:i', $value['createtime']);
        $list[$key]['status'] = empty($value['status']) ? '未处理' : '已处理';
    }
    util::send_result($list);
}
/**
 * 超市商品的删除
 */
if ($op == 'del') {
    $id = intval($_GPC['id']);
    $item = pdo_getcolumn('xcommunity_goods', array('id' => $id), 'id');
    if (empty($item)) {
        util::send_error(-1, '参数错误');
    }
//    if (pdo_delete('xcommunity_goods', array('id' => $id))) {
    if (pdo_update('xcommunity_goods', array('isshow' => 1), array('id' => $id))) {
        pdo_delete('xcommunity_goods_region', array('gid' => $id));
        util::send_result();
    }
}
/**
 * 超市商品的详情
 */
if ($op == 'detail') {
    //商品详情
    $goodsid = intval($_GPC['id']);
    if (empty($goodsid)) {
        util::send_error(-1, 'id is null');
        exit();
    }
    $goods = pdo_fetch("SELECT * FROM " . tablename('xcommunity_goods') . " WHERE id = :id", array(':id' => $goodsid));
    if (empty($goods)) {
        util::send_error(-1, '数据不存在');
        exit();
    }
    $sql = "select t1.id,t1.title from" . tablename('xcommunity_region') . "t1 left join" . tablename('xcommunity_goods_region') . "t2 on t1.id=t2.regionid where t2.gid=:gid";

    $regions = pdo_fetchall($sql, array(':gid' => $goodsid));
//    $goods['regions'] = $regions;
//    $region ='';
//    foreach ($regions as $k => $v){
//        $region .= $v['title'].',';
//    }
//    $goods['region'] = xtrim($region);
    $regionids = '';
    $regiontitle = '';
    $all = array();
    foreach ($regions as $k => $v) {
        $regiontitle .= $v['title'] . ',';
        $regionids .= $v['id'] . ',';
        $all[] = $v['id'];
    }
    $regiontitle = xtrim($regiontitle);
    $regionid = xtrim($regionids);
    //展示多图
    $piclist = array();
    if ($goods['thumb_url']) {
        $thumbs = explode(',', $goods['thumb_url']);
        if ($thumbs) {
            foreach ($thumbs as $key => $value) {
                $piclist[] = tomedia($value);
            }
        }
    }
    //展示多图
    $images = array();
    if ($goods['thumbs']) {
        $imgs = explode(',', $goods['thumbs']);
        if ($imgs) {
            foreach ($imgs as $key => $val) {
                $images[] = tomedia($val);
            }
        }
    }
    $data = array();
    $data = $goods;
    $data['thumbs'] = $images;
    $data['marketprice'] = $goods['marketprice'];
    $data['productprice'] = $goods['productprice'];
    $data['stock'] = $goods['total'];

    $data['piclist'] = $piclist;
    $data['title'] = $goods['title'];
    $data['sold'] = $goods['sold'];
    $data['content'] = $goods['content'];
    $data['thumb'] = tomedia($goods['thumb']);
    $data['credit_swith'] = set('p12') ? 1 : 0;
    $credit = set('p13');
    $data['credit'] = $data['marketprice'] * (float)$credit;
    $data['region'] = $regiontitle;
    $data['regionids'] = $regionid;
    $data['_status'] = set('p96') ? 1 : 0;
    $data['desc'] = strip_tags(str_replace(array('<br>', '&nbsp;'), array("\n", ' '), $goods['content']), '<a>');
    $childs = pdo_getall('xcommunity_category', array('parentid' => $goods['pcate']), array('id', 'name'));
    foreach ($childs as $ke => $va) {
        $childs[$ke]['key'] = $va['id'];
        $childs[$ke]['value'] = $va['name'];
    }
    $data['list'] = $all;
    $data['childs'] = $childs;
    $data['url'] = $this->createMobileUrl('xqsys', array('op' => 'shop', 'p' => 'add', 'id' => $goods['id']));
    util::send_result($data);
}
/**
 * 改变超市订单的状态
 */
if ($op == 'change') {
    $id = intval($_GPC['id']);
    $item = pdo_fetch("select status from" . tablename('xcommunity_order') . " where id=:id", array(':id' => $id));
    if (empty($item)) {
        util::send_error(-1, '参数错误');
    }
    if ($item['status'] == 0) {
        $status = 1;
    }
    elseif ($item['status'] == 1) {
        $status = 2;
    }
    elseif ($item['status'] == 2) {
        $status = 3;
    }
    if (pdo_update('xcommunity_order', array('status' => $status), array('id' => $id))) {
        util::send_result();
    }
}
/**
 * 超市订单的详情
 */
if ($op == 'orderdetail') {
    $id = intval($_GPC['id']) ? intval($_GPC['id']) : 259;
//    $user = util::xquser($_SESSION['appuid']);
    $cond = "t1.id=:orderid";
    $params[':orderid'] = $id;
//    if ($user) {
//        if ($user['uuid']) {
//            //判断上级管理员是否是超市
//            $suser = pdo_get("xcommunity_users", array('uid' => $user['uuid']), array());
//            $uid = $suser['type'] == 4 ? $suser['uid'] : $_SESSION['appuid'];
//            $cond .= " and t6.uid=:uid";
//            $params[':uid'] = $uid;
//        } else {
//            $cond .= " and t6.uid=:uid";
//            $params[':uid'] = $_SESSION['appuid'];
//        }
//    }
    $sql = "select t1.*,t2.realname,t2.mobile,t2.address,t2.city,t5.price as totalprice,t5.total from" . tablename('xcommunity_order') . "t1 left join" . tablename('xcommunity_member_address') . "t2 on t1.addressid=t2.id left join" . tablename('mc_members') . "t4 on t4.uid= t1.uid left join" . tablename('xcommunity_order_goods') . "t5 on t5.orderid= t1.id left join" . tablename('xcommunity_goods') . "t6 on t6.id=t5.goodsid where $cond ";
    $item = pdo_fetch($sql, $params);
    $condition = "o.orderid=:orderid";
    $paras[':orderid'] = $id;
//    if ($user) {
//        if ($user['uuid']) {
//            //判断上级管理员是否是超市
//            $suser = pdo_get("xcommunity_users", array('uid' => $user['uuid']), array());
//            $uid = $suser['type'] == 4 ? $suser['uid'] : $_SESSION['appuid'];
//            $condition .= " and g.uid=:uid";
//            $paras[':uid'] = $uid;
//
//        } else {
//            $condition .= " and g.uid=:uid";
//            $paras[':uid'] = $_SESSION['appuid'];
//        }
//    }
    $goods = pdo_fetchall("SELECT g.*, o.total,o.price as orderprice FROM " . tablename('xcommunity_order_goods') . " o left join " . tablename('xcommunity_goods') . " g on o.goodsid=g.id " . " WHERE $condition", $paras);
    $content = '';
    $price = '';
    foreach ($goods as $k => $v) {
//        $goods[$k]['goods'] = ($k+1)."商品:".$v['title'].",数量：".$v['total'].",单价：".$v['marketprice']."元</br>";
        $content .= ($k + 1) . "、商品:" . $v['title'] . ",数量：" . $v['total'] . ",单价：" . $v['marketprice'] . "元\n";
        $price += $v['orderprice'] * $v['total'];
    }
    $item['goods'] = $goods;
    $item['content'] = $content;
    $item['price'] = $user && set('p109', '', $item['uniacid']) ? $price : $item['price'];
    $item['apptype'] = $_SESSION['apptype'];
    $item['offcodeStatus'] = set('p163', '', $item['uniacid']) ? set('p163', '', $item['uniacid']) : 0;
    $item['offcode'] = $item['code'];
    util::send_result($item);
}
/**
 * 超市订单发货
 */
if ($op == 'ordersend') {
    $id = intval($_GPC['id']);
    if (empty($id)) {
        util::send_error(-1, '参数id错误');
    }
//    $user = util::xquser($_SESSION['appuid']);
//    echo $_SESSION['appuid'];
//    print_r($user);
    $cond = " t1.id=:orderid";
    $params[':orderid'] = $id;
//    if ($user) {
//        if ($user['uuid']) {
//            //判断上级管理员是否是超市
//            $suser = pdo_get("xcommunity_users", array('uid' => $user['uuid']), array());
//            $uid = $suser['type'] == 4 ? $suser['uid'] : $_SESSION['appuid'];
//            $cond .= " and t6.uid=:uid";
//            $params[':uid'] = $uid;
//        } else {
//            $cond .= " and t6.uid=:uid";
//            $params[':uid'] = $_SESSION['appuid'];
//        }
//    }
    if ($id) {
//        $sql = "select t1.*,t4.realname as address_realname,t4.mobile as address_telephone,t2.realname,t2.mobile,t2.address,t5.title,t1.regionid,t4.uid,t5.title,t6.openid,t1.addressid,t2.city,t5.price as totalprice,t5.total from" . tablename('xcommunity_order') . "t1 left join" . tablename('xcommunity_member_address') . "t2 on t1.addressid = t2.id left join" . tablename('mc_members') . "t4 on t4.uid=t1.uid left join" . tablename('xcommunity_region') . "t5 on t1.regionid=t5.id left join" . tablename('mc_mapping_fans') . "t6 on t6.uid = t1.uid where $cond";
        $item = pdo_fetch("select t1.*,t2.realname,t2.mobile,t2.address,t2.city,t5.price as totalprice,t5.total,t7.openid from" . tablename('xcommunity_order') . "t1 left join" . tablename('xcommunity_member_address') . "t2 on t1.addressid=t2.id left join" . tablename('mc_members') . "t4 on t4.uid= t1.uid left join" . tablename('xcommunity_order_goods') . "t5 on t5.orderid= t1.id left join" . tablename('xcommunity_goods') . "t6 on t6.id=t5.goodsid left join" . tablename('mc_mapping_fans') . "t7 on t7.uid = t1.uid where $cond ", $params);
    }
//    print_r($item);exit();
    $condition = " o.orderid=:orderid ";
    $paras[':orderid'] = $id;
//    if ($user) {
//        if ($user['uuid']) {
//            //判断上级管理员是否是超市
//            $suser = pdo_get("xcommunity_users", array('uid' => $user['uuid']), array());
//            $uid = $suser['type'] == 4 ? $suser['uid'] : $_SESSION['appuid'];
//            $condition .= " and g.uid=:uid";
//            $paras[':uid'] = $uid;
//
//        } else {
//            $condition .= " and g.uid=:uid";
//            $paras[':uid'] = $_SESSION['appuid'];
//        }
//    }
    $goods = pdo_fetchall("SELECT g.*, o.total,o.price as orderprice FROM " . tablename('xcommunity_order_goods') .
        " o left join " . tablename('xcommunity_goods') . " g on o.goodsid=g.id " . " WHERE $condition", $paras);
    $item['goods'] = $goods;

//    $title = '';
//    $count = count($goods);
//    if ($count == 1) {
//        foreach ($goods as $key => $value) {
//            $title = $value['title'];
//        }
//    } else {
//        foreach ($goods as $key => $value) {
//            $title .= $value['title'] . ',';
//        }
//    }
    $content = '';
    $price = '';
    foreach ($goods as $k => $v) {
//        $goods[$k]['goods'] = ($k+1)."商品:".$v['title'].",数量：".$v['total'].",单价：".$v['marketprice']."元</br>";
        $content .= ($k + 1) . "、商品:" . $v['title'] . ",数量：" . $v['total'] . ",单价：" . $v['marketprice'] . "元\n";
        $price += $v['orderprice'] * $v['total'];
    }
    $item['price'] = $user && set('p109') ? $price : $item['price'];
    $expressrel = $_GPC['realname'];
    $expresscom = $_GPC['company'];
    $expresssn = $_GPC['express'];
    $data = array(
        'realname' => $expressrel,
        'company'  => $expresscom,
        'express'  => $expresssn,
        'status'   => 2
    );
    pdo_update('xcommunity_order', $data, array('id' => $item['id']));
    util::permlog('', '超市订单发货,订单号:' . $item['ordersn']);
    if (set('s2') && set('s6')) {
        $type = set('s1') == 1 ? 'wwt' : 'juhe';
        $sdst = $item['mobile'];
        if ($type == 'wwt') {
            $smsg = "您的快递是" . $expresscom . ",快递单号" . $expresssn . "。有任何问题请随时与我们联系，谢谢。";
            $content = sms::send($sdst, '', $smsg, $type);
        }
        else {
            $smsg = urlencode("#express_name#=$expresscom&#express_code#=$expresssn");
            $content = sms::send($sdst, $smsg, '', 1, set('s10'));
        }
        $d = array(
            'uniacid'    => $_W['uniacid'],
            'sendid'     => $item['id'],
            'uid'        => $item['uid'],
            'type'       => 6,
            'cid'        => 2,
            'status'     => 1,
            'createtime' => TIMESTAMP,
            'regionid'   => $item['regionid']
        );
        pdo_insert('xcommunity_send_log', $d);

    }
    if (set('t15')) {
        $createtime = date('Y-m-d H:i:s', $_W['timestamp']);
        $content = array(
            'first'    => array(
                'value' => '发货啦，小主人，我是您的商品呀，老板已经安排发货了，我和您即将团聚了，等我哟！',
            ),
            'keyword1' => array(
                'value' => $item['price'] . '元',
            ),
            'keyword2' => array(
                'value' => $content,
            ),
            'keyword3' => array(
                'value' => $item['realname'] . ',' . $item['mobile'] . ',' . $item['city'] . ',' . $item['address'],
            ),
            'keyword4' => array(
                'value' => $item['ordersn'],
            ),
            'keyword5' => array(
                'value' => $expresscom . '(' . $expressrel . '),' . $expresssn,
            ),
            'remark'   => array(
                'value' => '有任何问题请随时与我们联系，谢谢。',
            ),
        );
        $tplid = set('t16');
        if ($item['openid']) {
            $status = util::sendTplNotice($item['openid'], $tplid, $content, $url = '');
        }

        $d = array(
            'uniacid'    => $_W['uniacid'],
            'sendid'     => $item['id'],
            'uid'        => $item['uid'],
            'type'       => 6,
            'cid'        => 1,
            'status'     => 1,
            'createtime' => TIMESTAMP,
            'regionid'   => $item['regionid']
        );
        pdo_insert('xcommunity_send_log', $d);
    }
    if (set('p105')) {
        $notice = pdo_fetch("select openid from" . tablename('xcommunity_counter_notice') . "where enable=1 and regionid=:regionid and type in(1,3)", array(':regionid' => $item['regionid']));
    }
    util::send_result();
}
/**
 * 超市的店铺列表
 */
if ($op == 'shops') {
    $condition = ' uniacid = :uniacid and status=1';
    $params[':uniacid'] = $_SESSION['appuniacid'];
    if ($_SESSION['apptype'] == 2 || $_SESSION['apptype'] == 3 || $_SESSION['apptype'] == 4) {
        //普通管理员
        if ($_SESSION['apptype'] == 4) {
            $condition .= " and id in({$_SESSION['appstore']})";
        }
        else {
            $condition .= " and uid =:uid";
            $params[':uid'] = $_SESSION['appuid'];
        }
    }
    if ($_SESSION['apptype'] == 5) {
        $data['list'] = array();
        util::send_result($data);
    }
    $sql = "SELECT id,title FROM " . tablename('xcommunity_shop') . " WHERE $condition ";
    $list = pdo_fetchall($sql, $params);
    $data = array();
    if ($list) {
        foreach ($list as $k => $v) {
            $data[] = array(
                'value' => $v['title'],
                'key'   => $v['id'],
            );
        }
    }
    util::send_result($data);
}
/**
 * 超市的列表
 */
if ($op == 'store') {
    $pindex = max(1, intval($_GPC['page']));
    $psize = max(15, intval($_GPC['psize']));
    $condition = ' uniacid=:uniacid and type = 1 and status=1';
    $parms[':uniacid'] = $_SESSION['appuniacid'];
    if ($_SESSION['apptype'] && $_SESSION['apptype'] != 1) {
//        $condition .= " AND uid=:uid";
//        $parms[':uid'] = $_SESSION['appuid'];
        if ($_SESSION['apptype'] == 4) {
            $condition .= " and id in({$_SESSION['appstore']})";
        }
    }
//    echo $_SESSION['appstore'];
    $sql = "SELECT * FROM " . tablename('xcommunity_shop') . " WHERE $condition ORDER BY id DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
    $list = pdo_fetchall($sql, $parms);
    foreach ($list as $k => $v) {
        $list[$k]['createtime'] = date('Y-m-d H:i', $v['createtime']);
        $list[$k]['link'] = $this->createMobileUrl('xqsys', array('op' => 'shop', 'p' => 'storedetail', 'id' => $v['id']));

    }
    util::send_result($list);
}
/**
 * 超市的详情
 */
if ($op == 'storedetail') {
    $id = intval($_GPC['id']);
    if (empty($id)) {
        util::send_error(-1, '超市不存在');
        exit();
    }
    $data = array();
    $item = pdo_fetch("SELECT * FROM" . tablename('xcommunity_shop') . "WHERE id=:id", array(':id' => $id));
    if (empty($item)) {
        util::send_error(-1, '超市不存在');
        exit();
    }
    $data['item'] = $item;
    $data['item']['url'] = $this->createMobileUrl('xqsys', array('op' => 'shop', 'p' => 'storeadd', 'id' => $item['id']));
    util::send_result($data);
}
/**
 * 超市的添加
 */
if ($op == 'storeadd') {
    $id = intval($_GPC['id']);
    $data = array(
        'uniacid'     => $_W['uniacid'],
        'title'       => $_GPC['title'],
        'contactname' => $_GPC['contactname'],
        'mobile'      => $_GPC['mobile'],
        'createtime'  => TIMESTAMP,
        'type'        => 1,
        'status'      => 1
    );
    if (empty($id)) {
        if ($user['uuid']) {
            //判断上级管理员是否是超市
            $suser = pdo_get("xcommunity_users", array('uid' => $user['uuid']), array());
            $data['uid'] = $suser['type'] == 4 ? $user['uuid'] : $_SESSION['appuid'];
        }
        else {
            $data['uid'] = $_SESSION['appuid'];
        }
        pdo_insert('xcommunity_shop', $data);
        $dpid = pdo_insertid();
        if ($_SESSION['appuid'] !== 1 && $user) {
            //超市管理员和超市绑定
            if ($suser['type'] == 4) {
                //上一级是超市
                $store = $suser['store'] . ',' . $dpid;
                pdo_update('xcommunity_users', array('store' => $store), array('id' => $suser['id']));
            }
            else {
                $store = $user['store'] . ',' . $dpid;
                pdo_update('xcommunity_users', array('store' => $store), array('id' => $user['id']));
            }
        }
        util::permlog('超市店铺-添加', '信息标题:' . $data['title']);
    }
    else {
        pdo_update('xcommunity_shop', $data, array('id' => $id));
        util::permlog('超市店铺-修改', '信息标题:' . $data['title']);
    }
    util::send_result();
}
/**
 * 超市的删除
 */
if ($op == 'storedel') {
    $id = intval($_GPC['id']);
    if ($id) {
        $item = pdo_get('xcommunity_shop', array('id' => $id), array('id'));
        if ($item) {
            if (pdo_update('xcommunity_shop', array('status' => 2), array('id' => $id))) {
                util::send_result();
            }
        }
    }
}