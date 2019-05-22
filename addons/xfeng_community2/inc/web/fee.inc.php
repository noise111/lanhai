<?php
/**
 * Created by njlanniu.com.
 * User: 蓝牛科技
 * Time: 2017/11/8 下午7:15
 */
global $_W, $_GPC;
$ops = array('category', 'group', 'list', 'cat', 'cate', 'log', 'order', 'center', 'entery', 'wechat', 'data', 'wxsend', 'reckon', 'smssend');
$op = !empty($_GPC['op']) ? $_GPC['op'] : 'category';
if (!in_array($op, $ops)) {
    message('该方法不存在(op:' . $op . ')');
}
$p = in_array(trim($_GPC['p']), array('list', 'add', 'del', 'detail', 'edit', 'import', 'categorry', 'qr', 'pldel', 'category', 'download', 'import', 'print', 'display', 'change', 'group', 'qrlist', 'qrdel', 'qrdown', 'post', 'plpay', 'newImport', 'call')) ? trim($_GPC['p']) : 'list';
$regions = model_region::region_fetall();
// 操作员
$user = util::xquser($_W['uid']);
if ($user && $user['menu_ops']) {
    $menu_opss = explode(',', $user['menu_ops']);
}
/**
 * 收费管理-收费项目
 */
if ($op == 'category') {
    /**
     * 收费项目列表
     */
    if ($p == 'list') {
        $pindex = max(1, intval($_GPC['page']));
        $psize = 20;
        $condition = 't1.uniacid =:uniacid and t1.type=1';
        $params[':uniacid'] = $_W['uniacid'];
        if (!empty($_GPC['keyword'])) {
            $condition .= " AND t1.title LIKE '%{$_GPC['keyword']}%'";
        }
        if (intval($_GPC['regionid'])) {
            $condition .= " and t1.regionid =:regionid";
            $params[':regionid'] = intval($_GPC['regionid']);
        }
        if ($user && $user[type] != 1) {
            //普通管理员
            $condition .= " AND t1.uid=:uid";
            $params[':uid'] = $_W['uid'];
        }
        $sql = "SELECT t1.*,t2.title as regiontitle FROM" . tablename('xcommunity_fee_category') . "t1 left join" . tablename('xcommunity_region') . "t2 on t1.regionid = t2.id WHERE $condition order by t1.id desc LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
        $list = pdo_fetchall($sql, $params);
        $total = pdo_fetchcolumn("SELECT COUNT(*) FROM" . tablename('xcommunity_fee_category') . "t1 WHERE $condition", $params);
        $pager = pagination($total, $pindex, $psize);
        include $this->template('web/core/fee/category/list');
    }
    /**
     * 收费项目添加
     */
    if ($p == 'add') {
        $id = intval($_GPC['id']);
        if ($id) {
            $item = pdo_get('xcommunity_fee_category', array('id' => $id), array());
        }
        if ($_W['isajax']) {
            $data = array(
                'uniacid'   => $_W['uniacid'],
                'pid'       => intval($_GPC['pid']),
                'regionid'  => intval($_GPC['regionid']),
                'title'     => $_GPC['title'],
                'way'       => intval($_GPC['way']),
                'unit'      => $_GPC['unit'],
                'price'     => $_GPC['price'],
                'cycle'     => intval($_GPC['cycle']),
                'rate'      => intval($_GPC['rate']),
                'start_day' => intval($_GPC['start_day']),
                'discount'  => intval($_GPC['discount']),
                'remark'    => trim($_GPC['remark']),
                'status'    => intval($_GPC['status']),
                'type'      => 1
            );
            if (empty($id)) {
                $data['uid'] = $_W['uid'];
                pdo_insert('xcommunity_fee_category', $data);
            }
            else {
                pdo_update('xcommunity_fee_category', $data, array('id' => $id));
            }
            echo json_encode(array('status' => 1));
            exit();
        }
        include $this->template('web/core/fee/category/add');
    }
    /**
     * 收费项目删除
     */
    if ($p == 'del') {
        $id = intval($_GPC['id']);
        if ($id) {
            $item = pdo_get('xcommunity_fee_category', array('id' => $id), array('id'));
            if ($item) {
                if (pdo_delete('xcommunity_fee_category', array('id' => $id))) {
                    itoast('删除成功', referer(), 'success');
                }
            }
        }
    }
}
/**
 * 收费管理-收费分组
 */
if ($op == 'group') {
    /**
     * 收费分组列表
     */
    if ($p == 'list') {
        $pindex = max(1, intval($_GPC['page']));
        $psize = 20;
        $condition = 't1.uniacid =:uniacid';
        $params[':uniacid'] = $_W['uniacid'];
        if (!empty($_GPC['keyword'])) {
            $condition .= " AND t1.title LIKE '%{$_GPC['keyword']}%'";
        }
        if (intval($_GPC['regionid'])) {
            $condition .= " and t1.regionid =:regionid";
            $params[':regionid'] = intval($_GPC['regionid']);
        }
        if ($user && $user[type] != 1) {
            //普通管理员
            $condition .= " AND t1.uid=:uid";
            $params[':uid'] = $_W['uid'];
        }
        $sql = "SELECT t1.*,t2.title as regiontitle FROM" . tablename('xcommunity_fee_group') . "t1 left join" . tablename('xcommunity_region') . "t2 on t1.regionid = t2.id WHERE $condition order by t1.id desc LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
        $list = pdo_fetchall($sql, $params);
        $total = pdo_fetchcolumn("SELECT COUNT(*) FROM" . tablename('xcommunity_fee_group') . "t1 WHERE $condition", $params);
        $pager = pagination($total, $pindex, $psize);

        include $this->template('web/core/fee/group/list');
    }
    /**
     * 收费分组添加
     */
    if ($p == 'add') {
        $id = intval($_GPC['id']);
        if ($id) {
            $item = pdo_get('xcommunity_fee_group', array('id' => $id), array());
            $build = pdo_getall('xcommunity_fee_group_build', array('groupid' => $id), array('buildid'));
            $buildids = array();
            foreach ($build as $key => $val) {
                $buildids[] = $val['buildid'];
            }
            $category = pdo_getall('xcommunity_fee_group_category', array('groupid' => $id), array('categoryid'));
            $categoryids = array();
            foreach ($category as $k => $v) {
                $categoryids[] = $v['categoryid'];
            }
            $areas = pdo_getall('xcommunity_area', array('regionid' => $item['regionid']), array());
            $areas_ids = _array_column($areas, NULL, 'id');
            $arr = util::xqset($item['regionid']);
            $builds = pdo_getall('xcommunity_build', array('regionid' => $item['regionid']), array());
            foreach ($builds as $k => $v) {
                if ($v['areaid']) {
                    $builds[$k]['buildtitle'] = $areas_ids[$v['areaid']]['title'] . $arr[a1] . $v['buildtitle'];
                }
            }
            $condition = "regionid=:regionid and way in(1,2,3) and type=1";
            $categories = pdo_fetchall('select * from' . tablename('xcommunity_fee_category') . "where $condition", array(':regionid' => $item['regionid']));
            $rules = pdo_getall('xcommunity_fee_group_floor_price', array('groupid' => $id));
            foreach ($rules as $k => $v) {
                $floor = explode(',', $v['floor']);
                $rules[$k]['floorstart'] = $floor[0];
                $rules[$k]['floorend'] = $floor[1];
            }
        }
        if ($_W['isajax']) {
            $data = array(
                'uniacid'  => $_W['uniacid'],
                'regionid' => intval($_GPC['regionid']),
                'title'    => trim($_GPC['title']),
                'remark'   => $_GPC['remark'],
                'status'   => intval($_GPC['status'])
            );
            if (empty($id)) {
                $data['uid'] = $_W['uid'];
                pdo_insert('xcommunity_fee_group', $data);
                $groupid = pdo_insertid();
            }
            else {
                pdo_update('xcommunity_fee_group', $data, array('id' => $id));
                $groupid = $id;
                pdo_delete('xcommunity_fee_group_build', array('groupid' => $id));
                pdo_delete('xcommunity_fee_group_floor_price', array('groupid' => $id));
                pdo_delete('xcommunity_fee_group_category', array('groupid' => $id));
            }
            if ($groupid) {
                $builds = $_GPC['build'];
                foreach ($builds as $k => $v) {
                    if ($v) {
                        $dat = array(
                            'uniacid' => $_W['uniacid'],
                            'groupid' => $groupid,
                            'buildid' => $v,
                        );
                        pdo_insert('xcommunity_fee_group_build', $dat);
                    }
                }
                if ($data['status'] == 1) {
                    $price = $_GPC['price'];
                    if ($price) {
                        foreach ($price as $k => $v) {
                            $floorstart = $_GPC['floorstart'];
                            $floorend = $_GPC['floorend'];
                            $d = array(
                                'uniacid' => $_W['uniacid'],
                                'groupid' => $groupid,
                                'floor'   => $floorstart[$k] . ',' . $floorend[$k],
                                'price'   => $v
                            );
                            pdo_insert('xcommunity_fee_group_floor_price', $d);
                        }
                    }
                }
                $categories = $_GPC['categoryid'];
                if ($categories) {
                    foreach ($categories as $k => $v) {
                        $da = array(
                            'uniacid'    => $_W['uniacid'],
                            'groupid'    => $groupid,
                            'categoryid' => $v,
                        );
                        pdo_insert('xcommunity_fee_group_category', $da);
                    }
                }
            }
            echo json_encode(array('status' => 1));
            exit();
        }
        include $this->template('web/core/fee/group/add');
    }
    /**
     * 收费分组删除
     */
    if ($p == 'del') {
        $id = intval($_GPC['id']);
        if (empty($id)) {
            itoast('缺少参数');
            exit();
        }
        $item = pdo_get('xcommunity_fee_group', array('id' => $id), array());
        if ($item) {
            if (pdo_delete('xcommunity_fee_group', array('id' => $id))) {
                if (pdo_delete('xcommunity_fee_group_build', array('groupid' => $id)) || pdo_delete('xcommunity_fee_group_floor_price', array('groupid' => $id)) || pdo_delete('xcommunity_fee_group_category', array('groupid' => $id))) {
                }
                itoast('删除成功', referer(), 'success');
            }
        }
    }
}
/**
 * 收费管理-物业账单
 */
