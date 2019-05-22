<?php
/**
 * Created by we7xq.
 * User: zhoufeng
 * Time: 2017/7/6 下午2:56
 */
global $_W, $_GPC;
$ops = array('branch', 'mail', 'perm', 'notice', 'memo', 'worker', 'company', 'print', 'reset', 'send', 'role');
$op = !empty($_GPC['op']) ? $_GPC['op'] : 'branch';
if (!in_array($op, $ops)) {
    message('该方法不存在(op:' . $op . ')');
}
$p = in_array(trim($_GPC['p']), array('list', 'add', 'change', 'user', 'menu', 'm', 'commission', 'verify', 'delete', 'company', 'edit', 'del', 'status')) ? trim($_GPC['p']) : 'list';
// 操作员
$user = util::xquser($_W['uid']);
if ($user && $user['menu_ops']) {
    $menu_opss = explode(',', $user['menu_ops']);
}
$d = '';
if ($user && $_W['uid'] != 1) {
    if ($user['type'] == 2) {
        $d = " and uid ={$_W['uid']}";
    } elseif ($user['type'] == 3) {
        $d = " and id={$user['pid']}";
    }
}
$properties = model_region::property_fetall($d);
$companies = model_region::company_fetall($d);
/**
 * 员工管理--部门管理
 */
if ($op == 'branch') {

    if ($p == 'list') {
        $pindex = max(1, intval($_GPC['page']));
        $psize = 20;
        $condition = 't1.uniacid =:uniacid';
        $params[':uniacid'] = $_W['uniacid'];
        if (intval($_GPC['pid'])) {
            $condition .= ' and t1.pid=:pid';
            $params[':pid'] = intval($_GPC['pid']);
        }
//        if($user){
//            $condition .=" and t1.pid=:pid";
//            $params[":pid"] = $user['pid'];
//        }
        if ($user && ($user[type] == 2 || $user[type] == 3)) {
            $condition .= " and t1.pid=:pid";
            $params[":pid"] = $user['pid'];
        }
        $sql = "select t1.id,t1.title,t2.title as ptitle from " . tablename('xcommunity_department') . "t1 left join" . tablename('xcommunity_property') . "t2 on t1.pid=t2.id where $condition limit " . ($pindex - 1) * $psize . ',' . $psize;
        $list = pdo_fetchall($sql, $params);
        $tsql = "select count(*) from " . tablename('xcommunity_department') . "t1 left join" . tablename('xcommunity_property') . "t2 on t1.pid=t2.id where $condition";
        $total = pdo_fetchcolumn($tsql, $params);
        $pager = pagination($total, $pindex, $psize);

        include $this->template('web/core/staff/branch/list');
    } elseif ($p == 'add') {
        $id = intval($_GPC['id']);
        if ($id) {
            $item = pdo_get('xcommunity_department', array('id' => $id), array());
        }
        if ($_W['isajax']) {
            $data = array(
                'uniacid'    => $_W['uniacid'],
                'pid'        => intval($_GPC['pid']),
                'title'      => $_GPC['title'],
                'createtime' => TIMESTAMP
            );
            if ($id) {
                pdo_update('xcommunity_department', $data, array('id' => $id));
                util::permlog('员工管理-部门管理-修改', '修改部门名称:' . $data['title']);
            } else {
                $department = pdo_get('xcommunity_department', array('title' => $data['title'], 'pid' => $data['pid']), array('id'));
                if ($department) {
                    echo json_encode(array('content' => '部门已经存在'));
                    exit();
                }
                $data['uid'] = $_W['uid'];
                pdo_insert('xcommunity_department', $data);
                util::permlog('员工管理-部门管理-添加', '添加部门名称:' . $data['title']);
            }
            echo json_encode(array('status' => 1));
            exit();
        }
        include $this->template('web/core/staff/branch/add');
    } elseif ($p == 'delete') {
        $id = intval($_GPC['id']);
        if (empty($id)) {
            itoast('参数不存在', referer(), 'error');
            exit();
        }
        $item = pdo_get('xcommunity_department', array('id' => $id), array());
        if (empty($item)) {
            itoast('信息不存在', referer(), 'error', ture);
            exit();
        }
        if (pdo_delete('xcommunity_department', array('id' => $id))) {
            util::permlog('员工管理-部门管理-删除', '删除部门名称:' . $item['title']);
            itoast('删除成功', referer(), 'success', ture);
        }
    }
}
/**
 * 员工管理--通讯录
 */
