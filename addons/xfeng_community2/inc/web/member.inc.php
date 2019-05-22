<?php
/**
 * 小区秘书模块
 *
 * [蓝牛科技] Copyright (c) 2013 xqms.cn
 */
/**
 * 后台小区用户信息
 */

global $_GPC, $_W;
$ops = array('list', 'add', 'delete', 'verify', 'bind', 'binddoor', 'del', 'visit', 'room', 'open', 'post', 'change', 'group', 'pldoor', 'users', 'usersDel');
$op = !empty($_GPC['op']) ? $_GPC['op'] : 'list';
if (!in_array($op, $ops)) {
    message('该方法不存在(op:' . $op . ')');
}
// 操作员
$user = util::xquser($_W['uid']);
if ($user && $user['menu_ops']) {
    $menu_opss = explode(',', $user['menu_ops']);
}
$regions = model_region::region_fetall();
/**
 * 住户信息列表
 */
if ($op == 'list') {
    if (checksubmit('send')) {
//        print_r($_GPC);exit();
        $status = intval($_GPC['status']);
        $remark = $_GPC['content'];
        $memberid = intval($_GPC['memberid']);
        if (empty($status)) {
            itoast('请认真填写信息', referer(), 'error', true);
        }
        $member = pdo_fetch("select t1.realname,t1.mobile,t3.openid,t1.uid,t2.status from" . tablename('mc_members') . "t1 left join" . tablename('xcommunity_member') . "t2 on t2.uid=t1.uid left join" . tablename('mc_mapping_fans') . "t3 on t3.uid = t1.uid where t2.id =:id", array(':id' => $memberid));
        if (empty($member)) {
            itoast('该信息不存在', referer(), 'error', true);
        }
        if ($status == 1 && $member['status'] == 0) {
            $first = "您好，您的资料已经通过审核";
            pdo_update("xcommunity_member", array('status' => 1, 'enable' => 1), array("id" => $memberid, "uniacid" => $_W['uniacid']));
        } elseif ($status == 2 && $member['status'] == 0) {
            $first = "您好，您的审核未通过,请重新申请";
            pdo_delete("xcommunity_member", array('id' => $memberid));
            pdo_delete('xcommunity_member_bind', array('id' => $memberid));
        } elseif ($status == 1 && $member['status'] == 1) {
            itoast('该用户已经是通过的状态', referer(), 'error', true);
        } elseif ($status == 2 && $member['status'] == 1) {
            $first = "您好，您的用户状态已经禁止";
            pdo_update("xcommunity_member", array('status' => 0), array("id" => $memberid, "uniacid" => $_W['uniacid']));
        }
        if (set('t19')) {
            $createtime = date('Y-m-d H:i', TIMESTAMP);
            $content = array(
                'first'    => array(
                    'value' => $first,
                ),
                'keyword1' => array(
                    'value' => $member['realname'],
                ),
                'keyword2' => array(
                    'value' => $member['mobile'],
                ),
                'keyword3' => array(
                    'value' => $createtime,
                ),
                'remark'   => array(
                    'value' => $remark,
                ),
            );
            $tplid = set('t20');
            $openid = $member['openid'];
            util::sendTplNotice($openid, $tplid, $content, $url = '', $color = '');
        }
        itoast('审核成功', referer(), 'success', true);
    }
    if (checksubmit('add')) {
        $memberid = intval($_GPC['memberid']);
        $data = array(
            'remark' => $_GPC['remark']
        );
        $r = pdo_update("xcommunity_member", $data, array("id" => $memberid, "uniacid" => $_W['uniacid']));
        util::permlog('', '业主id:' . $memberid . '修改备注');
        if ($r) {
            itoast('提交成功', referer(), 'success', true);
        }
    }
    if (checksubmit('del')) {
        $ids = $_GPC['ids'];
        if (!empty($ids)) {
            foreach ($ids as $key => $id) {
                $member = pdo_get('xcommunity_member', array('id' => $id), array());
                if ($member) {
//                    pdo_delete('xcommunity_member_bind', array('memberid' => $id));
                    pdo_delete('xcommunity_member', array('id' => $id));
                    pdo_delete('xcommunity_member_family', array('from_uid' => $member['uid']));
                    pdo_delete('xcommunity_member_family', array('to_uid' => $member['uid']));
                    $binds = pdo_getall('xcommunity_bind_door', array('uid' => $member['uid'], 'regionid' => $member['regionid']));
                    foreach ($binds as $k => $v) {
                        pdo_delete('xcommunity_bind_door_device', array('doorid' => $v['id']));
                        pdo_delete('xcommunity_bind_door', array('id' => $v['id']));
                    }
                    pdo_delete('xcommunity_face_users', array('uid' => $member['uid']));
                    $sql = "select t1.id,t1.memberid,t2.id as addressid from" . tablename('xcommunity_member_bind') . "t1 left join" . tablename('xcommunity_member_room') . "t2 on t1.addressid=t2.id where t1.memberid=:id";
                    $binds = pdo_fetchall($sql, array(':id' => $id));
                    foreach ($binds as $ke => $va) {
                        pdo_delete('xcommunity_member_bind', array('id' => $va['id']));
                        $bindid = pdo_getcolumn('xcommunity_member_bind', array('addressid' => $va['addressid']), 'id');
                        if (empty($bindid)) {
                            pdo_update('xcommunity_member_room', array('enable' => 0), array('id' => $va['addressid']));
                        }
                    }
                }
            }
            util::permlog('', '批量删除用户信息');
            itoast('删除成功', referer(), 'success', true);
        }
    }
    $condition = " t2.uniacid =:uniacid";
    $params[':uniacid'] = $_W['uniacid'];
    if (!empty($_GPC['realname'])) {
        $condition .= " AND t1.realname LIKE '%{$_GPC['realname']}%'";
    }
    if (!empty($_GPC['mobile'])) {
        $condition .= " AND t1.mobile LIKE '%{$_GPC['mobile']}%'";
    }

    if (checksubmit('plmember')) {
        $con = " t2.uniacid =:uniacid";
        $par[':uniacid'] = $_W['uniacid'];
        if ($_GPC['ids']) {
            $ids = implode(',', $_GPC['ids']);
            $con .= " and t2.id in({$ids})";
        }
        if ($user[type] == 3) {
            //普通管理员
            $con .= " and t2.regionid in({$user['regionid']})";
        }
        $sql = "select t2.status,t2.open_status,t2.id from" . tablename('xcommunity_member') . "as t2 where $con order by t2.createtime desc ";
        $members = pdo_fetchall($sql, $par);
        foreach ($members as $k => $v) {
            pdo_update('xcommunity_member', array('status' => 1), array('id' => $v['id']));

        }
        util::permlog('', '批量审核业主状态');
        itoast('审核成功', referer(), 'success', ture);
    }
    if (checksubmit('plopen')) {
        //批量开通大门
        $con = " t2.uniacid =:uniacid";
        $par[':uniacid'] = $_W['uniacid'];
        if ($_GPC['ids']) {
            $ids = implode(',', $_GPC['ids']);
            $con .= " and t2.id in({$ids})";
        }
        if ($user[type] == 3) {
            //普通管理员
            $con .= " and t2.regionid in({$user['regionid']})";
        }
        $sql = "select t2.status,t2.open_status,t2.id,t2.regionid,t2.uid from" . tablename('xcommunity_member') . "as t2 where $con order by t2.createtime desc ";
        $members = pdo_fetchall($sql, $par);
        foreach ($members as $k => $v) {
            pdo_update('xcommunity_member', array('open_status' => 1), array('id' => $v['id']));
            $door = pdo_get('xcommunity_bind_door', array('uid' => $v['uid'], 'regionid' => $v['regionid']), array('id'));
            if (empty($door)) {
                $data = array(
                    'regionid' => $v['regionid'],
                    'uid'      => $v['uid']
                );

                $data['uniacid'] = $_W['uniacid'];
                pdo_insert('xcommunity_bind_door', $data);
                $id = pdo_insertid();
            } else {
                $id = $door['id'];
                //pdo_delete('xcommunity_bind_door_device', array('doorid' => $id));
            }
            $devices = pdo_getall('xcommunity_building_device', array('regionid' => $v['regionid'], 'type' => 2), array('id'));
            foreach ($devices as $key => $value) {
                $item = pdo_get('xcommunity_bind_door_device', array('doorid' => $id, 'deviceid' => $value['id']), array('id'));
                if (empty($item)) {
                    $dat = array(
                        'doorid'   => $id,
                        'deviceid' => $value['id'],
                    );
                    pdo_insert('xcommunity_bind_door_device', $dat);
                }

            }
        }
        util::permlog('', '批量审核业主开门');
        itoast('审核成功', referer(), 'success', ture);
    }
    if (checksubmit('pldoor')) {
        //批量开通单元门
        $con = " t2.uniacid =:uniacid";
        $par[':uniacid'] = $_W['uniacid'];
        if ($_GPC['ids']) {
            $ids = implode(',', $_GPC['ids']);
            $con .= " and t2.id in({$ids})";
        }
        if ($user[type] == 3) {
            //普通管理员
            $con .= " and t2.regionid in({$user['regionid']})";
        }
        $sql = "select t2.status,t2.open_status,t2.id,t2.regionid,t2.uid from" . tablename('xcommunity_member') . "as t2 where $con order by t2.createtime desc ";
        $members = pdo_fetchall($sql, $par);
        foreach ($members as $k => $v) {
            pdo_update('xcommunity_member', array('open_status' => 1), array('id' => $v['id']));
            $door = pdo_get('xcommunity_bind_door', array('uid' => $v['uid'], 'regionid' => $v['regionid']), array('id'));
            if (empty($door)) {
                $data = array(
                    'regionid' => $v['regionid'],
                    'uid'      => $v['uid']
                );

                $data['uniacid'] = $_W['uniacid'];
                pdo_insert('xcommunity_bind_door', $data);
                $id = pdo_insertid();
            } else {
                $id = $door['id'];
                // pdo_delete('xcommunity_bind_door_device', array('doorid' => $id));
            }
            $sql = "select t1.unit,t1.build,t1.regionid from" . tablename('xcommunity_member_room') . "t1 left join" . tablename('xcommunity_member_bind') . "t2 on t1.id=t2.addressid where t2.memberid=:memberid";
            $room = pdo_fetch($sql, array(':memberid' => $v['id']));
            $arr = util::xqset($room['regionid']);
            $title = $room['build'] . $arr['b1'];
            $unit = $room['unit'] . $arr['c1'];
            $devices = pdo_getall('xcommunity_building_device', array('regionid' => $v['regionid'], 'type' => 1, 'title' => $title, 'unit' => $unit), array('id'));
            foreach ($devices as $key => $value) {
                $item = pdo_get('xcommunity_bind_door_device', array('doorid' => $id, 'deviceid' => $value['id']), array('id'));
                if (empty($item)) {
                    $dat = array(
                        'doorid'   => $id,
                        'deviceid' => $value['id'],
                    );
                    pdo_insert('xcommunity_bind_door_device', $dat);
                }
            }
        }
        util::permlog('', '批量审核业主开门');
        itoast('审核成功', referer(), 'success', ture);
    }
    if (checksubmit('plwechat')) {
        $con = " t2.uniacid =:uniacid";
        $par[':uniacid'] = $_W['uniacid'];
        if ($_GPC['ids']) {
            $ids = ltrim(rtrim($_GPC['ids'], ','), ',');
            $con .= " and t2.id in({$ids})";
        }
        if ($user[type] == 3) {
            //普通管理员
            $con .= " and t2.regionid in({$user['regionid']})";
        }
        $sql = "select t4.openid from" . tablename('xcommunity_member') . "as t2 left join" . tablename('mc_mapping_fans') . "t4 on t4.uid=t2.uid where $con order by t2.createtime desc ";
        $members = pdo_fetchall($sql, $par);
        $content = trim($_GPC['content']);
        $tplid = set('t2');
        $data = array(
            'first'    => array(
                'value' => '',
            ),
            'keyword1' => array(
                'value' => $_GPC['title'],
            ),
            'keyword2' => array(
                'value' => date('Y-m-d H:i', TIMESTAMP),
            ),
            'keyword3' => array(
                'value' => $content,
            ),
            'remark'   => array(
                'value' => '',
            )
        );
        foreach ($members as $k => $v) {
            $account_api = WeAccount::create();
            $status = $account_api->sendTplNotice($v['openid'], $tplid, $data, $url);
            if ($status) {
                util::permlog('用户管理-发送信息', '信息标题:' . $_GPC['title'] . '</br>接收人:' . $v['realname']);
            }
        }
        itoast('发送成功', referer(), 'success');
    }
    if ($_GPC['export'] == 1) {
        $ttsql = "select t1.uid,t1.realname as realname,t1.mobile as mobile,t1.createtime,t2.remark,t2.status,t2.enable,t2.open_status,t2.regionid,t2.id,t3.title from" . tablename('mc_members') . "as t1 left join" . tablename('xcommunity_member') . "as t2 on t1.uid=t2.uid left join" . tablename('xcommunity_region') . "as t3 on t2.regionid=t3.id where $condition and t1.mobile <> '' order by t2.createtime desc ";
        $li = pdo_fetchall($ttsql, $params);
        foreach ($li as $key => $value) {
            $li[$key]['cctime'] = date('Y-m-d H:i', $value['createtime']);
            $li[$key]['s'] = empty($value['status']) ? '未审核' : '通过';
        }
        model_execl::export($li, array(
            "title"   => "会员数据-" . date('Y-m-d-H-i', time()),
            "columns" => array(
                array(
                    'title' => '姓名',
                    'field' => 'realname',
                    'width' => 12
                ),
                array(
                    'title' => '手机号',
                    'field' => 'mobile',
                    'width' => 12
                ),
                array(
                    'title' => '注册时间',
                    'field' => 'cctime',
                    'width' => 12
                ),
                array(
                    'title' => '状态',
                    'field' => 's',
                    'width' => 12
                ),
            )
        ));
    }
    $homenumber = trim($_GPC['homenumber']);
    $cont = '';
    if (!empty($homenumber)) {
        $cont .= " left join " . tablename('xcommunity_member_room') . " t6 on t6.id = t5.addressid";
        $condition .= " AND t6.address LIKE '%{$homenumber}%' ";
    }
    if (intval($_GPC['regionid'])) {
        $condition .= " AND t2.regionid =:regionid";
        $params[':regionid'] = intval($_GPC['regionid']);
        if ($_GPC['buildid']) {
            $regionid = intval($_GPC['regionid']);
            $cont .= " left join " . tablename('xcommunity_member_room') . " t7 on t7.id = t5.addressid";
            $condition .= " AND t7.buildid =:buildid";
            $params[':buildid'] = intval($_GPC['buildid']);
        }
        $builds = pdo_fetchall("select t1.*,t2.title from" . tablename('xcommunity_build') . "t1 left join " . tablename('xcommunity_area') . "t2 on t1.areaid=t2.id where t1.regionid=:regionid order by t1.id asc ", array(':regionid' => $_GPC['regionid']));
        foreach ($builds as $k => $v) {
            $builds[$k]['title'] = $v['title'] ? $v['title'] . $arr[a1] . $v['buildtitle'] . $arr[b1] : $v['buildtitle'] . $arr[b1];
        }
    } else {
        if ($user && $user[type] == 3) {
            //普通管理员
            $condition .= " and t2.regionid in({$user['regionid']})";
        }
    }
    $addressid = intval($_GPC['addressid']);
    if ($addressid) {
        $condition .= " and t5.addressid=:addressid";
        $params[':addressid'] = $addressid;
    }
//显示业主信息
    $pindex = max(1, intval($_GPC['page']));
    $psize = 20;
    $sql = "select distinct t2.idcard,t2.contract,t2.id,t1.uid,t1.realname,t1.mobile,t2.createtime,t2.remark,t2.status,t2.enable,t2.open_status,t2.regionid,t2.id,t3.title,t4.openid from" . tablename('mc_members') . "as t1 left join" . tablename('xcommunity_member') . "as t2 on t1.uid=t2.uid left join" . tablename('xcommunity_region') . "as t3 on t2.regionid=t3.id left join" . tablename('mc_mapping_fans') . "t4 on t4.uid=t2.uid left join" . tablename('xcommunity_member_bind') . "t5 on t5.memberid = t2.id $cont where $condition and t5.id <> '' order by t2.createtime desc LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
    $list = pdo_fetchall($sql, $params);
//    print_r($list);
    foreach ($list as $key => $val) {

        $con = 't1.memberid=:memberid';
        $par[":memberid"] = $val['id'];

        $bsql = "select t1.status as bstatus,t2.address,t1.id,t1.createtime as bcreatetime,t1.enable from" . tablename('xcommunity_member_bind') . "t1 left join" . tablename('xcommunity_member_room') . "t2 on t1.addressid = t2.id where $con order by t1.createtime desc ";
        $binds = pdo_fetchall($bsql, $par);
        $list[$key]['bind'] = $binds;
    }
    $tsql = "select count(distinct t2.uid) from" . tablename('mc_members') . "as t1 left join" . tablename('xcommunity_member') . "as t2 on t1.uid=t2.uid left join" . tablename('xcommunity_region') . "as t3 on t2.regionid=t3.id left join" . tablename('xcommunity_member_bind') . "t5 on t5.memberid = t2.id $cont where $condition and t1.mobile <> '' and t5.id <> '' order by t2.id desc ";
    $total = pdo_fetchcolumn($tsql, $params);
    $pager = pagination($total, $pindex, $psize);

//    $pindex = max(1, intval($_GPC['page']));
//    $psize = 20;
//    $condition = [];
//    $condition['uniacid'] = $_W['uniacid'];
//    $list = pdo_getslice('xcommunity_member', $condition, array($pindex, $psize), $total, '', '', array('createtime desc'));
    include $this->template('web/core/member/list');

}
/**
 * 修改住户信息
 */