if ($op == 'list') {
    /**
     * 物业账单列表
     */
    if ($p == 'list') {
        // 排序
        if (!empty($_GPC['displayorder'])) {
            foreach ($_GPC['displayorder'] as $id => $displayorder) {
                pdo_update('xcommunity_fee', array('displayorder' => $displayorder), array('id' => $id));
            }
            itoast('排序更新成功！', 'refresh', 'success', ture);
        }
        $arr = util::xqset($item['regionid']);
        $pindex = max(1, intval($_GPC['page']));
        $psize = 20;
        $condition = 't1.uniacid =:uniacid and t1.type=1';
        $params[':uniacid'] = $_W['uniacid'];
        if (!empty($_GPC['keyword'])) {
            $condition .= " AND t2.room LIKE '%{$_GPC['keyword']}%'";
        }
        if (intval($_GPC['regionid'])) {
            $condition .= " and t2.regionid =:regionid";
            $params[':regionid'] = intval($_GPC['regionid']);
        }
        if ($_GPC['status']) {
            $condition .= " and t1.status =:status";
            $params[':status'] = intval($_GPC['status']);
        }
        if ($_GPC['categoryid']) {
            $condition .= " and t1.categoryid =:categoryid";
            $params[':categoryid'] = intval($_GPC['categoryid']);
        }
//        if ($user[type] == 2 || $user[type] == 3) {
//            //普通管理员
//            $condition .= " AND t1.uid=:uid";
//            $params[':uid'] = $_W['uid'];
//        }
        if ($user && $user[type] == 3) {
            //小区管理员
            $condition .= " and t2.regionid in({$user['regionid']})";
        }
        if ($_GPC['export'] == 1) {
            $sql = "SELECT t1.*,t2.area,t2.build,t2.unit,t2.room,t3.title,t4.title as regiontitle FROM" . tablename('xcommunity_fee') . "t1 left join" . tablename('xcommunity_member_room') . "t2 on t1.roomid = t2.id left join" . tablename('xcommunity_fee_category') . "t3 on t3.id = t1.categoryid left join" . tablename('xcommunity_region') . "t4 on t4.id = t2.regionid WHERE $condition order by t1.id desc";
            $list1 = pdo_fetchall($sql, $params);
            $paytype = array('', '余额', '微信支付', '货到付款', '支付宝', '现金', '银联刷卡');
            foreach ($list1 as $k => $v) {
                if ($v['area']) {
                    $list1[$k]['build'] = $v['area'] . $arr[a1] . $v['build'];
                }
                $list1[$k]['createtime'] = date('Y-m-d H:i', $v['createtime']);
                $list1[$k]['setime'] = date('Y-m-d', $v['starttime']) . '~' . date('Y-m-d', $v['endtime']);
                if ($v['status'] == 2) {
                    $list1[$k]['status'] = '已支付';
                }
                else {
                    $list1[$k]['status'] = '未支付';
                }
                $list1[$k]['paytype'] = $paytype[$v['paytype']];
            }
            model_execl::export($list1, array(
                "title"   => "物业账单数据-" . date('Y-m-d-H-i', time()),
                "columns" => array(
                    array(
                        'title' => '账单ID',
                        'field' => 'id',
                        'width' => 10
                    ),
                    array(
                        'title' => '收费项目',
                        'field' => 'title',
                        'width' => 12
                    ),
                    array(
                        'title' => '所属小区',
                        'field' => 'regiontitle',
                        'width' => 12
                    ),
                    array(
                        'title' => '楼宇',
                        'field' => 'build',
                        'width' => 18
                    ),
                    array(
                        'title' => '单元',
                        'field' => 'unit',
                        'width' => 18
                    ),
                    array(
                        'title' => '房号',
                        'field' => 'room',
                        'width' => 12
                    ),
                    array(
                        'title' => '账单日期',
                        'field' => 'setime',
                        'width' => 12
                    ),
                    array(
                        'title' => '状态',
                        'field' => 'status',
                        'width' => 12
                    ),
                    array(
                        'title' => '支付方式',
                        'field' => 'paytype',
                        'width' => 12
                    ),
                    array(
                        'title' => '费用',
                        'field' => 'price',
                        'width' => 12
                    ),
                    array(
                        'title' => '实付',
                        'field' => 'pay_price',
                        'width' => 18
                    ),
                )
            ));
        }
        $sql = "SELECT t1.*,t2.area,t2.build,t2.unit,t2.room,t3.title,t4.title as regiontitle,t2.address FROM" . tablename('xcommunity_fee') . "t1 left join" . tablename('xcommunity_member_room') . "t2 on t1.roomid = t2.id left join" . tablename('xcommunity_fee_category') . "t3 on t3.id = t1.categoryid left join" . tablename('xcommunity_region') . "t4 on t4.id = t2.regionid WHERE $condition order by t1.displayorder desc,t1.id desc LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
        $list = pdo_fetchall($sql, $params);
        foreach ($list as $k => $v) {
            if ($v['area']) {
                $list[$k]['build'] = $v['area'] . $arr[a1] . $v['build'];
            }
        }
        $tsql = "SELECT count(*) FROM" . tablename('xcommunity_fee') . "t1 left join" . tablename('xcommunity_member_room') . "t2 on t1.roomid = t2.id left join" . tablename('xcommunity_fee_category') . "t3 on t3.id = t1.categoryid left join" . tablename('xcommunity_region') . "t4 on t4.id = t2.regionid WHERE $condition ";
        $total = pdo_fetchcolumn($tsql, $params);
        $pager = pagination($total, $pindex, $psize);
        $con = array();
        if ($user && $user[type] == 3) {
            //小区管理员
            $con['regionid'] = $user['regionid'];
        }
        $con['type'] = 1;
        $con['way'] = array(1, 2, 3);
        $con['uniacid'] = $_W['uniacid'];
        $categorys = pdo_getall('xcommunity_fee_category', $con, array('id', 'title'));
        include $this->template('web/core/fee/list/list');
    }
    /**
     * 物业账单添加
     */
    if ($p == 'add') {
        $regions = model_region::region_fetall();
        if ($_W['isajax']) {
            $dat = array(
                'uniacid'  => $_W['uniacid'],
                'regionid' => intval($_GPC['regionid']),
                'title'    => trim($_GPC['title']),
                'remark'   => trim($_GPC['remark']),
                'status'   => intval($_GPC['status']),
                'endtime'  => strtotime($_GPC['enddate'])
            );
            if (pdo_insert('xcommunity_fee_log', $dat)) {
                $logid = pdo_insertid();
                $categoryids = $_GPC['categoryid'];
                $group = intval($_GPC['group']);
                $sql = "select t1.id,t1.square,t1.floor_num from" . tablename('xcommunity_member_room') . "t1 left join" . tablename('xcommunity_fee_group_build') . "t2 on t1.buildid = t2.buildid where t2.groupid=:groupid and t1.uniacid =:uniacid";
                $rooms = pdo_fetchall($sql, array(':groupid' => $group, ':uniacid' => $_W['uniacid']));
                //查询分组数据
                $groups = pdo_get('xcommunity_fee_group', array('id' => $group), array('status'));

                foreach ($rooms as $k => $room) {

                    $cids = implode(',', $categoryids);

                    $csql = "select * from" . tablename('xcommunity_fee_category') . "where id in (" . $cids . ")";

                    $categories = pdo_fetchall($csql);
                    foreach ($categories as $key => $category) {
                        if ($category['way'] != 4) {
                            //去除按计量收费
                            if ($groups['status'] == 1) {
                                //按楼宇计算规则计算价格
                                $floors = pdo_getall('xcommunity_fee_group_floor_price', array('groupid' => $group));
                                if ($floors) {
                                    foreach ($floors as $val) {
                                        if ($val['floor']) {
                                            $floor = explode(',', $val['floor']);
                                            if ($room['floor_num'] >= $floor[0] && $room['floor_num'] <= $floor[1]) {
                                                $price = $val['price'];
                                            }
                                        }
                                    }
                                }
                            }
                            else {
                                //统一计算
                                $price = $category['price'];
                            }
                            if ($category['way'] == 2 || $category['way'] == 3) {
                                $price = $price * $category['cycle'];
                            }
                            elseif ($category['way'] == 1) {
                                $price = $price * $room['square'] * $category['cycle'];
                            }

                            //计算滞纳金
                            $endday = strtotime($_GPC['enddate']) + $category['start_day'] * 24 * 60 * 60;
                            $rate = '';
                            if (TIMESTAMP > $endday) {
                                $day = round((TIMESTAMP - $endday) / (24 * 60 * 60));//滞纳金的天数
                                $rate = ($category['rate'] / 1000) * $price * $day;
                            }
//                            $cycle = "-" . $category['cycle'] . "months";
//                            $starttime = date("Y-m-d H:i:s", (strtotime($cycle, strtotime($_GPC['enddate']))));
                            $data = array(
                                'uniacid'    => $_W['uniacid'],
                                'categoryid' => $category['id'],
                                'roomid'     => $room['id'],
                                'starttime'  => strtotime($_GPC['startdate']),
                                'endtime'    => strtotime($_GPC['enddate']),
                                'price'      => $category['discount'] ? $price * ($category['discount'] * 10 / 100) : $price,
                                'rate'       => $rate,
                                'status'     => 1,
                                'type'       => 1,
                                'logid'      => $logid,
                                'createtime' => TIMESTAMP,
                                'regionid'   => intval($_GPC['regionid'])
                            );
                            pdo_insert('xcommunity_fee', $data);
                        }
                    }

                }
            }
            echo json_encode(array('status' => 1));
            exit();
        }
        include $this->template('web/core/fee/list/add');
    }
    /**
     * 物业账单详情
     */
    if ($p == 'detail') {
        $id = intval($_GPC['id']);
        if (empty($id)) {
            itoast('缺少参数');
        }
        if ($id) {
            $sql = "SELECT t1.*,t2.build,t2.unit,t2.room,t3.title,t4.title as regiontitle,t3.way,t3.unit,t3.cycle,t1.paytype FROM" . tablename('xcommunity_fee') . "t1 left join" . tablename('xcommunity_member_room') . "t2 on t1.roomid = t2.id left join" . tablename('xcommunity_fee_category') . "t3 on t3.id = t1.categoryid left join" . tablename('xcommunity_region') . "t4 on t4.id = t2.regionid WHERE t1.id=:id";
            $item = pdo_fetch($sql, array(':id' => $id));
        }
        include $this->template('web/core/fee/list/detail');
    }
    /**
     * 物业账单删除
     */
    if ($p == 'del') {
        $id = intval($_GPC['id']);
        if (empty($id)) {
            itoast('缺少参数');
            exit();
        }
        $item = pdo_get('xcommunity_fee', array('id' => $id));
        if ($item) {
            if (pdo_delete('xcommunity_fee', array('id' => $id))) {
                itoast('删除成功', $this->createWebUrl('fee', array('op' => 'list')), 'success');
            }
        }
    }
    /**
     * 物业账单编辑
     */
    if ($p == 'edit') {
        $id = intval($_GPC['id']);
        if (empty($id)) {
            itoast('缺少参数');
        }
        if ($id) {
            $sql = "SELECT t1.*,t2.build,t2.unit,t2.room,t3.title,t4.title as regiontitle,t3.way,t3.unit,t3.cycle,t1.paytype FROM" . tablename('xcommunity_fee') . "t1 left join" . tablename('xcommunity_member_room') . "t2 on t1.roomid = t2.id left join" . tablename('xcommunity_fee_category') . "t3 on t3.id = t1.categoryid left join" . tablename('xcommunity_region') . "t4 on t4.id = t2.regionid WHERE t1.id=:id";
            $item = pdo_fetch($sql, array(':id' => $id));
        }
        if (checksubmit('submit')) {
            $data = array(
                'price' => $_GPC['price'],
            );
            pdo_update('xcommunity_fee', $data, array('id' => intval($_GPC['feeid'])));
            itoast('更新成功', $this->createWebUrl('fee', array('op' => 'list')), 'success');
        }
        include $this->template('web/core/fee/list/edit');
    }
    /**
     * 物业账单批量删除
     */
    if ($p == 'pldel') {
        $ids = $_GPC['ids'];
        if ($ids) {
            pdo_delete('xcommunity_fee', array('id' => $ids));
            echo json_encode(array('status' => 'end'));
            exit();
        }
        else {
            echo json_encode(array('status' => 'error'));
            exit();
        }
    }
    /**
     * 物业账单的导入
     */
    if ($p == 'import') {
        if ($_W['isajax']) {
            $rows = model_execl::import('fee');
            if (empty($rows[1][0])) {
                echo json_encode(array('content' => '更新导入，请填写账单的id'));
                exit();
            }
            foreach ($rows as $rownum => $col) {
                if ($rownum >= 0) {
                    if ($col[0]) {
                        $birth = explode('~', $col[6]);
                        $starttime = strtotime($birth[0]);
                        $endtime = strtotime($birth[1]);
                        pdo_update('xcommunity_fee', array('price' => $col[9], 'starttime' => $starttime, 'endtime' => $endtime), array('id' => $col[0]));
                    }
                }
            }
            util::permlog('', '导入账单信息');
            echo json_encode(array('result' => 1, 'content' => '导入完成！'));
            exit();
        }
        include $this->template('web/core/fee/list/import');
    }
    /**
     * 物业账单获取该小区收费分组
     */
    if ($p == 'group') {
        if ($_W['isajax']) {
            $regionid = intval($_GPC['regionid']);
            $groups = pdo_getall('xcommunity_fee_group', array('regionid' => $regionid), array());
            echo json_encode($groups);
            exit();
        }
    }
    /**
     * 物业账单的单个添加
     */
    if ($p == 'post') {
        if ($_W['isajax']) {
            $categoryids = $_GPC['categoryid'];
            if ($categoryids) {
                $cids = implode(',', $categoryids);
            }
            $csql = "select * from" . tablename('xcommunity_fee_category') . "where id in (" . $cids . ")";
            $categories = pdo_fetchall($csql);
            $condition = array();
            if ($_GPC['addressid']) {
                $condition['id'] = intval($_GPC['addressid']);
            }
            else {
                if ($_GPC['regionid']) {
                    $condition['regionid'] = intval($_GPC['regionid']);
                }
                if ($_GPC['area']) {
                    $condition['areaid'] = intval($_GPC['area']);
                }
                if ($_GPC['build']) {
                    $condition['buildid'] = intval($_GPC['build']);
                }
                if ($_GPC['unit']) {
                    $condition['unitid'] = intval($_GPC['unit']);
                }
            }
            $group = intval($_GPC['group']);
            $rooms = pdo_getall('xcommunity_member_room', $condition, array('square', 'id', 'floor_num'));// 房号
            //查询分组数据
            $groups = pdo_get('xcommunity_fee_group', array('id' => $group), array('status'));
            foreach ($categories as $key => $category) {
                if ($category['way'] != 4) {
                    //去除按计量收费
                    foreach ($rooms as $k => $room) {
                        //去除按计量收费
                        if ($groups['status'] == 1) {
                            //按楼宇计算规则计算价格
                            $floors = pdo_getall('xcommunity_fee_group_floor_price', array('groupid' => $group));
                            foreach ($floors as $val) {
                                $floor = explode(',', $val['floor']);
                                if ($room['floor_num'] >= $floor[0] && $room['floor_num'] <= $floor[1]) {
                                    $price = $val['price'];
                                }
                            }

                        }
                        else {
                            //统一计算
                            $price = $category['price'];
                        }
                        if ($category['way'] == 2 || $category['way'] == 3) {
                            $price = $price * $category['cycle'];
                        }
                        elseif ($category['way'] == 1) {
                            $price = $price * $room['square'] * $category['cycle'];
                        }
                        //计算滞纳金
                        $endday = strtotime($_GPC['enddate']) + $category['start_day'] * 24 * 60 * 60;
                        $rate = '';
                        if (TIMESTAMP > $endday) {
                            $day = round((TIMESTAMP - $endday) / (24 * 60 * 60));//滞纳金的天数
                            $rate = ($category['rate'] / 1000) * $price * $day;
                        }
//                    $cycle = "-" . $category['cycle'] . "months";
//                    $starttime = date("Y-m-d H:i:s", (strtotime($cycle, strtotime($_GPC['enddate']))+86400));
                        $data = array(
                            'uniacid'    => $_W['uniacid'],
                            'categoryid' => $category['id'],
                            'roomid'     => $room['id'],
                            'starttime'  => strtotime($_GPC['startdate']),
                            'endtime'    => strtotime($_GPC['enddate']),
                            'price'      => $category['discount'] ? $price * ($category['discount'] * 10 / 100) : $price,
                            'rate'       => $rate,
                            'status'     => 1,
                            'type'       => 1,
                            'regionid'   => intval($_GPC['regionid']),
                            'createtime' => TIMESTAMP
//                        'logid' => $logid
                        );
                        pdo_insert('xcommunity_fee', $data);
                    }
                }
            }
            echo json_encode(array('status' => 1));
            exit();
        }
        include $this->template('web/core/fee/list/post');
    }
    /**
     * 新增账单的导入
     */
    if ($p == 'newImport') {
        if ($_W['isajax']) {
            $dat = array(
                'uniacid'  => $_W['uniacid'],
                'regionid' => intval($_GPC['regionid']),
                'title'    => trim($_GPC['title']),
                'remark'   => trim($_GPC['remark']),
                'status'   => 1,
                'endtime'  => strtotime($_GPC['enddate'])
            );
            $rooms = pdo_getall('xcommunity_member_room', array('regionid' => intval($_GPC['regionid'])), array('id', 'address'), 'address');
            if (pdo_insert('xcommunity_fee_log', $dat)) {
                $logid = pdo_insertid();
                $fees = model_execl::import('fee');
                $title = '';
                foreach ($fees as $rownum => $col) {
                    if ($rownum > 0) {
                        if ($col[0]) {
                            $room = $col[0];
                            if ($rooms[$room]['id']) {
                                $setime = explode('~', $col[1]);
                                $data = array(
                                    'uniacid'    => $_W['uniacid'],
                                    'categoryid' => $_GPC['category'],
                                    'roomid'     => $rooms[$room]['id'],
                                    'starttime'  => strtotime($setime[0]),
                                    'endtime'    => strtotime($setime[1]),
                                    'price'      => $col[3],
                                    'status'     => $col[2] == '是' ? 2 : 1,
                                    'type'       => 1,
                                    'logid'      => $logid,
                                    'createtime' => TIMESTAMP,
                                    'regionid'   => intval($_GPC['regionid'])
                                );
                                pdo_insert('xcommunity_fee', $data);
                            }
                            else {
                                $title = '部分房号费用未导入成功,请检查房屋管理中是否存在房号';
                            }
                        }
                    }

                }
                util::permlog('', '导入账单信息');
                echo json_encode(array('result' => 1, 'content' => '导入完成！' . $title));
                exit();
            }
        }
        include $this->template('web/core/fee/list/new_import');
    }
}
/**
 * 收费管理-获取抄表的收费项目
 */
