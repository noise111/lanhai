<?php
/**
 * Created by njlanniu.
 * User: njlanniu
 * Time: 2018/3/13 上午11:14
 */
global $_W, $_GPC;
$ops = array('list', 'detail','add','display','del');
$op = trim($_GPC['op']);
if (!in_array($op, $ops)) {
    util::send_error(-1, '参数错误');
}
if($op =='list'){
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
        $total = pdo_fetchcolumn("select count(*) from" . tablename('xcommunity_parking') . "where regionid=:regionid", array(':regionid' => $v['id']));
        $regions[$k]['total'] = $total;
        $regions[$k]['thumb'] = $v['thumb'] ? tomedia($v['thumb']) : MODULE_URL . 'template/mobile/default2/static/images/icon-zanwu.png';
        $regions[$k]['url'] = $this->createMobileUrl('xqsys', array('op' => 'park', 'p' => 'display', 'regionid' => $v['id']));
    }
    util::send_result($regions);
}
if($op =='display'){
    if ($_SESSION['apptype'] == 4 || $_SESSION['apptype'] == 5) {
        $list = array();
        util::send_result($list);
    }
    $pindex = max(1, intval($_GPC['page']));
    $psize = max(20, intval($_GPC['psize']));
    $condition = " t1.uniacid=:uniacid and t1.regionid=:regionid";
    $params[':uniacid'] =$_SESSION['appuniacid'];
    $params[':regionid'] = intval($_GPC['regionid']);
    $sql = "select t1.*,t2.title,t3.realname,t3.mobile,t3.starttime,t3.endtime from" . tablename('xcommunity_parking') . "t1 left join" . tablename('xcommunity_region') . "t2 on t1.regionid = t2.id left join".tablename('xcommunity_parking_record')."t3 on t3.parkingid = t1.id where $condition order by id desc LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
    $list = pdo_fetchall($sql, $params);
    foreach ($list as $k => $v){
        $list[$k]['url'] = $this->createMobileUrl('xqsys', array('op' => 'park','p' => 'detail', 'id' => $v['id']));
    }
    util::send_result($list);
}
if($op =='add'){
    $id = intval($_GPC['id']);
    if ($_SESSION['apptype'] == 4 || $_SESSION['apptype'] == 5) {
        util::send_error(2,'权限不足');
    }
    $data = array(
        'uniacid' => $_W['uniacid'],
        'regionid' => intval($_GPC['regionid']),
        'place_num' => trim($_GPC['place_num']),
        'area' => trim($_GPC['area']),
        'remark' => trim($_GPC['remark']),
        'status' => $_GPC['status']
    );
    if ($id) {
        pdo_update('xcommunity_parking', $data, array('id' => $id));
    } else {
        pdo_insert('xcommunity_parking', $data);
    }
    util::send_result();
}
/**
 * 车位的详情
 */
if($op == 'detail'){
    $id = intval($_GPC['id']);
    if ($id){
        $item = pdo_fetch("select t1.*,t2.title,t3.realname,t3.mobile,t3.starttime,t3.endtime,t4.car_num from" . tablename('xcommunity_parking') . "t1 left join" . tablename('xcommunity_region') . "t2 on t1.regionid = t2.id left join".tablename('xcommunity_parking_record')."t3 on t3.parkingid = t1.id left join".tablename('xcommunity_xqcars')."t4 on t4.id=t3.xqcarid where t1.id=:id",array(':id' => $id));
        $item['remark']=strip_tags(str_replace('&nbsp;','',$item['remark']));
        $item['url'] = $this->createMobileUrl('xqsys', array('op' => 'park', 'p' => 'add','id' => $item['id']));
        util::send_result($item);
    }
}
/**
 * 车位的删除
 */
if($op == 'del'){
    $id = intval($_GPC['id']);
    $item = pdo_getcolumn('xcommunity_parking',array('id' => $id),'id');
    if (empty($item)){
        util::send_error(-1,'参数错误');
    }
    if (pdo_delete('xcommunity_parking',array('id' => $id))){
        pdo_delete('xcommunity_parking_record',array('parkingid' => $id));
        util::send_result();
    }
}
