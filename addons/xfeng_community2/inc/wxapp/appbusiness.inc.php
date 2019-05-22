<?php
/**
 * Created by njlanniu.com.
 * User: 蓝牛科技
 * Time: 2018/4/17 下午2:19
 */
global $_GPC, $_W;
$ops = array('list', 'detail', 'add', 'display', 'goods', 'order', 'dp', 'qr', 'pay', 'rechargelog', 'cashlog', 'record', 'del', 'setting', 'orderdetail', 'hx', 'goodsCombo');
$op = trim($_GPC['op']);
if (!in_array($op, $ops)) {
    util::send_error(-1, '参数错误');
}
$user = util::xquser($_SESSION['appuid']);
$_SESSION['appstore'] = $user['store'];
/**
 * 商家的列表
 */
if ($op == 'list') {
    $pindex = max(1, intval($_GPC['page']));
    $psize = max(20, intval($_GPC['psize']));
    $condition = "uniacid=:uniacid and status=1";
    $params[':uniacid'] = $_SESSION['appuniacid'];
    if ($_SESSION['apptype'] && $_SESSION['apptype'] != 1) {
        if ($_SESSION['apptype'] == 5) {
            $store = xtrim($_SESSION['appstore']);
            $condition .= " and id in({$store})";
        }
//        $condition .= " and uid=:uid";
//        $params[':uid'] = $_SESSION['appuid'];
    }
    $sql = "SELECT * FROM" . tablename('xcommunity_dp') . "WHERE $condition order by id desc LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
    $list = pdo_fetchall($sql, $params);
    foreach ($list as $k => $v) {
//        $list[$k]['src'] = $v['picurl'] ? tomedia($v['picurl']) : MODULE_URL . 'template/mobile/default2/static/images/icon-zanwu.png';
        $list[$k]['createtime'] = date('Y-m-d H:i', $v['createtime']);
        $list[$k]['link'] = $this->createMobileUrl('xqsys', array('op' => 'business', 'p' => 'detail', 'id' => $v['id']));
    }
    util::send_result($list);
}
/**
 * 商家的商品
 */