if ($op == 'cat') {
    $regionid = intval($_GPC['regionid']);
    $condition = " regionid=:regionid and type=1 and way in(1,2,3) ";
    $params[':regionid'] = $regionid;
    $list = pdo_fetchall("select id,title from" . tablename('xcommunity_fee_category') . "where $condition", $params);
    echo json_encode($list);

}
/**
 * 收费管理-获取收费分组
 */
if ($op == 'cate') {
    $group = intval($_GPC['group']);
    if (empty($group)) {
        itoast('缺少参数');
    }
    else {
        $sql = "select t2.id,t2.title from" . tablename('xcommunity_fee_group_category') . "t1 left join" . tablename('xcommunity_fee_category') . "t2 on t1.categoryid=t2.id where t1.groupid=:groupid and t2.way in(1,2,3)";
        $categories = pdo_fetchall($sql, array(':groupid' => $group));
        echo json_encode($categories);
    }
}
/**
 * 收费管理-账单记录
 */
if ($op == 'log') {
    /**
     * 账单记录列表
     */
    if ($p == 'list') {
        $pindex = max(1, intval($_GPC['page']));
        $psize = 20;
        $condition = 't1.uniacid =:uniacid';
        $params[':uniacid'] = $_W['uniacid'];
        if ($user[type] == 3) {
            //小区管理员
            $condition .= " and t1.regionid in({$user['regionid']})";
        }
        $sql = "SELECT t1.*,t2.title as regiontitle FROM" . tablename('xcommunity_fee_log') . "t1 left join" . tablename('xcommunity_region') . "t2 on t2.id = t1.regionid WHERE $condition order by t1.id desc LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
        $list = pdo_fetchall($sql, $params);
        $tsql = "SELECT count(*) FROM" . tablename('xcommunity_fee_log') . "t1 left join" . tablename('xcommunity_region') . "t2 on t2.id = t1.regionid WHERE $condition order by t1.id desc ";
        $total = pdo_fetchcolumn($tsql, $params);
        $pager = pagination($total, $pindex, $psize);
        include $this->template('web/core/fee/log/list');
    }
    /**
     * 账单记录的修改
     */
    if ($p == 'add') {
        $id = intval($_GPC['id']);
        if ($id) {
            $item = pdo_fetch("SELECT t1.*,t2.title as regiontitle FROM" . tablename('xcommunity_fee_log') . "t1 left join" . tablename('xcommunity_region') . "t2 on t2.id = t1.regionid WHERE t1.id=:id", array(':id' => $id));
        }
        if ($_W['isajax']) {
            $data = array(
                'title'     => trim($_GPC['title']),
                'regionid'  => intval($_GPC['regionid']),
                'starttime' => strtotime($_GPC['startdate']),
                'endtime'   => strtotime($_GPC['enddate']),
                'remark'    => trim($_GPC['remark']),
                'status'    => intval($_GPC['status'])
            );
            if ($id) {
                pdo_update('xcommunity_fee_log', $data, array('id' => $id));
            }
            else {
                pdo_insert('xcommunity_fee_log', $data);
            }
            echo json_encode(array('status' => 1));
            exit();
        }
        include $this->template('web/core/fee/log/add');
    }
    /**
     * 物业账单记录的删除
     */
    if ($p == 'del') {
        $id = intval($_GPC['id']);
        if (empty($id)) {
            itoast('缺少参数');
            exit();
        }
        $item = pdo_get('xcommunity_fee_log', array('id' => $id));
        if ($item) {
            pdo_delete('xcommunity_fee', array('logid' => $id));
            if (pdo_delete('xcommunity_fee_log', array('id' => $id))) {
                itoast('删除成功', $this->createWebUrl('fee', array('op' => 'log')), 'success');
            }
        }
    }
}
/**
 * 收费管理-账单统计
 */
