<?php
/**
 * 小区秘书模块
 *
 * [蓝牛科技] Copyright (c) 2013 xqms.cn
 */
/**
 * 后台小区物业费
 */

global $_GPC, $_W;
$ops = array('list', 'add', 'delete', 'detail', 'setgoodsproperty', 'order', 'del', 'edit', 'category', 'ajax', 'verify', 'xqprint', 'auth', 'wxsend', 'set', 'wechat', 'smssend', 'print', 'data', 'call');
$op = !empty($_GPC['op']) ? $_GPC['op'] : 'list';
if (!in_array($op, $ops)) {
    message('该方法不存在(op:' . $op . ')');
}
$p = !empty($_GPC['p']) ? $_GPC['p'] : 'list';
$id = intval($_GPC['id']);
// 操作员
$user = util::xquser($_W['uid']);
if ($user && $user['menu_ops']) {
    $menu_opss = explode(',', $user['menu_ops']);
}
$regions = model_region::region_fetall();
/**
 * 费用列表
 */
if ($op == 'list') {
    $pindex = max(1, intval($_GPC['page']));
    $psize = 20;
    $condition = " ";
    if ($user[type] == 3) {
        //小区管理员
        $condition .= " and r.id in({$user['regionid']})";
    }
    else {
        if ($_GPC['regionid']) {
            $condition .= " and regionid =:regionid";
            $params[':regionid'] = $_GPC['regionid'];
        }
    }
    $keyword = trim($_GPC['keyword']);
    if ($keyword) {
        $condition .= " and c.remark like :keyword";
        $params[':keyword'] = "%{$keyword}%";
    }
    $list = pdo_fetchall("SELECT c.* , r.title as title,r.city,r.dist ,c.title as costtitle FROM" . tablename('xcommunity_cost') . "as c left join" . tablename('xcommunity_region') . "as r on c.regionid = r.id WHERE c.uniacid='{$_W['uniacid']}' $condition order by c.createtime desc LIMIT " . ($pindex - 1) * $psize . ',' . $psize, $params);
    $total = pdo_fetchcolumn("SELECT COUNT(*) FROM" . tablename('xcommunity_cost') . "as c left join" . tablename('xcommunity_region') . "as r on c.regionid = r.id WHERE c.uniacid='{$_W['uniacid']}' $condition order by c.createtime desc", $params);
    $pager = pagination($total, $pindex, $psize);
    // AJAX是否显示
    if ($_W['isajax'] && $id) {
        $data = array();
        $type = trim($_GPC['type']);
        if ($type == 'enable') {
            $data['enable'] = intval($_GPC['enable']);
        }
        else {
            $data['status'] = intval($_GPC['enable']);
        }
        if (pdo_update('xcommunity_cost', $data, array('id' => $_GPC['id'])) !== false) {
            util::permlog('', '缴费订单ID:' . $id . '关闭');
            exit('success');
        }

    }
    load()->func('file');
    include $this->template('web/core/cost/list');
}
/**
 * 费用导入
 */
if ($op == 'add') {
    //判断是否是操作员
    set_time_limit(0);

    if (checksubmit('submit')) {

        $costtime = $_GPC['costtime']['start'] . '至' . $_GPC['costtime']['end'];
        $dat = array(
            'uniacid'    => $_W['uniacid'],
            'createtime' => TIMESTAMP,
            'regionid'   => intval($_GPC['regionid']),
            'costtime'   => $costtime,
            'enable'     => 1,
            'status'     => 1,
            'title'      => trim($_GPC['title']),
            'remark'     => trim($_GPC['remark'])
        );
        if (pdo_insert('xcommunity_cost', $dat)) {
            $cid = pdo_insertid();
            util::permlog('', '导入费用列表,费用ID:' . $id);
            $category = util::fetch_category_one('', intval($_GPC['regionid']));
//            print_r($category);exit();
            $leng = 7 + count(explode('|', trim($category['name'])));
//            print_r($category);echo $leng;exit();
            $rows = model_execl::import('cost');
            if ($rows[0][0] != '姓名' || $rows[0][1] != '手机号码' || $rows[0][2] != '地址') {
                itoast('文件内容不符，请重新上传', referer(), 'error', ture);
            }
            if (empty($rows)) {
                pdo_delete('xcommunity_cost', array('id' => $cid));
            }
            foreach ($rows as $rownum => $col) {
                if ($rownum > 0) {
                    if ($col[0] && $col[2]) {
                        $fee = '';
                        for ($i = 5; $i < count(explode('|', $category['name'])) + 5; $i++) {
                            //判断变量类型是否为浮点数类型
                            $vi = sprintf("%.2f", $col[$i]) ? sprintf("%.2f", $col[$i]) : '0.00';
                            $fee .= $vi . '|';
                        }
                        $data['username'] = $col[0];
                        $data['mobile'] = $col[1] ? $col[1] : 'xxxxxxxx';
                        $data['homenumber'] = $col[2];
                        $data['area'] = $col[3];
                        $data['costtime'] = $col[4];
                        $data['fee'] = rtrim($fee, '|');
                        $data['status'] = trim($col[$leng - 1]) == '是' ? 1 : 2;//1和是是已缴费，否和2是未缴费
                        $data['uniacid'] = $_W['uniacid'];
                        $data['cid'] = $cid;
                        $data['regionid'] = intval($_GPC['regionid']);
                        $data['total'] = sprintf("%.2f", $col[$leng - 2]);
                        $data['createtime'] = TIMESTAMP;
                        $data['address'] = strFilter($data['homenumber']);
                        if ($data['username'] && $data['homenumber']) {
                            $result = pdo_insert('xcommunity_cost_list', $data);
                        }
                    }
                }
            }
            itoast('导入成功', $this->createWebUrl('cost'), 'success', true);
            exit();
        }
    }

    include $this->template('web/core/cost/add');
}
/**
 * 费用列表--删除
 */
if ($op == 'delete') {
    if (empty($id)) {
        itoast('缺少参数', referer(), 'error');
    }
    $cost = pdo_get('xcommunity_cost', array('id' => $id), array());
    if ($cost) {
        $res = pdo_delete('xcommunity_cost_list', array('cid' => $id));
        $result = pdo_delete('xcommunity_cost', array('id' => $id, 'uniacid' => $_W['uniacid']));
        util::permlog('缴费信息-删除', '费用时间:' . $cost['costtime']);
    }


    itoast('删除成功', referer(), 'success', true);

}
/**
 * 费用数据
 */
