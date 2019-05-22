<?php
/**
 * Created by njlanniu.com.
 * User: 蓝牛科技
 * Time: 2017/10/12 下午11:26
 */
global $_W, $_GPC;
$op = in_array(trim($_GPC['op']), array('add', 'list', 'del', 'set', 'setting', 'parking', 'manage', 'record', 'cardtypes', 'powers', 'temp')) ? trim($_GPC['op']) : 'list';
if ($op == 'set') {
    $settings = pdo_get('xcommunity_stop_park_set', array('uniacid' => $_W['uniacid']), array());
    if (checksubmit('submit')) {
        ////字段验证, 并获得正确的数据$dat
        $dat = array(
            'status'      => intval($_GPC['status']),
            'expire_num'  => $_GPC['expire_num'],
            'arrears_num' => $_GPC['arrears_num'],
            'remind_num'  => $_GPC['remind_num'],
            'uniacid'     => $_W['uniacid'],
            'tjtime'      => intval($_GPC['tjtime']),
            'car_tpl'     => $_GPC['car_tpl']
        );
        if (empty($settings)) {
            pdo_insert('xcommunity_stop_park_set', $dat);
        }
        else {
            pdo_update('xcommunity_stop_park_set', $dat, array('id' => $settings['id']));
        }
        itoast('提交成功', referer(), 'success');
    }

    include $this->template('web/plugin/zhpark/set');
}
elseif ($op == 'setting') {
    $p = in_array(trim($_GPC['p']), array('add', 'list', 'delete')) ? trim($_GPC['p']) : 'list';
    if ($p == 'add') {
        $id = intval($_GPC['id']);
        if ($id) {
            $item = pdo_get('xcommunity_stop_park_setting', array('id' => $id), array());
        }
        if ($_W['isajax']) {
            $data = array(
                'uniacid'       => $_W['uniacid'],
                'title'         => trim($_GPC['title']),
                'company_id'    => trim($_GPC['company_id']),
                'sign_key'      => trim($_GPC['sign_key']),
                'access_secret' => trim($_GPC['access_secret']),
                'createtime'    => TIMESTAMP
            );
            if ($id) {
                pdo_update('xcommunity_stop_park_setting', $data, array('id' => $id));
            }
            else {
                pdo_insert('xcommunity_stop_park_setting', $data);
            }
            echo json_encode(array('status'=>1));exit();
        }
        include $this->template('web/plugin/zhpark/setting_add');
    }
    elseif ($p == 'list') {
        $pindex = max(1, intval($_GPC['page']));
        $psize = 20;
        $condition = 'uniacid =:uniacid';
        $params[':uniacid'] = $_W['uniacid'];
        if ($_GPC['keyword']) {
            $condition .= " and title like '%{$_GPC['keyword']}%'";
        }
        $list = pdo_fetchall("SELECT * FROM " . tablename("xcommunity_stop_park_setting") . " WHERE  $condition ORDER BY  id DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize, $params);
        $total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename("xcommunity_stop_park_setting") . " WHERE $condition", $params);
        $pager = pagination($total, $pindex, $psize);
        include $this->template('web/plugin/zhpark/setting_list');
    }
    elseif ($p == 'delete') {
        $id = intval($_GPC['id']);
        if (empty($id)) {
            itoast('缺少参数', referer(), 'error');
            exit();
        }
        $item = pdo_get('xcommunity_stop_park_setting', array('id' => $id), array('id'));
        if ($item) {
            if (pdo_delete('xcommunity_stop_park_setting', array('id' => $id))) {
                itoast('删除成功', referer(), 'success');
                exit();
            }

        }

    }
}
elseif ($op == 'parking') {
    $p = in_array(trim($_GPC['p']), array('cloud', 'list', 'add')) ? trim($_GPC['p']) : 'list';
    if ($p == 'cloud') {
        $companies = pdo_getall('xcommunity_stop_park_setting', array('uniacid' => $_W['uniacid']), array());
        if (empty($companies)) {
            itoast('请先配置停车场', $this->createWebUrl('zhpark', array('op' => 'setting')), 'error');
            exit();
        }

        foreach ($companies as $key => $val) {
            $token = getAccessToken($val['company_id'], $val['access_secret'], $val['sign_key']);
            $parks = getParks($val['sign_key'], $token);
            foreach ($parks as $k => $v) {
                $park = pdo_get('xcommunity_stop_park', array('park_id' => $v['park_id']), array('id'));
                $data = array(
                    'uniacid'       => $_W['uniacid'],
                    'parkid'        => $val['id'],
                    'park_id'       => $v['park_id'],
                    'park_name'     => $v['park_name'],
                    'city'          => $v['city'],
                    'total_spaces'  => $v['total_spaces'],
                    'remain_spaces' => $v['remain_spaces'],
                    'company_id'    => $val['company_id'],
                    'createtime'    => TIMESTAMP,
                );
                if ($park) {
                    pdo_update('xcommunity_stop_park', $data, array('id' => $park['id']));
                }
                else {
                    pdo_insert('xcommunity_stop_park', $data);
                }
            }
        }
        exit();
        itoast('同步停车场成功', $this->createWebUrl('zhpark', array('op' => 'parking')), 'success');
        exit();
    }
    elseif ($p == 'list') {
        $pindex = max(1, intval($_GPC['page']));
        $psize = 20;
        $condition = 'uniacid =:uniacid';
        $params[':uniacid'] = $_W['uniacid'];
        if ($_GPC['keyword']) {
            $condition .= " and park_name like '%{$_GPC['keyword']}%'";
        }
        $list = pdo_fetchall("SELECT * FROM " . tablename("xcommunity_stop_park") . " WHERE  $condition ORDER BY  id DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize, $params);
        $total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename("xcommunity_stop_park") . " WHERE $condition", $params);
        $pager = pagination($total, $pindex, $psize);
        include $this->template('web/plugin/zhpark/parking');
    }
    elseif ($p == 'add') {
        if (checksubmit('submit')) {

        }
        include $this->template('web/plugin/zhpark/cars');
    }
}
elseif ($op == 'manage') {
    $p = in_array(trim($_GPC['p']), array('cloud', 'list', 'update', 'del')) ? trim($_GPC['p']) : 'list';
    if ($p == 'cloud') {
        $parks = pdo_getall('xcommunity_stop_park', array('uniacid' => $_W['uniacid']), array());
        if (empty($parks)) {
            itoast('请先同步停车场信息', $this->createWebUrl('zhpark', array('op' => 'parking')), 'error');
            exit();
        }
        foreach ($parks as $key => $val) {
            $setting = pdo_get('xcommunity_stop_park_setting', array('company_id' => $val['company_id']), array());
            $token = getAccessToken($val['company_id'], $setting['access_secret'], $setting['sign_key']);
            $time = '2017-09-01 18:20:30';
            $last_time = $val['last_time'] ? $val['last_time'] : $time;
            $cars = getAllCars($setting['sign_key'], $val['park_id'], $token, $last_time);
            pdo_update('xcommunity_stop_park', array('last_time' => $last_time), array('id' => $val['id']));
            if ($cars) {
                foreach ($cars as $k => $v) {
                    $car = pdo_get('xcommunity_stop_park_cars', array('card_no' => $v['card_no']), array('id'));
                    $data = array(
                        'uniacid'     => $_W['uniacid'],
                        'id_card'     => $val['id_card'],
                        'card_no'     => $v['card_no'],
                        'card_name'   => $v['card_name'],
                        'issued_by'   => $v['issued_by'],
                        'issued_date' => strtotime($v['issued_date']),
                        'renew_date'  => strtotime($v['renew_date']),
                        'start_date'  => strtotime($v['start_date']),
                        'valid_date'  => strtotime($v['valid_date']),
                        'deposit'     => $v['deposit'],
                        'month_fee'   => $v['month_fee'],
                        'car_no'      => $v['car_no'],
                        'car_type'    => $v['car_type'],
                        'car_pos'     => $v['car_pos'],
                        'realname'    => $v['name'],
                        'address'     => $v['address'],
                        'phone'       => $v['phone'],
                        'mobile'      => $v['mobile'],
                        'remark'      => $v['remark'],
                        'card_status' => $v['card_status'],
                        'park_id'     => $val['id'],
                        'createtime'  => TIMESTAMP,
                        'status'      => 1
                    );
                    if ($car) {
                        pdo_update('xcommunity_stop_park_cars', $data, array('id' => $car['id']));
                    }
                    else {
                        pdo_insert('xcommunity_stop_park_cars', $data);
                    }
                }
            }

        }
        itoast('同步月租车成功', $this->createWebUrl('zhpark', array('op' => 'manage')), 'success');
        exit();

    }
    elseif ($p == 'list') {
        $parks = pdo_fetchall("SELECT * FROM " . tablename("xcommunity_stop_park") . " WHERE uniacid=:uniacid ", array(':uniacid' => $_W['uniacid']));
        $pindex = max(1, intval($_GPC['page']));
        $psize = 20;
        $condition = 'uniacid =:uniacid';
        $params[':uniacid'] = $_W['uniacid'];
        if ($_GPC['keyword']) {
            $condition .= " and (car_no like '%{$_GPC['keyword']}%' or realname like '%{$_GPC['keyword']}%')";
        }
        $park_id = trim($_GPC['park_id']);
        if ($park_id) {
            $condition .= " and park_id =:park_id";
            $params[':park_id'] = $park_id;
        }
        $list = pdo_fetchall("SELECT * FROM " . tablename("xcommunity_stop_park_cars") . " WHERE  $condition ORDER BY  id DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize, $params);

        $total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename("xcommunity_stop_park_cars") . " WHERE $condition", $params);
        $pager = pagination($total, $pindex, $psize);
        include $this->template('web/plugin/zhpark/manage');
    }
    elseif ($p == 'update') {
        $carid = intval($_GPC['id']);
        if ($carid) {
            $car = pdo_get('xcommunity_stop_park_cars', array('id' => $carid), array());
        }
        if (checksubmit('submit')) {
            $sql = "select t1.park_name,t2.company_id,t2.access_secret,t2.sign_key,t1.park_id from" . tablename('xcommunity_stop_park') . "t1 left join" . tablename('xcommunity_stop_park_setting') . "t2 on t1.parkid=t2.id where t1.id=:id";
            $park = pdo_fetch($sql, array(':id' => $car['park_id']));

            $end_date = $car['valid_date'] + 86400 * 30 * $_GPC['month'];
            $data = array(
                'uniacid'     => $_W['uniacid'],
                'uid'         => '',
                'parkid'      => $car['park_id'],
                'carid'       => $carid,
                'park_id'     => $park['park_id'],
                'park_serial' => 'LN' . date('YmdHi') . random(10, 1),
                'car_no'      => $car['car_no'],
                'begin_date'  => $car['valid_date'],
                'end_date'    => $car['valid_date'] + 86400 * 30 * $_GPC['month'],
                'num'         => $_GPC['month'],
                'pay_fee'     => $car['month_fee'] * $_GPC['month'],
                'toll_date'   => TIMESTAMP,
                'toll_by'     => $park['park_name'],
                'remark'      => '线下续费',
                'enable'      => 2,//线下延期,
                'status'      => 1,

            );
            if (pdo_insert('xcommunity_stop_park_order', $data)) {
                $id = pdo_insertid();
                $token = getAccessToken($park['company_id'], $park['access_secret'], $park['sign_key']);
                $result = updateCar($park['sign_key'], $park['park_id'], $token, $data['park_serial'], $data['car_no'], date('Y-m-d', $data['begin_date']), date('Y-m-d', $data['end_date']), $data['pay_fee'], date('Y-m-d H:i:s', $data['toll_date']), $data['toll_by'], $data['remark']);
                if (empty($result['resp_code'])) {
                    //1 同步正常 0未同步成功
                    if (pdo_update('xcommunity_stop_park_order', array('code' => 1), array('id' => $id))) {
                        pdo_update('xcommunity_stop_park_cars', array('valid_date' => $data['end_date']), array('id' => $carid));
                        itoast('延期成功', $this->createWebUrl('zhpark', array('op' => 'manage')), 'success');
                    }
                }
            }
        }
        include $this->template('web/plugin/zhpark/manage_update');
    }
    elseif ($p == 'del') {
        $id = intval($_GPC['id']);
        if (empty($id)) {
            itoast('缺少参数');
            exit();
        }
        if (pdo_delete('xcommunity_stop_park_cars', array('id' => $id))) {
            itoast('删除成功', referer(), 'error');
        }
    }
}
elseif ($op == 'record') {
    $p = in_array(trim($_GPC['p']), array('cloud', 'list')) ? trim($_GPC['p']) : 'list';
    if ($p == 'cloud') {
        $order = pdo_get('xcommunity_stop_park_order', array('id' => intval($_GPC['id'])), array());
        if ($order['parkid']) {
            $sql = "select t2.company_id,t2.access_secret,t2.sign_key,t1.park_id from" . tablename('xcommunity_stop_park') . "t1 left join" . tablename('xcommunity_stop_park_setting') . "t2 on t1.parkid=t2.id where t1.id=:id";
            $park = pdo_fetch($sql, array(':id' => $order['parkid']));
            $token = getAccessToken($park['company_id'], $park['access_secret'], $park['sign_key']);
            $result = updateCar($park['sign_key'], $park['park_id'], $token, $order['park_serial'], $order['car_no'], date('Y-m-d', $order['begin_date']), date('Y-m-d', $order['end_date']), $order['pay_fee'], date('Y-m-d H:i:s', $order['toll_date']), $order['toll_by'], $order['remark']);
            if (empty($result['resp_code'])) {
                //1 同步正常 0未同步成功
                if (pdo_update('xcommunity_stop_park_order', array('code' => 1), array('id' => $order['id']))) {
                    itoast('同步延期成功', $this->createWebUrl('zhpark', array('op' => 'record')), 'success');
                }
            }
        }
    }
    elseif ($p == 'list') {
        $pindex = max(1, intval($_GPC['page']));
        $psize = 20;
        $condition = 'uniacid =:uniacid';
        $params[':uniacid'] = $_W['uniacid'];
        if ($_GPC['keyword']) {
            $condition .= " and park_serial=:park_serial";
            $params[':park_serial'] = trim($_GPC['keyword']);
        }
        if ($_GPC['enable']) {
            $condition .= " and enable=:enable";
            $params[':enable'] = intval($_GPC['enable']);
        }
        $list = pdo_fetchall("SELECT * FROM " . tablename("xcommunity_stop_park_order") . " WHERE  $condition ORDER BY  id DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize, $params);
        $total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename("xcommunity_stop_park_order") . " WHERE $condition", $params);
        $pager = pagination($total, $pindex, $psize);
        include $this->template('web/plugin/zhpark/record');
    }
}
elseif ($op == 'cardtypes') {
    $id = intval($_GPC['id']);
    $park = pdo_fetch("select t1.*,t2.company_id,t2.access_secret,t2.sign_key from" . tablename('xcommunity_stop_park') . "t1 left join" . tablename('xcommunity_stop_park_setting') . "t2 on t1.parkid=t2.id where t1.id=:id", array(':id' => $id));
    $token = getAccessToken($park['company_id'], $park['access_secret'], $park['sign_key']);
    $cardtypes = getCardTypes($park['park_id'], $park['sign_key'], $token);
    if (empty($cardtypes)) {
        itoast('该车场尚未上传月租类型', referer(), 'success');
        exit();
    }
    foreach ($cardtypes as $k => $v) {
        $item = pdo_get('xcommunity_stop_park_cardtypes', array('parkid' => $park['id'], 'type_code' => $v['type_code']), array('id'));
        $data = array(
            'uniacid'     => $_W['uniacid'],
            'park_id'     => $v['park_id'],
            'type_code'   => $v['type_code'],
            'type_name'   => $v['type_name'],
            'year_price'  => $v['year_price'],
            'month_price' => $v['month_price'],
            'day_price'   => $v['day_price'],
            'parkid'      => $park['id']
        );
        if (empty($item)) {
            pdo_insert('xcommunity_stop_park_cardtypes', $data);
        }
        else {
            pdo_update('xcommunity_stop_park_cardtypes', $data, array('id' => $item['id']));

        }

    }
    itoast('更新成功', referer(), 'success');
}
elseif ($op == 'powers') {
    $id = intval($_GPC['id']);
    $park = pdo_fetch("select t1.*,t2.company_id,t2.access_secret,t2.sign_key from" . tablename('xcommunity_stop_park') . "t1 left join" . tablename('xcommunity_stop_park_setting') . "t2 on t1.parkid=t2.id where t1.id=:id", array(':id' => $id));
    $token = getAccessToken($park['company_id'], $park['access_secret'], $park['sign_key']);
    $powers = getPowers($park['park_id'], $park['sign_key'], $token);
    if (empty($powers)) {
        itoast('该车场尚未上传出入权限', referer(), 'success');
        exit();
    }
    foreach ($powers as $k => $v) {
        $power = pdo_get('xcommunity_stop_park_powers', array('power_id' => $v['power_id']), array('id'));
        if ($v['power_name'] != '默认权限'&&$v['power_id']!=1) {
            $data = array(
                'uniacid'    => $_W['uniacid'],
                'park_id'    => $v['park_id'],
                'power_name' => $v['power_name'],
                'power_id'   => $v['power_id'],
                'parkid'     => $park['id']
            );

            if ($power) {
                pdo_update('xcommunity_stop_park_powers', $data, array('id' => $power['id']));
            }
            else {
                pdo_insert('xcommunity_stop_park_powers', $data);
            }
        }
    }
    itoast('更新成功', referer(), 'success');
}
elseif ($op == 'add') {
    $id = intval($_GPC['id']);
    $park = pdo_fetch("select t1.*,t2.company_id,t2.access_secret,t2.sign_key from" . tablename('xcommunity_stop_park') . "t1 left join" . tablename('xcommunity_stop_park_setting') . "t2 on t1.parkid=t2.id where t1.id=:id", array(':id' => $id));
    $token = getAccessToken($park['company_id'], $park['access_secret'], $park['sign_key']);
    $cardtypes = pdo_getall('xcommunity_stop_park_cardtypes', array('parkid' => $id), array());
    $powers = pdo_getall('xcommunity_stop_park_powers', array('parkid' => $id), array());
    if ($_W['isajax']) {
        $item = pdo_get('xcommunity_stop_park_cars', array('car_no' => trim($_GPC['car_no']), 'park_id' => $id), array('id'));
        if (empty($item)) {
            $cardtype = pdo_get('xcommunity_stop_park_cardtypes', array('id' => $_GPC['card_type_id']), array());
            load()->func('communication');
            $url = 'https://www.parkthe.com//xyyapi/private/park/car/new';
            $nonce = getRandom();
            $createtime = TIMESTAMP;
            $data = array(
                'park_id'     => $park['park_id'],
                'card_no'     => getNum(),
                'card_type'   => $cardtype['type_name'],
                'issued_by'   => $_GPC['issued_by'],
                'issued_date' => date('Y-m-d H:i:s', TIMESTAMP),
                'month_fee'   => $cardtype['month_price'],
                'car_no'      => trim($_GPC['car_no']),
                'name'        => $_GPC['realname'],
                'mobile'      => $_GPC['mobile'],
                'power_id'    => intval($_GPC['power_id']) ? intval($_GPC['power_id']) : 1,
                'address'     => trim($_GPC['address'])
            );

            $d = base64_encode(json_encode($data));
            $content = array(
                'rid'        => 'ab1000014108' . $createtime,
                'time_mills' => strval($createtime),
                'nonce'      => $nonce,
                'token'      => $token,
                'key'        => $park['sign_key'],
                'data'       => $d
            );

            asort($content);

            $content = implode(',', $content);

            $sign = md5($content);

            $t = array(
                'rid'        => 'ab1000014108' . $createtime,
                'time_mills' => $createtime,
                'nonce'      => $nonce,
                'sign'       => $sign,
                'token'      => $token,
                'data'       => $d
            );

            $result = ihttp_post($url, $t);
            $result = json_decode($result['content'], true);
            if (empty($result['resp_code'])) {
//            itoast('新增成功',referer(),'success');
                $cars = getOneCar($park['sign_key'], $park['park_id'], $token, $data['car_no']);
//                print_r($cars);exit();
                if (empty($cars)) {
                    echo json_encode(array('content'=>'网络异常，新增车牌失败，请重新操作'));exit();
                }
                //更新到本地
                $dat = array(
                    'uniacid'     => $_W['uniacid'],
                    'id_card'     => $cars['id_card'],
                    'card_no'     => $cars['card_no'],
                    'card_name'   => $cars['card_name'],
                    'issued_by'   => $cars['issued_by'],
                    'issued_date' => strtotime($cars['issued_date']),
                    'renew_date'  => strtotime($cars['renew_date']),
                    'start_date'  => strtotime($cars['start_date']),
                    'valid_date'  => strtotime($cars['valid_date']),
                    'deposit'     => $cars['deposit'],
                    'month_fee'   => $cars['month_fee'],
                    'car_no'      => $cars['car_no'],
                    'car_type'    => $cars['car_type'],
                    'car_pos'     => $cars['car_pos'],
                    'realname'    => $cars['name'],
                    'address'     => $cars['address'],
                    'phone'       => $cars['phone'],
                    'mobile'      => $cars['mobile'],
                    'remark'      => $cars['remark'],
                    'card_status' => $cars['card_status'],
                    'park_id'     => $park['id'],
                    'createtime'  => TIMESTAMP,
                    'status'      => 1,
                    'address'     => trim($_GPC['address'])
                );
                pdo_insert('xcommunity_stop_park_cars', $dat);
                $carid = pdo_insertid();

                //续费
                $begin_date = $_GPC['birth']['start'];
                $end_date = $_GPC['birth']['end'];
                if (!empty($begin_date) && $begin_date == $end_date) {
                    $endtime = strtotime($end_date) + 86400;
                    $end_date = date('Y-m-d', $endtime);
                }
                $dat1 = array(
                    'uniacid'     => $_W['uniacid'],
                    'uid'         => '',
                    'parkid'      => $cars['park_id'],
                    'carid'       => $carid,
                    'park_id'     => $park['park_id'],
                    'park_serial' => 'LN' . date('YmdHi') . random(10, 1),
                    'car_no'      => $dat['car_no'],
                    'begin_date'  => strtotime($begin_date),
                    'end_date'    => strtotime($end_date),
                    'num'         => '1',
                    'pay_fee'     => $cars['month_fee'] * 1,
                    'toll_date'   => TIMESTAMP,
                    'toll_by'     => $park['park_name'],
                    'remark'      => '线下续费',
                    'enable'      => 2,//线下延期,
                    'status'      => 1,

                );
                if (pdo_insert('xcommunity_stop_park_order', $dat1)) {
                    $orderid = pdo_insertid();
                    //续费
                    $result = updateCar($park['sign_key'], $park['park_id'], $token, $dat1['park_serial'], $dat1['car_no'], $begin_date, $end_date, $dat1['pay_fee'], date('Y-m-d H:i:s', $dat1['toll_date']), $dat1['toll_by'], $dat1['remark']);
                    if (empty($result['resp_code'])) {
                        //1 同步正常 0未同步成功
                        if (pdo_update('xcommunity_stop_park_order', array('code' => 1), array('id' => $orderid))) {
                            pdo_update('xcommunity_stop_park_cars', array('valid_date' => $dat1['end_date'], 'status' => 1), array('id' => $carid));
                            echo json_encode(array('status'=>1));exit();
                        }
                    }
                }
            }
            else {
//                itoast($result['resp_msg'], referer(), 'error');
                echo json_encode(array('content'=>$result['resp_msg']));exit();
            }
        }
        else {
//            itoast('车牌号已存在，请勿重复添加', referer(), 'error');
            echo json_encode(array('content'=>'车牌号已存在，请勿重复添加'));exit();
        }

    }
    include $this->template('web/plugin/zhpark/cars');
}
elseif ($op == 'temp') {
    $parks = pdo_getall('xcommunity_stop_park', array('uniacid' => $_W['uniacid']), array());
    if ($_GPC['cloud'] == '同步缴费记录') {
        $id = intval($_GPC['park_id']);
        $begin_time = date('Y-m-d H:i:s', strtotime($_GPC['birth']['start']));
        $end_time = date('Y-m-d H:i:s', strtotime($_GPC['birth']['end']));
        $park = pdo_fetch("select t1.*,t2.company_id,t2.access_secret,t2.sign_key from" . tablename('xcommunity_stop_park') . "t1 left join" . tablename('xcommunity_stop_park_setting') . "t2 on t1.parkid=t2.id where t1.id=:id", array(':id' => $id));
        $token = getAccessToken($park['company_id'], $park['access_secret'], $park['sign_key']);
        $temps = getTemp($park['park_id'], $park['sign_key'], $token, $begin_time, $end_time);
        foreach ($temps as $k => $v) {
            $data = array(
                'parkid'        => $id,
                'uniacid'       => $_W['uniacid'],
                'park_id'       => $v['park_id'],
                'park_serial'   => $v['park_serial'],
                'car_no'        => $v['car_no'],
                'car_owner'     => $v['car_owner'],
                'in_time'       => strtotime($v['in_time']),
                'out_time'      => strtotime($v['out_time']),
                'should_charge' => $v['should_charge'],
                'charge_fee'    => $v['charge_fee'],
                'online_charge' => $v['online_charge'],
                'free_charge'   => $v['free_charge'],
                'charged_by'    => $v['charged_by'],
                'toll_date'     => strtotime($v['charged_date'])
            );
            $temp = pdo_get('xcommunity_stop_park_temp', array('park_id' => $data['park_id']), array('id'));
            if (empty($temp)) {
                pdo_insert('xcommunity_stop_park_temp', $data);
            }
        }
        itoast('同步临时车缴费记录成功', $this->createWebUrl('zhpark', array('op' => 'temp')), 'success');
    }
    $pindex = max(1, intval($_GPC['page']));
    $psize = 20;
    $condition = 't1.uniacid =:uniacid';
    $params[':uniacid'] = $_W['uniacid'];
    $starttime = strtotime($_GPC['birth']['start']);
    $endtime = strtotime($_GPC['birth']['end']);
    if (!empty($starttime)) {
        $endtime = $endtime + 86400 - 1;
    }
    if ($starttime && $endtime) {
        $condition .= " AND t1.toll_date between '{$starttime}' and '{$endtime}'";
    }
    $park_id = trim($_GPC['park_id']);
    if ($park_id) {
        $condition .= " and t1.parkid =:park_id";
        $params[':park_id'] = $park_id;
    }
    $list = pdo_fetchall("SELECT t1.*,t2.park_name FROM " . tablename("xcommunity_stop_park_temp") . "t1 left join" . tablename('xcommunity_stop_park') . "t2 on t1.parkid=t2.id WHERE  $condition ORDER BY  t1.toll_date DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize, $params);

    $total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename("xcommunity_stop_park_temp") . "t1 left join" . tablename('xcommunity_stop_park') . "t2 on t1.parkid=t2.id WHERE $condition", $params);
    $pager = pagination($total, $pindex, $psize);
    include $this->template('web/plugin/zhpark/temp');
}