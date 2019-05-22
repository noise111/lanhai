<?php
/**
 * Created by njlanniu.com.
 * User: 蓝牛科技
 * Date: 2018/3/31 0031
 * Time: 下午 6:20
 */
global $_GPC, $_W;
$ops = array('del', 'list', 'verify', 'manage','detail');
$op = !empty($_GPC['op']) ? $_GPC['op'] : 'list';
if (!in_array($op, $ops)) {
    message('该方法不存在(op:' . $op . ')');
}
$p = !empty($_GPC['p']) ? $_GPC['p'] : 'list';
$id = intval($_GPC['id']);
// 操作员
$user = util::xquser($_W['uid']);
if ($user && $user['menu_ops']) {
    $menu_opss = explode(',', $user['menu_ops']);
}
/**
 * 入驻申请的列表
 */
if ($op == 'list') {
    if (checksubmit('add')) {
        $id = intval($_GPC['id']);
        $data = array(
            'remark' => $_GPC['remark']
        );
        $r = pdo_update("xcommunity_app_user", $data, array("id" => $id, "uniacid" => $_W['uniacid']));
        if ($r) {
            itoast('提交成功', referer(), 'success', true);
        }
    }
    $pindex = max(1, intval($_GPC['page']));
    $psize = 10;
    $condition = " uniacid='{$_W['uniacid']}'";
    if (!empty($_GPC['keyword'])) {
        $condition .= " AND realname LIKE '%{$_GPC['keyword']}%'";
    }
    $sql = "select * from " . tablename("xcommunity_app_user") . "where $condition order by createtime desc LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
    $list = pdo_fetchall($sql);
    $total = pdo_fetchcolumn('select count(*) from' . tablename("xcommunity_app_user") . "where $condition");
    $pager = pagination($total, $pindex, $psize);
    include $this->template('web/core/settled/list');
}
/**
 * 入驻申请的删除
 */
if ($op == 'del') {
    if ($id) {
        $item = pdo_get('xcommunity_app_user', array('id' => $id), array());
        if ($item) {
            if (pdo_delete("xcommunity_app_user", array('id' => $id))) {
                util::permlog('入驻申请-删除', '入驻联系人:' . $item['realname']);
                itoast('删除成功', referer(), 'success');
            }
        }

    }
}
/**
 * 入驻申请的审核
 */
if ($op == 'verify') {
    $id = intval($_GPC['id']);
    $item = pdo_get('xcommunity_app_user', array('id' => $id), array('realname', 'tel', 'address', 'category', 'status', 'remark'));
    if ($item['category'] == 1) {
        $type = 3;
        $xqtype = 1;
    } elseif ($item['category'] == 2) {
        $type = 5;
        $xqtype = 2;
    } elseif ($item['category'] == 3) {
        $type = 4;
        $xqtype = 2;
    }
    $data = array(
        'uniacid' => $_W['uniacid'],
        'realname' => $item['realname'],
        'mobile' => $item['tel'],
        'remark' => $item['remark'],
        'type' => 1,
    );
    $staff = pdo_get('xcommunity_staff', array('mobile' => $data['mobile']), array('id'));
    if ($staff) {
//        itoast('手机号已经存在', referer(), 'error', ture);
//        exit();
        $staffid = $staff['id'];
    } else {
        $data['uid'] = $_W['uid'];
        pdo_insert('xcommunity_staff', $data);
        $staffid = pdo_insertid();
        util::permlog('员工管理-通讯录-添加', '添加员工姓名:' . $data['realname']);
    }
    load()->model('user');
    $member = array();
    $password = '12345678';
    $member['password'] = $password;
    $member['status'] = 1;
    //小区用户表
    $dat = array(
        'type' => $type,
        'createtime' => TIMESTAMP,
    );
    $dat['uniacid'] = $_W['uniacid'];
    $uuid = $_W['uid'];
    $dat['uuid'] = $uuid;
    $dat['staffid'] = $staffid;
    $dat['xqtype'] = $xqtype ? $xqtype : $user['xqtype'];
    //添加到系统用户表中
    $member['username'] = $item['tel'];
    if (user_check(array('username' => $member['username']))) {
        itoast('非常抱歉，此用户名已经被注册，你需要更换注册名称！', referer(), 'error', true);
    }
    $member['remark'] = $item['remark'];
    $member['groupid'] = 1;
    $user_uid = user_register($member);
    //关联添加到小区的用户表，主要是做一些其他权限使用
    $dat['uid'] = $user_uid;
    pdo_insert('xcommunity_users', $dat);
    //系统用户表
    $user = array(
        'uniacid' => $_W['uniacid'],
        'uid' => $user_uid,
        'role' => 'operator',
    );
    pdo_insert('uni_account_users', $user);
    //系统用户表
    $insert = array(
        'uniacid' => $_W['uniacid'],
        'uid' => $user_uid,
        'type' => $this->module['name'],
        'permission' => 'all'
    );
    pdo_insert('users_permission', $insert);
    util::permlog('员工管理-权限管理-添加', '添加用户名:' . $member['username']);
    $regionids = explode(',', $_GPC['regionids']);
    if ($regionids) {
        foreach ($regionids as $key => $value) {
            $dat = array(
                'usersid' => $user_uid,
                'regionid' => $value,
            );
            pdo_insert('xcommunity_users_region', $dat);
        }
    }
    pdo_update('xcommunity_app_user', array('status' => 1), array('id' => $id));
    itoast('用户审核成功，默认密码为12345678！', $this->createWebUrl('settled', array('op' => 'list')), 'success', true);
}
/**
 * 入驻申请的接收员
 */