if ($op == 'goods') {
    $p = in_array(trim($_GPC['p']), array('add', 'detail', 'list', 'del')) ? trim($_GPC['p']) : 'list';
    /**
     * 商品的列表
     */
    if ($p == 'list') {
        $pindex = max(1, intval($_GPC['page']));
        $psize = max(20, intval($_GPC['psize']));
        $condition = " uniacid =:uniacid and type=2 and isshow=0";
        $parms[':uniacid'] = $_SESSION['appuniacid'];
        if ($_SESSION['apptype'] && $_SESSION['apptype'] != 1) {
//            $condition .= " and uid=:uid";
//            $parms[':uid'] = $_SESSION['appuid'];
            if ($_SESSION['apptype'] == 5) {
                $store = xtrim($_SESSION['appstore']);
                $condition .= " and dpid in({$store})";
            }
        }
        $sql = "SELECT * FROM" . tablename('xcommunity_goods') . "WHERE $condition order by createtime desc LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
        $list = pdo_fetchall($sql, $parms);
        foreach ($list as $k => $v) {
            $imgs = explode(',', $v['thumb_url']);
            $thumb = $v['thumb'] ? tomedia($v['thumb']) : tomedia($imgs[0]);
            $list[$k]['src'] = $thumb ? $thumb : MODULE_URL . 'template/mobile/default2/static/images/icon-zanwu.png';
            $list[$k]['createtime'] = date('Y-m-d H:i', $v['createtime']);
            $list[$k]['link'] = $this->createMobileUrl('xqsys', array('op' => 'business', 'p' => 'gooddetail', 'id' => $v['id']));
        }
        util::send_result($list);
    }
    /**
     * 商品的添加
     */
    if ($p == 'add') {
        $starttime = strtotime($_GPC['starttime']);
        $endtime = strtotime($_GPC['endtime']);
        if (!empty($starttime) && $starttime == $endtime) {
            $endtime = $endtime + 86400 - 1;
        }
        $startdate = strtotime($_GPC['startdate']);
        $enddate = strtotime($_GPC['enddate']);
        if (!empty($startdate) && $startdate == $enddate) {
            $enddate = $enddate + 86400 - 1;
        }
        $dpid = intval($_GPC['dpid']);
        $data = array(
            'uniacid'      => $_W['uniacid'],
            'title'        => $_GPC['title'],
            'status'       => $_GPC['status'],
            'thumb_url'    => $_GPC['pics'],
            'thumbs'       => $_GPC['moreimg'],
            'marketprice'  => $_GPC['marketprice'],
            'productprice' => $_GPC['productprice'],
            'total'        => intval($_GPC['total']),
            'dpid'         => $dpid,
            'type'         => 2,
            'createtime'   => TIMESTAMP,
            'instruction'  => $_GPC['instruction'],
            'starttime'    => $starttime,
            'endtime'      => $endtime,
            'startdate'    => $startdate,
            'enddate'      => $enddate,
            'isshow'       => 0,
            'limitnum'     => intval($_GPC['limitnum']),
            'wlinks'       => $_GPC['wlinks'],
            'rules'        => trim($_GPC['rules']),
        );
        // 商品的封面图
        if ($_GPC['thumb']) {
            $data['thumb'] = $_GPC['morepic'];
        }
        $id = intval($_GPC['id']);
        // 商品的套餐
        $combo = $_GPC['combo'];
        if ($combo) {
            $data['combo'] = serialize($combo);
        }
        if ($id) {
            pdo_update('xcommunity_goods', $data, array('id' => $id));
            util::permlog('商家商品-修改', '信息标题:' . $data['title']);
        } else {
            $data['uid'] = $_SESSION['appuid'];
            pdo_insert('xcommunity_goods', $data);
            $id = pdo_insertid();
            util::permlog('商家商品-添加', '信息标题:' . $data['title']);
        }
        $data = array();
        $data['id'] = $id;
        util::send_result($data);
    }
    /**
     * 商品的详情
     */
    if ($p == 'detail') {
        $id = intval($_GPC['id']);
        if ($id) {
            $sql = "SELECT t1.*,t2.sjname FROM" . tablename('xcommunity_goods') . "t1 left join" . tablename('xcommunity_dp') . "t2 on t1.dpid=t2.id WHERE t1.id=:id AND t1.uniacid=:uniacid";
            $params = array(':id' => $id, ':uniacid' => $_W['uniacid']);
            $item = pdo_fetch($sql, $params);
            $item['thumb'] = tomedia($item['thumb']);
            $item['content'] = strip_tags($item['content']);
            $item['description'] = strip_tags($item['description']);
            //展示多图
            $piclist = array();
            if ($item['thumb_url']) {
                $thumbs = explode(',', $item['thumb_url']);
                if ($thumbs) {
                    foreach ($thumbs as $key => $value) {
                        $piclist[] = tomedia($value);
                    }
                }
            }
            //展示多图
            $images = array();
            if ($item['thumbs']) {
                $imgs = explode(',', $item['thumbs']);
                if ($imgs) {
                    foreach ($imgs as $key => $val) {
                        $images[] = tomedia($val);
                    }
                }
            }
            $item['thumbs'] = $images;
            $item['piclist'] = $piclist;
            $item['url'] = $this->createMobileUrl('xqsys', array('op' => 'business', 'p' => 'post', 'id' => $item['id']));
            $item['combo'] = unserialize($item['combo']);
            $item['starttime'] = date('Y-m-d H:i', $item['starttime']);
            $item['endtime'] = date('Y-m-d H:i', $item['endtime']);
            $item['startdate'] = date('Y-m-d H:i', $item['startdate']);
            $item['enddate'] = date('Y-m-d H:i', $item['enddate']);
            util::send_result($item);
        }

    }
    /**
     * 商品的删除
     */
    if ($p == 'del') {
        $id = intval($_GPC['id']);
        if ($id) {
            $item = pdo_fetch("SELECT id FROM" . tablename('xcommunity_goods') . "WHERE id=:id AND uniacid=:uniacid", array(':id' => $id, ':uniacid' => $_W['uniacid']));
            if ($item) {
//                if (pdo_delete('xcommunity_goods', array('id' => $id))) {
                if (pdo_update('xcommunity_goods', array('isshow' => 1), array('dpid' => $id))) {
                    util::send_result();
                }
            }
        }
    }
}
/**
 * 商家的订单
 */