if ($op == 'add') {
    //查看住户信息
    $id = intval($_GPC['id']);
    $regionid = intval($_GPC['regionid']);
    $memberid = intval($_GPC['memberid']);

    if ($id) {
        $sql = "select t3.realname,t3.mobile,t1.status,t1.addressid,t3.uid,t4.areaid,t4.buildid,t4.unitid,t4.id from" . tablename('xcommunity_member_bind') . "t1 left join" . tablename('xcommunity_member') . "t2 on t1.memberid = t2.id left join" . tablename('mc_members') . "t3 on t3.uid = t2.uid left join" . tablename('xcommunity_member_room') . "t4 on t4.id=t1.addressid where t1.id=:id";
        $item = pdo_fetch($sql, array(':id' => $id));
        $arr = util::xqset($regionid);
        if ($arr[a]) {
            $areas = pdo_getall('xcommunity_area', array('regionid' => $regionid), array('id', 'title'));

        }
        if ($arr[b]) {
            $condition = " t1.regionid=:regionid";
            $param[':regionid'] = $regionid;

            $condition .= " and t1.areaid=:areaid";
            $param[':areaid'] = $item['areaid'] ? $item['areaid'] : 0;

            $builds = pdo_fetchall("select t1.*,t2.title from" . tablename('xcommunity_build') . "t1 left join " . tablename('xcommunity_area') . "t2 on t1.areaid=t2.id where $condition", $param);
        }
        if ($arr[c]) {
            $condition_unit = " buildid=:buildid";
            $param_unit[':buildid'] = $item['buildid'];
            $units = pdo_fetchall("select * from" . tablename('xcommunity_unit') . "where $condition_unit", $param_unit);
        }
        if ($arr[d]) {
            $condition_room = " unitid=:unitid";
            $param_room[':unitid'] = $item['unitid'];
            $rooms = pdo_fetchall("select * from" . tablename('xcommunity_member_room') . "where $condition_room", $param_room);
        }
    }
    if ($_W['isajax']) {
        $dat = array(
            'addressid'  => intval($_GPC['addressid']),
            'status'     => intval($_GPC['status']),
            'createtime' => TIMESTAMP
        );
        if (trim($_GPC['mobile']) != $item['mobile']) {
            if (model_user::member_check(trim($_GPC['mobile']))) {
                echo json_encode(array('content' => '请更换修改手机号'));
                exit();
            }
        }
        if (empty($id)) {
            $dat['memberid'] = $memberid;
            $dat['enable'] = 1;
            $dat['uniacid'] = $_W['uniacid'];
            $r1 = pdo_insert('xcommunity_member_bind', $dat);
        } else {
            $r1 = pdo_update('xcommunity_member_bind', $dat, array('id' => $id));
        }
        pdo_update('xcommunity_member', array('regionid' => trim($_GPC['regionid']), 'visit' => 0), array('id' => $memberid));
        $r2 = pdo_update('mc_members', array('createtime' => TIMESTAMP, 'realname' => trim($_GPC['realname']), 'mobile' => trim($_GPC['mobile'])), array('uid' => intval($_GPC['uid'])));
        if ($r1 || $r2) {
            echo json_encode(array('status' => 1));
            exit();
        }

    }

    include $this->template('web/core/member/add');
}
/**
 * 删除用户
 */
