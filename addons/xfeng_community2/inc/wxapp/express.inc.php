<?php
global $_GPC, $_W;
$ops = array('list', 'collect', 'companys', 'types', 'address', 'sendadd', 'price', 'parcel', 'my', 'grey', 'getAllUserInfo', 'getUserInfo', 'addAddress');
$op = trim($_GPC['op']);
$p = !empty($_GPC['p']) ? $_GPC['p'] : 'list';
if (!in_array($op, $ops)) {
    util::send_error(-1, '参数错误');
}
$uid = $_W['member']['uid'];
if ($op == 'list') {

}
elseif ($op == 'collect') {
    if ($p == 'list') {

    }
    elseif ($p == 'detail') {
        $id = intval($_GPC['id']);

        $sql = "select t1.*,t2.realname,t2.phone as mobile,t2.address as addr,t2.address as addr_detail from" . tablename('xcommunity_express_parcel') . "t1 left join" . tablename('xcommunity_express_linkman') . "t2 on t1.uid=t2.uid where t1.id=:id";
        $item = pdo_fetch($sql, array(':id' => $id));

        $item['createtime'] = date('Y-m-d H:i', $item['createtime']);
        if ($item['status'] == 1) {
            $item['status'] = '未发货';
        }
        elseif ($item['status'] == 2) {
            $item['status'] = '已发货';
        }
        elseif ($item['status'] == 3) {
            $item['status'] = '已代收';
        }
        elseif ($item['status'] == 4) {
            $item['status'] = '已取件';
        }
        util::send_result($item);
    }
}
elseif ($op == 'companys') {
    $uniacid = $_W['uniacid'];
    $list = pdo_getall('xcommunity_express_company', array('uniacid' => $uniacid), array(), '', 'createtime desc');
    foreach ($list as $k => $v) {
        $list[$k]['key'] = $v['id'];
        $list[$k]['value'] = $v['company'];
    }
    util::send_result($list);
}
elseif ($op == 'types') {
    $uniacid = $_W['uniacid'];
    $list = pdo_getall('xcommunity_express_type', array('uniacid' => $uniacid), array());
    foreach ($list as $k => $v) {
        $list[$k]['key'] = $v['type'];
        $list[$k]['value'] = $v['type'];
    }
    util::send_result($list);
}
elseif ($op == 'address') {
    $status = intval($_GPC['status']);
    $openid = $_W['openid'];
    if ($p == 'add') {
        $data = array(
            'openid'         => $openid,
            'uid'            => $uid,
            'realname'       => $_GPC['realname'],
            'phone'          => $_GPC['mobile'],
            'address'        => $_GPC['address'],
            'address_detail' => $_GPC['addr'],
            'createtime'     => TIMESTAMP,
            'status'         => $status,
        );
        $item = pdo_get('xcommunity_express_linkman', array('uid' => $uid, 'status' => $status));
        if (!empty($item)) {
            $result = pdo_update('xcommunity_express_linkman', $data, array('id' => $item['id']));
        }
        else {
            $data['uniacid'] = $_W['uniacid'];
            $result = pdo_insert('xcommunity_express_linkman', $data);
        }
        util::send_result();
    }
    elseif ($p == 'detail') {
        $list = pdo_getall('xcommunity_express_linkman', array('uniacid' => $_W['uniacid'], 'uid' => $uid));
        $data = array();
        foreach ($list as $k => $v) {
            if ($v['status'] == 1) {
                $data[1] = $v;
                $data[1]['addressvalue'] = explode(' ', $v['address']);
            }
            elseif ($v['status'] == 2) {
                $data[2] = $v;
                $data[2]['addressvalue'] = explode(' ', $v['address']);
            }
        }
        util::send_result($data);
    }
}
elseif ($op == 'sendadd') {
    $companyid = intval($_GPC['companyid']);
    if($companyid){
        $company = pdo_get('xcommunity_express_company',array('id'=>$companyid));
        $sendData = array(
            'uniacid'        => $_W['uniacid'],
            'openid'         => $_W['openid'],
            'uid'            => $uid,
            'name'           => $_GPC['name'],//收件人姓名
            'phone'          => $_GPC['tel'],//收件人电话
            'address'        => $_GPC['address'],//收件人地址
            'address_detail' => $_GPC['fullAddress'],//收件人详细地址
            'company'        => $company['company'],//物流公司
            'goodstatus'     => $_GPC['goodstatus'], //寄件类型
            'price'          => $_GPC['price'],//预估价格
            'remark'         => $_GPC['remark'], //备注
            'status'         => 1,//物流状态
            'weight'         => $_GPC['weight'],//重量
            'createtime'     => TIMESTAMP,//创建时间
        );
        $result = pdo_insert('xcommunity_express_parcel', $sendData);
        $id = pdo_insertid();
        $linkman = pdo_get('xcommunity_express_linkman', array('uniacid' => $_W['uniacid'], 'openid' => $_W['openid']));
        $time = TIMESTAMP;
        if (set('t27')) {
            $content = array(
                'first'    => array(
                    'value' => '有客户需要寄快递',
                ),
                'keyword1' => array(
                    'value' => $linkman['realname'],
                ),
                'keyword2' => array(
                    'value' => $linkman['phone'],
                ),
                'keyword3' => array(
                    'value' => $linkman['address'] . $linkman['address_detail'],
                ),
                'keyword4' => array(
                    'value' => date('Y-m-d H:i', $time),
                ),
                'remark'   => array(
                    'value' => '' . $_GPC['goodstatus'] . $_GPC['weight'] . 'Kg,备注：' . $_GPC['remark'],
                ),
            );
            $tplid = set('t28');
            $url = $_W['siteroot'] . "app/index.php?i={$_W['uniacid']}&c=entry&id={$id}&do=express&op=grey&p=detail&m=" . $this->module['name'];
            $company = pdo_get('xcommunity_express_company', array('uniacid' => $_W['uniacid'], 'company' => $_GPC['company']));
            $collecting = pdo_getall('xcommunity_express_collecting', array('uniacid' => $_W['uniacid'], 'companyid' => $company['id']));
            foreach ($collecting as $k => $v) {
                $res = sendTplNotice($v['openid'], $tplid, $content, $url, '');
            }
        }
        util::send_result();
    }

}
elseif ($op == 'price') {
//    $linkman = pdo_get('xcommunity_express_linkman', array('uniacid' => $_W['uniacid'], 'uid' => $uid));
//    $address = explode(" ", $linkman['area']);
//    $sendaddress = $address[0];
//    $d = $_GPC['address'];
//    $addr = explode(" ", $d);
//    $arraddress = $addr[0];
    $sender_area = explode(" ", trim($_GPC['sender_area']));
    $sendaddress = $sender_area[0];
    $revice_area = explode(" ", trim($_GPC['revice_area']));
    $receiveaddress = $revice_area[0];
//    $company = pdo_get('xcommunity_express_company', array('company' => $_GPC['company']));
//
//    $item = pdo_get('xcommunity_express_piecework', array('mailaddress' => $sendaddress, 'receiveaddress' => $receiveaddress, 'companyid' => $company['id']));
    $condition= " t1.mailaddress=:mailaddress and t1.receiveaddress=:receiveaddress and t1.companyid=:companyid";
    $params[':mailaddress']=$sendaddress;
    $params[':receiveaddress']=$receiveaddress;
    $params[':companyid']=intval($_GPC['companyid']);

    $sql = "select * from".tablename('xcommunity_express_piecework')."t1 left join".tablename('xcommunity_express_company')."t2 on t1.companyid=t2.id where $condition";
    $item = pdo_fetch($sql,$params);
    if ($item) {
//        if ($_GPC['number'] <= 1) {
//            util::send_result(array('price' => $price['price']));
//        }
//        else {
//            $i = ($_GPC['number'] - 1) * $price['overprice'] + $price['price'];
//            util::send_result(array('price' => $i));
//        }
        util::send_result(array('price' => $item['price'], 'overprice' => $item['overprice']));
    }
    else {
        util::send_result(array('price' => 0));
    }
}
elseif ($op == 'parcel') {
    $linkman = pdo_get('xcommunity_express_linkman', array('uid' => $uid));
    $list = array();
    $pindex = max(1, intval($_GPC['page']));
    $psize = max(20, intval($_GPC['psize']));
    $condition = " uniacid=:uniacid and phone=:phone and status in(3,4)";
    $params[':uniacid'] = $_W['uniacid'];
    $params[':phone'] = $linkman['phone'];
    $list = pdo_fetchall("select * from" . tablename('xcommunity_express_parcel') . " where $condition order by status asc LIMIT " . ($pindex - 1) * $psize . ',' . $psize, $params);
    foreach ($list as $k => $v) {
        $company = pdo_get('xcommunity_express_company', array('company' => $v['company']));
        $collecter = pdo_get('xcommunity_express_collecting', array('uniacid' => $_W['uniacid'], 'openid' => $v['collecter']));
        $list[$k]['logo'] = tomedia($company['logo']);
        $list[$k]['collectAddress'] = $collecter['name'];
        $list[$k]['collectTel'] = $collecter['mobile'];
        $list[$k]['overtime'] = date('Y-m-d H:i', $v['overtime']);
        $list[$k]['telurl'] = "tel:" . $v['collectTel'];
    }
    util::send_result($list);
}
elseif ($op == 'my') {
    $type = intval($_GPC['type']);
    if ($type == 1) {
        //我的快递
        $pindex = max(1, intval($_GPC['page']));
        $psize = max(20, intval($_GPC['psize']));
        $condition = " uniacid=:uniacid and uid=:uid";
        $params[':uniacid'] = $_W['uniacid'];
        $params[':uid'] = $uid;
        $sql = "select * from" . tablename('xcommunity_express_parcel') . "where $condition order by createtime desc LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
        $row = pdo_fetchall($sql, $params);
        foreach ($row as $k => $v) {
            $row[$k]['createtime'] = date('Y-m-d H:i', $v['createtime']);
            //$row[$k]['url'] = $this->createMobileUrl('express', array('op' => 'collect', 'p' => 'detail', 'id' => $v['id']));
            $row[$k]['url'] = $_W['siteroot'] . "app/index.php?i={$_W['uniacid']}&c=entry&id={$v['id']}&op=collect&p=detail&do=express&m=" . $this->module['name'];
            $company = pdo_get('xcommunity_express_company', array('company' => $v['company']));
            $row[$k]['logo'] = tomedia($company['logo']);
        }
        util::send_result($row);
    }
    else {
        $linkman = pdo_get('xcommunity_express_linkman', array('uid' => $uid,'status'=>1));
        $list = array();
        $pindex = max(1, intval($_GPC['page']));
        $psize = max(20, intval($_GPC['psize']));
        $condition = " uniacid=:uniacid and mobile=:mobile ";
        $params[':uniacid'] = $_W['uniacid'];
        $params[':mobile'] = $linkman['phone'];
        $list = pdo_fetchall("select * from" . tablename('xcommunity_express_save') . " where $condition order by status asc LIMIT " . ($pindex - 1) * $psize . ',' . $psize, $params);
        foreach ($list as $k => $v) {
            $company = pdo_get('xcommunity_express_company', array('company' => $v['company']));
            $collecter = pdo_get('xcommunity_express_collecting', array('uniacid' => $_W['uniacid'], 'openid' => $v['collecter']));
            $list[$k]['logo'] = tomedia($company['logo']);
            $list[$k]['collectAddress'] = $collecter['name'];
            $list[$k]['collectTel'] = $collecter['mobile'];
            $list[$k]['overtime'] = date('Y-m-d H:i', $v['overtime']);
            $list[$k]['telurl'] = "tel:" . $v['collectTel'];
        }
        util::send_result($list);
    }
}
elseif ($op == 'grey') {
    $id = $_GPC['id'];
    $codes = $_GPC['waybillcode'];
    $codes = explode(',', $codes);
    $code = intval($codes[1]);
    $status = intval($_GPC['status']);
    if ($code == '') {
        util::send_error(-1, '运单号错误');
    }
    $parcel = pdo_get('xcommunity_express_parcel', array('id' => $id));
    if ($parcel['status'] == 1) {
        if (pdo_update('xcommunity_express_parcel', array('status' => $status, 'waybillcode' => $code, 'price' => $_GPC['price']), array('id' => $id))) {
            $time = TIMESTAMP;
            if (set('t29') && ($status == 2)) {
                $content = array(
                    'first'    => array(
                        'value' => '您的快递已发货',
                    ),
                    'keyword1' => array(
                        'value' => $parcel['goodstatus'] . $parcel['weight'] . 'kg',
                    ),
                    'keyword2' => array(
                        'value' => $time,
                    ),
                    'keyword3' => array(
                        'value' => $parcel['company'],
                    ),
                    'keyword4' => array(
                        'value' => $code,
                    ),
                    'keyword5' => array(
                        'value' => $parcel['address'] . $parcel['address_detail'],
                    ),
                    'remark'   => array(
                        'value' => '注意查看物流信息哟',
                    ),
                );
                $tplid = set('t30');
                $url = $_W['siteroot'] . "app/index.php?i={$_W['uniacid']}&c=entry&id={$id}&do=express&op=collect&p=detail&m=" . $this->module['name'];
                sendTplNotice($parcel['openid'], $tplid, $content, $url, $color = '');
            }
            util::send_result();
        }
    }
}
elseif ($op == 'getAllUserInfo') {
    $status = intval($_GPC['status']) ? intval($_GPC['status']) : 2;
    $list = pdo_getall('xcommunity_express_linkman', array('uniacid' => $_W['uniacid'], 'uid' => $uid));
    $data = array();
    foreach ($list as $k => $v) {
        if ($v['status'] == 1) {
            $data[1] = $v;
            $data[1]['addressvalue'] = explode(' ', $v['address']);
        }
        elseif ($v['status'] == 2) {
            $data[2] = $v;
            $data[2]['addressvalue'] = explode(' ', $v['address']);
        }
    }
    util::send_result($data);
}
elseif ($op == 'addAddress') {
    $id = intval($_GPC['id']);
    $status = intval($_GPC['status']);
    $data = array(
        'uid'        => $uid,
        'realname'   => $_GPC['realname'],
        'phone'      => $_GPC['phone'],
        'address'    => $_GPC['address'],
        'area'       => $_GPC['area'],
        'createtime' => TIMESTAMP,
        'status'     => $status,
    );
//    $item = pdo_get('xcommunity_express_linkman', array('uid' => $uid, 'status' => $status));
    if ($id) {
        $result = pdo_update('xcommunity_express_linkman', $data, array('id' => $id));
    }
    else {
        $data['uniacid'] = $_W['uniacid'];
        $result = pdo_insert('xcommunity_express_linkman', $data);
    }
    util::send_result();
}
elseif ($op == 'getUserInfo') {
    $id = intval($_GPC['id']);
    if (empty($id)) {
        util::send_error(-1, '参数错误');
    }
    $item = pdo_get('xcommunity_express_linkman', array('id' => $id));
    util::send_result($item);
}