if ($op == 'mail') {
    if ($p == 'add') {
        $id = intval($_GPC['id']);
        if ($id) {
            $sql = "select t1.*,t3.id as pid,t2.title from" . tablename('xcommunity_staff') . "t1 left join" . tablename('xcommunity_department') . "t2 on t1.departmentid=t2.id left join" . tablename('xcommunity_property') . "t3 on t2.pid=t3.id where t1.id=:id";
            $item = pdo_fetch($sql, array(':id' => $id));
            if ($item['departmentid']) {
                $departments = pdo_getall('xcommunity_department', array('pid' => $item['pid']), array());
            }
        }
        if ($_W['isajax']) {
            $data = array(
                'uniacid'      => $_W['uniacid'],
                'realname'     => $_GPC['realname'],
                'nickname'     => $_GPC['nickname'],
                'mobile'       => trim($_GPC['mobile']),
                'wechat'       => $_GPC['wechat'],
                'mail'         => $_GPC['mail'],
                'departmentid' => intval($_GPC['departmentid']),
                'position'     => $_GPC['position'],
                'remark'       => $_GPC['remark'],
                'openid'       => trim($_GPC['openid']),
                'type'         => 1,
                'pid'          => intval($_GPC['pid'])
            );

            if ($id) {
                pdo_update('xcommunity_staff', $data, array('id' => $id));
                util::permlog('员工管理-通讯录-修改', '修改员工姓名:' . $data['realname']);
            } else {
                $staff = pdo_get('xcommunity_staff', array('mobile' => $data['mobile'], 'departmentid' => $data['departmentid']), array('id'));
                if ($staff) {
                    echo json_encode(array('content' => '手机号已经存在'));
                    exit();
                }
                $data['uid'] = $_W['uid'];
                pdo_insert('xcommunity_staff', $data);
                util::permlog('员工管理-通讯录-添加', '添加员工姓名:' . $data['realname']);
            }
            echo json_encode(array('status' => 1));
            exit();
        }
        include $this->template('web/core/staff/mail/add');
    } elseif ($p == 'list') {
        $pindex = max(1, intval($_GPC['page']));
        $psize = 10;
        $condition = 't1.uniacid =:uniacid and t1.type=1';
        $params[':uniacid'] = $_W['uniacid'];
        if (intval($_GPC['keyword'])) {
            $condition .= ' and t1.mobile=:keyword';
            $params[':keyword'] = trim($_GPC['keyword']);
        }
        if ($user && $user[type] == 3) {
            $condition .= " and t2.pid=:pid";
            $params[":pid"] = $user['pid'];
        } else {
            if ($_GPC['pid']) {
                $condition .= " and t2.pid=:pid";
                $params[":pid"] = $_GPC['pid'];
            }
        }
        $sql = "select t1.*,t2.title,t3.title as ptitle from " . tablename('xcommunity_staff') . "t1 left join" . tablename('xcommunity_department') . "t2 on t1.departmentid =t2.id left join" . tablename('xcommunity_property') . "t3 on t3.id=t2.pid where $condition limit " . ($pindex - 1) * $psize . ',' . $psize;
        $list = pdo_fetchall($sql, $params);
        $tsql = "select count(*) from " . tablename('xcommunity_staff') . "t1 left join" . tablename('xcommunity_department') . "t2 on t1.departmentid =t2.id left join" . tablename('xcommunity_property') . "t3 on t3.id=t2.pid  where $condition";
        $total = pdo_fetchcolumn($tsql, $params);
        $pager = pagination($total, $pindex, $psize);
        include $this->template('web/core/staff/mail/list');
    } elseif ($p == 'delete') {
        $id = intval($_GPC['id']);
        if (empty($id)) {
            itoast('参数不存在', referer(), 'error', ture);
            exit();
        }
        $item = pdo_get('xcommunity_staff', array('id' => $id), array());
        if (empty($item)) {
            itoast('信息不存在', referer(), 'error', ture);
            exit();
        }
        if (pdo_delete('xcommunity_staff', array('id' => $id))) {
            util::permlog('员工管理-通讯录-删除', '删除员工姓名:' . $item['realname']);
            $user = pdo_fetch('select * from' . tablename('xcommunity_users') . "where staffid=:staffid", array(':staffid' => $id));
            if ($user) {
                pdo_delete('xcommunity_users', array('staffid' => $id));
                if (pdo_delete('users', array('uid' => $user['uid'])) && pdo_delete("uni_account_users", array("uid" => $user['uid'])) && pdo_delete("users_permission", array("uid" => $user['uid']))) {

                    if (pdo_delete('xcommunity_users_region', array('usersid' => $user['uid']))) {
                        itoast('删除成功', referer(), 'success', ture);
                    }
                }

            }
            $wechat_notice = pdo_get('xcommunity_wechat_notice', array('staffid' => $id), array());
            if ($wechat_notice) {
                pdo_delete('xcommunity_wechat_notice', array('staffid' => $id));
            }
            $notice = pdo_get('xcommunity_notice', array('staffid' => $id), array());
            if ($notice) {
                pdo_delete('xcommunity_notice', array('staffid' => $id));
            }
            itoast('删除成功', referer(), 'success', ture);
        }
    } elseif ($p == 'change') {
        $pid = intval($_GPC['pid']);
        if ($pid) {
            $departments = pdo_getall('xcommunity_department', array('pid' => $pid), array());
            echo json_encode($departments);
            exit();
        }
    }
}
/**
 * 员工管理--权限设置
 */