if ($op == 'delete') {
    //删除用户
    $id = intval($_GPC['id']);
    if (empty($id)) {
        exit('缺少参数');
    }
    $item = pdo_fetch("select t1.id,t2.realname,t1.uid,t1.regionid from" . tablename('xcommunity_member') . "t1 left join" . tablename('mc_members') . "t2 on t1.uid = t2.uid where t1.id=:id", array(':id' => $id));
    if (empty($item)) {
        itoast('信息不存在或已删除', ture);
        exit();
    }
    pdo_delete('xcommunity_member_family', array('from_uid' => $item['uid']));
    pdo_delete('xcommunity_member_family', array('to_uid' => $item['uid']));
    $r = pdo_delete("xcommunity_member", array('id' => $id));
    pdo_delete('xcommunity_member_bind', array('id' => $id));
    // 删除绑定门禁
    $doorid = pdo_getcolumn('xcommunity_bind_door', array('uid' => $item['uid'], 'regionid' => $item['regionid']), 'id');
    pdo_delete('xcommunity_bind_door_device', array('doorid' => $doorid));
    pdo_delete('xcommunity_bind_door', array('id' => $doorid));
    if ($r) {
        util::permlog('用户管理-删除', '删除用户姓名:' . $item['realname']);
        itoast('删除成功', referer(), 'success', ture);
    }

}
/**
 * 审核用户
 */