if ($op == 'order') {
    /**
     * 账单统计列表
     */
    if ($p == 'list') {
        // 退款
        if (checksubmit('refund')) {
            $id = intval($_GPC['id']);
            if ($id) {
                $data = array(
                    'remark'    => $_GPC['remark'],
                    'status'    => 1,
                    'paytype'   => 0,
                    'paytime'   => 0,
                    'par_price' => 0
                );
                $r = pdo_update('xcommunity_fee', $data, array('id' => $id));
                if ($r) {
                    pdo_delete('xcommunity_fee_order', array('feeid' => $id));
                    itoast('退款成功', referer(), 'success');
                }
            }
        }
        $p161 = set('p161') ? set('p161') : 0;
        $arr = util::xqset('');
        $pindex = max(1, intval($_GPC['page']));
        $psize = $_GPC['export'] == 1 ? 10000 : 20;
        $condition = array();
        $condition['uniacid'] = $_W['uniacid'];
        $condition['status'] = 2;
        if (!empty($_GPC['keyword'])) {
            $room1 = pdo_getall('xcommunity_member_room', array('room like' => "%{$_GPC['keyword']}%"), array('id'));
            $room1_ids = _array_column($room1, 'id');
            $condition['roomid'] = $room1_ids;
        }
        if (intval($_GPC['regionid'])) {
            $condition['regionid'] = intval($_GPC['regionid']);
        }
//        if (intval($_GPC['status'])) {
//            $condition['status'] = intval($_GPC['status']);
//        }
        if (intval($_GPC['paytype'])) {
            $condition['paytype'] = intval($_GPC['paytype']);
        }
        $starttime = strtotime($_GPC['birth']['start']);
        $endtime = strtotime($_GPC['birth']['end']);
        if (!empty($starttime)) {
            $endtime = $endtime + 86400 - 1;
        }
        if ($starttime && $endtime) {
            $condition['paytime >='] = $starttime;
            $condition['paytime <='] = $endtime;
        }
        if ($_GPC['price']) {
            if ($_GPC['price'] == 1) {
                $condition['price >'] = 0;
            }
            if ($_GPC['price'] == 2) {
                $condition['price <='] = 0;
            }
        }
        $fees = pdo_getslice('xcommunity_fee', $condition, array($pindex, $psize), $total, '', '', array('paytime desc', 'id desc'));
        // 房屋
        $fees_roomids = _array_column($fees, 'roomid');
        $rooms = pdo_getall('xcommunity_member_room', array('id' => $fees_roomids), array('id', 'area', 'build', 'unit', 'room', 'regionid'), 'id');
        // 收费项目
        $fees_cids = _array_column($fees, 'categoryid');
        $categorys = pdo_getall('xcommunity_fee_category', array('id' => $fees_cids), array('id', 'title'), 'id');
        // 小区
        $rooms_rids = _array_column($rooms, 'regionid');
        $regions = pdo_getall('xcommunity_region', array('id' => $rooms_rids), array('id', 'title'), 'id');
        $list = array();
        foreach ($fees as $k => $v) {
            if ($v['type'] == 2) {
                //抄表
                $jftime = $v['starttime'] && $v['endtime'] ? date('Y-m-d', $v['starttime']) . '~' . date('Y-m-d', $v['endtime']) : date('Y-m-d', $v['createtime']);
            }
            else {
                $jftime = date('Y-m-d', $v['starttime']) . '~' . date('Y-m-d', $v['endtime']);
            }
            if ($rooms[$v['roomid']]['area']) {
                $buildTitle = $rooms[$v['roomid']]['area'] . $arr[a1] . $rooms[$v['roomid']]['build'];
            }
            $list[] = array(
                'id'          => $v['id'],
                'jftime'      => $jftime,
                'status'      => $v['status'],
                'price'       => $v['price'],
                'pay_price'   => $v['pay_price'],
                'paytype'     => $v['paytype'],
                'paytime'     => date('Y-m-d H:i', $v['paytime']),
                'username'    => $v['username'],
                'roomid'      => $v['roomid'],
                'build'       => $buildTitle ? $buildTitle : $rooms[$v['roomid']]['build'],
                'unit'        => $rooms[$v['roomid']]['unit'],
                'room'        => $rooms[$v['roomid']]['room'],
                'title'       => $categorys[$v['categoryid']]['title'],
                'regiontitle' => $regions[$v['regionid']]['title'],
                'createtime'  => date('Y-m-d H:i', $v['createtime']),
                'payStatus'   => $v['status'] == 2 ? '已支付' : '未支付'
            );
        }
        if ($_GPC['export'] == 1) {
            model_execl::export($list, array(
                "title"   => "物业账单数据-" . date('Y-m-d-H-i', time()),
                "columns" => array(
                    array(
                        'title' => '账单ID',
                        'field' => 'id',
                        'width' => 10
                    ),
                    array(
                        'title' => '收费项目',
                        'field' => 'title',
                        'width' => 12
                    ),
                    array(
                        'title' => '所属小区',
                        'field' => 'regiontitle',
                        'width' => 12
                    ),
                    array(
                        'title' => '楼宇',
                        'field' => 'build',
                        'width' => 18
                    ),
                    array(
                        'title' => '单元',
                        'field' => 'unit',
                        'width' => 18
                    ),
                    array(
                        'title' => '房号',
                        'field' => 'room',
                        'width' => 12
                    ),
                    array(
                        'title' => '账单日期',
                        'field' => 'jftime',
                        'width' => 12
                    ),
                    array(
                        'title' => '状态',
                        'field' => 'payStatus',
                        'width' => 12
                    ),
                    array(
                        'title' => '费用',
                        'field' => 'price',
                        'width' => 12
                    ),
                    array(
                        'title' => '实付',
                        'field' => 'pay_price',
                        'width' => 18
                    ),
                    array(
                        'title' => '付款时间',
                        'field' => 'paytime',
                        'width' => 18
                    ),
                )
            ));
        }
        $pager = pagination($total, $pindex, $psize);
        include $this->template('web/core/fee/order/list');
    }
    /**
     * 账单统计的详情
     */
    if ($p == 'add') {
        $id = intval($_GPC['id']);
        if (empty($id)) {
            itoaset('缺少参数');
        }
        if ($id) {
            $sql = "SELECT t1.*,t2.build,t2.unit,t2.room,t3.title,t4.title as regiontitle,t3.way,t3.unit,t3.cycle,t1.paytype FROM" . tablename('xcommunity_fee') . "t1 left join" . tablename('xcommunity_member_room') . "t2 on t1.roomid = t2.id left join" . tablename('xcommunity_fee_category') . "t3 on t3.id = t1.categoryid left join" . tablename('xcommunity_region') . "t4 on t4.id = t2.regionid WHERE t1.id=:id";
            $item = pdo_fetch($sql, array(':id' => $id));
        }
        include $this->template('web/core/fee/order/add');
    }
    /**
     * 物业账单的删除
     */
    if ($p == 'del') {
        $id = intval($_GPC['id']);
        if (empty($id)) {
            itoast('缺少参数');
        }
        $item = pdo_get('xcommunity_fee', array('id' => $id));
        if ($item) {
            if (pdo_delete('xcommunity_fee', array('id' => $id))) {
                itoast('删除成功', referer(), 'success');
            }
        }
    }
    /**
     * 账单的打印
     */
    if ($p == 'print') {
        $id = intval($_GPC['id']);
        if (empty($id)) {
            itoast('缺少参数');
        }
        if ($id) {
            $sql = "SELECT t1.*,t2.address,t2.build,t2.unit,t2.room,t3.title,t4.title as regiontitle,t1.paytype,t5.title as ptitle,t4.stamp,t8.realname FROM" . tablename('xcommunity_fee') . "t1 left join" . tablename('xcommunity_member_room') . "t2 on t1.roomid = t2.id left join" . tablename('xcommunity_fee_category') . "t3 on t3.id = t1.categoryid left join" . tablename('xcommunity_region') . "t4 on t4.id = t2.regionid left join" . tablename('xcommunity_property') . "t5 on t4.pid=t5.id left join" . tablename('xcommunity_member_bind') . "t6 on t2.id=t6.addressid left join" . tablename('xcommunity_member') . "t7 on t7.id=t6.memberid left join" . tablename('mc_members') . "t8 on t8.uid=t7.uid WHERE t1.id=:id";
            $item = pdo_fetch($sql, array(':id' => $id));
            $p161 = set('p161');
            if (empty($p161) && $item['price'] <= 0) {
                itoast('暂无打印', referer(), 'error');
            }
            $item['no'] = sprintf("%010d", $id);
            $item['dprice'] = num_to_rmb($item['price']);
            $table = '<tr style="height: 23px"><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>';
            $tab = '';
            for ($i = 0; $i < 9; $i++) {
                $tab .= $table;
            }
            if ($id) {
                $sql = "SELECT t1.*,t2.address,t2.build,t2.unit,t2.room,t3.title,t4.title as regiontitle,t1.paytype,t5.title as ptitle,t4.stamp,t8.realname FROM" . tablename('xcommunity_fee') . "t1 left join" . tablename('xcommunity_member_room') . "t2 on t1.roomid = t2.id left join" . tablename('xcommunity_fee_category') . "t3 on t3.id = t1.categoryid left join" . tablename('xcommunity_region') . "t4 on t4.id = t2.regionid left join" . tablename('xcommunity_property') . "t5 on t4.pid=t5.id left join" . tablename('xcommunity_member_bind') . "t6 on t2.id=t6.addressid left join" . tablename('xcommunity_member') . "t7 on t7.id=t6.memberid left join" . tablename('mc_members') . "t8 on t8.uid=t7.uid WHERE t1.id=:id";
                $item = pdo_fetch($sql, array(':id' => $id));
                $p = set('p161');
                if (empty($p) && $item['price'] <= 0) {
                    itoast('暂无打印', referer(), 'error');
                }
                $item['no'] = sprintf("%010d", $id);
                $item['dprice'] = num_to_rmb($item['price']);
                $table = '<tr style="height: 23px"><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>';
                $tab = '';
                for ($i = 0; $i < 9; $i++) {
                    $tab .= $table;
                }
            }
        }
        include $this->template('web/core/fee/order/print');
    }
}
/**
 * 收费管理-收银台
 */