if ($op == 'detail') {
    // 退款
    if (checksubmit('refund')) {
        $id = intval($_GPC['id']);
        if ($id) {
            $data = array(
                'remark'  => $_GPC['remark'],
                'status'  => 2,
                'paytype' => 0,
                'paytime' => 0
            );
            $r = pdo_update('xcommunity_cost_list', $data, array('id' => $id));
            if ($r) {
                itoast('退款成功', referer(), 'success');
            }
        }
    }
    // 排序
    if (checksubmit('submit')) {
        if (!empty($_GPC['displayorder'])) {
            foreach ($_GPC['displayorder'] as $id => $displayorder) {
                pdo_update('xcommunity_cost_list', array('displayorder' => $displayorder), array('id' => $id));
            }
            itoast('排序更新成功！', 'refresh', 'success', ture);
        }
    }
    if (checksubmit('update')) {
        $status = $_GPC['status'];
        $status = ($status == '是' || $status == 1 ? 2 : 1);
        $data = array(
            'status'  => $status,
            'remark'  => $_GPC['remark'],
            'paytype' => intval($_GPC['paytype']),
            'paytime' => TIMESTAMP
        );
        $costid = intval($_GPC['costid']);
        $cost = pdo_get('xcommunity_cost_list', array("id" => $costid, "uniacid" => $_W['uniacid']), array());
        $addressid = pdo_getcolumn('xcommunity_member_room', array('address' => $cost['homenumber'], 'regionid' => $cost['regionid']), 'id');
        $r = pdo_update("xcommunity_cost_list", $data, array("id" => $costid, "uniacid" => $_W['uniacid']));
        // 创建订单
        $dat = array(
            'uniacid'     => $_W['uniacid'],
            'ordersn'     => 'LN' . date('YmdHi') . random(10, 1),
            'createtime'  => TIMESTAMP,
            'price'       => $cost['total'],
            'regionid'    => $cost['regionid'],
            'addressid'   => $addressid,
            'type'        => 'pfree',
            'status'      => 1,
            'credit'      => 0,
            'total'       => $cost['total'],
            'paytype'     => intval($_GPC['paytype']),
            'offsetprice' => 0
        );
        pdo_insert('xcommunity_order', $dat);
        $orderid = pdo_insertid();
        $d = array(
            'uniacid'    => $_W['uniacid'],
            'goodsid'    => $costid,
            'orderid'    => $orderid,
            'price'      => $cost['total'],
            'createtime' => TIMESTAMP,
        );
        pdo_insert('xcommunity_order_goods', $d);
        // 修改的id添加超链接
        $url = $this->createWebUrl('cost', array('op' => 'edit', 'id' => $costid, 'regionid' => $cost['regionid'], 'costid' => $cost['cid'], 'cid' => $cost['cid']));
        util::permlog('费用数据-修改状态', '费用id:<a href="' . $url . '">' . $costid . '</a>修改状态');
        if ($r) {
            itoast('提交成功', referer(), 'success');
        }
    }
    if (checksubmit('updateStatus')) {
        if (empty($_GPC['ids'])) {
            itoast('请勾选费用', '', 'error');
        }
        if (empty($_GPC['regionid'])) {
            itoast('请勾选小区', '', 'error');
        }
        $ids = explode(',', $_GPC['ids']);
        $data = array(
            'status'  => 1,
            'remark'  => $_GPC['remark'],
            'paytype' => intval($_GPC['paytype']),
            'paytime' => TIMESTAMP
        );
        $idss = '';
        $r = pdo_update("xcommunity_cost_list", $data, array("id" => $ids, "uniacid" => $_W['uniacid']));
        $costList = pdo_getall('xcommunity_cost_list', array("id" => $ids, "uniacid" => $_W['uniacid']), array(), 'id');
        $cost_rooms = _array_column($costList, 'homenumber');
        $rooms = pdo_getall('xcommunity_member_room', array('address' => $cost_rooms, 'regionid' => intval($_GPC['regionid'])), array('id', 'address'), 'address');
        foreach ($ids as $id) {
            $dat = array(
                'uniacid'     => $_W['uniacid'],
                'ordersn'     => 'LN' . date('YmdHi') . random(10, 1),
                'createtime'  => TIMESTAMP,
                'price'       => $costList[$id]['total'],
                'regionid'    => $costList[$id]['regionid'],
                'addressid'   => $rooms[$costList[$id]['homenumber']]['id'],
                'type'        => 'pfree',
                'status'      => 1,
                'credit'      => 0,
                'total'       => $costList[$id]['total'],
                'paytype'     => intval($_GPC['paytype']),
                'offsetprice' => 0
            );
            pdo_insert('xcommunity_order', $dat);
            $orderid = pdo_insertid();
            $d = array(
                'uniacid'    => $_W['uniacid'],
                'goodsid'    => $id,
                'orderid'    => $orderid,
                'price'      => $costList[$id]['total'],
                'createtime' => TIMESTAMP,
            );
            pdo_insert('xcommunity_order_goods', $d);
            // 修改的id添加超链接
            $url = $this->createWebUrl('cost', array('op' => 'edit', 'id' => $id, 'regionid' => $costList[$id]['regionid'], 'costid' => $costList[$id]['cid'], 'cid' => $costList[$id]['cid']));
            $idss .= '<a href="' . $url . '">' . $id . '</a>,';
        }
        $idss = xtrim($idss);
        util::permlog('费用数据-批量修改', '费用id:' . $idss . '修改支付状态');
        itoast('提交成功', referer(), 'success');
    }
    $condition = 't1.uniacid =:uniacid ';
    $params[':uniacid'] = $_W['uniacid'];
    if ($id) {
        $condition .= 'and t1.cid=:id ';
        $params[':id'] = $id;
    }

    if ($_GPC['mobile']) {
        $condition .= "AND t1.mobile like '%{$_GPC['mobile']}%'";
    }
    if ($_GPC['username']) {
        $condition .= " AND t1.username like '%{$_GPC['username']}%'";
    }
    if ($_GPC['homenumber']) {
        $condition .= " AND t1.homenumber like '%{$_GPC['homenumber']}%'";
    }
    if ($_GPC['status'] == '是' || $_GPC['status'] == 1) {
        $condition .= " AND (t1.status='是' or t1.status = 1) ";
    }
    if ($_GPC['status'] == '否' || $_GPC['status'] == 2) {
        $condition .= " AND (t1.status='否' or t1.status = 2 or t1.status = '0') ";
    }
    $cids = $_GPC['cid'];
    //删除用户
    if (checksubmit('delete')) {
        if (!empty($cids)) {
            foreach ($cids as $key => $cid) {
                pdo_delete('xcommunity_cost_list', array('id' => $cid));
            }
            util::permlog('', '批量删除缴费用户信息');
            itoast('删除成功', referer(), 'success', true);
        }
    }
    $starttime = strtotime($_GPC['birth']['start']);
    if ($_GPC['birth']['end']) {
        $endtime = strtotime($_GPC['birth']['end']) + 86400 - 1;
    }
    if ($starttime && $endtime) {
        if ($_GPC['export'] == 2) {
            $condition .= " AND t1.paytime between '{$starttime}' and '{$endtime}'";
        }
    }
    if ($_GPC['detailid']) {
        $condition .= "and t1.id=:id ";
        $params[':id'] = intval($_GPC['detailid']);
    }
    if ($cids) {
        $costids = implode(',', $cids);
        $condition .= " and t1.id in({$costids})";
    }
    if ($_GPC['export'] == 1) {
        if (empty(intval($_GPC['regionid']))) {
            itoast('请选择小区', '', 'error');
            exit();
        }
        $xqcostsql = "select t1.username,t1.mobile,t1.homenumber,t1.area,t1.costtime,t1.total,t1.status,t1.fee,t1.id,t1.regionid,t1.paytype,t1.paytime,t1.createtime from" . tablename('xcommunity_cost_list') . "t1 where $condition ";
        $xqmembers = pdo_fetchall($xqcostsql, $params);
        $regionid = intval($_GPC['regionid']);
        $category = pdo_get('xcommunity_category', array('regionid' => $regionid), array('name'));
        $cates = explode('|', $category['name']);

        $arr = array(
            array(
                'title' => '姓名',
                'field' => 'username',
                'width' => 12
            ),
            array(
                'title' => '手机号',
                'field' => 'mobile',
                'width' => 12
            ),
            array(
                'title' => '房号',
                'field' => 'homenumber',
                'width' => 25
            ),
            array(
                'title' => '面积',
                'field' => 'area',
                'width' => 12
            ),
            array(
                'title' => '时间',
                'field' => 'costtime',
                'width' => 27
            )
        );
        $group = array();
//        print_r($cates);
        foreach ($cates as $i => $j) {
            $arr[] = array(
                'title' => $j,
                'field' => $j,
                'width' => 20
            );
        }
        $leng = count($cates);
        $arr[$leng + 5] = array(
            'title' => '总计',
            'field' => 'total',
            'width' => 12
        );
        $arr[$leng + 6] = array(
            'title' => '状态',
            'field' => 's',
            'width' => 12
        );
        $arr[$leng + 7] = array(
            'title' => '创建时间',
            'field' => 'jftime',
            'width' => 20
        );
        $arr[$leng + 8] = array(
            'title' => '支付方式',
            'field' => 'paytype',
            'width' => 20
        );
        $arr[$leng + 9] = array(
            'title' => '支付时间',
            'field' => 'paytime',
            'width' => 20
        );
        foreach ($xqmembers as $key => $val) {
            $s = $val['status'] == '是' || $val['status'] == 1 ? '已缴费' : '未缴费';
            $xqmembers[$key]['s'] = $s;
//            $order = pdo_get('xcommunity_order',array('goodsid' => $val['id']),array('createtime'));
            $tsql = "select t1.createtime from" . tablename('xcommunity_order') . "t1 left join" . tablename('xcommunity_order_goods') . "t2 on t1.id=t2.orderid where t2.goodsid=:goodsid";
            $order = pdo_fetch($tsql, array(':goodsid' => $val['id']));
            $xqmembers[$key]['jftime'] = date('Y-m-d H:i', $val['createtime']);
            $xqmembers[$key]['paytime'] = $val['paytime'] ? date('Y-m-d H:i', $val['paytime']) : 0;
//            $category = pdo_get('xcommunity_category',array('regionid'=>$val['regionid']),array('name'));
            $paytype = array('', '余额', '微信支付', '货到付款', '支付宝', '现金', '银联刷卡');
            $xqmembers[$key]['paytype'] = $paytype[$val['paytype']];
//            if ($val['paytype'] == 1) {
//                $xqmembers[$key]['paytype'] = '线上';
//            } elseif ($val['paytype'] == 2) {
//                $xqmembers[$key]['paytype'] = '现金';
//            } elseif ($val['paytype'] == 3) {
//                $xqmembers[$key]['paytype'] = '刷卡';
//            } elseif ($val['paytype'] == 4) {
//                $xqmembers[$key]['paytype'] = '转账';
//            } else {
//                $xqmembers[$key]['paytype'] = '';
//            }
            $fee = explode('|', $val['fee']);
            foreach ($cates as $k => $v) {
                $xqmembers[$key][$v] = $fee[$k];
            }

        }
//        print_r($arr);exit();
//        print_r($xqmembers);exit();
        model_execl::export($xqmembers, array(
            "title"   => "费用数据-" . date('Y-m-d-H-i', time()),
            "columns" => $arr
        ));
    }
    if ($_GPC['export'] == 2) {
        if (empty(intval($_GPC['regionid']))) {
            itoast('请选择小区', '', 'error');
            exit();
        }
        $xqcostsql = "select t1.username,t1.mobile,t1.homenumber,t1.area,t1.costtime,t1.total,t1.status,t1.fee,t1.id,t1.regionid,t1.paytype,t1.paytime from" . tablename('xcommunity_cost_list') . "t1 where $condition ";
        $xqmembers = pdo_fetchall($xqcostsql, $params);
        $regionid = intval($_GPC['regionid']);
        $category = pdo_get('xcommunity_category', array('regionid' => $regionid), array('name'));
        $cates = explode('|', $category['name']);

        $arr = array(
            array(
                'title' => '姓名',
                'field' => 'username',
                'width' => 12
            ),
            array(
                'title' => '手机号',
                'field' => 'mobile',
                'width' => 12
            ),
            array(
                'title' => '房号',
                'field' => 'homenumber',
                'width' => 25
            ),
            array(
                'title' => '面积',
                'field' => 'area',
                'width' => 12
            ),
            array(
                'title' => '时间',
                'field' => 'costtime',
                'width' => 27
            )
        );
        $group = array();
//        print_r($cates);
        foreach ($cates as $i => $j) {
            $arr[] = array(
                'title' => $j,
                'field' => $j,
                'width' => 20
            );
        }
        $leng = count($cates);
        $arr[$leng + 5] = array(
            'title' => '总计',
            'field' => 'total',
            'width' => 12
        );
        $arr[$leng + 6] = array(
            'title' => '状态',
            'field' => 's',
            'width' => 12
        );
        $arr[$leng + 7] = array(
            'title' => '创建时间',
            'field' => 'jftime',
            'width' => 20
        );
        $arr[$leng + 8] = array(
            'title' => '支付方式',
            'field' => 'paytype',
            'width' => 20
        );
        $arr[$leng + 9] = array(
            'title' => '支付时间',
            'field' => 'paytime',
            'width' => 20
        );
        foreach ($xqmembers as $key => $val) {
            $s = $val['status'] == '是' || $val['status'] == 1 ? '已缴费' : '未缴费';
            $xqmembers[$key]['s'] = $s;
//            $order = pdo_get('xcommunity_order',array('goodsid' => $val['id']),array('createtime'));
            $tsql = "select t1.createtime from" . tablename('xcommunity_order') . "t1 left join" . tablename('xcommunity_order_goods') . "t2 on t1.id=t2.orderid where t2.goodsid=:goodsid";
            $order = pdo_fetch($tsql, array(':goodsid' => $val['id']));
            $xqmembers[$key]['jftime'] = $order['createtime'] ? date('Y-m-d H:i', $order['createtime']) : 0;
            $xqmembers[$key]['paytime'] = $val['paytime'] ? date('Y-m-d H:i', $val['paytime']) : 0;
//            $category = pdo_get('xcommunity_category',array('regionid'=>$val['regionid']),array('name'));
//            if ($val['paytype'] == 1) {
//                $xqmembers[$key]['paytype'] = '线上';
//            } elseif ($val['paytype'] == 2) {
//                $xqmembers[$key]['paytype'] = '现金';
//            } elseif ($val['paytype'] == 3) {
//                $xqmembers[$key]['paytype'] = '刷卡';
//            } elseif ($val['paytype'] == 4) {
//                $xqmembers[$key]['paytype'] = '转账';
//            } else {
//                $xqmembers[$key]['paytype'] = '';
//            }
            $paytype = array('', '余额', '微信支付', '货到付款', '支付宝', '现金', '银联刷卡');
            $xqmembers[$key]['paytype'] = $paytype[$val['paytype']];
            $fee = explode('|', $val['fee']);
            foreach ($cates as $k => $v) {
                $xqmembers[$key][$v] = $fee[$k];
            }

        }
//        print_r($arr);exit();
//        print_r($xqmembers);exit();
        model_execl::export($xqmembers, array(
            "title"   => "费用数据-" . date('Y-m-d-H-i', time()),
            "columns" => $arr
        ));
    }
    if (intval($_GPC['regionid'])) {
        $condition .= "and t1.regionid=:regionid ";
        $params[':regionid'] = intval($_GPC['regionid']);
    }
    if ($user && $user[type] == 3) {
        //普通管理员
        $condition .= " and t1.regionid in({$user['regionid']})";
    }

    $pindex = max(1, intval($_GPC['page']));
    $psize = 10;
    $sql = "SELECT t1.*,t2.title FROM" . tablename('xcommunity_cost_list') . "t1 left join" . tablename('xcommunity_region') . "t2 on t1.regionid=t2.id where $condition ORDER BY t1.displayorder DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize;

    $list = pdo_fetchall($sql, $params);

    $total = pdo_fetchcolumn("SELECT COUNT(*) FROM" . tablename('xcommunity_cost_list') . "t1 where $condition", $params);
    $pager = pagination($total, $pindex, $psize);

    $sql = "select t1.id,t1.username,t5.title,t1.homenumber,t1.total,t5.linkway,t6.openid,t6.uid,t1.status from" . tablename('xcommunity_cost_list') . "t1 left join" . tablename('xcommunity_member_room') . "t2 on t1.homenumber=t2.address left join" . tablename('xcommunity_member_bind') . "t3 on t3.addressid= t2.id left join" . tablename('xcommunity_member') . "t4 on t4.id=t3.memberid left join" . tablename('xcommunity_region') . "t5 on t5.id=t1.regionid left join" . tablename('mc_mapping_fans') . "t6 on t6.uid=t4.uid where t1.uniacid =:uniacid and t1.status=2 and t6.openid!='' and t1.cid=:costid  and t2.regionid=:regionid and t1.regionid=:regionid and t4.regionid=:regionid ";
    $members = pdo_fetchall($sql, array(':uniacid' => $_W['uniacid'], ':costid' => $id, ':regionid' => intval($_GPC['regionid'])));
    $ttotal = count($members);
//    $ttotal = pdo_fetchcolumn($tsql,array(':uniacid'=>$_W['uniacid'],':costid'=>$id,':regionid'=>intval($_GPC['regionid'])));
    include $this->template('web/core/cost/detail');
}
/**
 * 修改缴费状态
 */
