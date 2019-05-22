<?php
/**
 * Created by xiaoqu.
 * User: zhoufeng
 * Time: 2017/12/10 下午5:25
 */
global $_GPC, $_W;
$ops = array('list', 'detail', 'add', 'confirm', 'order', 'delete', 'hx', 'rank', 'rankList');
$op = trim($_GPC['op']);
if (!in_array($op, $ops)) {
    util::send_error(-1, '参数错误');
}
/**
 * 团购商品的列表
 */
if ($op == 'list') {
    $pindex = max(1, intval($_GPC['page']));
    $psize = max(20, intval($_GPC['psize']));
    $sql = "SELECT * FROM" . tablename('xcommunity_goods') . "WHERE uniacid=:uniacid AND  type = 2 AND dpid = :dpid and status =1 order by id desc LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
    $params[':uniacid'] = $_W['uniacid'];
    $params[':dpid'] = intval($_GPC['dpid']);
    $list = pdo_fetchall($sql, $params);
    foreach ($list as $k => $v) {
        $imgs = explode(',', $v['thumb_url']);
        $thumb = $v['thumb'] ? tomedia($v['thumb']) : tomedia($imgs[0]);
        $list[$k]['thumb'] = $thumb ? $thumb : MODULE_URL . 'template/mobile/default2/static/images/icon-zanwu.png';
        if ($v['wlinks']) {
            $list[$k]['url'] = $v['wlinks'];
        } else {
            $list[$k]['url'] = $this->createMobileUrl('business', array('op' => 'coupon', 'operation' => 'detail', 'dpid' => intval($_GPC['dpid']), 'gid' => $v['id']));
        }
    }

    $data = array();
    $data['list'] = $list;
    $data['hstatus'] = set('p96') ? 1 : 0;
    util::send_result($data);

}
/**
 * 团购商品的详情
 */
if ($op == 'detail') {
    $id = intval($_GPC['gid']);
    if (empty($id)) {
        util::send_error(-1, '参数错误');
    }
    $item = pdo_get('xcommunity_goods', array('id' => $id), array());
    //幻灯展示多图
    $piclist = array();
    if ($item['thumb_url']) {
        $thumbs = explode(',', $item['thumb_url']);
        if ($thumbs) {
            foreach ($thumbs as $key => $value) {
//                $piclist[] = tomedia($value);
                $piclist[] = array(
                    'src' => tomedia($value),
                    'msrc' => tomedia($value),
                );
            }
        }
    }
    //详情展示多图
    $images = array();
    if ($item['thumbs']) {
        $imgs = explode(',', $item['thumbs']);
        if ($imgs) {
            foreach ($imgs as $key => $val) {
//                $images[] = tomedia($val);
                $images[] = array(
                    'src' => tomedia($val),
                    'msrc' => tomedia($val),
                );
            }
        }
    }
    $thumb = $item['thumb'] ? tomedia($item['thumb']) : tomedia($piclist[0][src]);
    $item['pic'] = $thumb;
    $item['hstatus'] = set('p96') ? 1 : 0;
    $item['piclist'] = !empty($piclist) ? $piclist : array(array('src' => $item['pic'], 'msrc' => $item['pic']));
    $item['thumbs'] = $images;
    // 商品的售卖时间、使用时间开关
    $commons = array();
    if ($item['common']) {
        $commons = unserialize($item['common']);
    }
    $item['saleStatus'] = $commons['saleStatus'] ? $commons['saleStatus'] : 2;
    $item['useStatus'] = $commons['useStatus'] ? $commons['useStatus'] : 2;
    if ($item['saleStatus'] == 2) {
        if ($item['startdate'] < TIMESTAMP && $item['enddate'] > TIMESTAMP) {
            $item['salestatus'] = 1;
        } elseif ($item['enddate'] < TIMESTAMP) {
            $item['salestatus'] = 3;
        } else {
            $item['salestatus'] = 2;
        }
    } else {
        $item['salestatus'] = 1;
    }
    $item['startdate'] = date('Y-m-d', $item['startdate']);
    $item['endtime'] = date('Y-m-d', $item['endtime']);
    $item['enddate'] = date('Y-m-d', $item['enddate']);
    $item['starttime'] = date('Y-m-d', $item['starttime']);
    $item['menus'] = unserialize($item['combo']);
    $item['limitnum'] = $item['limitnum'] ? $item['limitnum'] : $item['total'];
    /**
     * 计算折扣
     */
    if($item['marketprice'] !='0.00' && $item['productprice'] !='0.00'){
        $item['dis'] = sprintf("%.2f", ($item['marketprice'] / $item['productprice']) * 10);
    }else{
        $item['dis'] = '0.00';
    }

    /**
     * 分割规则
     */
    if ($item['rules']) {
        $item['rules'] = explode('|', $item['rules']);
    }

    //商家配置
    $dp = pdo_get('xcommunity_dp', array('id' => $item['dpid']), array());
    $setting = unserialize($dp['setting']);
    $item['setting'] = $setting;
    /**
     * 计算抵扣积分
     */
    $item['credit_swith'] = $setting['credit_swith'] ? 1 : 0;
    if ($item['credit_swith']) {
        $credit = $setting['credit2'] ? $setting['credit2'] : '0.00';
    } else {
        $credit = '0.00';
    }
    $item['rate'] = $setting['credit_rat'];
    $item['credit'] = $credit < $_W['member']['credit1'] ? $credit : $_W['member']['credit1'];
    /**
     * 随机减免
     */
    //判断是否开启随机减免
    $item['rand_swith'] = $setting['rand_swith'] ? 1 : 0;
    if ($item['rand_swith'] && $setting['minprice'] && $setting['maxprice']) {
        $randprice = rand($setting['minprice'], $setting['maxprice']);
    } else {
        $randprice = 0;
    }
    $item['randprice'] = $randprice;
    $item['rankUrl'] = $this->createMobileUrl('business', array('op' => 'my')) . "#/rankList/" . $item['id'];
    // 库存转换为int型
    $item['total'] = intval($item['total']);
    if ($item) {
        util::send_result($item);
    }

}
/**
 * 团购商品的提交
 */