if ($op == 'center') {
    /**
     * 收银台列表
     */
    if ($p == 'list') {
        $categorys = pdo_getall('xcommunity_fee_category', array('uniacid' => $_W['uniacid']), array('id', 'title'), 'id');
        $pindex = max(1, intval($_GPC['page']));
        $psize = $_GPC['export'] == 1 ? 100000 : 20;
        $condition = array();
        $condition['uniacid'] = $_W['uniacid'];
        $condition['status'] = 1;
        $con = array();
        $con['uniacid'] = $_W['uniacid'];
        if (!empty($_GPC['categoryid'])) {
            $condition['categoryid'] = intval($_GPC['categoryid']);
        }
        if (intval($_GPC['regionid'])) {
            $condition['regionid'] = intval($_GPC['regionid']);
            $con['regionid'] = intval($_GPC['regionid']);
            $arr = util::xqset($_GPC['regionid']);
            if (intval($_GPC['buildid'])) {
                $con['buildid'] = intval($_GPC['buildid']);
                $builds = pdo_getall('xcommunity_build', array('regionid' => intval($_GPC['regionid'])), array('id', 'areaid', 'buildtitle'));
                $areas = pdo_getall('xcommunity_area', array('regionid' => intval($_GPC['regionid'])), array('id', 'title'), 'id');
                foreach ($builds as $k => $v) {
                    $builds[$k]['title'] = $areas[$v['areaid']]['title'] ? $areas[$v['areaid']]['title'] . $arr[a1] . $v['buildtitle'] . $arr[b1] : $v['buildtitle'] . $arr[b1];
                }
            }
            if (intval($_GPC['unitid'])) {
                $con['unitid'] = intval($_GPC['unitid']);
                $units = pdo_getall('xcommunity_unit', array('buildid' => intval($_GPC['buildid'])), array('id', 'unit'));
                foreach ($units as $k => $v) {
                    $units[$k]['unit'] = $v['unit'] . $arr[c1];
                }
            }
        } // 没有勾选小区的判断是否为小区管理员
        else {
            if ($user && $user['type'] == 3) {
                //小区管理员
                $regionids = explode(',', $user['regionid']);
                $condition['regionid'] = $regionids;
                $con['regionid'] = $regionids;
            }
        }
        $keyword = trim($_GPC['keyword']);
        if (!empty($keyword)) {
            $con['room like'] = "%{$keyword}%";
            $rooms = pdo_getall('xcommunity_member_room', $con, array('id'));
            $rooms_ids = _array_column($rooms, 'id');
            $condition['roomid'] = $rooms_ids;
        }
        // 房屋信息
        $feesRooms = pdo_getall('xcommunity_member_room', $con, array('id', 'build', 'unit', 'room'), 'id');
        $dateStatus = intval($_GPC['dateStatus']);// 是否使用账单日期搜索
        // 搜索的账单日期

        $feeStatus = intval($_GPC['feeStatus']);
        if ($feeStatus == 2) {
            //是否开启账单时间筛选
            $starttime = strtotime($_GPC['starttime']);
            $endtime = strtotime($_GPC['endtime']);
            if ($starttime && $endtime) {
                $condition['starttime'] = $starttime;
                $condition['endtime'] = $endtime;
            }
        }
        $fees = pdo_getslice('xcommunity_fee', $condition, array($pindex, $psize), $total, '', '', array('id desc'));
        // 收费项目信息
        $fees_categoryids = _array_column($fees, 'categoryid');
        $feesCategorys = pdo_getall('xcommunity_fee_category', array('id' => $fees_categoryids), array('id', 'title'), 'id');
        // 小区信息
        $fees_regionids = _array_column($fees, 'regionid');
        $feeRegions = pdo_getall('xcommunity_region', array('id' => $fees_regionids), array('id', 'title'), 'id');
        $list = array();
        foreach ($fees as $k => $v) {
            if($feesRooms[$v['roomid']]['room']){
                $list[] = array(
                    'id'          => $v['id'],
                    'title'       => $feesCategorys[$v['categoryid']]['title'],
                    'regiontitle' => $feeRegions[$v['regionid']]['title'],
                    'build'       => $feesRooms[$v['roomid']]['build'],
                    'unit'        => $feesRooms[$v['roomid']]['unit'],
                    'room'        => $feesRooms[$v['roomid']]['room'],
                    'starttime'   => $v['starttime'],
                    'endtime'     => $v['endtime'],
                    'status'      => $v['status'],
                    'price'       => $v['price'],
                    'roomid'      => $v['roomid'],
                );
            }
        }
        $pager = pagination($total, $pindex, $psize);
        $totalPrice = pdo_getcolumn('xcommunity_fee', $condition, 'sum(price)');
        $totalPrice = sprintf('%.2f', $totalPrice);
        if ($_GPC['export'] == 1) {
            foreach ($list as $k => $v) {
                $list[$k]['createtime'] = date('Y-m-d', $v['starttime']) . '~' . date('Y-m-d', $v['endtime']);
                if ($v['status'] == 2) {
                    $list[$k]['status'] = '已支付';
                }
                else {
                    $list[$k]['status'] = '未支付';
                }
            }
            model_execl::export($list, array(
                "title"   => "物业账单数据-" . date('Y-m-d-H-i', time()),
                "columns" => array(
                    array(
                        'title' => '账单ID',
                        'field' => 'id',
                        'width' => 10
                    ),
                    array(
                        'title' => '收费项目',
                        'field' => 'title',
                        'width' => 12
                    ),
                    array(
                        'title' => '所属小区',
                        'field' => 'regiontitle',
                        'width' => 12
                    ),
                    array(
                        'title' => '楼宇',
                        'field' => 'build',
                        'width' => 18
                    ),
                    array(
                        'title' => '单元',
                        'field' => 'unit',
                        'width' => 18
                    ),
                    array(
                        'title' => '房号',
                        'field' => 'room',
                        'width' => 12
                    ),
                    array(
                        'title' => '账单日期',
                        'field' => 'createtime',
                        'width' => 12
                    ),
                    array(
                        'title' => '状态',
                        'field' => 'status',
                        'width' => 12
                    ),
                    array(
                        'title' => '费用',
                        'field' => 'price',
                        'width' => 12
                    ),
                )
            ));
        }
        include $this->template('web/core/fee/center/list');
    }
    /**
     * 收银台单笔收款
     */
    if ($p == 'add') {
        $id = intval($_GPC['id']);
        if (empty($id)) {
            itoaset('缺少参数');
        }
        if ($id) {
            $sql = "SELECT t1.*,t2.build,t2.unit,t2.room,t3.title,t4.title as regiontitle,t3.way,t3.unit,t3.cycle FROM" . tablename('xcommunity_fee') . "t1 left join" . tablename('xcommunity_member_room') . "t2 on t1.roomid = t2.id left join" . tablename('xcommunity_fee_category') . "t3 on t3.id = t1.categoryid left join" . tablename('xcommunity_region') . "t4 on t4.id = t2.regionid WHERE t1.id=:id";
            $item = pdo_fetch($sql, array(':id' => $id));
        }
        if (checksubmit('submit')) {
            $data = array(
                'uniacid'    => $_W['uniacid'],
                'feeid'      => intval($_GPC['feeid']),
                'paytype'    => intval($_GPC['paytype']),
                'rate'       => trim($_GPC['rate']),
                'price'      => trim($_GPC['pay_price']),
                'pay_price'  => trim($_GPC['pay_price']),
                'rate_price' => trim($_GPC['rate_price']),
                'rate_day'   => trim($_GPC['rate_day']),
                'status'     => 1,
                'remark'     => trim($_GPC['remark']),
                'createtime' => TIMESTAMP
            );
            if (pdo_insert('xcommunity_fee_order', $data)) {
                pdo_update('xcommunity_fee', array('pay_price' => $data['price'], 'status' => 2, 'paytype' => $data['paytype'], 'paytime' => TIMESTAMP, 'username' => trim($_GPC['username'])), array('id' => $data['feeid']));
                itoast('处理成功', $this->createWebUrl('fee', array('op' => 'center')), 'success');
            }
        }

        include $this->template('web/core/fee/center/add');
    }
    /**
     * 收银台合并收款
     */
    if ($p == 'display') {
        if ($_W['isajax']) {
            $data = array(
                'uniacid'    => $_W['uniacid'],
//                'feeid'      => intval($_GPC['feeid']),
                'paytype'    => intval($_GPC['paytype']),
                'rate'       => trim($_GPC['rate']),
//                'price'      => trim($_GPC['pay_price']),
                'rate_price' => trim($_GPC['rate_price']),
                'rate_day'   => trim($_GPC['rate_day']),
                'status'     => 1,
                'remark'     => trim($_GPC['remark']),
                'createtime' => TIMESTAMP,
            );
            $ids = explode(',', $_GPC['ids']);
            foreach ($ids as $k => $v) {
                $data['feeid'] = $v;
                $price = pdo_getcolumn('xcommunity_fee', array('id' => $v), 'price');
                $data['price'] = $price;
                if (pdo_insert('xcommunity_fee_order', $data)) {
                    pdo_update('xcommunity_fee', array('pay_price' => $price, 'status' => 2, 'paytype' => $data['paytype'], 'paytime' => TIMESTAMP, 'username' => trim($_GPC['username'])), array('id' => $v));
                }
            }
            echo json_encode(array('status' => 1));
            exit();
        }
        $roomid = intval($_GPC['roomid']);
        $pindex = max(1, intval($_GPC['page']));
        $psize = 20;
        $condition = 't1.uniacid =:uniacid and t1.status=:status and t1.roomid=:roomid';
        $params[':uniacid'] = $_W['uniacid'];
        $params[':status'] = intval($_GPC['status']);
        $params[':roomid'] = $roomid;
        $sql = "SELECT t1.*,t2.build,t2.unit,t2.room,t3.title,t4.title as regiontitle FROM" . tablename('xcommunity_fee') . "t1 left join" . tablename('xcommunity_member_room') . "t2 on t1.roomid = t2.id left join" . tablename('xcommunity_fee_category') . "t3 on t3.id = t1.categoryid left join" . tablename('xcommunity_region') . "t4 on t4.id = t2.regionid WHERE $condition order by t1.id desc LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
        $list = pdo_fetchall($sql, $params);
        $tsql = "SELECT count(*) FROM" . tablename('xcommunity_fee') . "t1 left join" . tablename('xcommunity_member_room') . "t2 on t1.roomid = t2.id left join" . tablename('xcommunity_fee_category') . "t3 on t3.id = t1.categoryid left join" . tablename('xcommunity_region') . "t4 on t4.id = t2.regionid WHERE $condition ";
        $total = pdo_fetchcolumn($tsql, $params);
        $pager = pagination($total, $pindex, $psize);
        include $this->template('web/core/fee/center/display');
    }
    /**
     * 收银台合并收款页面的价格变化
     */
    if ($p == 'change') {
        if ($_W['isajax']) {
            $ids = $_GPC['ids'];
            $price = 0;
            if (!empty($ids)) {
                foreach ($ids as $k => $v) {
                    $p = pdo_getcolumn('xcommunity_fee', array('id' => $v), 'price');
                    $price += $p;
                }
            }
            else {
                $price = 0;
            }
            $p = set('p161');
            if (empty($p) && $price <= 0) {
                echo json_encode(array('status' => 2, 'price' => $price));
                exit();
            }
            else {
                echo json_encode(array('status' => 1, 'price' => $price));
                exit();
            }
        }
    }
    /**
     * 收银台合并收款页面的打印
     */
    if ($p == 'print') {
        $ids = $_GPC['ids'];
        if (empty($ids)) {
            itoast('缺少参数');
        }
        $ids = explode(',', $_GPC['ids']);
        if ($ids) {
            $fee = pdo_get('xcommunity_fee', array('uniacid' => $_W['uniacid'], 'id' => $ids), array());
            $fees = pdo_getall('xcommunity_fee', array('uniacid' => $_W['uniacid'], 'id' => $ids), array());
            $fees_categoryids = _array_column($fees, 'categoryid');
            $categorys = pdo_getall('xcommunity_fee_category', array('uniacid' => $_W['uniacid'], 'id' => $fees_categoryids), array('id', 'title'), 'id');
            $region = pdo_get('xcommunity_region', array('uniacid' => $_W['uniacid'], 'id' => $fee['regionid']), array('id', 'title', 'stamp', 'pid'));
            $ptitle = pdo_getcolumn('xcommunity_property', array('id' => $region['pid']), 'title');
            $room = pdo_get('xcommunity_member_room', array('uniacid' => $_W['uniacid'], 'id' => $fee['roomid']), array('build', 'unit', 'room', 'address', 'id'));
            $memberid = pdo_getcolumn('xcommunity_member_bind', array('addressid' => $room['id']), 'memberid');
            $uid = pdo_getcolumn('xcommunity_member', array('id' => $memberid), 'uid');
            $realname = pdo_getcolumn('mc_members', array('uid' => $uid), 'realname');
            $total = 0;
            $stotal = '';
            $list = array();
            foreach ($fees as $k => $v) {
                if (set('p161') || $v['price'] > 0) {
                    $list[] = array(
                        'title'      => $categorys[$v['categoryid']]['title'],
                        'paytype'    => $v['paytype'],
                        'type'       => $v['type'],
                        'starttime'  => $v['starttime'],
                        'endtime'    => $v['endtime'],
                        'createtime' => $v['createtime'],
                        'price'      => $v['price'],
                        'old_num'    => $v['old_num'],
                        'new_num'    => $v['new_num'],
                        'paytime'    => $v['paytime'],
                        'username'   => $v['username'],
                        'status'     => $v['status']
                    );
                }
                $total += $v['price'];
            }
            $stotal = num_to_rmb($total);
//            $item['no'] = sprintf("%010d",$id);
            $numno = sprintf("%010d", date('YmdHis'));
            $address = $room['address'];
            $table = '<tr style="height: 23px"><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>';
            $tab = '';
            for ($i = 0; $i < 10 - count($list); $i++) {
                $tab .= $table;
            }
        }
        include $this->template('web/core/fee/center/print');
    }
    /**
     * 收银台批量支付账单
     */
    if ($p == 'plpay') {
        if ($_W['isajax']) {
            $data = array(
                'uniacid'    => $_W['uniacid'],
                'paytype'    => intval($_GPC['paytype']),
                'rate'       => trim($_GPC['rate']),
                'rate_price' => trim($_GPC['rate_price']),
                'rate_day'   => trim($_GPC['rate_day']),
                'status'     => 1,
                'remark'     => trim($_GPC['remark']),
                'createtime' => TIMESTAMP,
            );
            $ids = explode(',', $_GPC['ids']);
            foreach ($ids as $k => $v) {
                $data['feeid'] = $v;
                $price = pdo_getcolumn('xcommunity_fee', array('id' => $v), 'price');
                $data['price'] = $price;
                if (pdo_insert('xcommunity_fee_order', $data)) {
                    pdo_update('xcommunity_fee', array('pay_price' => $price, 'status' => 2, 'paytype' => $data['paytype'], 'paytime' => TIMESTAMP, 'username' => trim($_GPC['username'])), array('id' => $v));
                }
            }
            echo json_encode(array('status' => 1));
            exit();
        }
    }
    /**
     * 收银台合并收款页面的催缴打印
     */
    if ($p == 'call') {
        $ids = $_GPC['ids'];
        if (empty($ids)) {
            itoast('缺少参数');
        }
        $ids = explode(',', $_GPC['ids']);
        if ($ids) {
            $fee = pdo_get('xcommunity_fee', array('uniacid' => $_W['uniacid'], 'id' => $ids), array());
            $fees = pdo_getall('xcommunity_fee', array('uniacid' => $_W['uniacid'], 'id' => $ids), array());
            $fees_categoryids = _array_column($fees, 'categoryid');
            $categorys = pdo_getall('xcommunity_fee_category', array('uniacid' => $_W['uniacid'], 'id' => $fees_categoryids), array('id', 'title'), 'id');
            $region = pdo_get('xcommunity_region', array('uniacid' => $_W['uniacid'], 'id' => $fee['regionid']), array('id', 'title', 'stamp', 'pid'));
            $ptitle = pdo_getcolumn('xcommunity_property', array('id' => $region['pid']), 'title');
            $room = pdo_get('xcommunity_member_room', array('uniacid' => $_W['uniacid'], 'id' => $fee['roomid']), array('build', 'unit', 'room', 'address', 'id'));
            $memberid = pdo_getcolumn('xcommunity_member_bind', array('addressid' => $room['id']), 'memberid');
            $uid = pdo_getcolumn('xcommunity_member', array('id' => $memberid), 'uid');
            $realname = pdo_getcolumn('mc_members', array('uid' => $uid), 'realname');
            $total = 0;
            $stotal = '';
            $list = array();
            foreach ($fees as $k => $v) {
                if (set('p161') || $v['price'] > 0) {
                    $list[] = array(
                        'title'      => $categorys[$v['categoryid']]['title'],
                        'paytype'    => $v['paytype'],
                        'type'       => $v['type'],
                        'starttime'  => $v['starttime'],
                        'endtime'    => $v['endtime'],
                        'createtime' => $v['createtime'],
                        'price'      => $v['price'],
                        'old_num'    => $v['old_num'],
                        'new_num'    => $v['new_num'],
                        'paytime'    => $v['paytime'],
                        'username'   => $v['username'],
                        'status'     => $v['status']
                    );
                }
                $total += $v['price'];
            }
            $stotal = num_to_rmb($total);
//            $item['no'] = sprintf("%010d",$id);
            $numno = sprintf("%010d", date('YmdHis'));
            $address = $room['address'];
            $table = '<tr style="height: 23px"><td></td><td></td><td></td><td></td><td></td></tr>';
            $tab = '';
            for ($i = 0; $i < 10 - count($list); $i++) {
                $tab .= $table;
            }
        }
        include $this->template('web/core/fee/center/call');
    }
}
/**
 * 收费管理-抄表
 */
