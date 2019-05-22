<?php
/**
 * Created by xiaoqu.
 * User: zhoufeng
 * Time: 2017/12/8 下午1:42
 */
global $_GPC, $_W;
$ops = array('list', 'detail', 'add','del','toblack');
$op = trim($_GPC['op']);
if (!in_array($op, $ops)) {
    util::send_error(-1, '参数错误');
}
//$_SESSION['community'] = model_user::mc_check();
if ($op == 'list') {
    $pindex = max(1, intval($_GPC['page']));
    $psize = max(20, intval($_GPC['psize']));
    $condition = 't1.uniacid=:uniacid and t1.black=0 and t1.type=:type and t1.enable=0';
    $params[':uniacid'] = $_W['uniacid'];
    $params[':type'] = intval($_GPC['type']) ? intval($_GPC['type']) : 1;
    $p6 = set('p6');
    if (empty($p6)) {
        $condition .= " AND t1.regionid=:regionid";
        $params[':regionid'] = $_SESSION['community']['regionid'] ;
    }
    if (!empty($_GPC['driver_destination'])) {
        $keyword = "%{$_GPC['driver_destination']}%";
        $condition .= " AND t1.end_position LIKE '{$keyword}'";
    }
    $driver_seat = intval($_GPC['driver_seat']);
    if ($driver_seat) {
        $condition .= " AND t1.seat='{$driver_seat}'";
    }
    $price = intval($_GPC['price']);
    if ($price == 20) {
        $condition .= " AND t1.sprice between 0 and 20";
    }
    elseif ($price == 40) {
        $condition .= " AND t1.sprice between 20 and 40";
    }
    elseif ($price == 60) {
        $condition .= " AND t1.sprice between 40 and 60";
    }
    elseif ($price == 1000) {
        $condition .= " AND t1.sprice between 60 and 1000";
    }
    $sql = "select t1.*,t2.realname,t1.mobile,t2.avatar,t3.title as regionname from" . tablename('xcommunity_carpool') . "t1 left join" . tablename('mc_members') . "t2 on t1.uid=t2.uid left join" . tablename('xcommunity_region') . "t3 on t3.id = t1.regionid where $condition order by t1.createtime desc limit " . ($pindex - 1) * $psize . ',' . $psize;
    $list = pdo_fetchall($sql, $params);
    foreach ($list as $key => $value) {
        $url = $this->createMobileUrl('car', array('op' => 'detail', 'id' => $value['id']));
        $datetime = date('Y-m-d H:i', $value['createtime']);
        $list[$key]['url'] = $url;
        $list[$key]['datetime'] = $datetime;
        $list[$key]['link'] = $this->createMobileUrl('xqsys', array('op' => 'carpool','p' => 'detail', 'id' => $value['id']));
    }
    $data = array();
    $data['list'] = $list;
    $data['hstatus'] = set('p96') ? 1 :0;
    $data['url'] = $this->createMobileUrl('car', array('op' => 'add'));
    util::send_result($data);
}
elseif ($op == 'detail') {
    $id = intval($_GPC['id']) ;
//    if ($id) {
        $condition = " t1.id=:id";
        $params[':id'] = $id;
        $sql = "select t1.*,t2.realname,t1.mobile,t2.avatar,t3.title as regionname,t2.avatar from" . tablename('xcommunity_carpool') . "t1 left join" . tablename('mc_members') . "t2 on t1.uid=t2.uid left join" . tablename('xcommunity_region') . "t3 on t3.id = t1.regionid where $condition ";
        $item = pdo_fetch($sql, $params);
        $item['createtime'] = date('Y-m-d H:i', $item['createtime']);
        $item['hstatus'] = set('p96') ? 1 :0;
        $item['url'] = "tel:".$item['mobile'];
        if ($item) {
            util::send_result($item);
        }
//    }
}
elseif ($op == 'add') {
    $regionid = $_SESSION['community']['regionid'];
    $data = array(
        'uniacid'        => $_W['uniacid'],
        'regionid'       => $regionid,
        'uid'            => $_W['member']['uid'],
        'title'          => $_GPC['title'],
        'seat'           => $_GPC['seat'],
        'sprice'         => $_GPC['sprice'],
        'contact'        => $_GPC['contact'],
        'mobile'         => $_GPC['mobile'],
        'start_position' => $_GPC['start_position'],
        'end_position'   => $_GPC['end_position'],
        'tj_position'    => $_GPC['tj_position'],
        'gotime'         => $_GPC['gotime'],
        'backtime'       => $_GPC['backtime'],
        'type'           => intval($_GPC['type']) ? intval($_GPC['type']) : 1,
        'createtime'     => TIMESTAMP,
    );
    $data['enable'] = set('x9', $regionid) || set('p29') ? 1 : 0; //0审核通过
    if (strtotime($data['gotime']) > strtotime($data['backtime'])) {
        $data = array('content' => '出发时间不能早于返回时间');
        util::send_error(-1, '出发时间不能早于返回时间');
        exit();
    }
    if (pdo_insert('xcommunity_carpool', $data)) {
        $data = array();
        $data['content'] = '发布成功';
        util::send_result($data);
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