if ($op == 'confirm') {
    $gid = intval($_GPC['gid']);//商品id
    if (empty($gid)) {
        util::send_error(-1, '缺少参数');
        exit();
    }
    $address = pdo_get('xcommunity_member_address', array('uid' => $_W['member']['uid'], 'enable' => 1), array('id'));
    $item = pdo_get('xcommunity_goods', array('id' => $gid), array('marketprice', 'dpid', 'limitnum', 'total'));
    $goods = pdo_fetch("select sum(t1.total) as total from" . tablename('xcommunity_order_goods') . "t1 left join" . tablename('xcommunity_order') . "t2 on t1.orderid=t2.id where t2.uid=:uid and t1.goodsid=:goodsid and t2.status=1", array(':uid' => $_W['member']['uid'], ':goodsid' => $gid));
    $total = intval($_GPC['total']) + $goods['total'];
    $dp = pdo_get('xcommunity_dp', array('id' => $item['dpid']), array());
    $limitum = $item['limitnum'] ? $item['limitnum'] : $item['total'];
    if ($total > $limitum) {
        util::send_result(array('status' => 2, 'content' => '商品每人限购' . $item['limitnum'] . '份'));
        exit();
    }
    if ($item['total'] <= 0 || intval($_GPC['total']) > $item['total']) {
        $t = $item['total'] > 0 ? $item['total'] : 0;
        util::send_result(array('status' => 2, 'content' => '库存不足,最多可购买' . $t . '份'));
        exit();
    }

    /**
     * 处理传过来的金额、积分、减免
     */
    //支付金额
    $totalprice = $_GPC['totalprice'];//应付款
    $price = $totalprice;//实际付款
    $status = 0;//订单状态
    //是否开启积分抵扣
    $creditChecked = intval($_GPC['creditChecked']);//判断是否启用
    if ($creditChecked) {
        $credit = trim($_GPC['credit']);//抵扣积分默认0
        $creditTotal = trim($_GPC['creditTotal']);//积分抵扣费用
        $totalprice = $totalprice + $creditTotal;
    }
    //是否开启优惠减免
    $randChecked = intval($_GPC['randChecked']);//判断是否启用
    if ($randChecked) {
        $randprice = trim($_GPC['randprice']);//积分抵扣费用
        $totalprice = $totalprice + $randprice;
    }
    $status = $price == 0 ? 1 : 0;
    $data = array(
        'uniacid' => $_W['uniacid'],
        'uid' => $_W['member']['uid'],
        'ordersn' => 'LN' . date('YmdHi') . random(10, 1),
        'price' => $price,// $item['marketprice'] * intval($_GPC['total']),
        'status' => $status,
        'createtime' => TIMESTAMP,
        'type' => 'business',
        'regionid' => $_SESSION['community']['regionid'],
        'addressid' => $address['id'],
        'dpid' => $item['dpid'],
        'offsetprice' => $creditTotal,
        'randprice' => $randprice,
        'credit' => floatval($credit),
        'total' => floatval($totalprice),
    );
//    print_r($data);exit();
    $d = array(
        'uniacid' => $_W['uniacid'],
        'goodsid' => $gid,
        'total' => intval($_GPC['total']), //团购数量
        'price' => $item['marketprice'],
        'createtime' => TIMESTAMP,
    );
//    print_r($_GPC);exit();
//    print_r($data);exit();
    if (pdo_insert('xcommunity_order', $data)) {
        $orderid = pdo_insertid();
        $d['orderid'] = $orderid;
        pdo_update('xcommunity_goods', array('total' => $item['total'] - intval($_GPC['total'])), array('id' => $gid));
        if (pdo_insert('xcommunity_order_goods', $d)) {
            if (empty($price)) {
                //全额积分抵付
                mc_credit_update($data['uid'], 'credit1', -$data['credit'], array($data['uid'], '购买商家商品抵扣积分'));
                $creditdata1 = array(
                    'uid' => $data['uid'],
                    'uniacid' => $_W['uniacid'],
                    'realname' => $_W['member']['realname'],
                    'mobile' => $_W['member']['mobile'],
                    'content' => "订单号：" . $data['ordersn'] . ",减少积分",
                    'credit' => $data['credit'],
                    'creditstatus' => 2,
                    'createtime' => TIMESTAMP,
                    'type' => 2,
                    'typeid' => $data['dpid'],
                    'category' => 1,
                    'usename' => '系统'
                );
                pdo_insert('xcommunity_credit', $creditdata1);

                $content = '您本次共消费' . $totalprice . '元,' . '实际支付0元,消费' . $credit . '积分。';
                $ret = util::sendnotice($content);
                $print = pdo_get('xcommunity_business_print', array('uniacid' => $_W['uniacid'], 'dpid' => $data['dpid']));
                if ($print['type'] == 1) {
                    $createtime = date('Y-m-d H:i:s', $_W['timestamp']);
                    $yl = "^N1^F1\n";
                    $yl .= "^B2 商铺扫码付款\n\n";
                    $yl .= "商铺：" . $item['sjname'] . "\n";
                    $yl .= "内容：" . $content . "\n";
                    xq_print::yl($print['deviceNo'], $print['api_key'], $yl);
                }
                for ($i = 0; $i < $d['total']; $i++) {
                    $dat = array(
                        'orderid' => $orderid,
                        'couponsn' => date('md') . random(5, 1),
                        'status' => 1,
                        'createtime' => TIMESTAMP,
                    );
                    pdo_insert('xcommunity_coupon_order', $dat);
                }
                $result = array('url' => $this->createMobileUrl('home'), 'status' => 1);
                util::send_result($result);
                exit();
            } else {
                $url = $this->createMobileUrl('paycenter', array('type' => 3, 'orderid' => $orderid));
                $data = array();
                $data['status'] = 1;
                $data['url'] = $url;
                util::send_result($data);
                exit();
            }
        }
    }
}
/**
 * 团购商品的订单列表
 */