if ($op == 'entery') {
    /**
     * 抄表的列表
     */
    if ($p == 'list') {
        // 批量删除
        if (checksubmit('del')) {
            $ids = $_GPC['ids'];
            if (!empty($ids)) {
                pdo_delete('xcommunity_fee', array('id' => $ids));
                $idss = implode(',', $ids);
                util::permlog('', '批量删除抄表(' . $idss . ')');
                itoast('删除成功', referer(), 'success', true);
            }
        }
        if (checksubmit('update')) {
            $id = $_GPC['enteryid'];
            if ($id) {
                $item = pdo_get('xcommunity_fee', array('id' => $id), array('price', 'id'));
                if ($item) {
                    $data = array(
                        'status'    => 2,
                        'paytype'   => intval($_GPC['paytype']),
                        'paytime'   => TIMESTAMP,
                        'pay_price' => $item['price']
                    );
                    $r = pdo_update("xcommunity_fee", $data, array("id" => $id, "uniacid" => $_W['uniacid']));
                    if ($r) {
                        itoast('提交成功', referer(), 'success');
                    }
                }
            }

        }
        $pindex = max(1, intval($_GPC['page']));
        $psize = 20;
        $condition = 't1.uniacid =:uniacid and t1.type=2';
        $params[':uniacid'] = $_W['uniacid'];
        if (!empty($_GPC['regionid'])) {
            $condition .= " AND t4.regionid = '{$_GPC['regionid']}'";
        }
        if ($user && $user[type] == 3) {
            //小区管理员
            $condition .= " and t4.regionid in({$user['regionid']})";
        }
        if (!empty($_GPC['category'])) {
            $condition .= " AND t1.categoryid = '{$_GPC['category']}'";
        }
        $status = intval($_GPC['status']);
        if ($status) {
            $condition .= " AND t1.status = '{$status}'";
        }
        if (!empty($_GPC['keyword'])) {
            $condition .= " AND t4.address LIKE '%{$_GPC['keyword']}%'";
        }
        $sql = "SELECT t1.*,t2.title,t3.title as ctitle,t4.address as raddress FROM" . tablename('xcommunity_fee') . "t1 left join" . tablename('xcommunity_fee_category') . "t3 on t1.categoryid=t3.id left join" . tablename('xcommunity_member_room') . "t4 on t1.roomid=t4.id left join" . tablename('xcommunity_region') . "t2 on t4.regionid=t2.id WHERE $condition order by t1.id desc LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
        $list = pdo_fetchall($sql, $params);
        $tsql = "SELECT count(*) FROM" . tablename('xcommunity_fee') . "t1 left join" . tablename('xcommunity_fee_category') . "t3 on t1.categoryid=t3.id left join" . tablename('xcommunity_member_room') . "t4 on t1.roomid=t4.id left join" . tablename('xcommunity_region') . "t2 on t4.regionid=t2.id WHERE $condition ";
        $total = pdo_fetchcolumn($tsql, $params);
        $pager = pagination($total, $pindex, $psize);
        if ($_GPC['export'] == 1) {
            $sql = "SELECT t1.*,t2.title,t3.title as ctitle,t4.address as raddress FROM" . tablename('xcommunity_fee') . "t1 left join" . tablename('xcommunity_fee_category') . "t3 on t1.categoryid=t3.id left join" . tablename('xcommunity_member_room') . "t4 on t1.roomid=t4.id left join" . tablename('xcommunity_region') . "t2 on t4.regionid=t2.id WHERE $condition order by t1.id desc ";
            $list1 = pdo_fetchall($sql, $params);
            foreach ($list1 as $k => $v) {
                $list1[$k]['createtime'] = date('Y-m-d H:i', $v['createtime']);
                $list1[$k]['setime'] = $v['starttime'] && $v['endtime'] ? date('Y-m-d', $v['starttime']) . '~' . date('Y-m-d', $v['endtime']) : '';
                $paytype = array('', '余额', '微信支付', '货到付款', '支付宝', '现金', '银联刷卡');
                $list1[$k]['paytype'] = $paytype[$v['paytype']];
                $list1[$k]['status'] = $v['status'] == 2 ? '已支付' : '未支付';
            }
            model_execl::export($list1, array(
                "title"   => "抄表数据-" . date('Y-m-d-H-i', time()),
                "columns" => array(
                    array(
                        'title' => '抄表ID',
                        'field' => 'id',
                        'width' => 10
                    ),
                    array(
                        'title' => '小区名称',
                        'field' => 'title',
                        'width' => 18
                    ),
                    array(
                        'title' => '地址',
                        'field' => 'raddress',
                        'width' => 18
                    ),
                    array(
                        'title' => '抄表类型',
                        'field' => 'ctitle',
                        'width' => 12
                    ),
                    array(
                        'title' => '上期度数',
                        'field' => 'old_num',
                        'width' => 12
                    ),
                    array(
                        'title' => '本期度数',
                        'field' => 'new_num',
                        'width' => 12
                    ),
                    array(
                        'title' => '费用',
                        'field' => 'price',
                        'width' => 12
                    ),
                    array(
                        'title' => '抄表时间',
                        'field' => 'createtime',
                        'width' => 18
                    ),
                    array(
                        'title' => '日期',
                        'field' => 'setime',
                        'width' => 24
                    ),
                    array(
                        'title' => '支付状态',
                        'field' => 'status',
                        'width' => 18
                    ),
                    array(
                        'title' => '支付方式',
                        'field' => 'paytype',
                        'width' => 18
                    )
                )
            ));
        }
        include $this->template('web/core/fee/entery/list');
    }
    /**
     * 抄表的添加
     */
    if ($p == 'add') {
        $id = intval($_GPC['id']);
        if ($id) {
            $sql = "SELECT t1.*,t4.regionid FROM" . tablename('xcommunity_fee') . "t1 left join" . tablename('xcommunity_fee_category') . "t3 on t1.categoryid=t3.id left join" . tablename('xcommunity_member_room') . "t4 on t1.roomid=t4.id left join" . tablename('xcommunity_region') . "t2 on t4.regionid=t2.id WHERE t1.id=:id";
            $item = pdo_fetch($sql, array(':id' => $id));
            $arr = util::xqset($item['regionid']);
            $address = pdo_fetch("select * from" . tablename('xcommunity_member_room') . "where id=:id", array(':id' => $item['roomid']));
            $areas = pdo_getall('xcommunity_area', array('regionid' => $item['regionid']), array('id', 'title'));
            $builds = pdo_fetchall("select t1.*,t2.title from" . tablename('xcommunity_build') . "t1 left join " . tablename('xcommunity_area') . "t2 on t1.areaid=t2.id where t1.regionid=:regionid and t1.areaid=:areaid", array(':areaid' => $address['areaid'], ':regionid' => $item['regionid']));
            $units = pdo_fetchall("select * from" . tablename('xcommunity_unit') . "where buildid=:buildid", array(':buildid' => $address['buildid']));
            $rooms = pdo_fetchall("select * from" . tablename('xcommunity_member_room') . "where unitid=:unitid", array(':unitid' => $address['unitid']));
            $fees = pdo_getall('xcommunity_fee_category', array('uniacid' => $_W['uniacid'], 'regionid' => $item['regionid'], 'way' => 4), array('id', 'title', 'price'));
        }
        if ($_W['isajax']) {
            $fee = pdo_getcolumn('xcommunity_fee_category', array('id' => intval($_GPC['fee'])), 'price');
            $price = 0;
            $num = $_GPC['new_num'] - $_GPC['old_num'];
            if ($num > 0) {
                $price = $num * $fee;
            }
            $data = array(
                'uniacid'    => $_W['uniacid'],
                'roomid'     => trim($_GPC['addressid']),
                'categoryid' => intval($_GPC['fee']),
                'old_num'    => trim($_GPC['old_num']),
                'new_num'    => trim($_GPC['new_num']),
                'price'      => $price,
                'type'       => 2,
                'status'     => 1,
                'readername' => trim($_GPC['readername']),
                'regionid'   => intval($_GPC['regionid']),
                'starttime'  => strtotime($_GPC['starttime']),
                'endtime'    => strtotime($_GPC['endtime'])
            );
            if ($id) {
                pdo_update('xcommunity_fee', $data, array('id' => $id));
            }
            else {
                $y = date("Y", time());
                $m = date("m", time());
                $d = date("d", time());
                $t0 = date('t'); // 本月一共有几天
                $stime = mktime(0, 0, 0, $m, 1, $y); // 创建本月开始时间
                $etime = mktime(23, 59, 59, $m, $t0, $y); // 创建本月结束时间
                $mfee = pdo_fetch("select id from" . tablename('xcommunity_fee') . " where type=2 and roomid=:roomid and categoryid=:cate and createtime between :stime and :etime", array(':roomid' => intval($_GPC['addressid']), ':cate' => intval($_GPC['fee']), ':stime' => $stime, ':etime' => $etime));
                if ($mfee) {
                    echo json_encode(array('content' => '本月已经抄过一次表'));
                    exit();
                }
                $data['uid'] = $_W['uid'];
                $data['createtime'] = TIMESTAMP;
                pdo_insert('xcommunity_fee', $data);
            }
            echo json_encode(array('status' => 1));
            exit();
        }
        include $this->template('web/core/fee/entery/add');
    }
    /**
     * 抄表的删除
     */
    if ($p == 'del') {
        $id = intval($_GPC['id']);
        if (empty($id)) {
            itoast('缺少参数');
        }
        $item = pdo_get('xcommunity_fee', array('id' => $id));
        if ($item) {
            if (pdo_delete('xcommunity_fee', array('id' => $id))) {
                itoast('删除成功', referer(), 'success');
            }
        }
    }
    /**
     * 抄表的导入
     */
    if ($p == 'import') {
        if ($_W['isajax']) {
            $rows = model_execl::read('room');
            if ($rows[0][2] != '地址' || $rows[0][4] != '上期度数' || $rows[0][5] != '本期度数') {
                echo json_encode(array('content' => '文件内容不符，请重新上传'));
                exit();
            }
            $regionid = intval($_GPC['regionid']);
            $category = intval($_GPC['category']);
            $dat = array(
                'uniacid'  => $_W['uniacid'],
                'regionid' => $regionid,
                'title'    => trim($_GPC['title']),
                'remark'   => trim($_GPC['remark']),
                'status'   => 0,
                'endtime'  => strtotime($_GPC['enddate'])
            );
            pdo_insert('xcommunity_fee_log', $dat);
            $logid = pdo_insertid();
            foreach ($rows as $rownum => $col) {
                if ($rownum > 0) {
                    $address = pdo_fetch("select * from" . tablename('xcommunity_member_room') . "where address=:address and regionid=:regionid", array(':address' => $col[2], ':regionid' => $regionid));
                    $fee = pdo_getcolumn('xcommunity_fee_category', array('id' => $category), 'price');
                    $price = 0;
                    $num = $col[5] - $col[4];
                    if ($num > 0) {
                        $price = $num * $fee;
                    }
                    $rangeTime = explode('~', $col[8]);
                    if ($col[0]) {
                        //更新抄表数据
                        $data = array(
                            'old_num'   => $col[4],
                            'new_num'   => $col[5],
                            'price'     => $col[6] ? $col[6] : $price,
                            'starttime' => strtotime($rangeTime[0]),
                            'endtime'   => strtotime($rangeTime[1])
                        );
                        pdo_update('xcommunity_fee', $data, array('id' => $col[0]));
                        // 修改的id添加超链接
                        $url = $this->createWebUrl('fee', array('op' => 'entery', 'id' => $col[0], 'p' => 'add'));
                        util::permlog('抄表录入-更新', '抄表信息ID:<a href="' . $url . '">' . $col[0] . '</a>');
                    }
                    else {
                        if ($address) {
                            $data = array(
                                'uid'        => $_W['uid'],
                                'uniacid'    => $_W['uniacid'],
                                'roomid'     => $address['id'],
                                'old_num'    => $col[4],
                                'new_num'    => $col[5],
                                'categoryid' => $category,
                                'createtime' => $col[7] ? strtotime($col[7]) : strtotime($_GPC['enddate']),
                                'type'       => 2,
                                'status'     => 1,
                                'price'      => $col[6] ? $col[6] : $price,
                                'logid'      => $logid,
                                'regionid'   => intval($_GPC['regionid']),
                                'starttime'  => strtotime($rangeTime[0]),
                                'endtime'    => strtotime($rangeTime[1])
                            );
                            pdo_insert('xcommunity_fee', $data);
                            $enteryid = pdo_insertid();
                            // 修改的id添加超链接
                            $url = $this->createWebUrl('fee', array('op' => 'entery', 'id' => $enteryid, 'p' => 'add'));
                            util::permlog('抄表录入-导入', '抄表信息ID:<a href="' . $url . '">' . $enteryid . '</a>');
                        }
                    }
                }
            }
            util::permlog('', '导入抄表信息');
            echo json_encode(array('result' => 1, 'content' => '导入完成！'));
            exit();
        }
        include $this->template('web/core/fee/entery/import');
    }
    /**
     * 生成抄表的费用
     */
    if ($p == 'edit') {
        if ($_W['isajax']) {
            $regionid = intval($_GPC['regionid']);
            $categorys = pdo_getall('xcommunity_fee_category', array('regionid' => $regionid, 'way' => 4), array());
            echo json_encode($categorys);
            exit();
        }
        if (checksubmit('submit')) {
            $group = intval($_GPC['group']);
            $regionid = intval($_GPC['regionid']);
            $price = pdo_getcolumn('xcommunity_fee_category', array('id' => $group), 'price');
            $enterys = pdo_fetchall("select t1.new_num,t1.old_num,t1.id from" . tablename('xcommunity_fee') . "t1 left join" . tablename('xcommunity_member_room') . "t2 on t1.roomid=t2.id where t1.categoryid=:cid and t1.type=2 and t2.regionid=:regionid", array(':cid' => $group, ':regionid' => $regionid));
            foreach ($enterys as $k => $v) {
                $num = $v['new_num'] - $v['old_num'];
                if ($num > 0) {
                    $fee = $price * $num;
                    pdo_update('xcommunity_fee', array('price' => $fee), array('id' => $v['id']));
                }
            }
            itoast('更新成功', $this->createWebUrl('fee', array('op' => 'entery')), 'success');
        }
        include $this->template('web/core/fee/entery/edit');
    }
    /**
     * 批量生成抄表的二维码
     */
    if ($p == 'qr') {
        if (checksubmit('submit')) {
            $regionid = intval($_GPC['regionid']);
            $category = intval($_GPC['category']);
            if (empty($regionid) || empty($category)) {
                itoast('请选择小区和类型', referer(), 'error');
            }
            $rooms = pdo_fetchall("select id,address from" . tablename('xcommunity_member_room') . "where regionid=:regionid", array(':regionid' => $regionid));
            $title = pdo_getcolumn('xcommunity_region', array('id' => $regionid), 'title');
            $name = pdo_getcolumn('xcommunity_fee_category', array('id' => $category), 'title');
            $time = TIMESTAMP;
            $data = array(
                'uniacid'    => $_W['uniacid'],
                'uid'        => $_W['uid'],
                'createtime' => TIMESTAMP,
                'region'     => $title,
                'category'   => $name,
                'num'        => count($rooms)
            );
            pdo_insert('xcommunity_entery_log', $data);
            foreach ($rooms as $k => $v) {
                $url = $_W['siteroot'] . "app/index.php?i={$_W['uniacid']}&c=entry&id={$v['id']}&category={$category}&do=entery&op=add&m=" . $this->module['name'] . "&t=" . $time;//二维码内容
                $temp = $v['address'] . ".png";
                $tmpdir = "../addons/" . $this->module['name'] . "/data/qrcode/entery/" . $_W['uniacid'] . "/" . $title . "/" . $name . "/" . $data['createtime'] . '/';
                $qr = createQr($url, $temp, $tmpdir);
            }
            itoast('更新成功', $this->createWebUrl('fee', array('op' => 'entery', 'p' => 'qrlist')), 'success');
        }
        include $this->template('web/core/fee/entery/qr');
    }
    /**
     * 抄表获取该小区收费项目
     */
    if ($p == 'category') {
        $regionid = intval($_GPC['regionid']);
        $fees = pdo_getall('xcommunity_fee_category', array('uniacid' => $_W['uniacid'], 'regionid' => $regionid, 'way' => 4, 'type' => 1), array('id', 'title', 'price'));
        echo json_encode($fees);
        exit();
    }
    /**
     *抄表数据列表
     */
    if ($p == 'qrlist') {
        $pindex = max(1, intval($_GPC['page']));
        $psize = 20;
        $condition = 'uniacid =:uniacid';
        $params[':uniacid'] = $_W['uniacid'];
        $sql = "SELECT * FROM" . tablename('xcommunity_entery_log') . "WHERE $condition order by id desc LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
        $list = pdo_fetchall($sql, $params);
        $tsql = "SELECT count(*) FROM" . tablename('xcommunity_entery_log') . "WHERE $condition";
        $total = pdo_fetchcolumn($tsql, $params);
        $pager = pagination($total, $pindex, $psize);
        include $this->template('web/core/fee/entery/qrlist');
    }
    /**
     * 删除批量生成二维码记录
     */
    if ($p == 'qrdel') {
        $id = intval($_GPC['id']);
        if ($id) {
            $item = pdo_get('xcommunity_entery_log', array('id' => $id));
            if (empty($item)) {
                message('信息不存在或已删除', referer(), 'error');
            }
        }
        load()->func('file');
        $tmpdir = MODULE_ROOT . '/data/qrcode/entery/' . $item['uniacid'] . '/' . $item['region'] . '/' . $item['category'] . '/' . $item['createtime'] . '/';
        rmdirs($tmpdir);
        if (pdo_delete('xcommunity_entery_log', array('id' => $id))) {
            itoast('删除成功', referer(), 'success');
        }
    }
    /**
     * 批量下载二维码
     */
    if ($p == 'qrdown') {
        $id = intval($_GPC['id']);
        if ($id) {
            $item = pdo_get('xcommunity_entery_log', array('id' => $id));
            if (empty($item)) {
                message('信息不存在或已删除', referer(), 'error');
            }
        }
        $dir = MODULE_ROOT . '/data/qrcode/entery/' . $item['uniacid'] . '/' . $item['region'] . '/' . $item['category'] . '/' . $item['createtime'] . '/';

//获取列表
        $data = list_dir($dir);
        $path = "./" . $item['createtime'] . ".zip"; //最终生成的文件名（含路径）
        download($data, $path);
    }
}
/**
 * 收费管理-微信推送
 */
