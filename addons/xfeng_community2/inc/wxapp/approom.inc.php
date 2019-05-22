<?php
/**
 * Created by njlanniu.com.
 * User: 蓝牛科技
 * Time: 2018/4/17 下午12:52
 */
global $_W, $_GPC;
$ops = array('list', 'detail', 'add', 'member', 'delete', 'display', 'show', 'showDetail', 'showAdd', 'showQr');
$op = trim($_GPC['op']);
if (!in_array($op, $ops)) {
    util::send_error(-1, '参数错误');
}
/**
 * 房屋小区列表
 */
if ($op == 'list') {
    $pindex = max(1, intval($_GPC['page']));
    $psize = max(20, intval($_GPC['psize']));
    $condition = "";
    if ($_SESSION['apptype'] == 3) {
        //小区管理员
        $condition .= " and id in({$_SESSION['appregionids']})";
    }
    $sql = "select id,title,thumb from" . tablename('xcommunity_region') . "where uniacid=:uniacid $condition LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
    $regions = pdo_fetchall($sql, array(':uniacid' => $_SESSION['appuniacid']));
    foreach ($regions as $k => $v) {
        $total = pdo_fetchcolumn("select count(*) from" . tablename('xcommunity_member_room') . "where regionid=:regionid", array(':regionid' => $v['id']));
        $regions[$k]['total'] = $total;
        $regions[$k]['thumb'] = $v['thumb'] ? tomedia($v['thumb']) : MODULE_URL . 'template/mobile/default2/static/images/icon-zanwu.png';
        $regions[$k]['url'] = $this->createMobileUrl('xqsys', array('op' => 'room', 'p' => 'detail', 'regionid' => $v['id']));
    }
    util::send_result($regions);
}
/**
 * 房屋列表
 */
if ($op == 'display') {
    $regionid = intval($_GPC['regionid']);
    if ($_SESSION['apptype'] == 4 || $_SESSION['apptype'] == 5) {
        $list = array();
        util::send_result($list);
    }
    $pindex = max(1, intval($_GPC['page']));
    $psize = max(20, intval($_GPC['psize']));
    $condition = array();
    $condition['regionid'] = $regionid;
    $address = $_GPC['address'];
    if ($address) {
        $condition['address like'] = "%{$address}%";
    }
    $rooms = pdo_getslice('xcommunity_member_room', $condition, array($pindex, $psize), $total, '', '', array('id desc'));
    $regionTitle = pdo_getcolumn('xcommunity_region', array('id' => $regionid), 'title');
    $list = array();
    foreach ($rooms as $k => $v) {
        $list[] = array(
            'id'       => $v['id'],
            'regionid' => $v['regionid'],
            'title'    => $regionTitle,
            'address'  => $v['address'],
            'square'   => $v['square'],
            'code'     => $v['code'],
            'enable'   => $v['enable'],
            'url'      => $_W['siteroot'] . $this->createMobileUrl('xqsys', array('op' => 'room', 'p' => 'add', 'id' => $v['id'], 'regionid' => $v['regionid']))
        );
    }
    util::send_result($list);
}
/**
 * 添加修改房号
 */