if ($op == 'setgoodsproperty') {
    $data = $_GPC['data'];
    if (empty($data)) {
        $data = '否';
    }
    $data = ($data == '是' ? '否' : '是');
    pdo_update("xcommunity_cost_list", array('status' => $data), array("id" => $id, "uniacid" => $_W['uniacid']));
    die(json_encode(array("result" => 1, "data" => $data)));

}
/**
 * 费用订单管理
 */
if ($op == 'order') {
    //物业订单
    $pindex = max(1, intval($_GPC['page']));
    $psize = 20;
    $condition = "t1.uniacid=:uniacid and t1.type='pfree'";
    $params[':uniacid'] = $_W['uniacid'];
    if (!empty($_GPC['keyword'])) {
        $condition .= " AND (t5.realname = :keyword or t5.mobile = :keyword or t3.homenumber = :keyword or t1.ordersn =:keyword)";
        $params[':keyword'] = $_GPC['keyword'];
    }
    $status = intval($_GPC['status']);
    if ($status == 1) {
        $condition .= " AND t1.status=1";
    }
    elseif ($status == 2) {
        $condition .= " AND t1.status=0";
    }
    $starttime = strtotime($_GPC['birth']['start']);
    if ($_GPC['birth']['end']) {
        $endtime = strtotime($_GPC['birth']['end']) + 86400 - 1;
    }
    if ($starttime && $endtime) {
        $condition .= " AND t1.createtime between '{$starttime}' and '{$endtime}'";
    }
    if ($user[type] == 3) {
        //普通管理员
        $condition .= " and t1.regionid in({$user['regionid']})";
    }
    else {
        if ($_GPC['regionid']) {
            $condition .= " and t1.regionid =:regionid";
            $params[':regionid'] = $_GPC['regionid'];
        }
    }
    if (intval($_GPC['id'])) {
        $condition .= " and t3.cid=:id";
        $params[':id'] = intval($_GPC['id']);
    }
    if ($_GPC['export'] == 1) {

        $ttsql = "select t1.price,t2.price as goodsprice,t1.credit,t1.createtime,t1.status,t1.transid,t1.id,t5.realname,t6.title,t1.ordersn,t4.address,t5.mobile,t1.transid,t1.paytype,t1.offsetprice,t3.username,t3.homenumber,t3.mobile as cost_mobile,t3.costtime from" . tablename('xcommunity_order') . "t1 left join" . tablename('xcommunity_order_goods') . "t2 on t1.id=t2.orderid left join" . tablename('xcommunity_cost_list') . "t3 on t2.goodsid = t3.id left join" . tablename('xcommunity_member_room') . "t4 on t4.id=t1.addressid left join" . tablename('mc_members') . "t5 on t1.uid=t5.uid left join" . tablename('xcommunity_region') . "t6 on t6.id=t1.regionid where $condition order by t1.createtime desc ";
        $li = pdo_fetchall($ttsql, $params);
        $paytype = array('', '余额', '微信支付', '货到付款', '支付宝', '现金', '银联刷卡');
        foreach ($li as $key => $value) {
            $li[$key]['cctime'] = date('Y-m-d H:i', $value['createtime']);
            $li[$key]['s'] = empty($value['status']) ? '未支付' : '已支付';
            $li[$key]['paytype'] = $paytype[$value['paytype']];
            $li[$key]['realname'] = $value['realname'] ? $value['realname'] : $value['username'];
            $li[$key]['address'] = $value['address'] ? $value['address'] : $value['homenumber'];
            $li[$key]['mobile'] = $value['mobile'] ? $value['mobile'] : $value['cost_mobile'];
        }
        model_execl::export($li, array(
            "title"   => "缴费订单-" . date('Y-m-d-H-i', time()),
            "columns" => array(
                array(
                    'title' => '订单号',
                    'field' => 'ordersn',
                    'width' => 30
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
                    'title' => '房号',
                    'field' => 'address',
                    'width' => 18
                ),
                array(
                    'title' => '费用时间',
                    'field' => 'costtime',
                    'width' => 30
                ),
                array(
                    'title' => '应缴费用',
                    'field' => 'goodsprice',
                    'width' => 12
                ),
                array(
                    'title' => '实缴费用',
                    'field' => 'price',
                    'width' => 12
                ),
                array(
                    'title' => '抵扣积分',
                    'field' => 'credit',
                    'width' => 12
                ),
                array(
                    'title' => '抵扣费用',
                    'field' => 'offsetprice',
                    'width' => 12
                ),
                array(
                    'title' => '微信订单号',
                    'field' => 'transid',
                    'width' => 30
                ),
                array(
                    'title' => '支付状态',
                    'field' => 's',
                    'width' => 18
                ),
                array(
                    'title' => '支付方式',
                    'field' => 'paytype',
                    'width' => 18
                ),
                array(
                    'title' => '时间',
                    'field' => 'cctime',
                    'width' => 22
                ),
            )
        ));
    }

    $sql = "select t1.price,t1.total,t2.price as goodsprice,t1.credit,t1.createtime,t1.status,t1.transid,t1.id,t5.realname,t6.title,t1.ordersn,t4.address,t1.paytype,t1.offsetprice,t3.id as costid,t3.username,t3.homenumber,t3.mobile as cost_mobile,t3.costtime from" . tablename('xcommunity_order') . "t1 left join" . tablename('xcommunity_order_goods') . "t2 on t1.id=t2.orderid left join" . tablename('xcommunity_cost_list') . "t3 on t2.goodsid = t3.id left join" . tablename('xcommunity_member_room') . "t4 on t4.id=t1.addressid left join" . tablename('mc_members') . "t5 on t1.uid=t5.uid left join" . tablename('xcommunity_region') . "t6 on t6.id=t1.regionid where $condition order by t1.createtime desc limit " . ($pindex - 1) * $psize . ',' . $psize;
//    echo $sql;exit();
    $list = pdo_fetchall($sql, $params);
    foreach ($list as $key => $value) {
        $list[$key]['cctime'] = date('Y-m-d H:i', $value['createtime']);
        $list[$key]['s'] = empty($value['status']) ? '未支付' : '已支付';
        $list[$key]['realname'] = $value['realname'] ? $value['realname'] : $value['username'];
        $list[$key]['address'] = $value['address'] ? $value['address'] : $value['homenumber'];
        $list[$key]['mobile'] = $value['mobile'] ? $value['mobile'] : $value['cost_mobile'];
    }
//    $tsql = "select count(distinct t1.id) from" . tablename('xcommunity_order') . "t1 left join" . tablename('xcommunity_order_goods') . "t2 on t1.id=t2.orderid left join" . tablename('xcommunity_cost_list') . "t3 on t2.goodsid = t3.id left join" . tablename('xcommunity_member_room') . "t4 on t4.id=t1.addressid left join" . tablename('mc_members') . "t5 on t1.uid=t5.uid left join" . tablename('xcommunity_region') . "t6 on t6.id=t1.regionid where $condition order by t1.createtime desc ";
    $tsql = "select count(*) from" . tablename('xcommunity_order') . "t1 left join" . tablename('xcommunity_order_goods') . "t2 on t1.id=t2.orderid left join" . tablename('xcommunity_cost_list') . "t3 on t2.goodsid = t3.id left join" . tablename('xcommunity_member_room') . "t4 on t4.id=t1.addressid left join" . tablename('mc_members') . "t5 on t1.uid=t5.uid left join" . tablename('xcommunity_region') . "t6 on t6.id=t1.regionid where $condition order by t1.createtime desc ";

    $total = pdo_fetchcolumn($tsql, $params);

    $pager = pagination($total, $pindex, $psize);

    include $this->template('web/core/cost/order');
}
/**
 * 费用订单管理--删除
 */
