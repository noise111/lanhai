<?php
/**
 * Created by njlanniu.com.
 * User: 蓝牛科技
 * Time: 2017/11/7 上午10:55
 */
global $_GPC, $_W;
$ops = array('list', 'add', 'unit_list', 'unit_add', 'delete', 'del', 'area', 'import', 'importArea');
$op = !empty($_GPC['op']) ? $_GPC['op'] : 'list';
if (!in_array($op, $ops)) {
    message('该方法不存在(op:' . $op . ')');
}
// 操作员
$user = util::xquser($_W['uid']);
if ($user && $user['menu_ops']) {
    $menu_opss = explode(',', $user['menu_ops']);
}
/**
 * 楼宇列表
 */
if ($op == 'list') {
    if (checksubmit('delete')) {
        $ids = $_GPC['ids'];
        if (!empty($ids)) {
            foreach ($ids as $key => $id) {
                pdo_delete('xcommunity_build', array('id' => $id));
                pdo_delete('xcommunity_unit', array('buildid' => $id));
            }
            util::permlog('', '批量删除小区楼宇、单元');
            itoast('删除成功', referer(), 'success');
        }
    }
    $regions = model_region::region_fetall();
    $pindex = max(1, intval($_GPC['page']));
    $psize = 20;
    $condition = 't1.uniacid =:uniacid';
    $params[':uniacid'] = $_W['uniacid'];
    if (!empty($_GPC['keyword'])) {
        $condition .= " AND t1.buildtitle LIKE '%{$_GPC['keyword']}%'";
    }
    if (intval($_GPC['regionid'])) {
        $condition .= " and t1.regionid =:regionid";
        $params[':regionid'] = intval($_GPC['regionid']);
    }
    if ($user && $user[type] == 3) {
        //普通管理员
        $condition .= " and t1.regionid in({$user['regionid']})";
    }
    $sql = "SELECT t1.*,t2.title,t3.title as area FROM" . tablename('xcommunity_build') . "t1 left join" . tablename('xcommunity_region') . "t2 on t1.regionid = t2.id left join" . tablename('xcommunity_area') . "t3 on t3.id = t1.areaid WHERE $condition order by t1.id desc LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
    $list = pdo_fetchall($sql, $params);
    sortArrByField($list, 'buildtitle');
    $total = pdo_fetchcolumn("SELECT COUNT(*) FROM" . tablename('xcommunity_build') . "t1 WHERE $condition", $params);
    $pager = pagination($total, $pindex, $psize);
    include $this->template('web/core/build/build_list');
}
/**
 * 楼宇的添加
 */
if ($op == 'add') {
    $regions = model_region::region_fetall();
    $id = intval($_GPC['id']);
    if ($id) {
        $item = pdo_get('xcommunity_build', array('id' => $id), array());
        if ($item['regionid']) {
            $areas = pdo_getall('xcommunity_area', array('regionid' => $item['regionid']), array());
        }
    }
    if ($_W['isajax']) {
        $regionid = intval($_GPC['regionid']);
        $data = array(
            'uniacid'    => $_W['uniacid'],
            'regionid'   => $regionid,
            'buildtitle' => trim($_GPC['buildtitle']),
            'unit_num'   => intval($_GPC['unit_num']),
            'floor_num'  => intval($_GPC['floor_num']),
            'room_num'   => intval($_GPC['room_num']),
            'build_type' => trim($_GPC['build_type']),
            'areaid'     => trim($_GPC['area'])
        );
        if (empty($id)) {
            $data['uid'] = $_W['uid'];
            $item = pdo_get('xcommunity_build', array('buildtitle' => $data['buildtitle'], 'regionid' => $data['regionid'], 'areaid' => $data['areaid']), array());
            if (empty($item)) {
                pdo_insert('xcommunity_build', $data);
            } else {
                echo json_encode(array('content' => '楼宇不可重复添加'));
                exit();
            }

            $buildid = pdo_insertid();
            for ($i = 1; $i <= $data['unit_num']; $i++) {
                $dat = array(
                    'uniacid'  => $_W['uniacid'],
                    'buildid'  => $buildid,
                    'unit'     => $i,
                    'uid'      => $_W['uid'],
                    'regionid' => $regionid
                );
                pdo_insert('xcommunity_unit', $dat);
            }
        } else {
            $data['yybstatus'] = 0;
            $data['action'] = 1;
            pdo_update('xcommunity_build', $data, array('id' => $id));
        }
        echo json_encode(array('status' => 1));
        exit();
    }

    include $this->template('web/core/build/build_add');
}
/**
 * 楼宇删除
 */
