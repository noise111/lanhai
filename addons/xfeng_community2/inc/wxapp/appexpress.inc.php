<?php
global $_GPC, $_W;
$ops = array('list', 'send', 'senddetail', 'grab', 'addgrab', 'log', 'outgrab');
$op = trim($_GPC['op']);
$p = !empty($_GPC['p']) ? $_GPC['p'] : 'list';
$uniacid = $_SESSION['appuniacid'];
if (!in_array($op, $ops)) {
    util::send_error(-1, '参数错误');
}
if ($op == 'list') {
    $starttime = strtotime(date('Y-m-d')) - 5 * 86400;
    $endtime = strtotime(date('Y-m-d')) + 86400;
    $s = $starttime;
    $e = $endtime;
    $list = array();
    $j = 0;
    $condition = "uniacid=:uniacid ";
    $param[':uniacid'] = $_SESSION['appuniacid'];
    while ($e >= $s) {
        $param[':createtime'] = $e - 86400;
        $param[':endtime'] = $e;
        $listone = pdo_fetchall("select * from" . tablename('xcommunity_express_parcel') . "where $condition AND createtime >= :createtime AND createtime <= :endtime  ORDER BY createtime ASC", $param);
        $list[$j]['gnum'] = count($listone);
        $list[$j]['createtime'] = $e - 86400;
        $j++;
        $e = $e - 86400;
    }
    $day = $hit1 = $hit3 = array();
    $hit = array();
    if (!empty($list)) {
        foreach ($list as $row) {
            $day[] = date('m-d', $row['createtime']);
            $hit[] = intval($row['gnum']);

        }
    }
    $data = array();
    for ($i = 0; $i < count($day); $i++) {
        $data[] = array(
            'day'   => $day[$i],
            'value' => $hit[$i]
        );
    }
    util::send_result($data);
}
elseif ($op == 'send') {
    $list = array();
    $pindex = max(1, intval($_GPC['page']));
    $psize = max(20, intval($_GPC['psize']));
    $condition = " uniacid=:uniacid";
    $params[':uniacid'] = $_SESSION['appuniacid'];
    $list = pdo_fetchall("select * from" . tablename('xcommunity_express_parcel') . " where $condition order by status asc LIMIT " . ($pindex - 1) * $psize . ',' . $psize, $params);
    foreach ($list as $k => $v) {
        $company = pdo_get('xcommunity_express_company', array('company' => $v['company']));
        $collecter = pdo_get('xcommunity_express_collecting', array('uniacid' => $_W['uniacid'], 'openid' => $v['collecter']));
        $list[$k]['logo'] = tomedia($company['logo']);
        $list[$k]['collectAddress'] = $collecter['name'];
        $list[$k]['collectTel'] = $collecter['mobile'];
        $list[$k]['overtime'] = date('Y-m-d H:i', $v['overtime']);
        $list[$k]['telurl'] = "tel:" . $v['collectTel'];
        $list[$k]['url'] = $this->createMobileUrl('xqsys', array('op' => 'express', 'p' => 'senddetail', 'id' => $v['id']));
    }
    util::send_result($list);
}
elseif ($op == 'senddetail') {
    $id = intval($_GPC['id']);
    $item = pdo_fetch("select t1.*,t2.realname,t2.phone as mobile,t2.address as addr,t2.address_detail as addr_detail from" . tablename('xcommunity_express_parcel') . "t1 left join" . tablename('xcommunity_express_linkman') . "t2 on t1.openid=t2.openid where t1.id=:id", array(':id' => $id));
    $item['createtime'] = date('Y-m-d H:i', $item['createtime']);
    util::send_result($item);
}
elseif ($op == 'grab') {
    $id = intval($_GPC['id']);
    if (!$id) {
        util::send_error(-1, '参数错误');
    }
    $data = array(
        'waybillcode' => $_GPC['waycode'],
        'status'      => 2,
        'price'       => $_GPC['price']
    );
    $par = pdo_get('xcommunity_express_parcel', array('waybillcode' => $_GPC['waycode']));
    if ($par) {
        util::send_error(-1, '运单号已存在');
    }
    if (pdo_update('xcommunity_express_parcel', $data, array('id' => $id))) {
        util::send_result();
    }
}
elseif ($op == 'addgrab') {
//    $codes = $_GPC['waybillcode'];
//    $codes = explode(',', $codes);
//    $code = $codes[1];
    $code = $_GPC['waybillcode'];
    $pick_code = substr($code, -6);
    $parcel = pdo_get('xcommunity_express_parcel', array('waybillcode' => $code));
//    $store = pdo_get('xcommunity_express_save', array('waybillcode' => $code));
//    if($parcel) {
        if ($parcel['status'] == 2) {
            pdo_update('xcommunity_express_parcel', array('status' => 3, 'arrivetime' => TIMESTAMP, 'collecter' => $_W['openid'], 'pick_code' => $pick_code), array('waybillcode' => $code));
        }
    $data = array(
        'uniacid'     => $uniacid,
        'waybillcode' => $code,
        'name'        => $_GPC['name'],
        'mobile'      => $_GPC['phone'],
        'company'     => $parcel['company'],
        'pick_code'   => $pick_code,
        'status'      => 3,
        'openid'      => $_W['openid'],
        'createtime'  => TIMESTAMP
    );
    $result = pdo_insert('xcommunity_express_save', $data);
    $saveid = pdo_insertid();
    $dat = array(
        'uniacid'     => $uniacid,
        'saveid'      => $saveid,
        'waybillcode' => $code,
        'type'        => 1,
        'createtime'  => TIMESTAMP
    );
    pdo_insert('xcommunity_express_save_log', $dat);
    util::send_result();
//        }else{
//            util::send_error(-1, '该物件入库错误');
//        }
//    } else {
//        util::send_error(-1, '快递不存在');
//    }
}
elseif ($op == 'log') {
    $list = array();
    $pindex = max(1, intval($_GPC['page']));
    $psize = max(20, intval($_GPC['psize']));
    $condition = " uniacid=:uniacid and type=:type";
    $params[':uniacid'] = $_SESSION['appuniacid'] ? $_SESSION['appuniacid'] : 2;
    $params[':type'] = intval($_GPC['type']);
    $list = pdo_fetchall("select * from" . tablename('xcommunity_express_save_log') . " where $condition order by createtime asc LIMIT " . ($pindex - 1) * $psize . ',' . $psize, $params);
    foreach ($list as $k => $v) {
        $list[$k]['createtime'] = date('Y-m-d H:i', $v['createtime']);
    }
    util::send_result($list);
}
elseif ($op == 'outgrab') {
//    $codes = $_GPC['waybillcode'];
//    $codes = explode(',', $codes);
//    $code = $codes[1];
    $code = $_GPC['waybillcode'];
    $pick_code = substr($code, -6);
    $parcel = pdo_get('xcommunity_express_parcel', array('waybillcode' => $code));
    $log = pdo_get('xcommunity_express_save', array('waybillcode' => $code));
    if ($parcel) {
        if ($parcel['status'] == 3) {
            pdo_update('xcommunity_express_parcel', array('status' => 4, 'ovetime' => TIMESTAMP), array('waybillcode' => $code));
            pdo_update('xcommunity_express_save', array('status' => 4, 'ovetime' => TIMESTAMP), array('waybillcode' => $code));
            $saveid = pdo_insertid();
            $dat = array(
                'uniacid'     => $uniacid,
                'saveid'      => $log['id'],
                'waybillcode' => $code,
                'type'        => 2,
                'createtime'  => TIMESTAMP
            );
            pdo_insert('xcommunity_express_save_log', $dat);
            util::send_result();
        }
        else {
            util::send_error(-1, '该物件出库错误');
        }
    }
    else {
        util::send_error(-1, '快递不存在');
    }
}