if ($op == 'del') {
    //物业费订单删除
    if ($_W['isajax']) {
        if (empty($id)) {
            exit('缺少参数');
        }
        $order = pdo_fetch("SELECT * FROM" . tablename('xcommunity_order') . "WHERE id=:id", array(':id' => $id));
        if (empty($order)) {
            exit('订单不存在');
        }
        $r = pdo_delete("xcommunity_order", array('id' => $id));
        util::permlog('', '删除缴费订单ID:' . $id);
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
 * 费用数据--编辑
 */
if ($op == 'edit') {
    //编辑用户
//	if (empty($id)) {
//		itoast('缺少参数',referer().'error');
//	}

    if ($id) {
        $item = pdo_fetch("SELECT * FROM" . tablename('xcommunity_cost_list') . "WHERE id=:id", array(':id' => $id));
        if (empty($item)) {
            itoast('数据不存在或已被删除', referer(), 'error', true);
        }
        $regionid = $item['regionid'];
    }
    else {
        $regionid = intval($_GPC['regionid']);
    }
//	echo $regionid;
    $category = pdo_fetch("SELECT * FROM" . tablename('xcommunity_category') . "WHERE regionid=:regionid AND type =7 AND uniacid=:uniacid", array(':regionid' => $regionid, ':uniacid' => $_W['uniacid']));
    $cate = explode('|', $category['name']);

    if ($item['fee']) {
        $fee = explode('|', $item['fee']);
    }
    if ($_W['isajax']) {
        $fee = implode("|", $_GPC['fee']);

        $data = array(
            'username'   => $_GPC['username'],
            'area'       => $_GPC['area'],
            'mobile'     => $_GPC['mobile'],
            'homenumber' => $_GPC['homenumber'],
            'costtime'   => $_GPC['costtime'],
            'total'      => $_GPC['total'],
            'fee'        => $fee,
            'address'    => strFilter($_GPC['homenumber'])
        );
        if ($id) {
            $r = pdo_update('xcommunity_cost_list', $data, array('id' => $id));
            // 修改的id添加超链接
            $url = $this->createWebUrl('cost', array('op' => 'edit', 'id' => $id, 'regionid' => $regionid, 'costid' => $item['cid'], 'cid' => $item['cid']));
            util::permlog('费用管理-修改', '修改用户费用ID:<a href="' . $url . '">' . $id . '</a>');
        }
        else {
            $data['uniacid'] = $_W['uniacid'];
            $data['regionid'] = $regionid;
            $data['cid'] = intval($_GPC['cid']);
            $data['status'] = 2;
            $data['createtime'] = TIMESTAMP;
            $r = pdo_insert('xcommunity_cost_list', $data);
            $id = pdo_insertid();
            // 修改的id添加超链接
            $url = $this->createWebUrl('cost', array('op' => 'edit', 'id' => $id, 'regionid' => $regionid, 'costid' => $data['cid'], 'cid' => $data['cid']));
            util::permlog('费用管理-添加', '添加用户费用ID:<a href="' . $url . '">' . $id . '</a>');
        }
        if ($r) {
            echo json_encode(array('status' => 1));
            exit();
        }
        else {
            echo json_encode(array('content' => '内容未修改或操作错误'));
            exit();
        }
    }
    include $this->template('web/core/cost/edit');
}
/**
 * 费用类型
 */
if ($op == 'category') {
    $operation = !empty($_GPC['operation']) ? $_GPC['operation'] : 'list';
    if ($operation == 'add') {
        //增加费用类型

        if ($id) {
            $item = pdo_fetch("SELECT * FROM" . tablename('xcommunity_category') . "WHERE id=:id AND uniacid=:uniacid", array(':id' => $id, ':uniacid' => $_W['uniacid']));
        }
        if ($_W['isajax']) {
            $data = array(
                'uniacid' => $_W['uniacid'],
                'type'    => 7,
                'name'    => $_GPC['name'],
            );
            $regionid = intval($_GPC['regionid']);
            if ($id) {
                pdo_update('xcommunity_category', $data, array('id' => $id));
            }
            else {
                $data['regionid'] = $regionid;
                $category = pdo_fetch("SELECT * FROM" . tablename('xcommunity_category') . "WHERE regionid=:regionid", array(':regionid' => $regionid));
                if ($category) {
                    echo json_encode(array('content' => '该小区费用类型已存在,无需在增加'));
                    exit();
                }
                pdo_insert('xcommunity_category', $data);
            }
            echo json_encode(array('status' => 1));
            exit();
        }
        include $this->template('web/core/cost/category/add');
    }
    elseif ($operation == 'list') {
        $pindex = max(1, intval($_GPC['page']));
        $psize = 10;
        $condition = '';
        if ($user[type] == 3) {
            $condition .= " and r.id in({$user['regionid']})";
        }
        $list = pdo_fetchall("SELECT c.*,r.title,r.city,r.dist FROM" . tablename('xcommunity_category') . "as c left join" . tablename('xcommunity_region') . "as r on c.regionid = r.id WHERE c.uniacid='{$_W['uniacid']}' AND c.type=7 $condition", $params);
        $total = pdo_fetchcolumn("SELECT COUNT(*) FROM" . tablename('xcommunity_category') . "as c left join" . tablename('xcommunity_region') . "as r on c.regionid = r.id WHERE c.uniacid='{$_W['uniacid']}' AND c.type=7 $condition", $params);
        $pager = pagination($total, $pindex, $psize);
        include $this->template('web/core/cost/category/list');
    }
    elseif ($operation == 'del') {
        if (empty($id)) {
            itoast('缺少参数', referer(), 'error', true);
        }
        $result = pdo_delete('xcommunity_category', array('id' => $id, 'uniacid' => $_W['uniacid']));
        itoast('删除成功', referer(), 'success', true);
    }
}
/**
 * 获取费用类型
 */
if ($op == 'ajax') {
    $regionid = intval($_GPC['regionid']);
    if ($regionid) {
        $cate = pdo_fetch("SELECT * FROM" . tablename('xcommunity_category') . "WHERE regionid=:regionid", array(':regionid' => $regionid));
        print_r(json_encode($cate));
        exit();
    }
    else {
        $cate = 0;
        print_r(json_encode($cate));
        exit();
    }
}
/**
 * 费用列表--改变状态
 */
if ($op == 'verify') {
    //审核用户
    $id = intval($_GPC['id']);
    $type = $_GPC['type'];
    $data = intval($_GPC['data']);
    if (in_array($type, array('status', 'open_status'))) {
        $data = ($data == 1 ? '0' : '1');
        pdo_update("xcommunity_cost", array($type => $data), array("id" => $id, "uniacid" => $_W['uniacid']));
        die(json_encode(array("result" => 1, "data" => $data)));
    }
}
/**
 * 费用小票打印
 */
if ($op == 'xqprint') {
    $id = intval($_GPC['id']);
    if ($id) {
        $item = pdo_get('xcommunity_cost_list', array('id' => $id), array('username', 'homenumber', 'total'));
        if (empty($item)) {
            itoast('非法访问', referer(), 'error', true);
            exit();
        }

        $api = set('d2');
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

            $type = set('d1') == 1 ? 'yl' : 'fy';
            $content = set('d1') == 1 ? $yl : $fy;
            xq_print::xqprint($type, 1, $content);
            echo json_encode(array('status' => 1, 'content' => '打印成功'));
            exit();

        }
        else {
            $type = set('x26', $_GPC['regionid']) == 1 ? 'yl' : 'fy';
            $content = set('x26', $_GPC['regionid']) == 1 ? $yl : $fy;
            xq_print::xqprint($type, 2, $content, $_GPC['regionid']);
            echo json_encode(array('status' => 1, 'content' => '打印成功'));
            exit();
        }
        util::permlog('', '打印缴费信息ID:' . $id);
    }
}
/**
 * 费用列表--设置支付期限
 */
if ($op == 'auth') {
    $id = intval($_GPC['id']);
    if ($id) {
        $item = pdo_get('xcommunity_cost', array('id' => $id), array('authtime', 'auth'));
        $authtime = !empty($item['authtime']) ? date('Y-m-d H:i', $item['authtime']) : date('Y-m-d', TIMESTAMP);
    }

    if (checksubmit('submit')) {
        if (pdo_update('xcommunity_cost', array('authtime' => strtotime($_GPC['authtime']), 'auth' => intval($_GPC['auth'])), array('id' => $id))) {
            itoast('修改成功', referer(), 'success');
        }
    }

    include $this->template('web/core/cost/auth');
}
/**
 * 费用数据--微信通知
 */
if ($op == 'wxsend') {
    //物业费微信通知提醒
    $condition = "t1.uniacid =:uniacid and t1.status=2 ";
    $params[':uniacid'] = $_W['uniacid'];
    $cids = $_GPC['cid'];
    if ($cids) {
        $costids = implode(',', $cids);
        $condition .= " and t1.id in({$costids})";
    }
    $costid = intval($_GPC['costid']);
    if ($costid) {
        $condition .= ' and t1.cid=:costid ';
        $params[':costid'] = $costid;
    }
    if (intval($_GPC['regionid'])) {
        $condition .= " and t2.regionid=:regionid and t1.regionid=:regionid and t4.regionid=:regionid";
        $params[':regionid'] = intval($_GPC['regionid']);
    }

    $pindex = max(1, intval($_GPC['page']));
    $psize = 5;

    $sql = "select t1.id,t1.username,t5.title,t1.homenumber,t1.total,t5.linkway,t6.openid,t6.uid,t1.status from" . tablename('xcommunity_cost_list') . "t1 left join" . tablename('xcommunity_member_room') . "t2 on t1.homenumber=t2.address left join" . tablename('xcommunity_member_bind') . "t3 on t3.addressid= t2.id left join" . tablename('xcommunity_member') . "t4 on t4.id=t3.memberid left join" . tablename('xcommunity_region') . "t5 on t5.id=t1.regionid left join" . tablename('mc_mapping_fans') . "t6 on t6.uid=t4.uid where $condition LIMIT " . ($pindex - 1) * $psize . ',' . $psize;

    $members = pdo_fetchall($sql, $params);

    $tsql = "select t1.id,t1.regionid,t1.username,t5.title,t1.homenumber,t1.total,t5.linkway,t6.openid,t6.uid,t1.status from" . tablename('xcommunity_cost_list') . "t1 left join" . tablename('xcommunity_member_room') . "t2 on t1.homenumber=t2.address left join" . tablename('xcommunity_member_bind') . "t3 on t3.addressid= t2.id left join" . tablename('xcommunity_member') . "t4 on t4.id=t3.memberid left join" . tablename('xcommunity_region') . "t5 on t5.id=t1.regionid left join" . tablename('mc_mapping_fans') . "t6 on t6.uid=t4.uid where $condition";
    $ttotal = pdo_fetchall($tsql, $params);
    $total = count($ttotal);
    if (set('t11')) {
        $record = intval($_GPC['record']) ? intval($_GPC['record']) : 0;
        $ok = intval($_GPC['ok']) ? intval($_GPC['ok']) : 0;
        $fail = intval($_GPC['fail']) ? intval($_GPC['fail']) : 0;
        if (!empty($members)) {
            foreach ($members as $key => $val) {
                $record++;//已发送记录

                $url = $_W['siteroot'] . "app/index.php?i={$_W['uniacid']}&c=entry&op=detail&do=cost&id={$val['id']}&regionid={$val['regionid']}&m=" . $this->module['name'];
                $tplid = set('t12');
                $content = array(
                    'first'    => array(
                        'value' => '您好，您的物业费已出。',
                    ),
                    'userName' => array(
                        'value' => $val['username'],
                    ),
                    'address'  => array(
                        'value' => $val['title'] . $val['homenumber'],
                    ),
                    'pay'      => array(
                        'value' => $val['total'] . '元',
                    ),
                    'remark'   => array(
                        'value' => '请尽快缴纳，如有疑问，请咨询.' . $val['linkway'],
                    ),
                );
                if (!empty($val['openid'])) {
//                    $ret = util::sendTplNotice($v['openid'], $tplid, $content, $url, '');
                    $account_api = WeAccount::create();
                    $status = $account_api->sendTplNotice($val['openid'], $tplid, $content, $url);
                    $d = array(
                        'uniacid'    => $_W['uniacid'],
                        'sendid'     => $val['id'],
                        'uid'        => $val['uid'],
                        'type'       => 3,
                        'cid'        => 1,
                        'createtime' => TIMESTAMP
                    );
                    if ($status == 1) {
                        $d['status'] = 1;
                        $ok++;//成功发送
                        pdo_insert('xcommunity_send_log', $d);
                    }
                    else {
                        $d['status'] = 2;
                        $fail++;//失败发送
                        pdo_insert('xcommunity_send_log', $d);
                    }
                }
//            }


            }
        }

    }
    if ($ok >= $total || empty($members)) {
        echo json_encode(array('status' => 'end', 'fail' => $fail, 'ok' => $ok, 'record' => $record));
        exit();
    }
    else {
        echo json_encode(array('fail' => $fail, 'ok' => $ok, 'record' => $record));
        exit();
    }

    util::permlog('', '批量微信发送费用' . ',费用列表ID:' . $id);

}
/**
 * 费用管理--基本设置
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
            }
            else {
                pdo_insert('xcommunity_setting', $data);
            }
        }
        itoast('操作成功', referer(), 'success', ture);
    }
    $set = pdo_getall('xcommunity_setting', array('uniacid' => $_W['uniacid']), array(), 'xqkey', array());
    include $this->template('web/core/cost/set');
}
/**
 * 费用管理--接收员
 */
if ($op == 'wechat') {
    if ($p == 'list') {
        $condition = " t1.uniacid=:uniacid ";
        $parms[':uniacid'] = $_W['uniacid'];
        if ($user) {
            if ($user['uuid']) {
                //判断上级管理员是否是商家管理员
                $suser = pdo_get("xcommunity_users", array('uid' => $user['uuid']), array());
                $uid = $suser['type'] == 5 ? $user['uuid'] : $_W['uid'];
                $condition .= " and t1.uid = {$uid}";
            }
            else {
                $condition .= " and t1.uid = {$_W['uid']}";
            }
        }
        $pindex = max(1, intval($_GPC['page']));
        $psize = 20;
        $sql = "SELECT t1.* FROM" . tablename('xcommunity_cost_wechat') . "t1 WHERE $condition order by t1.id desc LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
        $list = pdo_fetchall($sql, $parms);
        $total = pdo_fetchcolumn("SELECT COUNT(*) FROM" . tablename('xcommunity_cost_wechat') . "t1 WHERE $condition", $parms);
        $pager = pagination($total, $pindex, $psize);
        include $this->template('web/core/cost/wechat/list');
    }
    elseif ($p == 'add') {
        $regionids = '[]';
        $id = intval($_GPC['id']);
        if ($id) {
            $item = pdo_get('xcommunity_cost_wechat', array('id' => $id), array());
            $regs = pdo_getall('xcommunity_cost_wechat_region', array('cid' => $id), array('regionid'));
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
                $birth['province'] = '';
            }
            else {
                if (empty($birth['province'])) {
                    echo json_encode(array('content' => '必须选择省市区和小区'));
                    exit();
                }
            }
            $data = array(
                'uniacid'  => $_W['uniacid'],
                'enable'   => $_GPC['enable'],
                'type'     => $_GPC['type'],
                'realname' => trim($_GPC['realname']),
                'mobile'   => trim($_GPC['mobile']),
                'openid'   => trim($_GPC['openid']),
                'province' => $birth['province'],
                'city'     => $birth['city'],
                'dist'     => $birth['district'],
            );
            if ($id) {
                pdo_update('xcommunity_cost_wechat', $data, array('id' => $id));
                pdo_delete('xcommunity_cost_wechat_region', array('cid' => $id));
            }
            else {
                $data['uid'] = $_W['uid'];
                pdo_insert('xcommunity_cost_wechat', $data);
                $id = pdo_insertid();
            }
            if ($allregion == 1) {
                $regions = model_region::region_fetall();
                foreach ($regions as $k => $v) {
                    $dat = array(
                        'cid'      => $id,
                        'regionid' => $v['id'],
                    );
                    pdo_insert('xcommunity_cost_wechat_region', $dat);
                }
            }
            else {
                $regionids = explode(',', $_GPC['regionids']);
                foreach ($regionids as $key => $value) {
                    $dat = array(
                        'cid'      => $id,
                        'regionid' => $value,
                    );
                    pdo_insert('xcommunity_cost_wechat_region', $dat);
                }
            }
            echo json_encode(array('status' => 1));
            exit();
        }
        include $this->template('web/core/cost/wechat/add');
    }
    elseif ($p == 'del') {
        $notice = pdo_get('xcommunity_cost_wechat', array('id' => $id), array());
        if ($notice) {
            if (pdo_delete('xcommunity_cost_wechat', array('id' => $id))) {
                pdo_delete('xcommunity_cost_wechat_region', array('cid' => $id));
                util::permlog('费用接收员-删除', '信息标题:' . $notice['realname']);
                $result = array(
                    'status' => 1,
                );
                echo json_encode($result);
                exit();
            }
        }
    }
    elseif ($p == 'verify') {
        //审核用户
        $id = intval($_GPC['id']);
        $type = $_GPC['type'];
        $data = intval($_GPC['data']);
        if (in_array($type, array('enable'))) {
            $data = ($data == 0 ? '1' : '0');
            pdo_update("xcommunity_cost_wechat", array($type => $data), array("id" => $id, "uniacid" => $_W['uniacid']));
            die(json_encode(array("result" => 1, "data" => $data)));
        }
    }
}
/**
 * 费用数据--短信通知
 */
