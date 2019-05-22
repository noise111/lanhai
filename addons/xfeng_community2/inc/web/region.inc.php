<?php
/**
 * 小区秘书模块
 *
 * [蓝牛科技] Copyright (c) 2013 xqms.cn
 */
/**
 * 后台小区信息
 */
global $_GPC, $_W;
$p = !empty($_GPC['p']) ? $_GPC['p'] : 'list';
$ops = array('add', 'delete', 'list', 'open', 'set', 'sms', 'xqprint', 'fields', 'change', 'search', 'register', 'payapi');
$op = !empty($_GPC['op']) ? $_GPC['op'] : 'list';
if (!in_array($op, $ops)) {
    message('该方法不存在(op:' . $op . ')');
}
$id = intval($_GPC['id']);
// 操作员
$user = util::xquser($_W['uid']);
if ($user && $user['menu_ops']) {
    $menu_opss = explode(',', $user['menu_ops']);
}
/**
 * 小区的添加
 */
if ($op == 'add') {
    if (empty($id)) {
//        if ($user) {
        $uid = $user['uuid'] && $user['uuid'] != 1 ? $user['uuid'] : $_W['uid'];
        $total = pdo_fetchcolumn("SELECT COUNT(*) FROM" . tablename('xcommunity_region') . "WHERE uniacid=:uniacid and uid=:uid", array(':uid' => $uid, ':uniacid' => $_W['uniacid']));
        $xquser = pdo_fetch("SELECT * FROM" . tablename('xcommunity_users') . "as u left join" . tablename('xcommunity_users_group') . "as g on u.groupid = g.id WHERE u.uid=:uid", array(':uid' => $uid));
        if ($xquser) {
            $groupid = $xquser['groupid'];
            $maxaccount = $xquser['maxaccount'];
        }
        if ($groupid) {
            if ($total >= $maxaccount) {
                itoast("已经达到添加小区上限", $this->createWebUrl('region', array('op' => 'list')), 'success', ture);
                exit();
            }
        }
        //兼容云平台小区限制
        $u = pdo_fetch('select t1.maxregion from' . tablename('users') . "t1 left join" . tablename('users_group') . "t2 on t1.groupid=t2.id where t1.uid=:uid", array(':uid' => $_W['uid']));
        if (!empty($u['maxregion'])) {
            if ($total >= $u['maxregion']) {
                itoast("已经达到添加小区上限", $this->createWebUrl('region', array('op' => 'list')), 'success', ture);
                exit();

            }
        }
//        }
    }
    if ($id) {
        $item = pdo_fetch("SELECT * FROM" . tablename('xcommunity_region') . "WHERE uniacid=:uniacid AND id=:id", array(":id" => $id, ":uniacid" => $_W['uniacid']));
        if (empty($item)) {
            itoast('不存在该小区信息或已删除', referer(), 'error');
        }
    }
    $d = '';
    if ($user && $_W['uid'] != 1) {
        if ($user['type'] == 2) {
            $d = " and uid ={$_W['uid']}";
        }
        elseif ($user['type'] == 3) {
            $d = " and id={$user['pid']}";
        }
    }
    $propertys = model_region::property_fetall($d);

    if ($_W['isajax']) {
        $reside = $_GPC['reside'];
        $data = array(
            'uniacid'  => $_W['uniacid'],
            'title'    => $_GPC['title'],
            'linkmen'  => $_GPC['linkmen'],
            'linkway'  => $_GPC['linkway'],
            'lng'      => $_GPC['baidumap']['lng'],
            'lat'      => $_GPC['baidumap']['lat'],
            'address'  => $_GPC['address'],
            'url'      => $_GPC['url'],
            'thumb'    => $_GPC['thumb'],
            'qq'       => $_GPC['qq'],
            'province' => $reside['province'],
            'city'     => $reside['city'],
            'dist'     => $reside['district'],
            'pic'      => $_GPC['pic'],
            'keyword'  => $_GPC['keyword'],
            'pid'      => intval($_GPC['pid']),
            'stamp'    => $_GPC['stamp'],
        );
        $ru = pdo_get('rule', array('name' => $_GPC['title']), array('id'));
        if (empty($ru)) {
            $rule = array(
                'uniacid' => $_W['uniacid'],
                'name'    => $_GPC['title'],
                'module'  => 'cover',
                'status'  => 1,
            );
            $result = pdo_insert('rule', $rule);
            $rid = pdo_insertid();
        }
        else {
            $rid = $ru['id'];
        }
        if ($id) {
            $data['rid'] = $ru['id'];
            $data['yybstatus'] = 0;
            $data['action'] = 1;
            pdo_update("xcommunity_region", $data, array('id' => $id));
            $regionid = $id;
            util::permlog('小区管理-修改', '修改小区ID:' . $regionid . '修改名称:' . $data['title']);
        }
        else {
            $region = pdo_fetch("SELECT id FROM" . tablename('xcommunity_region') . "WHERE uniacid='{$_W['uniacid']}' AND title='{$_GPC['title']}' AND province='{$reside['province']}' AND city='{$reside['city']}' AND dist='{$reside['dist']}'");
            if ($region) {
                itoast('该小区已经存在,无需在添加.', referer(), 'error', ture);
            }
            $data['rid'] = $rid;
            $data['uid'] = $_W['uid'];
            $data['status'] = 1;
            pdo_insert("xcommunity_region", $data);
            $regionid = pdo_insertid();
            util::permlog('小区管理-添加', '添加小区ID:' . $regionid . '添加名称:' . $data['title']);
            if ($user) {
                $d = array(
                    'usersid'  => $_W['uid'],
                    'regionid' => $regionid
                );
                pdo_insert('xcommunity_users_region', $d);
            }
            //主页导航
            $navs = pdo_getall('xcommunity_nav', array('uniacid' => $_W['uniacid']), array('id'));
            foreach ($navs as $k => $v) {
                $d = array(
                    'nid'      => $v['id'],
                    'regionid' => $regionid
                );
                pdo_insert('xcommunity_nav_region', $d);
            }
            //住户中心
            $hnavs = pdo_getall('xcommunity_housecenter', array('uniacid' => $_W['uniacid']), array('id'));
            foreach ($hnavs as $k => $v) {
                $d = array(
                    'nid'      => $v['id'],
                    'regionid' => $regionid
                );
                pdo_insert('xcommunity_housecenter_region', $d);
            }
            //便民查询
            $search = pdo_getall('xcommunity_search', array('uniacid' => $_W['uniacid']), array('id'));
            foreach ($search as $key => $val) {
                $d = array(
                    'sid'      => $val['id'],
                    'regionid' => $regionid
                );
                pdo_insert('xcommunity_search_region', $d);
            }
            //便民号码
            $phone = pdo_getall('xcommunity_phone', array('uniacid' => $_W['uniacid']), array('id'));
            foreach ($phone as $key => $val) {
                $d = array(
                    'phoneid'  => $val['id'],
                    'regionid' => $regionid
                );
                pdo_insert('xcommunity_phone_region', $d);
            }
            if (!set('p106')) {
                //首页公告
                $announcements = pdo_getall('xcommunity_announcement', array('status' => 1, 'uniacid' => $_W['uniacid']), array());
                foreach ($announcements as $key => $val) {
                    $d = array(
                        'aid'      => $val['id'],
                        'regionid' => $regionid
                    );
                    pdo_insert('xcommunity_announcement_region', $d);
                }
                //首页幻灯/魔方推荐/首页广告
                $slides = pdo_getall('xcommunity_slide', array('uniacid' => $_W['uniacid']), array());
                foreach ($slides as $key => $val) {
                    $d = array(
                        'sid'      => $val['id'],
                        'regionid' => $regionid
                    );
                    pdo_insert('xcommunity_slide_region', $d);
                }
                $homemakings = pdo_getall('xcommunity_category', array('uniacid' => $_W['uniacid'], 'type' => 1), array());
                foreach ($homemakings as $key => $val) {
                    $d = array(
                        'cid'      => $val['id'],
                        'regionid' => $regionid,
                    );
                    pdo_insert('xcommunity_category_region', $d);
                }
            }
            //底部菜单
            $footers = pdo_getall('xcommunity_footmenu', array('uniacid' => $_W['uniacid']), array('id'));
            foreach ($footers as $key => $val) {
                $d = array(
                    'fid'      => $val['id'],
                    'regionid' => $regionid
                );
                pdo_insert('xcommunity_footmenu_region', $d);
            }
        }
        $rules = pdo_get("rule_keyword", array('rid' => $rid), array('id'));
        $covers = pdo_get('cover_reply', array('rid' => $rid), array('id'));
        $ruleword = array(
            'rid'     => $rid,
            'uniacid' => $_W['uniacid'],
            'module'  => 'cover',
            'content' => $data['keyword'],
            'type'    => 1,
            'status'  => 1,
        );
        if (empty($rules)) {
            pdo_insert('rule_keyword', $ruleword);
        }
        else {
            pdo_update('rule_keyword', $ruleword, array('id' => $rules['id']));
        }
        $crid = $ru ? $ru['id'] : $rid;
        $entry = array(
            'uniacid'     => $_W['uniacid'],
            'multiid'     => 0,
            'rid'         => $crid,
            'title'       => $_GPC['title'],
            'description' => '',
            'thumb'       => tomedia($_GPC['pic']),
            'url'         => $this->createMobileUrl('home', array('regionid' => $regionid)),
            'do'          => 'home',
            'module'      => $this->module['name'],
        );
        if (empty($covers)) {
            pdo_insert('cover_reply', $entry);
        }
        else {
            pdo_update('cover_reply', $entry, array('rid' => $crid));
        }
        echo json_encode(array('status' => 1));
        exit();
    }
    load()->func('tpl');
    $options=array();
    $options['dest_dir']=$_W['uid'] == 1 ? '' : MODULE_NAME.'/'.$_W['uid'];
    include $this->template('web/core/region/add');
}
elseif ($op == 'list') {
    $pindex = max(1, intval($_GPC['page']));
    $psize = 20;
    $condition = 't1.uniacid =:uniacid';
    $params[':uniacid'] = $_W['uniacid'];
    if (!empty($_GPC['keyword'])) {
        $condition .= " AND t1.title LIKE :keyword";
        $params[':keyword'] = "%{$_GPC['keyword']}%";
    }
    $reside = $_GPC['reside'];
    if (!empty($reside)) {
        if ($reside['province']) {
            $condition .= " AND t1.province = :province";
            $params[':province'] = $reside['province'];
        }
        if ($reside['city']) {
            $condition .= " AND t1.city = :city";
            $params[':city'] = $reside['city'];
        }
        if ($reside['district']) {
            $condition .= " AND t1.dist = :dist";
            $params[':dist'] = $reside['district'];
        }
    }
    if (intval($_GPC['pid'])) {
        $condition .= " and t2.id =:pid";
        $params[':pid'] = intval($_GPC['pid']);
    }
    if ($user[type] == 2) {
        //普通管理员
        $condition .= " AND t1.uid='{$_W['uid']}'";
    }
    if ($user[type] == 3) {
        //普通管理员
        $condition .= " AND t1.id in({$user['regionid']})";
    }
    $sql = "select t1.*,t2.title as ptitle,t1.title as rtitle from" . tablename('xcommunity_region') . "t1 left join" . tablename('xcommunity_property') . "t2 on t1.pid = t2.id where $condition LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
    $list = pdo_fetchall($sql, $params);

    $tsql = "select count(*) from" . tablename('xcommunity_region') . "t1 left join" . tablename('xcommunity_property') . "t2 on t1.pid = t2.id where $condition ";
    $total = pdo_fetchcolumn($tsql, $params);

    $pager = pagination($total, $pindex, $psize);
    load()->func('tpl');
    load()->func('communication');
//    $result = ihttp_request('http://cloud.we7xq.com/addons/bull_manage/api.php', array('type' => 'checkauth','module' => '$this->module['name']'),null,5);
//    $result = @json_decode($result['content'], true);
//    $result = $result['data'];
//
//    if($result['status'] == 1){
//        $num = rand(10000,9999);
//        itoast('系统错误:'.$num);exit();
//    }
    include $this->template('web/core/region/list');
}
elseif ($op == 'delete') {
    //小区删除
    if ($id) {
        $item = pdo_fetch("SELECT * FROM" . tablename('xcommunity_region') . "WHERE id=:id AND uniacid=:uniacid", array(":id" => $id, ":uniacid" => $_W['uniacid']));
        if (empty($item)) {
            itoast("不存在该小区信息或已删除", referer(), 'error');
        }
        if (pdo_delete('xcommunity_region', array('id' => $id))) {
            pdo_delete('xcommunity_area', array('regionid' => $id));
            pdo_delete('xcommunity_build', array('regionid' => $id));
            pdo_delete('xcommunity_unit', array('regionid' => $id));
            util::permlog('小区管理-删除', '删除小区ID:' . $id . '小区名称:' . $item['title']);
            pdo_delete('xcommunity_member', array('regionid' => $id));
            pdo_delete('xcommunity_xqcars', array('regionid' => $id));
            $parking = pdo_getall('xcommunity_parking', array('regionid' => $id), array('id'));
            foreach ($parking as $k => $v) {
                pdo_delete('xcommunity_parking_record', array('parkingid' => $v['id']));
            }
            pdo_delete('xcommunity_parking', array('regionid' => $id));
            itoast('删除成功', referer(), 'success', ture);
        }
    }

}
elseif ($op == 'open') {
    //删除
    if (checksubmit('delete')) {
        $ids = $_GPC['ids'];
        if (!empty($ids)) {
            foreach ($ids as $key => $id) {
                pdo_delete('xcommunity_open_log', array('id' => $id));
            }
            util::permlog('', '批量删除开门记录');
            itoast('删除成功', referer(), 'success', ture);
        }
    }
    $regionid = intval($_GPC['regionid']);
    $condition = ' t1.uniacid=:uniacid and t1.regionid=:regionid';
    $params[':uniacid'] = $_W['uniacid'];
    $params[':regionid'] = $regionid;

    if (checksubmit('export')) {
        $sql = "select t1.id,t4.title,t3.realname,t3.mobile,t1.type,t1.createtime,t5.address from" . tablename('xcommunity_open_log') . "t1 left join" . tablename('mc_members') . "t3 on t3.uid=t1.uid left join" . tablename('xcommunity_region') . "t4 on t1.regionid=t4.id left join " . tablename('xcommunity_member_room') . "t5 on t1.addressid=t5.id where $condition order by t1.createtime desc";
        $xqlist = pdo_fetchall($sql, $params);
        if (empty($xqlist)) {
            itoast('暂无数据导出', referer(), 'error');
            exit();
        }
        foreach ($xqlist as $k => $val) {
            $xqlist[$k]['cctime'] = date('Y-m-d H:i', $val['createtime']);
        }
        model_execl::export($xqlist, array(
            "title"   => "开门数据-" . date('Y-m-d-H-i', time()),
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
                    'title' => '地址',
                    'field' => 'address',
                    'width' => 12
                ),
                array(
                    'title' => '区域',
                    'field' => 'type',
                    'width' => 18
                ),
                array(
                    'title' => '开门时间',
                    'field' => 'cctime',
                    'width' => 18
                ),
            )
        ));
    }
    //显示访客记录信息
    $pindex = max(1, intval($_GPC['page']));
    $psize = 20;
    if ($_GPC['mobile']) {
        $condition .= " and t3.mobile like '%{$_GPC['mobile']}%'";
    }
    if ($_GPC['realname']) {
        $condition .= " and t3.realname like '%{$_GPC['realname']}%'";
    }
    if ($_GPC['idcard']) {
        $condition .= " and t3.idcard like '%{$_GPC['idcard']}%'";
    }
    $sql = "select t1.id,t4.title,t3.realname,t3.mobile,t1.type,t1.createtime,t5.address,t5.area,t5.build,t5.unit,t5.room from" . tablename('xcommunity_open_log') . "t1 left join" . tablename('mc_members') . "t3 on t3.uid=t1.uid left join" . tablename('xcommunity_region') . "t4 on t1.regionid=t4.id left join " . tablename('xcommunity_member_room') . "t5 on t1.addressid=t5.id where $condition order by t1.createtime desc LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
    $list = pdo_fetchall($sql, $params);
    $sql = "select count(*) from" . tablename('xcommunity_open_log') . "t1 left join" . tablename('mc_members') . "t3 on t3.uid=t1.uid left join" . tablename('xcommunity_region') . "t4 on t1.regionid=t4.id left join " . tablename('xcommunity_member_room') . "t5 on t1.addressid=t5.id where $condition order by t1.createtime desc";
    $total = pdo_fetchcolumn($sql, $params);
    $pager = pagination($total, $pindex, $psize);

    include $this->template('web/core/region/open');
}
elseif ($op == 'set') {
    $regionid = trim(intval($_GPC['regionid']));
    if ($regionid) {
        $set = pdo_getall('xcommunity_setting', array('uniacid' => $_W['uniacid'], 'regionid' => $regionid), array(), 'xqkey', array());
    }
    if (checksubmit('submit')) {
        foreach ($_GPC['sett'] as $k => $v) {
            $item = pdo_get('xcommunity_setting', array('regionid' => $regionid, 'xqkey' => $k), array('id'));
            $data = array(
                'xqkey'    => $k,
                'value'    => $v,
                'regionid' => $regionid,
                'uniacid'  => $_W['uniacid']
            );
            if ($item['id']) {
                pdo_update('xcommunity_setting', $data, array('id' => $item['id']));
            }
            else {
                pdo_insert('xcommunity_setting', $data);
            }
        }
//        $item = pdo_get('xcommunity_setting', array('regionid' => $regionid, 'xqkey' => 'x12'), array('id'));
//        $data = array(
//            'xqkey'    => 'x12',
//            'value'    => $_GPC['house'],
//            'regionid' => $regionid,
//            'uniacid'  => $_W['uniacid']
//        );
//        if ($item['id']) {
//            pdo_update('xcommunity_setting', $data, array('id' => $item['id']));
//        }
//        else {
//            pdo_insert('xcommunity_setting', $data);
//        }
        itoast('操作成功', referer(), 'success', ture);
    }

    include $this->template('web/core/region/set');
}
elseif ($op == 'sms') {
    $regionid = trim(intval($_GPC['regionid']));
    if ($regionid) {
        $set = pdo_getall('xcommunity_setting', array('uniacid' => $_W['uniacid'], 'regionid' => $regionid), array(), 'xqkey', array());
    }
    if (checksubmit('submit')) {

        foreach ($_GPC['sms'] as $key => $val) {
            $sql = "select * from" . tablename('xcommunity_setting') . "where xqkey='{$key}' and uniacid={$_W['uniacid']} and regionid={$regionid}";
            $item = pdo_fetch($sql);
            $data = array(
                'xqkey'    => $key,
                'value'    => $val,
                'uniacid'  => $_W['uniacid'],
                'regionid' => $regionid
            );
            if ($item) {
                pdo_update('xcommunity_setting', $data, array('id' => $item['id'], 'uniacid' => $_W['uniacid']));
            }
            else {
                pdo_insert('xcommunity_setting', $data);
            }
        }
        itoast('操作成功', referer(), 'success', ture);
    }
    include $this->template('web/core/region/sms');
}
elseif ($op == 'xqprint') {
    $regionid = trim(intval($_GPC['regionid']));
    if ($regionid) {
        $set = pdo_getall('xcommunity_setting', array('uniacid' => $_W['uniacid'], 'regionid' => $regionid), array(), 'xqkey', array());
    }
    if (checksubmit('submit')) {
        foreach ($_GPC['print'] as $key => $val) {
            $sql = "select * from" . tablename('xcommunity_setting') . "where xqkey='{$key}' and uniacid={$_W['uniacid']} and regionid={$regionid}";
            $item = pdo_fetch($sql);
            $data = array(
                'xqkey'    => $key,
                'value'    => $val,
                'uniacid'  => $_W['uniacid'],
                'regionid' => $regionid
            );
            if ($item) {
                pdo_update('xcommunity_setting', $data, array('id' => $item['id'], 'uniacid' => $_W['uniacid']));
            }
            else {
                pdo_insert('xcommunity_setting', $data);
            }
        }
        itoast('操作成功', referer(), 'success', ture);
    }
    include $this->template('web/core/region/xqprint');
}
elseif ($op == 'fields') {
    $regionid = trim(intval($_GPC['regionid']));
    if ($regionid) {
        $set = pdo_getall('xcommunity_setting', array('uniacid' => $_W['uniacid'], 'regionid' => $regionid), array(), 'xqkey', array());
    }
    if (checksubmit('submit')) {
        foreach ($_GPC['set'] as $key => $val) {
            $sql = "select * from" . tablename('xcommunity_setting') . "where xqkey='{$key}' and uniacid={$_W['uniacid']} and regionid={$regionid}";
            $item = pdo_fetch($sql);
            $data = array(
                'xqkey'    => $key,
                'value'    => $val,
                'uniacid'  => $_W['uniacid'],
                'regionid' => $regionid
            );
            if ($item) {
                pdo_update('xcommunity_setting', $data, array('id' => $item['id'], 'uniacid' => $_W['uniacid']));
            }
            else {
                pdo_insert('xcommunity_setting', $data);
            }
        }
        itoast('操作成功', referer(), 'success', ture);
    }
    include $this->template('web/core/region/fields');
}
elseif ($op == 'change') {
    $id = intval($_GPC['id']);
    $status = intval($_GPC['status']);
    $status = $status == 2 || $status == 0 ? 1 : 2;
    if ($id) {
        if (pdo_update('xcommunity_region', array('status' => $status), array('id' => $id))) {
            echo json_encode(array('status' => 1));
            exit();
        }
    }
}
elseif ($op == 'search') {
    if ($_W['isajax']) {
        $words = trim($_GPC['words']);
        $p = trim($_GPC['p']);
        if ($p == 'region') {
            $regionid = pdo_getcolumn('xcommunity_region', array('title' => $words, 'uniacid' => $_W['uniacid']), 'id');
            echo json_encode(array('err_code' => 0, 'regionid' => $regionid));
            exit();
        }
        elseif ($p == 'property') {
            $pid = pdo_getcolumn('xcommunity_property', array('title' => $words, 'uniacid' => $_W['uniacid']), 'id');
            echo json_encode(array('err_code' => 0, 'pid' => $pid));
            exit();
        }
    }
}
elseif ($op == 'register') {
    $regionid = trim(intval($_GPC['regionid']));
    if ($regionid) {
        $set = pdo_getall('xcommunity_setting', array('uniacid' => $_W['uniacid'], 'regionid' => $regionid), array(), 'xqkey', array());
    }
    if (checksubmit('submit')) {
        foreach ($_GPC['sett'] as $k => $v) {
            $item = pdo_get('xcommunity_setting', array('regionid' => $regionid, 'xqkey' => $k), array('id'));
            $data = array(
                'xqkey'    => $k,
                'value'    => $v,
                'regionid' => $regionid,
                'uniacid'  => $_W['uniacid']
            );
            if ($item['id']) {
                pdo_update('xcommunity_setting', $data, array('id' => $item['id']));
            }
            else {
                pdo_insert('xcommunity_setting', $data);
            }
        }
        itoast('操作成功', referer(), 'success', ture);
    }

    include $this->template('web/core/region/register');
}
elseif ($op == 'payapi') {
    $regionid = intval($_GPC['regionid']);
    if ($p == 'list') {
        if ($regionid) {
            $set = pdo_getall('xcommunity_setting', array('uniacid' => $_W['uniacid'], 'regionid' => $regionid), array(), 'xqkey', array());
        }
        if (checksubmit('submit')) {
            foreach ($_GPC['sett'] as $k => $v) {
                $item = pdo_get('xcommunity_setting', array('regionid' => $regionid, 'xqkey' => $k), array('id'));
                $data = array(
                    'xqkey'    => $k,
                    'value'    => $v,
                    'regionid' => $regionid,
                    'uniacid'  => $_W['uniacid']
                );
                if ($item['id']) {
                    pdo_update('xcommunity_setting', $data, array('id' => $item['id']));
                }
                else {
                    pdo_insert('xcommunity_setting', $data);
                }
            }
            itoast('操作成功', referer(), 'success', ture);
        }
        include $this->template('web/core/region/payapi/list');
    }
    elseif ($p == 'alipay') {
        $item = pdo_get('xcommunity_alipayment', array('userid' => $regionid, 'type' => 1), array());
        if (checksubmit('submit')) {
            $data = array(
                'uniacid' => $_W['uniacid'],
                'type'    => 1,
                'account' => $_GPC['account'],
                'partner' => $_GPC['partner'],
                'secret'  => $_GPC['secret'],
                'userid'  => $regionid
            );
            if ($item) {
                pdo_update('xcommunity_alipayment', $data, array('userid' => $regionid, 'type' => 1));
                util::permlog('', '修改支付宝ID:' . $item['id']);
            }
            else {
                $data['uid'] = $_W['uid'];
                pdo_insert('xcommunity_alipayment', $data);
                $id = pdo_insertid();
                util::permlog('', '添加支付宝ID:' . $id);
            }
            itoast('提交成功', referer(), 'success', ture);
        }
        include $this->template('web/core/region/payapi/alipay');
    }
    elseif ($p == 'wechat') {
        $item = pdo_get('xcommunity_wechat', array('userid' => $regionid, 'type' => 1), array());
        if (checksubmit('submit')) {
            $data = array(
                'uniacid'   => $_W['uniacid'],
                'appid'     => $_GPC['appid'],
                'appsecret' => $_GPC['appsecret'],
                'mchid'     => $_GPC['mchid'],
                'apikey'    => $_GPC['apikey'],
                'type'      => 1,
                'userid'    => $regionid
            );
            if ($item) {
                pdo_update('xcommunity_wechat', $data, array('userid' => $regionid, 'type' => 1));
                util::permlog('', '修改借用支付ID:' . $item['id']);
            }
            else {
                $data['uid'] = $_W['uid'];
                pdo_insert('xcommunity_wechat', $data);
                $id = pdo_insertid();
                util::permlog('', '添加借用支付ID:' . $id);
            }
            itoast('提交成功', referer(), 'success', ture);
        }
        include $this->template('web/core/region/payapi/wechat');
    }
    elseif ($p == 'sub') {
        $item = pdo_get('xcommunity_service_data', array('userid' => $regionid, 'type' => 1), array());
        if (checksubmit('submit')) {
            $data = array(
                'uniacid'    => $_W['uniacid'],
                'sub_id'     => $_GPC['sub_id'],
                'apikey'     => $_GPC['apikey'],
                'appid'      => $_GPC['appid'],
                'appsecret'  => $_GPC['appsecret'],
                'sub_mch_id' => $_GPC['sub_mch_id'],
                'type'       => 1,
                'userid'     => $regionid
            );
            if ($item) {
                pdo_update('xcommunity_service_data', $data, array('id' => $id));
                util::permlog('', '修改子商户ID:' . $item['id']);
            }
            else {
                $data['uid'] = $_W['uid'];
                pdo_insert('xcommunity_service_data', $data);
                $id = pdo_insertid();
                util::permlog('', '添加子商户ID:' . $id);
            }
            itoast('提交成功', referer(), 'success', ture);
        }
        include $this->template('web/core/region/payapi/sub');
    }
    elseif ($p == 'swiftpass') {
        $item = pdo_get('xcommunity_swiftpass', array('userid' => $regionid, 'type' => 1), array());
        if (checksubmit('submit')) {
            $data = array(
                'uniacid'     => $_W['uniacid'],
                'type'        => 1,
                'account'     => trim($_GPC['account']),
                'secret'      => trim($_GPC['secret']),
                'appid'       => trim($_GPC['appid']),
                'appsecret'   => trim($_GPC['appsecret']),
                'userid'      => $regionid,
                'private_key' => trim($_GPC['private_key']),
                'banktype'    => intval($_GPC['banktype'])
            );
            if ($item) {
                pdo_update('xcommunity_swiftpass', $data, array('id' => $id));
                util::permlog('', '修改威富通微信支付ID:' . $item['id']);
            }
            else {
                $data['uid'] = $_W['uid'];
                pdo_insert('xcommunity_swiftpass', $data);
                $id = pdo_insertid();
                util::permlog('', '添加威富通微信支付ID:' . $id);
            }
            itoast('提交成功', referer(), 'success', ture);
        }
        include $this->template('web/core/region/payapi/swiftpass');
    }
    elseif ($p == 'hsyunfu') {
        $item = pdo_get('xcommunity_hsyunfu', array('userid' => $regionid, 'type' => 1), array());
        if (checksubmit('submit')) {
            $data = array(
                'uniacid'   => $_W['uniacid'],
                'type'      => 1,
                'account'   => trim($_GPC['account']),
                'secret'    => trim($_GPC['secret']),
                'appid'     => trim($_GPC['appid']),
                'appsecret' => trim($_GPC['appsecret']),
                'userid'    => $regionid
            );
            if ($item) {
                pdo_update('xcommunity_hsyunfu', $data, array('userid' => $regionid, 'type' => 1));
                util::permlog('', '修改华商云付微信支付ID:' . $item['id']);
            }
            else {
                $data['uid'] = $_W['uid'];
                pdo_insert('xcommunity_hsyunfu', $data);
                $id = pdo_insertid();
                util::permlog('', '添加华商云付微信支付ID:' . $id);
            }
            itoast('提交成功', referer(), 'success', ture);
        }
        include $this->template('web/core/region/payapi/hsyunfu');
    }
    elseif ($p == 'chinaums') {
        $item = pdo_get('xcommunity_chinaums', array('userid' => $regionid, 'type' => 1), array());
        if (checksubmit('submit')) {
            $data = array(
                'uniacid'  => $_W['uniacid'],
                'type'     => 1,
                'userid'   => $regionid,
                'mid'      => $_GPC['mid'],
                'tid'      => $_GPC['tid'],
                'instmid'  => $_GPC['instmid'],
                'msgsrc'   => $_GPC['msgsrc'],
                'msgsrcid' => $_GPC['msgsrcid'],
                'secret'   => $_GPC['secret'],
            );
            if ($item) {
                pdo_update('xcommunity_chinaums', $data, array('userid' => $regionid, 'type' => 1));
                util::permlog('', '修改银联ID:' . $item['id']);
            }
            else {
                $data['uid'] = $_W['uid'];
                pdo_insert('xcommunity_chinaums', $data);
                $id = pdo_insertid();
                util::permlog('', '添加银联ID:' . $id);
            }
            itoast('提交成功', referer(), 'success', ture);
        }
        include $this->template('web/core/region/payapi/chinaums');
    }
}