if ($op == 'perm') {
    /**
     * 添加权限
     */
    if ($p == 'add') {

        $condition = 'uniacid=:uniacid';
        $par[':uniacid'] = $_W['uniacid'];
        if ($user[type] == 2 || $user[type] == 5 || $user[type] == 4) {
            $condition .= " and uid=:uid";
            $par[':uid'] = $_W['uid'];
        }
        $sql = "select * from" . tablename('xcommunity_company') . "where $condition";
        $companies = pdo_fetchall($sql, $par);
        $id = intval($_GPC['id']);
        $regionids = '[]';
        if ($id) {
            $sql = "select t1.*,t2.username,t3.realname,t2.salt from" . tablename('xcommunity_users') . "t1 left join" . tablename('users') . "t2 on t1.uid=t2.uid left join" . tablename('xcommunity_staff') . "t3 on t1.staffid = t3.id where t1.id=:id";
            $item = pdo_fetch($sql, array(':id' => $id));
            if ($item['xqtype'] == 1) {
                $departments = pdo_getall('xcommunity_department', array('pid' => $item['pid']), array('id', 'title'));
                $staffs = pdo_getall('xcommunity_staff', array('departmentid' => $item['departmentid'], 'type' => 1), array());
            } elseif ($item['xqtype'] == 2) {
                $staffs = pdo_getall('xcommunity_staff', array('departmentid' => $item['pid'], 'type' => 2), array());
            }

            $regs = pdo_getall('xcommunity_users_region', array('usersid' => $item['uid']), array('regionid'));
            $regionid = array();
            foreach ($regs as $key => $val) {
                $regionid[] = $val['regionid'];
            }
            $regionids = json_encode($regionid);
            $province = $item['type'] == 3 || $item['type'] == 4 ? $item['province'] : '';
            $city = $item['type'] == 3 || $item['type'] == 4 ? $item['city'] : '';
            $dist = $item['type'] == 3 || $item['type'] == 4 ? $item['dist'] : '';
            $m = user_single($item['uid']);
            if ($item['store']) {
                $storeids = explode(',', $item['store']);
            }
        }
        if ($_W['isajax']) {
            load()->model('user');
            $member = array();
            if (empty($_GPC['password'])) {
                echo json_encode(array('content' => '必须输入密码，且密码长度不得低于8位。'));
                exit();
            }
            $member['password'] = $_GPC['password'];
            if (!empty($_GPC['password'])) {
                if (istrlen($member['password']) < 8) {
                    echo json_encode(array('content' => '必须输入密码，且密码长度不得低于8位。'));
                    exit();
                }
            }
            $member['status'] = intval($_GPC['status']);
            //小区用户表
            $data = array(
                'type'       => intval($_GPC['type']) ? intval($_GPC['type']) : $user['type'],
                'createtime' => TIMESTAMP,
                'roleid'     => intval($_GPC['roleid'])
            );
            if ($data['type'] == 3 || $data['type'] == 4) {
                //小区管理员、超市管理员绑定小区和省市区
                $birth = $_GPC['birth'];
                $data['province'] = $birth['province'];
                $data['city'] = $birth['city'];
                $data['dist'] = $birth['district'];
                if ($data['type'] == 4) {
                    $shopids = $_GPC['shopids'];
                    $data['store'] = implode(',', $shopids);
                }
            }
            if ($data['type'] == 5) {
                $dpids = $_GPC['dpids'];
                $data['store'] = implode(',', $dpids);
            }
            //系统用户表
            $role = $_GPC['role'] ? $_GPC['role'] : 'operator';
            $user = array(
                'uniacid' => $_W['uniacid'],
                'role'    => $role,
            );
            if ($role == 'operator') {
                //系统用户表
                $insert = array(
                    'uniacid'    => $_W['uniacid'],
                    'type'       => $this->module['name'],
                    'permission' => 'all'
                );
            }
            if ($id) {
                $member['salt'] = $item['salt'];
                $member['uid'] = $item['uid'];
                user_update($member);
                $user_uid = $item['uid'];
                $data['staffid'] = intval($_GPC['staffid']);
                $data['pid'] = trim($_GPC['pid']) ? trim($_GPC['pid']) : trim($_GPC['companyid']);
                $data['departmentid'] = trim($_GPC['departmentid']);
                $data['xqtype'] = intval($_GPC['xqtype']) ? intval($_GPC['xqtype']) : $user['xqtype'];
                pdo_update('xcommunity_users', $data, array('id' => $id));
                pdo_update('uni_account_users', $user, array('uid' => $item['uid']));
                pdo_update('users_permission', $insert, array('uid' => $item['uid']));
                pdo_delete('xcommunity_users_region', array('usersid' => $item['uid']));
                util::permlog('员工管理-权限管理-修改', '修改用户名:' . $item['username']);
            } else {
                $data['uniacid'] = $_W['uniacid'];
                $uuid = intval($_GPC['uuid']) ? intval($_GPC['uuid']) : $_W['uid'];
                $data['uuid'] = $uuid;
                $data['staffid'] = intval($_GPC['staffid']);
                $data['pid'] = trim($_GPC['pid']) ? trim($_GPC['pid']) : trim($_GPC['companyid']);
                $data['departmentid'] = trim($_GPC['departmentid']);
                $data['xqtype'] = intval($_GPC['xqtype']) ? intval($_GPC['xqtype']) : $user['xqtype'];

                //添加到系统用户表中
                $member['username'] = trim($_GPC['username']);
                if (user_check(array('username' => $member['username']))) {
                    echo json_encode(array('content' => '非常抱歉，此用户名已经被注册，你需要更换注册名称！'));
                    exit();
                }

                $member['remark'] = $_GPC['remark'];
                $member['groupid'] = $_W['user']['groupid'];
                $member['endtime'] = $_W['user']['endtime'];
                $user_uid = user_register($member, $source = '');
                //关联添加到小区的用户表，主要是做一些其他权限使用
                $data['uid'] = $user_uid;
                pdo_insert('xcommunity_users', $data);
                $user['uid'] = $user_uid;
                pdo_insert('uni_account_users', $user);

                $insert['uid'] = $user_uid;
                pdo_insert('users_permission', $insert);
                util::permlog('员工管理-权限管理-添加', '添加用户名:' . $member['username']);
            }
            $regionids = explode(',', $_GPC['regionids']);
            if ($regionids) {
                foreach ($regionids as $key => $value) {
                    $dat = array(
                        'usersid'  => $user_uid,
                        'regionid' => $value,
                    );
                    pdo_insert('xcommunity_users_region', $dat);
                }
            }
            echo json_encode(array('status' => 1));
            exit();
        }
        $dps = pdo_getall('xcommunity_dp', array('uniacid' => $_W['uniacid'], 'status' => 1));
        $shops = pdo_getall('xcommunity_shop', array('uniacid' => $_W['uniacid'], 'status' => 1));
        $roles = pdo_getall('xcommunity_menu_role', array('uniacid' => $_W['uniacid']), array('id', 'title'));
        include $this->template('web/core/staff/perm/add');
    }
    /**
     * 列表
     */
    if ($p == 'list') {
        $roles = pdo_getall('xcommunity_menu_role', array('uniacid' => $_W['uniacid']), array('id', 'title'));
        if (empty($roles)) {
            itoast('请先添加角色', $this->createWebUrl('staff', array('op' => 'role')), 'error', true);
        }
        $pindex = max(1, intval($_GPC['page']));
        $psize = 20;
        $condition = 't1.uniacid =:uniacid ';
        $params[':uniacid'] = $_W['uniacid'];
        if (trim($_GPC['keyword'])) {
            $condition .= ' and t3.mobile=:keyword';
            $params[':keyword'] = trim($_GPC['keyword']);
        }
        $uuid = intval($_GPC['uuid']);
        if ($user || $uuid) {
            $condition .= " and t1.uuid=:uuid";
            $params[":uuid"] = intval($_GPC['uuid']) ? intval($_GPC['uuid']) : $_W['uid'];
        }
        $sql = "select t1.id,t1.uid,t1.id,t1.createtime,t2.username,t3.realname,t3.mobile,t1.type,t2.status from " . tablename('xcommunity_users') . "t1 left join" . tablename('users') . "t2 on t1.uid=t2.uid left join" . tablename('xcommunity_staff') . "t3 on t1.staffid=t3.id where $condition limit " . ($pindex - 1) * $psize . ',' . $psize;

        $list = pdo_fetchall($sql, $params);

        $tsql = "select count(*) from " . tablename('xcommunity_users') . "t1 left join" . tablename('users') . "t2 on t1.uid=t2.uid left join" . tablename('xcommunity_staff') . "t3 on t1.staffid=t3.id where $condition";
        $total = pdo_fetchcolumn($tsql, $params);
        $pager = pagination($total, $pindex, $psize);
        include $this->template('web/core/staff/perm/list');
    } elseif ($p == 'change') {
        $departmentid = intval($_GPC['departmentid']);
        $type = intval($_GPC['type']);
        if ($departmentid) {
            $users = pdo_getall('xcommunity_staff', array('departmentid' => $departmentid, 'type' => $type), array());
            echo json_encode($users);
            exit();
        }
    } elseif ($p == 'user') {
        $userid = intval($_GPC['userid']);
        if ($userid) {
            $user = pdo_get('xcommunity_staff', array('id' => $userid), array('realname', 'mobile', 'id'));
            echo json_encode($user);
            exit();
        }
    } elseif ($p == 'menu') {
        $menus = $this->NavMenu();
        $id = intval($_GPC['id']);
        if ($id) {
            $item = pdo_fetch("SELECT * FROM" . tablename('xcommunity_users') . "WHERE id=:id ", array(':id' => $id));
            $mmenus = explode(',', $item['menus']);
            $xqplugin = explode(',', $item['plugin']);
        }
        $plugins = pdo_getall('xcommunity_plugin', array(), array());
        if (checksubmit('submit')) {
            $data = array(
                'menus' => is_array($_GPC['menus']) ? implode(',', $_GPC['menus']) : '',
            );
            if ($id) {
                $r = pdo_update('xcommunity_users', $data, array('id' => $id));
                itoast('权限修改成功', $this->createWebUrl('staff', array('op' => 'perm')), 'success', true);

            }
            util::permlog('员工管理-权限管理-设置后台权限', '用户ID:' . $id);
        }
        include $this->template('web/core/staff/perm/menu');
    } elseif ($p == 'm') {
//        $menus =array(
//            array('id' => 1,'title' => '报修管理'),
//            array('id' => 2,'title' => '建议管理'),
//            array('id' => 3,'title' => '公告管理'),
//            array('id' => 4,'title' => '超市管理'),
//            array('id' => 5,'title' => '商家管理'),
//            array('id' => 6,'title' => '费用查询'),
//            array('id' => 7,'title' => '小区活动'),
//            array('id' => 8,'title' => '家政管理'),
//            array('id' => 9,'title' => '租赁管理'),
//            array('id' => 10,'title' => '二手管理'),
//            array('id' => 11,'title' => '拼车管理'),
//            array('id' => 12,'title' => '住户管理'),
//            array('id' => 13,'title' => '门禁管理'),
//            array('id' => 14,'title' => '内部通知'),
//        );
//        $userid = intval($_GPC['id']);
//        if($userid){
//            $user = pdo_get('xcommunity_users',array('id' => $userid),array('xqmenus'));
//            $xqmenus = explode(',',$user['xqmenus']);
//        }
//        if(checksubmit('submit')){
//            $data = array(
//                'xqmenus' => implode(',',$_GPC['data'])
//            );
//            if($userid){
//                pdo_update('xcommunity_users',$data,array('id' => $userid));
//            }
//            util::permlog('员工管理-权限管理-设置手机端权限','用户ID:'.$userid);
//            itoast('提交成功',referer(),$this->createWebUrl('staff',array('op' => 'perm')),true);
//        }
//        include $this->template('web/core/staff/perm/m');
        $menus = m($_W['uid']);
        $userid = intval($_GPC['id']);
        if ($userid) {
            $user = pdo_get('xcommunity_users', array('id' => $userid), array('xqmenus'));
            $mmenus = explode(',', $user['xqmenus']);
        }
        if (checksubmit('submit')) {
            $data = array(
                'xqmenus' => is_array($_GPC['menus']) ? implode(',', $_GPC['menus']) : '',
            );
            if ($userid) {
                pdo_update('xcommunity_users', $data, array('id' => $userid));
            }
            util::permlog('员工管理-权限管理-设置手机端权限', '用户ID:' . $userid);
            itoast('提交成功', referer(), $this->createWebUrl('staff', array('op' => 'perm', 'p' => 'm')), true);
        }
        include $this->template('web/core/staff/perm/m');
    } elseif ($p == 'commission') {
        $id = intval($_GPC['id']);
        if ($id) {
            $user = pdo_fetch("SELECT * FROM" . tablename('users') . "as u left join " . tablename('xcommunity_users') . " as x on u.uid = x.uid WHERE x.id=:id", array(':id' => $id));
        }
        if (checksubmit('submit')) {
            if ($id) {
                $r = pdo_update('xcommunity_users', array('commission' => $_GPC['commission'], 'xqcommission' => $_GPC['xqcommission']), array('id' => $id));
                if ($r) {
                    itoast('设置成功', $this->createWebUrl('staff', array('op' => 'perm')), 'success', true);
                }

            }
        }
        include $this->template('web/core/staff/perm/commission');
    } elseif ($p == 'delete') {
        $id = intval($_GPC['id']);
        $uuid = intval($_GPC['uuid']);
        if (empty($id)) {
            itoast('参数不存在', referer(), 'error');
            exit();
        }
        $item = pdo_get('xcommunity_users', array('id' => $id), array());

        pdo_delete('users', array('uid' => $item['uid']));
        pdo_delete("uni_account_users", array("uid" => $item['uid']));
        pdo_delete("users_permission", array("uid" => $item['uid']));
        pdo_delete('xcommunity_users', array('id' => $id));
        pdo_delete('xcommunity_users', array('uuid' => $uuid));
        util::permlog('员工管理-权限设置-删除', '删除用户名:' . $item['realname']);
        if (pdo_delete('xcommunity_users_region', array('usersid' => $item['uid']))) {
            itoast('删除成功', referer(), 'success', true);
        }
        itoast('删除成功', referer(), 'success', true);

    } elseif ($p == 'status') {
        $uid = intval($_GPC['uid']);
        $status = intval($_GPC['status']);
        $status = $status == 1 || $status == 0 ? 2 : 1;
        if ($uid) {
            if (pdo_update('users', array('status' => $status), array('uid' => $uid))) {
                echo json_encode(array('status' => 1));
                exit();
            }
        }
    }
}
/**
 * 员工管理--通知设置
 */