if ($op == 'smssend') {
    //物业费短信通知提醒
    $condition = "t1.uniacid =:uniacid and t1.status=2 ";
    $params[':uniacid'] = $_W['uniacid'];
    $cids = $_GPC['cid'];
    if ($cids) {
        $costids = implode(',', $cids);
        $condition .= " and t1.id in({$costids})";
    }
    $costid = intval($_GPC['costid']);
    if ($costid) {
        $condition .= ' and t1.cid=:costid ';
        $params[':costid'] = $costid;
    }
    if (intval($_GPC['regionid'])) {
        $condition .= " and t2.regionid=:regionid and t1.regionid=:regionid and t4.regionid=:regionid";
        $params[':regionid'] = intval($_GPC['regionid']);
    }

    $pindex = max(1, intval($_GPC['page']));
    $psize = 5;

    $sql = "select t1.id,t1.costtime,t1.username,t5.title,t1.homenumber,t1.mobile,t1.total,t5.linkway,t6.openid,t6.uid,t1.status from" . tablename('xcommunity_cost_list') . "t1 left join" . tablename('xcommunity_member_room') . "t2 on t1.homenumber=t2.address left join" . tablename('xcommunity_member_bind') . "t3 on t3.addressid= t2.id left join" . tablename('xcommunity_member') . "t4 on t4.id=t3.memberid left join" . tablename('xcommunity_region') . "t5 on t5.id=t1.regionid left join" . tablename('mc_mapping_fans') . "t6 on t6.uid=t4.uid where $condition LIMIT " . ($pindex - 1) * $psize . ',' . $psize;

    $members = pdo_fetchall($sql, $params);
    $tsql = "select t1.id,t1.costtime,t1.username,t5.title,t1.homenumber,t1.total,t5.linkway,t6.openid,t6.uid,t1.status from" . tablename('xcommunity_cost_list') . "t1 left join" . tablename('xcommunity_member_room') . "t2 on t1.homenumber=t2.address left join" . tablename('xcommunity_member_bind') . "t3 on t3.addressid= t2.id left join" . tablename('xcommunity_member') . "t4 on t4.id=t3.memberid left join" . tablename('xcommunity_region') . "t5 on t5.id=t1.regionid left join" . tablename('mc_mapping_fans') . "t6 on t6.uid=t4.uid where $condition";
    $ttotal = pdo_fetchall($tsql, $params);
    $total = count($ttotal);


    $record = intval($_GPC['record']) ? intval($_GPC['record']) : 0;
    $ok = intval($_GPC['ok']) ? intval($_GPC['ok']) : 0;
    $fail = intval($_GPC['fail']) ? intval($_GPC['fail']) : 0;
    if (!empty($members)) {
        foreach ($members as $key => $val) {
            $record++;//已发送记录

            if (set('s1')) {
                $type = set('s1');
                if ($type == 1) {
                    $type = 'wwt';
                }
                elseif ($type == 2) {
                    $type = 'juhe';
                    $tpl_id = set('s11');
                }
                else {
                    $type = 'aliyun_new';
                    $tpl_id = set('s23');
                }
                $api = 1;
            }
            else {
                $type = set('x21', $val['regionid']);
                if ($type == 1) {
                    $type = 'wwt';
                }
                elseif ($type == 2) {
                    $type = 'juhe';
                    $tpl_id = set('x36', $val['regionid']);
                }
                else {
                    $type = 'aliyun_new';
                    $tpl_id = set('x71', $val['regionid']);
                }
                $api = 2;
                $d['regionid'] = $val['regionid'];
            }
            if ($type == 'wwt') {
                $smsg = "您好,您本月物业费已出。物业费金额" . $val['total'] . "。请尽快缴纳，如有疑问，请咨询。" . $val['linkway'];
            }
            elseif ($type == 'juhe') {
                $phone = $val['linkway'];
                $price = $val['total'];
                $costtime = $val['costtime'];
                $smsg = urlencode("#price#=$price&#costtime#=$costtime&#mobile#=$phone");
            }
            else {
                $smsg = json_encode(array('price' => $val['total'], 'costtime' => $val['costtime'], 'tel' => $val['linkway']));
            }

            if ($val['mobile']) {

                $resp = sms::send($val['mobile'], $smsg, $type, $val['regionid'], $api, $tpl_id);

            }

            $d = array(
                'uniacid'    => $_W['uniacid'],
                'sendid'     => $val['id'],
                'uid'        => $val['uid'],
                'type'       => 3,
                'cid'        => 2,
                'createtime' => TIMESTAMP
            );
            if ($resp['status'] == 1) {
                $d['status'] = 1;
                $ok++;//成功发送
                pdo_insert('xcommunity_send_log', $d);
            }
            else {
                $d['status'] = 2;
                $fail++;//失败发送
                pdo_insert('xcommunity_send_log', $d);
            }


        }
    }


    if ($ok > $total || empty($members)) {
        echo json_encode(array('status' => 'end', 'fail' => $fail, 'ok' => $ok, 'record' => $record));
        exit();
    }
    else {
        echo json_encode(array('fail' => $fail, 'ok' => $ok, 'record' => $record));
        exit();
    }

    util::permlog('', '批量短信发送费用' . ',费用列表ID:' . $id);
}
/**
 * 费用数据的单据打印
 */
