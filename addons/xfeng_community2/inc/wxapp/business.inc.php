<?php
/**
 * Created by njlanniu.
 * User: njlanniu
 * Time: 2018/3/13 下午3:18
 */
global $_GPC, $_W;
$ops = array('list', 'detail', 'add', 'rank', 'display', 'applist', 'goods', 'order', 'dp', 'qr', 'pay', 'rechargelog', 'cashlog', 'record', 'del', 'setting', 'orderdetail', 'ranklist', 'pay', 'getCategories');
$op = trim($_GPC['op']);
if (!in_array($op, $ops)) {
    util::send_error(-1, '参数错误');
}

if ($op == 'list') {
    $lng = $_GPC['lng'];
    $lat = $_GPC['lat'];
    $condition = "uniacid=:uniacid and enable=1 and status=1";
    $params[':uniacid'] = $_W['uniacid'];
    $keyword = $_GPC['keyword'];
    if ($keyword) {
        $cate = explode('-', $keyword);
        if ($cate[0] == 'all') {
            if ($cate[1] != 'all') {
                $condition .= " and parent=:parent";
                $params[':parent'] = $cate[1];
            }
        }
        if ($cate[0] == 'child') {
            $condition .= " and child=:child";
            $params[':child'] = $cate[1];
        }
    }
    if (set('p10')) {
        if ($lng && $lat) {
            $range = set('p11') ? set('p11') : 5;
            $point = util::squarePoint($lng, $lat, $range);
            $condition .= " AND lat<>0 AND lat >= '{$point['right-bottom']['lat']}' AND lat <= '{$point['left-top']['lat']}' AND lng >= '{$point['left-top']['lng']}' AND lng <= '{$point['right-bottom']['lng']}'";
        }
    }
    $pindex = max(1, intval($_GPC['page']));
    $psize = max(50, intval($_GPC['psize']));
    $sql = "SELECT * FROM" . tablename('xcommunity_dp') . "WHERE $condition order by id desc LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
    $result = pdo_fetchall($sql, $params);
    $count = count($result);
    $list = array();
    if (!empty($result)) {
        $min = -1;
        foreach ($result as &$row) {
            $row['distance'] = util::GetDistance($lat, $lng, $row['lat'], $row['lng']);
            if ($min < 0 || $row['distance'] < $min) {
                $min = $row['distance'];
            }
            $total = pdo_fetchcolumn("select count(*) from" . tablename('xcommunity_rank') . "where rankid=:id", array(':id' => $row['id']));
            $row['total'] = $total;
            $ranks = pdo_getall('xcommunity_rank', array('rankid' => $row['id']), array('rank'));
            $ranktotal = '';
            foreach ($ranks as $k => $v) {
                $ranktotal += $v['rank'];
            }
            $t = 0;
            if ($total) {
                $t = ($ranktotal / ($total * 5)) * 100;
            }

            $arr = array();
            if ($t < 20) {
                $arr = array('full', 'none', 'none', 'none', 'none');
            }
            elseif ($t >= 20 && $t < 40) {
                $arr = array('full', 'half', 'none', 'none', 'none');
            }
            elseif ($t == 40) {
                $arr = array('full', 'full', 'none', 'none', 'none');
            }
            elseif ($t > 40 && $t < 60) {
                $arr = array('full', 'full', 'half', 'none', 'none');
            }
            elseif ($t == 60) {
                $arr = array('full', 'full', 'full', 'none', 'none');
            }
            elseif ($t > 60 && $t < 80) {
                $arr = array('full', 'full', 'full', 'half', 'none');
            }
            elseif ($t == 80) {
                $arr = array('full', 'full', 'full', 'full', 'none');
            }
            elseif ($t > 80 && $t < 100) {
                $arr = array('full', 'full', 'full', 'full', 'half');
            }
            elseif ($t == 100 || $t == 0) {
                $arr = array('full', 'full', 'full', 'full', 'full');
            }
            $row['rank'] = $arr;
            $row['ranks'] = $t;
            $starttime = time() - 86400 * 30;
            $ordertotal = pdo_fetchcolumn("select count(*) from" . tablename('xcommunity_order') . "where dpid=:id and createtime > :starttime", array(':id' => $row['id'], ':starttime' => $starttime));
            $row['ordertotal'] = $ordertotal;
        }
        unset($row);
        $temp = array();
        $distance = intval($_GPC['distance']);
        if ($distance) {
            $temp['distance'] = $distance;
        }
        for ($i = 0; $i < $count; $i++) {
            foreach ($result as $j => $row) {
                if (empty($temp['distance']) || $row['distance'] < $temp['distance']) {
                    $temp = $row;
                    $h = $j;
                }
            }
            if (!empty($temp)) {
                $juli = floor($temp['distance']) / 1000 ? floor($temp['distance']) / 1000 : '0.1';
//                $url = $this->createMobileUrl('business', array('op' => 'detail', 'id' => $temp['id']));
                $url = $_W['siteroot'] . "app/index.php?i={$_W['uniacid']}&c=entry&do=business&op=detail&m=" . $this->module['name'] . "&id=" . $temp['id'];
                $list[] = array(
                    'sjname'      => $temp['sjname'],
                    'juli'        => sprintf('%.1f', (float)$juli),
                    'lng'         => $temp['lng'],
                    'lat'         => $temp['lat'],
                    'address'     => $temp['address'],
                    'mobile'      => $temp['mobile'],
                    'picurl'      => tomedia($temp['picurl']),
                    'id'          => $temp['id'],
                    'price'       => intval($temp['price']),
                    'area'        => $temp['area'],
                    'businessurl' => $temp['businessurl'] ? $temp['businessurl'] : $url,
                    'total'       => $temp['total'],
                    'instruction' => $temp['instruction'],
                    'cid'         => $temp['cid'],
                    'ordertotal'  => $temp['ordertotal'] ? $temp['ordertotal'] : 0,
                    'rank'        => $temp['rank'],
                    'ranks'       => $temp['ranks'],
                    'createtime'  => $temp['createtime']
                );
                unset($result[$h]);
                $temp = array();
            }
        }
//        sortArrByField($list, 'juli');
        if ($keyword) {
            $cate = explode('-', $keyword);
            // 智能排序
            if ($cate[0] == 'compositor') {
//                sortArrByField($list, 'juli');
            }
            if ($cate[0] == 'juli') {
                sortArrByField($list, 'juli');
            }
            if ($cate[0] == 'rank') {
                sortArrByField($list, 'ranks', true);
            }
            if ($cate[0] == 'priceasc') {
                sortArrByField($list, 'price');
            }
            if ($cate[0] == 'pricedesc') {
                sortArrByField($list, 'price', true);
            }
            // 销量排序
            if ($cate[0] == 'order') {
//                sortArrByField($list, 'juli');
            }
            if ($cate[0] == 'ordermax') {
                sortArrByField($list, 'ordertotal', true);
            }
            if ($cate[0] == 'ordermin') {
                sortArrByField($list, 'ordertotal');
            }
        } else {
            sortArrByField($list, 'juli');
        }
    }
    $data = array();
    $data['list'] = $list;
    $data['hstatus'] = set('p96') ? 1 : 0;
    util::send_result($data);
}
if ($op == 'detail') {
    $id = intval($_GPC['id']);
    if ($id) {
        //$item = pdo_fetch("SELECT * FROM" . tablename('xcommunity_dp') . "WHERE id=:id", array(':id' => $id));
        $sql = "select t1.*,t2.name as category from" . tablename('xcommunity_dp') . "t1 left join" . tablename('xcommunity_category') . "t2 on t1.parent=t2.id where t1.id=:id and t1.status=1";
        $item = pdo_fetch($sql, array(':id' => $id));
        $thumb = tomedia($item['picurl']);
        if ($item['id']) {
            $time = TIMESTAMP;
            $list = pdo_fetchall("SELECT * FROM" . tablename('xcommunity_goods') . "WHERE uniacid=:uniacid AND type = 2 AND dpid = :dpid and status =1 and endtime > :endtime and isshow=0", array(':uniacid' => $_W['uniacid'], ':dpid' => $item['id'], ':endtime' => $time));
            foreach ($list as $k => $v) {
                $imgs = explode(',', $v['thumb_url']);
                $thumb = $v['thumb'] ? tomedia($v['thumb']) : tomedia($imgs[0]);
                $list[$k]['src'] = $thumb ? $thumb : MODULE_URL . 'template/mobile/default2/static/images/icon-zanwu.png';
                $list[$k]['title'] = $v['title'];
                $list[$k]['desc'] = $v['productprice'];
                if ($v['wlinks']) {
                    $list[$k]['url'] = $v['wlinks'];
                }
                else {
                    $list[$k]['url'] = $this->createMobileUrl('business', array('op' => 'list')) . '#/couponDetail/' . $v['id'];
                }
            }
            $count = pdo_fetchcolumn("select count(*) from" . tablename('xcommunity_rank') . "where rankid=:id", array(':id' => $item['id']));
//            $row['total'] = $total;
            $ranks = pdo_getall('xcommunity_rank', array('rankid' => $item['id']), array('rank'));
            $ranktotal = '';
            foreach ($ranks as $k => $v) {
                $ranktotal += $v['rank'];
            }
            $t = 5;
            if ($count) {
                //$t = ($ranktotal / ($count * 5)) * 100;
                //计算平均分
                $t = $ranktotal / $count;
            }
//            $arr = array();
//            if ($t < 20) {
//                $arr = array('full', 'none', 'none', 'none', 'none');
//            } elseif ($t >= 20 && $t < 40) {
//                $arr = array('full', 'half', 'none', 'none', 'none');
//            } elseif ($t == 40) {
//                $arr = array('full', 'full', 'none', 'none', 'none');
//            } elseif ($t > 40 && $t < 60) {
//                $arr = array('full', 'full', 'half', 'none', 'none');
//            } elseif ($t == 60) {
//                $arr = array('full', 'full', 'full', 'none', 'none');
//            } elseif ($t > 60 && $t < 80) {
//                $arr = array('full', 'full', 'full', 'half', 'none');
//            } elseif ($t == 80) {
//                $arr = array('full', 'full', 'full', 'full', 'none');
//            } elseif ($t > 80 && $t < 100) {
//                $arr = array('full', 'full', 'full', 'full', 'half');
//            } elseif ($t == 100 || $t == 0) {
//                $arr = array('full', 'full', 'full', 'full', 'full');
//            }
        }
    }
    $lng = $_GPC['lng'];
    $lat = $_GPC['lat'];
    $item['distance'] = util::GetDistance($lat, $lng, $item['lat'], $item['lng']);
    $juli = floor($item['distance']) / 1000;
    $data = array();
    $setting = array();
    $data = array(
        'sjname'       => $item['sjname'],
        'price'        => $item['price'],
        'area'         => $item['area'],
        'count'        => $count ? $count : 0,
        'address'      => $item['address'],
        'instruction'  => $item['instruction'],
        'goods'        => $list,
        'id'           => $item['id'],
        'mobile'       => 'tel:' . $item['mobile'],
        'images'       => array(
            array(
                'url'   => '',
                'img'   => tomedia($item['picurl']),
                'title' => ''
            )
        ),
        'distance'     => $juli,
        'hstatus'      => set('p96') ? 1 : 0,
        'title'        => $item['sjname'],
        'picurl'       => tomedia($item['picurl']),
        'rank'         => $t,
        'lat'          => $item['lat'],
        'lng'          => $item['lng'],
        'category'     => $item['category'],
        'tag'          => explode(',', $item['tag']),
        'businesstime' => $item['businesstime'],
        'serviceurl'   => $item['serviceurl'],
        'cardurl'      => $item['cardurl'],
        'activityurl'  => $item['activityurl'],
    );
    //商家配置
    $setting = unserialize($item['setting']);
    $data['setting'] = $setting;
    // 是否开启优惠买单
    $data['paystatus'] = $setting['paystatus'] ? 1 : 0;
    /**
     * 计算抵扣积分
     */
//    $rate = set('p95'); //抵扣比例
    $data['credit_swith'] = $setting['credit_swith'] ? 1 : 0;
    if ($data['credit_swith']) {
        $credit = $setting['credit2'] ? $setting['credit2'] : 0.00;
    }
    else {
        $credit = 0.00;
    }
    $data['rate'] = $setting['credit_rat'];
    $data['credit'] = $credit < $_W['member']['credit1'] ? $credit : $_W['member']['credit1'];
    /**
     * 随机减免
     */
    //判断是否开启随机减免
    $data['rand_swith'] = $setting['rand_swith'] ? 1 : 0;
    if ($data['rand_swith'] && $setting['minprice'] && $setting['maxprice']) {
        $randprice = rand($setting['minprice'], $setting['maxprice']);
    }
    else {
        $randprice = 0.00;
    }
    $data['randprice'] = $randprice;
    util::send_result($data);
}
if ($op == 'rank') {
    $data = array(
        'uniacid'    => $_W['uniacid'],
        'type'       => 1,
        'rankid'     => intval($_GPC['rankid']),
        'uid'        => $_W['member']['uid'],
        'content'    => $_GPC['content'],
        'createtime' => TIMESTAMP,
        'rank'       => $_GPC['rank'],
        'status'     => intval($_GPC['status']),
    );
    $pics = xtrim($_GPC['pics']);
//    $pic = '';
    if ($pics) {
//        $pics = explode(',', $pics);
//        if (!empty($pics)) {
//            foreach ($pics as $k => $v) {
//                $pic .= util::get_media($v) . ',';
//            }
//        }
//        $pic = ltrim(rtrim($pic, ','), ',');
        $data['images'] = $pics;
    }
    if (pdo_insert('xcommunity_rank', $data)) {
        $data = array();
        $data['content'] = '评价成功';
        util::send_result($data);
    }
}
if ($op == 'ranklist') {
    $pindex = max(1, intval($_GPC['page']));
    $psize = max(20, intval($_GPC['psize']));
    $dpid = intval($_GPC['dpid']);
    $list = pdo_getslice('xcommunity_rank', array('uniacid' => $_W['uniacid'], 'rankid' => $dpid, 'type' => 1), array($pindex, $psize), $total, '', '', array('createtime desc'));
    $list_uids = _array_column($list, 'uid');
    $members = pdo_getall('mc_members', array('uid' => $list_uids), array('avatar', 'uid', 'nickname', 'realname'));
    $members_ids = _array_column($members, NULL, 'uid');
    $data = array();
    $pics = array();
    foreach ($list as $k => $v) {
        $images = explode(',', $v['images']);
        foreach ($images as $key => $value) {
            $pics[] = array(
                'src'  => tomedia($value),
                'msrc' => tomedia($value),
            );
        }
        $data[] = array(
            'createtime' => date('Y-m-d', $v['createtime']),
            'images'     => $pics,
            'avatar'     => $members_ids[$v['uid']]['avatar'],
            'nickname'   => $members_ids[$v['uid']]['nickname'],
            'realname'   => $members_ids[$v['uid']]['realname'],
            'content'    => $v['content'],
            'rank'       => $v['rank'],
            'status'     => $v['status']
        );
    }
    util::send_result($data);
}
if ($op == 'dp') {
    $id = intval($_GPC['id']);
    $p = in_array(trim($_GPC['p']), array('list', 'detail', 'del')) ? trim($_GPC['p']) : 'detail';
    if ($p == 'detail') {
        if (empty($id)) {
            util::send_error(-1, '店铺不存在');
            exit();
        }
        $data = array();
        $item = pdo_fetch("SELECT * FROM" . tablename('xcommunity_dp') . "WHERE id=:id", array(':id' => $id));
        if (empty($item)) {
            util::send_error(-1, '店铺不存在');
            exit();
        }
        $item['picurl'] = tomedia($item['picurl']);
        $item['pics'] = array(tomedia($item['picurl']));
        $item['credit'] = set('p95') * $_W['member']['credit1'];
        $shopdesc = strip_tags($item['shopdesc']);
        $item['shopdesc'] = htmlspecialchars(strip_tags($item['shopdesc']));
        $childs = pdo_getall('xcommunity_category', array('parentid' => $item['parent']), array('id', 'name'));
        foreach ($childs as $key => $val) {
            $childs[$key]['key'] = $val['id'];
            $childs[$key]['value'] = $val['name'];
        }
        $data['hstatus'] = set('p96') ? 1 : 0;
        $data['item'] = $item;
        $data['url'] = $this->createMobileUrl('xqsys', array('op' => 'business', 'p' => 'add', 'id' => $item['id']));
        util::send_result($data);
    }
    elseif ($p == 'list') {
        $condition = "uniacid=:uniacid";
        $params[':uniacid'] = $_SESSION['appuniacid'];
        if ($_SESSION['apptype'] && $_SESSION['apptype'] != 1) {
            $condition .= " and uid=:uid";
            $params[':uid'] = $_SESSION['appuid'];
        }
        $sql = "SELECT * FROM" . tablename('xcommunity_dp') . "WHERE $condition order by id desc";
        $list = pdo_fetchall($sql, $params);
        foreach ($list as $k => $v) {
            $list[$k]['key'] = $v['id'];
            $list[$k]['value'] = $v['sjname'];
        }
        util::send_result($list);
    }
    elseif ($p == 'del') {
        $id = intval($_GPC['id']);
        if ($id) {
            $item = pdo_get('xcommunity_dp', array('id' => $id), array('id'));
            if ($item) {
//                pdo_delete('xcommunity_goods', array('dpid' => $id));
                pdo_update('xcommunity_goods', array('isshow' => 1), array('dpid' => $id));
                if (pdo_delete('xcommunity_dp', array('id' => $id))) {
                    util::send_result();
                }
            }
        }
    }
}
/**
 * 优惠买单
 */