if ($op == 'add') {
    if ($_SESSION['apptype'] == 4 || $_SESSION['apptype'] == 5) {
        util::send_error(2, '权限不足');
    }
    $addr = '';
    $condition = array();
    $xqzd = util::xqzd(intval($_GPC['regionid']));
    $areaid = trim($_GPC['areaid']);
    $buildid = trim($_GPC['buildid']);
    $unitid = trim($_GPC['unitid']);
    if ($areaid) {
        $area = pdo_getcolumn('xcommunity_area', array('id' => $areaid), 'title');
    }
    if ($buildid) {
        $build = pdo_getcolumn('xcommunity_build', array('id' => $buildid), 'buildtitle');
    }
    if ($unitid) {
        $unit = pdo_getcolumn('xcommunity_unit', array('id' => $unitid), 'unit');
    }
    if ($areaid) {
        $addr .= $area . $xqzd['a'];
        $condition['areaid'] = $areaid;
    }
    if ($buildid) {
        $addr .= $build . $xqzd['b'];
        $condition['buildid'] = $buildid;
    }
    if ($unitid) {
        $addr .= $unit . $xqzd['c'];
        $condition['unitid'] = $unitid;
    }
    if ($_GPC['room']) {
        $addr .= $_GPC['room'] . $xqzd['d'];
        $condition['room'] = $_GPC['room'];
    }
    $id = intval($_GPC['id']);
    $data = array(
        'area'       => $area ? $area : 0,
        'build'      => $build,
        'unit'       => $unit,
        'room'       => $_GPC['room'],
        'square'     => $_GPC['square'],
        'address'    => $_GPC['address'] ? $_GPC['address'] : $addr,
        'createtime' => TIMESTAMP,
    );

    if (empty($id)) {
        $room = pdo_get('xcommunity_member_room', $condition, array());
        $d = array(
            'uniacid'  => $_W['uniacid'],
            'regionid' => intval($_GPC['regionid']),
            'realname' => trim($_GPC['realname']),
            'mobile'   => trim($_GPC['mobile']),
            'status'   => 1
        );
        if ($room['id']) {
            $d['addressid'] = $room['id'];
            if (!empty($_GPC['realname']) || !empty($_GPC['mobile'])) {
                pdo_insert('xcommunity_member_log', $d);
            }
        } else {
            $data['uniacid'] = $_W['uniacid'];
            $data['code'] = rand(10000000, 99999999);
            $data['areaid'] = $areaid;
            $data['buildid'] = $buildid;
            $data['unitid'] = $unitid;
            $data['regionid'] = intval($_GPC['regionid']);
            if (pdo_insert('xcommunity_member_room', $data)) {
                $d['addressid'] = pdo_insertid();
                pdo_insert('xcommunity_member_log', $d);
                util::permlog('房号管理-添加', '添加房号信息ID:' . $d['addressid']);
            }
        }
    } else {
        pdo_update('xcommunity_member_room', $data, array('id' => $id));
        util::permlog('房号管理-修改', '修改房号信息ID:' . $addressid);
    }
    util::send_result();
}
if ($op == 'member') {
    $p = in_array(trim($_GPC['p']), array('list', 'add', 'del')) ? trim($_GPC['p']) : 'list';
    if ($p == 'list') {

        $pindex = max(1, intval($_GPC['page']));
        $psize = max(30, intval($_GPC['psize']));
        $condition = 't1.uniacid=:uniacid';
        $params[':uniacid'] = $_SESSION['appuniacid'];
        $addressid = intval($_GPC['addressid']);
        if ($addressid) {
            $condition .= " and t1.addressid=:addressid";
            $params[':addressid'] = $addressid;
        }
        if (!empty($_GPC['keyword'])) {
            $condition .= " AND (t2.address LIKE :keyword or t1.realname LIKE :keyword or t1.mobile LIKE :keyword)";
            $params[':keyword'] = "%{$_GPC['keyword']}%";
        }
        if (intval($_GPC['regionid'])) {
            $condition .= " and t1.regionid=:regionid";
            $params[':regionid'] = intval($_GPC['regionid']);
        }
        $sql = "select t1.*,t2.address,t3.title from " . tablename("xcommunity_member_log") . "t1 left join" . tablename('xcommunity_member_room') . "t2 on t1.addressid = t2.id left join" . tablename('xcommunity_region') . "t3 on t3.id=t2.regionid where $condition order by t1.id desc LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
        $list = pdo_fetchall($sql, $params);
        util::send_result($list);
        include $this->template('web/room/show/list');
    } elseif ($p == 'del') {
        $id = intval($_GPC['id']);
        if (empty($id)) {
            itoast('缺少参数');
        }
        $item = pdo_get('xcommunity_member_log', array('id' => $id), array('id'));
        if ($item) {
            if (pdo_delete('xcommunity_member_log', array('id' => $id))) {
                itoast('删除成功', referer(), 'success');
            }
        }
    } elseif ($p == 'add') {
        $id = intval($_GPC['id']);
        $addressid = intval($_GPC['addressid']);
        if (empty($addressid)) {
            if (empty($id)) {
                itoast('缺少参数');
            }
            if ($id) {
                $item = pdo_get('xcommunity_member_log', array('id' => $id), array());
                $addressid = $item['addressid'];
            }
        }
        if (checksubmit('submit')) {
            $data = array(
                'realname' => trim($_GPC['realname']),
                'mobile'   => trim($_GPC['mobile']),
            );
            if ($id) {
                pdo_update('xcommunity_member_log', $data, array('id' => $id));
            } else {
                $data['addressid'] = $addressid;
                pdo_insert('xcommunity_member_log', $data);
            }
            itoast('操作成功', $this->createWebUrl('room', array('op' => 'show', 'p' => 'list', 'addressid' => $addressid)), 'success');
        }

    }

}
if ($op == 'detail') {
    $id = intval($_GPC['id']);
    $item = array();
    $sql = "select * from" . tablename('xcommunity_member_room') . "where id=:id ";
    $item = pdo_fetch($sql, array(':id' => $id));
    $arr = util::xqset($item['regionid']);
    $areas = pdo_getall('xcommunity_area', array('regionid' => $item['regionid']));
    foreach ($areas as $a => $area) {
        $areas[$a]['key'] = $area['id'];
        $areas[$a]['value'] = $area['title'] . $arr['a1'];
    }
    $builds = pdo_getall('xcommunity_build', array('regionid' => $item['regionid']));
    foreach ($builds as $b => $build) {
        $builds[$b]['key'] = $build['id'];
        $builds[$b]['value'] = $build['buildtitle'] . $arr['b1'];
    }
    $units = pdo_getall('xcommunity_unit', array('buildid' => $item['buildid']));
    foreach ($units as $c => $unit) {
        $units[$c]['key'] = $unit['id'];
        $units[$c]['value'] = $unit['unit'] . $arr['c1'];
    }
    $item['areas'] = $areas;
    $item['builds'] = $builds;
    $item['units'] = $units;
    $item['url'] = $this->createMobileUrl('xqsys', array('op' => 'room', 'p' => 'add', 'id' => $item['id']));
    util::send_result($item);
}
/**
 * 删除房屋的信息
 */