if ($op == 'print') {
    $id = intval($_GPC['id']);
    if (empty($id)) {
        itoast('缺少参数');
    }
    if ($id) {
        $item = pdo_fetch("SELECT t1.*,t2.title as rtitle,t3.title as ptitle,t2.stamp FROM" . tablename('xcommunity_cost_list') . "t1 left join" . tablename('xcommunity_region') . "t2 on t1.regionid=t2.id left join" . tablename('xcommunity_property') . "t3 on t2.pid=t3.id WHERE t1.id=:id", array(':id' => $id));
        if (empty($item)) {
            itoast('数据不存在或已被删除', referer(), 'error', true);
        }
        $regionid = $item['regionid'];
        $category = pdo_fetch("SELECT * FROM" . tablename('xcommunity_category') . "WHERE regionid=:regionid AND type =7 AND uniacid=:uniacid", array(':regionid' => $regionid, ':uniacid' => $_W['uniacid']));
        $cate = explode('|', $category['name']);
        $num = count($cate);
        if ($item['fee']) {
            $fee = explode('|', $item['fee']);
        }
        for ($j = 0; $j < count($cate); $j++) {
            if ($fee[$j] == '0.00') {
                $num--;
                unset($fee[$j]);
                unset($cate[$j]);
            }
        }
        $table = '<tr style="height: 23px"><td></td><td></td><td></td><td></td><td></td></tr>';
        $tab = '';
        if ($num < 10) {
            for ($i = 0; $i < (10 - $num); $i++) {
                $tab .= $table;
            }
        }
    }
//    $item['stamp'] = 'background:url(' . tomedia($item['stamp']) . ')';
    $item['no'] = sprintf("%010d", $id);
    $item['dprice'] = num_to_rmb($item['total']);
    include $this->template('web/core/cost/print');
}
/**
 * 费用管理--数据统计
 */
