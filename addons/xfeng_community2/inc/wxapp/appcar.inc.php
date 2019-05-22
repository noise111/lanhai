<?php
/**
 * Created by njlanniu.com.
 * User: 蓝牛科技
 * Time: 2018/4/17 下午3:13
 */
global $_GPC,$_W;
$ops = array('list', 'detail', 'add','del','toblack');
$op = trim($_GPC['op']);
if (!in_array($op, $ops)) {
    util::send_error(-1, '参数错误');
}
if ($op == 'list') {
    $pindex = max(1, intval($_GPC['page']));
    $psize = max(20, intval($_GPC['psize']));
    $condition = 't1.uniacid=:uniacid ';
    $params[':uniacid'] = $_SESSION['appuniacid'];
    $keyword = trim($_GPC['keyword']);
    if($keyword){
        $condition .= " and t1.title like '%{$keyword}%'";
    }
    if ($_SESSION['apptype'] == 2) {
        $condition .= " and t1.uid=:uid ";
        $params[':uid'] = $_SESSION['appuid'];
    }
    if($_SESSION['apptype'] == 3){
        $condition .= " and t1.regionid in (:regionid)";
        $params[':regionid'] = $_SESSION['appregionids'];
    }
    if ($_SESSION['apptype'] == 4 || $_SESSION['apptype'] == 5) {
        util::send_result();
    }
    $sql = "select t1.*,t2.realname,t2.mobile,t2.avatar,t3.title as regionname from" . tablename('xcommunity_carpool') . "t1 left join" . tablename('mc_members') . "t2 on t1.uid=t2.uid left join" . tablename('xcommunity_region') . "t3 on t3.id = t1.regionid where $condition order by t1.createtime desc limit " . ($pindex - 1) * $psize . ',' . $psize;
    $list = pdo_fetchall($sql, $params);
    foreach ($list as $key => $value) {
        $datetime = date('Y-m-d H:i', $value['createtime']);
        $list[$key]['datetime'] = $datetime;
        $list[$key]['link'] = $this->createMobileUrl('xqsys', array('op' => 'carpool','p' => 'detail', 'id' => $value['id']));
    }
    util::send_result($list);
}
elseif ($op == 'detail') {
    $id = intval($_GPC['id']);
    if ($id) {
        //$item = pdo_get('xcommunity_carpool',array('id'=>$id),array());
        $condition = " t1.id=:id";
        $params[':id'] = $id;
        $sql = "select t1.*,t2.realname,t2.mobile,t2.avatar,t3.title as regionname from" . tablename('xcommunity_carpool') . "t1 left join" . tablename('mc_members') . "t2 on t1.uid=t2.uid left join" . tablename('xcommunity_region') . "t3 on t3.id = t1.regionid where $condition ";
        $item = pdo_fetch($sql, $params);
        $item['createtime'] = date('Y-m-d', $item['createtime']);
        $item['_status'] = set('p96') ? 1 :0;
        if ($item) {
            util::send_result($item);
        }
    }
}
elseif($op == 'del'){
    $id = intval($_GPC['id']);
    $item = pdo_getcolumn('xcommunity_carpool',array('id' => $id),'id');
    if (empty($item)){
        util::send_error(-1,'参数错误');
    }
    if (pdo_delete('xcommunity_carpool',array('id' => $id))){
        util::send_result();
    }
}
elseif($op == 'toblack'){
    $id = intval($_GPC['id']);
    $item = pdo_get('xcommunity_carpool',array('id' => $id),array('id','black'));
    if (empty($item)){
        util::send_error(-1,'参数错误');
    }
    $black = $item['black']==1 ? 0 : 1;
    if (pdo_update('xcommunity_carpool',array('black' => $black),array('id' => $id))){
        util::send_result();
    }
}