if ($op == 'notice') {
    $con = " uniacid=:uniacid";
    $par[':uniacid'] = $_W['uniacid'];
//    if ($user[type] == 2 || $user[type] == 3 || $user[type] == 4 || $user[type] == 5) {
////            $condition .=" and id=:companyid";
////            $params[":companyid"] = $user['pid'];
//        $con .= " and uid=:uid";
//        $par[':uid'] = $_W['uid'];
//    }
    if ($user) {
        //普通管理员
//            if ($user['uuid'] == 1){
//                $condition .= " and uid = {$_W['uid']}";
//            }else{
//                $condition .= " and uid = {$user['uuid']}";
//            }
        if ($user['uuid']) {
            //判断上级管理员是否是超市
            $suser = pdo_get("xcommunity_users", array('uid' => $user['uuid']), array());
            $uid = $suser['type'] == 4 || $suser['type'] == 5 ? $user['uuid'] : $_W['uid'];
            $condition .= " and uid = {$uid}";
        } else {
            $condition .= " and uid = {$_W['uid']}";
        }
    }
    $sql = "select * from" . tablename('xcommunity_company') . "where $con";
    $companies = pdo_fetchall($sql, $par);
    if ($p == 'list') {

        $pindex = max(1, intval($_GPC['page']));
        $psize = 20;
        $condition = 't1.uniacid =:uniacid';
        $params[':uniacid'] = $_W['uniacid'];
        if (trim($_GPC['keyword'])) {
            $condition .= " and t2.realname like '%{$_GPC['keyword']}%'";
        }
        if ($user[type] == 2 || $user[type] == 3 || $user[type] == 4 || $user[type] == 5) {
            $condition .= " and t1.uid=:uid";
            $params[":uid"] = $_W['uid'];
        }
        $sql = "select t1.*,t2.realname,t4.title as ptitle,t2.type as stype,t2.departmentid from " . tablename('xcommunity_wechat_notice') . "t1 left join" . tablename('xcommunity_staff') . "t2 on t1.staffid=t2.id left join" . tablename('xcommunity_department') . "t3 on t2.departmentid=t3.id left join" . tablename('xcommunity_property') . "t4 on t3.pid=t4.id where $condition limit " . ($pindex - 1) * $psize . ',' . $psize;
        $list = pdo_fetchall($sql, $params);
        foreach ($list as $k => $v) {
            if ($v['stype'] == 2) {
                $list[$k]['ptitle'] = pdo_getcolumn('xcommunity_company', array('id' => $v['departmentid']), 'title');
            }
        }
        $tsql = "select count(*) from " . tablename('xcommunity_wechat_notice') . "t1 left join" . tablename('xcommunity_staff') . "t2 on t1.staffid=t2.id left join" . tablename('xcommunity_department') . "t3 on t2.departmentid=t3.id left join" . tablename('xcommunity_property') . "t4 on t3.pid=t4.id where $condition";
        $total = pdo_fetchcolumn($tsql, $params);
        $pager = pagination($total, $pindex, $psize);
        include $this->template('web/core/staff/notice/list');
    } elseif ($p == 'add') {
        $id = intval($_GPC['id']);
        $regionids = '[]';
        if ($id) {
            $item = pdo_fetch("SELECT * FROM" . tablename('xcommunity_wechat_notice') . "WHERE id=:id", array(':id' => $id));
            $regs = pdo_getall('xcommunity_wechat_notice_region', array('nid' => $id), array('regionid'));
            $regionid = array();
            foreach ($regs as $key => $val) {
                $regionid[] = $val['regionid'];
            }
            $regionids = json_encode($regionid);
//            print_r($item);
//            print_r($regs);exit();
        }
        if ($_W['isajax']) {
            $birth = $_GPC['birth'];
            if (empty($birth['province'])) {
                echo json_encode(array('content' => '必须选择省市区和小区'));
                exit();
            }
            $data = array(
                'type'     => intval($_GPC['type']),
                'province' => $birth['province'],
                'city'     => $birth['city'],
                'dist'     => $birth['district'],
            );
            if ($id) {
                pdo_update('xcommunity_wechat_notice', $data, array('id' => $id));
                pdo_delete('xcommunity_wechat_notice_region', array('nid' => $id));
                util::permlog('员工管理-通知设置-修改', '接收员ID:' . $id);

            } else {
                $data['staffid'] = intval($_GPC['staffid']);
                $staf = pdo_get('xcommunity_wechat_notice', array('staffid' => $data['staffid']), array('id'));
                if ($staf) {
                    echo json_encode(array('content' => '接收员已添加'));
                    exit();
                }
//                if($data['staffid']){
//                    $user = pdo_get('xcommunity_users',array('staffid' => $data['staffid']),array('uid'));
//                    $data['uid'] = $user['uid'];
//                }
//                $data['uid'] = $_W['uid'];
                if ($user['uuid']) {
                    //判断上级管理员是否是超市
                    $suser = pdo_get("xcommunity_users", array('uid' => $user['uuid']), array());
                    $data['uid'] = $suser['type'] == 4 || $suser['type'] == 5 ? $suser['uid'] : $_W['uid'];
                } else {
                    $data['uid'] = $_W['uid'];
                }
                $data['uniacid'] = $_W['uniacid'];
                pdo_insert('xcommunity_wechat_notice', $data);
                $id = pdo_insertid();
                util::permlog('员工管理-权限设置-添加', '接收员ID:' . $id);
            }
            $regionids = explode(',', $_GPC['regionids']);
            foreach ($regionids as $key => $value) {
                $dat = array(
                    'nid'      => $id,
                    'regionid' => $value,
                );
                pdo_insert('xcommunity_wechat_notice_region', $dat);
            }
            echo json_encode(array('status' => 1));
            exit();
        }
        include $this->template('web/core/staff/notice/add');
    } elseif ($p == 'verify') {

        $id = intval($_GPC['id']);
        $type = $_GPC['type'];
        $data = intval($_GPC['status']);
        if (in_array($type, array('repair', 'report', 'shopping', 'homemaking', 'cost', 'business'))) {
            $data = ($data == 1 ? 0 : 1);
            pdo_update("xcommunity_wechat_notice", array($type => $data), array("id" => $id, "uniacid" => $_W['uniacid']));
            die(json_encode(array("result" => 1, "data" => $data)));
        }
    } elseif ($p == 'delete') {
        $id = intval($_GPC['id']);
        if ($id) {
            $r = pdo_delete('xcommunity_wechat_notice', array('id' => $id));
            if ($r) {
                util::permlog('员工管理-通知设置-删除', '通知接收员');
                pdo_delete('xcommunity_wechat_notice_region', array('nid' => $id));
                $result = array(
                    'status' => 1,
                );
                echo json_encode($result);
                exit();
            }

        }
    }
}
/**
 * 员工管理--内部公告
 */