if ($op == 'data') {
    $pindex = max(1, intval($_GPC['page']));
    $psize = 20;
    $condition['uniacid'] = $_W['uniacid'];
    if ($user && $user[type] == 2) {
        //普通管理员
        $condition['uid'] = $_W['uid'];
    }
    if ($user && $user[type] == 3) {
        //普通管理员
        $user_region = explode(',', $user['regionid']);
        $condition['id'] = $user_region;
    }
    else {
        if ($_GPC['regionid']) {
            $condition['id'] = $_GPC['regionid'];
        }
    }
    $condi['uniacid'] = $_W['uniacid'];
    $condi['status'] = 1;
    $starttime = strtotime($_GPC['birth']['start']);
    $endtime = strtotime($_GPC['birth']['end']);
    $stime = strtotime(date('Y-m-d', TIMESTAMP));
    $etime = strtotime(date('Y-m-d', TIMESTAMP)) + 86400 - 1;
    if (!empty($starttime)) {
        $endtime = $endtime + 86400 - 1;
    }
    if ($starttime && $endtime) {
        $condi['createtime >='] = $starttime;
        $condi['createtime <='] = $endtime;
    }
    else {
        $condi['createtime >='] = $stime;
        $condi['createtime <='] = $etime;
    }
    $condi['type'] = 'pfree';
    $orders = pdo_getall('xcommunity_order', $condi, array());
    if ($_GPC['export'] == 1) {
        $list1 = array();
        $regions = pdo_getall('xcommunity_region', $condition, array('title', 'id'));
        foreach ($regions as $key => $val) {
            $credit_total = 0;
            $wechat_total = 0;
            $alipay_total = 0;
            $cash_total = 0;
            $card_total = 0;
            $offset_total = 0;
            $credit1_total = 0;
            $ztotal = 0;
            foreach ($orders as $k => $v) {
                if ($v['regionid'] == $val['id']) {
                    if ($v['paytype'] == 1) {
                        $credit_total += $v['price'];
                    }
                    elseif ($v['paytype'] == 2) {
                        $wechat_total += $v['price'];
                    }
                    elseif ($v['paytype'] == 4) {
                        $alipay_total += $v['price'];
                    }
                    elseif ($v['paytype'] == 5) {
                        $cash_total += $v['price'];
                    }
                    elseif ($v['paytype'] == 6) {
                        $card_total += $v['price'];
                    }
                    $offset_total += $v['offsetprice'];
                    $credit1_total += $v['credit'];
                    $ztotal += $v['price'];
                }
            }
            $list1[] = array(
                'title'         => $val['title'],
                'credit_total'  => sprintf("%.2f", $credit_total),
                'wechat_total'  => sprintf("%.2f", $wechat_total),
                'alipay_total'  => sprintf("%.2f", $alipay_total),
                'cash_total'    => sprintf("%.2f", $cash_total),
                'card_total'    => sprintf("%.2f", $card_total),
                'ztotal'        => sprintf("%.2f", $ztotal),
                'offset_total'  => sprintf("%.2f", $offset_total),
                'credit1_total' => sprintf("%.2f", $credit1_total)
            );
        }
        model_execl::export($list1, array(
            "title"   => "费用数据统计-" . date('Y-m-d-H-i', time()),
            "columns" => array(
                array(
                    'title' => '小区名称',
                    'field' => 'title',
                    'width' => 10
                ),
                array(
                    'title' => '支付宝',
                    'field' => 'alipay_total',
                    'width' => 12
                ),
                array(
                    'title' => '微信',
                    'field' => 'wechat_total',
                    'width' => 12
                ),
                array(
                    'title' => '现金',
                    'field' => 'cash_total',
                    'width' => 12
                ),
                array(
                    'title' => '余额',
                    'field' => 'credit_total',
                    'width' => 12
                ),
                array(
                    'title' => '银联刷卡',
                    'field' => 'card_total',
                    'width' => 12
                ),
                array(
                    'title' => '积分抵扣数量',
                    'field' => 'credit1_total',
                    'width' => 12
                ),
                array(
                    'title' => '积分抵扣费用',
                    'field' => 'offset_total',
                    'width' => 12
                ),
                array(
                    'title' => '总计',
                    'field' => 'ztotal',
                    'width' => 12
                ),
            )
        ));
    }
    $regions = pdo_getslice('xcommunity_region', $condition, array($pindex, $psize), $total, '', '', array('id desc'));
    $list = array();
    foreach ($regions as $key => $val) {
        $credit_total = 0;
        $wechat_total = 0;
        $alipay_total = 0;
        $cash_total = 0;
        $card_total = 0;
        $offset_total = 0;
        $credit1_total = 0;
        $ztotal = 0;
        foreach ($orders as $k => $v) {
            if ($v['regionid'] == $val['id']) {
                if ($v['paytype'] == 1) {
                    $credit_total += $v['price'];
                }
                elseif ($v['paytype'] == 2) {
                    $wechat_total += $v['price'];
                }
                elseif ($v['paytype'] == 4) {
                    $alipay_total += $v['price'];
                }
                elseif ($v['paytype'] == 5) {
                    $cash_total += $v['price'];
                }
                elseif ($v['paytype'] == 6) {
                    $card_total += $v['price'];
                }
                $offset_total += $v['offsetprice'];
                $credit1_total += $v['credit'];
                $ztotal += $v['price'];
            }
        }
        $list[] = array(
            'title'         => $val['title'],
            'credit_total'  => $credit_total,
            'wechat_total'  => $wechat_total,
            'alipay_total'  => $alipay_total,
            'cash_total'    => $cash_total,
            'card_total'    => $card_total,
            'ztotal'        => $ztotal,
            'offset_total'  => $offset_total,
            'credit1_total' => $credit1_total
        );
    }
    $pager = pagination($total, $pindex, $psize);
    include $this->template('web/core/cost/data_list');
}
/**
 * 费用数据的催缴打印
 */
