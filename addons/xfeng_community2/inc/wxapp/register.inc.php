<?php
/**
 * Created by xiaoqu.
 * User: zhoufeng
 * Time: 2017/12/13 下午9:11
 */
global $_GPC, $_W;
$ops = array('add', 'post', 'register', 'verity', 'veritymobile', 'veritycode', 'room');
$op = trim($_GPC['op']);
if (!in_array($op, $ops)) {
    util::send_error(-1, '参数错误');
}
if(empty($_W['openid'])){
    $info = mc_oauth_userinfo();
    $openid = $info['openid'];
}else{
    $openid = $_W['openid'];
}
$_uid = $_W['member']['uid'] ? $_W['member']['uid'] : mc_openid2uid($openid);
if ($op == 'add') {
    $regionid = intval($_GPC['regionid']) ? intval($_GPC['regionid']) : $_SESSION['community']['regionid'];
    $mobile = trim($_GPC['mobile']);
    $memberid = intval($_GPC['memberid']);
    $status = intval($_GPC['status']);
    $realname = trim($_GPC['realname']);
    if (empty($_W['member']['realname']) || empty($_W['member']['mobile'])) {

//        $r = pdo_update('mc_members', array('realname' => $realname, 'mobile' => $mobile), array('uid' => $_uid));
load()->model('mc');
        $record = array(
            'realname' => $realname,
            'mobile'   => $mobile
        );
        $result = mc_update($_uid, $record);
    }
    if (empty($memberid)) {
        $memberid = model_user::mc_register_region($regionid, $_uid, trim($_GPC['realname']), $mobile, trim($_GPC['license']), trim($_GPC['idcard']), trim($_GPC['gender']));
    }
    else {
        //判断是否开启人工审核
        $verityStatus = set('p18') || set('x1', $regionid) ? 0 : 1;
        $_uid = $_uid ? $_uid : mc_openid2uid($_W['openid']);
        $uid = $uid ? $uid : $_uid;
        pdo_update('xcommunity_member', array('status' => $verityStatus, 'enable' => 1), array('id' => $memberid));
        if (empty($_W['member']['realname']) || empty($_W['member']['mobile'])) {
            pdo_update('mc_members', array('createtime' => TIMESTAMP, 'realname' => $realname, 'mobile' => $mobile), array('uid' => $_uid));
        }
    }
    if ($memberid) {
        //联动注册
        $house = set("p91") || set('x58', $regionid) ? 1 : 0;//1开启选择房号
        if (empty($house) && (set('p89') || set('x57', $regionid))) {
            //验证预先房导入房屋
            if (trim($_GPC['area'])) {
                $area = pdo_get('xcommunity_area', array('title' => trim($_GPC['area']), 'regionid' => $regionid), array('id'));
                if (empty($area)) {
                    $data = array();
                    $data['content'] = '区域不存在，请重新填写';
                    util::send_result($data);
                    exit();
                }
            }
            if (trim($_GPC['build'])) {
                $build = pdo_get('xcommunity_build', array('buildtitle' => trim($_GPC['build']), 'regionid' => $regionid), array('id'));
                if (empty($build)) {
                    $data = array();
                    $data['content'] = '楼宇不存在，请重新填写';
                    util::send_result($data);
                    exit();
                }
            }
            if (trim($_GPC['room'])) {
                $room = pdo_get('xcommunity_member_room', array('room' => trim($_GPC['room']), 'regionid' => $regionid), array('id'));
                if (empty($room)) {
                    $data = array();
                    $data['content'] = '房屋不存在，请重新填写';
                    util::send_result($data);
                    exit();
                }
            }
        }
        $result = model_user::mc_register_address($regionid, trim($_GPC['area']), trim($_GPC['build']), trim($_GPC['unit']), trim($_GPC['room']), $memberid, $mobile, '', $status, $house, trim($_GPC['license']), trim($_GPC['realname']));
        if ($result) {

            $data = array();
            $data['content'] = (set('p18') || set('x1', $regionid)) && !set('p22') && !set('x4', $regionid) ? '您的帐号正在审核中，我们将在24小时内为您开通' : '注册成功';
            util::send_result($data);
        }

    }
}
elseif ($op == 'post') {
    $regionid = trim($_GPC['regionid']) && trim($_GPC['regionid']) != 'undefined' ? trim($_GPC['regionid']) : $_SESSION['community']['regionid'];
    $addressid = intval($_GPC['addressid']);
    $mobile = trim($_GPC['mobile']);
    $realname = trim($_GPC['realname']);
    $status = intval($_GPC['status']);
    $memberid = intval($_GPC['memberid']);
    $condition = 't1.regionid=:regionid';
    $params[':regionid'] = $regionid;
    $type = intval($_GPC['type']);
    $address = pdo_get('xcommunity_member_bind', array('memberid' => $memberid, 'uniacid' => $_W['uniacid'], 'enable' => 1), array('id'));
    if ($address) {
        //新增地址
        pdo_query('update ' . tablename('xcommunity_member_bind') . "set enable=0 where id=:id ", array(':id' => $address['id']));
    }
    if (empty($_W['member']['realname']) || empty($_W['member']['mobile'])) {
        pdo_update('mc_members', array('idcard' => trim($_GPC['idcard']), 'gender' => trim($_GPC['gender']), 'createtime' => TIMESTAMP, 'realname' => $realname, 'mobile' => $mobile), array('uid' => $_uid));
    }
    if ($type == 1) {
        //手机号
        $condition .= " and t2.mobile=:mobile and t1.regionid=:regionid";
        $params[':mobile'] = $mobile;
        $params[':regionid'] = $regionid;
        $sql = "select t1.*,t2.realname,t2.mobile from" . tablename('xcommunity_member_room') . "t1 left join" . tablename('xcommunity_member_log') . "t2 on t1.id = t2.addressid where $condition";
        $item = pdo_fetch($sql, $params);
        $realname = $realname ? $realname : $item['realname'];
        $mobile = $mobile ? $mobile : $item['mobile'];
        if ($item) {
            if (empty($memberid)) {
                $memberid = model_user::mc_register_region($regionid, '', $realname, $mobile);
            }
            else {
                if (empty($_W['member']['realname']) || empty($_W['member']['mobile'])) {
                    pdo_update('mc_members', array('createtime' => TIMESTAMP, 'realname' => $realname, 'mobile' => $mobile), array('uid' => $_uid));
                }
            }


            if ($memberid) {
                if ($addressid) {
                    $bind = pdo_get('xcommunity_member_bind', array('memberid' => $memberid, 'addressid' => $addressid), array());
                    if ($bind['enable']) {
                        //地址已经绑定过用户
                        $data = array();
                        $data['content'] = '该房号已绑定，无所重复绑定';
                        util::send_result($data);
                        exit();
                    }
                    else {
                        if (pdo_update('xcommunity_member_bind', array('enable' => 1), array('id' => $bind['id']))) {
                            $data = array();
                            $data['content'] = '注册成功';
                            util::send_result($data);
                            exit();
                        }
                    }

                }

                $d = array(
                    'uniacid'    => $_W['uniacid'],
                    'memberid'   => $memberid,
                    'createtime' => TIMESTAMP,
                    'status'     => $status ? $status : 1,
                    'enable'     => 1,
                    'addressid'  => $addressid
                );

                if (pdo_insert('xcommunity_member_bind', $d)) {

                    $_SESSION['community']['addressid'] = $addressid;
                    pdo_update('xcommunity_member', array('visit' => 0, 'createtime' => TIMESTAMP), array('id' => $memberid));
                    pdo_update('xcommunity_member_room', array('enable' => 1), array('id' => $addressid));
                    $region = model_region::region_check($regionid);
                    $city = $region['province'] ? $region['province'] . ' ' . $region['city'] . ' ' . $region['dist'] : '江苏省 南京市 浦口区';
                    $d = array(
                        'uniacid'  => $_W['uniacid'],
                        'realname' => $realname,
                        'mobile'   => $mobile,
                        'address'  => $region['title'] . $item['address'],
                        'city'     => $city,
                        'enable'   => 1,
                        'uid'      => $_uid
                    );
                    pdo_insert('xcommunity_member_address', $d);
                    $data = array();
                    $data['content'] = '注册成功';
                    util::send_result($data);
                    exit();
                }

            }
        }
        else {
            $data = array();
            $data['content'] = '该手机号未导入房号或已注册';
            util::send_result($data);
            exit();
        }
    }
    if ($type == 2) {
        //注册码
        //同时验证手机号和注册码
        $condition .= " and t1.code =:code";
        $params[':code'] = trim($_GPC['regcode']);

//        if (set('p19') || set('x2', $regionid)) {
//            $condition .= " and t2.mobile=:mobile";
//            $params[':mobile'] = $mobile;
//        }
        $sql = "select t1.*,t2.realname,t2.mobile,t2.status as lstatus from" . tablename('xcommunity_member_room') . "t1 left join" . tablename('xcommunity_member_log') . "t2 on t1.id = t2.addressid where $condition";
        $item = pdo_fetch($sql, $params);
//        $status = $item['lstatus'];
        if (empty($item)) {
            $data = array();
            $data['content'] = '注册码不存在或该手机号未导入房号';
            util::send_result($data);
            exit();
        }
        else {
            $realname = $realname ? $realname : $item['realname'];
            $mobile = $mobile ? $mobile : $item['mobile'];
            if (empty($memberid)) {
                $memberid = model_user::mc_register_region($regionid, '', $realname, $mobile);
            }
            else {
                if (empty($_W['member']['realname']) || empty($_W['member']['mobile'])) {
                    pdo_update('mc_members', array('createtime' => TIMESTAMP, 'realname' => $realname, 'mobile' => $mobile), array('uid' => $_uid));
                }
            }

            if ($memberid) {
//                $result = model_user::mc_register_address($item['regionid'], $area = '', $build = '', $unit = '', $room = '', $memberid, $mobile = '', $item['id'], $status);
                $bind = pdo_get('xcommunity_member_bind', array('memberid' => $memberid, 'addressid' => $addressid), array());
                if ($bind) {
                    //地址已经绑定过用户
                    $data = array();
                    $data['content'] = '该房号已绑定，无所重复绑定';
                    util::send_result($data);
                    exit();
                }
                $d = array(
                    'uniacid'    => $_W['uniacid'],
                    'memberid'   => $memberid,
                    'createtime' => TIMESTAMP,
                    'status'     => $status ? $status : 1,
                    'enable'     => 1,
                    'addressid'  => $addressid
                );
                if (pdo_insert('xcommunity_member_bind', $d)) {
                    $_SESSION['community']['addressid'] = $addressid;
                    pdo_update('xcommunity_member', array('visit' => 0, 'createtime' => TIMESTAMP), array('id' => $memberid));
                    pdo_update('xcommunity_member_room', array('enable' => 1), array('id' => $addressid));
                    $item = pdo_get('xcommunity_member_address', array('uid' => $_uid, 'enable' => 1), array('id'));
                    $region = model_region::region_check($regionid);
                    if (empty($item)) {
                        $enable = 1;
                    }
                    else {
                        $enable = 0;
                    }
                    $city = $region['province'] ? $region['province'] . ' ' . $region['city'] . ' ' . $region['dist'] : '江苏省 南京市 浦口区';
                    $d = array(
                        'uniacid'  => $_W['uniacid'],
                        'realname' => $realname,
                        'mobile'   => $mobile,
                        'address'  => $region['title'] . $item['address'],
                        'city'     => $city,
                        'enable'   => $enable,
                        'uid'      => $_uid
                    );
                    pdo_insert('xcommunity_member_address', $d);
                    $data = array();
                    $data['content'] = '注册成功';
                    util::send_result($data);
                    exit();
                }
            }

        }
        if ($result) {
            $data = array();
            $data['content'] = (set('p18') || set('x1', $regionid)) && !set('p22') && !set('x4', $regionid) ? '您的帐号正在审核中，我们将在24小时内为您开通' : '注册成功';
            util::send_result($data);
        }
    }

    if ($result) {
        $data = array();
        $data['content'] = (set('p18') || set('x1', $regionid)) && !set('p22') && !set('x4', $regionid) ? '您的帐号正在审核中，我们将在24小时内为您开通' : '注册成功';
        util::send_result($data);
    }

}
elseif ($op == 'register') {
    $mobile = intval($_GPC['mobile']);
    $realname = trim($_GPC['realname']);
    $area = trim($_GPC['area']);
    $build = trim($_GPC['build']);
    $unit = trim($_GPC['unit']);
    $room = trim($_GPC['room']);
    $idcard = trim($_GPC['idcard']);
    $contract = trim($_GPC['contract']);
    $status = intval($_GPC['status']);
    $regionid = intval($_GPC['regionid']);
    if (empty($_uid)) {
        model_user::checkauth();
    }
    $uid = $_uid ? $_uid : mc_openid2uid($_W['openid']);
    pdo_update('mc_members', array('createtime' => TIMESTAMP, 'realname' => $realname, 'mobile' => $mobile), array('uid' => $uid));
    //联动注册
    $house = set("p91") || set('x58', $regionid) ? 1 : 0;//1开启选择房号
    if ($house) {
        $addressid = $room;
    }
    else {
        if ($area) {
            $_area = pdo_get('xcommunity_area', array('regionid' => $regionid, 'title' => $area), array('id'));
            $areaid = $_area['id'];
            if (empty($_area)) {
                $d = array(
                    'uniacid'  => $_W['uniacid'],
                    'regionid' => $regionid,
                    'title'    => $area,
                    'uid'      => $_W['uid']
                );
                pdo_insert('xcommunity_area', $d);
                $areaid = pdo_insertid();
            }
        }
        if ($build) {
            $condition = "regionid=:regionid and buildtitle=:buildtitle";
            $par[':regionid'] = $regionid;
            $par[':buildtitle'] = $build;
            if ($build) {
                $condition .= " and areaid=:areaid";
                $par[':areaid'] = $areaid;
            }
            $sql = "select * from" . tablename('xcommunity_build') . "where $condition";
            $_build = pdo_fetch($sql, $par);
            $buildid = $_build['id'];
            if (empty($_build)) {
                $dat = array(
                    'uniacid'    => $_W['uniacid'],
                    'regionid'   => $regionid,
                    'buildtitle' => $build,
                    'areaid'     => $areaid
                );
                pdo_insert('xcommunity_build', $dat);
                $buildid = pdo_insertid();
            }
        }
        if ($unit) {
            $_unit = pdo_get('xcommunity_unit', array('regionid' => $regionid, 'buildid' => $buildid, 'unit' => $unit), array('id'));
            $unitid = $_unit['id'];
            if (empty($_unit)) {
                $data = array(
                    'uniacid' => $_W['uniacid'],
                    'buildid' => $buildid,
                    'unit'    => $unit,
                    'uid'     => $_W['uid']
                );
                pdo_insert('xcommunity_unit', $data);
                $unitid = pdo_insertid();
            }
        }
        $addr = '';
        $arr = util::xqzd($regionid);
        $condition = '';
        if ($area) {
            $addr .= $area . $arr['a'];
            $condition .= " and area=:area";
            $params[':area'] = $area;
        }
        if ($build) {
            $addr .= $build . $arr['b'];
            $condition .= " and build=:build";
            $params[':build'] = $build;
        }
        if ($unit) {
            $addr .= $unit . $arr['c'];
            $condition .= " and unit=:unit";
            $params[':unit'] = $unit;
        }
        if ($room) {
            $addr .= $room . $arr['d'];
            $condition .= " and room=:room";
            $params[':room'] = $room;
        }
        $sql = "select id from " . tablename('xcommunity_member_room') . "where regionid=:regionid and uniacid=:uniacid $condition";
        $params[':regionid'] = $regionid;
        $params[':uniacid'] = intval($_W['uniacid']);
        $address = pdo_fetch($sql, $params);
        if (empty($address)) {

            $da = array(
                'area'       => $area,
                'build'      => $build,
                'unit'       => $unit,
                'room'       => $room,
                'square'     => '',
                'address'    => $addr,
                'createtime' => TIMESTAMP,
                'regionid'   => $regionid,
                'uniacid'    => $_W['uniacid'],
                'code'       => rand(10000000, 99999999),
                'enable'     => 1,
                'areaid'     => $areaid,
                'buildid'    => $buildid,
                'unitid'     => $unitid
            );
            pdo_insert('xcommunity_member_room', $da);
            $addressid = pdo_insertid();
            $unit_num = pdo_fetchcolumn('select count(*) from' . tablename('xcommunity_unit') . " where uniacid=:uniacid and buildid=:buildid and regionid=:regionid", array(':uniacid' => $_W['uniacid'], ':buildid' => $buildid, 'regionid' => $regionid));
            $room_num = pdo_fetchcolumn('select count(*) from' . tablename('xcommunity_member_room') . " where areaid=:areaid and buildid=:buildid", array(':areaid' => $areaid, ':buildid' => $buildid));
            pdo_update('xcommunity_build', array('unit_num' => $unit_num, 'room_num' => $room_num), array('id' => $buildid));
        }
        else {
            $addressid = $address['id'];
        }

    }


    //家属，检测业主有没注册
    if ($status == 2) {
        $sql = "select t1.openid from" . tablename('mc_mapping_fans') . "t1 left join" . tablename('xcommunity_member') . "t2 on t1.uid=t2.uid left join" . tablename('xcommunity_member_bind') . "t3 on t2.id = t3.memberid where t2.open_status=1 and t3.status= 1 and t3.addressid=:addressid";
        $users = pdo_fetchall($sql, array(':addressid' => $addressid));

//        if (empty($contract) && empty($users)) {
//            $data = array();
//            $data['content'] = '注册失败，由于业主尚未注册，您必须填写购房合同编号才能注册成功。';
//            util::send_result($data);
//        }
    }
    $memberid = intval($_GPC['memberid']);
    $data = array(
        'uniacid'     => $_W['uniacid'],
        'regionid'    => $regionid,
        'uid'         => $uid,
        'createtime'  => TIMESTAMP,
        'enable'      => 1,
        'status'      => (set('p18') || set('x1', $regionid)) && !set('p22') && !set('x4', $regionid) ? 0 : 1,//修改，注意游客的情况
        'open_status' => set('p32') || set('x14', $regionid) ? 1 : 0,
        'visit'       => 1,
        'idcard'      => $idcard,
        'contract'    => $contract
    );
    if (empty($memberid)) {
        pdo_insert('xcommunity_member', $data);
        $memberid = pdo_insertid();
    }
    if ($memberid) {
        $dat = array(
            'uniacid'    => $_W['uniacid'],
            'memberid'   => $memberid,
            'createtime' => TIMESTAMP,
            'status'     => $status ? $status : 1,
            'enable'     => 1,
            'addressid'  => $addressid
        );
        if (pdo_insert('xcommunity_member_bind', $dat)) {
            $d = array(
                'uniacid'   => $_W['uniacid'],
                'regionid'  => $regionid,
                'realname'  => $realname,
                'mobile'    => $mobile,
                'addressid' => $addressid,
                'status'    => $status ? $status : 1,
            );
            pdo_insert('xcommunity_member_log', $d);
            pdo_update('xcommunity_member', array('visit' => 0, 'open_status' => 0, 'idcard' => $idcard, 'contract' => $contract, 'createtime' => TIMESTAMP), array('id' => $memberid));
            pdo_update('xcommunity_member_room', array('enable' => 1), array('id' => $addressid));
            $data = array();
            $data['content'] = $status == 3 ? '请您携带身份证和租赁合同来物业中心办理审核。' : '您的账号将在24小时内为您审核';
            if ($status == 2) {
                if ($users) {
                    $account_api = WeAccount::create();
                    $template_id = 'yROTQJ4CbiQCSnjthMgidWAJPVbVFgCGgsj4li3uQNg';
                    $content = array(
                        'first'    => array(
                            'value' => '您好，您有家属注册微信开门功能，请您审核',
                        ),
                        'keyword1' => array(
                            'value' => $realname,
                        ),
                        'keyword2' => array(
                            'value' => $mobile,
                        ),
                        'keyword3' => array(
                            'value' => date('Y-m-d H:i', TIMESTAMP),
                        ),
                        'remark'   => array(
                            'value' => '您最多可以审核5个家属，超出请在物业中心办理',
                        ),
                    );
                    $url = $_W['siteroot'] . "app/index.php?i={$_W['uniacid']}&c=entry&op=verity&memberid={$memberid}&do=register&m=" . $this->module['name'];
                    foreach ($users as $k => $user) {
                        $status = $account_api->sendTplNotice($user['openid'], $template_id, $content, $url);
                    }
                    $data['content'] = '请联系业主审核开通微信开门权限。';
                }

            }

            util::send_result($data);
        }
    }

}
elseif ($op == 'verity') {
    $log = pdo_getall('xcommunity_user_open_log', array('uid' => $_uid), array('id'));
    $total = count($log);
    if ($total == 5) {
        util::send_error(-1, '您开通的家属已经超过5个，请来物业中心办理');
    }
    $member = pdo_get('xcommunity_member', array('id' => intval($_GPC['memberid'])), array('uid', 'regionid'));
    if ($member) {
        $sql = "select t1.deviceid from" . tablename('xcommunity_bind_door_device') . "t1 left join" . tablename('xcommunity_bind_door') . "t2 on t1.doorid = t2.id where t2.uid=:uid and t2.regionid=:regionid";
        $doors = pdo_fetchall($sql, array(':uid' => $_uid, ':regionid' => $member['regionid']));
        if (pdo_update('xcommunity_member', array('open_status' => 1), array('id' => intval($_GPC['memberid'])))) {
            $dat = array(
                'regionid' => $member['regionid'],
                'uid'      => $member['uid'],
                'uniacid'  => $_W['uniacid']
            );
            if (pdo_insert('xcommunity_bind_door', $dat)) {
                $id = pdo_insertid();
                foreach ($doors as $key => $value) {
                    if ($value['deviceid']) {
                        $d = array(
                            'doorid'   => $id,
                            'deviceid' => $value['deviceid'],
                        );
                        pdo_insert('xcommunity_bind_door_device', $d);
                    }
                }
            }
            $data = array(
                'uniacid' => $_W['uniacid'],
                'uid'     => $_uid,
                'num'     => 1
            );
            if (pdo_insert('xcommunity_user_open_log', $data)) {
                util::send_error(-1, '开通成功');
            }
        }
    }

}
elseif ($op == 'veritymobile') {
    $condition = "t2.regionid=:regionid and t2.mobile=:mobile";
    $regionid = intval($_GPC['regionid']) ? intval($_GPC['regionid']) : $_SESSION['community']['regionid'];
    $mobile = trim($_GPC['mobile']);
    $params[':mobile'] = $mobile;
    $params[':regionid'] = $regionid;
    $sql = "select distinct t1.id,t1.address,t1.id from" . tablename('xcommunity_member_room') . "t1 left join" . tablename('xcommunity_member_log') . "t2 on t1.id = t2.addressid where $condition";
    $item = pdo_fetchall($sql, $params);
    foreach ($item as $k => $v) {
        $item[$k]['key'] = $v['id'];
        $item[$k]['value'] = $v['address'];
    }
    $data = array();
    if ($item) {
        $data['content'] = $item;

    }
    else {
        $data = array('status' => 1, 'content' => '系统未录入该手机号');
    }
    util::send_result($data);
}
elseif ($op == 'veritycode') {
    $condition = "t1.regionid=:regionid ";
    $regionid = trim($_GPC['regionid']) ? trim($_GPC['regionid']) : $_SESSION['community']['regionid'];
    $params[':regionid'] = $regionid;
//    $mobile = trim($_GPC['mobile']);
//    if ($mobile) {
//        $condition .= " and t2.mobile=:mobile";
//        $params[':mobile'] = $mobile;
//    }
    $regcode = intval($_GPC['regcode']);

    if ($regcode) {
        $condition .= " and t1.code=:regcode";
        $params[':regcode'] = $regcode;
    }
    $sql = "select distinct t1.id,t1.address,t1.id from" . tablename('xcommunity_member_room') . "t1 left join" . tablename('xcommunity_member_log') . "t2 on t1.id = t2.addressid where $condition";

    $item = pdo_fetchall($sql, $params);

    foreach ($item as $k => $v) {
        $item[$k]['key'] = $v['id'];
        $item[$k]['value'] = $v['address'];
    }
    if ($item) {
        $data = array();
        $data['content'] = $item;
        util::send_result($data);
    }
    else {
        $data = array();
        util::send_error(-1, '注册码不存在或该手机号未导入房号');
    }
}
elseif ($op == 'room') {
    $memberid = intval($_GPC['memberid']) ? intval($_GPC['memberid']) : $_SESSION['community']['id'];
    $enable = intval($_GPC['enable']);
    $regionid = intval($_GPC['regionid']) ? intval($_GPC['regionid']) : $_SESSION['community']['regionid'];
    $area = trim($_GPC['area']);
    $build = trim($_GPC['build']);
    $unit = trim($_GPC['unit']);
    $room = trim($_GPC['room']);
    if (empty($memberid)) {
        util::send_error(-1, 'memberid null');
    }
    $rooms = pdo_getall('xcommunity_member_bind', array('memberid' => $memberid), array());
    if ($rooms) {
        //请求为 1 所有房号设置为0
        if ($enable == 1) {
            pdo_update('xcommunity_member_bind', array('enable' => 0), array('memberid' => $memberid));
        }
    }
    else {
        $enable = 1;
    }

    //联动注册
    $house = set("p91") || set('x58', $regionid) ? 1 : 0;//1开启选择房号
    $addressid = '';
    $member_room = array();
    if ($house) {
        //开启联动
        $addressid = $room;
        $member_room = pdo_get('xcommunity_member_room', array('id' => $addressid), array('address', 'areaid', 'buildid', 'unitid'));
    }
    if (empty($addressid)) {
        if (set('p89') || set('x57', $regionid)) {
            //开启注册验证
            if ($area) {
                $_area = pdo_get('xcommunity_area', array('title' => $area, 'regionid' => $regionid), array('id'));
                if (empty($_area)) {
                    $data = array();
                    $data['content'] = '区域不存在，请重新填写';
                    util::send_result($data);
                    exit();
                }
            }
            if ($build) {
                $_build = pdo_get('xcommunity_build', array('build' => $build, 'regionid' => $regionid), array('id'));
                if (empty($_build)) {
                    $data = array();
                    $data['content'] = '楼宇不存在，请重新填写';
                    util::send_result($data);
                    exit();
                }
            }
            if ($room) {
                $_room = pdo_get('xcommunity_member_room', array('room' => $room, 'regionid' => $regionid), array('id'));
                if (empty($_room)) {
                    $data = array();
                    $data['content'] = '房屋不存在，请重新填写';
                    util::send_result($data);
                    exit();
                }
            }
        }
        if ($area) {
            $_area = pdo_get('xcommunity_area', array('regionid' => $regionid, 'title' => $area), array('id'));
            $areaid = $_area['id'];
            if (empty($_area)) {
                $d = array(
                    'uniacid'  => $_W['uniacid'],
                    'regionid' => $regionid,
                    'title'    => $area,
                    'uid'      => $_W['uid']
                );
                pdo_insert('xcommunity_area', $d);
                $areaid = pdo_insertid();
            }
        }
        if ($build) {
            $condition = "regionid=:regionid and buildtitle=:buildtitle";
            $par[':regionid'] = $regionid;
            $par[':buildtitle'] = $build;
            if ($build) {
                $condition .= " and areaid=:areaid";
                $par[':areaid'] = $areaid;
            }
            $sql = "select * from" . tablename('xcommunity_build') . "where $condition";
            $_build = pdo_fetch($sql, $par);
            $buildid = $_build['id'];
            if (empty($_build)) {
                $dat = array(
                    'uniacid'    => $_W['uniacid'],
                    'regionid'   => $regionid,
                    'buildtitle' => $build,
                    'areaid'     => $areaid
                );
                pdo_insert('xcommunity_build', $dat);
                $buildid = pdo_insertid();
            }
        }
        if ($unit) {
            $_unit = pdo_get('xcommunity_unit', array('regionid' => $regionid, 'buildid' => $buildid, 'unit' => $unit), array('id'));
            $unitid = $_unit['id'];
            if (empty($_unit)) {
                $data = array(
                    'uniacid' => $_W['uniacid'],
                    'buildid' => $buildid,
                    'unit'    => $unit,
                    'uid'     => $_W['uid']
                );
                pdo_insert('xcommunity_unit', $data);
                $unitid = pdo_insertid();
            }
        }
        $addr = '';
        $arr = util::xqzd($regionid);
        $condition = '';
        if ($area) {
            $addr .= $area . $arr['a'];
            $condition .= " and area=:area";
            $params[':area'] = $area;
        }
        if ($build) {
            $addr .= $build . $arr['b'];
            $condition .= " and build=:build";
            $params[':build'] = $build;
        }
        if ($unit) {
            $addr .= $unit . $arr['c'];
            $condition .= " and unit=:unit";
            $params[':unit'] = $unit;
        }
        if ($room) {
            $addr .= $room . $arr['d'];
            $condition .= " and room=:room";
            $params[':room'] = $room;
        }
        $sql = "select id,enable from " . tablename('xcommunity_member_room') . "where regionid=:regionid and uniacid=:uniacid $condition";

        $params[':regionid'] = $regionid;
        $params[':uniacid'] = intval($_W['uniacid']);
        $r = pdo_fetch($sql, $params);
        if (empty($r['id'])) {

            $dat = array(
                'area'       => $area,
                'build'      => $build,
                'unit'       => $unit,
                'room'       => $room,
                'square'     => '',
                'address'    => $addr,
                'createtime' => TIMESTAMP,
                'regionid'   => $regionid,
                'uniacid'    => $_W['uniacid'],
                'code'       => rand(10000000, 99999999),
                'enable'     => 1,
                'areaid'     => $areaid,
                'buildid'    => $buildid,
                'unitid'     => $unitid
            );
            pdo_insert('xcommunity_member_room', $dat);
            $addressid = pdo_insertid();
            $unit_num = pdo_fetchcolumn('select count(*) from' . tablename('xcommunity_unit') . " where uniacid=:uniacid and buildid=:buildid and regionid=:regionid", array(':uniacid' => $_W['uniacid'], ':buildid' => $buildid, 'regionid' => $regionid));
            $room_num = pdo_fetchcolumn('select count(*) from' . tablename('xcommunity_member_room') . " where areaid=:areaid and buildid=:buildid", array(':areaid' => $areaid, ':buildid' => $buildid));
            pdo_update('xcommunity_build', array('unit_num' => $unit_num, 'room_num' => $room_num), array('id' => $buildid));
        }
        else {
            $addressid = $r['id'];
            $member_room = pdo_get('xcommunity_member_room', array('id' => $addressid), array('address', 'areaid', 'buildid', 'unitid'));
        }
    }
    $bind = pdo_get('xcommunity_member_bind', array('memberid' => $memberid, 'addressid' => $addressid), array());
    if ($bind) {
        //地址已经绑定过用户
        util::send_error(-1, '房号已绑定');
        exit();
    }
    /**
     * 为用户绑定门禁
     */
    if ($member_room) {
        $doorid = pdo_getcolumn('xcommunity_bind_door', array('uniacid' => $_W['uniacid'], 'uid' => $_uid, 'regionid' => $regionid), 'id');
        if (!$doorid) {
            $data1 = array(
                'regionid' => $regionid,
                'uid'      => $_uid,
                'uniacid'  => $_W['uniacid']
            );
            pdo_insert('xcommunity_bind_door', $data1);
            $doorid = pdo_insertid();
        }
        $bind_devices = pdo_getall('xcommunity_bind_door_device', array('doorid' => $doorid), array('deviceid'));
        $device_ids = _array_column($bind_devices, 'deviceid');
        $devices1 = pdo_getall('xcommunity_building_device', array('regionid' => $regionid, 'type' => 2), array('id'));
        if ($devices1) {
            foreach ($devices1 as $k => $v) {
                if (!in_array($v['id'], $device_ids)) {
                    $dat1 = array(
                        'doorid'   => $doorid,
                        'deviceid' => $v['id'],
                    );
                    pdo_insert('xcommunity_bind_door_device', $dat1);
                }
            }
        }
        $condition = " regionid=:regionid and type=1 ";
        $paramss[':regionid'] = $regionid;
        if ($member_room['areaid']) {
            $condition .= " and areaid=:areaid";
            $paramss[':areaid'] = $member_room['areaid'];
        }
        if ($member_room['buildid']) {
            $condition .= " and buildid=:buildid";
            $paramss[':buildid'] = $member_room['buildid'];
        }
        if ($member_room['unitid']) {
            $condition .= " and unitid=:unitid";
            $paramss[':unitid'] = $member_room['unitid'];
        }
        $devices2 = pdo_fetchall("select id from" . tablename('xcommunity_building_device') . "where $condition", $paramss);
        if ($devices2) {
            foreach ($devices2 as $k => $v) {
                if (!in_array($v['id'], $device_ids)) {
                    $dat2 = array(
                        'doorid'   => $doorid,
                        'deviceid' => $v['id'],
                    );
                    pdo_insert('xcommunity_bind_door_device', $dat2);
                }
            }
        }
    }
    $d = array(
        'uniacid'    => $_W['uniacid'],
        'memberid'   => $memberid,
        'createtime' => TIMESTAMP,
        'status'     => $status ? $status : 1,
        'enable'     => $enable,
        'addressid'  => $addressid
    );
    if (pdo_insert('xcommunity_member_bind', $d)) {
        $_SESSION['community']['addressid'] = $addressid;
        pdo_update('xcommunity_member', array('visit' => 0, 'createtime' => TIMESTAMP), array('id' => $memberid));
        if ($r && $r['enable'] == 0) {
            pdo_update('xcommunity_member_room', array('enable' => 1), array('id' => $r['id']));
        }
        $data = array();
        $data['content'] = '添加成功';
        util::send_result($data);
    }
}