if ($op == 'memo') {
    if ($p == 'add') {
        $id = intval($_GPC['id']);
        if (!empty($id)) {
            $item = pdo_fetch("SELECT * FROM" . tablename('xcommunity_memo') . "WHERE id=:id", array(':id' => $id));
            $regs = pdo_getall('xcommunity_memo_company', array('memoid' => $id), array('companyid'));
            $companyids = array();
            foreach ($regs as $key => $val) {
                $companyids[] = $val['companyid'];
            }
        }
        //添加公告
        if ($_W['isajax']) {
            $data = array(
                'uniacid'    => $_W['uniacid'],
                'title'      => $_GPC['title'],
                'createtime' => TIMESTAMP,
                'reason'     => htmlspecialchars_decode($_GPC['reason']),
                'xqtype'     => intval($_GPC['xqtype']),
            );
            if (empty($id)) {
                $data['uid'] = $_W['uid'];
                pdo_insert("xcommunity_memo", $data);
                $id = pdo_insertid();
                util::permlog('内部通知-添加', '信息标题:' . $data['title']);
            } else {
                pdo_update("xcommunity_memo", $data, array('id' => $id));
                pdo_delete('xcommunity_memo_company', array('memoid' => $id));
                util::permlog('内部通知-修改', '信息标题:' . $data['title']);
            }
            if ($data['xqtype'] == 1) {
                $regionids = $_GPC['pid'];
            } else {
                $regionids = $_GPC['companyid'];
            }
            foreach ($regionids as $key => $value) {
                $dat = array(
                    'memoid'    => $id,
                    'companyid' => $value,
                );
                pdo_insert('xcommunity_memo_company', $dat);
            }
            echo json_encode(array('status' => 1));
            exit();
        }

        include $this->template('web/core/staff/memo/add');
    } elseif ($p == 'list') {
        if ($_W['ispost']) {
            $ids = $_GPC['ids'];
            if (!empty($ids)) {
                foreach ($ids as $key => $id) {
                    pdo_delete('xcommunity_memo', array('id' => $id));
                }
                util::permlog('', '批量删除小区公告');
                itoast('删除成功', referer(), 'success', true);
            }
        }
        //公告搜索
        $condition = '';
        if (!empty($_GPC['title'])) {
            $condition .= " AND title LIKE '%{$_GPC['title']}%'";
        }
        if ($user[type] == 2 || $user[type] == 3) {
            //普通管理员
            $condition .= " AND uid='{$_W['uid']}'";
        }
        //管理公告reason
        $pindex = max(1, intval($_GPC['page']));
        $psize = 10;
        $sql = "select * from " . tablename("xcommunity_memo") . "where  uniacid = {$_W['uniacid']}  $condition order by createtime desc LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
        $list = pdo_fetchall($sql);
        $total = pdo_fetchcolumn('select count(*) from' . tablename("xcommunity_memo") . "where  uniacid = {$_W['uniacid']}  $condition");
        $pager = pagination($total, $pindex, $psize);

        include $this->template('web/core/staff/memo/list');
    } elseif ($p == 'del') {
        $id = intval($_GPC['id']);
        if ($id) {
            pdo_delete('xcommunity_memo', array('id' => $id));
            pdo_delete('xcommunity_memo_company', array('memoid' => $id));
            itoast('删除成功', referer(), 'success', true);
        }
    }
}
/**
 * 员工管理--外部人员
 */