if ($op == 'verify') {
    //审核用户
    $id = intval($_GPC['id']);
    $type = $_GPC['type'];
    $data = intval($_GPC['data']);
    if ($type == 'status') {
        $first = '您好，您的资料已经通过审核。';
    }
    if ($type == 'open_status') {
        $first = '您好，您的开门权限已经通过审核。';
    }
    if (in_array($type, array('status', 'open_status'))) {
        $sql = "select t1.realname,t1.mobile,t3.openid,t1.uid from" . tablename('mc_members') . "t1 left join" . tablename('xcommunity_member') . "t2 on t2.uid=t1.uid left join" . tablename('mc_mapping_fans') . "t3 on t3.uid = t1.uid where t2.id =:id";
        $member = pdo_fetch($sql, array(':id' => $id));

        $data = ($data == 1 ? '0' : '1');
        if ($data == 1) {
            if (set('t19')) {
                $createtime = date('Y-m-d H:i', TIMESTAMP);
                $content = array(
                    'first'    => array(
                        'value' => $first,
                    ),
                    'keyword1' => array(
                        'value' => $member['realname'],
                    ),
                    'keyword2' => array(
                        'value' => $member['mobile'],
                    ),
                    'keyword3' => array(
                        'value' => $createtime,
                    ),
                    'remark'   => array(
                        'value' => '感谢你的使用。',
                    ),
                );
                $tplid = set('t20');
                $openid = $member['openid'];
                util::sendTplNotice($openid, $tplid, $content, $url = '', $color = '');
            }
        }

        //审核通过，改当前用户所有的绑定为0;
        if ($data == 1 && $type == 'status') {
            $users = pdo_getall(('xcommunity_member'), array('uid' => $member['uid']), array('id'));
            if ($users) {
                if (pdo_update('xcommunity_member', array('enable' => 0), array('uid' => $member['uid']))) {
                    pdo_update('xcommunity_member', array('enable' => 1), array('id' => $id));
                }
            }
        } else {
            pdo_update('xcommunity_member', array('enable' => 1), array('id' => $id));
        }

        pdo_update("xcommunity_member", array($type => $data), array("id" => $id, "uniacid" => $_W['uniacid']));
        die(json_encode(array("result" => 1, "data" => $data)));
    }
}
/**
 * 未知
 */