if ($op == 'manage') {
    $d = '';
    if ($user) {
        if ($user['type'] == 2) {
            $d = " and uid ={$_W['uid']}";
        } elseif ($user['type'] == 3) {
            $d = " and id={$user['pid']}";
        }
    }
    $properties = model_region::property_fetall($d);
    $con = " uniacid=:uniacid";
    $par[':uniacid'] = $_W['uniacid'];
    if ($user) {
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
    /**
     * 入驻申请接收员的列表
     */
    if ($p == 'list') {
        $condition = " t1.uniacid='{$_W['uniacid']}' AND t1.enable = 4";
        if ($user['type'] == 2 || $user['type'] == 3) {
            $condition .= " AND t1.uid='{$_W['uid']}'";
        }
        $sql = "select t1.*,t2.realname from" . tablename('xcommunity_notice') . "t1 left join" . tablename('xcommunity_staff') . "t2 on t1.staffid=t2.id where $condition";
        $list = pdo_fetchall($sql);
        include $this->template('web/core/settled/manage_list');
    }
    /**
     * 入驻申请接收员的添加
     */
    if ($p == 'add') {
        if ($id) {
            $sql = "select t1.*,t2.realname,t3.title,t3.pid,t2.id as staffid from" . tablename('xcommunity_notice') . "t1 left join" . tablename('xcommunity_staff') . "t2 on t1.staffid= t2.id left join" . tablename('xcommunity_department') . 't3 on t2.departmentid=t3.id left join' . tablename('xcommunity_property') . "t4 on t4.id = t3.pid where t1.id=:id";
            $item = pdo_fetch($sql, array(':id' => $id));
            if (empty($item)) {
                itoast('该信息不存在或已删除', referer(), 'error', ture);
            }
        }
        if ($_W['isajax']) {
            $birth = $_GPC['birth'];
            $data = array(
                'uniacid' => $_W['uniacid'],
                'enable' => 4,
                'type' => intval($_GPC['type']),
//                'province' => $birth['province'],
//                'city'     => $birth['city'],
//                'dist'     => $birth['district'],
                'staffid' => intval($_GPC['staffid'])
            );
            if ($id) {
                pdo_update('xcommunity_notice', $data, array('id' => $id, 'enable' => 4));
                util::permlog('', '入驻接收员信息修改,信息ID:' . $id);
            } else {
                $data['uid'] = $_W['uid'];
                $staf = pdo_get('xcommunity_notice', array('staffid' => $data['staffid'], 'enable' => 4), array('id'));
                if ($staf) {
                    echo json_encode(array('content' => '接收员已添加'));
                    exit();
                }
                if (pdo_insert('xcommunity_notice', $data)) {
                    $id = pdo_insertid();
                    util::permlog('', '入驻接收员信息添加,信息ID:' . $id);
                }
            }
            echo json_encode(array('status' => 1));
            exit();
        }
        include $this->template('web/core/settled/manage_add');
    }
    /**
     * 入驻申请接收员的删除
     */
    if ($p == 'del') {
        $id = intval($_GPC['id']);
        if ($id) {
            $r = pdo_delete('xcommunity_notice', array('id' => $id));
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
 * 入驻申请的查看
 */
if ($op == 'detail') {
    $item = pdo_get('xcommunity_app_user', array('id' => $id), array());
    include $this->template('web/core/settled/detail');
}