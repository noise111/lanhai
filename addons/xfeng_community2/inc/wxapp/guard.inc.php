<?php
/**
 * Created by njlanniu.com.
 * User: 蓝牛科技
 * Time: 2018/3/8 下午1:50
 */
global $_GPC, $_W;
$ops = array('list', 'detail', 'add');
$op = trim($_GPC['op']);
if (!in_array($op, $ops)) {
    util::send_error(-1, '参数错误');
}
if ($op == 'list') {
    $pindex = max(1, intval($_GPC['page']));
    $psize = 20;
    $condition = ' uniacid = :uniacid';
    $params[':uniacid'] = $_SESSION['appuniacid'] ? $_SESSION['appuniacid'] : $_W['uniacid'];
    if ($_SESSION['apptype'] == 2) {
        //普通管理员
        $condition .= " and uid =:uid";
        $params[':uid'] = $_SESSION['appuid'];
    }
    if ($_SESSION['apptype'] == 3) {
        //小区管理员
        $condition .= " and id in({$_SESSION['appregionids']})";
    }
    if ($_SESSION['apptype'] == 4 || $_SESSION['apptype'] == 5) {
        $regions = array();
        util::send_result($regions);
    }
    $sql = "select id,title,thumb from" . tablename('xcommunity_region') . "where $condition LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
    $regions = pdo_fetchall($sql, $params);
    foreach ($regions as $k => $v) {
        $total = pdo_fetchcolumn("select count(*) from" . tablename('xcommunity_building_device') . "where regionid=:regionid", array(':regionid' => $v['id']));
        $regions[$k]['total'] = $total;
        $regions[$k]['thumb'] = $v['thumb'] ? tomedia($v['thumb']) : MODULE_URL . 'template/mobile/default2/static/img/icon-zanwu.png';
        $regions[$k]['url'] = $this->createMobileUrl('xqsys', array('op' => 'guard', 'p' => 'detail', 'regionid' => $v['id']));
    }
    util::send_result($regions);
}
elseif ($op == 'detail') {
    $regionid = intval($_GPC['regionid']);
    $pindex = max(1, intval($_GPC['page']));
    $psize = 20;
    $sql = "select t1.title,t1.unit,t2.title as rtitle from" . tablename('xcommunity_building_device') . "t1 left join" . tablename('xcommunity_region') . "t2 on t1.regionid=t2.id where t2.id=:regionid order by t1.displayorder asc LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
    $list = pdo_fetchall($sql, array(':regionid' => $regionid));
    util::send_result($list);
}
elseif($op =='add'){
    if (empty($id)) {
        $device = pdo_get('xcommunity_building_device', array('device_code' => $_GPC['device_code']), array('id'));
        if ($device) {
            util::send_error(-1, '');
            exit();
        }
    }
    if ($_SESSION['apptype'] == 4 || $_SESSION['apptype'] == 5) {
        util::send_error(2,'权限不足');
    }
    $data = array(
        'uniacid'     => $_W['uniacid'],
        'title'       => $_GPC['title'],
        'device_code' => $_GPC['device_code'],
        'type'        => intval($_GPC['type']),
        'openurl'     => $_GPC['openurl'],
        'regionid'    => intval($_GPC['regionid']),
        'device_gprs' => $_GPC['device_gprs'],
        'createtime'  => TIMESTAMP,
        'lng'         => '',
        'lat'         => '',
        'thumb'       => $_GPC['pic'],
        'category'    => intval($_GPC['category'])
    );
    if ($data['type'] == 1) {
        $data['unit'] = $_GPC['unit'];
    }
    if ($id) {
        pdo_update('xcommunity_building_device', $data, array('id' => $id));
        util::permlog('智能门禁-修改', '区域名称:' . $data['title']);
    }
    else {
        $data['uid'] = $_SESSION['appuid'];
        pdo_insert('xcommunity_building_device', $data);
        $id = pdo_insertid();
        util::permlog('智能门禁-添加', '区域名称:' . $data['title']);
    }
    util::send_result();
}