if ($op == 'bind') {
    set_time_limit(0);

    $condition = ' t1.uniacid=:uniacid';
    $params[':uniacid'] = $_W['uniacid'];
    $memberid = intval($_GPC['memberid']);
    if ($memberid) {
        $condition .= " and t2.memberid=:memberid";
        $params[':memberid'] = $memberid;
    }

    $status = intval($_GPC['status']);
    if ($status) {
        $condition .= " and t2.status=:status";
        $params[':status'] = $status;
    }
    $keyword = trim($_GPC['keyword']);
    if ($keyword) {
        $condition .= " and (t4.mobile=:keyword or t4.realname=:keyword or t3.address=:keyword or t3.address like '%{$_GPC['keyword']}%')";
        $params[':keyword'] = $keyword;
    }
    $regionid = intval($_GPC['regionid']);
    if ($regionid) {
        $condition .= " and t1.regionid=:regionid";
        $params[':regionid'] = $regionid;
    } else {
        if ($user[type] == 3) {
            //小区管理员
            $condition .= " and t1.regionid in({$user['regionid']})";
        }
    }

    if ($_GPC['export'] == 1) {
        $sql = "select t2.id,t3.address,t4.realname,t4.mobile,t2.createtime,t2.status,t5.title from" . tablename('xcommunity_member') . "t1 left join" . tablename('xcommunity_member_bind') . "t2 on t1.id = t2.memberid left join" . tablename('xcommunity_member_room') . "t3 on t2.addressid = t3.id left join" . tablename('mc_members') . "t4 on t4.uid = t1.uid left join" . tablename('xcommunity_region') . "t5 on t5.id = t1.regionid where $condition order by t2.createtime desc ";
        $xqlist = pdo_fetchall($sql, $params);

        foreach ($xqlist as $k => $v) {

            $xqlist[$k]['cctime'] = date('Y-m-d H:i', $v['createtime']);
            if ($v['status'] == 1) {
                $xqstatus = '户主';
            } elseif ($v['status'] == 2) {
                $xqstatus = '家属';
            } elseif ($v['status'] == 3) {
                $xqstatus = '租户';
            } else {
                $xqstatus = '未绑定';
            }
            $xqlist[$k]['xqstatus'] = $xqstatus;
        }
        model_execl::export($xqlist, array(
            "title"   => "房号数据-" . date('Y-m-d-H-i', time()),
            "columns" => array(
                array(
                    'title' => '小区名称',
                    'field' => 'title',
                    'width' => 12
                ),
                array(
                    'title' => '姓名',
                    'field' => 'realname',
                    'width' => 12
                ),
                array(
                    'title' => '手机号',
                    'field' => 'mobile',
                    'width' => 12
                ),
                array(
                    'title' => '车牌号',
                    'field' => 'license',
                    'width' => 12
                ),
                array(
                    'title' => '地址',
                    'field' => 'address',
                    'width' => 18
                ),
                array(
                    'title' => '时间',
                    'field' => 'cctime',
                    'width' => 18
                ),
                array(
                    'title' => '状态',
                    'field' => 'xqstatus',
                    'width' => 18
                ),
                array(
                    'title' => '注册码',
                    'field' => 'code',
                    'width' => 18
                ),
            )
        ));
    }

    $pindex = max(1, intval($_GPC['page']));
    $psize = 20;
    $sql = "select t2.id,t3.address,t4.realname,t4.mobile,t2.createtime,t2.status,t5.title,t3.regionid from" . tablename('xcommunity_member') . "t1 left join" . tablename('xcommunity_member_bind') . "t2 on t1.id = t2.memberid left join" . tablename('xcommunity_member_room') . "t3 on t2.addressid = t3.id left join" . tablename('mc_members') . "t4 on t4.uid = t1.uid left join" . tablename('xcommunity_region') . "t5 on t5.id = t1.regionid where $condition order by t2.createtime desc LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
    $list = pdo_fetchall($sql, $params);

    $tsql = "select count(*) from" . tablename('xcommunity_member') . "t1 left join" . tablename('xcommunity_member_bind') . "t2 on t1.id = t2.memberid left join" . tablename('xcommunity_member_room') . "t3 on t2.addressid = t3.id left join" . tablename('mc_members') . "t4 on t4.uid = t1.uid left join" . tablename('xcommunity_region') . "t5 on t5.id = t1.regionid where $condition order by t2.createtime desc ";
    $total = pdo_fetchcolumn($tsql, $params);

    $pager = pagination($total, $pindex, $psize);


    include $this->template('web/core/member/bind');
}
/**
 * 用户绑定门禁
 */
if ($op == 'binddoor') {
    if ($_W['isajax']) {
        $regionid = intval($_GPC['regionid']);
        if (empty($regionid)) {
            echo json_encode(array('status' => 1, 'content' => '缺少参数'));
            exit();
        }
        $uid = trim($_GPC['uid']);
        $doors = pdo_fetchall("select b.deviceid from" . tablename('xcommunity_bind_door') . "as d left join" . tablename('xcommunity_bind_door_device') . "as b on d.id=b.doorid where d.uid=:uid ", array(":uid" => $uid));

        $d = array();
        foreach ($doors as $key => $val) {
            $d[] = $val['deviceid'];
        }
        //$devices = pdo_getall('xcommunity_building_device', array('regionid' => $regionid, 'uniacid' => $_W['uniacid']), array('title', 'id', 'unit'));
        $sql = "select * from" . tablename('xcommunity_building_device') . "where regionid=:regionid and uniacid=:uniacid order by displayorder asc ";
        $devices = pdo_fetchall($sql, array(':regionid' => $regionid, ':uniacid' => $_W['uniacid']));
        $content = '';
        foreach ($devices as $key => $item) {
            if (in_array($item['id'], $d)) {
                $check = 'checked';
                $devices[$key]['check'] = $check;
            }
        }
        if ($devices) {
            if ($door) {
                echo json_encode(array('status' => 2, 'content' => $devices, 'result' => $door['deviceid']));
                exit();
            } else {
                echo json_encode(array('status' => 2, 'content' => $devices));
                exit();
            }

        } else {
            echo json_encode(array('status' => 3, 'content' => '该小区未开通微信开门权限'));
            exit();
        }
    }

}
/**
 * 删除用户的房号
 */