if ($op == 'call') {
    $id = intval($_GPC['id']);
    if (empty($id)) {
        itoast('缺少参数');
    }
    if ($id) {
        $item = pdo_fetch("SELECT t1.*,t2.title as rtitle,t3.title as ptitle,t2.stamp FROM" . tablename('xcommunity_cost_list') . "t1 left join" . tablename('xcommunity_region') . "t2 on t1.regionid=t2.id left join" . tablename('xcommunity_property') . "t3 on t2.pid=t3.id WHERE t1.id=:id", array(':id' => $id));
        if (empty($item)) {
            itoast('数据不存在或已被删除', referer(), 'error', true);
        }
        $regionid = $item['regionid'];
        $category = pdo_fetch("SELECT * FROM" . tablename('xcommunity_category') . "WHERE regionid=:regionid AND type =7 AND uniacid=:uniacid", array(':regionid' => $regionid, ':uniacid' => $_W['uniacid']));
        $cate = explode('|', $category['name']);
        $num = count($cate);
        if ($item['fee']) {
            $fee = explode('|', $item['fee']);
        }
        for ($j = 0; $j < count($cate); $j++) {
            if ($fee[$j] == '0.00') {
                $num--;
                unset($fee[$j]);
                unset($cate[$j]);
            }
        }
        $table = '<tr style="height: 23px"><td></td><td></td><td></td><td></td></tr>';
        $tab = '';
        if ($num < 10) {
            for ($i = 0; $i < (10 - $num); $i++) {
                $tab .= $table;
            }
        }
    }
//    $item['stamp'] = 'background:url(' . tomedia($item['stamp']) . ')';
    $item['no'] = sprintf("%010d", $id);
    $item['dprice'] = num_to_rmb($item['total']);
    include $this->template('web/core/cost/call');
}