if ($op == 'delete') {
    $id = intval($_GPC['id']);
    if ($id) {
        $item = pdo_get('xcommunity_member_room', array('id' => $id), array());
        if ($item) {
            pdo_delete('xcommunity_member_log', array('addressid' => $item['id']));
            pdo_delete('xcommunity_member_bind', array('addressid' => $item['id']));
            pdo_delete('xcommunity_member_family', array('addressid' => $item['id']));
            if (pdo_delete("xcommunity_member_room", array('id' => $item['id']))) {
                util::permlog('用户数据-删除', '删除用房号:' . $item['room']);
            }
        }
    }
    util::send_result();
}
/**
 * 房屋预留信息
 */
if ($op == 'show') {
    $pindex = max(1, intval($_GPC['page']));
    $psize = max(20, intval($_GPC['psize']));
    if ($_SESSION['apptype'] == 4 || $_SESSION['apptype'] == 5) {
        $list = array();
        util::send_result($list);
    }
    $condition = array();
    $addressid = intval($_GPC['addressid']);
    if ($addressid) {
        $condition['addressid'] = $addressid;
    }
    $logs = pdo_getslice('xcommunity_member_log', $condition, array($pindex, $psize), $total, '', '', array('id desc'));
    $logs_addressids = _array_column($logs, 'addressid');
    $rooms = pdo_getall('xcommunity_member_room', array('id' => $logs_addressids), array('id', 'address'), 'id');
    $logs_rids = _array_column($logs, 'regionid');
    $regions = pdo_getcolumn('xcommunity_region', array('id' => $logs_rids), 'title');
    $list = array();
    if ($logs) {
        foreach ($logs as $k => $v) {
            $list[] = array(
                'id'        => $v['id'],
                'realname'  => $v['realname'],
                'mobile'    => $v['mobile'],
                'status'    => $v['status'],
                'address'   => $regionTitle . $rooms[$v['addressid']]['address'],
                'addressid' => $v['addressid'],
            );
        }
    }
    util::send_result($list);
}
/**
 * 房屋预留信息的详情
 */
if ($op == 'showDetail') {
    $id = intval($_GPC['id']) ? intval($_GPC['id']) : 26;
    if ($id) {
        $item = pdo_get('xcommunity_member_log', array('id' => $id), array());
    }
    $data = array(
        'id'       => $item['id'],
        'realname' => $item['realname'],
        'mobile'   => $item['mobile'],
        'status'   => $item['status'],
    );
    util::send_result($data);
}
/**
 * 房屋预留信息添加修改
 */
if ($op == 'showAdd') {
    $id = intval($_GPC['id']);
    $addressid = intval($_GPC['addressid']);
    $room = pdo_get('xcommunity_member_room', array('id' => $addressid), array('uniacid', 'id', 'regionid'));
    $data = array(
        'uniacid'  => $room['uniacid'],
        'regionid' => $room['regionid'],
        'realname' => trim($_GPC['realname']),
        'mobile'   => trim($_GPC['mobile']),
        'status'   => trim($_GPC['status']),
    );
    if ($id) {
        pdo_update('xcommunity_member_log', $data, array('id' => $id));
    } else {
        $data['addressid'] = $addressid;
        pdo_insert('xcommunity_member_log', $data);
    }
    util::send_result();
}
/**
 * 房屋预留信息的二维码
 */
if ($op == 'showQr') {
    $id = intval($_GPC['id']) ? intval($_GPC['id']) : 26;
    $log = pdo_get('xcommunity_member_log', array('id' => $id), array('addressid', 'regionid', 'id', 'realname', 'uniacid'));
    $address = pdo_getcolumn('xcommunity_member_room', array('id' => $log['addressid']), 'address');
    $regionTitle = pdo_getcolumn('xcommunity_region', array('id' => $log['regionid']), 'title');
    $url = $_W['siteroot'] . "app/index.php?i={$log['uniacid']}&c=entry&id={$id}&do=bind&m=" . $this->module['name'] . "&t=" . time();//二维码内容
    $temp = $regionTitle . "-" . strFilter($address) . "-" . $log['realname'] . ".png";
    $tmpdir = "../addons/" . $this->module['name'] . "/data/qrcode/room/" . $log['uniacid'] . "/" . $regionTitle . "/" . strFilter($address) . "/";
    $qr = createQr($url, $temp, $tmpdir);
    $data = array();
    $data['qrcode'] = $url;
    util::send_result($data);
}