if ($op == 'order') {
    $p = in_array(trim($_GPC['p']), array('add', 'detail', 'list')) ? trim($_GPC['p']) : 'list';
    if ($p == 'list') {
        $condition = "t1.uniacid=:uniacid and t1.type='business'";
        $parms[':uniacid'] = $_SESSION['appuniacid'];
//        if ($_SESSION['apptype'] && $_SESSION['apptype'] != 1) {
//            $condition .= " and t6.uid=:uid";
//            $parms[':uid'] = $_SESSION['appuid'];
//        }
        if ($_SESSION['apptype'] && $_SESSION['apptype'] != 1) {
//        $condition .= " and t2.uid=:uid";
//        $parms[':uid'] = $_SESSION['appuid'];
            if ($_SESSION['apptype'] == 5) {
                $condition .= " and t1.dpid in({$_SESSION['appstore']})";
            }
        }
        $pindex = max(1, intval($_GPC['page']));
        $psize = max(20, intval($_GPC['psize']));
        $sql = "select t1.id,t1.ordersn,t4.realname,t4.mobile,t1.price,t1.status,t1.createtime from" . tablename('xcommunity_order') . "t1 left join" . tablename('xcommunity_member_room') . "t2 on t1.addressid=t2.id left join" . tablename('mc_members') . "t4 on t1.uid=t4.uid left join" . tablename('xcommunity_order_goods') . "t5 on t5.orderid= t1.id left join" . tablename('xcommunity_goods') . "t6 on t6.id=t5.goodsid where $condition ORDER BY t1.status DESC, t1.createtime DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
        $list = pdo_fetchall($sql, $parms);
        foreach ($list as $key => $value) {
            $list[$key]['cctime'] = date('Y-m-d H:i', $value['createtime']);
            $list[$key]['s'] = empty($value['status']) ? '未付' : '已付';
            $list[$key]['link'] = $this->createMobileUrl('xqsys', array('op' => 'business', 'p' => 'orderdetail', 'id' => $value['id']));
        }
        util::send_result($list);
    }
}
/**
 * 充值记录
 */
if ($op == 'rechargelog') {
    $condition = " uniacid=:uniacid ";
    $parms[':uniacid'] = $_SESSION['appuniacid'];
    if ($_SESSION['apptype'] && $_SESSION['apptype'] != 1) {
        $condition .= " and uid=:uid";
        $parms[':uid'] = $_SESSION['appuid'];

    }

    $pindex = max(1, intval($_GPC['page']));
    $psize = max(20, intval($_GPC['psize']));
    $sql = "select * from" . tablename('xcommunity_recharge') . "where $condition ORDER BY createtime DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
    $list = pdo_fetchall($sql, $parms);
    foreach ($list as $key => $value) {
        $list[$key]['createtime'] = date('Y-m-d H:i', $value['createtime']);
    }
    util::send_result($list);
}
/**
 * 商家的提现记录
 */