if ($op == 'worker') {
    $con = "uniacid =:uniacid";
    $par[':uniacid'] = $_W['uniacid'];
//    if($user){
//        $con .=" and id=:companyid";
//        $par[":companyid"] = $user['pid'];
//    }
    if ($user[type] == 2 || $user[type] == 3) {
//            $condition .=" and id=:companyid";
//            $params[":companyid"] = $user['pid'];
        $con .= " and uid=:uid";
        $par[':uid'] = $_W['uid'];
    }
    $sql = "select * from" . tablename('xcommunity_company') . "where $con";
    $companies = pdo_fetchall($sql, $par);
    if ($p == 'company') {
        if ($_W['isajax']) {
            $data = array(
                'uniacid'   => $_W['uniacid'],
                'title'     => trim($_GPC['title']),
                'telephone' => trim($_GPC['telephone']),
                'uid'       => $_W['uid']
            );
            pdo_insert('xcommunity_company', $data);
            echo json_encode(array('status' => 1));
            exit();
        }
        $pindex = max(1, intval($_GPC['page']));
        $psize = 20;
        $condition = 'uniacid =:uniacid ';
        $params[':uniacid'] = $_W['uniacid'];
        if ($user[type] == 2 || $user[type] == 3) {
//            $condition .=" and id=:companyid";
//            $params[":companyid"] = $user['pid'];
            $condition .= " and uid=:uid";
            $params[':uid'] = $_W['uid'];
        }
        $sql = "select * from " . tablename('xcommunity_company') . "where $condition order by id desc limit " . ($pindex - 1) * $psize . ',' . $psize;
        $list = pdo_fetchall($sql, $params);
        $tsql = "select count(*) from " . tablename('xcommunity_company') . "where $condition";
        $total = pdo_fetchcolumn($tsql, $params);
        $pager = pagination($total, $pindex, $psize);
        include $this->template('web/core/staff/worker/company');
    } elseif ($p == 'list') {
        $condition = '';
        if ($user[type] == 2 || $user[type] == 3) {
            $condition = " and id={$user['pid']}";

        }
        if ($_W['isajax']) {
            $data = array(
                'uniacid'      => $_W['uniacid'],
                'realname'     => $_GPC['realname'],
                'nickname'     => $_GPC['nickname'],
                'mobile'       => trim($_GPC['mobile']),
                'wechat'       => $_GPC['wechat'],
                'mail'         => $_GPC['mail'],
                'departmentid' => intval($_GPC['departmentid']),
                'position'     => $_GPC['position'],
                'remark'       => $_GPC['remark'],
                'openid'       => trim($_GPC['openid']),
                'type'         => 2,
                'uid'          => $_W['uid'],
                'pid'          => intval($_GPC['departmentid'])
            );
            pdo_insert('xcommunity_staff', $data);
            echo json_encode(array('status' => 1));
            exit();
        }
        $pindex = max(1, intval($_GPC['page']));
        $psize = 20;
        $condition = 't1.uniacid =:uniacid and t1.type=2';
        $params[':uniacid'] = $_W['uniacid'];


        if ($user[type] == 2 || $user[type] == 3) {
            //普通管理员
            $condition .= " AND t2.uid='{$_W['uid']}'";
        }
        $sql = "select t1.*,t2.title from " . tablename('xcommunity_staff') . "t1 left join" . tablename('xcommunity_company') . "t2 on t1.departmentid =t2.id where $condition limit " . ($pindex - 1) * $psize . ',' . $psize;
        $list = pdo_fetchall($sql, $params);
        $tsql = "select count(*) from " . tablename('xcommunity_staff') . "t1 left join" . tablename('xcommunity_company') . "t2 on t1.departmentid =t2.id where $condition";
        $total = pdo_fetchcolumn($tsql, $params);
        $pager = pagination($total, $pindex, $psize);
        include $this->template('web/core/staff/worker/worker');
    } elseif ($p == 'add') {
        $id = intval($_GPC['id']);
        if ($id) {
            $item = pdo_get('xcommunity_company', array('id' => $id));
        }
        if ($_W['isajax']) {
            $data = array(
                'uniacid'   => $_W['uniacid'],
                'title'     => trim($_GPC['title']),
                'telephone' => trim($_GPC['telephone'])
            );
            pdo_update('xcommunity_company', $data, array('id' => $id));
            echo json_encode(array('status' => 1));
            exit();
        }
        include $this->template('web/core/staff/worker/company_add');
    } elseif ($p == 'delete') {
        $id = intval($_GPC['id']);
        if ($id) {
            $r = pdo_delete('xcommunity_company', array('id' => $id));
            if ($r) {
                itoast('删除成功', referer(), 'success', true);
                exit();
            }
        }
    } elseif ($p == 'edit') {
        $id = intval($_GPC['id']);
        if ($id) {
            $item = pdo_get('xcommunity_staff', array('id' => $id));
        }
        if ($_W['isajax']) {
            $data = array(
                'uniacid'      => $_W['uniacid'],
                'realname'     => $_GPC['realname'],
                'nickname'     => $_GPC['nickname'],
                'mobile'       => trim($_GPC['mobile']),
                'wechat'       => $_GPC['wechat'],
                'mail'         => $_GPC['mail'],
                'departmentid' => intval($_GPC['departmentid']),
                'position'     => $_GPC['position'],
                'remark'       => $_GPC['remark'],
                'openid'       => trim($_GPC['openid']),
                'type'         => 2
            );
            pdo_update('xcommunity_staff', $data, array('id' => $id));
            echo json_encode(array('status' => 1));
            exit();
        }
        include $this->template('web/core/staff/worker/worker_edit');
    } elseif ($p == 'del') {
        $id = intval($_GPC['id']);
        if ($id) {
            $r = pdo_delete('xcommunity_staff', array('id' => $id));
            if ($r) {
                $user = pdo_fetch('select * from' . tablename('xcommunity_users') . "where staffid=:staffid", array(':staffid' => $id));
                if ($user) {
                    pdo_delete('xcommunity_users', array('staffid' => $id));
                    if (pdo_delete('users', array('uid' => $user['uid'])) && pdo_delete("uni_account_users", array("uid" => $user['uid'])) && pdo_delete("users_permission", array("uid" => $user['uid']))) {

                        if (pdo_delete('xcommunity_users_region', array('usersid' => $user['uid']))) {
                            itoast('删除成功', referer(), 'success', true);
                        }
                    }
                }
                $wechat_notice = pdo_get('xcommunity_wechat_notice', array('staffid' => $id), array());
                if ($wechat_notice) {
                    pdo_delete('xcommunity_wechat_notice', array('staffid' => $id));
                }
                $notice = pdo_get('xcommunity_notice', array('staffid' => $id), array());
                if ($notice) {
                    pdo_delete('xcommunity_notice', array('staffid' => $id));
                }
                itoast('删除成功', referer(), 'success', true);
                exit();
            }
        }
    }
}
/**
 * 员工管理--打印机（已废弃）
 */