if ($op == 'order') {
    $pindex = max(1, intval($_GPC['page']));
    $psize = max(20, intval($_GPC['psize']));
    $status = intval($_GPC['status']) ? intval($_GPC['status']) : 3;
    $condition = 't4.uid=:uid';
    if ($status == 3 || empty($status)) {
        $condition .= " and t4.status=0 and t4.type='business'";
        $sql = "select t4.id,t3.marketprice,t3.title,t3.thumb,t4.status as t_status,t4.ordersn,t4.createtime,t4.price,t3.starttime,t3.endtime,t3.thumb_url,t4.rank_status from" . tablename('xcommunity_order') . "t4 left join" . tablename('xcommunity_order_goods') . "t2 on t2.orderid = t4.id left join" . tablename('xcommunity_goods') . "t3 on t2.goodsid = t3.id where $condition order by t4.createtime desc LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
    }
    if ($status == 1 || $status == 2) {
        $condition .= " and t1.status=:status";
        $params[':status'] = $status;
        $sql = "select t1.couponsn,t3.title,t3.marketprice as price,t3.thumb,t1.status,t3.starttime,t3.endtime,t4.status as t_status,t4.ordersn,t4.createtime,t3.thumb,t3.thumb_url,t4.id,t4.rank_status from" . tablename('xcommunity_coupon_order') . "t1 left join" . tablename('xcommunity_order_goods') . "t2 on t1.orderid=t2.orderid left join" . tablename('xcommunity_goods') . "t3 on t2.goodsid=t3.id left join" . tablename('xcommunity_order') . "t4 on t4.id = t1.orderid where $condition order by t1.createtime desc LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
    }
    $params[':uid'] = $_W['member']['uid'];
    $list = pdo_fetchall($sql, $params);
    foreach ($list as $k => $v) {
        if ($v['endtime'] >= TIMESTAMP) {
            $list[$k]['term'] = 1;
        } else {
            $list[$k]['term'] = 0;
        }
        $list[$k]['endtime'] = date('Y-m-d', $v['endtime']);
        $list[$k]['createtime'] = date('Y-m-d', $v['createtime']);
        $imgs = explode(',', $v['thumb_url']);
        $thumb = $v['thumb'] ? tomedia($v['thumb']) : tomedia($imgs[0]);
        $list[$k]['thumb'] = $thumb ? $thumb : MODULE_URL . 'template/mobile/default2/static/images/icon-zanwu.png';
        $list[$k]['totalprice'] = $v['price'];
        $list[$k]['url'] = $this->createMobileUrl('paycenter', array('type' => 3, 'orderid' => $v['id']));
        if ($v['couponsn']) {
            $images = array();
            $temp = $v['couponsn'] . ".png";
            $tmpdir = "../addons/" . $this->module['name'] . "/data/qrcode/coupon/" . $_W['uniacid'] . "/";
            if (!is_dir($tmpdir)) {
                load()->func('file');
                mkdirs($tmpdir);
            }
            //生成二维码
            require_once IA_ROOT . '/framework/library/qrcode/phpqrcode.php';
            $errorCorrectionLevel = 'L';//容错级别
            $matrixPointSize = 10;//生成图片大小
            //生成二维码图片
            $imgUrl = $tmpdir . $temp;
            QRcode::png($v['couponsn'], $imgUrl, $errorCorrectionLevel, $matrixPointSize, 2);
            $images[] = array(
                'src' => tomedia($imgUrl),
                'msrc' => tomedia($imgUrl),
            );
            $list[$k]['qrcode'] = $images;
        }
        $list[$k]['rankStatus'] = $v['rank_status'] ? $v['rank_status'] : 0;

    }
    $data = array();
    $data['list'] = $list;
    $data['item']['hstatus'] = set('p96') ? 1 : 0;
    $data['item']['status'] = $status;
    util::send_result($data);
}
/**
 * 团购商品的订单删除
 */