if ($op == 'del') {
    $id = intval($_GPC['id']);
    if (empty($id)) {
        exit('缺少参数');
    }
    $item = pdo_get('xcommunity_member_bind', array('id' => $id), array('id', 'memberid', 'addressid'));
    $room = pdo_get('xcommunity_member_room', array('id' => $item['addressid']), array('regionid', 'address'));
    if ($item) {
        $member = pdo_get('xcommunity_member', array('id' => $item['memberid']), array());
        if ($member) {
            $total = pdo_fetchcolumn('select count(id) from ' . tablename('xcommunity_member_bind') . " where memberid=:memberid", array(':memberid' => $item['memberid']));
            pdo_delete('xcommunity_member_family', array('from_uid' => $member['uid'], 'addressid' => $item['addressid']));
            pdo_delete('xcommunity_member_family', array('to_uid' => $member['uid'], 'addressid' => $item['addressid']));
            $binds = pdo_getall('xcommunity_bind_door', array('uid' => $member['uid'], 'regionid' => $room['regionid']));
            if ($total == 1 && $binds) {
                foreach ($binds as $k => $v) {
                    pdo_delete('xcommunity_bind_door_device', array('doorid' => $v['id']));
                    pdo_delete('xcommunity_bind_door', array('id' => $v['id']));
                }
                pdo_delete('xcommunity_face_users', array('uid' => $member['uid']));
            }
            if (pdo_delete('xcommunity_member_bind', array('id' => $id))) {
                $bindid = pdo_getcolumn('xcommunity_member_bind', array('addressid' => $item['addressid']), 'id');
                if (empty($bindid)) {
                    pdo_update('xcommunity_member_room', array('enable' => 0), array('id' => $item['addressid']));
                }
                util::permlog('房号管理-删除', '删除用户房号:' . $room['address']);
                itoast('删除成功', referer(), 'success', ture);
            }
        }
    }

}
/**
 * 游客
 */
if ($op == 'visit') {
    $pindex = max(1, intval($_GPC['page']));
    $psize = 20;

    if (checksubmit('del')) {
        $ids = $_GPC['ids'];
        if (!empty($ids)) {
            foreach ($ids as $key => $id) {
                pdo_delete('xcommunity_member', array('id' => $id));
            }
        }
        util::permlog('', '批量删除游客信息');
        itoast('删除成功', referer(), 'success');
    }
    $condition = " t1.uniacid =:uniacid and t1.visit =1 ";
    $params[':uniacid'] = $_W['uniacid'];
    if (!empty($_GPC['keyword'])) {
        $condition .= " AND (t2.nickname LIKE '%{$_GPC['keyword']}%' or t2.realname LIKE '%{$_GPC['keyword']}%')";
    }

    if (intval($_GPC['regionid'])) {
        $condition .= " AND t1.regionid =:regionid";
        $params[':regionid'] = intval($_GPC['regionid']);
    }
    if ($user[type] == 3) {
        //普通管理员
        $condition .= " and t1.regionid in({$user['regionid']})";
    }
    $sql = "select t1.*,t2.nickname,t3.title from" . tablename('xcommunity_member') . "t1 left join" . tablename('mc_members') . "t2 on t1.uid=t2.uid left join" . tablename('xcommunity_region') . "t3 on t3.id = t1.regionid  where $condition order by t1.createtime desc LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
    $list = pdo_fetchall($sql, $params);
    $tsql = "select count(*) from" . tablename('xcommunity_member') . "t1 left join" . tablename('mc_members') . "t2 on t1.uid=t2.uid left join" . tablename('xcommunity_region') . "t3 on t3.id = t1.regionid where $condition";
    $total = pdo_fetchcolumn($tsql, $params);

    $pager = pagination($total, $pindex, $psize);
    include $this->template('web/core/member/visit');
}
/**
 * 房号信息
 */
if ($op == 'room') {
    $regionid = intval($_GPC['regionid']);
    $arr = util::xqset($regionid);
    if ($arr[a]) {
        $areas = pdo_getall('xcommunity_area', array('regionid' => $regionid), array('id', 'title'));
    }
    if ($arr[b]) {
        $condition = " t1.regionid=:regionid";
        $param[':regionid'] = $regionid;
        $areaid = intval($_GPC['areaid']);
        if ($areaid) {
            $condition .= " and t1.areaid=:areaid";
            $param[':areaid'] = $areaid;
        }
        $builds = pdo_fetchall("select t1.*,t2.title from" . tablename('xcommunity_build') . "t1 left join " . tablename('xcommunity_area') . "t2 on t1.areaid=t2.id where $condition order by t1.id asc ", $param);
        foreach ($builds as $k => $v) {
            $builds[$k]['title'] = $v['title'] ? $v['title'] . $arr[a1] : '';
        }
    }
    if ($arr[c]) {
        $condition_unit = " buildid=:buildid";
        $param_unit[':buildid'] = intval($_GPC['buildid']);
        $units = pdo_fetchall("select * from" . tablename('xcommunity_unit') . "where $condition_unit order by id asc ", $param_unit);
    }
    if ($arr[d]) {
        $condition_room = " unitid=:unitid";
        $param_room[':unitid'] = intval($_GPC['unitid']);
        $rooms = pdo_fetchall("select * from" . tablename('xcommunity_member_room') . "where $condition_room order by id asc ", $param_room);
    }
    $p = trim($_GPC['p']);
    $data = array();
    if ($p == 'region') {
        if ($arr[a]) {
            $data['list'] = $areas;
            $data['zd'] = $arr[a1];
            $data['kg'] = 1;
        } else {
            $data['list'] = $builds;
            $data['zd'] = $arr[b1];
        }
    }
    if ($p == 'build') {
        $data['list'] = $builds;
        $data['zd'] = $arr[b1];
    }
    if ($p == 'unit') {
        $data['list'] = $units;
        $data['zd'] = $arr[c1];
    }
    if ($p == 'room') {
        $data['list'] = $rooms;
        $data['zd'] = $arr[d1];
    }
    echo json_encode($data);
}
/**
 * 设置门禁时间（已经移到门禁）
 */
