<?php
/**
 * Created by njlanniu.
 * User: njlanniu
 * Time: 2018/3/9 上午10:57
 */
global $_W, $_GPC;
$ops = array('list', 'detail','add');
$op = trim($_GPC['op']);
if (!in_array($op, $ops)) {
    util::send_error(-1, '参数错误');
}
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
        $tsql = "select count(*) from" . tablename('mc_members') . "as t1 left join" . tablename('xcommunity_member') . "as t2 on t1.uid=t2.uid left join" . tablename('xcommunity_region') . "as t3 on t2.regionid=t3.id left join" . tablename('xcommunity_member_bind') . "t5 on t5.memberid = t2.id where t3.id =:regionid and t1.mobile <> '' and t5.id <> '' order by t2.id desc ";
        $params[':regionid'] = $v['id'];
        $total = pdo_fetchcolumn($tsql, $params);
        $regions[$k]['total'] = $total;
        $regions[$k]['thumb'] = $v['thumb'] ? tomedia($v['thumb']) : MODULE_URL . 'template/mobile/default2/static/images/icon-zanwu.png';
        $regions[$k]['url'] = $this->createMobileUrl('xqsys', array('op' => 'member', 'p' => 'detail', 'regionid' => $v['id']));
    }
    util::send_result($regions);
}
elseif ($op == 'detail') {
    $regionid = intval($_GPC['regionid']);
    $pindex = max(1, intval($_GPC['page']));
    $psize = max(20, intval($_GPC['psize']));
    $sql = "select t4.realname,t3.address,t5.title,t1.createtime,t4.mobile,t2.id from" . tablename('xcommunity_member') . "t1 left join" . tablename('xcommunity_member_bind') . "t2 on t1.id = t2.memberid left join" . tablename('xcommunity_member_room') . "t3 on t3.id= t2.addressid left join" . tablename('mc_members') . "t4 on t4.uid=t1.uid left join" . tablename('xcommunity_region') . "t5 on t5.id=t1.regionid where t1.regionid=:regionid and t1.visit =0 order by t2.createtime desc LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
    $list = pdo_fetchall($sql, array(':regionid' => $regionid));
    foreach ($list as $k => $v){
        $list[$k]['createtime'] = date('Y-m-d H:i',$v['createtime']);
        //暂时不更新
//        $list[$k]['url'] = $this->createMobileUrl('xqsys', array('op' => 'member', 'p' => 'add', 'id' => $v['id']));
    }

    util::send_result($list);
}elseif($op=='add'){
        $id = intval($_GPC['id']);
        if($id){
            $sql = "select t3.realname,t3.mobile,t1.status,t1.addressid,t3.uid,t4.areaid,t4.buildid,t4.unitid,t4.id from" . tablename('xcommunity_member_bind') . "t1 left join" . tablename('xcommunity_member') . "t2 on t1.memberid = t2.id left join" . tablename('mc_members') . "t3 on t3.uid = t2.uid left join" . tablename('xcommunity_member_room') . "t4 on t4.id=t1.addressid where t1.id=:id";
            $item = pdo_fetch($sql, array(':id' => $id));
        }
    util::send_result($item);
}