<?php
/**
 * Created by njlanniu.
 * User: njlanniu
 * Time: 2018/3/13 上午11:38
 */
global $_W, $_GPC;
$ops = array('list', 'detail', 'add', 'one', 'del', 'change','member','display');
$op = trim($_GPC['op']);
if (!in_array($op, $ops)) {
    util::send_error(-1, '参数错误');
}
if($op =='list'){
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
        $total = pdo_fetchcolumn("select count(*) from" . tablename('xcommunity_xqcars') . "where regionid=:regionid", array(':regionid' => $v['id']));
        $regions[$k]['total'] = $total;
        $regions[$k]['thumb'] = $v['thumb'] ? tomedia($v['thumb']) : MODULE_URL . 'template/mobile/default2/static/img/icon-zanwu.png';
        $regions[$k]['url'] = $this->createMobileUrl('xqsys', array('op' => 'car', 'p' => 'display', 'regionid' => $v['id']));
    }
    util::send_result($regions);
}
elseif($op =='display'){
    if ($_SESSION['apptype'] == 4 || $_SESSION['apptype'] == 5) {
        $list = array();
        util::send_result($list);
    }
    $pindex = max(1, intval($_GPC['page']));
    $psize = 20;
    $condition = " t1.uniacid=:uniacid and t1.regionid=:regionid";
    $params[':uniacid'] = $_SESSION['appuniacid'];
    $params[':regionid'] = intval($_GPC['regionid']);
    $sql = "select t1.*,t2.title,t3.place_num from".tablename('xcommunity_xqcars')."t1 left join".tablename('xcommunity_region')."t2 on t1.regionid = t2.id left join".tablename('xcommunity_parking')."t3 on t3.id = t1.parkingid where $condition order by t1.id desc LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
    $list = pdo_fetchall($sql, $params);
    foreach ($list as $k => $v){
        $list[$k]['url'] = $this->createMobileUrl('xqsys', array('op' => 'car','p' => 'detail', 'id' => $v['id']));
    }
    util::send_result($list);
}
elseif($op == 'detail'){
    $id = intval($_GPC['id']);
    if($id){
        $item = pdo_fetch("select t1.*,t2.title,t3.place_num from".tablename('xcommunity_xqcars')."t1 left join".tablename('xcommunity_region')."t2 on t1.regionid = t2.id left join".tablename('xcommunity_parking')."t3 on t3.id = t1.parkingid where t1.id=:id",array(':id' => $id));
        util::send_result($item);
    }
}
elseif($op == 'add'){
    $id = intval($_GPC['id']);
    if ($_SESSION['apptype'] == 4 || $_SESSION['apptype'] == 5) {
        util::send_error(2,'权限不足');
    }
    $data = array(
        'uniacid' => $_W['uniacid'],
        'regionid' => intval($_GPC['regionid']),
        'parkingid' => intval($_GPC['parkingid']),
        'realname' => trim($_GPC['realname']),
        'mobile' => trim($_GPC['mobile']),
        'car_num' => $_GPC['car_num']
    );
    if ($id) {
        pdo_update('xcommunity_xqcars', $data, array('id' => $id));
    } else {
        pdo_insert('xcommunity_xqcars', $data);
    }
    util::send_result();
}
elseif($op == 'del'){
    $id = intval($_GPC['id']);
    $item = pdo_getcolumn('xcommunity_xqcars',array('id' => $id),'id');
    if (empty($item)){
        util::send_error(-1,'参数错误');
    }
    if (pdo_delete('xcommunity_xqcars',array('id' => $id))){
        util::send_result();
    }
}