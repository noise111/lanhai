<?php
/**
 * 小区秘书模块
 *
 * [蓝牛科技] Copyright (c) 2013 xqms.cn
 */
/**
 * 后台小区商家
 */
global $_GPC, $_W;
$ops = array('dp', 'order', 'del', 'verify', 'coupon', 'goods', 'setgoodsproperty', 'rank', 'notice', 'delete', 'wechat', 'qrpl', 'download', 'print', 'set', 'payapi', 'qrlist', 'qrdel', 'setting', 'lineorder', 'commission', 'dpset');
$op = !empty($_GPC['op']) ? $_GPC['op'] : 'dp';
if (!in_array($op, $ops)) {
    message('该方法不存在(op:' . $op . ')');
}
$p = !empty($_GPC['p']) ? $_GPC['p'] : 'list';
$operation = !empty($_GPC['operation']) ? $_GPC['operation'] : 'list';
$id = intval($_GPC['id']);
// 操作员
$user = util::xquser($_W['uid']);
if ($user && $user['menu_ops']) {
    $menu_opss = explode(',', $user['menu_ops']);
}
/**
 * 商家的店铺
 */
if ($op == 'dp') {
    /**
     * 店铺列表
     */
    if ($operation == 'list') {
        // AJAX是否显示
        $id = intval($_GPC['id']);
        if ($_W['isajax'] && $id) {
            $data = array();
            $data['creditpay'] = intval($_GPC['creditpay']);
            if (pdo_update('xcommunity_dp', $data, array('id' => $id)) !== false) {
                exit('success');
            }

        }
        //店铺列表
        $condition = 'uniacid=:uniacid and status=1';
        $parms[':uniacid'] = $_W['uniacid'];
        if ($user) {
            //普通管理员
            if ($user['type'] == 5) {
                $store = xtrim($user['store']);
                $condition .= " and id in({$store})";
            }
        }
        if (!empty($_GPC['keyword'])) {
            $condition .= " AND (sjname LIKE '%{$_GPC['keyword']}%' or mobile LIKE '%{$_GPC['keyword']}%')";
        }
        $pindex = max(1, intval($_GPC['page']));
        $psize = 20;
        $sql = "SELECT * FROM" . tablename('xcommunity_dp') . "WHERE $condition order by id desc LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
        $list = pdo_fetchall($sql, $parms);
        foreach ($list as $key => $val) {
            $pate = pdo_get('xcommunity_category', array('id' => $val['parent']), array('name'));
            $list[$key][patename] = $pate['name'];
            $cate = pdo_get('xcommunity_category', array('id' => $val['child']), array('name'));
            $list[$key][catename] = $cate['name'];
        }

        $total = pdo_fetchcolumn("SELECT COUNT(*) FROM" . tablename('xcommunity_dp') . "WHERE $condition", $parms);
        $pager = pagination($total, $pindex, $psize);

        include $this->template('web/plugin/business/dp/list');
    }
    /**
     * 店铺的添加修改
     */
    if ($operation == 'add') {
        //添加店铺
        $category = util::fetchall_category(6);
        $parent = array();
        $children = array();

        if (!empty($category)) {
            $children = array();
            foreach ($category as $cid => $cate) {
                if (!empty($cate['parentid'])) {
                    $children[$cate['parentid']][] = $cate;
                } else {
                    $parent[$cate['id']] = $cate;
                }
            }
        }
        if ($id) {
            $item = pdo_fetch("SELECT * FROM" . tablename('xcommunity_dp') . "WHERE id=:id", array(':id' => $id));
            $tags = explode(',', $item['tag']);
            if (empty($item)) {
                itoast('店铺不存在或已删除', referer(), 'error', true);
            }
            $pcate = $item['parent'];
            $ccate = $item['child'];
            $businesstime = explode('-', $item['businesstime']);
            // print_r($businesstime);
            $regs = iunserializer($item['regionid']);
        }
        $start = $businesstime[0] ? $businesstime[0] : '8:00';
        $end = $businesstime[1] ? $businesstime[1] : '20:00';
        if ($_W['isajax']) {
            $reside = $_GPC['reside'];
            $baidumap = $_GPC['baidumap'];
            if (empty($_GPC['picurl'])) {
                echo json_encode(array('content' => '图片不可为空!'));
                exit();
            }
            $data = array(
                'uniacid'      => $_W['uniacid'],
                'sjname'       => $_GPC['sjname'],
                'picurl'       => $_GPC['picurl'],
                'contactname'  => $_GPC['contactname'],
                'mobile'       => $_GPC['mobile'],
                'phone'        => $_GPC['phone'],
                'qq'           => $_GPC['qq'],
                'businesstime' => $_GPC['open_time_start'] . '-' . $_GPC['open_time_end'],
                'address'      => $_GPC['address'],
                'shopdesc'     => htmlspecialchars_decode($_GPC['shopdesc']),
                'parent'       => intval($_GPC['category']['parentid']),
                'child'        => intval($_GPC['category']['childid']),
                'province'     => $reside['province'],
                'city'         => $reside['city'],
                'dist'         => $reside['district'],
                'lat'          => $baidumap['lat'],
                'lng'          => $baidumap['lng'],
                'businessurl'  => $_GPC['businessurl'],
                'createtime'   => TIMESTAMP,
                'price'        => $_GPC['price'],
                'area'         => $_GPC['area'],
                'instruction'  => $_GPC['instruction'],
                'tag'          => $_GPC['tag'] ? implode(',', $_GPC['tag']) : '',
                'cid'          => intval($_GPC['cid']),
                'enable'       => intval($_GPC['enable']),
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
//                $data['uid'] = $_W['uid'];
                if ($user['uuid']) {
                    //判断上级管理员是否是超市
                    $suser = pdo_get("xcommunity_users", array('uid' => $user['uuid']), array());
                    $data['uid'] = $suser['type'] == 5 || $suser['type'] == 1 ? $user['uuid'] : $_W['uid'];
                } else {
                    $data['uid'] = $_W['uid'];
                }
                $data['rid'] = $rid;
                pdo_insert('xcommunity_dp', $data);
                $dpid = pdo_insertid();
                if ($_W['uid'] !== 1 && $user) {
                    //商家管理员和商家绑定
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
                'thumb'       => tomedia($_GPC['picurl']),
                'url'         => $this->createMobileUrl('business', array('op' => 'detail', 'id' => $dpid)),
                'do'          => 'business',
                'module'      => $this->module['name'],
            );
            pdo_insert('cover_reply', $entry);
            echo json_encode(array('status' => 1));
            exit();
        }
        load()->func('tpl');
        $options = array();
        $options['dest_dir'] = $_W['uid'] == 1 ? '' : MODULE_NAME . '/' . $_W['uid'];
        include $this->template('web/plugin/business/dp/add');
    }
    /**
     * 店铺的打印设置（废弃）
     */
    if ($operation == 'print') {
        $dpid = intval($_GPC['dpid']);
        if ($dpid) {
            $item = pdo_get('xcommunity_business_print', array('dpid' => $dpid), array());
        }
        if (checksubmit('submit')) {
            $data = array(
                'uniacid'  => $_W['uniacid'],
                'type'     => $_GPC['type'],
                'dpid'     => $dpid,
                'api_key'  => $_GPC['api_key'],
                'deviceNo' => $_GPC['deviceNo']
            );
            if (empty($item['id'])) {
                pdo_insert('xcommunity_business_print', $data);
            } else {
                pdo_update('xcommunity_business_print', $data, array('id' => $id));
            }
            itoast('添加成功', $this->createWebUrl('business', array('op' => 'dp')), 'success', true);
        }
        include $this->template('web/plugin/business/dp/print');
    }
    /**
     * 店铺的二维码
     */
    if ($operation == 'qr') {
        $id = intval($_GPC['id']);
        $item = pdo_get('xcommunity_dp', array('uniacid' => $_W['uniacid'], 'id' => $id), array('id', 'sjname'));
        $url = $_W['siteroot'] . "app/index.php?i={$_W['uniacid']}&c=entry&id={$item['id']}&op=pay&do=business&m=" . $this->module['name'];//二维码内容
        $img = convertEncoding($item['sjname']) . ".png";
        $tmpdir = "../addons/" . $this->module['name'] . "/data/qrcode/business/" . $_W['uniacid'] . "/";
        $imgHtml = createQr($url, $img, $tmpdir);
        echo $imgHtml;
        exit();
    }
    /**
     * 店铺状态的修改
     */
    if ($operation == 'change') {
        $type = $_GPC['type'];
        $data = intval($_GPC['data']);
        if (in_array($type, array('enable'))) {
            $data = ($data == 1 ? '2' : '1');
            pdo_update("xcommunity_dp", array($type => $data), array("id" => $id, "uniacid" => $_W['uniacid']));
            die(json_encode(array("result" => 1, "data" => $data)));
        }
        die(json_encode(array("result" => 0)));
    }
}
/**
 * 商家的订单
 */
if ($op == 'order') {
    /**
     * 订单的列表
     */
    if ($operation == 'list') {
        //删除
        if ($_W['ispost']) {
            $ids = $_GPC['ids'];
            if (!empty($ids)) {
                foreach ($ids as $key => $id) {
                    pdo_delete('xcommunity_order', array('id' => $id));
                }
                util::permlog('', '批量删除商家订单');
                itoast('删除成功', referer(), 'success', true);
            }
        }

        $condition = "t1.uniacid=:uniacid and t1.type='business'";
        $parms[':uniacid'] = $_W['uniacid'];
        if ($user && $user['type'] != 1) {
            if ($user['type'] == 5) {
                $condition .= " and t6.id in({$user['store']})";
            }
        }

        if (empty($starttime) || empty($endtime)) {
            $starttime = strtotime('-1 month');
            $endtime = time();
        }
        if (!empty($_GPC['time'])) {
            $starttime = strtotime($_GPC['time']['start']);
            $endtime = strtotime($_GPC['time']['end']) + 86399;
            $condition .= " AND t1.createtime >= :starttime AND t1.createtime <= :endtime ";
            $parms[':starttime'] = $starttime;
            $parms[':endtime'] = $endtime;
        }
        if (!empty($_GPC['member'])) {
            $condition .= " AND (t4.realname LIKE '%{$_GPC['member']}%' or t4.mobile LIKE '%{$_GPC['member']}%')";
        }
        $status = $_GPC['status'];
        if ($status != '') {
            $condition .= " AND t1.status = '" . intval($status) . "'";
        }
        $keyword = trim($_GPC['keyword']);
        if ($keyword) {
            $condition .= " and (t1.ordersn =:keyword or t1.transid=:keyword)";
            $parms[':keyword'] = $keyword;
        }
        $dps = pdo_getall('xcommunity_dp', array('uniacid' => $_W['uniacid']), array('id', 'sjname'), 'id');
        if ($_GPC['export'] == 1) {
            $sql = "select t1.id,t1.ordersn,t1.transid,t4.realname,t4.mobile,t1.price,t1.status,t1.createtime,t1.credit,t1.randprice,t1.offsetprice,t6.dpid from" . tablename('xcommunity_order') . "t1 left join" . tablename('xcommunity_member_room') . "t2 on t1.addressid=t2.id left join" . tablename('mc_members') . "t4 on t1.uid=t4.uid left join" . tablename('xcommunity_order_goods') . "t5 on t5.orderid= t1.id left join" . tablename('xcommunity_goods') . "t6 on t6.id=t5.goodsid where $condition ORDER BY t1.status desc, t1.createtime desc ";
            $xqlist = pdo_fetchall($sql, $parms);
            foreach ($xqlist as $key => $value) {
                $xqlist[$key]['cctime'] = date('Y-m-d H:i', $value['createtime']);
                $xqlist[$key]['s'] = empty($value['status']) ? '未付' : '已付';
                $xqlist[$key]['sjname'] = $dps[$value['dpid']]['sjname'];
            }
            model_execl::export($xqlist, array(
                "title"   => "商家订单数据-" . date('Y-m-d-H-i', time()),
                "columns" => array(
                    array(
                        'title' => '平台订单号',
                        'field' => 'ordersn',
                        'width' => 30
                    ),
                    array(
                        'title' => '微信订单号',
                        'field' => 'transid',
                        'width' => 30
                    ),
                    array(
                        'title' => '商家姓名',
                        'field' => 'sjname',
                        'width' => 12
                    ),
                    array(
                        'title' => '姓名',
                        'field' => 'realname',
                        'width' => 12
                    ),
                    array(
                        'title' => '手机号',
                        'field' => 'mobile',
                        'width' => 12
                    ),
                    array(
                        'title' => '总价',
                        'field' => 'price',
                        'width' => 12
                    ),
                    array(
                        'title' => '减免金额',
                        'field' => 'randprice',
                        'width' => 12
                    ),
                    array(
                        'title' => '抵扣积分数量',
                        'field' => 'credit',
                        'width' => 12
                    ),
                    array(
                        'title' => '积分抵扣费用',
                        'field' => 'offsetprice',
                        'width' => 12
                    ),
                    array(
                        'title' => '状态',
                        'field' => 's',
                        'width' => 12
                    ),
                    array(
                        'title' => '下单时间',
                        'field' => 'cctime',
                        'width' => 15
                    ),
                )
            ));
        }
        $pindex = max(1, intval($_GPC['page']));
        $psize = 20;
        $sql = "select t1.id,t1.ordersn,t1.transid,t4.realname,t4.mobile,t1.price,t1.status,t1.createtime,t1.credit,t1.randprice,t1.offsetprice,t1.total,t6.dpid from" . tablename('xcommunity_order') . "t1 left join" . tablename('xcommunity_member_room') . "t2 on t1.addressid=t2.id left join" . tablename('mc_members') . "t4 on t1.uid=t4.uid left join" . tablename('xcommunity_order_goods') . "t5 on t5.orderid= t1.id left join" . tablename('xcommunity_goods') . "t6 on t6.id=t5.goodsid where $condition ORDER BY t1.status DESC, t1.createtime DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
        $list = pdo_fetchall($sql, $parms);
        foreach ($list as $key => $value) {
            $list[$key]['cctime'] = date('Y-m-d H:i', $value['createtime']);
            $list[$key]['s'] = empty($value['status']) ? '未付' : '已付';
            $list[$key]['sjname'] = $dps[$value['dpid']]['sjname'];
        }

        $tsql = "select count(*) from" . tablename('xcommunity_order') . "t1 left join" . tablename('xcommunity_member_room') . "t2 on t1.addressid=t2.id left join" . tablename('mc_members') . "t4 on t1.uid=t4.uid left join" . tablename('xcommunity_order_goods') . "t5 on t5.orderid= t1.id left join" . tablename('xcommunity_goods') . "t6 on t6.id=t5.goodsid where $condition ORDER BY t1.status DESC, t1.createtime DESC ";
        $total = pdo_fetchcolumn($tsql, $parms);
        $pager = pagination($total, $pindex, $psize);

        load()->func('tpl');
        include $this->template('web/plugin/business/order/list');
    }
    /**
     * 订单的详情
     */
    if ($operation == 'detail') {
        $id = intval($_GPC['id']);
        if ($id) {
            $item = pdo_get('xcommunity_order', array('id' => $id), array());
            if (empty($item)) {
                itoast('信息不存在或已经删除', referer(), 'error');
            }
            $address = pdo_get('xcommunity_member_address', array('id' => $item['addressid']), array('city', 'address'));
            $member = pdo_get('mc_members', array('uid' => $item['uid']), array('realname', 'mobile'));
            $goodsid = pdo_getcolumn('xcommunity_order_goods', array('orderid' => $item['id']), 'goodsid');
            $goods = pdo_get('xcommunity_goods', array('id' => $goodsid), array('title', 'marketprice', 'productprice', 'status', 'total', 'dpid'));
            $sjname = pdo_getcolumn('xcommunity_dp', array('id' => $goods['dpid']), 'sjname');
            $region = pdo_get('xcommunity_region', array('id' => $item['regionid']), array('title'));
            $item['address'] = $address['address'];
            $item['city'] = $address['city'];
            $item['realname'] = $member['realname'];
            $item['mobile'] = $member['mobile'];
            $item['title'] = $goods['title'];
            $item['marketprice'] = $goods['marketprice'];
            $item['productprice'] = $goods['productprice'];
            $item['goods_status'] = $goods['status'];
            $item['total'] = $goods['total'];
            $item['sjname'] = $sjname;
            $item['xqtitle'] = $region['title'];
        }
        include $this->template('web/plugin/business/order/detail');
    }
    /**
     * 订单的删除
     */
    if ($operation == 'delete') {
        $id = intval($_GPC['id']);
        if ($id) {
            $r = pdo_delete('xcommunity_order', array('id' => $id));
            util::permlog('商家订单-删除', '删除订单ID:' . $id);
            if ($r) {
                itoast('删除成功', referer(), 'success', true);
            }
        }
    }

}
/**
 * 商家的店铺删除
 */
if ($op == 'del') {
    if ($id) {
        $dp = pdo_get('xcommunity_dp', array('id' => $id), array());
        if ($dp) {
            pdo_update('xcommunity_goods', array('isshow' => 1), array('dpid' => $id));
            pdo_delete("xcommunity_alipayment", array('userid' => $id, 'type' => 3));
            pdo_delete("xcommunity_wechat", array('userid' => $id, 'type' => 3));
            pdo_delete("xcommunity_service_data", array('userid' => $id, 'type' => 3));
            pdo_delete("xcommunity_swiftpass", array('userid' => $id, 'type' => 3));
            pdo_delete("xcommunity_hsyunfu", array('userid' => $id, 'type' => 3));
            pdo_delete("xcommunity_chinaums", array('userid' => $id, 'type' => 3));
            pdo_delete('xcommunity_business_print', array('dpid' => $id));
            pdo_delete('xcommunity_business_wechat', array('dpid' => $id));
            if (pdo_update('xcommunity_dp', array('status' => 2), array('id' => $id))) {
                util::permlog('商家店铺-删除', '信息标题:' . $dp['sjname']);
                itoast('删除成功', referer(), 'success');
            }
        }
    }
}
/**
 * 商家的订单处理状态
 */
if ($op == 'verify') {
    $id = intval($_GPC['id']);
    if ($id) {
        if ($_W['isajax']) {
            $r = pdo_update('xcommunity_order', array('status' => 1), array('id' => $id));
            if ($r) {
                $result = array(
                    'status' => 1,
                );
                echo json_encode($result);
                exit();
            }
        }
    }
}
/**
 * 商家的团购券
 */
if ($op == 'coupon') {
    /**
     * 卡券核销列表
     */
    if ($operation == 'list') {
//        $condition = " t2.uniacid=:uniacid ";
//        $parms[':uniacid'] = $_W['uniacid'];
//
//        $code = $_GPC['code'];
//        if ($code) {
//            $condition .= " and t1.couponsn=:couponsn";
//            $parms[':couponsn'] = trim($code);
//        }
//        /**
//         * 判断是总管理员/小区内超级管理员
//         */
//        if ($_W['uid'] == 1 || $user['type'] == 1) {
//            $status = intval($_GPC['status']) ? intval($_GPC['status']) : 3;
//            if ($status == 1 || $status == 2) {
//                $condition .= " and t1.status=:status";
//                $parms[':status'] = $status;
//            }
//        } else {
//            /**
//             * 商家管理员，只查核销
//             */
//            if ($user['type'] == 5) {
//                $condition .= " and t7.dpid in({$user['store']})";
//            }
//            if (empty($code)) {
//                $condition .= " and t1.status=:status ";
//                $parms[':status'] = 2;
//            }
//        }
//        $pindex = max(1, intval($_GPC['page']));
//        $psize = 20;
//        $sql = "select t1.ip,t1.user,t1.usetime,t1.id,t7.title,t6.realname,t1.couponsn,t1.status,t1.createtime,t2.ordersn,t2.transid from" . tablename('xcommunity_coupon_order') . "t1 left join" . tablename('xcommunity_order') . "t2 on t1.orderid=t2.id left join" . tablename('xcommunity_order_goods') . "t3 on t2.id=t3.orderid left join" . tablename('xcommunity_member_room') . "t4 on t2.addressid=t4.id left join" . tablename('mc_members') . "t6 on t2.uid=t6.uid left join" . tablename('xcommunity_goods') . "t7 on t7.id=t3.goodsid where $condition order by t1.createtime desc limit " . ($pindex - 1) * $psize . ',' . $psize;
//        $list = pdo_fetchall($sql, $parms);
//        $tsql = "select count(*) from" . tablename('xcommunity_coupon_order') . "t1 left join" . tablename('xcommunity_order') . "t2 on t1.orderid=t2.id left join" . tablename('xcommunity_order_goods') . "t3 on t2.id=t3.orderid left join" . tablename('xcommunity_member_room') . "t4 on t2.addressid=t4.id left join" . tablename('mc_members') . "t6 on t6.uid=t2.uid left join" . tablename('xcommunity_goods') . "t7 on t7.id=t3.goodsid where $condition order by t1.createtime desc ";
//        $total = pdo_fetchcolumn($tsql, $parms);

        $pindex = max(1, intval($_GPC['page']));
        $psize = 20;
        $condition = array();
        $orderss = pdo_getall('xcommunity_order', array('uniacid' => $_W['uniacid']), array('id', 'ordersn', 'transid', 'uid'), 'id');
        $orderss_ids = _array_column($orderss, 'id');
        $condition['orderid'] = $orderss_ids;
        $code = $_GPC['code'];
        if ($code) {
            $condition['couponsn'] = trim($code);
        }
        /**
         * 判断是总管理员/小区内超级管理员
         */
        if ($_W['uid'] == 1 || $user['type'] == 1) {
            $status = intval($_GPC['status']) ? intval($_GPC['status']) : 3;
            if ($status == 1 || $status == 2) {
                $condition['status'] = $status;
            }
        } else {
            /**
             * 商家管理员，只查核销
             */
            if ($user['type'] == 5) {
                $dpgoods = pdo_getall('xcommunity_goods', array('dpid' => explode(',', $user['store'])), array('id', 'title'));
                $dpgoods_ids = _array_column($dpgoods, 'id');
            }
            if (empty($code)) {
                $condition['status'] = 2;
            }
        }
        $coupons = pdo_getslice('xcommunity_coupon_order', $condition, array($pindex, $psize), $total, '', '', array('createtime desc'));
        $coupons_orderids = _array_column($coupons, 'orderid');
        // 查询订单的商品 判断商家管理员的商品的核销券
        $orderGoods = pdo_getall('xcommunity_order_goods', array('uniacid' => $_W['uniacid'], 'orderid' => $coupons_orderids), array('goodsid', 'orderid'), 'orderid');
        $order_ids = $coupons_orderids;// 订单的id
        $order_goodsids = _array_column($orderGoods, 'goodsid');// 商品的id
        if ($user && $user['type'] == 5) {
            // 判断商家管理员的商品相关
            $order_ids = array();
            $order_goodsids = array();
            foreach ($orderGoods as $k => $v) {
                if (in_array($v['goodsid'], $dpgoods_ids)) {
                    $order_ids[] = $v['orderid'];
                    $order_goodsids[] = $v['goodsid'];
                }
            }
        }
        // 查询订单的信息
        $orders = pdo_getall('xcommunity_order', array('uniacid' => $_W['uniacid'], 'id' => $order_ids), array('id', 'ordersn', 'transid', 'uid', 'dpid'), 'id');
        $order_uids = _array_column($orders, 'uid');
        // 商品的信息
        $goods = pdo_getall('xcommunity_goods', array('uniacid' => $_W['uniacid'], 'id' => $order_goodsids), array('id', 'title'), 'id');
        // 会员的信息
        $members = pdo_getall('mc_members', array('uniacid' => $_W['uniacid'], 'uid' => $order_uids), array('uid', 'realname'), 'uid');
        // 店铺的信息
        $dps = pdo_getall('xcommunity_dp', array('uniacid' => $_W['uniacid']), array('id', 'sjname'), 'id');
        $list = array();
        foreach ($coupons as $k => $v) {
            if (in_array($v['orderid'], $order_ids)) {
                $list[] = array(
                    'id'         => $v['id'],
                    'ip'         => $v['ip'],
                    'user'       => $v['user'],
                    'usetime'    => $v['usetime'],
                    'couponsn'   => $v['couponsn'],
                    'status'     => $v['status'],
                    'createtime' => $v['createtime'],
                    'ordersn'    => $orders[$v['orderid']]['ordersn'],
                    'transid'    => $orders[$v['orderid']]['transid'],
                    'title'      => $goods[$orderGoods[$v['orderid']]['goodsid']]['title'],
                    'realname'   => $members[$orders[$v['orderid']]['uid']]['realname'],
                    'sjname'     => $dps[$orders[$v['orderid']]['dpid']]['sjname'],
                );
            }
        }
        $pager = pagination($total, $pindex, $psize);
        include $this->template('web/plugin/business/coupon/list');
    }
    /**
     * 卡券核销
     */
    if ($operation == 'use') {
        if ($_W['isajax']) {
            if (empty($id)) {
                exit('缺少参数');
            }
            $item = pdo_fetch("select t3.endtime from" . tablename('xcommunity_coupon_order') . "t1 left join" . tablename('xcommunity_order_goods') . "t2 on t1.orderid=t2.orderid left join" . tablename('xcommunity_goods') . "t3 on t2.goodsid=t3.id where t1.id=:id", array(':id' => $id));
            $nowtime = time();
            if ($item['endtime'] >= $nowtime) {
                $r = pdo_update('xcommunity_coupon_order', array('status' => 2, 'usetime' => TIMESTAMP, 'ip' => CLIENT_IP, 'user' => $_W['username']), array('id' => $id));
                if ($r) {
                    util::permlog('商家券号-核销', '核销券号ID:' . $id);
                    $result = array(
                        'status' => 1,
                    );
                    echo json_encode($result);
                    exit();
                }
            } else {
                echo json_encode(array('status' => 2));
                exit();
            }
        }
    }
}
/**
 * 商家的商品
 */
if ($op == 'goods') {
    $cond = array();
    $cond['uniacid'] = $_W['uniacid'];
    $cond['status'] = 1;
    $dpid = intval($_GPC['dpid']);
    if ($dpid) {
        $cond['id'] = $dpid;
    }
    if ($user) {
        if ($user['type'] == 5) {
            $cond['id'] = explode(',', $user['store']);
        }
    }
    $dps = pdo_getall('xcommunity_dp', $cond, array('id', 'sjname'), 'id');
    /**
     * 商品管理列表
     */
    if ($operation == 'list') {
        $pindex = max(1, intval($_GPC['page']));
        $psize = 20;
        $condition = array();
        $condition['uniacid'] = $_W['uniacid'];
        $condition['type'] = 2;
        $condition['isshow'] = 0;
        $dpid = intval($_GPC['dpid']);
        if ($dpid) {
            $condition['dpid'] = $dpid;
        }
        if (!empty($_GPC['keyword'])) {
            $condition['title like'] .= "%{$_GPC['keyword']}%";
        }
        if (isset($_GPC['status'])) {
            $condition['status'] = intval($_GPC['status']);
        }
        if ($user) {
            if ($user['type'] == 5) {
                $condition['dpid'] = explode(',', $user['store']);
            }
        }
        $goods = pdo_getslice('xcommunity_goods', $condition, array($pindex, $psize), $total, '', '', array('id desc'));
        $list = array();
        foreach ($goods as $k => $v) {
            $list[] = array(
                'id'           => $v['id'],
                'sjname'       => $dps[$v['dpid']]['sjname'],
                'title'        => $v['title'],
                'total'        => $v['total'],
                'unit'         => $v['unit'],
                'marketprice'  => $v['marketprice'],
                'productprice' => $v['productprice'],
                'startdate'    => $v['startdate'],
                'enddate'      => $v['enddate'],
                'status'       => $v['status']
            );
        }
        $pager = pagination($total, $pindex, $psize);
        include $this->template('web/plugin/business/goods/list');
    }
    /**
     * 商品的添加修改
     */
    if ($operation == 'add') {
        if ($id) {
            $item = pdo_fetch("SELECT * FROM" . tablename('xcommunity_goods') . "WHERE id=:id AND uniacid=:uniacid", array(':id' => $id, ':uniacid' => $_W['uniacid']));
            $starttime = $item['starttime'];
            $endtime = $item['endtime'];
            $startdate = $item['startdate'];
            $enddate = $item['enddate'];
            $piclist = array();
            if ($item['thumb_url']) {
                $piclist = explode(',', $item['thumb_url']);
            }
            $imglist = array();
            if ($item['thumbs']) {
                $imglist = explode(',', $item['thumbs']);
            }
            $menus = array();
            if ($item['combo']) {
                $menus = unserialize($item['combo']);
            }
            $commons = array();
            if ($item['common']) {
                $commons = unserialize($item['common']);
            }
        }

        if ($_W['isajax']) {
//            print_r($_GPC['combo']);exit();
            $starttime = strtotime($_GPC['birth']['start']);
            $endtime = strtotime($_GPC['birth']['end']);
            if (!empty($starttime) && $starttime == $endtime) {
                $endtime = $endtime + 86400 - 1;
            }
            $startdate = strtotime($_GPC['sale']['start']);
            $enddate = strtotime($_GPC['sale']['end']);
            if (!empty($startdate) && $startdate == $enddate) {
                $enddate = $enddate + 86400 - 1;
            }
            $dpid = intval($_GPC['dpid']);
            $common = array(
                'saleStatus' => intval($_GPC['saleStatus']),
                'useStatus'  => intval($_GPC['useStatus']),
            );
            $data = array(
                'uniacid'      => $_W['uniacid'],
                'title'        => $_GPC['title'],
                'status'       => intval($_GPC['status']),
//                'recommand'    => intval($_GPC['recommand']),
                'thumb'        => $_GPC['thumb'],
                'marketprice'  => $_GPC['marketprice'],
                'productprice' => $_GPC['productprice'],
                'total'        => intval($_GPC['total']),
//                'content'      => htmlspecialchars_decode($_GPC['content']),
//                'description'  => htmlspecialchars_decode($_GPC['description']),
                'dpid'         => $dpid,
                'type'         => 2,
                'createtime'   => TIMESTAMP,
                'instruction'  => $_GPC['instruction'],
                'starttime'    => $starttime,
                'endtime'      => $endtime,
                'wlinks'       => $_GPC['wlinks'],
                'isshow'       => 0,
                'limitnum'     => intval($_GPC['limitnum']),
                'startdate'    => $startdate,
                'enddate'      => $enddate,
                'rules'        => trim($_GPC['rules']),
                'combo'        => serialize($_GPC['combo']),
                'common'       => serialize($common)
            );
            if (@is_array($_GPC['images'])) {
                $data['thumb_url'] = implode(',', $_GPC['images']);
            }
            if (@is_array($_GPC['thumbs'])) {
                $data['thumbs'] = implode(',', $_GPC['thumbs']);
            }
            if ($id) {
                pdo_update('xcommunity_goods', $data, array('id' => $id));
                util::permlog('商家商品-修改', '信息标题:' . $data['title']);
            } else {
//                $data['uid'] = $_W['uid'];
                if ($user['uuid']) {
                    //判断上级管理员是否是商家管理员
                    $suser = pdo_get("xcommunity_users", array('uid' => $user['uuid']), array());
                    $uid = $suser['type'] == 5 || $suser['type'] == 1 ? $user['uuid'] : $_W['uid'];
                    $data['uid'] = $uid;
                } else {
                    $data['uid'] = $_W['uid'];
                }
                pdo_insert('xcommunity_goods', $data);
                $id = pdo_insertid();
                util::permlog('商家商品-添加', '信息标题:' . $data['title']);
            }
            echo json_encode(array('status' => 1));
            exit();
        }
        load()->func('tpl');
        $options = array();
        $options['dest_dir'] = $_W['uid'] == 1 ? '' : MODULE_NAME . '/' . $_W['uid'];
        include $this->template('web/plugin/business/goods/add');
    }
    /**
     * 商品的删除
     */
    if ($operation == 'delete') {
        if ($_W['isajax']) {
            $id = intval($_GPC['id']);
            if (empty($id)) {
                exit('缺少参数');
            }
            $item = pdo_get('xcommunity_goods', array('id' => $id), array());
            if ($item) {
//                $r = pdo_delete("xcommunity_goods", array('id' => $id));
                $r = pdo_update('xcommunity_goods', array('isshow' => 1), array('id' => $id));
                if ($r) {
                    util::permlog('商家商品-删除', '信息标题:' . $item['title']);
                    $result = array(
                        'status' => 1,
                    );
                    echo json_encode($result);
                    exit();
                }
            }

        }
    }

}
/**
 * 商家的商品状态修改
 */
if ($op == 'setgoodsproperty') {
    $type = $_GPC['type'];
    $data = intval($_GPC['data']);
    if (in_array($type, array('recommand', 'status'))) {
        $data = ($data == 1 ? '0' : '1');
        pdo_update("xcommunity_goods", array($type => $data), array("id" => $id, "uniacid" => $_W['uniacid']));
        die(json_encode(array("result" => 1, "data" => $data)));
    }
    die(json_encode(array("result" => 0)));
}
/**
 * 商家的评价
 */
if ($op == 'rank') {
    //评价管理
    $dpid = intval($_GPC['dpid']);
    if ($operation == 'list') {
        $pindex = max(1, intval($_GPC['page']));
        $psize = 20;
        $condition = array();
        $condition['uniacid'] = $_W['uniacid'];
        $condition['type'] = 1;
        if ($dpid) {
            $dpGoods = pdo_getall('xcommunity_goods', array('type' => 2, 'dpid' => $dpid), array('id'));
            $goodsIds = _array_column($dpGoods, 'id');
            $condition['rankid'] = $goodsIds;
        }
        $ranks = pdo_getslice('xcommunity_rank', $condition, array($pindex, $psize), $total, '', '', array('createtime desc'));
        $ranks_uids = _array_column($ranks, 'uid');
        $members = pdo_getall('mc_members', array('uid' => $ranks_uids), array('avatar', 'nickname', 'realname', 'uid'), 'uid');
        $list = array();
        if ($ranks) {
            foreach ($ranks as $k => $v) {
                $list[] = array(
                    'id'         => $v['id'],
                    'content'    => $v['content'],
                    'rankid'     => $v['rankid'],
                    'rank'       => $v['rank'],
                    'createtime' => $v['createtime'],
                    'status'     => 1,
                    'realname'   => $members[$v['uid']]['realname'],
                );
            }
        }
        $pager = pagination($total, $pindex, $psize);
        include $this->template('web/plugin/business/rank/list');
    }
}
/**
 * 未知
 */
if ($op == 'notice') {
    $operation = in_array($_GPC['operation'], array('list', 'add')) ? $_GPC['operation'] : 'list';
    if ($operation == 'add') {
        $uniacid = intval($_W['uniacid']);
        $condition = '';
        if (intval($_GPC['uuid'])) {
            $uuid = intval($_GPC['uuid']);
            $condition = " AND x.uuid='{$uuid}'";
        } else {
            $condition = " AND x.uuid='{$_W['uid']}'";
        }
        $permission = pdo_fetchall("SELECT u.uid,x.id as id FROM " . tablename('uni_account_users') . "as u left join " . tablename('xcommunity_users') . "as x on u.uid = x.uid WHERE x.uniacid = '{$uniacid}' AND u.role='operator' $condition", array(), 'uid');
        if (!empty($permission)) {
            $member = pdo_fetchall("SELECT username, uid,status FROM " . tablename('users') . "WHERE uid IN (" . implode(',', array_keys($permission)) . ")", array(), 'uid');
        }
        $id = intval($_GPC['id']);
        if ($id) {
            $item = pdo_fetch("SELECT * FROM" . tablename('xcommunity_xqnotice') . "WHERE uniacid=:uniacid AND id=:id", array(":uniacid" => $_W['uniacid'], ":id" => $id));
            if (empty($item)) {
                itoast('该信息不存在或已删除', referer(), 'error', true);
            }
        }
        if (checksubmit('submit')) {
            $userid = intval($_GPC['userid']);
            $data = array(
                'uniacid'    => $_W['uniacid'],
                'userid'     => $userid,
                'username'   => $_GPC['username'],
                'createtime' => TIMESTAMP,
                'status'     => intval($_GPC['status']),
                'uid'        => $_W['uid'],
                'type'       => 2
            );
            if ($id) {
                if (pdo_update('xcommunity_xqnotice', $data, array('id' => $id))) {
                    itoast('提交成功', referer(), 'success', true);
                }
            } else {
                if (pdo_insert('xcommunity_xqnotice', $data)) {
                    itoast('提交成功', referer(), 'success', true);
                }
            }
        }

        include $this->template('web/plugin/shopping/notice/add');
    } elseif ($operation == 'list') {
        $pindex = max(1, intval($_GPC['page']));
        $psize = 20;
        $condition = "uniacid=:uniacid";
        $params[':uniacid'] = $_W['uniacid'];
        if ($user['type'] != 1) {
            $condition .= " AND uid=:uid";
            $params[':uid'] = $_W['uid'];
        }
        $sql = "SELECT * FROM" . tablename('xcommunity_xqnotice') . "WHERE $condition LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
        $list = pdo_fetchall($sql, $params);
        foreach ($list as $key => $val) {
            $user = pdo_get('users', array('uid' => $val['userid']), array('username'));
            $list[$key]['xqusername'] = $user['username'];
        }
        $total = pdo_fetchcolumn("SELECT COUNT(*) FROM" . tablename('xcommunity_xqnotice') . "WHERE $condition", $params);
        $pager = pagination($total, $pindex, $psize);


        include $this->template('web/plugin/shopping/notice/list');
    }


}
/**
 * 未知
 */
if ($op == 'delete') {
    $id = intval($_GPC['id']);
    if ($id) {
        $r = pdo_delete('xcommunity_xqnotice', array('id' => $id));
        if ($r) {
            $result = array(
                'status' => 1,
            );
            echo json_encode($result);
            exit();
        }

    }
}
/**
 * 商家的接收员
 */
if ($op == 'wechat') {
    $dpid = intval($_GPC['dpid']);
    /**
     * 商家接收员列表
     */
    if ($operation == 'list') {
        $condition = " t1.uniacid=:uniacid ";
        $parms[':uniacid'] = $_W['uniacid'];
        if ($user) {
            if ($user['type'] == 5) {
                $condition .= " and t2.id in({$user['store']})";
            }
//            if ($user['uuid']) {
//                //判断上级管理员是否是商家管理员
//                $suser = pdo_get("xcommunity_users", array('uid' => $user['uuid']), array());
//                $uid = $suser['type'] == 5 ? $user['uuid'] : $_W['uid'];
//                if ($user['uuid'] == 1) {
//                    $condition .= " and t2.uid = {$uid}";
//                } else {
//                    $condition .= " and (t2.uid = {$uid} or t2.uid = {$user['uid']})";
//                }
//            } else {
//                $condition .= " and t2.uid = {$_W['uid']}";
//            }
        }
        $pindex = max(1, intval($_GPC['page']));
        $psize = 20;
        $sql = "SELECT t1.*,t2.sjname FROM" . tablename('xcommunity_business_wechat') . "t1 left join" . tablename('xcommunity_dp') . "t2 on t2.id=t1.dpid WHERE $condition order by t1.id desc LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
        $list = pdo_fetchall($sql, $parms);
        $total = pdo_fetchcolumn("SELECT COUNT(*) FROM" . tablename('xcommunity_business_wechat') . "t1 left join" . tablename('xcommunity_dp') . "t2 on t2.id=t1.dpid WHERE $condition", $parms);
        $pager = pagination($total, $pindex, $psize);
        include $this->template('web/plugin/business/wechat/list');
    }
    /**
     * 商家接收员的添加修改
     */
    if ($operation == 'add') {
        $id = intval($_GPC['id']);
        if ($id) {
            $item = pdo_get('xcommunity_business_wechat', array('id' => $id), array());
        }
        if ($_W['isajax']) {
            $data = array(
                'uniacid'  => $_W['uniacid'],
                'enable'   => $_GPC['enable'],
                'type'     => $_GPC['type'],
                'dpid'     => $dpid,
                'realname' => trim($_GPC['realname']),
                'mobile'   => trim($_GPC['mobile']),
                'openid'   => trim($_GPC['openid'])
            );
            if (empty($item['id'])) {
                $data['uid'] = $_W['uid'];
                pdo_insert('xcommunity_business_wechat', $data);
            } else {
                pdo_update('xcommunity_business_wechat', $data, array('id' => $id));
            }
            echo json_encode(array('status' => 1));
            exit();
        }
        $condition = " uniacid=:uniacid and status=1";
        $params[':uniacid'] = $_W['uniacid'];
        if ($user) {
            if ($user['type'] == 5) {
                $condition .= " and id in({$user['store']}) ";
            }
        }

        $dps = pdo_fetchall("select * from" . tablename('xcommunity_dp') . "where $condition", $params);
        include $this->template('web/plugin/business/wechat/add');
    }
    /**
     * 商家接收员的删除
     */
    if ($operation == 'del') {
        $dp = pdo_get('xcommunity_business_wechat', array('id' => $id), array());
        if ($dp) {
            if (pdo_delete('xcommunity_business_wechat', array('id' => $id))) {
                util::permlog('店铺接收员-删除', '信息标题:' . $dp['realname']);
                $result = array(
                    'status' => 1,
                );
                echo json_encode($result);
                exit();
            }
        }
    }
    /**
     * 商家接收员的状态
     */
    if ($operation == 'verify') {
        $id = intval($_GPC['id']);
        $type = $_GPC['type'];
        $data = intval($_GPC['data']);
        if (in_array($type, array('enable'))) {
            $data = ($data == 0 ? '1' : '0');
            pdo_update("xcommunity_business_wechat", array($type => $data), array("id" => $id, "uniacid" => $_W['uniacid']));
            die(json_encode(array("result" => 1, "data" => $data)));
        }
    }
}
/**
 * 商家批量生成二维码
 */
if ($op == 'qrpl') {
    $list = pdo_getall('xcommunity_dp', array('uniacid' => $_W['uniacid']), array('id', 'sjname'));
    foreach ($list as $k => $v) {
        $url = $_W['siteroot'] . "app/index.php?i={$_W['uniacid']}&c=entry&id={$v['id']}&op=pay&do=business&m=" . $this->module['name'];//二维码内容
        $img = convertEncoding($v['sjname']) . ".png";
        $tmpdir = "../addons/" . $this->module['name'] . "/data/qrcode/business/" . $_W['uniacid'] . "/";
        $imgHtml = createQr($url, $img, $tmpdir);
    }
    itoast('更新成功', referer(), 'success');
}
/**
 * 商家的二维码下载
 */
if ($op == 'download') {
    $dir = MODULE_ROOT . '/data/qrcode/business/' . $_W['uniacid'] . '/';
    $data = list_dir($dir);
    $path = "./" . time() . ".zip"; //最终生成的文件名（含路径）
    download($data, $path);
}
/**
 * 商家的打印机设置
 */
if ($op == 'print') {
    /**
     * 打印机列表
     */
    if ($operation == 'list') {
        $condition = " t1.uniacid=:uniacid ";
        $parms[':uniacid'] = $_W['uniacid'];
        $condi = " t2.uniacid=:uniacid and t2.status=1";
        $pars[':uniacid'] = $_W['uniacid'];
        if ($user) {
            if ($user['type'] == 5) {
                $condition .= " AND t2.id in({$user['store']}) ";
                $condi .= " AND t2.id in({$user['store']}) ";
            }
        }
        $dps = pdo_fetchall("select * from" . tablename('xcommunity_dp') . "t2 where $condi", $pars);
        if ($_GPC['dpid']) {
            $condition .= " and t1.dpid=:dpid ";
            $parms[':dpid'] = $_GPC['dpid'];
        }
        $pindex = max(1, intval($_GPC['page']));
        $psize = 20;
        $sql = "SELECT t1.*,t2.sjname FROM" . tablename('xcommunity_business_print') . "t1 left join" . tablename('xcommunity_dp') . "t2 on t2.id=t1.dpid WHERE $condition order by t1.id desc LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
        $list = pdo_fetchall($sql, $parms);
        $total = pdo_fetchcolumn("SELECT COUNT(*) FROM" . tablename('xcommunity_business_print') . "t1 left join" . tablename('xcommunity_dp') . "t2 on t2.id=t1.dpid WHERE $condition", $parms);
        $pager = pagination($total, $pindex, $psize);
        include $this->template('web/plugin/business/print/list');
    }
    /**
     * 打印机添加修改
     */
    if ($operation == 'add') {
        $dpid = intval($_GPC['dpid']);
        $id = intval($_GPC['id']);
        if ($id) {
            $item = pdo_get('xcommunity_business_print', array('id' => $id), array());
        }
        if ($_W['isajax']) {
            $data = array(
                'uniacid'  => $_W['uniacid'],
                'type'     => $_GPC['type'],
                'dpid'     => $dpid,
                'api_key'  => $_GPC['api_key'],
                'deviceNo' => $_GPC['deviceNo']
            );
            if (empty($item['id'])) {
                $data['uid'] = $_W['uid'];
                pdo_insert('xcommunity_business_print', $data);
            } else {
                pdo_update('xcommunity_business_print', $data, array('id' => $id));
            }
            echo json_encode(array('status' => 1));
            exit();
        }
        $condition = " uniacid=:uniacid and status=1";
        $params[':uniacid'] = $_W['uniacid'];
        if ($user) {
            if ($user['type'] == 5) {
                $condition .= " and id in({$user['store']}) ";
            }
        }
        $dps = pdo_fetchall("select * from" . tablename('xcommunity_dp') . "where $condition", $params);
        include $this->template('web/plugin/business/print/add');
    }
    /**
     * 打印机删除
     */
    if ($operation == 'del') {
        $item = pdo_get('xcommunity_business_print', array('id' => $id), array());
        if ($item) {
            if (pdo_delete('xcommunity_business_print', array('id' => $id))) {
                util::permlog('店铺打印机-删除', '信息ID:' . $item['id']);
                $result = array(
                    'status' => 1,
                );
                echo json_encode($result);
                exit();
            }
        }
    }
}
/**
 * 商家的设置
 */
if ($op == 'set') {
    if (checksubmit('submit')) {
        foreach ($_GPC['set'] as $key => $val) {
            $sql = "select * from" . tablename('xcommunity_setting') . "where xqkey='{$key}' and uniacid={$_W['uniacid']} ";
            $item = pdo_fetch($sql);
            if ($key == 'p49') {
                $val = htmlspecialchars_decode($val);
            }
            $data = array(
                'xqkey'   => $key,
                'value'   => $val,
                'uniacid' => $_W['uniacid']
            );
            if ($item) {
                pdo_update('xcommunity_setting', $data, array('id' => $item['id'], 'uniacid' => $_W['uniacid']));
            } else {
                pdo_insert('xcommunity_setting', $data);
            }
        }
        itoast('操作成功', referer(), 'success', ture);
    }
    $set = pdo_getall('xcommunity_setting', array('uniacid' => $_W['uniacid']), array(), 'xqkey', array());
    include $this->template('web/plugin/business/set');
}
/**
 * 商家的支付接口配置
 */
if ($op == 'payapi') {
    $condition = 'uniacid=:uniacid and status=1';
    $params[':uniacid'] = $_W['uniacid'];
    if ($user) {
        if ($user['type'] == 5) {
            $condition .= " AND id in({$user['store']}) ";
        }
    }
    $sql = "SELECT * FROM" . tablename('xcommunity_dp') . "WHERE $condition order by id desc ";
    $dps = pdo_fetchall($sql, $params);
    if ($p == 'list') {

    }
    /**
     * 支付接口-支付宝
     */
    if ($p == 'alipay') {
        /**
         * 支付宝添加
         */
        if ($operation == 'add') {
            $id = intval($_GPC['id']);
            if ($id) {
                $item = pdo_get('xcommunity_alipayment', array('id' => $id), array());
            }
            if ($_W['isajax']) {
                $data = array(
                    'uniacid' => $_W['uniacid'],
                    'type'    => 3,
                    'account' => $_GPC['account'],
                    'partner' => $_GPC['partner'],
                    'secret'  => $_GPC['secret']
                );
                $data['userid'] = $_GPC['dpid'];
                if ($id) {
                    pdo_update('xcommunity_alipayment', $data, array('id' => $id));
                    util::permlog('', '修改支付宝ID:' . $id);
                } else {
                    if (pdo_get('xcommunity_alipayment', array('userid' => $_GPC['dpid'], 'type' => 3))) {
                        echo json_encode(array('content' => '该店铺已有支付账号'));
                        exit();
                    }
                    $data['uid'] = $_W['uid'];
                    pdo_insert('xcommunity_alipayment', $data);
                    $id = pdo_insertid();
                    util::permlog('', '添加支付宝ID:' . $id);
                }
                echo json_encode(array('status' => 1));
                exit();
            }
            include $this->template('web/plugin/business/payapi/alipay/add');
        }
        /**
         * 支付宝列表
         */
        if ($operation == 'list') {
            $pindex = max(1, intval($_GPC['page']));
            $psize = 20;
            $condition = '';
            $condition .= " t1.uniacid = {$_W['uniacid']} and t1.type=3";
            if ($user && $user[type] != 1) {
//                $condition .= " and t1.uid = {$_W['uid']}";
                if ($user['type'] == 5) {
                    $condition .= " and t2.id in({$user['store']}) ";
                }
            }
            $sql = "SELECT t1.*,t2.sjname FROM" . tablename('xcommunity_alipayment') . "t1 left join" . tablename('xcommunity_dp') . "t2 on t1.userid=t2.id WHERE $condition LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
            $list = pdo_fetchall($sql);
            $total = pdo_fetchcolumn("SELECT COUNT(*) FROM" . tablename('xcommunity_alipayment') . "t1 left join" . tablename('xcommunity_dp') . "t2 on t1.userid=t2.id WHERE $condition");
            $pager = pagination($total, $pindex, $psize);
            include $this->template('web/plugin/business/payapi/alipay/list');
        }
        /**
         * 支付宝删除
         */
        if ($operation == 'del') {
            $id = intval($_GPC['id']);
            if ($id) {
                if (pdo_delete('xcommunity_alipayment', array('id' => $id))) {
                    itoast('删除成功', referer(), 'success', ture);
                    exit();
                }
            }
        }
    }
    /**
     * 支付接口-微信
     */
    if ($p == 'wechat') {
        /**
         * 微信添加
         */
        if ($operation == 'add') {
            $id = intval($_GPC['id']);
            if ($id) {
                $item = pdo_get('xcommunity_wechat', array('id' => $id), array());
            }
            if ($_W['isajax']) {
                $data = array(
                    'uniacid'   => $_W['uniacid'],
                    'appid'     => $_GPC['appid'],
                    'appsecret' => $_GPC['appsecret'],
                    'mchid'     => $_GPC['mchid'],
                    'apikey'    => $_GPC['apikey'],
                    'type'      => 3,
                );

                $data['userid'] = $_GPC['dpid'];
                if ($id) {
                    pdo_update('xcommunity_wechat', $data, array('id' => $id));
                    util::permlog('', '修改借用支付ID:' . $id);
                } else {
                    if (pdo_get('xcommunity_wechat', array('userid' => $_GPC['dpid'], 'type' => 3))) {
                        echo json_encode(array('content' => '该店铺已有支付账号'));
                        exit();
                    }
                    $data['uid'] = $_W['uid'];
                    pdo_insert('xcommunity_wechat', $data);
                    $id = pdo_insertid();
                    util::permlog('', '添加借用支付ID:' . $id);
                }
                echo json_encode(array('status' => 1));
                exit();
            }
            include $this->template('web/plugin/business/payapi/wechat/add');
        }
        /**
         * 微信列表
         */
        if ($operation == 'list') {
            $pindex = max(1, intval($_GPC['page']));
            $psize = 20;
            $condition = '';
            $condition .= " t1.uniacid = {$_W['uniacid']} and t1.type=3";
            if ($user && $user[type] != 1) {
//                $condition .= " and t1.uid = {$_W['uid']}";
                if ($user['type'] == 5) {
                    $condition .= " and t2.id in({$user['store']}) ";
                }
            }
            $sql = "SELECT t1.*,t2.sjname FROM" . tablename('xcommunity_wechat') . "t1 left join" . tablename('xcommunity_dp') . "t2 on t1.userid=t2.id WHERE $condition LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
            $list = pdo_fetchall($sql, array(':uniacid' => $_W['uniacid']));
            $total = pdo_fetchcolumn("SELECT COUNT(*) FROM" . tablename('xcommunity_wechat') . "t1 left join" . tablename('xcommunity_dp') . "t2 on t1.userid=t2.id WHERE $condition");
            $pager = pagination($total, $pindex, $psize);
            include $this->template('web/plugin/business/payapi/wechat/list');
        }
        /**
         * 微信删除
         */
        if ($operation == 'del') {
            $id = intval($_GPC['id']);
            if ($id) {
                if (pdo_delete('xcommunity_wechat', array('id' => $id))) {
                    util::permlog('', '删除借用支付ID:' . $id);
                    itoast('删除成功', referer(), 'success', ture);
                    exit();
                }
            }
        }
    }
    /**
     * 支付接口-服务商
     */
    if ($p == 'sub') {
        /**
         * 服务商添加
         */
        if ($operation == 'add') {
            $id = intval($_GPC['id']);
            if ($id) {
                $item = pdo_get('xcommunity_service_data', array('id' => $id), array());
            }
            if ($_W['isajax']) {
                $data = array(
                    'uniacid'    => $_W['uniacid'],
                    'sub_id'     => $_GPC['sub_id'],
                    'apikey'     => $_GPC['apikey'],
                    'appid'      => $_GPC['appid'],
                    'appsecret'  => $_GPC['appsecret'],
                    'sub_mch_id' => $_GPC['sub_mch_id'],
                    'type'       => 3,
                );
                $data['userid'] = $_GPC['dpid'];
                if ($id) {
                    pdo_update('xcommunity_service_data', $data, array('id' => $id));
                    util::permlog('', '修改子商户ID:' . $id);
                } else {
                    if (pdo_get('xcommunity_service_data', array('userid' => $_GPC['dpid'], 'type' => 3))) {
                        echo json_encode(array('content' => '该店铺已有支付账号'));
                        exit();
                    }
                    $data['uid'] = $_W['uid'];
                    pdo_insert('xcommunity_service_data', $data);
                    $id = pdo_insertid();
                    util::permlog('', '添加子商户ID:' . $id);
                }
                echo json_encode(array('status' => 1));
                exit();
            }
            include $this->template('web/plugin/business/payapi/sub/add');
        }
        /**
         * 服务商列表
         */
        if ($operation == 'list') {
            $pindex = max(1, intval($_GPC['page']));
            $psize = 20;
            $condition = '';
            $condition .= " t1.uniacid = {$_W['uniacid']} and t1.type=3";
            if ($user && $user[type] != 1) {
//                $condition .= " and t1.uid = {$_W['uid']}";
                if ($user['type'] == 5) {
                    $condition .= " and t2.id in({$user['store']}) ";
                }
            }
            $sql = "SELECT t1.*,t2.sjname FROM" . tablename('xcommunity_service_data') . "t1 left join" . tablename('xcommunity_dp') . "t2 on t1.userid=t2.id WHERE $condition LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
            $list = pdo_fetchall($sql);
            $total = pdo_fetchcolumn("SELECT COUNT(*) FROM" . tablename('xcommunity_service_data') . "t1 left join" . tablename('xcommunity_dp') . "t2 on t1.userid=t2.id WHERE $condition");
            $pager = pagination($total, $pindex, $psize);
            include $this->template('web/plugin/business/payapi/sub/list');
        }
        /**
         * 服务商删除
         */
        if ($operation == 'del') {
            $id = intval($_GPC['id']);
            if ($id) {
                if (pdo_delete('xcommunity_service_data', array('id' => $id))) {
                    util::permlog('', '删除子商户ID:' . $id);
                    itoast('删除成功', referer(), 'success');
                    exit();
                }
            }
        }
    }
    /**
     * 支付接口-威富通
     */
    if ($p == 'swiftpass') {
        /**
         * 威富通添加
         */
        if ($operation == 'add') {
            $id = intval($_GPC['id']);
            if ($id) {
                $item = pdo_get('xcommunity_swiftpass', array('id' => $id), array());
            }
            if ($_W['isajax']) {
                $data = array(
                    'uniacid'     => $_W['uniacid'],
                    'type'        => 3,
                    'account'     => trim($_GPC['account']),
                    'secret'      => trim($_GPC['secret']),
                    'appid'       => trim($_GPC['appid']),
                    'appsecret'   => trim($_GPC['appsecret']),
                    'private_key' => trim($_GPC['private_key']),
                    'banktype'    => intval($_GPC['banktype'])
                );
                $data['userid'] = $_GPC['dpid'];
                if ($id) {
                    pdo_update('xcommunity_swiftpass', $data, array('id' => $id));
                    util::permlog('', '修改威富通微信支付ID:' . $id);
                } else {
                    if (pdo_get('xcommunity_swiftpass', array('userid' => $_GPC['dpid'], 'type' => 3))) {
                        echo json_encode(array('content' => '该店铺已有支付账号'));
                        exit();
                    }
                    $data['uid'] = $_W['uid'];
                    pdo_insert('xcommunity_swiftpass', $data);
                    $id = pdo_insertid();
                    util::permlog('', '添加威富通微信支付ID:' . $id);
                }
                echo json_encode(array('status' => 1));
                exit();
            }
            include $this->template('web/plugin/business/payapi/swiftpass/add');
        }
        /**
         * 威富通列表
         */
        if ($operation == 'list') {
            $pindex = max(1, intval($_GPC['page']));
            $psize = 20;
            $condition = '';
            $condition .= " t1.uniacid = {$_W['uniacid']} and t1.type=3";
            if ($user && $user[type] != 1) {
//                $condition .= " and t1.uid = {$_W['uid']}";
                if ($user['type'] == 5) {
                    $condition .= " and t2.id in({$user['store']}) ";
                }
            }
            $sql = "SELECT t1.*,t2.sjname FROM" . tablename('xcommunity_swiftpass') . "t1 left join" . tablename('xcommunity_dp') . "t2 on t1.userid=t2.id WHERE $condition LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
            $list = pdo_fetchall($sql);
            $total = pdo_fetchcolumn("SELECT COUNT(*) FROM" . tablename('xcommunity_swiftpass') . "t1 left join" . tablename('xcommunity_dp') . "t2 on t1.userid=t2.id WHERE $condition");
            $pager = pagination($total, $pindex, $psize);

            include $this->template('web/plugin/business/payapi/swiftpass/list');
        }
        /**
         * 威富通删除
         */
        if ($operation == 'del') {
            $id = intval($_GPC['id']);
            if ($id) {
                if (pdo_delete('xcommunity_swiftpass', array('id' => $id))) {
                    itoast('删除成功', referer(), 'success', ture);
                    exit();
                }
            }
        }
    }
    /**
     * 支付接口-华商云付
     */
    if ($p == 'hsyunfu') {
        /**
         * 华商云付添加
         */
        if ($operation == 'add') {
            $id = intval($_GPC['id']);
            if ($id) {
                $item = pdo_get('xcommunity_hsyunfu', array('id' => $id), array());
            }
            if ($_W['isajax']) {
                $data = array(
                    'uniacid'   => $_W['uniacid'],
                    'type'      => 3,
                    'account'   => trim($_GPC['account']),
                    'secret'    => trim($_GPC['secret']),
                    'appid'     => trim($_GPC['appid']),
                    'appsecret' => trim($_GPC['appsecret'])
                );
                $data['userid'] = $_GPC['dpid'];
                if ($id) {
                    pdo_update('xcommunity_hsyunfu', $data, array('id' => $id));
                    util::permlog('', '修改华商云付微信支付ID:' . $id);
                } else {
                    if (pdo_get('xcommunity_hsyunfu', array('userid' => $_GPC['dpid'], 'type' => 3))) {
                        echo json_encode(array('content' => '该店铺已有支付账号'));
                        exit();
                    }
                    $data['uid'] = $_W['uid'];
                    pdo_insert('xcommunity_hsyunfu', $data);
                    $id = pdo_insertid();
                    util::permlog('', '添加华商云付微信支付ID:' . $id);
                }
                echo json_encode(array('status' => 1));
                exit();
            }
            include $this->template('web/plugin/business/payapi/hsyunfu/add');
        }
        /**
         * 华商云付列表
         */
        if ($operation == 'list') {
            $pindex = max(1, intval($_GPC['page']));
            $psize = 20;
            $condition = '';
            $condition .= " t1.uniacid = {$_W['uniacid']} and t1.type=3";
            if ($user && $user[type] != 1) {
//                $condition .= " and t1.uid = {$_W['uid']}";
                if ($user['type'] == 5) {
                    $condition .= " and t2.id in({$user['store']}) ";
                }
            }
            $sql = "SELECT t1.*,t2.sjname FROM" . tablename('xcommunity_hsyunfu') . "t1 left join" . tablename('xcommunity_dp') . "t2 on t1.userid=t2.id WHERE $condition LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
            $list = pdo_fetchall($sql);
            $total = pdo_fetchcolumn("SELECT COUNT(*) FROM" . tablename('xcommunity_hsyunfu') . "t1 left join" . tablename('xcommunity_dp') . "t2 on t1.userid=t2.id WHERE $condition");
            $pager = pagination($total, $pindex, $psize);

            include $this->template('web/plugin/business/payapi/hsyunfu/list');
        }
        /**
         * 华商云付删除
         */
        if ($operation == 'del') {
            $id = intval($_GPC['id']);
            if ($id) {
                if (pdo_delete('xcommunity_hsyunfu', array('id' => $id))) {
                    itoast('删除成功', referer(), 'success', ture);
                    exit();
                }
            }
        }
    }
    /**
     * 支付接口-银联
     */
    if ($p == 'chinaums') {
        /**
         * 银联添加
         */
        if ($operation == 'add') {
            $id = intval($_GPC['id']);
            if ($id) {
                $item = pdo_get('xcommunity_chinaums', array('id' => $id), array());
            }
            if ($_W['isajax']) {
                $data = array(
                    'uniacid'  => $_W['uniacid'],
                    'type'     => 3,
                    'mid'      => $_GPC['mid'],
                    'tid'      => $_GPC['tid'],
                    'instmid'  => $_GPC['instmid'],
                    'msgsrc'   => $_GPC['msgsrc'],
                    'msgsrcid' => $_GPC['msgsrcid'],
                    'secret'   => $_GPC['secret']
                );
                $data['userid'] = $_GPC['dpid'];
                if ($id) {
                    pdo_update('xcommunity_chinaums', $data, array('id' => $id));
                    util::permlog('', '修改银联ID:' . $id);
                } else {
                    if (pdo_get('xcommunity_chinaums', array('userid' => $_GPC['dpid'], 'type' => 3))) {
                        echo json_encode(array('content' => '该店铺已有支付账号'));
                        exit();
                    }
                    $data['uid'] = $_W['uid'];
                    pdo_insert('xcommunity_chinaums', $data);
                    $id = pdo_insertid();
                    util::permlog('', '添加银联ID:' . $id);
                }
                echo json_encode(array('status' => 1));
                exit();
            }
            include $this->template('web/plugin/business/payapi/chinaums/add');
        }
        /**
         * 银联列表
         */
        if ($operation == 'list') {
            $pindex = max(1, intval($_GPC['page']));
            $psize = 20;
            $condition = '';
            $condition .= " t1.uniacid = {$_W['uniacid']} and t1.type=3";
            if ($user && $user[type] != 1) {
//                $condition .= " and t1.uid = {$_W['uid']}";
                if ($user['type'] == 5) {
                    $condition .= " and t2.id in({$user['store']}) ";
                }
            }
            $sql = "SELECT t1.*,t2.sjname FROM" . tablename('xcommunity_chinaums') . "t1 left join" . tablename('xcommunity_dp') . "t2 on t1.userid=t2.id WHERE $condition LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
            $list = pdo_fetchall($sql);
            $total = pdo_fetchcolumn("SELECT COUNT(*) FROM" . tablename('xcommunity_chinaums') . "t1 left join" . tablename('xcommunity_dp') . "t2 on t1.userid=t2.id WHERE $condition");
            $pager = pagination($total, $pindex, $psize);
            include $this->template('web/plugin/business/payapi/chinaums/list');
        }
        /**
         * 银联删除
         */
        if ($operation == 'del') {
            $id = intval($_GPC['id']);
            if ($id) {
                if (pdo_delete('xcommunity_chinaums', array('id' => $id))) {
                    itoast('删除成功', referer(), 'success', ture);
                    exit();
                }
            }
        }
    }
}
/**
 * 二维码的列表
 */
if ($op == 'qrlist') {
    $tmpdir = MODULE_ROOT . '/data/qrcode/business/' . $_W['uniacid'] . '/';
    $result = list_dir($tmpdir);
    $list = array();
    foreach ($result as $k => $v) {

        $list[$k]['url'] = $v;
        $title = str_replace(MODULE_ROOT . '/data/qrcode/business/' . $_W['uniacid'] . '/', '', $v);
        if (stristr(PHP_OS, 'WIN')) {
            $list[$k]['title'] = iconv("GB2312", "UTF-8", $title);
            $list[$k]['url'] = iconv("GB2312", "UTF-8", $v);
        } else {
            $list[$k]['title'] = $title;
            $list[$k]['url'] = $v;
        }

    }
    include $this->template('web/plugin/business/qrlist');
}
/**
 * 删除批量生成二维码
 */
if ($op == 'qrdel') {
    $title = trim($_GPC['title']);
    load()->func('file');
    $tmpdir = MODULE_ROOT . '/data/qrcode/business/' . $_W['uniacid'] . '/' . $title;
    file_delete($tmpdir);
    itoast('删除成功', referer(), 'success');
}
/**
 * 店铺独立设置
 */
if ($op == 'setting') {
    $dpid = intval($_GPC['dpid']);
    if ($dpid) {
        $dp = pdo_get("xcommunity_dp", array('id' => $dpid), array('setting'));
        $setting = unserialize($dp['setting']);
    }
    if (checksubmit('submit')) {
        $setting = $_GPC['setting'];
        pdo_update('xcommunity_dp', array('setting' => serialize($setting)), array('id' => $dpid));
        itoast('操作成功', referer(), 'success', ture);
    }

    include $this->template('web/plugin/business/dp/setting');
}
/**
 * 商家线下的订单
 */
if ($op == 'lineorder') {
    /**
     * 订单的列表
     */
    if ($operation == 'list') {
        //删除
        if ($_W['ispost']) {
            $ids = $_GPC['ids'];
            if (!empty($ids)) {
                foreach ($ids as $key => $id) {
                    pdo_delete('xcommunity_order', array('id' => $id));
                }
                util::permlog('', '批量删除商家订单');
                itoast('删除成功', referer(), 'success', true);
            }
        }
        // 查询店铺
        $condi = array();
        $condi['uniacid'] = $_W['uniacid'];
        $condi['status'] = 1;
        if ($user) {
            if ($user['type'] == 5) {
                $condi['id'] = explode(',', $user['store']);
            }
        }
        $dps = pdo_getall('xcommunity_dp', $condi, array('sjname', 'id'), 'id');
        // 查询订单
        $pindex = max(1, intval($_GPC['page']));
        $psize = 20;
        $condition = array();
        $condition['uniacid'] = $_W['uniacid'];
        $condition['type'] = 'xxbusiness';
        if ($_GPC['dpid']) {
            $condition['dpid'] = $_GPC['dpid'];
        } else {
            if ($user && $user['type'] != 1) {
                if ($user['type'] == 5) {
                    $condition['dpid'] = explode(',', $user['store']);
                }
            }
        }
        if (empty($starttime) || empty($endtime)) {
            $starttime = strtotime('-1 month');
            $endtime = time();
        }
        if (!empty($_GPC['time'])) {
            $starttime = strtotime($_GPC['time']['start']);
            $endtime = strtotime($_GPC['time']['end']) + 86399;
            $condition['createtime >='] = $starttime;
            $condition['createtime <='] = $endtime;
        }
        $status = $_GPC['status'];
        if ($status != '') {
            $condition['status'] = intval($status);
        }
        $keyword = trim($_GPC['keyword']);
        if ($keyword) {
            $condition['ordersn'] = $keyword;
        }
        if ($_GPC['export'] == 1) {
            $xqlist = pdo_getall('xcommunity_order', $condition, array());
            foreach ($xqlist as $key => $value) {
                $xqlist[$key]['cctime'] = date('Y-m-d H:i', $value['createtime']);
                $xqlist[$key]['s'] = empty($value['status']) ? '未付' : '已付';
                if ($value['paytype'] == 1) {
                    $xqlist[$key]['paytype'] = '余额';
                } elseif ($value['paytype'] == 2) {
                    $xqlist[$key]['paytype'] = '微信支付';
                } elseif ($value['paytype'] == 4) {
                    $xqlist[$key]['paytype'] = '支付宝';
                } else {
                    $xqlist[$key]['paytype'] = '';
                }
                $xqlist[$key]['sjanme'] = $dps[$value['dpid']]['sjname'];
            }
            model_execl::export($xqlist, array(
                "title"   => "商家线下订单数据-" . date('Y-m-d-H-i', time()),
                "columns" => array(
                    array(
                        'title' => '订单号',
                        'field' => 'ordersn',
                        'width' => 30
                    ),
                    array(
                        'title' => '商家名称',
                        'field' => 'sjanme',
                        'width' => 12
                    ),
                    array(
                        'title' => '应付金额',
                        'field' => 'total',
                        'width' => 12
                    ),
                    array(
                        'title' => '实付金额',
                        'field' => 'price',
                        'width' => 12
                    ),
                    array(
                        'title' => '减免金额',
                        'field' => 'randprice',
                        'width' => 12
                    ),
                    array(
                        'title' => '抵扣积分数量',
                        'field' => 'credit',
                        'width' => 12
                    ),
                    array(
                        'title' => '积分抵扣费用',
                        'field' => 'offsetprice',
                        'width' => 12
                    ),
                    array(
                        'title' => '支付方式',
                        'field' => 'paytype',
                        'width' => 12
                    ),
                    array(
                        'title' => '状态',
                        'field' => 's',
                        'width' => 12
                    ),
                    array(
                        'title' => '支付时间',
                        'field' => 'cctime',
                        'width' => 15
                    ),
                )
            ));
        }

        $pindex = max(1, intval($_GPC['page']));
        $psize = 20;
        $condition = array();
        $condition['uniacid'] = $_W['uniacid'];
        $condition['type'] = 'xxbusiness';
        if ($_GPC['dpid']) {
            $condition['dpid'] = $_GPC['dpid'];
        } else {
            if ($user && $user['type'] != 1) {
                if ($user['type'] == 5) {
                    $condition['dpid'] = explode(',', $user['store']);
                }
            }
        }
        if (empty($starttime) || empty($endtime)) {
            $starttime = strtotime('-1 month');
            $endtime = time();
        }
        if (!empty($_GPC['time'])) {
            $starttime = strtotime($_GPC['time']['start']);
            $endtime = strtotime($_GPC['time']['end']) + 86399;
            $condition['createtime >='] = $starttime;
            $condition['createtime <='] = $endtime;
        }
        $status = $_GPC['status'];
        if ($status != '') {
            $condition['status'] = intval($status);
        }
        $keyword = trim($_GPC['keyword']);
        if ($keyword) {
            $condition['ordersn'] = $keyword;
        }
        $orders = pdo_getslice('xcommunity_order', $condition, array($pindex, $psize), $total, '', '', array('status desc', 'createtime desc'));
        $list = array();
        foreach ($orders as $k => $v) {
            $list[] = array(
                'id'          => $v['id'],
                'ordersn'     => $v['ordersn'],
                'sjname'      => $dps[$v['dpid']]['sjname'],
                'total'       => $v['total'],
                'price'       => $v['price'],
                'randprice'   => $v['randprice'],
                'credit'      => $v['credit'],
                'offsetprice' => $v['offsetprice'],
                'paytype'     => $v['paytype'],
                'status'      => $v['status'],
                'createtime'  => $v['createtime']
            );
        }
        $pager = pagination($total, $pindex, $psize);

        include $this->template('web/plugin/business/order/line');
    }
    /**
     * 订单的删除
     */
    if ($operation == 'delete') {
        $id = intval($_GPC['id']);
        if ($id) {
            $r = pdo_delete('xcommunity_order', array('id' => $id));
            util::permlog('商家订单-删除', '删除订单ID:' . $id);
            if ($r) {
                itoast('删除成功', referer(), 'success', true);
            }
        }
    }

}
/**
 * 商家的分成设置
 */
if ($op == 'commission') {
    $dpid = intval($_GPC['dpid']);
    if (checksubmit('submit')) {
        $setting = $_GPC['commission'] . ',' . $_GPC['xqcommission'];
        pdo_update('xcommunity_dp', array('commission' => $setting), array('id' => $dpid));
        itoast('操作成功', referer(), 'success', ture);
    }
    $dp = pdo_get("xcommunity_dp", array('id' => $dpid), array('commission'));
    $setting = explode(',', $dp['commission']);
    include $this->template('web/plugin/business/dp/commission');
}
/**
 *商家积分设置
 */
if ($op == 'dpset') {

    include $this->template('web/plugin/business/dpset');
}