if ($op == 'open') {
    $id = intval($_GPC['id']);
    if ($id) {
        $item = pdo_get('xcommunity_member', array('id' => $id), array('opentime'));
        $opentime = !empty($item['opentime']) ? date('Y-m-d', $item['opentime']) : date('Y-m-d', TIMESTAMP);
    }

    if (checksubmit('submit')) {
        if (pdo_update('xcommunity_member', array('opentime' => strtotime($_GPC['opentime'])), array('id' => $id))) {
            itoast('修改成功', referer(), 'success');
        }
    }
    include $this->template('web/core/member/open');
}
/**
 * 用户添加房号
 */
if ($op == 'post') {
    $id = intval($_GPC['id']);
    if ($_W['isajax']) {
        $addressid = intval($_GPC['addressid']);
        $enable = intval($_GPC['enable']);
        $regionid = intval($_GPC['regionid']);
        $member = pdo_get('xcommunity_member', array('id' => $id, 'uniacid' => $_W['uniacid']));
        $memberr = pdo_get('xcommunity_member', array('uniacid' => $_W['uniacid'], 'uid' => $member['uid'], 'regionid' => $regionid));
        if (($regionid == $member['regionid']) || !empty($memberr)) {
            if (!empty($memberr)) {
                $id = $memberr['id'];
            }
            $item = pdo_get('xcommunity_member_bind', array('addressid' => $addressid, 'memberid' => $id), array('id'));
            if (empty($item)) {
                $dat = array(
                    'addressid'  => $addressid,
                    'status'     => intval($_GPC['status']),
                    'createtime' => TIMESTAMP,
                    'memberid'   => $id,
                    'uniacid'    => $_W['uniacid'],
                );
                if ($enable == 1) {
                    pdo_update('xcommunity_member_bind', array('enable' => 0), array('memberid' => $id));
                }
                $dat['enable'] = $enable;
                if (pdo_insert('xcommunity_member_bind', $dat)) {
                    pdo_update('xcommunity_member_room', array('enable' => 1), array('id' => $addressid));
                    echo json_encode(array('status' => 1));
                    exit();
                }
            } else {
                echo json_encode(array('content' => '无需重复绑定'));
                exit();
            }
        } else {
            $data = array(
                'uniacid'    => $_W['uniacid'],
                'regionid'   => $regionid,
                'status'     => 1,
                'enable'     => 0,
                'uid'        => $member['uid'],
                'visit'      => 0,
                'createtime' => TIMESTAMP
            );
            pdo_insert('xcommunity_member', $data);
            $id = pdo_insertid();
            $da = array(
                'addressid'  => $addressid,
                'status'     => intval($_GPC['status']),
                'createtime' => TIMESTAMP,
                'memberid'   => $id,
                'uniacid'    => $_W['uniacid'],
                'enable'     => $enable
            );
            if (pdo_insert('xcommunity_member_bind', $da)) {
                pdo_update('xcommunity_member_room', array('enable' => 1), array('id' => $addressid));
                echo json_encode(array('status' => 1));
                exit();
            }
        }
    }
    include $this->template('web/core/member/post');
}
/**
 * 切换房号的默认
 */
if ($op == 'change') {
    $id = intval($_GPC['id']);
    $memberid = intval($_GPC['memberid']);
    $enable = intval($_GPC['enable']) == 1 ? 0 : 1;
    if (empty($id)) {
        itoast('id 为空', referer(), 'success');
        exit();
    }
    $total = pdo_fetchcolumn("select count(*) from" . tablename('xcommunity_member_bind') . "where memberid=:memberid", array(':memberid' => $memberid));
    if ($total == 1) {
        pdo_update('xcommunity_member_bind', array('enable' => 1), array('memberid' => $memberid, 'id' => $id));
        itoast('切换成功', '', 'success');
        return false;
    }
    //请求为 1 所有地址设置为0
    if ($enable == 1) {
        pdo_update('xcommunity_member_bind', array('enable' => 0), array('memberid' => $memberid));
    }
    //根据请求设置
    pdo_update('xcommunity_member_bind', array('enable' => $enable), array('memberid' => $memberid, 'id' => $id));
    itoast('切换成功', '', 'success');
}
/**
 * 获取门禁分组
 */
if ($op == 'group') {
    if ($_W['isajax']) {
        $regionid = intval($_GPC['regionid']);
        if (empty($regionid)) {
            echo json_encode(array('status' => 1, 'content' => '缺少参数'));
            exit();
        }
        $uid = trim($_GPC['uid']);
        $groupid = pdo_getcolumn('xcommunity_member', array('uid' => $uid), 'groupid');
        $groups = pdo_getall('xcommunity_guard_group', array('regionid' => $regionid), array('title', 'id'));
        foreach ($groups as $key => $item) {
            if ($item['id'] == $groupid) {
                $groups[$key]['select'] = 'selected';
            }
        }
        if ($groups) {
            echo json_encode(array('status' => 2, 'content' => $groups));
            exit();
        } else {
//            echo json_encode(array('status' => 3, 'content' => '该小区未设置门禁套餐'));
//            exit();
        }
    }
}
/**
 * 批量审核用户开门
 */