if ($op == 'delete') {
    $orderid = intval($_GPC['orderid']);
    if ($orderid) {
        $item = pdo_get('xcommunity_order', array('id' => $orderid, 'uid' => $_W['member']['uid']), array());
        if ($item) {
            if (pdo_delete('xcommunity_order', array('id' => $orderid, 'uid' => $_W['member']['uid']))) {
                util::permlog('团购订单-删除', '订单号:' . $item['ordersn']);
                $data = array();
                $data['content'] = '删除成功';
                util::send_result($data);
            }
        }
    }
}
/**
 * 商家已消费订单的评价
 */
if ($op == 'rank') {
    $orderid = intval($_GPC['orderid']);
    $order = pdo_get('xcommunity_order', array('id' => $orderid), array('dpid', 'rank_status'));
    if ($order['rank_status'] == 1) {
        util::send_error(-1, '您已经评价过了');
    }
    $orderGoods = pdo_get('xcommunity_order_goods', array('orderid' => $orderid), array());
    $data = array(
        'uniacid'    => $_W['uniacid'],
        'type'       => 1,
        'rankid'     => $orderGoods['goodsid'],
        'uid'        => $_W['member']['uid'],
        'content'    => $_GPC['content'],
        'createtime' => TIMESTAMP,
        'rank'       => $_GPC['rank'],
//        'status'     => intval($_GPC['status']),
        'status'     => intval($_GPC['status'][0]),
    );
    $pics = xtrim($_GPC['pics']);
    if ($pics) {
        $data['images'] = $pics;
    }
    if (pdo_insert('xcommunity_rank', $data)) {
        pdo_update('xcommunity_order', array('rank_status' => 1), array('id' => $orderid));
        $data = array();
        $data['content'] = '评价成功';
        util::send_result($data);
    }
}
/**
 * 团购商品的评价
 */
if ($op == 'rankList') {
    $pindex = max(1, intval($_GPC['page']));
    $psize = 10;
    $condition = array();
    $condition['uniacid'] = $_W['uniacid'];
    $condition['type'] = 1;
    $goodsId = intval($_GPC['id']);
    $condition['rankid'] = $goodsId;
    $ranks = pdo_getslice('xcommunity_rank', $condition, array($pindex, $psize), $total, '', '', array('createtime desc'));
    $ranks_uids = _array_column($ranks, 'uid');
    $members = pdo_getall('mc_members', array('uid' => $ranks_uids), array('avatar', 'nickname', 'realname', 'uid'), 'uid');
    $data = array();
    foreach ($ranks as $k =>$v) {
        $pics = array();
        if ($v['images']) {
            $images = explode(',', $v['images']);
            foreach ($images as $key => $value) {
                $pics[] = array(
                    'msrc'  => tomedia($value),
                    'src' => tomedia($value),
                );
            }
        }
        $data[] = array(
            'createtime' => date('Y-m-d H:i', $v['createtime']),
            'images' => $pics,
            'avatar'     => $members[$v['uid']]['avatar'],
            'nickname'   => $members[$v['uid']]['nickname'],
            'realname'   => $members[$v['uid']]['realname'],
            'content'    => $v['content'],
            'rank'       => intval($v['rank']),
            'status'     => intval($v['status'])
        );
    }
    util::send_result($data);
}