if ($op == 'delete') {
    $id = intval($_GPC['id']);
    if ($id) {
        $item = pdo_get('xcommunity_build', array('id' => $id), array('id'));
        if ($item) {
            $rooms = pdo_getall('xcommunity_member_room', array('uniacid' => $_W['uniacid'], 'buildid' => $id), array('id'));
            $rooms_ids = _array_column($rooms, 'id');
            if ($rooms_ids) {
                pdo_delete('xcommunity_member_log', array('addressid' => $rooms_ids));
                pdo_delete('xcommunity_member_bind', array('addressid' => $rooms_ids));
                pdo_delete('xcommunity_member_family', array('addressid' => $rooms_ids));
            }
            pdo_delete('xcommunity_member_room', array('buildid' => $id));
            pdo_delete('xcommunity_unit', array('buildid' => $id));
            if (pdo_delete('xcommunity_build', array('id' => $id))) {
                itoast('删除成功', referer(), 'success');
                exit();
            }
        }
    }
}
/**
 * 单元列表
 */
if ($op == 'unit_list') {
    if (checksubmit('delete')) {
        $ids = $_GPC['ids'];
        if (!empty($ids)) {
            foreach ($ids as $key => $id) {
                pdo_delete('xcommunity_unit', array('id' => $id));
            }
            util::permlog('', '批量删除小区单元');
            itoast('删除成功', referer(), 'success');
        }
    }
    $regions = model_region::region_fetall();
    $pindex = max(1, intval($_GPC['page']));
    $psize = 20;
    $condition = 't1.uniacid =:uniacid and t2.id=:buildid';
    $params[':uniacid'] = $_W['uniacid'];
    $params[':buildid'] = intval($_GPC['buildid']);
    if (!empty($_GPC['keyword'])) {
        $condition .= " AND t1.unit LIKE '%{$_GPC['keyword']}%'";
    }
    if (intval($_GPC['regionid'])) {
        $condition .= " and t2.regionid =:regionid";
        $params[':regionid'] = intval($_GPC['regionid']);
    }
    if ($user && $user[type] != 1) {
        //普通管理员
        $condition .= " AND t1.uid=:uid";
        $params[':uid'] = $_W['uid'];
    }
    $sql = "SELECT t1.id,t1.unit,t2.buildtitle,t3.title FROM" . tablename('xcommunity_unit') . "t1 left join" . tablename('xcommunity_build') . "t2 on t1.buildid = t2.id left join" . tablename('xcommunity_region') . "t3 on t2.regionid=t3.id WHERE $condition order by t1.id desc LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
    $list = pdo_fetchall($sql, $params);
    $total = pdo_fetchcolumn("SELECT count(*) FROM" . tablename('xcommunity_unit') . "t1 left join" . tablename('xcommunity_build') . "t2 on t1.buildid = t2.id left join" . tablename('xcommunity_region') . "t3 on t2.regionid=t3.id WHERE $condition order by t1.id desc", $params);
    $pager = pagination($total, $pindex, $psize);
    include $this->template('web/core/build/unit_list');
}
/**
 * 单元添加
 */
