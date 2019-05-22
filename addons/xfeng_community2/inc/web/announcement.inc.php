<?php
/**
 * 小区秘书模块
 *
 * [蓝牛科技] Copyright (c) 2013 xqms.cn
 */
/**
 * 后台小区公告信息
 */
global $_GPC, $_W;
$op = !empty($_GPC['op']) ? $_GPC['op'] : 'list';
$id = intval($_GPC['id']);
// 操作员
$user = util::xquser($_W['uid']);
if ($user && $user['menu_ops']) {
    $menu_opss = explode(',', $user['menu_ops']);
}
if ($op == 'list') {
    //删除
    if ($_W['ispost']) {
        $ids = $_GPC['ids'];
        if (!empty($ids)) {
            foreach ($ids as $key => $id) {
                pdo_delete('xcommunity_announcement', array('id' => $id));
            }
            util::permlog('', '批量删除小区公告');
            itoast('删除成功', referer(), 'success');
        }
    }
    $pindex = max(1, intval($_GPC['page']));
    $psize = 10;
    $condition = array();
    $condition['uniacid'] = $_W['uniacid'];
    $condition['status'] = 2;
    if (!empty($_GPC['keyword'])) {
        $condition['title like'] = "%{$_GPC['keyword']}%";
    }
    if ($user) {
        if ($user['type'] == 2) {
            //普通管理员
            $condition['uid'] = $_W['uid'];
        }
        if ($user['type'] == 3) {
            $regionids = explode(',', $user['regionid']);
            $regs = pdo_getall('xcommunity_announcement_region', array('regionid' => $regionids), array('aid'));
            $aids = _array_column($regs, 'aid');
            $condition['id'] = $aids;
        }
    }
    $list = pdo_getslice('xcommunity_announcement', $condition, array($pindex, $psize), $total, '', '', array('createtime desc'));
    $pager = pagination($total, $pindex, $psize);
    include $this->template('web/core/announcement/list');
}
elseif ($op == 'add') {
    $regionids = '[]';
    $regions = model_region::region_fetall();
    if (!empty($id)) {
        $item = pdo_fetch("SELECT * FROM" . tablename('xcommunity_announcement') . "WHERE id=:id", array(':id' => $id));
        $thumbs = explode(',', $item['pic']);
        if ($item['type'] == 1) {
            $regs = pdo_getall('xcommunity_announcement_region', array('aid' => $id), array('regionid'));
            $regionid = array();
            foreach ($regs as $key => $val) {
                $regionid[] = $val['regionid'];
            }
            $regionids = json_encode($regionid);
        }
        else {
            $builds = pdo_getall('xcommunity_build', array('regionid' => $item['regionid']), array());
            foreach ($builds as $k => $v) {
                if (set('p55')) {
                    if (set('p36')) {
                        $area = set('p37');
                    }
                }
                else {
                    if (set('x17', $item['regionid'])) {
                        $area = set('x46', $item['regionid']);
                    }
                }
                if ($v['areaid']) {
                    $areatitle = pdo_getcolumn('xcommunity_area', array('id' => $v['areaid']), 'title');
                    $builds[$k]['buildtitle'] = $areatitle . $area . $v['buildtitle'];
                }
            }
            $regs = pdo_getall('xcommunity_announcement_build', array('aid' => $id), array('buildid'));
            $buildids = array();
            foreach ($regs as $key => $val) {
                $buildids[] = $val['buildid'];
            }
//            $builds = json_encode($buildids);
        }

    }
    if (($item['type'] == 1 || empty($item['type'])) && $item['allregion'] == 2) {
//        echo 1;
    }
    //添加公告
    if ($_W['isajax']) {
        $type = intval($_GPC['type']);
        $pic = '';
        if ($_GPC['thumbs']) {
            $pic = implode(',', $_GPC['thumbs']);
        }
        $data = array(
            'uniacid'    => $_W['uniacid'],
            'title'      => $_GPC['title'],
            'createtime' => TIMESTAMP,
            'reason'     => htmlspecialchars_decode($_GPC['reason']),
            'remark'     => $_GPC['remark'],
            'status'     => 2,
            'enable'     => 1,//1显示2隐藏
            'type'       => $type,
            'pic'        => $pic
        );
        if ($type == 1) {
            $birth = $_GPC['birth'];
            $allregion = intval($_GPC['allregion']);
            if ($allregion == 1) {

            }
            else {
                if (empty($birth['province'])) {
                    echo json_encode(array('content' => '必须选择省市区和小区'));
                    exit();
                }
                if (empty($_GPC['regionids'])) {
                    echo json_encode(array('content' => '必选选择小区'));
                    exit();
                }
            }
            $regionids = explode(',', $_GPC['regionids']);
            $data['province'] = $birth['province'];
            $data['city'] = $birth['city'];
            $data['dist'] = $birth['dist'];
            $data['allregion'] = $allregion;
        }
        else {
            $data['regionid'] = intval($_GPC['regionid']);
        }
        if (empty($id)) {
            $data['uid'] = $_W['uid'];
            pdo_insert("xcommunity_announcement", $data);
            $id = pdo_insertid();
            util::permlog('小区公告-添加', '信息标题:' . $data['title']);
        }
        else {
            pdo_update("xcommunity_announcement", $data, array('id' => $id));
            if ($data['type'] == 1) {
                pdo_delete('xcommunity_announcement_region', array('aid' => $id));
            }
            else {
                pdo_delete('xcommunity_announcement_region', array('aid' => $id));
                pdo_delete('xcommunity_announcement_build', array('aid' => $id));
            }

            util::permlog('小区公告-修改', '信息标题:' . $data['title']);
        }
        if ($type == 1) {
            if ($allregion == 1) {
                $regions = model_region::region_fetall();
                foreach ($regions as $k => $v) {
                    $dat = array(
                        'aid'      => $id,
                        'regionid' => $v['id'],
                    );
                    pdo_insert('xcommunity_announcement_region', $dat);
                }
            }
            else {
                foreach ($regionids as $key => $value) {
                    $dat = array(
                        'aid'      => $id,
                        'regionid' => $value,
                    );
                    pdo_insert('xcommunity_announcement_region', $dat);
                }
            }
        }
        else {
            $builds = $_GPC['builds'];
            foreach ($builds as $key => $value) {
                $dat = array(
                    'aid'     => $id,
                    'buildid' => $value,
                );
                pdo_insert('xcommunity_announcement_build', $dat);
            }
            $d = array(
                'aid'      => $id,
                'regionid' => intval($_GPC['regionid']),
            );
            pdo_insert('xcommunity_announcement_region', $d);
        }

        if (set('p53')) {
            if ($_GPC['regionids']) {
//                $regionids = implode(',', $_GPC['regionids']);
                $regionids = $_GPC['regionids'];
                $sql = "select * from" . tablename('xcommunity_member') . "where regionid in({$regionids}) group by uid";
                $users = pdo_fetchall($sql);
                foreach ($users as $key => $val) {
                    util::app_send($val['uid'], $data['title']);
                }
                util::permlog('小区公告-推送', '信息标题:' . $data['title']);
            }
        }
        echo json_encode(array('status' => 1));
        exit();
    }
    $options = array();
    $options['dest_dir'] = $_W['uid'] == 1 ? '' : MODULE_NAME . '/' . $_W['uid'];
    include $this->template('web/core/announcement/add');
}
elseif ($op == 'delete') {
    //删除公告
    if ($id) {
        $item = pdo_get('xcommunity_announcement', array('id' => $id), array());
        if ($item) {
            if (pdo_delete("xcommunity_announcement", array('id' => $id))) {
                util::permlog('小区公告-删除', '信息标题:' . $item['title']);
                if (pdo_delete('xcommunity_announcement_region', array('aid' => $id))) {
                    itoast('删除成功', referer(), 'success');
                }
            }
        }

    }
}
elseif ($op == 'send') {

    $t1 = set('t1');
    if (empty($t1)) {
        itoast('请先开启物业管理通知推送开关', $this->createWebUrl('tpl'), 'error');
        exit();
    }
    $id = intval($_GPC['id']);
    if (checksubmit('submit')) {
        $url = $this->createWebUrl('announcement', array('op' => 'wxsend', 'id' => $id));
        $this->xqmessage('正在发送,请勿关闭！', $url, 'success');
    }
    if ($id) {
        $item = pdo_get('xcommunity_announcement', array('id' => $id), array());
        $pindex = max(1, intval($_GPC['page']));
        $psize = 20;
        $condition['aid'] = $id;
        $cond = array();
        $cond['uniacid'] = $_W['uniacid'];
        $cond['visit'] = 0;
        if ($item['type'] == 1) {
            //按小区推送
            $list = pdo_getall('xcommunity_announcement_region', $condition, array('regionid'));
            $list_regionids = _array_column($list, 'regionid');
            $cond['regionid'] = $list_regionids;
        }
        else {
            $list = pdo_getall('xcommunity_announcement_build', $condition, array('buildid'));
            $list_buildids = _array_column($list, 'buildid');
            $cond['buildid'] = $list_buildids;
        }
        $members = pdo_getslice('xcommunity_member', $cond, array($pindex, $psize), $count, array(), '', array('id DESC'));
        //$members = pdo_getall('xcommunity_member',$cond,array('uid'), 'uid', array('uid ASC'), $psize);
        //一共发送
        pdo_getslice('xcommunity_send_log', array('sendid' => $id, 'type' => 1, 'cid' => 1), array($pindex, $psize), $total, array(), '', array('id DESC'));
        //$total = pdo_fetchcolumn("select count(*) from" . tablename('xcommunity_send_log') . "where sendid =:sendid and type = 1 and cid=1", array(':sendid' => $id));
        //成功
        pdo_getslice('xcommunity_send_log', array('sendid' => $id, 'type' => 1, 'cid' => 1, 'status' => 1), array($pindex, $psize), $total1, array(), '', array('id DESC'));
        //$total1 = pdo_fetchcolumn("select count(*) from" . tablename('xcommunity_send_log') . "where sendid =:sendid and status =1 and type = 1 and cid=1", array(':sendid' => $id));
        //失败
        $total2 = $total - $total1;
        if ($total) {
            $hhl = (($total1 / $total) * 100) . '%';
        }
    }
//    if ($id) {
//        $item = pdo_get('xcommunity_announcement', array('id' => $id), array());
//        if ($item['type'] == 1) {
//            $regs = pdo_getall('xcommunity_announcement_region', array('aid' => $id), array('regionid'));
//            $regionids = _array_column($regs, 'regionid');
//            if (empty($regionids)) {
//                itoast('当前通知未绑定小区，请先绑定小区', referer(), 'error');
//                exit();
//            }
//        }
//        else {
//            $regs = pdo_getall('xcommunity_announcement_build', array('aid' => $id), array('buildid'));
//            $buildids = _array_column($regs, 'buildid');
//        }
//    }
//    if ($item['type'] == 1) {
//        //可发送业主
//        $members = pdo_getall('xcommunity_member', array('uniacid' => $_W['uniacid'], 'regionid' => $regionids), 'regionid');
//    }
//    else {
//        $sql = "select t1.regionid,t2.mobile from" . tablename('xcommunity_member') . "t1 left join" . tablename('mc_members') . "t2 on t1.uid = t2.uid left join" . tablename('xcommunity_member_bind') . "t3 on t3.memberid= t1.id left join" . tablename('xcommunity_member_room') . "t4 on t4.id = t3.addressid where t4.buildid in($builds) and t1.uniacid =:uniacid ";
//        $rooms = pdo_getall('xcommunity_member_room', array('buildid' => $buildids), array('id'));
//        $rooms_ids = _array_column($rooms, 'id');
//        $binds = pdo_getall('xcommunity_member_bind', array('addressid' => $rooms_ids), array('memberid'));
//        $binds_ids = _array_column($binds, 'memberid');
//        $members = pdo_getall('xcommunity_member', array('uniacid' => $_W['uniacid'], 'id' => $binds_ids), 'regionid');
//    }
    //$count = count($members);


    include $this->template('web/core/announcement/send');
}
elseif ($op == 'change') {
    $id = intval($_GPC['id']);
    $enable = intval($_GPC['enable']);
    $enable = $enable == 2 || $enable == 0 ? 1 : 2;
    if ($id) {
        if (pdo_update('xcommunity_announcement', array('enable' => $enable), array('id' => $id))) {
            echo json_encode(array('status' => 1));
            exit();
        }
    }
}
elseif ($op == 'sms') {
    $id = intval($_GPC['id']);
    if ($id) {
        $item = pdo_get('xcommunity_announcement', array('id' => $id), array());
        if ($item['type'] == 1) {
            $regs = pdo_getall('xcommunity_announcement_region', array('aid' => $id), array('regionid'));
            $regionids = _array_column($regs, 'regionid');
        }
        else {
            $regs = pdo_getall('xcommunity_announcement_build', array('aid' => $id), array('buildid'));
            $buildids = _array_column($regs, 'buildid');
        }

    }
    if ($item['type'] == 1) {
        //可发送业主
        $members = pdo_getall('xcommunity_member', array('uniacid' => $_W['uniacid'], 'regionid' => $regionids), 'regionid');
    }
    else {
        $sql = "select t1.regionid,t2.mobile from" . tablename('xcommunity_member') . "t1 left join" . tablename('mc_members') . "t2 on t1.uid = t2.uid left join" . tablename('xcommunity_member_bind') . "t3 on t3.memberid= t1.id left join" . tablename('xcommunity_member_room') . "t4 on t4.id = t3.addressid where t4.buildid in($builds) and t1.uniacid =:uniacid ";
        $rooms = pdo_getall('xcommunity_member_room', array('buildid' => $buildids), array('id'));
        $rooms_ids = _array_column($rooms, 'id');
        $binds = pdo_getall('xcommunity_member_bind', array('addressid' => $rooms_ids), array('memberid'));
        $binds_ids = _array_column($binds, 'memberid');
        $members = pdo_getall('xcommunity_member', array('uniacid' => $_W['uniacid'], 'id' => $binds_ids), 'regionid');
    }
    $count = count($members);
    //一共发送
    $total = pdo_getcolumn('xcommunity_send_log', array('sendid' => $id, 'type' => 1, 'cid' => 2), 'count(*)');
    //成功
    $total1 = pdo_getcolumn('xcommunity_send_log', array('sendid' => $id, 'type' => 1, 'cid' => 2, 'status' => 1), 'count(*)');
    //失败
    $total2 = $total - $total1;
    if ($total) {
        $hhl = (($total1 / $total) * 100) . '%';
    }
    include $this->template('web/core/announcement/sms');
}
elseif ($op == 'wxsend') {
    $psize = 200;
    $star = intval($_GPC['star']);
    $id = intval($_GPC['id']);
    //微信推送公告
    if ($id) {

        $item = pdo_get('xcommunity_announcement', array('id' => $id), array());
        $condition['aid'] = $id;
        $cond = array();
        $cond['uid >'] = $star;
        $cond['uniacid'] = $_W['uniacid'];
        $cond['visit'] = 0;
        if ($item['type'] == 1) {
            //按小区推送
            $list = pdo_getall('xcommunity_announcement_region', $condition, array('regionid'));
            $list_regionids = _array_column($list, 'regionid');
            $cond['regionid'] = $list_regionids;
        }
        else {
            $list = pdo_getall('xcommunity_announcement_build', $condition, array('buildid'));
            $list_buildids = _array_column($list, 'buildid');
            $cond['buildid'] = $list_buildids;
        }
        $members = pdo_getall('xcommunity_member', $cond, array('uid'), 'uid', array('uid ASC'), $psize);

        if (empty($members)) {
            $url = $this->createWebUrl('announcement', array('op' => 'send', 'id' => $id));
            $this->xqmessage('发送成功！', $url, 'success');
        }
        $members_uids = _array_column($members, 'uid');
        $fans = pdo_getall('mc_mapping_fans', array('uid' => $members_uids), array('openid'));
        $reason = htmlspecialchars_decode($item['reason']);
        $content = str_replace(array('<br>', '&nbsp;'), array("\n", ' '), $reason);
        $content = strip_tags($content, '<a>');
        $tplid = set('t2');
        $data = array(
            'first'    => array(
                'value' => '',
            ),
            'keyword1' => array(
                'value' => $item['title'],
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
        foreach ($fans as $k => $v) {
            if ($v['openid']) {
                $url = $_W['siteroot'] . "app/index.php?i={$_W['uniacid']}&c=entry&id={$id}&op=detail&do=announcement&m=" . MODULE_NAME;
                $account_api = WeAccount::create();
                $status = $account_api->sendTplNotice($v['openid'], $tplid, $data, $url);
            }
            $d = array(
                'uniacid'    => $_W['uniacid'],
                'sendid'     => $id,
                'uid'        => $v['uid'],
                'type'       => 1,
                'cid'        => 1,
                'regionid'   => $v['regionid'],
                'createtime' => TIMESTAMP
            );
            if ($status) {
                $d['status'] = 1;
                pdo_insert('xcommunity_send_log', $d);
            }
            else {
                $d['status'] = 2;
                pdo_insert('xcommunity_send_log', $d);
            }
        }
        foreach ($members as $row) {
            $lastid = $row['uid'];
        }
        $url = $this->createWebUrl('announcement', array('op' => 'wxsend', 'star' => $lastid, 'id' => $id));
        $this->xqmessage('正在发送,请勿关闭', $url, 'success');
        util::permlog('小区公告-微信推送', '信息标题:' . $item['title']);
    }

//    $record = intval($_GPC['record']) ? intval($_GPC['record']) : 0;
//    $ok = intval($_GPC['ok']) ? intval($_GPC['ok']) : 0;
//    $fail = intval($_GPC['fail']) ? intval($_GPC['fail']) : 0;
//    $total = intval($_GPC['total']);
//    foreach ($members as $key => $val) {
//        $record++;//已发送记录
//        if ($val['openid']) {
//            $url = $_W['siteroot'] . "app/index.php?i={$_W['uniacid']}&c=entry&id={$id}&regionid={$val['regionid']}&op=detail&do=announcement&m=" . MODULE_NAME;
//            $account_api = WeAccount::create();
//            $status = $account_api->sendTplNotice($val['openid'], $tplid, $data, $url);
//        }
//        $d = array(
//            'uniacid'    => $_W['uniacid'],
//            'sendid'     => $id,
//            'uid'        => $val['uid'],
//            'type'       => 1,
//            'cid'        => 1,
//            'regionid'   => $val['regionid'],
//            'createtime' => TIMESTAMP
//        );
//        if ($status) {
//            $d['status'] = 1;
//            $ok++;//成功发送
//            pdo_insert('xcommunity_send_log', $d);
//        }
//        else {
//            $d['status'] = 2;
//            $fail++;//失败发送
//            pdo_insert('xcommunity_send_log', $d);
//        }
////        }
//    }
//
//
//    if ($ok == $total || empty($members)) {
//        echo json_encode(array('status' => 'end'));
//        exit();
//    }
//    else {
//        echo json_encode(array('fail' => $fail, 'ok' => $ok, 'record' => $record));
//        exit();
//    }
    //util::permlog('小区公告-微信推送', '信息标题:' . $item['title']);
}
elseif ($op == 'smssend') {
    $id = intval($_GPC['id']);
    if ($id) {
        $item = pdo_get('xcommunity_announcement', array('id' => $id), array());
        if ($item['type'] == 1) {
            $regs = pdo_getall('xcommunity_announcement_region', array('aid' => $id), array('regionid'));
            foreach ($regs as $key => $val) {
                $regionid .= $val['regionid'] . ',';
            }
            $regionid = ltrim(rtrim($regionid, ","), ',');

        }
        else {
            $regs = pdo_getall('xcommunity_announcement_build', array('aid' => $id), array('buildid'));
            foreach ($regs as $key => $val) {
                $builds .= $val['buildid'] . ',';
            }
            $builds = ltrim(rtrim($builds, ","), ',');
        }

    }
    $pindex = max(1, intval($_GPC['page']));
    $psize = 5;
    if ($item['type'] == 1) {
        //可发送业主
        $sql = "select t1.regionid,t2.mobile from" . tablename('xcommunity_member') . "t1 left join" . tablename('mc_members') . "t2 on t1.uid = t2.uid where t1.regionid in($regionid) and t1.uniacid =:uniacid LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
    }
    else {
        $sql = "select t1.regionid,t2.mobile from" . tablename('xcommunity_member') . "t1 left join" . tablename('mc_members') . "t2 on t1.uid = t2.uid left join" . tablename('xcommunity_member_bind') . "t3 on t3.memberid= t1.id left join" . tablename('xcommunity_member_room') . "t4 on t4.id = t3.addressid where t4.buildid in($builds) and t1.uniacid =:uniacid LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
    }
    $members = pdo_fetchall($sql, array(':uniacid' => $_W['uniacid']));
    $reason = htmlspecialchars_decode($item['reason']);
    $content = str_replace(array('<br>', '&nbsp;'), array("\n", ' '), $reason);
    $content = strip_tags($content, '<a>');
    $record = intval($_GPC['record']) ? intval($_GPC['record']) : 0;
    $ok = intval($_GPC['ok']) ? intval($_GPC['ok']) : 0;
    $fail = intval($_GPC['fail']) ? intval($_GPC['fail']) : 0;
    $total = intval($_GPC['total']);
    foreach ($members as $key => $val) {
        $record++;//已发送记录
        if (set('s1') && set('s17')) {
            $type = set('s1');
            if ($type == 1) {
                $type = 'wwt';
            }
            elseif ($type == 2) {
                $type = 'juhe';
                $tpl_id = set('s18');
            }
            else {
                $type = 'aliyun_new';
                $tpl_id = set('s25');
            }
//                    $x37 = '';
            $api = 1;
        }
        else {
            $type = set('x21', $val['regionid']);
            if ($type == 1) {
                $type = 'wwt';
            }
            elseif ($type == 2) {
                $type = 'juhe';
                $tpl_id = set('x54', $val['regionid']);
            }
            else {
                $type = 'aliyun_new';
                $tpl_id = set('x73', $val['regionid']);
            }
            $api = 2;
            $d['regionid'] = $val['regionid'];
        }
        if ($type == 'wwt') {
            $smsg = "小区最新通知：" . $content;
        }
        elseif ($type == 'juhe') {
            $smsg = urlencode("#content#=$content");
        }
        else {
            $smsg = json_encode(array('content' => $content));
        }

        if ($val['mobile']) {
            $resp = sms::send($val['mobile'], $smsg, $type, $val['regionid'], $api, $tpl_id);
        }
        $d = array(
            'uniacid'    => $_W['uniacid'],
            'sendid'     => $id,
            'uid'        => $val['uid'],
            'type'       => 1,
            'cid'        => 2,
            'createtime' => TIMESTAMP
        );
        if ($resp['status'] == 1) {
            $d['status'] = 1;
            $ok++;//成功发送
            pdo_insert('xcommunity_send_log', $d);
        }
        else {
            $d['status'] = 2;
            $fail++;//失败发送
            pdo_insert('xcommunity_send_log', $d);
        }
//        }
    }


    if ($ok == $total || empty($members)) {
        echo json_encode(array('status' => 'end'));
        exit();
    }
    else {
        echo json_encode(array('fail' => $fail, 'ok' => $ok, 'record' => $record));
        exit();
    }

//        itoast('发送成功', referer(), 'success');
//        exit();


//        itoast('未开启通知发送接口', referer(), 'success');
//        exit();

    util::permlog('小区公告-短信推送', '信息标题:' . $item['title']);
}


