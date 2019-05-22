<?php
/**
 * Created by njlanniu.com.
 * User: 蓝牛科技
 * Date: 2018/8/2 0002
 * Time: 上午 9:51
 */
global $_GPC, $_W;
$op = !empty($_GPC['op']) ? $_GPC['op'] : 'list';
$p = !empty($_GPC['p']) ? $_GPC['p'] : 'list';
$regions = model_region::region_fetall();
$user = util::xquser($_W['uid']);
$id = intval($_GPC['id']);
if ($op == 'list') {
    if ($p == 'list') {
        $pindex = max(1, intval($_GPC['page']));
        $psize = 20;
        $condition = 't1.uniacid =:uniacid and t1.type=3 and t1.status=1';
        $params[':uniacid'] = $_W['uniacid'];
        if (!empty($_GPC['keyword'])) {
            $condition .= " AND t2.room LIKE '%{$_GPC['keyword']}%'";
        }
        if (intval($_GPC['regionid'])) {
            $condition .= " and t2.regionid =:regionid";
            $params[':regionid'] = intval($_GPC['regionid']);
        }
        if ($user && $user[type] == 3) {
            //小区管理员
            $condition .= " and t2.regionid in({$user['regionid']})";
        }
        if ($_GPC['export'] == 1) {
            $sql = "SELECT t1.*,t2.build,t2.unit,t2.room,t3.title,t4.title as regiontitle FROM" . tablename('xcommunity_fee') . "t1 left join" . tablename('xcommunity_member_room') . "t2 on t1.roomid = t2.id left join" . tablename('xcommunity_fee_category') . "t3 on t3.id = t1.categoryid left join" . tablename('xcommunity_region') . "t4 on t4.id = t2.regionid WHERE $condition order by t1.id desc";
            $list1 = pdo_fetchall($sql, $params);
            foreach ($list1 as $k => $v) {
                $list1[$k]['createtime'] = date('Y-m-d H:i', $v['createtime']);
                $list1[$k]['setime'] = date('Y-m-d', $v['starttime']) . '~' . date('Y-m-d', $v['endtime']);
                if ($v['status'] == 2) {
                    $list1[$k]['status'] = '已支付';
                }
                else {
                    $list1[$k]['status'] = '未支付';
                }
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
        $sql = "SELECT t1.*,t2.build,t2.unit,t2.room,t3.title,t4.title as regiontitle FROM" . tablename('xcommunity_fee') . "t1 left join" . tablename('xcommunity_member_room') . "t2 on t1.roomid = t2.id left join" . tablename('xcommunity_fee_category') . "t3 on t3.id = t1.categoryid left join" . tablename('xcommunity_region') . "t4 on t4.id = t2.regionid WHERE $condition order by t1.id desc LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
        $list = pdo_fetchall($sql, $params);
        $tsql = "SELECT count(*) FROM" . tablename('xcommunity_fee') . "t1 left join" . tablename('xcommunity_member_room') . "t2 on t1.roomid = t2.id left join" . tablename('xcommunity_fee_category') . "t3 on t3.id = t1.categoryid left join" . tablename('xcommunity_region') . "t4 on t4.id = t2.regionid WHERE $condition ";
        $total = pdo_fetchcolumn($tsql, $params);
        $pager = pagination($total, $pindex, $psize);

        include $this->template('web/core/homefee/list/list');
    }
    elseif ($p == 'add') {
        if ($_W['isajax']) {
            $regionid = intval($_GPC['regionid']);
            $groups = pdo_getall('xcommunity_fee_category', array('regionid' => $regionid,'type'=>2), array());
            echo json_encode($groups);
            exit();
        }
        if (checksubmit('submit')) {
            $caetgoryid = intval($_GPC['caetgoryid']);
            $sql = "select t1.id,t1.square from" . tablename('xcommunity_member_room') . "t1 left join" . tablename('xcommunity_fee_category_build') . "t2 on t1.buildid = t2.buildid where t2.caetgoryid=:caetgoryid and t1.uniacid =:uniacid";
            $rooms = pdo_fetchall($sql, array(':caetgoryid' => $caetgoryid, ':uniacid' => $_W['uniacid']));
            foreach ($rooms as $k => $room) {

                $csql = "select * from" . tablename('xcommunity_fee_category') . "where id=:id and square=:square";

                $category = pdo_fetch($csql, array(':id' => $caetgoryid, ':square' => $room['square']));
                if($category){
                    if ($category['way'] == 2 || $category['way'] == 3) {
                        $price = $category['price'];
                    }
                    elseif ($category['way'] == 1) {
                        $price = $category['price'] * $room['square'] * $category['cycle'];
                    }
                    //计算滞纳金
                    $endday = strtotime($_GPC['enddate']) + $category['start_day'] * 24 * 60 * 60;
                    $rate = '';
                    if (TIMESTAMP > $endday) {
                        $day = round((TIMESTAMP - $endday) / (24 * 60 * 60));//滞纳金的天数
                        $rate = ($category['rate'] / 1000) * $price * $day;
                    }
                    $cycle = "-" . $category['cycle'] . "months";
                    $starttime = date("Y-m-d H:i:s", strtotime($cycle, strtotime($_GPC['enddate'])));
                    $data = array(
                        'uniacid'    => $_W['uniacid'],
                        'categoryid' => $category['id'],
                        'roomid'     => $room['id'],
                        'starttime'  => strtotime($starttime),
                        'endtime'    => strtotime($_GPC['enddate']),
                        'price'      => $category['discount'] ? $price * ($category['discount'] * 10 / 100) : $price,
                        'rate'       => $rate,
                        'status'     => 1,
                        'type'       => 3
                    );
                    pdo_insert('xcommunity_fee', $data);
                }
            }
            itoast('成功生成', $this->createWebUrl('homefee', array('op' => 'list')), 'success');
        }
        include $this->template('web/core/homefee/list/add');
    }
    elseif ($p == 'del') {
        $id = intval($_GPC['id']);
        if (empty($id)) {
            itoast('缺少参数');
            exit();
        }
        $item = pdo_get('xcommunity_fee', array('id' => $id));
        if ($item) {
            if (pdo_delete('xcommunity_fee', array('id' => $id))) {
                itoast('删除成功', $this->createWebUrl('homefee', array('op' => 'list')), 'success');
            }
        }
    }
    elseif ($p == 'detail') {
        $id = intval($_GPC['id']);
        if (empty($id)) {
            itoast('缺少参数');
        }
        if ($id) {
            $sql = "SELECT t1.*,t2.build,t2.unit,t2.room,t3.title,t4.title as regiontitle,t1.paytype,t3.way FROM" . tablename('xcommunity_homefee') . "t1 left join" . tablename('xcommunity_member_room') . "t2 on t1.roomid = t2.id left join" . tablename('xcommunity_homefee_group') . "t3 on t3.id = t1.groupid left join" . tablename('xcommunity_region') . "t4 on t4.id = t2.regionid WHERE t1.id=:id";
            $item = pdo_fetch($sql, array(':id' => $id));
        }
        include $this->template('web/core/homefee/list/detail');
    }
    elseif ($p == 'edit') {
        $id = intval($_GPC['id']);
        if (empty($id)) {
            itoast('缺少参数');
        }
        if ($id) {
            $sql = "SELECT t1.*,t2.build,t2.unit,t2.room,t3.title,t4.title as regiontitle,t3.way,t1.paytype FROM" . tablename('xcommunity_homefee') . "t1 left join" . tablename('xcommunity_member_room') . "t2 on t1.roomid = t2.id left join" . tablename('xcommunity_homefee_group') . "t3 on t3.id = t1.groupid left join" . tablename('xcommunity_region') . "t4 on t4.id = t2.regionid WHERE t1.id=:id";
            $item = pdo_fetch($sql, array(':id' => $id));
        }
        if (checksubmit('submit')) {
            $data = array(
                'price' => $_GPC['price'],
            );
            pdo_update('xcommunity_homefee', $data, array('id' => intval($_GPC['feeid'])));
            itoast('更新成功', $this->createWebUrl('homefee', array('op' => 'list')), 'success');
        }
        include $this->template('web/core/homefee/list/edit');
    }
    elseif ($p == 'pldel') {
        //批量删除
        $pindex = max(1, intval($_GPC['page']));
        $psize = 5;
        $condition = 'uniacid =:uniacid';
        $params[':uniacid'] = $_W['uniacid'];
        $sql = "SELECT id FROM" . tablename('xcommunity_fee') . "WHERE $condition LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
        $list = pdo_fetchall($sql, $params);
//        $ids = xtrim($ids);
        $tsql = "SELECT count(*) FROM" . tablename('xcommunity_fee') . "WHERE $condition ";
        $total = pdo_fetchcolumn($tsql, $params);
        $record = intval($_GPC['record']) ? intval($_GPC['record']) : 0;
        $ok = intval($_GPC['ok']) ? intval($_GPC['ok']) : 0;
        $fail = intval($_GPC['fail']) ? intval($_GPC['fail']) : 0;
        if ($list) {
            foreach ($list as $k => $v) {
                $ids .= $v['id'] . ',';
                $record++;
                if (pdo_delete('xcommunity_fee', array('id' => $v['id']))) {
                    $ok++;
                    util::permlog('', '批量删除房屋费用' . ',费用列表ID:' . $v['id']);
                }
                else {
                    $fail++;
                }

            }
        }
        if (empty($list)) {
            echo $sql;
            print_r($list);
            echo json_encode(array('status' => 'end'));
            exit();
        }
        else {
            echo json_encode(array('total' => $total, 'fail' => $fail, 'ok' => $ok, 'record' => $record));
            exit();
        }
    }
    elseif ($p == 'print') {
        $id = intval($_GPC['id']);
        if (empty($id)) {
            itoast('缺少参数');
        }
        if ($id) {
            $sql = "SELECT t1.*,t2.build,t2.unit,t2.room,t3.title,t4.title as regiontitle,t1.paytype,t5.title as ptitle,t4.stamp,t8.realname FROM" . tablename('xcommunity_fee') . "t1 left join" . tablename('xcommunity_member_room') . "t2 on t1.roomid = t2.id left join" . tablename('xcommunity_fee_category') . "t3 on t3.id = t1.categoryid left join" . tablename('xcommunity_region') . "t4 on t4.id = t2.regionid left join".tablename('xcommunity_property')."t5 on t4.pid=t5.id left join".tablename('xcommunity_member_bind')."t6 on t2.id=t6.addressid left join".tablename('xcommunity_member')."t7 on t7.id=t6.memberid left join".tablename('mc_members')."t8 on t8.uid=t7.uid WHERE t1.id=:id";
            $item = pdo_fetch($sql, array(':id' => $id));
            $item['no'] = sprintf("%010d",$id);
            $item['dprice'] = num_to_rmb($item['price']);
            $table = '<tr style="height: 23px"><td></td><td></td><td></td><td></td><td></td></tr>';
            $tab = '';
            for ($i=0; $i < 9; $i++){
                $tab .= $table;
            }
        }
        include $this->template('web/core/homefee/list/print');
    }
}
elseif ($op == 'group') {
    if ($p == 'list') {
        $pindex = max(1, intval($_GPC['page']));
        $psize = 20;
        $condition = 't1.uniacid =:uniacid and t1.type=2';
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
        include $this->template('web/core/homefee/group/list');
    }
    elseif ($p == 'add') {
        $id = intval($_GPC['id']);
        if ($id) {
            $item = pdo_get('xcommunity_fee_category', array('id' => $id), array());
            $build = pdo_getall('xcommunity_fee_category_build', array('caetgoryid' => $id), array('buildid'));
            $buildids = array();
            foreach ($build as $key => $val) {
                $buildids[] = $val['buildid'];
            }
            $builds = pdo_getall('xcommunity_build', array('regionid' => $item['regionid']), array());
        }
        if (checksubmit('submit')) {
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
                'square'    => trim($_GPC['square']),
                'type'      => 2
            );
            if (empty($id)) {
                $data['uid'] = $_W['uid'];
                pdo_insert('xcommunity_fee_category', $data);
                $categoryid = pdo_insertid();
            }
            else {
                $categoryid = $id;
                pdo_update('xcommunity_fee_category', $data, array('id' => $id));
                pdo_delete('xcommunity_fee_category_build', array('caetgoryid' => $id));
            }
            if ($categoryid) {
                $builds = $_GPC['build'];
                foreach ($builds as $k => $v) {
                    if ($v) {
                        $dat = array(
                            'uniacid'    => $_W['uniacid'],
                            'caetgoryid' => $categoryid,
                            'buildid'    => $v,
                        );
                        pdo_insert('xcommunity_fee_category_build', $dat);
                    }

                }
            }
            itoast('提交成功', referer(), 'success');
            exit();

        }
        include $this->template('web/core/homefee/group/add');
    }
    elseif ($p == 'del') {
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
elseif ($op == 'pact') {
    if ($p == 'list') {
        $condition = '';
        if ($user && $user[type] != 1) {
            $condition .= " and uid='{$_W['uid']}'";
        }
        $pindex = max(1, intval($_GPC['page']));
        $psize = 10;
        $starttime = strtotime($_GPC['birth']['start']);
        $endtime = strtotime($_GPC['birth']['end']);
        if (!empty($starttime)) {
            $endtime = $endtime + 86400 - 1;
        }
        if ($starttime && $endtime) {
            $condition .= " and createtime between '{$starttime}' and '{$endtime}'";
        }
        $sql = "select * from " . tablename("xcommunity_homefee_pact") . "where  uniacid = {$_W['uniacid']} and type = 1 $condition order by createtime desc LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
        $list = pdo_fetchall($sql);
        $total = pdo_fetchcolumn('select count(*) from' . tablename("xcommunity_homefee_pact") . "where  uniacid = {$_W['uniacid']} and type = 1 $condition");
        $pager = pagination($total, $pindex, $psize);

        include $this->template('web/core/homefee/pact/list');
    }
    elseif ($p == 'add') {
        if (!empty($id)) {
            $item = pdo_fetch("SELECT * FROM" . tablename('xcommunity_homefee_pact') . "WHERE id=:id", array(':id' => $id));
        }
        if (checksubmit('submit')) {
            $type = intval($_GPC['type']) ? intval($_GPC['type']) : 1;
            $data = array(
                'uniacid'    => $_W['uniacid'],
                'title'      => $_GPC['title'],
                'createtime' => TIMESTAMP,
                'content'    => htmlspecialchars_decode($_GPC['content']),
                'type'       => $type,
            );
            if ($type == 1) {
                if (empty($id)) {
                    $data['uid'] = $_W['uid'];
                    pdo_insert("xcommunity_homefee_pact", $data);
                    $id = pdo_insertid();
                    util::permlog('合同-添加', '信息标题:' . $data['title']);
                }
                else {
                    pdo_update("xcommunity_homefee_pact", $data, array('id' => $id));
                    util::permlog('合同-修改', '信息标题:' . $data['title']);
                }
            }
            elseif ($type == 2) {
                $data['uid'] = $_W['uid'];
                pdo_insert("xcommunity_homefee_pact", $data);
            }
            itoast('提交成功', referer(), 'success');
        }
        include $this->template('web/core/homefee/pact/add');
    }
    elseif ($p == 'del') {
        if ($id) {
            $item = pdo_get('xcommunity_homefee_pact', array('id' => $id), array());
            if ($item) {
                if (pdo_delete("xcommunity_homefee_pact", array('id' => $id))) {
                    util::permlog('合同-删除', '信息标题:' . $item['title']);
                    itoast('删除成功', referer(), 'success');
                }
            }

        }
    }
    elseif ($p == 'plist') {
        $condition = '';
        if ($user && $user[type] != 1) {
            $condition .= " and uid='{$_W['uid']}'";
        }
        $pindex = max(1, intval($_GPC['page']));
        $psize = 10;
        $starttime = strtotime($_GPC['birth']['start']);
        $endtime = strtotime($_GPC['birth']['end']);
        if (!empty($starttime)) {
            $endtime = $endtime + 86400 - 1;
        }
        if ($starttime && $endtime) {
            $condition .= " and createtime between '{$starttime}' and '{$endtime}'";
        }
        $sql = "select * from " . tablename("xcommunity_homefee_pact") . "where  uniacid = {$_W['uniacid']} and type = 2 $condition order by createtime desc LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
        $list = pdo_fetchall($sql);
        $total = pdo_fetchcolumn('select count(*) from' . tablename("xcommunity_homefee_pact") . "where  uniacid = {$_W['uniacid']} and type = 2 $condition");
        $pager = pagination($total, $pindex, $psize);

        include $this->template('web/core/homefee/pact/plist');
    }
    elseif ($p == 'padd') {
        if (!empty($id)) {
            $item = pdo_fetch("SELECT * FROM" . tablename('xcommunity_homefee_pact') . "WHERE id=:id", array(':id' => $id));
        }
        if (checksubmit('submit')) {
            $type = intval($_GPC['type']) ? intval($_GPC['type']) : 1;
            $data = array(
                'uniacid'    => $_W['uniacid'],
                'title'      => $_GPC['title'],
                'createtime' => TIMESTAMP,
                'content'    => htmlspecialchars_decode($_GPC['content']),
                'type'       => $type,
            );
            if ($type == 2) {
                pdo_update("xcommunity_homefee_pact", $data, array('id' => $id));
                util::permlog('合同-修改', '信息标题:' . $data['title']);
            }
            itoast('提交成功', referer(), 'success');
        }
        include $this->template('web/core/homefee/pact/padd');
    }
    elseif ($p == 'save') {
        $condition = '';
        if ($user && $user[type] != 1) {
            $condition .= " and uid='{$_W['uid']}'";
        }
        $pindex = max(1, intval($_GPC['page']));
        $psize = 10;
        $sql = "select * from " . tablename("xcommunity_homefee_pact_save") . "where  uniacid = {$_W['uniacid']} $condition order by createtime desc LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
        $list = pdo_fetchall($sql);
        $total = pdo_fetchcolumn('select count(*) from' . tablename("xcommunity_homefee_pact_save") . "where  uniacid = {$_W['uniacid']} $condition");
        $pager = pagination($total, $pindex, $psize);

        include $this->template('web/core/homefee/pact/save');
    }
}
elseif ($op == 'room') {
    $regionid = intval($_GPC['regionid']);
    $buildid = intval($_GPC['buildid']);
    $condition = " regionid=:regionid ";
    $params[':regionid'] = $regionid;
    if ($buildid) {
        $condition .= " and buildid=:buildid ";
        $params[':buildid'] = $buildid;
    }
    $list = pdo_fetchall("select id,address from" . tablename('xcommunity_member_room') . "where $condition", $params);
    echo json_encode($list);

}
elseif($op =='order'){
    if($p =='list'){
        $pindex = max(1, intval($_GPC['page']));
        $psize = 20;
        $condition = 't1.uniacid =:uniacid and t1.status =2 and t1.type=3';
        $params[':uniacid'] = $_W['uniacid'];
        if (!empty($_GPC['keyword'])) {
            $condition .= " AND t2.room LIKE '%{$_GPC['keyword']}%'";
        }
        if (intval($_GPC['regionid'])) {
            $condition .= " and t2.regionid =:regionid";
            $params[':regionid'] = intval($_GPC['regionid']);
        }
        if ($_GPC['export'] == 1) {
            $sql = "SELECT t1.*,t2.build,t2.unit,t2.room,t3.title,t4.title as regiontitle FROM" . tablename('xcommunity_fee') . "t1 left join" . tablename('xcommunity_member_room') . "t2 on t1.roomid = t2.id left join" . tablename('xcommunity_fee_category') . "t3 on t3.id = t1.categoryid left join" . tablename('xcommunity_region') . "t4 on t4.id = t2.regionid WHERE $condition order by t1.id desc";
            $list1 = pdo_fetchall($sql, $params);
            foreach ($list1 as $k => $v) {
                $list1[$k]['createtime'] = date('Y-m-d H:i', $v['createtime']);
                $list1[$k]['setime'] = date('Y-m-d', $v['starttime']) . '~' . date('Y-m-d', $v['endtime']);
                if ($v['status'] == 2) {
                    $list1[$k]['status'] = '已支付';
                }
                else {
                    $list1[$k]['status'] = '未支付';
                }
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
        $sql = "SELECT t1.*,t2.build,t2.unit,t2.room,t3.title,t4.title as regiontitle FROM" . tablename('xcommunity_fee') . "t1 left join" . tablename('xcommunity_member_room') . "t2 on t1.roomid = t2.id left join" . tablename('xcommunity_fee_category') . "t3 on t3.id = t1.categoryid left join" . tablename('xcommunity_region') . "t4 on t4.id = t2.regionid WHERE $condition order by t1.id desc LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
        $list = pdo_fetchall($sql, $params);
        foreach ($list as $k => $v) {
            if ($v['type'] == 2) {
                //抄表
                $list[$k]['jftime'] = date('Y-m-d', $v['createtime']);
            }
            else {
                $list[$k]['jftime'] = date('Y-m-d', $v['starttime']) . '~' . date('Y-m-d', $v['endtime']);
            }
        }

        $tsql = "SELECT count(*) FROM" . tablename('xcommunity_fee') . "t1 left join" . tablename('xcommunity_member_room') . "t2 on t1.roomid = t2.id left join" . tablename('xcommunity_fee_category') . "t3 on t3.id = t1.categoryid left join" . tablename('xcommunity_region') . "t4 on t4.id = t2.regionid WHERE $condition ";
        $total = pdo_fetchcolumn($tsql, $params);
        $pager = pagination($total, $pindex, $psize);
        include $this->template('web/core/homefee/order/list');
    }
    elseif ($p == 'add') {
        $id = intval($_GPC['id']);
        if (empty($id)) {
            itoaset('缺少参数');
        }
        if ($id) {
            $sql = "SELECT t1.*,t2.build,t2.unit,t2.room,t3.title,t4.title as regiontitle,t3.way,t3.unit,t3.cycle,t1.paytype FROM" . tablename('xcommunity_fee') . "t1 left join" . tablename('xcommunity_member_room') . "t2 on t1.roomid = t2.id left join" . tablename('xcommunity_fee_category') . "t3 on t3.id = t1.categoryid left join" . tablename('xcommunity_region') . "t4 on t4.id = t2.regionid WHERE t1.id=:id";
            $item = pdo_fetch($sql, array(':id' => $id));
        }
        include $this->template('web/core/homefee/order/add');
    }
    elseif ($p == 'del') {
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
}