if ($op == 'pldoor') {
    $pindex = max(1, intval($_GPC['page']));
    $psize = 50;
    //批量开通单元门
    $con = " t2.uniacid =:uniacid";
    $par[':uniacid'] = $_W['uniacid'];
    if ($_GPC['ids']) {
        $ids = implode(',', $_GPC['ids']);
        $con .= " and t2.id in({$ids})";
    }
    if ($user[type] == 3) {
        //普通管理员
        $con .= " and t2.regionid in({$user['regionid']})";
    }
    $sql = "select t2.status,t2.open_status,t2.id,t2.regionid,t2.uid from" . tablename('xcommunity_member') . "as t2 where $con order by t2.createtime desc LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
    $members = pdo_fetchall($sql, $par);

    $tsql = "select count(*) from" . tablename('xcommunity_member') . "as t2 where $con order by t2.createtime desc ";
    $total = pdo_fetchcolumn($tsql, $par);

    $record = intval($_GPC['record']) ? intval($_GPC['record']) : 0;
    $ok = intval($_GPC['ok']) ? intval($_GPC['ok']) : 0;
    $fail = intval($_GPC['fail']) ? intval($_GPC['fail']) : 0;
    foreach ($members as $k => $v) {
        $record++;//已发送记录
        $ok++;//成功发送
        pdo_update('xcommunity_member', array('open_status' => 1), array('id' => $v['id']));
        $door = pdo_get('xcommunity_bind_door', array('uid' => $v['uid'], 'regionid' => $v['regionid']), array('id'));
        if (empty($door)) {
            $data = array(
                'regionid' => $v['regionid'],
                'uid'      => $v['uid']
            );

            $data['uniacid'] = $_W['uniacid'];
            pdo_insert('xcommunity_bind_door', $data);
            $id = pdo_insertid();
        } else {
            $id = $door['id'];
            // pdo_delete('xcommunity_bind_door_device', array('doorid' => $id));
        }
        $sql = "select t1.unit,t1.build,t1.regionid from" . tablename('xcommunity_member_room') . "t1 left join" . tablename('xcommunity_member_bind') . "t2 on t1.id=t2.addressid where t2.memberid=:memberid";
        $room = pdo_fetch($sql, array(':memberid' => $v['id']));
        $arr = util::xqset($room['regionid']);
        $title = $room['build'] . $arr['b1'];
        $unit = $room['unit'] . $arr['c1'];
        $devices = pdo_getall('xcommunity_building_device', array('regionid' => $v['regionid'], 'type' => 1, 'title' => $title, 'unit' => $unit), array('id'));
        foreach ($devices as $key => $value) {
            $item = pdo_get('xcommunity_bind_door_device', array('doorid' => $id, 'deviceid' => $value['id']), array('id'));
            if (empty($item)) {
                $dat = array(
                    'doorid'   => $id,
                    'deviceid' => $value['id'],
                );
                pdo_insert('xcommunity_bind_door_device', $dat);
            }
        }
    }
    if ($ok == $total || empty($members)) {
        echo json_encode(array('status' => 'end'));
        exit();
    } else {
        echo json_encode(array('fail' => $fail, 'ok' => $ok, 'record' => $record, 'total' => $total));
        exit();
    }
    util::permlog('', '批量审核业主开门');
    itoast('审核成功', referer(), 'success', ture);

}
/**
 * 用户管理
 */
if ($op == 'users') {
    // 批量删除
    if (checksubmit('del')) {
        $ids = $_GPC['ids'];
        if (!empty($ids)) {
            pdo_delete('xcommunity_member', array('id' => $ids));
            $idss = implode(',', $ids);
            util::permlog('', '批量删除用户(' . $idss . ')');
            itoast('删除成功', referer(), 'success', true);
        }
    }
    $pindex = max(1, intval($_GPC['page']));
    $psize = 20;
    $condition = array();
    $condition['uniacid'] = $_W['uniacid'];
    $condition['visit'] = 0;
    if (!empty($_GPC['realname'])) {
        $condition['realname like'] = "%{$_GPC['realname']}%";
    }
    if (!empty($_GPC['mobile'])) {
        $condition['realname mobile'] = "%{$_GPC['mobile']}%";
    }
    if (intval($_GPC['regionid'])) {
        $condition['regionid'] = intval($_GPC['regionid']);
    } else {
        if ($user && $user['type'] == 3) {
            //小区管理员
            $userRegionid = explode(',', $user['regionid']);
            $condition['regionid'] = $userRegionid;
        }
    }
    // 绑定的房屋信息
    $binds = pdo_getall('xcommunity_member_bind', array('uniacid' => $_W['uniacid']), array('id', 'memberid'), 'memberid');
    $binds_memberids = _array_column($binds, 'memberid');
    $members = pdo_getslice('xcommunity_member', $condition, array($pindex, $psize), $total, '', '', array('createtime desc'));
    // 会员信息
    $members_uids = _array_column($members, 'uid');
    $mcMembers = pdo_getall('mc_members', array('uid' => $members_uids), array('uid', 'realname', 'mobile'), 'uid');
    $fans = pdo_getall('mc_mapping_fans', array('uid' => $members_uids), array('uid', 'openid'), 'uid');
    // 小区信息
    $members_regionids = _array_column($members, 'regionid');
    $memberRegions = pdo_getall('xcommunity_region', array('uid' => $members_regionids), array('id', 'title'), 'id');
    $list = array();
    foreach ($members as $k => $v) {
        if (!in_array($v['id'], $binds_memberids)) {
            $list[] = array(
                'id'         => $v['id'],
                'realname'   => $mcMembers[$v['uid']]['realname'],
                'mobile'     => $mcMembers[$v['uid']]['mobile'],
                'openid'     => $fans[$v['uid']]['openid'],
                'title'      => $memberRegions[$v['regionid']]['title'],
                'status'     => $v['status'],
                'createtime' => date('Y-m-d H:i', $v['createtime'])
            );
        } else {
            // 不符合条件的扣除总数
            $total--;
        }
    }
    $pager = pagination($total, $pindex, $psize);
    include $this->template('web/core/member/users');
}
/**
 * 删除用户
 */
if ($op == 'usersDel') {
    //删除用户
    $id = intval($_GPC['id']);
    if (empty($id)) {
        exit('缺少参数');
    }
    $item = pdo_get('xcommunity_member', array('id' => $id), array());
    $realname = pdo_getcolumn('mc_members', array('uid' => $item['uid']), 'realname');
    if (empty($item)) {
        itoast('信息不存在或已删除', ture);
        exit();
    }
    $r = pdo_delete("xcommunity_member", array('id' => $id));
    if ($r) {
        util::permlog('用户管理-删除', '删除用户姓名:' . $realname);
        itoast('删除成功', referer(), 'success', ture);
    }
}