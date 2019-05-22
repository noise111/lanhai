<?php
/**
 * Created by njlanniu.com.
 * User: 蓝牛科技
 * Time: 2017/10/22 下午3:54
 */
global $_GPC,$_W;
$op = in_array(trim($_GPC['op']),array('add','list','edit','user')) ? $_GPC['op'] : 'list';
$id = intval($_GPC['id']);

if ($op == 'user') {

    $pindex = max(1, intval($_GPC['page']));
    $psize = 20;
    $where = ' WHERE uid != 1';
    $params = array();
    if ($_GPC['status'] > 0) {
        $where .= " AND status = :status";
        $params[':status'] = intval($_GPC['status']);
    }
    if (!empty($_GPC['username'])) {
        $where .= " AND username LIKE :username";
        $params[':username'] = "%{$_GPC['username']}%";
    }
    $sql = 'SELECT * FROM ' . tablename('users') .$where . " LIMIT " . ($pindex - 1) * $psize .',' .$psize;
    $users = pdo_fetchall($sql, $params);
    foreach ($users as $key => $value) {
        $u = pdo_fetch("SELECT groupid FROM".tablename('xcommunity_users')."WHERE uid=:uid",array(':uid' => $value['uid']));
        $group = pdo_fetch("SELECT title FROM".tablename('xcommunity_users_group')."WHERE id=:id",array(":id" => $u['groupid']));
        $users[$key]['title'] = $group['title'];
    }

    $total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('users') . $where, $params);
    $pager = pagination($total, $pindex, $psize);

    include $this->template('web/core/group/user');
}elseif ($op == 'edit') {
    $uid = intval($_GPC['uid']);
    $user = user_single($uid);
    $groups = pdo_fetchall("SELECT id, title FROM ".tablename('xcommunity_users_group')." ORDER BY id ASC");
    if ($uid) {
        //是否是操作员
        $item = pdo_fetch("SELECT * FROM".tablename('xcommunity_users')."WHERE uid=:uid and type != 4 ",array(':uid' => $uid));
        $id = $item['id'];
    }
    if (checksubmit('submit')) {
        $data = array(
            'groupid' => intval($_GPC['groupid']),
        );
        if ($id) {
            pdo_update('xcommunity_users',$data,array('id' => $id));
        }else{
            $data['uid'] = $uid;
            $data['type'] = 6;//普通用户
            pdo_insert('xcommunity_users',$data);
        }
        itoast('确定提交',referer(),'success',true);
    }
    include $this->template('web/core/group/edit');
}elseif ($op == 'add') {
    $id = intval($_GPC['id']);
    if (!empty($id)) {
        $group = pdo_fetch("SELECT * FROM ".tablename('xcommunity_users_group') . " WHERE id = :id", array(':id' => $id));
    }
    if (checksubmit('submit')) {
        if (empty($_GPC['title'])) {
            itoast('请输入用户组名称！');
        }
        $data = array(
            'title' => $_GPC['title'],
            'maxaccount' => intval($_GPC['maxaccount']),
        );
        if (empty($id)) {
            pdo_insert('xcommunity_users_group', $data);
        } else {
            pdo_update('xcommunity_users_group', $data, array('id' => $id));
        }
        itoast('用户组更新成功！', referer(), 'success',true);
    }


    include $this->template('web/core/group/add');
}elseif($op == 'list'){
    if (checksubmit('submit')) {
        if (!empty($_GPC['delete'])) {
            pdo_query("DELETE FROM ".tablename('xcommunity_users_group')." WHERE id IN ('".implode("','", $_GPC['delete'])."')");
        }
        itoast('用户组更新成功！', referer(), 'success',true);
    }
    $list = pdo_fetchall("SELECT * FROM ".tablename('xcommunity_users_group')."");
    include $this->template('web/core/group/list');
}