if ($op == 'print') {
    if ($p == 'add') {
        $id = intval($_GPC['id']);
        if ($id) {
            $item = pdo_get('xcommunity_print', array('id' => $id), array());
        }
        if (checksubmit('submit')) {
            $data = array(
                'uniacid'  => $_W['uniacid'],
                'type'     => $_GPC['type'],
                'uid'      => $_W['uid'],
                'api_key'  => $_GPC['api_key'],
                'deviceNo' => $_GPC['deviceNo']
            );
            if (empty($id)) {
                pdo_insert('xcommunity_print', $data);
            } else {
                pdo_update('xcommunity_print', $data, array('id' => $id));
            }
            itoast('添加成功', $this->createWebUrl('staff', array('op' => 'print')), 'success', true);
        }

        include $this->template('web/core/staff/print/add');
    } elseif ($p == 'list') {
        $pindex = max(1, intval($_GPC['page']));
        $psize = 20;
        $condition = "uniacid=:uniacid";
        $params[':uniacid'] = $_W['uniacid'];
        if ($user['type'] == 2 || $user[type] == 3) {
            $condition .= " AND uid=:uid";
            $params[':uid'] = $_W['uid'];
        }
        $sql = "SELECT * FROM" . tablename('xcommunity_print') . "WHERE $condition LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
        $list = pdo_fetchall($sql, $params);
        $total = pdo_fetchcolumn("SELECT COUNT(*) FROM" . tablename('xcommunity_print') . "WHERE $condition", $params);

        include $this->template('web/core/staff/print/list');
    } elseif ($p == 'delete') {
        $id = intval($_GPC['id']);
        if ($id) {
            $r = pdo_delete('xcommunity_print', array('id' => $id));
            if ($r) {
                $result = array(
                    'status' => 1,
                );
                echo json_encode($result);
                exit();
            }

        }
    }
}
/**
 * 员工管理--权限管理--重置密码
 */