if ($op == 'unit_add') {
    $id = intval($_GPC['id']);
    $buildid = intval($_GPC['buildid']);
    if ($id) {
        $item = pdo_get('xcommunity_unit', array('id' => $id), array());
    }
    $regionid = pdo_getcolumn('xcommunity_build', array('id' => $buildid), 'regionid');
    if ($_W['isajax']) {
        $data = array(
            'uniacid'  => $_W['uniacid'],
            'buildid'  => intval($_GPC['buildid']),
            'unit'     => $_GPC['unit'],
            'uid'      => $_W['uid'],
            'regionid' => $regionid
        );
        if (empty($id)) {
            $item = pdo_get('xcommunity_unit', array('buildid' => $data['buildid'], 'unit' => $data['unit'], 'regionid' => $regionid), array());
            if (empty($item)) {
                pdo_insert('xcommunity_unit', $data);
            } else {
                echo json_encode(array('content' => '单元不可重复添加'));
                exit();
            }
        } else {
            $data['yybstatus'] = 0;
            $data['action'] = 1;
            pdo_update('xcommunity_unit', $data, array('id' => $id));
        }
        echo json_encode(array('status' => 1));
        exit();
    }
    include $this->template('web/core/build/unit_add');
}
/**
 * 获取区域列表
 */
if ($op == 'area') {
    if ($_W['isajax']) {
        $regionid = intval($_GPC['regionid']);
        $areas = pdo_getall('xcommunity_area', array('regionid' => $regionid), array('title', 'id'));
        echo json_encode($areas);
    }
}
/**
 * 单元的删除
 */
if ($op == 'del') {
    $id = intval($_GPC['id']);
    if ($id) {
        $item = pdo_get('xcommunity_unit', array('id' => $id), array('id'));
        if ($item) {
            if (pdo_delete('xcommunity_unit', array('id' => $id))) {
                itoast('删除成功', referer(), 'success');
                exit();
            }
        }
    }
}
/**
 * 楼宇的导入
 */
if ($op == 'import') {
    if ($_W['isajax']) {
        $rows = model_execl::read('build');
        if (trim($rows[0][0]) != '楼宇名称') {
            echo json_encode(array('content' => '文件内容不符，请重新上传'));
            exit();
        }
        $regionid = intval($_GPC['regionid']);
        $areaid = intval($_GPC['areaid']) ? intval($_GPC['areaid']) : 0;
        $sql = "insert into " . tablename('xcommunity_build') . " (uniacid, regionid, areaid, buildtitle, unit_num ,floor_num, build_type, room_num)values";
        $condition = array();
        $condition['uniacid'] = $_W['uniacid'];
        if ($regionid) {
            $condition['regionid'] = $regionid;
        }
        if ($areaid) {
            $condition['areaid'] = $areaid;
        }
        $builds = pdo_getall('xcommunity_build', $condition, array('id', 'buildtitle'), 'buildtitle');
        foreach ($rows as $rownum => $col) {
            if ($rownum > 0) {
                if ($col[0]) {
                    if (empty($bulids[$col[0]])) {
                        $sql .= "(" . $_W['uniacid'] . "," . $regionid . "," . $areaid . ",'" . $col[0] . "'," . $col[1] . "," . $col[2] . ",'" . $col[3] . "'," . $col[4] . "),";
                    }
                }
            }
        }
        $sql = xtrim($sql);
        pdo_query($sql);
        util::permlog('', '导入楼宇信息');
        echo json_encode(array('result' => 1, 'content' => '导入完成！'));
        exit();
    }
    $regions = model_region::region_fetall();
    include $this->template('web/core/build/build_import');
}
/**
 * 楼宇导入关联的区域
 */
if ($op == 'importArea') {
    if ($_W['isajax']) {
        $regionid = intval($_GPC['regionid']);
        $arr = util::xqzd($regionid);
        $p36 = set('p36');
        $x17 = set('x17', $regionid);
        if ($p36 || $x17) {
            $areas = pdo_getall('xcommunity_area', array('regionid' => $regionid), array('title', 'id'));
            echo json_encode(array('list' => $areas, 'err_code' => 0));
            exit();
        } else {
            echo json_encode(array('err_code' => 2));
            exit();
        }
    }
}