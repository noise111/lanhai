<?php
/**
 * Created by njlanniu.
 * User: njlanniu
 * Time: 2018/3/12 下午7:59
 */
global $_GPC, $_W;
$ops = array('list', 'detail', 'add','display','del');
$op = trim($_GPC['op']);
if (!in_array($op, $ops)) {
    util::send_error(-1, '参数错误');
}
if ($op == 'list') {
    $pindex = max(1, intval($_GPC['page']));
    $psize = 20;
    $condition = "";
    if ($_SESSION['apptype'] == 3) {
        //小区管理员
        $condition .= " and id in({$_SESSION['appregionids']})";
    }
    $sql = "select id,title,thumb from" . tablename('xcommunity_region') . "where uniacid=:uniacid $condition LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
    $regions = pdo_fetchall($sql, array(':uniacid' => $_SESSION['appuniacid']));
    foreach ($regions as $k => $v) {
        $total = pdo_fetchcolumn("select count(*) from" . tablename('xcommunity_build') . "where regionid=:regionid", array(':regionid' => $v['id']));
        $regions[$k]['total'] = $total;
        $regions[$k]['thumb'] = $v['thumb'] ? tomedia($v['thumb']) : MODULE_URL . 'template/mobile/default2/static/img/icon-zanwu.png';
        $regions[$k]['url'] = $this->createMobileUrl('xqsys', array('op' => 'build', 'p' => 'display', 'regionid' => $v['id']));
    }
    util::send_result($regions);
}
elseif ($op == 'add') {
    $id = intval($_GPC['id']);
    $regionid = intval($_GPC['regionid']);
    if ($_SESSION['apptype'] == 4 || $_SESSION['apptype'] == 5) {
        util::send_error(2,'权限不足');
    }
    $data = array(
        'uniacid' => $_W['uniacid'],
        'regionid' => $regionid,
        'buildtitle' => trim($_GPC['buildtitle']),
        'unit_num' => intval($_GPC['unit_num']),
        'floor_num' => intval($_GPC['floor_num']),
        'room_num' => intval($_GPC['room_num']),
        'build_type' => trim($_GPC['build_type']),
        'areaid' => trim($_GPC['areaid'])

    );
    if (empty($id)) {
        $data['uid'] = $_SESSION['appuid'];
        $item = pdo_get('xcommunity_build', array('buildtitle' => $data['buildtitle'], 'regionid' => $data['regionid'], 'areaid' => $data['areaid']), array());
        if (empty($item)) {
            pdo_insert('xcommunity_build', $data);
        } else {
            util::send_error(-1, '楼宇不可重复添加');
            exit();
        }

        $buildid = pdo_insertid();
        for ($i = 1; $i <= $data['unit_num']; $i++) {
            $dat = array(
                'uniacid' => $_W['uniacid'],
                'buildid' => $buildid,
                'unit' => $i,
                'uid' => $_W['uid']
            );
            pdo_insert('xcommunity_unit', $dat);
        }
    } else {
        pdo_update('xcommunity_build', $data, array('id' => $id));
    }
    util::send_result();
}
elseif ($op == 'detail') {
    $id = intval($_GPC['id']);
    if ($id) {
        $item = pdo_get('xcommunity_build', array('id' => $id), array());
        util::send_result($item);
    }
}
elseif($op =='display'){
    if ($_SESSION['apptype'] == 4 || $_SESSION['apptype'] == 5) {
        $data['list'] = array();
        util::send_result($data);
    }
    $pindex = max(1, intval($_GPC['page']));
    $psize = 20;
    $condition = 't1.uniacid =:uniacid and t1.regionid=:regionid ';
    $params[':uniacid'] = $_SESSION['appuniacid'];
    $params[':regionid'] = intval($_GPC['regionid']);
    $sql = "SELECT t1.*,t2.title,t3.title as area FROM" . tablename('xcommunity_build') . "t1 left join" . tablename('xcommunity_region') . "t2 on t1.regionid = t2.id left join" . tablename('xcommunity_area') . "t3 on t3.id = t1.areaid WHERE $condition order by t1.id desc LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
    $list = pdo_fetchall($sql, $params);
    foreach ($list as $k => $v) {
        $list[$k]['link'] = $this->createMobileUrl('xqsys', array('op' => 'build', 'p' => 'add', 'id' => $v['id']));
    }
    $data = array();
    $data['list'] = $list;
    util::send_result($data);
}
elseif($op == 'del'){
    $id = intval($_GPC['id']);
    $item = pdo_getcolumn('xcommunity_build',array('id' => $id),'id');
    if (empty($item)){
        util::send_error(-1,'参数错误');
    }
    pdo_delete('xcommunity_unit',array('buildid'=>$id));
    if (pdo_delete('xcommunity_build',array('id' => $id))){
        util::send_result();
    }
}