if ($op == 'wxsend') {
    //物业费微信通知提醒
    $pindex = max(1, intval($_GPC['page']));
    $psize = 5;
    $condition = 't1.uniacid =:uniacid and t1.status=1';
    $params[':uniacid'] = $_W['uniacid'];
    $ids = $_GPC['ids'];
    if ($ids) {
        $idss = implode(',', $ids);
        $condition .= " and t1.id in({$idss})";
        $psize = 20;
    }
    if (intval($_GPC['regionid'])) {
        $condition .= " and t2.regionid =:regionid";
        $params[':regionid'] = intval($_GPC['regionid']);
    }
    $sql = "SELECT t1.*,t2.build,t2.unit,t2.room,t2.address,t3.title,t4.title as regiontitle,t7.openid,t8.realname,t6.uid FROM" . tablename('xcommunity_fee') . "t1 left join" . tablename('xcommunity_member_room') . "t2 on t1.roomid = t2.id left join" . tablename('xcommunity_fee_category') . "t3 on t3.id = t1.categoryid left join" . tablename('xcommunity_region') . "t4 on t4.id = t2.regionid left join" . tablename('xcommunity_member_bind') . "t5 on t5.addressid = t2.id left join" . tablename('xcommunity_member') . "t6 on t6.id= t5.memberid left join" . tablename('mc_mapping_fans') . "t7 on t7.uid=t6.uid left join" . tablename('mc_members') . "t8 on t8.uid=t7.uid WHERE $condition order by t1.id desc LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
    $list = pdo_fetchall($sql, $params);
    $tsql = "SELECT count(*) FROM" . tablename('xcommunity_fee') . "t1 left join" . tablename('xcommunity_member_room') . "t2 on t1.roomid = t2.id left join" . tablename('xcommunity_fee_category') . "t3 on t3.id = t1.categoryid left join" . tablename('xcommunity_region') . "t4 on t4.id = t2.regionid WHERE $condition ";
    $total = pdo_fetchcolumn($tsql, $params);
    if (set('t11')) {
        $record = intval($_GPC['record']) ? intval($_GPC['record']) : 0;
        $ok = intval($_GPC['ok']) ? intval($_GPC['ok']) : 0;
        $fail = intval($_GPC['fail']) ? intval($_GPC['fail']) : 0;
        foreach ($list as $key => $val) {
            $record++;//已发送记录
            $url = $_W['siteroot'] . "app/index.php?i={$_W['uniacid']}&c=entry&op=bill&do=cost&m=" . $this->module['name'];
            $tplid = set('t12');
            $content = array(
                'first'    => array(
                    'value' => '您好，您的物业费已出。',
                ),
                'userName' => array(
                    'value' => $val['realname'],
                ),
                'address'  => array(
                    'value' => $val['regiontitle'] . $val['address'],
                ),
                'pay'      => array(
                    'value' => $val['price'] . '元',
                ),
                'remark'   => array(
                    'value' => '请尽快缴纳，如有疑问，请咨询物业.',
                ),
            );
            if (!empty($val['openid']) && $val['price'] > 0) {
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
                if ($status) {
                    $d['status'] = 1;
                    $ok++;//成功发送
                    pdo_insert('xcommunity_send_log', $d);
                    util::permlog('', '批量微信发送费用' . ',费用列表ID:' . $val['id']);
                }
                else {
                    $d['status'] = 2;
                    $fail++;//失败发送
                    pdo_insert('xcommunity_send_log', $d);

                }
            }

        }
    }
    if ($ok == $total || empty($list)) {
        echo json_encode(array('status' => 'end'));
        exit();
    }
    else {
        echo json_encode(array('total' => $total, 'fail' => $fail, 'ok' => $ok, 'record' => $record));
        exit();
    }

}
/**
 * 收费管理-接收员
 */