if ($op == 'pay') {
    $dpid = intval($_GPC['dpid']);
    if (empty($dpid)) {
        util::send_error(-1, '非法操作');
    }
    $item = pdo_get('xcommunity_dp', array('id' => $dpid));
    if (empty($item)) {
        util::send_error(-1, '非法操作');
    }
    //支付金额
    $total = $_GPC['total'];//应付款
    $price = $total;//实际付款

    $status = 0;//订单状态
    //是否开启积分抵扣

    $creditChecked = intval($_GPC['creditChecked']);//判断是否启用
    if ($creditChecked) {
        $credit = trim($_GPC['credit']);//抵扣积分默认0
        $creditTotal = trim($_GPC['creditTotal']);//积分抵扣费用
        $status = $price == $creditTotal ? 1 : 0;
        $total = $total + $creditTotal;
    }

    //是否开启优惠减免

    $randChecked = intval($_GPC['randChecked']);//判断是否启用
    if ($randChecked) {
        $randprice = trim($_GPC['randprice']);//积分抵扣费用
        $status = $price == $randprice ? 1 : 0;
        $total = $total + $randprice;
    }

    $data = array(
        'uniacid'     => $_W['uniacid'],
        'ordersn'     => 'LN' . date('YmdHi') . random(10, 1),
        'price'       => floatval($price),
        'total'       => floatval($total),
        'uid'         => $_W['member']['uid'],
        'createtime'  => TIMESTAMP,
        'credit'      => floatval($credit),
        'offsetprice' => $creditTotal,
        'dpid'        => intval($_GPC['dpid']),
        'status'      => $status,
        'randprice'   => $randprice,
        'type'        => 'xxbusiness',
        'regionid'    => $_SESSION['community']['regionid'],
        'paytype'     => empty($price) ? 5 : ''
    );
    if (pdo_insert('xcommunity_order', $data)) {
        $orderid = pdo_insertid();
        if (empty($price)) {
            //全额积分抵付
            if (pdo_update('mc_members', array('credit1 -=' => $credit), array('uid' => $_W['member']['uid']))) {
                $creditdata1 = array(
                    'uid'          => $data['uid'],
                    'uniacid'      => $_W['uniacid'],
                    'realname'     => $_W['member']['realname'],
                    'mobile'       => $_W['member']['mobile'],
                    'content'      => "扫码支付订单号：" . $data['ordersn'] . ",减少积分",
                    'credit'       => $creditTotal,
                    'creditstatus' => 2,
                    'createtime'   => TIMESTAMP,
                    'type'         => 2,
                    'typeid'       => $data['dpid'],
                    'category'     => 1,
                    'usename'      => '系统'
                );
                pdo_insert('xcommunity_credit', $creditdata1);
            }
            $content = '您本次共消费' . $price . '元,' . '实际支付0元,消费' . $credit . '积分。';
            $ret = util::sendnotice($content);
            $print = pdo_get('xcommunity_business_print', array('uniacid' => $_W['uniacid'], 'dpid' => $data['dpid']));
            if ($print['type'] == 1) {
                $createtime = date('Y-m-d H:i:s', $_W['timestamp']);
                $yl = "^N1^F1\n";
                $yl .= "^B2 商家小票\n\n";
                $yl .= "商户名称:" . $item['sjname'] . "\n";
                $yl .= "商品名称:线下扫码\n";
                $yl .= "平台单号:" . $data['ordersn'] . "\n";
                $yl .= "订单信息:您本次共消费" . $data['total'] . "元" . "\n";
                $yl .= "付款信息:实际在线支付" . $data['price'] . "元,积分抵扣" . $data['offsetprice'] . "元" . "\n";
                $yl .= "交易方式:积分抵扣" . "\n";
                $yl .= "付款时间:" . $createtime . "\n";
                xq_print::yl($print['deviceNo'], $print['api_key'], $yl);
            }
            $result = array('url' => $this->createMobileUrl('home'), 'status' => 1);
            util::send_result($result);
            exit();
        }
        else {
            //需付款
            $result = array('url' => $this->createMobileUrl('paycenter', array('type' => 3, 't' => 1, 'orderid' => $orderid)));
            util::send_result($result);
        }
    }


}

/**
 * 获取周边商家全部分类
 */
if ($op == 'getCategories') {

}