if ($op == 'cashlog') {
    $condition = " uniacid=:uniacid and type='cash'";
    $parms[':uniacid'] = $_SESSION['appuniacid'];
    if ($_SESSION['apptype'] && $_SESSION['apptype'] != 1) {
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
 * 消费记录
 */
if ($op == 'record') {
    $condition = " t1.uniacid=:uniacid and t1.type='xxbusiness'";
    $parms[':uniacid'] = $_SESSION['appuniacid'];
    if ($_SESSION['apptype'] && $_SESSION['apptype'] != 1) {
//        $condition .= " and t2.uid=:uid";
//        $parms[':uid'] = $_SESSION['appuid'];
        if ($_SESSION['apptype'] == 5) {
            $condition .= " and t2.id in({$_SESSION['appstore']})";
        }
    }
    $pindex = max(1, intval($_GPC['page']));
    $psize = max(20, intval($_GPC['psize']));
    $sql = "select t1.*,t3.realname,t3.mobile from" . tablename('xcommunity_order') . "t1 left join" . tablename('xcommunity_dp') . "t2 on t1.dpid = t2.id left join" . tablename('mc_members') . "t3 on t3.uid=t1.uid where $condition ORDER BY t1.createtime DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
    $list = pdo_fetchall($sql, $parms);
    foreach ($list as $key => $value) {
        $list[$key]['createtime'] = date('Y-m-d H:i', $value['createtime']);
        $list[$key]['status'] = empty($value['status']) ? '未支付' : '已支付';
    }
    util::send_result($list);
}
/**
 * 商家店铺的添加
 */
if ($op == 'add') {

    $id = intval($_GPC['id']);
    $data = array(
        'uniacid'      => $_W['uniacid'],
        'sjname'       => $_GPC['sjname'],
        'picurl'       => $_GPC['morepic'],
        'contactname'  => $_GPC['contactname'],
        'mobile'       => $_GPC['mobile'],
        'phone'        => $_GPC['phone'],
        'qq'           => $_GPC['qq'],
        'businesstime' => $_GPC['open_time_start'] . '-' . $_GPC['open_time_end'],
        'address'      => $_GPC['address'],
        'shopdesc'     => htmlspecialchars_decode($_GPC['shopdesc']),
        'parent'       => intval($_GPC['parentvalue']),
        'child'        => intval($_GPC['childvalue']),
        'province'     => $reside['province'],
        'city'         => $reside['city'],
        'dist'         => $reside['district'],
        'lat'          => $_GPC['lat'],
        'lng'          => $_GPC['lng'],
        'businessurl'  => $_GPC['businessurl'],
        'createtime'   => TIMESTAMP,
        'price'        => $_GPC['price'],
        'area'         => $_GPC['area'],
        'instruction'  => $_GPC['instruction'],
        'cid'          => intval($_GPC['cid']),
        'status'       => 1,
        'serviceurl'   => $_GPC['serviceurl'],
        'cardurl'      => $_GPC['cardurl'],
        'activityurl'  => $_GPC['activityurl']
    );
    $rule = array(
        'uniacid' => $_W['uniacid'],
        'name'    => $_GPC['sjname'],
        'module'  => 'cover',
        'status'  => 1,
    );

    $result = pdo_insert('rule', $rule);
    $rid = pdo_insertid();
    if (empty($id)) {
//
//        $data['uid'] = $_SESSION['appuid'];
        if ($user['uuid']) {
            //判断上级管理员是否是超市
            $suser = pdo_get("xcommunity_users", array('uid' => $user['uuid']), array());
            $data['uid'] = $suser['type'] == 5 ? $user['uuid'] : $_SESSION['appuid'];
        } else {
            $data['uid'] = $_SESSION['appuid'];
        }
        $data['rid'] = $rid;
        pdo_insert('xcommunity_dp', $data);
        $dpid = pdo_insertid();
        if ($_SESSION['appuid'] !== 1 && $user) {
            //超市管理员和超市绑定
            if ($suser['type'] == 5) {
                //上一级是超市
                $store = xtrim($suser['store'] . ',' . $dpid);
                pdo_update('xcommunity_users', array('store' => $store), array('id' => $suser['id']));
            } else {
                $store = xtrim($user['store'] . ',' . $dpid);
                pdo_update('xcommunity_users', array('store' => $store), array('id' => $user['id']));
            }
        }
        util::permlog('商家店铺-添加', '信息标题:' . $data['sjname']);
    } else {
        $data['rid'] = $rid;
        pdo_update('xcommunity_dp', $data, array('id' => $id));
        $dpid = $id;
        util::permlog('商家店铺-修改', '信息标题:' . $data['sjname']);
    }
    $ruleword = array(
        'rid'     => $rid,
        'uniacid' => $_W['uniacid'],
        'module'  => 'cover',
        'content' => $data['sjname'],
        'type'    => 1,
        'status'  => 1,
    );
    pdo_insert('rule_keyword', $ruleword);
    $entry = array(
        'uniacid'     => $_W['uniacid'],
        'multiid'     => 0,
        'rid'         => $rid,
        'title'       => $_GPC['sjname'],
        'description' => '',
        'thumb'       => tomedia($_GPC['morepic']),
        'url'         => $this->createMobileUrl('business', array('op' => 'detail', 'id' => $dpid)),
        'do'          => 'business',
        'module'      => $this->module['name'],
    );
    pdo_insert('cover_reply', $entry);
    util::send_result();
}
/**
 *
 */
if ($op == 'del') {

}
/**
 * 参数设置
 */
if ($op == 'setting') {
    $data = array(
        'creditstatus' => intval($_GPC['creditstatus']),
        'integral'     => intval($_GPC['integral'])
    );
    pdo_update('xcommunity_users', $data, array('uid' => $_SESSION['appuid']));
    util::send_result();
}
/**
 * 订单的详情
 */
if ($op == 'orderdetail') {
    $id = intval($_GPC['id']);
    $item = pdo_fetch("select t1.ordersn,t1.*,t4.realname,t4.mobile,t2.address,t2.city,t6.title from" . tablename('xcommunity_order') . "t1 left join" . tablename('xcommunity_member_address') . "t2 on t1.addressid=t2.id left join" . tablename('mc_members') . "t4 on t4.uid= t1.uid left join" . tablename('xcommunity_order_goods') . "t5 on t5.orderid= t1.id left join" . tablename('xcommunity_goods') . "t6 on t6.id=t5.goodsid where t1.id=:id", array(':id' => $id));
    util::send_result($item);
}
/**
 * 商家的店铺二维码
 */
if ($op == 'qr') {
    //支付二维码
    $id = intval($_GPC['id']);
    if (empty($id)) {
        util::send_error(-1, '缺少参数');
        exit();
    }
    $dp = pdo_get('xcommunity_dp', array('id' => $id), array('id', 'sjname'));
    if ($dp) {
        $url = $_W['siteroot'] . "app/index.php?i={$_W['uniacid']}&c=entry&id={$id}&op=pay&do=business&m=" . $this->module['name'];//二维码内容
        $img = $dp['sjname'] . ".png";
        $tmpdir = "../addons/" . $this->module['name'] . "/data/qrcode/business/" . $_W['uniacid'] . "/";
        $imgHtml = createQr($url, $img, $tmpdir);
        $data = array();
        $data['url'] = $url;
        util::send_result($data);
    }

}
/**
 * 核销
 */
if ($op == 'hx') {
    $code = trim($_GPC['code']);
    $user = $_W['username'] ? $_W['username'] : $_SESSION['username'];
    if ($code) {
        //这个地方你需要判断code是否存在，当前商家是否有权限核销code
        if ($_SESSION['apptype'] == 2 || $_SESSION['apptype'] == 3 || $_SESSION['apptype'] == 4) {
            util::send_result(array('content' => '无权限操作'));
            exit();
        }
        $condition = "t1.couponsn=:code";
        $params[':code'] = $code;
        if ($_SESSION['apptype'] == 5) {
            /**
             * 商家管理员
             */
//            $condition .= " and t3.uid=:uid";
//            $params[':uid'] = $_SESSION['appuid'];
            if ($_SESSION['apptype'] == 5) {
                $condition .= " and t4.dpid in({$_SESSION['appstore']})";
            }
        }
        $item = pdo_fetch("select t3.endtime,t1.status,t1.id from" . tablename('xcommunity_coupon_order') . "t1 left join" . tablename('xcommunity_order_goods') . "t2 on t1.orderid=t2.orderid left join" . tablename('xcommunity_goods') . "t3 on t2.goodsid=t3.id left join" . tablename('xcommunity_order') . "t4 on t4.id = t2.orderid where $condition", $params);
        if ($item) {
            /**
             * 判断是否已核销
             */
            if ($item['status'] == 2) {
                util::send_result(array('content' => '已被核销，无法重复核销'));
                exit();
            }
            /**
             * 判断是否已过期
             */
            $nowtime = time();
            if ($item['endtime'] >= $nowtime) {
                $r = pdo_update('xcommunity_coupon_order', array('status' => 2, 'usetime' => TIMESTAMP, 'ip' => CLIENT_IP, 'user' => $user), array('id' => $item['id']));
                if ($r) {
                    util::permlog('商家券号-核销', '核销券号ID:' . $id);
                    util::send_result(array('content' => '核销成功'));
                    exit();
                }
            } else {
                util::send_result(array('content' => '核销失败,已过期'));
                exit();
            }
        } else {
            util::send_error(-1, '非法操作');
        }
    } else {
        util::send_error(-1, '非法操作');
    }

}
/**
 * 商家的店铺
 */
if ($op == 'dp') {
    $id = intval($_GPC['id']);
    $p = in_array(trim($_GPC['p']), array('list', 'detail', 'del')) ? trim($_GPC['p']) : 'detail';
    /**
     * 商铺的详情
     */
    if ($p == 'detail') {
        if (empty($id)) {
            util::send_error(-1, '店铺不存在');
            exit();
        }
        $data = array();
        $item = pdo_fetch("SELECT * FROM" . tablename('xcommunity_dp') . "WHERE id=:id and status=1", array(':id' => $id));
        if (empty($item)) {
            util::send_error(-1, '店铺不存在');
            exit();
//            itoast('店铺不存在或已删除', referer(), 'error',true);
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
    /**
     * 商铺的列表
     */
    if ($p == 'list') {
        $condition = "uniacid=:uniacid and status=1";
        $params[':uniacid'] = $_SESSION['appuniacid'];
        if ($_SESSION['apptype'] && $_SESSION['apptype'] != 1) {
//            $condition .= " and uid=:uid";
//            $params[':uid'] = $_SESSION['appuid'];
            if ($_SESSION['apptype'] == 5) {
                $store = xtrim($_SESSION['appstore']);
                $condition .= " and id in({$store})";
            }
        }
        $sql = "SELECT * FROM" . tablename('xcommunity_dp') . "WHERE $condition order by id desc";
        $list = pdo_fetchall($sql, $params);
        foreach ($list as $k => $v) {
            $list[$k]['key'] = $v['id'];
            $list[$k]['value'] = $v['sjname'];
        }
        util::send_result($list);
    }
    /**
     * 店铺的删除
     */
    if ($p == 'del') {
        $id = intval($_GPC['id']);
        if ($id) {
            $item = pdo_get('xcommunity_dp', array('id' => $id), array('id'));
            if ($item) {
//                pdo_delete('xcommunity_goods', array('dpid' => $id));
                pdo_update('xcommunity_goods', array('isshow' => 1), array('dpid' => $id));
                if (pdo_update('xcommunity_dp', array('status' => 2), array('id' => $id))) {
                    util::send_result();
                }
            }
        }
    }
}
/**
 * 修改商品的套餐
 */
if ($op == 'goodsCombo') {
    $id = intval($_GPC['id']);
    $combo = $_GPC['combo'];
    $combo = json_decode($combo, true);
    if (empty($id)) {
        util::send_error(-1, 'id参数错误');
    }
    $item = pdo_get('xcommunity_goods', array('id' => $id), array());
    if (empty($item)) {
        util::send_error(-1, '信息不存在或已经删除');
    }
    pdo_update('xcommunity_goods', array('combo' => serialize($combo)), array('id' => $id));
    util::send_result();
}