if ($op == 'wechat') {
    /**
     * 接收员列表
     */
    if ($p == 'list') {
        $condition = " t1.uniacid=:uniacid ";
        $parms[':uniacid'] = $_W['uniacid'];
        if ($user) {
            if ($user['uuid']) {
                $suser = pdo_get("xcommunity_users", array('uid' => $user['uuid']), array());
                $uid = $suser['type'] == 1 || $suser['type'] == 3 ? $user['uuid'] : $_W['uid'];
                $condition .= " and t1.uid = {$uid}";
            }
            else {
                $condition .= " and t1.uid = {$_W['uid']}";
            }
        }
        $pindex = max(1, intval($_GPC['page']));
        $psize = 20;
        $sql = "SELECT t1.*,t2.title FROM" . tablename('xcommunity_fee_wechat') . "t1 left join" . tablename('xcommunity_region') . "t2 on t1.regionid=t2.id WHERE $condition order by t1.id desc LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
        $list = pdo_fetchall($sql, $parms);
        $total = pdo_fetchcolumn("SELECT COUNT(*) FROM" . tablename('xcommunity_fee_wechat') . "t1 left join" . tablename('xcommunity_region') . "t2 on t1.regionid=t2.id WHERE $condition", $parms);
        $pager = pagination($total, $pindex, $psize);
        include $this->template('web/core/fee/entery/wechat/list');
    }
    /**
     * 接收员的添加修改
     */
    if ($p == 'add') {
        $id = intval($_GPC['id']);
        if ($id) {
            $item = pdo_get('xcommunity_fee_wechat', array('id' => $id), array());
            $category = pdo_getall('xcommunity_fee_wechat_category', array('fid' => $id), array('categoryid'));
            $categoryids = array();
            foreach ($category as $k => $v) {
                $categoryids[] = $v['categoryid'];
            }
            $categories = pdo_getall('xcommunity_fee_category', array('regionid' => $item['regionid'], 'way' => 4), array());
        }
        if ($_W['isajax']) {
            $data = array(
                'uniacid'  => $_W['uniacid'],
                'realname' => trim($_GPC['realname']),
                'mobile'   => trim($_GPC['mobile']),
                'openid'   => trim($_GPC['openid']),
                'regionid' => intval($_GPC['regionid']),
            );
            if ($id) {
                pdo_update('xcommunity_fee_wechat', $data, array('id' => $id));
                pdo_delete('xcommunity_fee_wechat_category', array('fid' => $id));
            }
            else {
                $data['uid'] = $_W['uid'];
                pdo_insert('xcommunity_fee_wechat', $data);
                $id = pdo_insertid();
            }
            $categories = $_GPC['categoryid'];
            foreach ($categories as $k => $v) {
                $da = array(
                    'fid'        => $id,
                    'categoryid' => $v,
                );
                pdo_insert('xcommunity_fee_wechat_category', $da);
            }
            echo json_encode(array('status' => 1));
            exit();
        }
        include $this->template('web/core/fee/entery/wechat/add');
    }
    /**
     * 接收员的删除
     */
    if ($p == 'del') {
        $id = intval($_GPC['id']);
        $notice = pdo_get('xcommunity_fee_wechat', array('id' => $id), array());
        if ($notice) {
            if (pdo_delete('xcommunity_fee_wechat', array('id' => $id))) {
                pdo_delete('xcommunity_fee_wechat_category', array('fid' => $id));
                util::permlog('抄表处理员-删除', '信息标题:' . $notice['realname']);
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
 * 收费管理-数据统计
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
        $condition['id'] = explode(',', $user['regionid']);
    }
    else {
        if ($_GPC['regionid']) {
            $condition['id'] = $_GPC['regionid'];
        }
    }
    $condi['uniacid'] = $_W['uniacid'];
    $condi['status'] = 2;//已支付
    $starttime = strtotime($_GPC['birth']['start']);
    $endtime = strtotime($_GPC['birth']['end']);
    $stime = strtotime(date('Y-m-d', TIMESTAMP));
    $etime = strtotime(date('Y-m-d', TIMESTAMP)) + 86400 - 1;
    if (!empty($starttime)) {
        $endtime = $endtime + 86400 - 1;
    }
    if ($starttime && $endtime) {
        $condi['paytime >='] = $starttime;
        $condi['paytime <='] = $endtime;
    }
    else {
        $condi['paytime >='] = $stime;
        $condi['paytime <='] = $etime;
    }
    $orders = pdo_getall('xcommunity_fee', $condi, array());


    if ($_GPC['export'] == 1) {
        $regions = pdo_getall('xcommunity_region', $condition, array());
        $list1 = array();
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
            "title"   => "账单数据统计-" . date('Y-m-d-H-i', time()),
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
    $pager = pagination($total, $pindex, $psize);
    include $this->template('web/core/fee/data/list');
}
/**
 * 收费管理--报表统计
 */
if ($op == 'reckon') {
    $categorys = pdo_getall('xcommunity_fee_category', array('uniacid' => $_W['uniacid']), array('id', 'title'), 'id');
    $pindex = max(1, intval($_GPC['page']));
    $psize = $_GPC['export'] == 1 || $_GPC['export'] == 2 ? 100000 : 20;
    $condition = array();
    $condition['uniacid'] = $_W['uniacid'];
    if ($_GPC['regionid']) {
        $condition['regionid'] = intval($_GPC['regionid']);
    }
    if ($_GPC['categoryid']) {
        $condition['categoryid'] = intval($_GPC['categoryid']);
    }
    if ($_GPC['status']) {
        $condition['status'] = intval($_GPC['status']);
    }
    $starttime = strtotime($_GPC['birth']['start']);
    $endtime = strtotime($_GPC['birth']['end']);
    if (!empty($starttime)) {
        $endtime = $endtime + 86400 - 1;
    }
    if ($starttime && $endtime) {
        $condition['createtime >='] = $starttime;
        $condition['createtime <='] = $endtime;
    }
    $fees = pdo_getslice('xcommunity_fee', $condition, array($pindex, $psize), $total, '', '', array('displayorder desc', 'id desc'));
    // 房屋
    $fees_roomids = _array_column($fees, 'roomid');
    $rooms = pdo_getall('xcommunity_member_room', array('id' => $fees_roomids), array('id', 'area', 'build', 'unit', 'room', 'address', 'square'), 'id');
    // 收费项目
    $fees_cids = _array_column($fees, 'categoryid');
    $feeCategorys = pdo_getall('xcommunity_fee_category', array('id' => $fees_cids), array('id', 'title', 'unit', 'price'), 'id');
    // 小区
    $fees_regionids = _array_column($fees, 'regionid');
    $feeRegions = pdo_getall('xcommunity_region', array('id' => $fees_regionids), array('id', 'title'), 'id');
    // 账单的订单
    $fees_ids = _array_column($fees, 'id');
    $feeOrders = pdo_getall('xcommunity_fee_order', array('feeid' => $fees_ids), array('id', 'feeid', 'remark'), 'feeid');
    $list = array();
    $paytype = array('', '余额', '微信支付', '货到付款', '支付宝', '现金', '银联刷卡');
    $total1 = 0;
    $total2 = 0;
    $total3 = 0;
    $areaTotal = 0;
    foreach ($fees as $k => $v) {
        $jftime = '';
        $amount = 0;
        if ($v['type'] == 2) {
            //抄表
            $jftime = $v['starttime'] && $v['endtime'] ? date('Y-m-d', $v['starttime']) . '~' . date('Y-m-d', $v['endtime']) : date('Y-m-d', $v['createtime']);
            $amount = $v['new_num'] - $v['old_num'];
        }
        else {
            $jftime = date('Y-m-d', $v['starttime']) . '~' . date('Y-m-d', $v['endtime']);
            $amount = $rooms[$v['roomid']]['square'];
        }
        $areaTotal += $amount;
        $list[] = array(
            'id'          => $v['id'],
            'address'     => $rooms[$v['roomid']]['address'],
            'jftime'      => $jftime,
            'amount'      => $amount ? $amount : 0,
            'title'       => $feeCategorys[$v['categoryid']]['title'],
            'unitPrice'   => $feeCategorys[$v['categoryid']]['price'] . $feeCategorys[$v['categoryid']]['unit'],
            'regionTitle' => $feeRegions[$v['regionid']]['title'],
            'price'       => $v['price'],
            'payPrice'    => $v['pay_price'],
            'paytype'     => $paytype[$v['paytype']],
            'status'      => $v['status'] == 2 ? '已支付' : '未支付',
            'remark'      => $feeOrders[$v['id']]['remark']
        );
    }
    $feeList = pdo_getall('xcommunity_fee', $condition, array('status', 'price'));
    foreach ($feeList as $k => $v) {
        if ($v['status'] == 2) {
            $total2 += sprintf("%.2f", $v['price']);
        }
        else {
            $total3 += sprintf("%.2f", $v['price']);
        }
        $total1 += sprintf("%.2f", $v['price']);
    }
    $total1 = sprintf("%.2f", $total1);
    $total2 = sprintf("%.2f", $total2);
    $total3 = sprintf("%.2f", $total3);
    if ($_GPC['export'] == 1) {
        model_execl::export($list, array(
            "title"   => "账单报表统计-" . date('Y-m-d-H-i', time()),
            "columns" => array(
                array(
                    'title' => '小区名称',
                    'field' => 'regionTitle',
                    'width' => 12
                ),
                array(
                    'title' => '收费项目',
                    'field' => 'title',
                    'width' => 18
                ),
                array(
                    'title' => '地址',
                    'field' => 'address',
                    'width' => 18
                ),
                array(
                    'title' => '期间',
                    'field' => 'jftime',
                    'width' => 24
                ),
                array(
                    'title' => '房屋面积/用量',
                    'field' => 'amount',
                    'width' => 12
                ),
                array(
                    'title' => '单价',
                    'field' => 'unitPrice',
                    'width' => 12
                ),
                array(
                    'title' => '应缴金额',
                    'field' => 'price',
                    'width' => 12
                ),
                array(
                    'title' => '实缴',
                    'field' => 'payPrice',
                    'width' => 12
                ),
                array(
                    'title' => '支付状态',
                    'field' => 'status',
                    'width' => 12
                ),
                array(
                    'title' => '备注',
                    'field' => 'remark',
                    'width' => 20
                ),
            )
        ));
    }
    if ($_GPC['export'] == 2) {
        if (empty($_GPC['categoryid']) || empty($_GPC['dataStatus'])) {
            itoast('请先勾选收费项目和类型再统计导出', '', 'error');
        }
        if ($_GPC['dataStatus'] == 1) {
            $title = '应收金额';
            $price = $total1;
        }
        if ($_GPC['dataStatus'] == 2) {
            $title = '实收金额';
            $price = $total2;
        }
        if ($_GPC['dataStatus'] == 3) {
            $title = '未收金额';
            $price = $total3;
        }
        $data = array(
            array(
                'title'     => $title,
                'category'  => $feeCategorys[$_GPC['categoryid']]['title'],
                'time'      => $_GPC['birth']['start'] . '~' . $_GPC['birth']['end'],
                'amount'    => sprintf("%.2f", $areaTotal),
                'unitPrice' => $feeCategorys[$_GPC['categoryid']]['price'] . $feeCategorys[$_GPC['categoryid']]['unit'],
                'price'     => sprintf("%.2f", $price)
            )
        );
        model_execl::export($data, array(
            "title"   => "账单报表统计-" . date('Y-m-d-H-i', time()),
            "columns" => array(
                array(
                    'title' => '类型',
                    'field' => 'title',
                    'width' => 12
                ),
                array(
                    'title' => '收费项目',
                    'field' => 'category',
                    'width' => 18
                ),
                array(
                    'title' => '期间',
                    'field' => 'time',
                    'width' => 24
                ),
                array(
                    'title' => '房屋面积/用量',
                    'field' => 'amount',
                    'width' => 18
                ),
                array(
                    'title' => '单价',
                    'field' => 'unitPrice',
                    'width' => 12
                ),
                array(
                    'title' => '金额',
                    'field' => 'price',
                    'width' => 12
                ),
            )
        ));
    }
    $pager = pagination($total, $pindex, $psize);
    include $this->template('web/core/fee/fee_reckon');
}
/**
 * 收银台--批量短信推送
 */
if ($op == 'smssend') {
    $pindex = max(1, intval($_GPC['page']));
    $psize = 5;
    $condition = array();
    $condition['uniacid'] = $_W['uniacid'];
    $condition['status'] = 1;
    $ids = $_GPC['ids'];
    if ($ids) {
        $condition['id'] = $ids;
        $psize = 20;
    }
    $fees = pdo_getslice('xcommunity_fee', $condition, array($pindex, $psize), $total, '', '', array('displayorder desc', 'id desc'));
    $total = pdo_getcolumn('xcommunity_fee', $condition, 'count(id)');
    // 收费项目
    $fees_cids = _array_column($fees, 'categoryid');
    $feeCategorys = pdo_getall('xcommunity_fee_category', array('id' => $fees_cids), array('title', 'id'), 'id');
    // 房屋
    $fees_roomids = _array_column($fees, 'roomid');
    $rooms = pdo_getall('xcommunity_member_room', array('id' => $fees_roomids), array('address', 'build', 'unit', 'room', 'id'), 'id');
    // 小区
    $fees_regionids = _array_column($fees, 'regionid');
    $feeRegions = pdo_getall('xcommunity_region', array('id' => $fees_regionids), array('title', 'id', 'linkway'), 'id');
    // 绑定的信息
    $binds = pdo_getall('xcommunity_member_bind', array('addressid' => $fees_roomids), array('memberid', 'addressid'), 'addressid');
    // 小区会员信息
    $memberids = _array_column($binds, 'memberid');
    $feemembers = pdo_getall('xcommunity_member', array('id' => $memberids), array('uid', 'id'), 'id');
    // 会员信息
    $uids = _array_column($feemembers, 'uid');
    $members = pdo_getall('mc_members', array('uid' => $uids), array('uid', 'realname', 'mobile'), 'uid');
    $record = intval($_GPC['record']) ? intval($_GPC['record']) : 0;
    $ok = intval($_GPC['ok']) ? intval($_GPC['ok']) : 0;
    $fail = intval($_GPC['fail']) ? intval($_GPC['fail']) : 0;
    foreach ($fees as $k => $v) {
        $memberid = $binds[$v['roomid']]['memberid'];
        $uid = $feemembers[$memberid]['uid'];
        $mobile = $members[$uid]['mobile'];
        if ($mobile) {
            $record++;//已发送记录
            if ($v['type'] == 2) {
                //抄表
                $costtime = $v['endtime'] ? date('Y-m-d', $v['endtime']) : date('Y-m-d', $v['createtime']);
            }
            else {
                $costtime = date('Y-m-d', $v['endtime']);
            }
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
                $type = set('x21', $v['regionid']);
                if ($type == 1) {
                    $type = 'wwt';
                }
                elseif ($type == 2) {
                    $type = 'juhe';
                    $tpl_id = set('x36', $v['regionid']);
                }
                else {
                    $type = 'aliyun_new';
                    $tpl_id = set('x71', $v['regionid']);
                }
                $api = 2;
                $d['regionid'] = $v['regionid'];
            }
            if ($type == 'wwt') {
                $smsg = "您好,您本月" . $feeCategorys[$v['categoryid']]['title'] . "已出。" . $feeCategorys[$v['categoryid']]['title'] . "金额" . $v['price'] . "。请尽快缴纳，如有疑问，请咨询。" . $feeRegions[$v['regionid']]['linkway'];
            }
            elseif ($type == 'juhe') {
                $phone = $feeRegions[$v['regionid']]['linkway'];
                $price = $v['price'];
                $smsg = urlencode("#price#=$price&#costtime#=$costtime&#mobile#=$phone");
            }
            else {
                $smsg = json_encode(array('price' => $v['price'], 'costtime' => $costtime, 'tel' => $feeRegions[$v['regionid']]['linkway']));
            }
            $resp = sms::send($mobile, $smsg, $type, $v['regionid'], $api, $tpl_id);
            $d = array(
                'uniacid'    => $_W['uniacid'],
                'sendid'     => $v['id'],
                'uid'        => $uid,
                'type'       => 1,
                'cid'        => 8,
                'createtime' => TIMESTAMP
            );
            if ($resp['status'] == 1) {
                $d['status'] = 1;
                $ok++;//成功发送
                pdo_insert('xcommunity_send_log', $d);
                util::permlog('', '批量短信发送账单' . ',账单列表ID:' . $v['id']);
            }
            else {
                $d['status'] = 2;
                $fail++;//失败发送
                pdo_insert('xcommunity_send_log', $d);
            }
        }
    }
    if ($ok == $total || empty($fees)) {
        echo json_encode(array('status' => 'end'));
        exit();
    }
    else {
        echo json_encode(array('total' => $total, 'fail' => $fail, 'ok' => $ok, 'record' => $record));
        exit();
    }
}