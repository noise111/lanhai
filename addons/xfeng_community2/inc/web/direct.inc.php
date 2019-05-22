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
$op = !empty($_GPC['op']) ? $_GPC['op'] : 'goods';
$p = !empty($_GPC['p']) ? $_GPC['p'] : 'list';
$operation = !empty($_GPC['operation']) ? $_GPC['operation'] : 'list';
$user = util::xquser($_W['uid']);
$regions = model_region::region_fetall();
$id = intval($_GPC['id']);
if ($op == 'goods') {
    //商品管理
    $category = util::fetchall_category(10);
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
    $condition = " uniacid=:uniacid and type=2 ";
    $params[':uniacid'] =  $_W['uniacid'];
    if ($user){
        if($user['uuid']){
            //判断上级管理员是否是商家管理员
            $suser = pdo_get("xcommunity_users",array('uid'=>$user['uuid']),array());
            $uid = $suser['type'] == 4 ||  $suser['type'] == 1 ? $user['uuid']:$_W['uid'];
            $condition .= " and uid = {$uid}";
        }else{
            $condition .= " and uid = {$_W['uid']}";
        }
    }
    $shops = pdo_fetchall("select * from".tablename('xcommunity_shop')."where $condition",$params);
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
        if ($_W['ispost']) {
            if (empty($_GPC['goodsname'])) {
                itoast('请输入商品名称！');
            }
            if (empty($_GPC['thumbs'])) {
                $_GPC['thumbs'] = array();
            }
            $birth = $_GPC['birth'];
            $allregion = intval($_GPC['allregion']);
            if ($allregion == 1){
                $birth['province'] = '';
            }else{
                if(empty($birth['province'])){
                    itoast('必须选择省市区和小区',referer(),'error');exit();
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
                'type'         => 3,
                'province'     => $birth['province'],
                'city'         => $birth['city'],
                'dist'         => $birth['district'],
                'recommand'    => intval($_GPC['recommand']),
                'wlinks'        => $_GPC['wlinks'],
                'shopid'        => intval($_GPC['shopid'])
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
                    $data['uid'] = $suser['type'] == 4 ||  $suser['type'] == 1 ? $suser['uid'] : $_W['uid'];
                }
                else {
                    $data['uid'] = $_W['uid'];
                }
                pdo_insert('xcommunity_goods', $data);
                $id = pdo_insertid();
                util::permlog('直通车商品-添加', '信息标题:' . $data['title']);
            }
            else {
                unset($data['createtime']);
                pdo_update('xcommunity_goods', $data, array('id' => $id));
                pdo_delete('xcommunity_goods_region', array('gid' => $id));
                util::permlog('直通车商品-修改', '信息标题:' . $data['title']);
            }
            if ($allregion == 1){
                $regions = model_region::region_fetall();
                foreach ($regions as $k => $v){
                    $dat = array(
                        'gid' => $id,
                        'regionid' => $v['id'],
                    );
                    pdo_insert('xcommunity_goods_region', $dat);
                }
            }else {
                $regionids = explode(',', $_GPC['regionids']);
                foreach ($regionids as $key => $value) {
                    $dat = array(
                        'gid' => $id,
                        'regionid' => $value,
                    );
                    pdo_insert('xcommunity_goods_region', $dat);
                }
            }
            itoast('商品更新成功！', referer(), 'success', ture);
        }
        load()->func('tpl');
        include $this->template('web/plugin/direct/goods/add');
    }
    elseif ($operation == 'list') {
        if (checksubmit('delete')) {
            $ids = $_GPC['ids'];
            if (!empty($ids)) {
                foreach ($ids as $key => $id) {
                    pdo_delete('xcommunity_goods', array('id' => $id));
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
        $psize = 15;
        $condition = '';
        if (!empty($_GPC['keyword'])) {
            $condition .= " AND title LIKE '%{$_GPC['keyword']}%'";
        }
        $pcate = intval($_GPC['category']['parentid']);
        $ccate = intval($_GPC['category']['childid']);
        if (!empty($pcate)) {
//            $cid = intval($_GPC['cate_1']);
//            $condition .= " AND pcate = '{$cid}'";
            $condition .= " and pcate={$pcate} ";
        }
        if (!empty($ccate)) {
            $condition .= " and child={$ccate}";
        }
        if (isset($_GPC['status']) && $_GPC['status'] != 3) {
            $condition .= " AND status = '" . intval($_GPC['status']) . "'";
        }
        if ($user) {
            //普通管理员
//            if ($user['uuid'] == 1){
//                $condition .= " and uid = {$_W['uid']}";
//            }else{
//                $condition .= " and uid = {$user['uuid']}";
//            }
            if ($user['uuid']) {
                //判断上级管理员是否是超市
                $suser = pdo_get("xcommunity_users", array('uid' => $user['uuid']), array());
                $uid = $suser['type'] == 4 ||  $suser['type'] == 1  ? $user['uuid'] : $_W['uid'];
                $condition .= " and uid = {$uid}";
            }
            else {
                $condition .= " and uid = {$_W['uid']}";
            }
        }
        $list = pdo_fetchall("SELECT * FROM " . tablename('xcommunity_goods') . " WHERE uniacid = '{$_W['uniacid']}' AND type = 3 $condition ORDER BY id DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
        foreach ($list as $k => $v){
            $stitle = pdo_getcolumn('xcommunity_shop',array('id' => $v['shopid']),'title');
            $list[$k]['stitle'] = $stitle;
        }
        $total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('xcommunity_goods') . " WHERE uniacid = '{$_W['uniacid']}' AND type = 3 $condition");
        $pager = pagination($total, $pindex, $psize);

        include $this->template('web/plugin/direct/goods/list');
    }
    elseif ($operation == 'delete') {

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

        pdo_delete('xcommunity_goods', array('id' => $id));
        util::permlog('直通车商品-删除', '信息标题:' . $row['title']);
        itoast('删除成功！', referer(), 'success', ture);
    }
}
elseif ($op == 'setgoodsproperty') {
    $type = $_GPC['type'];
    $data = intval($_GPC['data']);
    if (in_array($type, array('isrecommand', 'status'))) {
        $data = ($data == 1 ? '0' : '1');
        pdo_update("xcommunity_goods", array($type => $data), array("id" => $id, "uniacid" => $_W['uniacid']));
        die(json_encode(array("result" => 1, "data" => $data)));
    }
    die(json_encode(array("result" => 0)));
}
elseif ($op == 'order') {
    load()->func('tpl');
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

        $condition = " t1.uniacid = :uniacid and t1.type='direct' and t1.enable=1";
        $paras[':uniacid'] = $_W['uniacid'];
        if ($user) {
            if ($user['uuid']) {
                //判断上级管理员是否是超市
                $suser = pdo_get("xcommunity_users", array('uid' => $user['uuid']), array());
                $uid = $suser['type'] == 4 ? $suser['uid'] : $_W['uid'];
                $condition .= " and t6.uid=:uid";
                $paras[':uid'] = $uid;

            }
            else {
                $condition .= " and t6.uid=:uid";
                $paras[':uid'] = $_W['uid'];
            }
        }

//        if ($user && $user[type] != 1) {
//            //普通管理员
//            if ($user['uuid'] == 1){
//                $condition .= " AND t6.uid=:uid";
//                $paras[':uid'] = $_W['uid'];
//            }else{
//                $condition .= " AND t6.uid=:uid";
//                $paras[':uid'] = $user['uuid'];
//            }
//        }
//        if($user['type'] == 3){
//            $condition .=" and t3.regionid in({$user['regionid']})";
//        }
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
            if ($_GPC['paytype'] == 5) {
                $condition .= " AND t1.paytype = 0";
            }
            else {
                $condition .= " AND t1.paytype = '{$_GPC['paytype']}'";
            }

        }

//        if (!empty($_GPC['keyword'])) {
//            $condition .= " AND t1.ordersn LIKE '%{$_GPC['keyword']}%'";
//        }
        if (!empty($_GPC['keyword'])) {
            $condition .= " AND (t1.ordersn LIKE '%{$_GPC['keyword']}%' or t3.realname LIKE '%{$_GPC['keyword']}%' or t3.mobile LIKE '%{$_GPC['keyword']}%')";
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
                if ($val['paytype'] == 2) {
                    if (empty($val['transid'])) {
                        $xqlist[$k]['paytype'] = '支付宝支付';
                        // $value['paytype'] = '支付宝支付';
                    }
                    else {
                        $xqlist[$k]['paytype'] = '微信支付';
                        // $value['paytype'] = '微信支付';
                    }
                }
                else {
                    $xqlist[$k]['paytype'] = $paytype[$val['paytype']]['name'];
                    // $value['paytype'] = $paytype[$value['paytype']]['name'];
                }
                $xqlist[$k]['ordersn'] = chunk_split($val['ordersn']);


            }

            model_execl::export($xqlist, array(
                "title"   => "超市订单数据-" . date('Y-m-d-H-i', time()),
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
        if($user){
            $sql = "select distinct t1.id,t1.createtime,t1.*,t4.realname as address_realname,t4.mobile as address_telephone,t2.realname,t2.mobile,t5.price as totalprice,t5.total from" . tablename('xcommunity_order') . "t1 left join" . tablename('xcommunity_member_address') . "t2 on t1.addressid=t2.id left join" . tablename('mc_members') . "t4 on t4.uid= t1.uid left join" . tablename('xcommunity_order_goods') . "t5 on t5.orderid= t1.id left join" . tablename('xcommunity_goods') . "t6 on t6.id=t5.goodsid where $condition order by t1.createtime desc LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
        }else{
            $sql = "select t1.*,t3.realname,t3.mobile from" . tablename('xcommunity_order') . "t1 left join" . tablename('mc_members') . "t3 on t3.uid= t1.uid where $condition order by t1.createtime desc LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
        }

        $list = pdo_fetchall($sql, $paras);

        $paytype = array(
            '1' => array('css' => 'danger', 'name' => '余额支付'),
            '2' => array('css' => 'info', 'name' => '在线支付'),
            '3' => array('css' => 'warning', 'name' => '货到付款'),
            '4' => array('css' => 'info', 'name' => '后台支付'),
            '5' => array('css' => 'default', 'name' => '未支付'),
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
            // $value['statuscss'] = $orderstatus[$value['status']]['css'];
            // $value['status'] = $orderstatus[$value['status']]['name'];
//            if ($s < 1) {
//                $list[$key]['css'] = $paytype[$s]['css'];
//                if ($value['paytype'] == 0) {
//                    $t = 5;
//                    $list[$key]['paytype'] = $paytype[$t]['name'];
//                } else {
//                    $list[$key]['paytype'] = $paytype[$s]['name'];
//                }
//
//                // $value['css'] = $paytype[$s]['css'];
//                // $value['paytype'] = $paytype[$s]['name'];
//                continue;
//            }
            $list[$key]['css'] = $paytype[$value['paytype']]['css'];
            // $value['css'] = $paytype[$value['paytype']]['css'];
            if ($value['paytype'] == 2) {
                if (empty($value['transid'])) {
                    $list[$key]['paytype'] = '支付宝支付';

                }
                else {
                    $list[$key]['paytype'] = '微信支付';
                }
            }
            elseif ($value['paytype'] == 0) {
                $t = 5;
                $list[$key]['paytype'] = $paytype[$t]['name'];
            }
            else {
                $list[$key]['paytype'] = $paytype[$value['paytype']]['name'];
            }

            $list[$key]['ordersn'] = chunk_split($value['ordersn']);
        }
        if($user){
            $tsql = "select count(*) from" . tablename('xcommunity_order') . "t1 left join" . tablename('xcommunity_member_address') . "t2 on t1.addressid=t2.id left join" . tablename('mc_members') . "t4 on t4.uid= t1.uid left join" . tablename('xcommunity_order_goods') . "t5 on t5.orderid= t1.id left join" . tablename('xcommunity_goods') . "t6 on t6.id=t5.goodsid where $condition ";
        }else{
            $tsql = "select count(*) from" . tablename('xcommunity_order') . "t1 left join" . tablename('mc_members') . "t3 on t3.uid= t1.uid where $condition ";
        }

        $total = pdo_fetchcolumn($tsql, $paras);
        $pager = pagination($total, $pindex, $psize);

        include $this->template('web/plugin/direct/order/list');
    }
    elseif ($operation == 'detail') {
        $id = intval($_GPC['id']);

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
//            $sql = "select t1.*,t4.realname as address_realname,t4.mobile as address_telephone,t2.realname,t2.mobile,t2.address,t5.title,t1.regionid,t4.uid,t5.title,t6.openid,t1.addressid,t2.city from" . tablename('xcommunity_order') . "t1 left join" . tablename('xcommunity_member_address') . "t2 on t1.addressid = t2.id left join" . tablename('mc_members') . "t4 on t4.uid=t1.uid left join" . tablename('xcommunity_region') . "t5 on t1.regionid=t5.id left join" . tablename('mc_mapping_fans') . "t6 on t6.uid = t1.uid where t1.id=:orderid";
            $tsql = "select t1.*,t4.realname as address_realname,t4.mobile as address_telephone,t2.realname,t2.mobile,t5.price as totalprice,t5.total,t7.openid,t2.address,t2.city from" . tablename('xcommunity_order') . "t1 left join" . tablename('xcommunity_member_address') . "t2 on t1.addressid=t2.id left join" . tablename('mc_members') . "t4 on t4.uid= t1.uid left join" . tablename('xcommunity_order_goods') . "t5 on t5.orderid= t1.id left join" . tablename('xcommunity_goods') . "t6 on t6.id=t5.goodsid left join" . tablename('mc_mapping_fans') . "t7 on t7.uid = t1.uid  where $cond ";
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
        $item['goods'] = $goods;
        $price ='';
        foreach ($goods as $k => $v) {
//        $goods[$k]['goods'] = ($k+1)."商品:".$v['title'].",数量：".$v['total'].",单价：".$v['marketprice']."元</br>";
            $price +=  $v['orderprice'] * $v['total'];
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
            if (set('s2') && set('s6')) {
                $type = set('s1');
                if($type ==1){
                    $type ='wwt';
                }elseif($type ==2){
                    $type = 'juhe';
                    $tpl_id = set('s10');
                }else{
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
                }else{
                    $smsg = json_encode(array('express_name' =>$expresscom,'express_code' => $expresssn));
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
                    if($type ==1){
                        $type ='wwt';
                    }elseif($type ==2){
                        $type = 'juhe';
                        $tpl_id = set('x35',$item['regionid']);
                    }elseif($type ==3){
                        $type = 'aliyun_new';
                        $tpl_id = set('x70',$item['regionid']);
                    }
                    $sdst = $item['mobile'];
                    if ($type == 'wwt') {
                        $smsg = "您的快递是" . $expresscom . ",快递单号" . $expresssn . "。有任何问题请随时与我们联系，谢谢。";
                        $content = sms::send($sdst,$smsg, $type, '',1,$tpl_id);
                    }
                    elseif ($type == 'juhe') {
                        $smsg = urlencode("#express_name#=$expresscom&#express_code#=$expresssn");
                        $content = sms::send($sdst, $smsg, $type, '', 1, $tpl_id);
                    }elseif ($type == 'aliyun_new') {
                        $smsg = json_encode(array('express_name' =>$expresscom,'express_code' => $expresssn));
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
                        'value' => $item['realname'] . ',' . $item['mobile'] . ','. $item['city'] . ',' . $item['address'],
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

        include $this->template('web/plugin/direct/order/detail');
    }
    elseif ($operation == 'delete') {
        /*订单删除*/
        $orderid = intval($_GPC['id']);
        $item = pdo_get('xcommunity_order', array('id' => $orderid), array());
        if ($item) {
            if (pdo_delete('xcommunity_order', array('id' => $orderid))) {
                util::permlog('直通车订单-删除', '订单号:' . $item['ordersn']);
                itoast('订单删除成功', $this->createWebUrl('direct', array('op' => 'order', 'operation' => 'list')), 'success', ture);
            }
        }

    }


}
elseif ($op == 'verify') {
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
elseif ($op == 'set') {
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
elseif ($op == 'print') {
    if ($operation == 'list'){
        $condition = " t1.uniacid=:uniacid and t1.shoptype=2";
        $parms[':uniacid'] =  $_W['uniacid'];
        $condi = " t2.uniacid=:uniacid and t2.type=2";
        $pars[':uniacid'] =  $_W['uniacid'];
        if ($user){
            if($user['uuid']){
                //判断上级管理员是否是商家管理员
                $suser = pdo_get("xcommunity_users",array('uid'=>$user['uuid']),array());
                $uid = $suser['type'] == 5 ? $user['uuid']:$_W['uid'];
                $condition .= " and t2.uid = {$uid}";
                $condi .= " and t2.uid = {$uid}";
            }else{
                $condition .= " and t2.uid = {$_W['uid']}";
                $condi .= " and t2.uid = {$_W['uid']}";
            }
        }
        $shops = pdo_fetchall("select * from".tablename('xcommunity_shop')."t2 where $condi",$pars);
        if ($_GPC['shopid']){
            $condition .= " t1.shopid=:shopid ";
            $parms[':shopid'] =  $_GPC['shopid'];
        }
        $pindex = max(1, intval($_GPC['page']));
        $psize = 20;
        $sql = "SELECT t1.*,t2.title FROM" . tablename('xcommunity_shop_print') . "t1 left join".tablename('xcommunity_shop')."t2 on t2.id=t1.shopid WHERE $condition order by t1.id desc LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
        $list = pdo_fetchall($sql, $parms);
        $total = pdo_fetchcolumn("SELECT COUNT(*) FROM" . tablename('xcommunity_shop_print') . "t1 left join".tablename('xcommunity_shop')."t2 on t2.id=t1.shopid WHERE $condition", $parms);
        $pager = pagination($total, $pindex, $psize);
        include $this->template('web/plugin/direct/print/list');
    }
    elseif($operation == 'add'){
        $shopid = intval($_GPC['shopid']);
        $id = intval($_GPC['id']);
        if ($id) {
            $item = pdo_get('xcommunity_shop_print', array('id' => $id), array());
        }
        if (checksubmit('submit')) {
            $data = array(
                'uniacid'  => $_W['uniacid'],
                'type'     => $_GPC['type'],
                'shopid'      => $shopid,
                'api_key'  => $_GPC['api_key'],
                'deviceNo' => $_GPC['deviceNo'],
                'shoptype'     => 2,
            );
            if ($id) {
                pdo_update('xcommunity_shop_print', $data, array('id' => $id));
            }
            else {
                $data['uid'] = $_W['uid'];
                pdo_insert('xcommunity_shop_print', $data);
            }
            itoast('添加成功', $this->createWebUrl('direct', array('op' => 'print')), 'success', true);
        }
        $condition = " uniacid=:uniacid and type=2";
        $params[':uniacid'] =  $_W['uniacid'];
        if ($user){
            if($user['uuid']){
                //判断上级管理员是否是商家管理员
                $suser = pdo_get("xcommunity_users",array('uid'=>$user['uuid']),array());
                $uid = $suser['type'] == 5 ? $user['uuid']:$_W['uid'];
                $condition .= " and uid = {$uid}";
            }else{
                $condition .= " and uid = {$_W['uid']}";
            }
        }
        $shops = pdo_fetchall("select * from".tablename('xcommunity_shop')."where $condition",$params);
        include $this->template('web/plugin/direct/print/add');
    }
    elseif($operation == 'del'){
        $item = pdo_get('xcommunity_shop_print',array('id'=> $id),array());
        if($item){
            if (pdo_delete('xcommunity_shop_print', array('id' => $id))) {
                util::permlog('超市打印机-删除','信息ID:'.$item['id']);
                $result = array(
                    'status' => 1,
                );
                echo json_encode($result);
                exit();
            }
        }
    }
}
elseif ($op == 'bind') {
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
elseif ($op == 'sets') {
    if(checksubmit('submit')){
        foreach ($_GPC['set'] as $key => $val){
            $sql = "select * from".tablename('xcommunity_setting')."where xqkey='{$key}' and uniacid={$_W['uniacid']} ";
            $item = pdo_fetch($sql);
            if($key =='p49'){
                $val = htmlspecialchars_decode($val);
            }
            $data = array(
                'xqkey' => $key,
                'value' => $val,
                'uniacid' => $_W['uniacid']
            );
            if($item){
                pdo_update('xcommunity_setting',$data,array('id' => $item['id'],'uniacid' => $_W['uniacid']));
            }else{
                pdo_insert('xcommunity_setting',$data);
            }
        }
        itoast('操作成功',referer(),'success',ture);
    }
    $set = pdo_getall('xcommunity_setting',array('uniacid' => $_W['uniacid']),array(),'xqkey',array());
    include $this->template('web/plugin/direct/set');
}
elseif ($op == 'shop') {
    if ($operation == 'list') {
        //店铺列表
        $condition = ' and type=2';
        if ($user) {
            if($user['uuid']){
                //判断上级管理员是否是超市管理员
                $suser = pdo_get("xcommunity_users",array('uid'=>$user['uuid']),array());
                $uid = $suser['type'] == 4 ? $user['uuid']:$_W['uid'];
                $condition .= " and uid = {$uid}";
            }else{
                $condition .= " and uid = {$_W['uid']}";
            }
        }
        if (!empty($_GPC['keyword'])) {
            $condition .= " AND (title LIKE '%{$_GPC['keyword']}%' or mobile LIKE '%{$_GPC['keyword']}%')";
        }
        $pindex = max(1, intval($_GPC['page']));
        $psize = 20;
        $sql = "SELECT * FROM" . tablename('xcommunity_shop') . "WHERE uniacid='{$_W['uniacid']}' $condition order by id desc LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
        $list = pdo_fetchall($sql);
        $total = pdo_fetchcolumn("SELECT COUNT(*) FROM" . tablename('xcommunity_shop') . "WHERE uniacid='{$_W['uniacid']}' $condition");
        $pager = pagination($total, $pindex, $psize);

        include $this->template('web/plugin/direct/shop/list');
    }
    elseif ($operation == 'add') {
        if ($id) {
            $item = pdo_fetch("SELECT * FROM" . tablename('xcommunity_shop') . "WHERE id=:id", array(':id' => $id));
            if (empty($item)) {
                itoast('信息不存在或已删除', referer(), 'error',true);
            }
        }
        if (checksubmit('submit')) {
            $data = array(
                'uniacid' => $_W['uniacid'],
                'title' => $_GPC['title'],
                'contactname' => $_GPC['contactname'],
                'mobile' => $_GPC['mobile'],
                'createtime' => TIMESTAMP,
                'type'      => 2
            );
            if (empty($id)) {
                if($user['uuid']){
                    //判断上级管理员是否是超市
                    $suser = pdo_get("xcommunity_users",array('uid'=>$user['uuid']),array());
                    $data['uid'] = $suser['type'] == 4 ? $user['uuid']:$_W['uid'];
                }else{
                    $data['uid'] = $_W['uid'];
                }
                pdo_insert('xcommunity_shop', $data);
                $dpid = pdo_insertid();
                util::permlog('直通车超市-添加','信息标题:'.$data['title']);
            } else {
                pdo_update('xcommunity_shop', $data, array('id' => $id));
                $dpid = $id;
                util::permlog('直通车超市-修改','信息标题:'.$data['title']);
            }
            itoast('提交成功', referer(), 'success',true);
        }
        load()->func('tpl');
        include $this->template('web/plugin/direct/shop/add');
    }
    elseif ($operation == 'del') {
        //删除店铺
        $dp = pdo_get('xcommunity_shop',array('id'=> $id),array());
        if($dp){
            if (pdo_delete('xcommunity_shop', array('id' => $id))) {
                util::permlog('超市-删除','信息标题:'.$dp['title']);
                $result = array(
                    'status' => 1,
                );
                echo json_encode($result);
                exit();
            }
        }

    }
}
elseif ($op == 'payapi'){
    $condition = ' and type=2';
    if ($user) {
        if($user['uuid']){
            //判断上级管理员是否是商家管理员
            $suser = pdo_get("xcommunity_users",array('uid'=>$user['uuid']),array());
            $uid = $suser['type'] == 5 ? $user['uuid']:$_W['uid'];
            $condition .= " and uid = {$uid}";
        }else{
            $condition .= " and uid = {$_W['uid']}";
        }
    }
    $sql = "SELECT * FROM" . tablename('xcommunity_shop') . "WHERE uniacid='{$_W['uniacid']}' $condition order by id desc ";
    $shops = pdo_fetchall($sql);
    if ($p == 'list') {

    }
    elseif ($p == 'alipay') {
        if ($operation == 'add') {
            $id = intval($_GPC['id']);
            if ($id) {
                $item = pdo_get('xcommunity_alipayment', array('id' => $id), array());
            }
            if (checksubmit('submit')) {
                $data = array(
                    'uniacid' => $_W['uniacid'],
                    'type'    => 8,
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
                    if (pdo_get('xcommunity_alipayment',array('userid' => $_GPC['shopid'],'type' => 8))){
                        itoast('该店铺已有支付账号', referer(), 'error');
                    }
                    $data['uid'] = $_W['uid'];
                    pdo_insert('xcommunity_alipayment', $data);
                    $id = pdo_insertid();
                    util::permlog('', '添加支付宝ID:' . $id);
                }
                itoast('提交成功', referer(), 'success', ture);
            }
            include $this->template('web/plugin/direct/payapi/alipay/add');
        }
        elseif ($operation == 'list') {
            $pindex = max(1, intval($_GPC['page']));
            $psize = 20;
            $condition = '';
            $condition .= " t1.uniacid = {$_W['uniacid']} and t1.type=8";
            if ($user[type] != 1) {
                $condition .= " and t1.uid = {$_W['uid']}";
            }
            $sql = "SELECT t1.*,t2.title FROM" . tablename('xcommunity_alipayment') . "t1 left join".tablename('xcommunity_shop')."t2 on t1.userid=t2.id WHERE $condition LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
            $list = pdo_fetchall($sql);
            $total = pdo_fetchcolumn("SELECT COUNT(*) FROM" . tablename('xcommunity_alipayment') . "t1 left join".tablename('xcommunity_shop')."t2 on t1.userid=t2.id WHERE $condition");
            $pager = pagination($total, $pindex, $psize);
            include $this->template('web/plugin/direct/payapi/alipay/list');
        }
        elseif ($operation == 'del') {
            $id = intval($_GPC['id']);
            if ($id) {
                if (pdo_delete('xcommunity_alipayment', array('id' => $id))) {
                    itoast('删除成功', referer(), 'success', ture);
                    exit();
                }
            }
        }
    }
    elseif ($p == 'wechat') {
        if ($operation == 'add') {
            $id = intval($_GPC['id']);
            if ($id) {
                $item = pdo_get('xcommunity_wechat', array('id' => $id), array());
            }
            if (checksubmit('submit')) {
                $data = array(
                    'uniacid'   => $_W['uniacid'],
                    'appid'     => $_GPC['appid'],
                    'appsecret' => $_GPC['appsecret'],
                    'mchid'     => $_GPC['mchid'],
                    'apikey'    => $_GPC['apikey'],
                    'type'      => 8,
                );

                $data['userid'] = $_GPC['shopid'];
                if ($id) {
                    pdo_update('xcommunity_wechat', $data, array('id' => $id));
                    util::permlog('', '修改借用支付ID:' . $id);
                }
                else {
                    if (pdo_get('xcommunity_wechat',array('userid' => $_GPC['shopid'],'type' => 8))){
                        itoast('该店铺已有支付账号', referer(), 'error');
                    }
                    $data['uid'] = $_W['uid'];
                    pdo_insert('xcommunity_wechat', $data);
                    $id = pdo_insertid();
                    util::permlog('', '添加借用支付ID:' . $id);
                }
                itoast('提交成功', referer(), 'success', ture);
            }
            include $this->template('web/plugin/direct/payapi/wechat/add');
        }
        elseif ($operation == 'list') {
            $pindex = max(1, intval($_GPC['page']));
            $psize = 20;
            $condition = '';
            $condition .= " t1.uniacid = {$_W['uniacid']} and t1.type=8";
            if ($user[type] != 1) {
                $condition .= " and t1.uid = {$_W['uid']}";
            }
            $sql = "SELECT t1.*,t2.title FROM" . tablename('xcommunity_wechat') . "t1 left join".tablename('xcommunity_shop')."t2 on t1.userid=t2.id WHERE $condition LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
            $list = pdo_fetchall($sql, array(':uniacid' => $_W['uniacid']));
            $total = pdo_fetchcolumn("SELECT COUNT(*) FROM" . tablename('xcommunity_wechat') . "t1 left join".tablename('xcommunity_shop')."t2 on t1.userid=t2.id WHERE $condition");
            $pager = pagination($total, $pindex, $psize);
            include $this->template('web/plugin/direct/payapi/wechat/list');
        }
        elseif ($operation == 'del') {
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
    elseif ($p == 'sub') {
        if ($operation == 'add') {
            $id = intval($_GPC['id']);
            if ($id) {
                $item = pdo_get('xcommunity_service_data', array('id' => $id), array());
            }
            if (checksubmit('submit')) {
                $data = array(
                    'uniacid'    => $_W['uniacid'],
                    'sub_id' => $_GPC['sub_id'],
                    'apikey' => $_GPC['apikey'],
                    'appid' => $_GPC['appid'],
                    'appsecret' => $_GPC['appsecret'],
                    'sub_mch_id' => $_GPC['sub_mch_id'],
                    'type'       => 8,
                );
                $data['userid'] = $_GPC['shopid'];
                if ($id) {
                    pdo_update('xcommunity_service_data', $data, array('id' => $id));
                    util::permlog('', '修改子商户ID:' . $id);
                }
                else {
                    if (pdo_get('xcommunity_service_data',array('userid' => $_GPC['shopid'],'type' => 8))){
                        itoast('该店铺已有支付账号', referer(), 'error');
                    }
                    $data['uid'] = $_W['uid'];
                    pdo_insert('xcommunity_service_data', $data);
                    $id = pdo_insertid();
                    util::permlog('', '添加子商户ID:' . $id);
                }
                itoast('提交成功', referer(), 'success', ture);
            }
            include $this->template('web/plugin/direct/payapi/sub/add');
        }
        elseif ($operation == 'list') {
            $pindex = max(1, intval($_GPC['page']));
            $psize = 20;
            $condition = '';
            $condition .= " t1.uniacid = {$_W['uniacid']} and t1.type=8";
            if ($user[type] != 1) {
                $condition .= " and t1.uid = {$_W['uid']}";
            }
            $sql = "SELECT t1.*,t2.title FROM" . tablename('xcommunity_service_data') . "t1 left join".tablename('xcommunity_shop')."t2 on t1.userid=t2.id WHERE $condition LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
            $list = pdo_fetchall($sql);
            $total = pdo_fetchcolumn("SELECT COUNT(*) FROM" . tablename('xcommunity_service_data') . "t1 left join".tablename('xcommunity_shop')."t2 on t1.userid=t2.id WHERE $condition");
            $pager = pagination($total, $pindex, $psize);
            include $this->template('web/plugin/direct/payapi/sub/list');
        }
        elseif ($operation == 'del') {
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
    elseif ($p == 'swiftpass') {
        if ($operation == 'add') {
            $id = intval($_GPC['id']);
            if ($id) {
                $item = pdo_get('xcommunity_swiftpass', array('id' => $id), array());
            }
            if (checksubmit('submit')) {
                $data = array(
                    'uniacid'   => $_W['uniacid'],
                    'type'      => 8,
                    'account'   => trim($_GPC['account']),
                    'secret'    => trim($_GPC['secret']),
                    'appid'     => trim($_GPC['appid']),
                    'appsecret' => trim($_GPC['appsecret'])
                );
                $data['userid'] = $_GPC['shopid'];
                if ($id) {
                    pdo_update('xcommunity_swiftpass', $data, array('id' => $id));
                    util::permlog('', '修改威富通微信支付ID:' . $id);
                }
                else {
                    if (pdo_get('xcommunity_swiftpass',array('userid' => $_GPC['shopid'],'type' => 8))){
                        itoast('该店铺已有支付账号', referer(), 'error');
                    }
                    $data['uid'] = $_W['uid'];
                    pdo_insert('xcommunity_swiftpass', $data);
                    $id = pdo_insertid();
                    util::permlog('', '添加威富通微信支付ID:' . $id);
                }
                itoast('提交成功', referer(), 'success', ture);
            }
            include $this->template('web/plugin/direct/payapi/swiftpass/add');
        }
        elseif ($operation == 'list') {
            $pindex = max(1, intval($_GPC['page']));
            $psize = 20;
            $condition = '';
            $condition .= " t1.uniacid = {$_W['uniacid']} and t1.type=8";
            if ($user[type] != 1) {
                $condition .= " and t1.uid = {$_W['uid']}";
            }
            $sql = "SELECT t1.*,t2.title FROM" . tablename('xcommunity_swiftpass') . "t1 left join".tablename('xcommunity_shop')."t2 on t1.userid=t2.id WHERE $condition LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
            $list = pdo_fetchall($sql);
            $total = pdo_fetchcolumn("SELECT COUNT(*) FROM" . tablename('xcommunity_swiftpass') . "t1 left join".tablename('xcommunity_shop')."t2 on t1.userid=t2.id WHERE $condition");
            $pager = pagination($total, $pindex, $psize);

            include $this->template('web/plugin/direct/payapi/swiftpass/list');
        }
        elseif ($operation == 'del') {
            $id = intval($_GPC['id']);
            if ($id) {
                if (pdo_delete('xcommunity_swiftpass', array('id' => $id))) {
                    itoast('删除成功', referer(), 'success', ture);
                    exit();
                }
            }
        }
    }
    elseif ($p == 'hsyunfu') {
        if ($operation == 'add') {
            $id = intval($_GPC['id']);
            if ($id) {
                $item = pdo_get('xcommunity_hsyunfu', array('id' => $id), array());
            }
            if (checksubmit('submit')) {
                $data = array(
                    'uniacid'   => $_W['uniacid'],
                    'type'      => 8,
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
                    if (pdo_get('xcommunity_hsyunfu',array('userid' => $_GPC['shopid'],'type' => 8))){
                        itoast('该店铺已有支付账号', referer(), 'error');
                    }
                    $data['uid'] = $_W['uid'];
                    pdo_insert('xcommunity_hsyunfu', $data);
                    $id = pdo_insertid();
                    util::permlog('', '添加华商云付微信支付ID:' . $id);
                }
                itoast('提交成功', referer(), 'success', ture);
            }
            include $this->template('web/plugin/direct/payapi/hsyunfu/add');
        }
        elseif ($operation == 'list') {
            $pindex = max(1, intval($_GPC['page']));
            $psize = 20;
            $condition = '';
            $condition .= " t1.uniacid = {$_W['uniacid']} and t1.type=8";
            if ($user[type] != 1) {
                $condition .= " and t1.uid = {$_W['uid']}";
            }
            $sql = "SELECT t1.*,t2.title FROM" . tablename('xcommunity_hsyunfu') . "t1 left join".tablename('xcommunity_shop')."t2 on t1.userid=t2.id WHERE $condition LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
            $list = pdo_fetchall($sql);
            $total = pdo_fetchcolumn("SELECT COUNT(*) FROM" . tablename('xcommunity_hsyunfu') . "t1 left join".tablename('xcommunity_shop')."t2 on t1.userid=t2.id WHERE $condition");
            $pager = pagination($total, $pindex, $psize);

            include $this->template('web/plugin/direct/payapi/hsyunfu/list');
        }
        elseif ($operation == 'del') {
            $id = intval($_GPC['id']);
            if ($id) {
                if (pdo_delete('xcommunity_hsyunfu', array('id' => $id))) {
                    itoast('删除成功', referer(), 'success', ture);
                    exit();
                }
            }
        }
    }
    elseif ($p == 'chinaums') {
        if ($operation == 'add') {
            $id = intval($_GPC['id']);
            if ($id) {
                $item = pdo_get('xcommunity_chinaums', array('id' => $id), array());
            }
            if (checksubmit('submit')) {
                $data = array(
                    'uniacid' => $_W['uniacid'],
                    'type'    => 8,
                    'mid' => $_GPC['mid'],
                    'tid' => $_GPC['tid'],
                    'instmid' => $_GPC['instmid'],
                    'msgsrc' => $_GPC['msgsrc'],
                    'msgsrcid' => $_GPC['msgsrcid'],
                    'secret'  => $_GPC['secret'],
                );
                $data['userid'] = $_GPC['shopid'];
                if ($id) {
                    pdo_update('xcommunity_chinaums', $data, array('id' => $id));
                    util::permlog('', '修改银联ID:' . $id);
                }
                else {
                    if (pdo_get('xcommunity_chinaums',array('userid' => $_GPC['shopid'],'type' => 8))){
                        itoast('该店铺已有银联账号', referer(), 'error');
                    }
                    $data['uid'] = $_W['uid'];
                    pdo_insert('xcommunity_chinaums', $data);
                    $id = pdo_insertid();
                    util::permlog('', '添加银联ID:' . $id);
                }
                itoast('提交成功', referer(), 'success', ture);
            }
            include $this->template('web/plugin/direct/payapi/chinaums/add');
        }
        elseif ($operation == 'list') {
            $pindex = max(1, intval($_GPC['page']));
            $psize = 20;
            $condition = '';
            $condition .= " t1.uniacid = {$_W['uniacid']} and t1.type=8";
            if ($user[type] != 1) {
                $condition .= " and t1.uid = {$_W['uid']}";
            }
            $sql = "SELECT t1.*,t2.title FROM" . tablename('xcommunity_chinaums') . "t1 left join".tablename('xcommunity_shop')."t2 on t1.userid=t2.id WHERE $condition LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
            $list = pdo_fetchall($sql);
            $total = pdo_fetchcolumn("SELECT COUNT(*) FROM" . tablename('xcommunity_chinaums') . "t1 left join".tablename('xcommunity_shop')."t2 on t1.userid=t2.id WHERE $condition");
            $pager = pagination($total, $pindex, $psize);
            include $this->template('web/plugin/direct/payapi/chinaums/list');
        }
        elseif ($operation == 'del') {
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
elseif ($op == 'wechat') {
    $shopid = intval($_GPC['shopid']);
    if ($operation == 'list'){
        $condition = " t1.uniacid=:uniacid and t1.shoptype=2";
        $parms[':uniacid'] =  $_W['uniacid'];
        if ($user){
            if($user['uuid']){
                //判断上级管理员是否是商家管理员
                $suser = pdo_get("xcommunity_users",array('uid'=>$user['uuid']),array());
                $uid = $suser['type'] == 5 ? $user['uuid']:$_W['uid'];
                $condition .= " and t2.uid = {$uid}";
            }else{
                $condition .= " and t2.uid = {$_W['uid']}";
            }
        }
        $pindex = max(1, intval($_GPC['page']));
        $psize = 20;
        $sql = "SELECT t1.*,t2.title FROM" . tablename('xcommunity_shop_wechat') . "t1 left join".tablename('xcommunity_shop')."t2 on t2.id=t1.shopid WHERE $condition order by t1.id desc LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
        $list = pdo_fetchall($sql, $parms);
        $total = pdo_fetchcolumn("SELECT COUNT(*) FROM" . tablename('xcommunity_shop_wechat') . "t1 left join".tablename('xcommunity_shop')."t2 on t2.id=t1.shopid WHERE $condition", $parms);
        $pager = pagination($total, $pindex, $psize);
        include $this->template('web/plugin/direct/wechat/list');
    }
    elseif($operation == 'add'){
        $id = intval($_GPC['id']);
        if ($id) {
            $item = pdo_get('xcommunity_shop_wechat', array('id' => $id), array());
        }
        if (checksubmit('submit')) {
            $data = array(
                'uniacid'   => $_W['uniacid'],
                'enable'    => $_GPC['enable'],
                'type'      => $_GPC['type'],
                'shopid'      => $shopid,
                'realname'  => trim($_GPC['realname']),
                'mobile' => trim($_GPC['mobile']),
                'openid'    => trim($_GPC['openid']),
                'shoptype'  => 2
            );
            if ($id) {
                pdo_update('xcommunity_shop_wechat', $data, array('id' => $id));
            }
            else {
                $data['uid'] = $_W['uid'];
                pdo_insert('xcommunity_shop_wechat', $data);
            }
            itoast('添加成功', $this->createWebUrl('direct', array('op' => 'wechat')), 'success', true);
        }
        $condition = " uniacid=:uniacid and type=2";
        $params[':uniacid'] =  $_W['uniacid'];
        if ($user){
            if($user['uuid']){
                //判断上级管理员是否是商家管理员
                $suser = pdo_get("xcommunity_users",array('uid'=>$user['uuid']),array());
                $uid = $suser['type'] == 5 ? $user['uuid']:$_W['uid'];
                $condition .= " and uid = {$uid}";
            }else{
                $condition .= " and uid = {$_W['uid']}";
            }
        }
        $shops = pdo_fetchall("select * from".tablename('xcommunity_shop')."where $condition",$params);
        include $this->template('web/plugin/direct/wechat/add');
    }
    elseif($operation == 'del'){
        $dp = pdo_get('xcommunity_shop_wechat',array('id'=> $id),array());
        if($dp){
            if (pdo_delete('xcommunity_shop_wechat', array('id' => $id))) {
                util::permlog('超市接收员-删除','信息标题:'.$dp['realname']);
                $result = array(
                    'status' => 1,
                );
                echo json_encode($result);
                exit();
            }
        }
    }
    elseif ($operation == 'verify') {
        //审核用户
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