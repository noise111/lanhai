<?php
/**
 * 小区秘书模块
 *
 * [蓝牛科技] Copyright (c) 2013 xqms.cn
 */
/**
 * 后台超市管理
 */
global $_W, $_GPC;
$ops = array('goods', 'setgoodsproperty', 'order', 'verify', 'set', 'notice', 'delete', 'print', 'address', 'bind', 'sets', 'shop', 'payapi', 'wechat', 'commission');
$op = !empty($_GPC['op']) ? $_GPC['op'] : 'goods';
if (!in_array($op, $ops)) {
    message('该方法不存在(op:' . $op . ')');
}
$operation = !empty($_GPC['operation']) ? $_GPC['operation'] : 'list';
$p = !empty($_GPC['p']) ? $_GPC['p'] : 'list';
// 操作员
$user = util::xquser($_W['uid']);
if ($user && $user['menu_ops']) {
    $menu_opss = explode(',', $user['menu_ops']);
}
$regions = model_region::region_fetall();
$id = intval($_GPC['id']);
/**
 * 超市的商品
 */
if ($op == 'goods') {
    //商品管理
    $category = util::fetchall_category(5);
    $parent = array();
    $children = array();

    if (!empty($category)) {
        $children = array();
        foreach ($category as $cid => $cate) {
            if (!empty($cate['parentid'])) {
                $children[$cate['parentid']][] = $cate;
            }
            else {
                $parent[$cate['id']] = $cate;
            }
        }
    }
    $condition = array();
    $condition['uniacid'] = $_W['uniacid'];
    $condition['type'] = 1;
    $condition['status'] = 1;
    if ($user) {
        if ($user['type'] == 4) {
            $condition['shopid'] = explode(',', $user['store']);
        }
    }
    $shops = pdo_getall('xcommunity_shop', $condition, array(), 'id');
    /**
     * 商品的添加
     */
    if ($operation == 'add') {
        $regionids = '[]';
        if (!empty($id)) {

            $item = pdo_fetch("SELECT * FROM " . tablename('xcommunity_goods') . " WHERE id = :id", array(':id' => $id));
            $pcate = $item['pcate'];
            $ccate = $item['child'];

            if (empty($item)) {
                itoast('抱歉，商品不存在或是已经删除！', '', 'error', ture);
            }
            $piclist = array();
            if ($item['thumb_url']) {
                $piclist = explode(',', $item['thumb_url']);
            }
            $imglist = array();
            if ($item['thumbs']) {
                $imglist = explode(',', $item['thumbs']);
            }

//            $starttime = !empty($item['starttime']) ? date('Y-m-d H:i', $item['starttime']) : date('Y-m-d', timestamp);
//            $endtime = !empty($item['endtime']) ? date('Y-m-d H:i', $item['endtime']) : date('Y-m-d', timestamp);
            $regs = pdo_getall('xcommunity_goods_region', array('gid' => $id), array('regionid'));
            $regionid = array();
            foreach ($regs as $key => $val) {
                $regionid[] = $val['regionid'];
            }
            $regionids = json_encode($regionid);

        }
        if (empty($category)) {
            itoast('抱歉，请您先添加商品分类！', $this->createWebUrl('category', array('op' => 'list', 'type' => 5)), 'error', ture);
        }
        if ($_W['isajax']) {
            if (empty($_GPC['goodsname'])) {
                echo json_encode(array('content' => '请输入商品名称！'));
                exit();
            }
            if (empty($_GPC['thumbs'])) {
                $_GPC['thumbs'] = array();
            }
            $birth = $_GPC['birth'];
            $allregion = intval($_GPC['allregion']);
            if ($allregion == 1) {

            }
            else {
                if (empty($birth['province'])) {
                    echo json_encode(array('content' => '必须选择省市区和小区！'));
                    exit();
                }
            }

            $data = array(
                'uniacid'      => intval($_W['uniacid']),
                'displayorder' => intval($_GPC['displayorder']),
                'title'        => $_GPC['goodsname'],
                'pcate'        => intval($_GPC['category']['parentid']),
                'child'        => intval($_GPC['category']['childid']),
                'thumb'        => $_GPC['thumb'],
                'content'      => htmlspecialchars_decode($_GPC['content']),
                'unit'         => $_GPC['unit'],
                'createtime'   => TIMESTAMP,
                'total'        => intval($_GPC['total']),
                'marketprice'  => $_GPC['marketprice'],
                'productprice' => $_GPC['productprice'],
                'credit'       => intval($_GPC['credit']),
                'status'       => intval($_GPC['status']),
                'type'         => 1,
                'province'     => $birth['province'],
                'city'         => $birth['city'],
                'dist'         => $birth['district'],
                'recommand'    => intval($_GPC['recommand']),
                'wlinks'       => $_GPC['wlinks'],
                'shopid'       => intval($_GPC['shopid']),
                'allregion'    => $allregion,
                'isshow'       => 0,
            );
            if (@is_array($_GPC['images'])) {
                $data['thumb_url'] = implode(',', $_GPC['images']);
            }
            if (@is_array($_GPC['thumbs'])) {
                $data['thumbs'] = implode(',', $_GPC['thumbs']);
            }
//            print_r($data);exit();
            if (empty($id)) {
                if ($user['uuid']) {
                    //判断上级管理员是否是超市
                    $suser = pdo_get("xcommunity_users", array('uid' => $user['uuid']), array());
                    $data['uid'] = $suser['type'] == 4 || $suser['type'] == 1 ? $suser['uid'] : $_W['uid'];
                }
                else {
                    $data['uid'] = $_W['uid'];
                }
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
            if ($allregion == 1) {
                $regions = model_region::region_fetall();
                foreach ($regions as $k => $v) {
                    $dat = array(
                        'gid'      => $id,
                        'regionid' => $v['id'],
                    );
                    pdo_insert('xcommunity_goods_region', $dat);
                }
            }
            else {
                $regionids = explode(',', $_GPC['regionids']);
                foreach ($regionids as $key => $value) {
                    $dat = array(
                        'gid'      => $id,
                        'regionid' => $value,
                    );
                    pdo_insert('xcommunity_goods_region', $dat);
                }
            }
            echo json_encode(array('status' => 1));
            exit();
        }
        load()->func('tpl');
        $options = array();
        $options['dest_dir'] = $_W['uid'] == 1 ? '' : MODULE_NAME . '/' . $_W['uid'];
        include $this->template('web/plugin/shopping/goods/add');
    }
    /**
     * 商品的列表
     */
    if ($operation == 'list') {
        if (checksubmit('delete')) {
            $ids = $_GPC['ids'];
            if (!empty($ids)) {
                foreach ($ids as $key => $id) {
//                    pdo_delete('xcommunity_goods', array('id' => $id));
                    pdo_update('xcommunity_goods', array('isshow' => 1), array('id' => $id));
                }
                util::permlog('', '批量删除商品信息');
                itoast('删除成功', referer(), 'success', ture);
            }
        }
        if (checksubmit('plsj')) {
            $ids = $_GPC['ids'];
            if (!empty($ids)) {
                foreach ($ids as $key => $id) {
                    pdo_update('xcommunity_goods', array('status' => 1), array('id' => $id));
                }
                util::permlog('', '批量商品上架');
                itoast('上架成功', referer(), 'success', ture);
            }
        }
        if (checksubmit('plxj')) {
            $ids = $_GPC['ids'];
            if (!empty($ids)) {
                foreach ($ids as $key => $id) {
                    pdo_update('xcommunity_goods', array('status' => 0), array('id' => $id));
                }
                util::permlog('', '批量商品下架');
                itoast('下架成功', referer(), 'success', ture);
            }
        }
        $pindex = max(1, intval($_GPC['page']));
        $psize = 20;
        $condition = array();
        $condition['uniacid'] = $_W['uniacid'];
        $condition['type'] = 1;
        $condition['isshow'] = 0;
        if (!empty($_GPC['keyword'])) {
            $condition['title like'] .= "%{$_GPC['keyword']}%";
        }
        $pcate = intval($_GPC['category']['parentid']);
        $ccate = intval($_GPC['category']['childid']);
        if (!empty($pcate)) {
            $condition['pcate'] = $pcate;
        }
        if (!empty($ccate)) {
            $condition['child'] = $ccate;
        }
        if(isset($_GPC['status']) && $_GPC['status'] != 3){
            $status = intval($_GPC['status']);
            if($status ==2){
                $status = 0;
            }
            $condition['status'] = $status;
        }
        if ($user) {
            if ($user['type'] == 4) {
                $condition['shopid'] = explode(',', $user['store']);
            }
        }
        $goods = pdo_getslice('xcommunity_goods', $condition, array($pindex, $psize), $total, '', '', array('id desc'));
        $list = array();
        foreach ($goods as $k => $v) {
            $list[] = array(
                'id'           => $v['id'],
                'pcate'        => $v['pcate'],
                'child'        => $v['child'],
                'title'        => $v['title'],
                'stitle'       => $shops[$v['shopid']]['title'],
                'total'        => $v['total'],
                'marketprice'  => $v['marketprice'],
                'productprice' => $v['productprice'],
                'status'       => $v['status']
            );
        }
        $pager = pagination($total, $pindex, $psize);

        include $this->template('web/plugin/shopping/goods/list');
    }
    /**
     * 商品的删除
     */
    if ($operation == 'delete') {

        $row = pdo_fetch("SELECT id, thumb,thumb_url,title FROM " . tablename('xcommunity_goods') . " WHERE id = :id", array(':id' => $id));
        if (empty($row)) {
            itoast('抱歉，商品不存在或是已经被删除！', ture);
        }
        //删除商品图
        load()->func('file');
        if (!empty($row['thumb'])) {

            file_delete($row['thumb']);
        }
        //删除多图
        $piclist1 = unserialize($row['thumb_url']);
        if (is_array($piclist1)) {
            foreach ($piclist1 as $p) {
                file_delete($p);
            }
        }

//        pdo_delete('xcommunity_goods', array('id' => $id));
        pdo_update('xcommunity_goods', array('isshow' => 1), array('id' => $id));
        util::permlog('超市商品-删除', '信息标题:' . $row['title']);
        itoast('删除成功！', referer(), 'success', ture);
    }
    /**
     *
     */
    if ($operation == 'set') {
        //个人设置
        if ($user) {
            $users = pdo_fetch("SELECT * FROM" . tablename('xcommunity_users') . "WHERE uid=:uid", array(':uid' => $_W['uid']));
        }

        include $this->template('web/plugin/shopping/goods/set');
    }
    /**
     * 商家的提现
     */
    if ($operation == 'cash') {
        //商家提现
        if ($user) {
            $users = pdo_fetch("SELECT * FROM" . tablename('xcommunity_users') . "WHERE uid=:uid", array(':uid' => $_W['uid']));
        }
        if (checksubmit('submit')) {
            if ($_GPC['cash'] > $users['balance']) {
                itoast('余额不足，无法提现', referer(), 'error', ture);
            }
            $data = array(
                'uniacid'    => $_W['uniacid'],
                'ordersn'    => date('YmdHi') . random(10, 1),
                'price'      => $_GPC['cash'],
                'type'       => 'scash',
                'createtime' => TIMESTAMP,
                'uid'        => $_W['uid'],
            );
            $r = pdo_insert('xcommunity_order', $data);
            if ($r) {
                pdo_update('xcommunity_users', array('balance' => $users['balance'] - number_format(floatval($_GPC['cash']), 2)), array('id' => $users['id']));
                itoast('提交成功', $this->createWebUrl('shopping', array('op' => 'cash')), 'success', ture);
            }

        }

        include $this->template('web/plugin/shopping/goods/cash');
    }
}
/**
 * 改变商品状态
 */
if ($op == 'setgoodsproperty') {
    $type = $_GPC['type'];
    $data = intval($_GPC['data']);
    if (in_array($type, array('isrecommand', 'status'))) {
        $data = ($data == 1 ? '0' : '1');
        pdo_update("xcommunity_goods", array($type => $data), array("id" => $id, "uniacid" => $_W['uniacid']));
        die(json_encode(array("result" => 1, "data" => $data)));
    }
    die(json_encode(array("result" => 0)));
}
/**
 * 超市的订单
 */
if ($op == 'order') {
    load()->func('tpl');
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
                itoast('删除成功', referer(), 'success', ture);
            }
        }
        $pindex = max(1, intval($_GPC['page']));
        $psize = 20;
        $codeStatus = set('p163') ? 1 : 0;
        $condition = " t1.uniacid = :uniacid and t1.type='shopping' and t1.enable=1";
        $paras[':uniacid'] = $_W['uniacid'];
        if ($user) {
            if ($user['type'] == 4) {
                $condition .= " and t6.shopid in({$user['store']}) ";
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
            $paras[':starttime'] = $starttime;
            $paras[':endtime'] = $endtime;
        }
        if (!empty($_GPC['paytype'])) {
//            if ($_GPC['paytype'] == 5) {
//                $condition .= " AND t1.paytype = 0";
//            }
//            else {
//                $condition .= " AND t1.paytype = '{$_GPC['paytype']}'";
//            }
            if ($_GPC['paytype'] == -1) {
                $condition .= " AND t1.paytype = 0";
            } else {
                $condition .= " AND t1.paytype = '{$_GPC['paytype']}'";
            }
        }
        if (!empty($_GPC['keyword'])) {
            $condition .= " AND (t1.ordersn LIKE '%{$_GPC['keyword']}%' or t2.realname LIKE '%{$_GPC['keyword']}%' or t2.mobile LIKE '%{$_GPC['keyword']}%')";
        }
        $status = $_GPC['status'];
        if ($status != '') {
            $condition .= " AND t1.status = '" . intval($status) . "'";
        }
        if ($_GPC['export'] == 1) {
            $sql1 = "select t1.ordersn,t3.realname,t3.mobile,t1.price,t1.createtime,t1.id,t1.paytype,t1.status,t2.address,t6.title from" . tablename('xcommunity_order') . "t1 left join" . tablename('xcommunity_member_address') . "t2 on t1.addressid=t2.id left join" . tablename('mc_members') . "t3 on t3.uid= t1.uid left join" . tablename('xcommunity_order_goods') . "t4 on t4.orderid= t1.id left join" . tablename('xcommunity_goods') . "t5 on t5.id=t4.goodsid left join" . tablename('xcommunity_region') . "t6 on t6.id=t1.regionid where $condition order by t1.createtime desc";
            $xqlist = pdo_fetchall($sql1, $paras);
            $paytype = array(
                '0' => array('css' => 'default', 'name' => '未支付'),
                '1' => array('css' => 'danger', 'name' => '余额支付'),
                '2' => array('css' => 'info', 'name' => '微信支付'),
                '3' => array('css' => 'warning', 'name' => '货到付款'),
                '4' => array('css' => 'info', 'name' => '支付宝支付'),
                '5' => array('css' => 'default', 'name' => '未支付'),
                '6' => array('css' => 'default', 'name' => '积分抵扣')
            );
            $orderstatus = array(
                '-1' => array('css' => 'default', 'name' => '已关闭'),
                '0'  => array('css' => 'danger', 'name' => '待付款'),
                '1'  => array('css' => 'info', 'name' => '待发货'),
                '2'  => array('css' => 'warning', 'name' => '待收货'),
                '3'  => array('css' => 'success', 'name' => '已完成')
            );
            foreach ($xqlist as $k => $val) {
                $xqlist[$k]['addr'] = $val['title'] . $val['address'];
                $xqlist[$k]['cctime'] = date('Y-m-d H:i', $val['createtime']);
                $s = $val['status'];
                $xqlist[$k]['statuscss'] = $orderstatus[$val['status']]['css'];
                $xqlist[$k]['status'] = $orderstatus[$val['status']]['name'];
                // $value['statuscss'] = $orderstatus[$value['status']]['css'];
                // $value['status'] = $orderstatus[$value['status']]['name'];
                if ($s < 1) {
                    $xqlist[$k]['css'] = $paytype[$s]['css'];
                    $xqlist[$k]['paytype'] = $paytype[$s]['name'];
                    // $value['css'] = $paytype[$s]['css'];
                    // $value['paytype'] = $paytype[$s]['name'];
                    continue;
                }
                $xqlist[$k]['css'] = $paytype[$val['paytype']]['css'];
                // $value['css'] = $paytype[$value['paytype']]['css'];
//                if ($val['paytype'] == 2) {
//                    if (empty($val['transid'])) {
//                        $xqlist[$k]['paytype'] = '支付宝支付';
//                        // $value['paytype'] = '支付宝支付';
//                    }
//                    else {
//                        $xqlist[$k]['paytype'] = '微信支付';
//                        // $value['paytype'] = '微信支付';
//                    }
//                }
//                elseif ($val['paytype'] == 0) {
//                    if ($val['status'] == 1) {
//                        $xqlist[$k]['paytype'] = $paytype[6]['name'];
//                    } else {
//                        $t = 5;
//                        $xqlist[$k]['paytype'] = $paytype[$t]['name'];
//                    }
//                }
//                else {
//                    $xqlist[$k]['paytype'] = $paytype[$val['paytype']]['name'];
//                    // $value['paytype'] = $paytype[$value['paytype']]['name'];
//                }
                $xqlist[$k]['ordersn'] = chunk_split($val['ordersn']);


            }

            model_execl::export($xqlist, array(
                "title"   => "超市订单数据-" . $_GPC['time']['start'] . '-' . $_GPC['time']['end'],
                "columns" => array(
                    array(
                        'title' => '订单号',
                        'field' => 'ordersn',
                        'width' => 24
                    ),
                    array(
                        'title' => '收货姓名(或自提人)',
                        'field' => 'realname',
                        'width' => 12
                    ),
                    array(
                        'title' => '收货地址',
                        'field' => 'addr',
                        'width' => 20
                    ),
                    array(
                        'title' => '手机号',
                        'field' => 'mobile',
                        'width' => 12
                    ),
                    array(
                        'title' => '支付方式',
                        'field' => 'paytype',
                        'width' => 12
                    ),
                    array(
                        'title' => '总价',
                        'field' => 'price',
                        'width' => 12
                    ),
                    array(
                        'title' => '状态',
                        'field' => 'status',
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
        if ($user) {
            $sql = "select distinct t1.id,t1.createtime,t2.realname as address_realname,t2.mobile as address_mobile,t4.realname,t4.mobile,t5.price as totalprice,t5.total,t1.total as ytotal,t1.credit,t1.offsetprice,t1.status,t1.ordersn,t1.paytype,t1.transid,t1.code,t1.paytype from" . tablename('xcommunity_order') . "t1 left join" . tablename('xcommunity_member_address') . "t2 on t1.addressid=t2.id left join" . tablename('mc_members') . "t4 on t4.uid= t1.uid left join" . tablename('xcommunity_order_goods') . "t5 on t5.orderid= t1.id left join" . tablename('xcommunity_goods') . "t6 on t6.id=t5.goodsid where $condition order by t1.createtime desc LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
        }
        else {
            $sql = "select t3.realname,t3.mobile,t1.total as ytotal,t1.credit,t1.offsetprice,t1.createtime,t1.id,t1.status,t1.ordersn,t1.paytype,t1.transid,t1.price,t1.code,t2.realname as address_realname,t2.mobile as address_mobile,t1.paytype from" . tablename('xcommunity_order') . "t1 left join" . tablename('xcommunity_member_address') . "t2 on t1.addressid=t2.id left join" . tablename('mc_members') . "t3 on t3.uid= t1.uid where $condition order by t1.createtime desc LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
        }
        $list = pdo_fetchall($sql, $paras);
//        $paytype = array(
//            '0' => array('css' => 'default', 'name' => '未支付'),
//            '1' => array('css' => 'danger', 'name' => '余额支付'),
//            '2' => array('css' => 'info', 'name' => '微信支付'),
//            '3' => array('css' => 'warning', 'name' => '货到付款'),
//            '4' => array('css' => 'info', 'name' => '支付宝支付'),
//            '5' => array('css' => 'default', 'name' => '未支付'),
//            '6' => array('css' => 'default', 'name' => '积分抵扣')
//        );
        $paytype = array(
            '0' => array('css' => 'default', 'name' => '未支付'),
            '1' => array('css' => 'danger', 'name' => '余额支付'),
            '2' => array('css' => 'info', 'name' => '微信支付'),
            '3' => array('css' => 'warning', 'name' => '货到付款'),
            '4' => array('css' => 'info', 'name' => '支付宝支付'),
            '5' => array('css' => 'default', 'name' => '现金支付'),
            '6' => array('css' => 'default', 'name' => '银联刷卡'),
            '7' => array('css' => 'default', 'name' => '积分抵扣')
        );
        $orderstatus = array(
            '-1' => array('css' => 'default', 'name' => '已关闭'),
            '0'  => array('css' => 'danger', 'name' => '待付款'),
            '1'  => array('css' => 'info', 'name' => '待发货'),
            '2'  => array('css' => 'warning', 'name' => '待收货'),
            '3'  => array('css' => 'success', 'name' => '已完成')
        );
        foreach ($list as $key => $value) {

            $list[$key]['cctime'] = date('Y-m-d H:i', $value['createtime']);
            $s = $value['status'];
            $list[$key]['statuscss'] = $orderstatus[$value['status']]['css'];
            $list[$key]['status'] = $orderstatus[$value['status']]['name'];
            $list[$key]['css'] = $paytype[$value['paytype']]['css'];
            $list[$key]['paytype'] = $paytype[$value['paytype']]['name'];
//            if ($value['paytype'] == 2) {
//                if (empty($value['transid'])) {
//                    $list[$key]['paytype'] = '支付宝支付';
//
//                }
//                else {
//                    $list[$key]['paytype'] = '微信支付';
//                }
//            }
//            elseif ($value['paytype'] == 0) {
//                if ($value['status'] == 1) {
//                    $list[$key]['paytype'] = $paytype[6]['name'];
//                }
//                else {
//                    $t = 5;
//                    $list[$key]['paytype'] = $paytype[$t]['name'];
//                }
//            }
//            else {
//                $list[$key]['paytype'] = $paytype[$value['paytype']]['name'];
//            }

            $list[$key]['ordersn'] = chunk_split($value['ordersn']);
        }

        if ($user) {
            $tsql = "select count(*) from" . tablename('xcommunity_order') . "t1 left join" . tablename('xcommunity_member_address') . "t2 on t1.addressid=t2.id left join" . tablename('mc_members') . "t4 on t4.uid= t1.uid left join" . tablename('xcommunity_order_goods') . "t5 on t5.orderid= t1.id left join" . tablename('xcommunity_goods') . "t6 on t6.id=t5.goodsid where $condition ";
        }
        else {
            $tsql = "select count(*) from" . tablename('xcommunity_order') . "t1 left join" . tablename('xcommunity_member_address') . "t2 on t1.addressid=t2.id left join" . tablename('mc_members') . "t3 on t3.uid= t1.uid where $condition ";
        }

        $total = pdo_fetchcolumn($tsql, $paras);
        $pager = pagination($total, $pindex, $psize);

        include $this->template('web/plugin/shopping/order/list');
    }
    /**
     * 订单的详情
     */
    if ($operation == 'detail') {
        $id = intval($_GPC['id']);
        $codeStatus = set('p163') ? 1 : 0;
        if ($id) {
            $cond = " t1.id=:orderid";
            $params[':orderid'] = $id;
            if ($user) {
                if ($user['uuid']) {
                    //判断上级管理员是否是超市
                    $suser = pdo_get("xcommunity_users", array('uid' => $user['uuid']), array());
                    $uid = $suser['type'] == 4 ? $suser['uid'] : $_W['uid'];
                    $cond .= " and t6.uid=:uid";
                    $params[':uid'] = $uid;

                }
                else {
                    $cond .= " and t6.uid=:uid";
                    $params[':uid'] = $_W['uid'];
                }
            }
            $tsql = "select t1.*,t4.realname,t4.mobile,t5.price as totalprice,t5.total,t7.openid,t2.address,t2.city,t1.code,t2.realname as address_realname,t2.mobile as address_mobile from" . tablename('xcommunity_order') . "t1 left join" . tablename('xcommunity_member_address') . "t2 on t1.addressid=t2.id left join" . tablename('mc_members') . "t4 on t4.uid= t1.uid left join" . tablename('xcommunity_order_goods') . "t5 on t5.orderid= t1.id left join" . tablename('xcommunity_goods') . "t6 on t6.id=t5.goodsid left join" . tablename('mc_mapping_fans') . "t7 on t7.uid = t1.uid  where $cond ";
            $item = pdo_fetch($tsql, $params);

        }
        $condition = " o.orderid=:orderid ";
        $paras[':orderid'] = $id;
        if ($user) {
            if ($user['uuid']) {
                //判断上级管理员是否是超市
                $suser = pdo_get("xcommunity_users", array('uid' => $user['uuid']), array());
                $uid = $suser['type'] == 4 ? $suser['uid'] : $_W['uid'];
                $condition .= " and g.uid=:uid";
                $paras[':uid'] = $uid;

            }
            else {
                $condition .= " and g.uid=:uid";
                $paras[':uid'] = $_W['uid'];
            }
        }
        //获取商品信息
        $goods = pdo_fetchall("SELECT g.*, o.total,o.price as orderprice FROM " . tablename('xcommunity_order_goods') . " o left join " . tablename('xcommunity_goods') . " g on o.goodsid=g.id " . " WHERE $condition", $paras);
//        $item['goods'] = $goods;
        $price = '';
        $shops = pdo_getall('xcommunity_shop', array('uniacid' => $_W['uniacid']), array('id', 'title'), 'id');
        foreach ($goods as $k => $v) {
//        $goods[$k]['goods'] = ($k+1)."商品:".$v['title'].",数量：".$v['total'].",单价：".$v['marketprice']."元</br>";
            $price += $v['orderprice'] * $v['total'];
            $goods[$k]['stitle'] = $shops[$v['shopid']]['title'];
        }
        $item['price'] = $user && set('p109') ? $price : $item['price'];
        $title = '';
        $count = count($goods);
        if ($count == 1) {
            foreach ($goods as $key => $value) {
                $title = $value['title'];
            }
        }
        else {
            foreach ($goods as $key => $value) {
                $title .= $value['title'] . ',';
            }
        }
        $item['goods'] = $goods;
        if (empty($item)) {
            itoast("抱歉，订单不存在!", referer(), "error", ture);
        }
        if (checksubmit('confirmsend')) {
            if (!empty($item['transid'])) {
                $this->changeWechatSend($id, 1);
            }
            $expressrel = $_GPC['realname'];
            $expresscom = $_GPC['company'];
            $expresssn = $_GPC['express'];
            $data = array(
                'realname' => $expressrel,
                'company'  => $expresscom,
                'express'  => $expresssn,
            );
            pdo_update('xcommunity_order', $data, array('id' => $item['id']));
            $little = pdo_fetch("select t1.*,t2.title from" . tablename('xcommunity_counter_little') . "t1 left join" . tablename('xcommunity_counter_main') . "t2 on t1.deviceid=t2.id where t1.id=:id", array(':id' => $item['littleid']));
            $data = array(
                'uniacid'    => $_W['uniacid'],
                'status'     => 0,
                'code'       => rand(1000, 9999),
                'deviceid'   => $little['deviceid'],
                'orderid'    => $id,
                'createtime' => TIMESTAMP
            );
            $address = $item['address'] . '-' . $little['title'] . '-' . ($little['lock'] + 1) . '柜子';
            pdo_insert('xcommunity_counter_code', $data);
            if (set('s2') && set('s6')) {
                $type = set('s1');
                if ($type == 1) {
                    $type = 'wwt';
                }
                elseif ($type == 2) {
                    $type = 'juhe';
                    $tpl_id = set('s10');
                }
                else {
                    $type = 'aliyun_new';
                    $tpl_id = set('s22');
                }
                $sdst = $item['mobile'];
                if ($type == 'wwt') {
                    $smsg = "您的快递是" . $expresscom . ",快递单号" . $expresssn . "。有任何问题请随时与我们联系，谢谢。";
                    $content = sms::send($sdst, '', $smsg, $type);
                }
                elseif ($type == 'juhe') {
                    $smsg = urlencode("#express_name#=$expresscom&#express_code#=$expresssn");
                    $content = sms::send($sdst, $smsg, '', 1, $tpl_id);
                }
                else {
                    $smsg = json_encode(array('express_name' => $expresscom, 'express_code' => $expresssn));
                    $content = sms::send($sdst, $smsg, '', 1, $tpl_id);
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
            else {

                if (set('x21', $item['regionid'])) {
                    $type = set('x21', $item['regionid']);
                    if ($type == 1) {
                        $type = 'wwt';
                    }
                    elseif ($type == 2) {
                        $type = 'juhe';
                        $tpl_id = set('x35', $item['regionid']);
                    }
                    elseif ($type == 3) {
                        $type = 'aliyun_new';
                        $tpl_id = set('x70', $item['regionid']);
                    }
                    $sdst = $item['mobile'];
                    if ($type == 'wwt') {
                        $smsg = "您的快递是" . $expresscom . ",快递单号" . $expresssn . "。有任何问题请随时与我们联系，谢谢。";
                        $content = sms::send($sdst, $smsg, $type, '', 1, $tpl_id);
                    }
                    elseif ($type == 'juhe') {
                        $smsg = urlencode("#express_name#=$expresscom&#express_code#=$expresssn");
                        $content = sms::send($sdst, $smsg, $type, '', 1, $tpl_id);
                    }
                    elseif ($type == 'aliyun_new') {
                        $smsg = json_encode(array('express_name' => $expresscom, 'express_code' => $expresssn));
                        $content = sms::send($sdst, $smsg, $type, '', 1, $tpl_id);
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
                        'value' => $title,
                    ),
                    'keyword3' => array(
                        'value' => $item['address_realname'] . ',' . $item['address_mobile'] . ',' . $item['city'] . ',' . $item['address'],
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
                $re = util::sendTplNotice($item['openid'], $tplid, $content, $url = '');
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
                $content = array(
                    'first'    => array(
                        'value' => '发货啦！',
                    ),
                    'keyword1' => array(
                        'value' => $item['price'] . '元',
                    ),
                    'keyword2' => array(
                        'value' => $title,
                    ),
                    'keyword3' => array(
                        'value' => $item['address_realname'] . ',' . $item['address_mobile'] . ',' . $address,
                    ),
                    'keyword4' => array(
                        'value' => $item['ordersn'],
                    ),
                    'keyword5' => array(
                        'value' => $little['title'] . '-开柜码：' . $data['code'],
                    ),
                    'remark'   => array(
                        'value' => '有任何问题请随时与我们联系，谢谢。',
                    ),
                );
                if ($little) {
                    foreach ($goods as $k => $v) {
                        $snotice = pdo_getall('xcommunity_shop_wechat', array('shopid' => $v['shopid'], 'enable' => 1, 'shoptype' => 1));
                        foreach ($snotice as $key => $val) {
                            if ($val['type'] == 1 || $val['type'] == 3) {
                                util::sendTplNotice($val['openid'], $tplid, $content, $url = '', $topcolor = '#FF683F');
                            }
                        }
                    }
                }
            }
            pdo_update(
                'xcommunity_order',
                array(
                    'status' => 2,
                    'remark' => $_GPC['remark'],
                ),
                array('id' => $id)
            );
            util::permlog('', '超市订单发货,订单号:' . $item['ordersn']);
            itoast('发货操作成功！', referer(), 'success', ture);
        }
        if (checksubmit('cancelsend')) {
            $item = pdo_fetch("SELECT transid FROM " . tablename('xcommunity_order') . " WHERE id = :id", array(':id' => $id));
            if (!empty($item['transid'])) {
                $this->changeWechatSend($id, 0, $_GPC['cancelreson']);
            }
            pdo_update(
                'xcommunity_order',
                array(
                    'status' => 1,
                    'remark' => $_GPC['remark'],
                ),
                array('id' => $id)
            );
            util::permlog('', '超市订单取消发货,订单号' . $item['ordersn']);
            itoast('取消发货操作成功！', referer(), 'success', ture);
        }
        if (checksubmit('finish')) {
            pdo_update('xcommunity_order', array('status' => 3, 'remark' => $_GPC['remark']), array('id' => $id));
            itoast('订单操作成功！', referer(), 'success', ture);
        }
        if (checksubmit('cancel')) {
            pdo_update('xcommunity_order', array('status' => 1, 'remark' => $_GPC['remark']), array('id' => $id));
            itoast('取消完成订单操作成功！', referer(), 'success', ture);
        }
        if (checksubmit('cancelpay')) {
            pdo_update('xcommunity_order', array('status' => 0, 'remark' => $_GPC['remark']), array('id' => $id));
            //设置库存
            $this->setOrderStock($id, false);
            //减少积分
            $this->setOrderCredit($id, false);

            itoast('取消订单付款操作成功！', referer(), 'success', ture);
        }
        if (checksubmit('confrimpay')) {
            pdo_update('xcommunity_order', array('status' => 1, 'paytype' => 4, 'remark' => $_GPC['remark']), array('id' => $id));
            //设置库存
            $this->setOrderStock($id);
            //增加积分
            $this->setOrderCredit($id);
            itoast('确认订单付款操作成功！', referer(), 'success', ture);
        }
        if (checksubmit('close')) {
            $item = pdo_fetch("SELECT transid FROM " . tablename('xcommunity_order') . " WHERE id = :id", array(':id' => $id));
            if (!empty($item['transid'])) {
                $this->changeWechatSend($id, 0, $_GPC['reson']);
            }
            pdo_update('xcommunity_order', array('status' => -1, 'remark' => $_GPC['remark']), array('id' => $id));
            itoast('订单关闭操作成功！', referer(), 'success', ture);
        }
        if (checksubmit('open')) {
            $item = pdo_fetch("SELECT paytype FROM " . tablename('xcommunity_order') . " WHERE id = :id", array(':id' => $id));
            if (!empty($item['paytype']) && $item['paytype'] != 3) {
                $status = 1;
            }
            pdo_update('xcommunity_order', array('status' => $status, 'remark' => $_GPC['remark']), array('id' => $id));
            itoast('开启订单操作成功！', referer(), 'success', ture);
        }

        include $this->template('web/plugin/shopping/order/detail');
    }
    /**
     * 订单的删除
     */
    if ($operation == 'delete') {
        /*订单删除*/
        $orderid = intval($_GPC['id']);
        $item = pdo_get('xcommunity_order', array('id' => $orderid), array());
        if ($item) {
            if (pdo_delete('xcommunity_order', array('id' => $orderid))) {
                util::permlog('超市订单-删除', '订单号:' . $item['ordersn']);
                itoast('订单删除成功', $this->createWebUrl('shopping', array('op' => 'order', 'operation' => 'list')), 'success', ture);
            }
        }

    }
}
/**
 * 修改订单的状态
 */
if ($op == 'verify') {
    //处理状态
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
 *
 */
if ($op == 'set') {
    $id = intval($_GPC['id']);
    if (empty($id)) {
        itoast('缺少参数', referer(), 'error');
    }
    $type = $_GPC['type'];
    $data = intval($_GPC['data']);
    $data = ($data == 1 ? 0 : 1);
    pdo_query("UPDATE " . tablename('xcommunity_category') . "SET isshow = '{$data}' WHERE id=:id", array(":id" => $id));
    die(json_encode(array("result" => 1, "data" => $data)));
}
/**
 *
 */
if ($op == 'notice') {
    $operation = in_array($_GPC['operation'], array('list', 'add')) ? $_GPC['operation'] : 'list';
    if ($operation == 'add') {
        $uniacid = intval($_W['uniacid']);
        $condition = '';
        if (intval($_GPC['uuid'])) {
            $uuid = intval($_GPC['uuid']);
            $condition = " AND x.uuid='{$uuid}'";
        }
        else {
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
                itoast('该信息不存在或已删除', referer(), 'error', ture);
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
                'type'       => 1
            );
            if ($id) {
                if (pdo_update('xcommunity_xqnotice', $data, array('id' => $id))) {
                    itoast('提交成功', referer(), 'success', ture);
                }
            }
            else {
                if (pdo_insert('xcommunity_xqnotice', $data)) {
                    itoast('提交成功', referer(), 'success', ture);
                }
            }
        }

        include $this->template('web/plugin/shopping/notice/add');
    }
    elseif ($operation == 'list') {
        $pindex = max(1, intval($_GPC['page']));
        $psize = 20;
        $condition = "uniacid=:uniacid";
        $params[':uniacid'] = $_W['uniacid'];
        if ($user['type'] == 4) {
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
 *
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
 * 打印机
 */
if ($op == 'print') {
    /**
     * 打印机列表
     */
    if ($operation == 'list') {
        $condition = " t1.uniacid=:uniacid and t1.shoptype=1";
        $parms[':uniacid'] = $_W['uniacid'];
        $condi = " t2.uniacid=:uniacid and t2.type=1";
        $pars[':uniacid'] = $_W['uniacid'];
        if ($user) {
            if ($user['type'] == 4) {
                $condition .= " and t2.id in({$user['store']}) ";
            }
        }
        $shops = pdo_fetchall("select * from" . tablename('xcommunity_shop') . "t2 where $condi", $pars);
        if ($_GPC['shopid']) {
            $condition .= " t1.shopid=:shopid ";
            $parms[':shopid'] = $_GPC['shopid'];
        }
        $pindex = max(1, intval($_GPC['page']));
        $psize = 20;
        $sql = "SELECT t1.*,t2.title FROM" . tablename('xcommunity_shop_print') . "t1 left join" . tablename('xcommunity_shop') . "t2 on t2.id=t1.shopid WHERE $condition order by t1.id desc LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
        $list = pdo_fetchall($sql, $parms);
        $total = pdo_fetchcolumn("SELECT COUNT(*) FROM" . tablename('xcommunity_shop_print') . "t1 left join" . tablename('xcommunity_shop') . "t2 on t2.id=t1.shopid WHERE $condition", $parms);
        $pager = pagination($total, $pindex, $psize);
        include $this->template('web/plugin/shopping/print/list');
    }
    /**
     * 打印机的添加
     */
    if ($operation == 'add') {
        $shopid = intval($_GPC['shopid']);
        $id = intval($_GPC['id']);
        if ($id) {
            $item = pdo_get('xcommunity_shop_print', array('id' => $id), array());
        }
        if ($_W['isajax']) {
            $data = array(
                'uniacid'  => $_W['uniacid'],
                'type'     => $_GPC['type'],
                'shopid'   => $shopid,
                'api_key'  => $_GPC['api_key'],
                'deviceNo' => $_GPC['deviceNo'],
                'shoptype' => 1
            );
            if ($id) {
                pdo_update('xcommunity_shop_print', $data, array('id' => $id));
            }
            else {
                $data['uid'] = $_W['uid'];
                pdo_insert('xcommunity_shop_print', $data);
            }
            echo json_encode(array('status' => 1));
            exit();
        }
        $condition = " uniacid=:uniacid and type=1 and status=1";
        $params[':uniacid'] = $_W['uniacid'];
        if ($user) {
            if ($user['uuid']) {
                //判断上级管理员是否是商家管理员
                $suser = pdo_get("xcommunity_users", array('uid' => $user['uuid']), array());
                $uid = $suser['type'] == 5 ? $user['uuid'] : $_W['uid'];
                $condition .= " and uid = {$uid}";
            }
            else {
                $condition .= " and uid = {$_W['uid']}";
            }
        }
        $shops = pdo_fetchall("select * from" . tablename('xcommunity_shop') . "where $condition", $params);
        include $this->template('web/plugin/shopping/print/add');
    }
    /**
     * 打印机删除
     */
    if ($operation == 'del') {
        $item = pdo_get('xcommunity_shop_print', array('id' => $id), array());
        if ($item) {
            if (pdo_delete('xcommunity_shop_print', array('id' => $id))) {
                util::permlog('超市打印机-删除', '信息ID:' . $item['id']);
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
 * 废弃
 */
if ($op == 'address') {
    if ($operation == 'list') {
        if ($_W['ispost']) {
            $ids = $_GPC['ids'];
            if (!empty($ids)) {
                foreach ($ids as $key => $id) {
                    pdo_delete('xcommunity_member_address', array('id' => $id));
                }
                itoast('删除成功', referer(), 'success', ture);
            }
        }
        $pindex = max(1, intval($_GPC['page']));
        $psize = 20;
        $condition = "uniacid=:uniacid";
        $params[':uniacid'] = $_W['uniacid'];
        $keyword = trim($_GPC['keyword']);
        if ($keyword) {
            $condition .= " and (mobile=:keyword or realname=:keyword)";
            $params[':keyword'] = $keyword;
        }
        $sql = "SELECT * FROM" . tablename('xcommunity_member_address') . "WHERE $condition order by id desc LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
        $list = pdo_fetchall($sql, $params);
        $total = pdo_fetchcolumn("SELECT COUNT(*) FROM" . tablename('xcommunity_member_address') . "WHERE $condition", $params);
        $pager = pagination($total, $pindex, $psize);
        include $this->template('web/plugin/shopping/address/list');
    }
    elseif ($operation == 'del') {
        $id = $_GPC['id'];
        if ($id) {
            $item = pdo_get('xcommunity_member_address', array('id' => $id), array('id'));
            if ($item) {
                if (pdo_delete('xcommunity_member_address', array('id' => $id))) {
                    itoast('删除成功', referer(), 'success', ture);
                }
            }
        }
    }
    elseif ($operation == 'add') {
        $id = intval($_GPC['id']);
        if ($id) {
            $item = pdo_get('xcommunity_member_address', array('id' => $id), array());
            $birth = explode(' ', $item['city']);
            $item['province'] = $birth[0];
            $item['city'] = $birth[1];
            $item['dist'] = $birth[2];
        }
        if (checksubmit('submit')) {
            $birth = $_GPC['birth']['province'] . ' ' . $_GPC['birth']['city'] . ' ' . $_GPC['birth']['district'];
            $data = array(
                'realname'   => $_GPC['realname'],
                'mobile'     => $_GPC['mobile'],
                'createtime' => TIMESTAMP,
                'address'    => $_GPC['address'],
                'city'       => $birth,
            );
            if (!empty($id)) {
                pdo_update('xcommunity_member_address', $data, array('id' => $id));
            }
            itoast('地址更新成功！', referer(), 'success', ture);
        }
        include $this->template('web/plugin/shopping/address/add');
    }
}
/**
 *
 */
if ($op == 'bind') {
    $id = intval($_GPC['id']);
    if (empty($id)) {
        itoast('缺少参数', referer(), 'error');
        exit();
    }
    $item = pdo_get('xcommunity_goods', array('id' => $id), array('id'));
    if (empty($item)) {
        itoast('商品不存在或已删除', referer(), 'error');
        exit();
    }
    if (pdo_delete('xcommunity_goods_region', array('gid' => $id))) {
        $regions = model_region::region_fetall();
        foreach ($regions as $key => $value) {
            $dat = array(
                'gid'      => $id,
                'regionid' => $value['id'],
            );
            pdo_insert('xcommunity_goods_region', $dat);
        }
        itoast('绑定成功', referer(), 'success');
        exit();
    }

}
/**
 * 基本设置
 */
if ($op == 'sets') {
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
            }
            else {
                pdo_insert('xcommunity_setting', $data);
            }
        }
        itoast('操作成功', referer(), 'success', ture);
    }
    $set = pdo_getall('xcommunity_setting', array('uniacid' => $_W['uniacid']), array(), 'xqkey', array());
    include $this->template('web/plugin/shopping/set');
}
/**
 * 小区超市
 */
if ($op == 'shop') {
    /**
     * 超市的列表
     */
    if ($operation == 'list') {
        //店铺列表
        $condition = 'uniacid=:uniacid and type=1 and status=1';
        $params[':uniacid'] = $_W['uniacid'];
        if ($user) {
            if ($user['type'] == 4) {
                $condition .= " and id in({$user['store']}) ";
            }
        }
        if (!empty($_GPC['keyword'])) {
            $condition .= " AND (title LIKE '%{$_GPC['keyword']}%' or mobile LIKE '%{$_GPC['keyword']}%')";
        }
        $pindex = max(1, intval($_GPC['page']));
        $psize = 20;
        $sql = "SELECT * FROM" . tablename('xcommunity_shop') . "WHERE $condition order by id desc LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
        $list = pdo_fetchall($sql, $params);
        $total = pdo_fetchcolumn("SELECT COUNT(*) FROM" . tablename('xcommunity_shop') . "WHERE $condition", $params);
        $pager = pagination($total, $pindex, $psize);

        include $this->template('web/plugin/shopping/shop/list');
    }
    /**
     * 超市的添加
     */
    if ($operation == 'add') {
        if ($id) {
            $item = pdo_fetch("SELECT * FROM" . tablename('xcommunity_shop') . "WHERE id=:id", array(':id' => $id));
            if (empty($item)) {
                itoast('信息不存在或已删除', referer(), 'error', true);
            }
        }
        if ($_W['isajax']) {
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
                    $data['uid'] = $suser['type'] == 4 ? $user['uuid'] : $_W['uid'];
                }
                else {
                    $data['uid'] = $_W['uid'];
                }
                pdo_insert('xcommunity_shop', $data);
                $dpid = pdo_insertid();
                if ($_W['uid'] !== 1 && $user) {
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
                util::permlog('超市-添加', '信息标题:' . $data['title']);
            }
            else {
                pdo_update('xcommunity_shop', $data, array('id' => $id));
                $dpid = $id;
                util::permlog('超市-修改', '信息标题:' . $data['title']);
            }
            echo json_encode(array('status' => 1));
            exit();
        }
        load()->func('tpl');
        include $this->template('web/plugin/shopping/shop/add');
    }
    /**
     * 超市的删除
     */
    if ($operation == 'del') {
        //删除店铺
        if ($id) {
            $dp = pdo_get('xcommunity_shop', array('id' => $id), array());
            if ($dp) {
                pdo_update('xcommunity_goods', array('isshow' => 1), array('shopid' => $id));
                pdo_delete("xcommunity_alipayment", array('userid' => $id, 'type' => 4));
                pdo_delete("xcommunity_wechat", array('userid' => $id, 'type' => 4));
                pdo_delete("xcommunity_service_data", array('userid' => $id, 'type' => 4));
                pdo_delete("xcommunity_swiftpass", array('userid' => $id, 'type' => 4));
                pdo_delete("xcommunity_hsyunfu", array('userid' => $id, 'type' => 4));
                pdo_delete("xcommunity_chinaums", array('userid' => $id, 'type' => 4));
                pdo_delete('xcommunity_shop_print', array('shopid' => $id));
                pdo_delete('xcommunity_shop_wechat', array('shopid' => $id));
                if (pdo_update('xcommunity_shop', array('status' => 2), array('id' => $id))) {
                    util::permlog('超市-删除', '信息标题:' . $dp['title']);
                    itoast('删除成功', referer(), 'success');
                }
            }
        }
    }
}
/**
 * 超市的支付接口
 */
if ($op == 'payapi') {
    $condition = 'uniacid=:uniacid and type=1 and status=1';
    $params[':uniacid'] = $_W['uniacid'];
    if ($user) {
        if ($user['type'] == 4) {
            $condition .= " and id in({$user['store']}) ";
        }
    }
    $sql = "SELECT * FROM" . tablename('xcommunity_shop') . "WHERE $condition order by id desc ";
    $shops = pdo_fetchall($sql, $params);
    /**
     * 支付宝
     */
    if ($p == 'alipay') {
        /**
         * 支付宝的添加
         */
        if ($operation == 'add') {
            $id = intval($_GPC['id']);
            if ($id) {
                $item = pdo_get('xcommunity_alipayment', array('id' => $id), array());
            }
            if ($_W['isajax']) {
                $data = array(
                    'uniacid' => $_W['uniacid'],
                    'type'    => 4,
                    'account' => $_GPC['account'],
                    'partner' => $_GPC['partner'],
                    'secret'  => $_GPC['secret']
                );
                $data['userid'] = $_GPC['shopid'];
                if ($id) {
                    pdo_update('xcommunity_alipayment', $data, array('id' => $id));
                    util::permlog('', '修改支付宝ID:' . $id);
                }
                else {
                    if (pdo_get('xcommunity_alipayment', array('userid' => $_GPC['shopid'], 'type' => 4))) {
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
            include $this->template('web/plugin/shopping/payapi/alipay/add');
        }
        /**
         * 支付宝列表
         */
        if ($operation == 'list') {
            $pindex = max(1, intval($_GPC['page']));
            $psize = 20;
            $condition = '';
            $condition .= " t1.uniacid = {$_W['uniacid']} and t1.type=4";
            if ($user && $user[type] != 1) {
//                $condition .= " and t1.uid = {$_W['uid']}";
                if ($user['type'] == 4) {
                    $condition .= " and t2.id in({$user['store']}) ";
                }
            }
            $sql = "SELECT t1.*,t2.title FROM" . tablename('xcommunity_alipayment') . "t1 left join" . tablename('xcommunity_shop') . "t2 on t1.userid=t2.id WHERE $condition LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
            $list = pdo_fetchall($sql);
            $total = pdo_fetchcolumn("SELECT COUNT(*) FROM" . tablename('xcommunity_alipayment') . "t1 left join" . tablename('xcommunity_shop') . "t2 on t1.userid=t2.id WHERE $condition");
            $pager = pagination($total, $pindex, $psize);
            include $this->template('web/plugin/shopping/payapi/alipay/list');
        }
        /**
         * 支付宝的删除
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
     * 微信
     */
    if ($p == 'wechat') {
        /**
         * 微信的添加
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
                    'type'      => 4,
                );

                $data['userid'] = $_GPC['shopid'];
                if ($id) {
                    pdo_update('xcommunity_wechat', $data, array('id' => $id));
                    util::permlog('', '修改借用支付ID:' . $id);
                }
                else {
                    if (pdo_get('xcommunity_wechat', array('userid' => $_GPC['shopid'], 'type' => 4))) {
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
            include $this->template('web/plugin/shopping/payapi/wechat/add');
        }
        /**
         * 微信的列表
         */
        if ($operation == 'list') {
            $pindex = max(1, intval($_GPC['page']));
            $psize = 20;
            $condition = '';
            $condition .= " t1.uniacid = {$_W['uniacid']} and t1.type=4";
            if ($user && $user[type] != 1) {
//                $condition .= " and t1.uid = {$_W['uid']}";
                if ($user['type'] == 4) {
                    $condition .= " and t2.id in({$user['store']}) ";
                }
            }
            $sql = "SELECT t1.*,t2.title FROM" . tablename('xcommunity_wechat') . "t1 left join" . tablename('xcommunity_shop') . "t2 on t1.userid=t2.id WHERE $condition LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
            $list = pdo_fetchall($sql, array(':uniacid' => $_W['uniacid']));
            $total = pdo_fetchcolumn("SELECT COUNT(*) FROM" . tablename('xcommunity_wechat') . "t1 left join" . tablename('xcommunity_shop') . "t2 on t1.userid=t2.id WHERE $condition");
            $pager = pagination($total, $pindex, $psize);
            include $this->template('web/plugin/shopping/payapi/wechat/list');
        }
        /**
         * 微信的删除
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
     * 服务商
     */
    if ($p == 'sub') {
        /**
         * 服务商的添加
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
                    'type'       => 4,
                );
                $data['userid'] = $_GPC['shopid'];
                if ($id) {
                    pdo_update('xcommunity_service_data', $data, array('id' => $id));
                    util::permlog('', '修改子商户ID:' . $id);
                }
                else {
                    if (pdo_get('xcommunity_service_data', array('userid' => $_GPC['shopid'], 'type' => 4))) {
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
            include $this->template('web/plugin/shopping/payapi/sub/add');
        }
        /**
         * 服务商的列表
         */
        if ($operation == 'list') {
            $pindex = max(1, intval($_GPC['page']));
            $psize = 20;
            $condition = '';
            $condition .= " t1.uniacid = {$_W['uniacid']} and t1.type=4";
            if ($user && $user[type] != 1) {
//                $condition .= " and t1.uid = {$_W['uid']}";
                if ($user['type'] == 4) {
                    $condition .= " and t2.id in({$user['store']}) ";
                }
            }
            $sql = "SELECT t1.*,t2.title FROM" . tablename('xcommunity_service_data') . "t1 left join" . tablename('xcommunity_shop') . "t2 on t1.userid=t2.id WHERE $condition LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
            $list = pdo_fetchall($sql);
            $total = pdo_fetchcolumn("SELECT COUNT(*) FROM" . tablename('xcommunity_service_data') . "t1 left join" . tablename('xcommunity_shop') . "t2 on t1.userid=t2.id WHERE $condition");
            $pager = pagination($total, $pindex, $psize);
            include $this->template('web/plugin/shopping/payapi/sub/list');
        }
        /**
         * 服务商的删除
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
     * 威富通
     */
    if ($p == 'swiftpass') {
        /**
         * 威富通的添加
         */
        if ($operation == 'add') {
            $id = intval($_GPC['id']);
            if ($id) {
                $item = pdo_get('xcommunity_swiftpass', array('id' => $id), array());
            }
            if ($_W['isajax']) {
                $data = array(
                    'uniacid'     => $_W['uniacid'],
                    'type'        => 4,
                    'account'     => trim($_GPC['account']),
                    'secret'      => trim($_GPC['secret']),
                    'appid'       => trim($_GPC['appid']),
                    'appsecret'   => trim($_GPC['appsecret']),
                    'private_key' => trim($_GPC['private_key']),
                    'banktype'    => intval($_GPC['banktype'])
                );
                $data['userid'] = $_GPC['shopid'];
                if ($id) {
                    pdo_update('xcommunity_swiftpass', $data, array('id' => $id));
                    util::permlog('', '修改威富通微信支付ID:' . $id);
                }
                else {
                    if (pdo_get('xcommunity_swiftpass', array('userid' => $_GPC['shopid'], 'type' => 4))) {
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
            include $this->template('web/plugin/shopping/payapi/swiftpass/add');
        }
        /**
         * 威富通的列表
         */
        if ($operation == 'list') {
            $pindex = max(1, intval($_GPC['page']));
            $psize = 20;
            $condition = '';
            $condition .= " t1.uniacid = {$_W['uniacid']} and t1.type=4";
            if ($user && $user[type] != 1) {
//                $condition .= " and t1.uid = {$_W['uid']}";
                if ($user['type'] == 4) {
                    $condition .= " and t2.id in({$user['store']}) ";
                }
            }
            $sql = "SELECT t1.*,t2.title FROM" . tablename('xcommunity_swiftpass') . "t1 left join" . tablename('xcommunity_shop') . "t2 on t1.userid=t2.id WHERE $condition LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
            $list = pdo_fetchall($sql);
            $total = pdo_fetchcolumn("SELECT COUNT(*) FROM" . tablename('xcommunity_swiftpass') . "t1 left join" . tablename('xcommunity_shop') . "t2 on t1.userid=t2.id WHERE $condition");
            $pager = pagination($total, $pindex, $psize);

            include $this->template('web/plugin/shopping/payapi/swiftpass/list');
        }
        /**
         * 威富通的删除
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
     * 华商云付
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
                    'type'      => 4,
                    'account'   => trim($_GPC['account']),
                    'secret'    => trim($_GPC['secret']),
                    'appid'     => trim($_GPC['appid']),
                    'appsecret' => trim($_GPC['appsecret'])
                );
                $data['userid'] = $_GPC['shopid'];
                if ($id) {
                    pdo_update('xcommunity_hsyunfu', $data, array('id' => $id));
                    util::permlog('', '修改华商云付微信支付ID:' . $id);
                }
                else {
                    if (pdo_get('xcommunity_hsyunfu', array('userid' => $_GPC['shopid'], 'type' => 4))) {
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
            include $this->template('web/plugin/shopping/payapi/hsyunfu/add');
        }
        /**
         * 华商云付的列表
         */
        if ($operation == 'list') {
            $pindex = max(1, intval($_GPC['page']));
            $psize = 20;
            $condition = '';
            $condition .= " t1.uniacid = {$_W['uniacid']} and t1.type=4";
            if ($user && $user[type] != 1) {
//                $condition .= " and t1.uid = {$_W['uid']}";
                if ($user['type'] == 4) {
                    $condition .= " and t2.id in({$user['store']}) ";
                }
            }
            $sql = "SELECT t1.*,t2.title FROM" . tablename('xcommunity_hsyunfu') . "t1 left join" . tablename('xcommunity_shop') . "t2 on t1.userid=t2.id WHERE $condition LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
            $list = pdo_fetchall($sql);
            $total = pdo_fetchcolumn("SELECT COUNT(*) FROM" . tablename('xcommunity_hsyunfu') . "t1 left join" . tablename('xcommunity_shop') . "t2 on t1.userid=t2.id WHERE $condition");
            $pager = pagination($total, $pindex, $psize);

            include $this->template('web/plugin/shopping/payapi/hsyunfu/list');
        }
        /**
         * 华商云付的删除
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
     * 银联
     */
    if ($p == 'chinaums') {
        /**
         * 银联的添加
         */
        if ($operation == 'add') {
            $id = intval($_GPC['id']);
            if ($id) {
                $item = pdo_get('xcommunity_chinaums', array('id' => $id), array());
            }
            if ($_W['isajax']) {
                $data = array(
                    'uniacid'  => $_W['uniacid'],
                    'type'     => 4,
                    'mid'      => $_GPC['mid'],
                    'tid'      => $_GPC['tid'],
                    'instmid'  => $_GPC['instmid'],
                    'msgsrc'   => $_GPC['msgsrc'],
                    'msgsrcid' => $_GPC['msgsrcid'],
                    'secret'   => $_GPC['secret']
                );
                $data['userid'] = $_GPC['shopid'];
                if ($id) {
                    pdo_update('xcommunity_chinaums', $data, array('id' => $id));
                    util::permlog('', '修改银联ID:' . $id);
                }
                else {
                    if (pdo_get('xcommunity_chinaums', array('userid' => $_GPC['shopid'], 'type' => 4))) {
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
            include $this->template('web/plugin/shopping/payapi/chinaums/add');
        }
        /**
         * 银联的列表
         */
        if ($operation == 'list') {
            $pindex = max(1, intval($_GPC['page']));
            $psize = 20;
            $condition = '';
            $condition .= " t1.uniacid = {$_W['uniacid']} and t1.type=4";
            if ($user && $user[type] != 1) {
//                $condition .= " and t1.uid = {$_W['uid']}";
                if ($user['type'] == 4) {
                    $condition .= " and t2.id in({$user['store']}) ";
                }
            }
            $sql = "SELECT t1.*,t2.title FROM" . tablename('xcommunity_chinaums') . "t1 left join" . tablename('xcommunity_shop') . "t2 on t1.userid=t2.id WHERE $condition LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
            $list = pdo_fetchall($sql);
            $total = pdo_fetchcolumn("SELECT COUNT(*) FROM" . tablename('xcommunity_chinaums') . "t1 left join" . tablename('xcommunity_shop') . "t2 on t1.userid=t2.id WHERE $condition");
            $pager = pagination($total, $pindex, $psize);
            include $this->template('web/plugin/shopping/payapi/chinaums/list');
        }
        /**
         * 银联的删除
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
 * 超市的接收员
 */
if ($op == 'wechat') {
    $shopid = intval($_GPC['shopid']);
    /**
     * 接收员的列表
     */
    if ($operation == 'list') {
        $condition = " t1.uniacid=:uniacid and t1.shoptype=1";
        $parms[':uniacid'] = $_W['uniacid'];
        if ($user) {
            if ($user['type'] == 4) {
                $condition .= " and t2.id in({$user['store']}) ";
            }
        }
        $pindex = max(1, intval($_GPC['page']));
        $psize = 20;
        $sql = "SELECT t1.*,t2.title FROM" . tablename('xcommunity_shop_wechat') . "t1 left join" . tablename('xcommunity_shop') . "t2 on t2.id=t1.shopid WHERE $condition order by t1.id desc LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
        $list = pdo_fetchall($sql, $parms);
        $total = pdo_fetchcolumn("SELECT COUNT(*) FROM" . tablename('xcommunity_shop_wechat') . "t1 left join" . tablename('xcommunity_shop') . "t2 on t2.id=t1.shopid WHERE $condition", $parms);
        $pager = pagination($total, $pindex, $psize);
        include $this->template('web/plugin/shopping/wechat/list');
    }
    /**
     * 接收员的添加
     */
    if ($operation == 'add') {
        $regionids = '[]';
        $id = intval($_GPC['id']);
        if ($id) {
            $item = pdo_get('xcommunity_shop_wechat', array('id' => $id), array());
            $regs = pdo_getall('xcommunity_shop_wechatregion', array('wechatid' => $id), array('regionid'));
            $regionid = array();
            foreach ($regs as $key => $val) {
                $regionid[] = $val['regionid'];
            }
            $regionids = json_encode($regionid);
        }
        if ($_W['isajax']) {
            $birth = $_GPC['birth'];
            $allregion = intval($_GPC['allregion']);
            if ($allregion == 1) {

            }
            else {
                if (empty($birth['province'])) {
                    echo json_encode(array('content' => '必须选择省市区和小区！'));
                    exit();
                }
            }
            $data = array(
                'uniacid'   => $_W['uniacid'],
                'enable'    => $_GPC['enable'],
                'type'      => $_GPC['type'],
                'shopid'    => $shopid,
                'realname'  => trim($_GPC['realname']),
                'mobile'    => trim($_GPC['mobile']),
                'openid'    => trim($_GPC['openid']),
                'shoptype'  => 1,
                'province'  => $birth['province'],
                'city'      => $birth['city'],
                'dist'      => $birth['district'],
                'allregion' => $allregion
            );
            if ($id) {
                pdo_update('xcommunity_shop_wechat', $data, array('id' => $id));
                pdo_delete('xcommunity_shop_wechatregion', array('wechatid' => $id));
            }
            else {
                $data['uid'] = $_W['uid'];
                pdo_insert('xcommunity_shop_wechat', $data);
                $id = pdo_insertid();
            }
            if ($allregion == 1) {
                $regions = model_region::region_fetall();
                foreach ($regions as $k => $v) {
                    $dat = array(
                        'wechatid' => $id,
                        'regionid' => $v['id'],
                    );
                    pdo_insert('xcommunity_shop_wechatregion', $dat);
                }
            }
            else {
                $regionids = explode(',', $_GPC['regionids']);
                foreach ($regionids as $key => $value) {
                    $dat = array(
                        'wechatid' => $id,
                        'regionid' => $value,
                    );
                    pdo_insert('xcommunity_shop_wechatregion', $dat);
                }
            }
            echo json_encode(array('status' => 1));
            exit();
        }
        $condition = " uniacid=:uniacid and type=1 and status=1";
        $params[':uniacid'] = $_W['uniacid'];
        if ($user) {
            if ($user['type'] == 4) {
                $condition .= " and id in({$user['store']}) ";
            }
        }
        $shops = pdo_fetchall("select * from" . tablename('xcommunity_shop') . "where $condition", $params);
        include $this->template('web/plugin/shopping/wechat/add');
    }
    /**
     * 接收员的删除
     */
    if ($operation == 'del') {
        $dp = pdo_get('xcommunity_shop_wechat', array('id' => $id), array());
        if ($dp) {
            pdo_delete('xcommunity_shop_wechatregion', array('wechatid' => $id));
            if (pdo_delete('xcommunity_shop_wechat', array('id' => $id))) {
                util::permlog('超市接收员-删除', '信息标题:' . $dp['realname']);
                $result = array(
                    'status' => 1,
                );
                echo json_encode($result);
                exit();
            }
        }
    }
    /**
     * 接收员的状态
     */
    if ($operation == 'verify') {
        $id = intval($_GPC['id']);
        $type = $_GPC['type'];
        $data = intval($_GPC['data']);
        if (in_array($type, array('enable'))) {
            $data = ($data == 0 ? '1' : '0');
            pdo_update("xcommunity_shop_wechat", array($type => $data), array("id" => $id, "uniacid" => $_W['uniacid']));
            die(json_encode(array("result" => 1, "data" => $data)));
        }
    }
}
/**
 * 超市的分成设置
 */
if ($op == 'commission') {
    if (checksubmit('submit')) {
        $setting = $_GPC['commission'] . ',' . $_GPC['xqcommission'];
        pdo_update('xcommunity_shop', array('commission' => $setting), array('id' => $id));
        itoast('操作成功', referer(), 'success', ture);
    }
    $dp = pdo_get("xcommunity_shop", array('id' => $id), array('commission'));
    $setting = explode(',', $dp['commission']);
    include $this->template('web/plugin/shopping/shop/commission');
}