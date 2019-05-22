<?php
/**
 * Created by njlanniu.com.
 * User: 蓝牛科技
 * Time: 2018/4/17 下午3:33
 */
global $_GPC, $_W;
$ops = array('list', 'detail', 'add','del','grab');
$op = trim($_GPC['op']);
if (!in_array($op, $ops)) {
    util::send_error(-1, '参数错误');
}
if ($op =='list'){

    $pindex = max(1, intval($_GPC['page']));
    $psize = max(20, intval($_GPC['psize']));
    //搜索
    $condition = 't1.uniacid=:uniacid';
    $params[':uniacid'] = $_SESSION['appuniacid'];
    if ($_SESSION['apptype'] == 2) {
        $condition .= " and t1.uid=:uid ";
        $params[':uid'] = $_SESSION['appuid'];
    }
    if($_SESSION['apptype'] == 3){
        $condition .= " and t1.regionid in (:regionid)";
        $params[':regionid'] = $_SESSION['appregionids'];
    }
    if ($_SESSION['apptype'] == 4 || $_SESSION['apptype'] == 5) {
        $list = array();
        util::send_result($list);
    }
    $sql = "select t5.title,t1.*,t4.realname,t4.mobile,t2.area,t2.build,t2.unit,t2.room,t6.name,t2.address from" . tablename('xcommunity_homemaking') . "t1 left join" . tablename('xcommunity_member_room') . "t2 on t1.addressid=t2.id left join" . tablename('mc_members') . "t4 on t4.uid=t1.uid left join " . tablename('xcommunity_region') . "t5 on t5.id = t1.regionid left join".tablename('xcommunity_category')."t6 on t6.id = t1.category where $condition order by t1.createtime desc limit " . ($pindex - 1) * $psize . ',' . $psize;
    $list = pdo_fetchall($sql, $params);
    foreach ($list as $k => $v){
        $list[$k]['createtime'] = date('Y-m-d H:i',$v['createtime']);
        $list[$k]['link'] = $this->createMobileUrl('xqsys', array('op' => 'homemaking','p' => 'detail', 'id' => $v['id']));
    }
    util::send_result($list);
}
elseif($op == 'detail'){
    $id = intval($_GPC['id']);
    $item = pdo_fetch("select t5.title,t1.*,t4.realname,t4.mobile,t2.area,t2.build,t2.unit,t2.room,t6.name,t2.address from" . tablename('xcommunity_homemaking') . "t1 left join" . tablename('xcommunity_member_room') . "t2 on t1.addressid=t2.id left join" . tablename('mc_members') . "t4 on t4.uid=t1.uid left join " . tablename('xcommunity_region') . "t5 on t5.id = t1.regionid left join".tablename('xcommunity_category')."t6 on t6.id = t1.category where t1.id=:id",array(':id' => $id));
    $item['createtime'] = date('Y-m-d H:i',$item['createtime']);
    $item['url'] = $this->createMobileUrl('xqsys', array('op' => 'homemaking', 'p' => 'add','id' => $item['id']));
    util::send_result($item);
}
elseif($op == 'grab'){
    $id = intval($_GPC['id']);
    $item = pdo_fetch("select t1.*,t4.realname,t4.mobile,t2.area,t2.build,t2.unit,t2.room from" . tablename('xcommunity_homemaking') . "t1 left join" . tablename('xcommunity_member_room') . "t2 on t1.addressid=t2.id left join" . tablename('mc_members') . "t4 on t4.uid=t1.uid where t1.id=:id order by t1.createtime desc ", array(':id' => $id));
    if ($item){
        $data = array(
            'status' => $_GPC['status'],
            'remark' => $_GPC['remark']
        );
        pdo_update("xcommunity_homemaking", $data, array('id' => $id));
        if (set('p53')) {
            $content = $_GPC['status'] == 1 ? '您的家政服务已完成' : '您的家政服务已取消';
            util::app_send($item['uid'], $content);
        }
        util::permlog('', '修改家政服务状态' . '家政信息ID:' . $id);
        util::send_result($item);
    }
}
elseif($op == 'del'){
    $id = intval($_GPC['id']);
    $item = pdo_getcolumn('xcommunity_homemaking',array('id' => $id),'id');
    if (empty($item)){
        util::send_error(-1,'参数错误');
    }
    if (pdo_delete('xcommunity_homemaking',array('id' => $id))){
        util::send_result();
    }
}