if ($op == 'reset') {
    if ($_W['isajax']) {
        $item = pdo_get('users', array('uid' => intval($_GPC['uid'])), array());
        $member = array(
            'uid'      => intval($_GPC['uid']),
            'password' => $_GPC['pw'],
            'salt'     => $item['salt']
        );
        if (user_update($member)) {
            echo json_encode(array('status' => 1));
            exit();
        }
    }
}
/**
 * 员工管理--内部公告推送
 */
if ($op == 'send') {
    $psize = 10;
    $star = intval($_GPC['star']);
    $id = intval($_GPC['id']);
    $condition = array();
    $condition['id >'] = $star;
    $condition['memoid'] = $id;
    //先查通知内容
    $item = pdo_get('xcommunity_memo', array('id' => $id), array());
    //查物业人员
    $list = pdo_getall('xcommunity_memo_company', $condition, array(), 'id', array('id ASC'), $psize);
    if (empty($list)) {
        exit('all ok');
    }
    $company_ids = _array_column($list, 'companyid');
    $openids = pdo_getall('xcommunity_staff', array('pid' => $company_ids), array('openid'), 'id');
    $content = str_replace(array('<br>', '&nbsp;'), array("\n", ' '), $item['reason']);
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
    foreach ($openids as $k => $v) {
        if ($v['openid']) {
            $account_api = WeAccount::create();
            $status = $account_api->sendTplNotice($v['openid'], $tplid, $data, $url);
        }
    }
    foreach ($list as $row) {
        $lastid = $row['id'];
    }
    $url = $this->createWebUrl('staff', array('op' => 'send', 'star' => $lastid, 'id' => $id));
    message('正在发送下一组！', $url, 'success');
}
/**
 * 角色管理
 */
if ($op == 'role') {
    xqmenuop();
    /**
     * 角色的列表
     */
    if ($p == 'list') {
        $pindex = max(1, intval($_GPC['page']));
        $psize = 20;
        $condition = array();
        $condition['uniacid'] = $_W['uniacid'];
        $list = pdo_getslice('xcommunity_menu_role', $condition, array($pindex, $psize), $total, '', '', array('createtime desc'));
        $pager = pagination($total, $pindex, $psize);
        include $this->template('web/core/staff/role/list');
    }
    /**
     * 角色的添加编辑
     */
    if ($p == 'add') {
        $id = intval($_GPC['id']);
        if ($id) {
            $item = pdo_get('xcommunity_menu_role', array('id' => $id), array());
            if ($item['menus']) {
                $mmenus = explode(',', $item['menus']);
            }
            if ($item['menu_ops']) {
                $menu_ops = explode(',', $item['menu_ops']);
            }
        }
        if (checksubmit()) {
            $data = array(
                'uniacid'  => $_W['uniacid'],
                'title'    => trim($_GPC['title']),
                'menus'    => implode(',', $_GPC['menus']),
                'menu_ops' => implode(',', $_GPC['menuOps'])
            );
            if ($id) {
                pdo_update('xcommunity_menu_role', $data, array('id' => $id));
            } else {
                $data['createtime'] = TIMESTAMP;
                $data['uid'] = $_W['uid'];
                pdo_insert('xcommunity_menu_role', $data);
            }
            util::permlog('员工管理-角色管理-添加修改角色', '名称:' . trim($_GPC['title']));
            itoast('提交成功', referer(), $this->createWebUrl('staff', array('op' => 'perm', 'p' => 'm')), true);
        }
        $menus = $this->NavMenu();
        $menuOps = pdo_getall('xcommunity_menu_operation', array(), array());
        $menuOps_ids = _array_column($menuOps, 'pcate');
        include $this->template('web/core/staff/role/add');
    }
    /**
     * 删除角色
     */
    if ($p == 'delete') {
        $id = intval($_GPC['id']);
        if (empty($id)) {
            itoast('参数不存在', referer(), 'error');
            exit();
        }
        $item = pdo_get('xcommunity_menu_role', array('id' => $id), array());
        if (pdo_delete('xcommunity_menu_role', array('id' => $id))) {
            itoast('删除成功', referer(), 'success', true);
        }
    }
}