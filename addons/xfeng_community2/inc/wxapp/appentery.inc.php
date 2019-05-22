<?php
global $_GPC, $_W;
$ops = array('list', 'detail', 'add', 'del', 'category', 'user', 'fee');
$op = trim($_GPC['op']);
if (!in_array($op, $ops)) {
    util::send_error(-1, '参数错误');
}
if ($op == 'list') {
    $pindex = max(1, intval($_GPC['page']));
    $psize = max(20, intval($_GPC['psize']));
    $condition = " t1.uniacid=:uniacid and t1.type=2";
    $params[':uniacid'] = $_SESSION['appuniacid'];
    if ($_SESSION['apptype'] == 2) {
        $condition .= " and t1.uid=:uid ";
        $params[':uid'] = $_SESSION['appuid'];
    }
    if ($_SESSION['apptype'] == 3) {
        $condition .= " and t3.regionid in (:regionid)";
        $params[':regionid'] = $_SESSION['appregionids'];
    }
    if ($_SESSION['apptype'] == 4 || $_SESSION['apptype'] == 5) {
        $list = array();
        util::send_result($list);
    }
    $row = pdo_fetchall("select t1.*,t2.title,t3.address from" . tablename('xcommunity_fee') . "t1 left join" . tablename('xcommunity_member_room') . "t3 on t3.id=t1.roomid left join" . tablename('xcommunity_region') . "t2 on t3.regionid=t2.id where $condition order by t1.createtime desc LIMIT " . ($pindex - 1) * $psize . ',' . $psize, $params);
    foreach ($row as $k => $v) {
        $row[$k]['createtime'] = date('Y-m-d H:i', $v['createtime']);
        $row[$k]['link'] = $this->createMobileUrl('xqsys', array('op' => 'entery', 'p' => 'detail', 'id' => $v['id']));
    }
    util::send_result($row);
}
elseif ($op == 'detail') {
    $id = intval($_GPC['id']);
    if ($id) {
        $item = pdo_fetch("SELECT t1.*,t2.title,t3.title as name,t4.address,t4.areaid,t4.buildid,t4.unitid,t4.regionid FROM" . tablename('xcommunity_fee') . "t1 left join" . tablename('xcommunity_fee_category') . "t3 on t1.categoryid=t3.id left join" . tablename('xcommunity_member_room') . "t4 on t1.roomid=t4.id left join" . tablename('xcommunity_region') . "t2 on t4.regionid=t2.id WHERE t1.uniacid=:uniacid AND t1.id=:id", array(":id" => $id, ":uniacid" => $_W['uniacid']));
        $arr = util::xqset($item['regionid']);
        $fees = pdo_getall('xcommunity_fee_category', array('regionid' => $item['regionid'], 'uniacid' => $_SESSION['appuniacid'], 'way' => 4));
        foreach ($fees as $k => $v) {
            $fees[$k]['key'] = $v['id'];
            $fees[$k]['value'] = $v['title'];
        }
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
        $rooms = pdo_getall('xcommunity_member_room', array('regionid' => $item['regionid'], 'buildid' => $item['buildid'], 'unitid' => $item['unitid']));
        foreach ($rooms as $d => $room) {
            $rooms[$d]['key'] = $room['id'];
            $rooms[$d]['value'] = $room['room'] . $arr['d1'];
        }
        $item['fees'] = $fees;
        $item['areas'] = $areas;
        $item['builds'] = $builds;
        $item['units'] = $units;
        $item['rooms'] = $rooms;
        $item['url'] = $this->createMobileUrl('xqsys', array('op' => 'entery', 'p' => 'add', 'id' => $item['id']));
        $item['username'] = $item['readername'];
        util::send_result($item);
    }
}
elseif ($op == 'add') {
    if ($_SESSION['apptype'] == 4 || $_SESSION['apptype'] == 5) {
        util::send_error(2, '权限不足');
    }
    $id = intval($_GPC['id']);
    $fee = pdo_getcolumn('xcommunity_fee_category', array('id' => intval($_GPC['fee'])), 'price');
    $price = 0;
    $num = $_GPC['new_num'] - $_GPC['old_num'];
    if ($num > 0) {
        $price = $num * $fee;
    }
    $data = array(
        'uniacid'    => $_SESSION['appuniacid'],
        'roomid'     => trim($_GPC['address']),
        'categoryid' => intval($_GPC['fee']),
        'new_num'    => $_GPC['new_num'],
        'old_num'    => $_GPC['old_num'],
        'createtime' => TIMESTAMP,
        'price'      => $price,
        'readername'   => $_GPC['username'],
        'uid'        => $_SESSION['appuid'],
        'type'       => 2,
        'status'    => 1
    );
    if (empty($id)) {
        $y=date("Y",time());
        $m=date("m",time());
        $d=date("d",time());
        $t0=date('t'); // 本月一共有几天
        $stime=mktime(0,0,0,$m,1,$y); // 创建本月开始时间
        $etime=mktime(23,59,59,$m,$t0,$y); // 创建本月结束时间
        $mfee = pdo_fetch("select id from".tablename('xcommunity_fee')." where type=2 and roomid=:roomid and categoryid=:cate and createtime between :stime and :etime",array(':roomid' => intval($_GPC['address']),':cate' => intval($_GPC['fee']),':stime' => $stime,':etime' => $etime));
        if ($mfee){
            util::send_error(2, '本月已经抄过一次表');
        }
        pdo_insert("xcommunity_fee", $data);
        $id = pdo_insertid();
    }
    else {
        pdo_update("xcommunity_fee", $data, array('id' => $id));
    }
    util::send_result();
}
elseif ($op == 'del') {
    $id = intval($_GPC['id']);
    $item = pdo_getcolumn('xcommunity_fee', array('id' => $id), 'id');
    if (empty($item)) {
        util::send_error(-1, '参数错误');
    }
    if (pdo_delete('xcommunity_fee', array('id' => $id))) {
        util::send_result();
    }
}
elseif ($op == 'category') {
    $item = pdo_getall('xcommunity_fee_category', array('way' => 4, 'uniacid' => $_SESSION['appuniacid']));
    foreach ($item as $k => $v) {
        $item[$k]['key'] = $v['id'];
        $item[$k]['value'] = $v['title'];
    }
    util::send_result($item);
}
elseif ($op == 'user') {
    $uid = $_SESSION['appuid'];
    $user = pdo_fetch("select t1.realname from" . tablename('xcommunity_staff') . "t1 left join" . tablename('xcommunity_users') . "t2 on t1.id=t2.staffid where t2.uid=:uid", array(':uid' => $uid));
    util::send_result($user);
}
elseif ($op == 'fee') {
    $condition = " t1.uniacid=:uniacid ";
    $params[':uniacid'] = $_SESSION['appuniacid'];
    if ($_SESSION['apptype'] == 2) {
        $condition .= " and t1.uid=:uid";
        $params[':uid'] = $_SESSION['appuid'];
    }
    if ($_SESSION['apptype'] == 3) {
        $condition .= " and t1.id in (:regionid)";
        $params[':regionid'] = $_SESSION['appregionids'];
    }
    if ($_SESSION['apptype'] == 4 || $_SESSION['apptype'] == 5) {
        $list = array();
        util::send_result($list);
    }
    $regions = pdo_fetchall("select t1.* from" . tablename('xcommunity_region') . "t1 where $condition ", $params);
    $fees = array();
    foreach ($regions as $ke => $va) {
        $item = pdo_getall('xcommunity_fee_category', array('way' => 4, 'regionid' => $va['id'], 'uniacid' => $_SESSION['appuniacid']));
        foreach ($item as $k => $v) {
            $fees[$va['id']][$k]['key'] = $v['id'];
            $fees[$va['id']][$k]['value'] = $v['title'];
        }
    }
    util::send_result($fees);
}