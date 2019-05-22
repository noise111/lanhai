<?php
/**
 * Created by 小区秘书.
 * User: 蓝牛科技
 * Date: 2016/11/10
 * Time: 下午10:01
 * Function:
 */
global $_W, $_GPC;
$op = in_array($_GPC['op'], array('list', 'alipay', 'wechat', 'sub', 'del', 'service', 'cmbc', 'swiftpass','hsyunfu')) ? $_GPC['op'] : 'list';
$operation = in_array($_GPC['operation'], array('add', 'list', 'del')) ? $_GPC['operation'] : 'list';
$user = util::xquser($_W['uid']);
$uniacid = intval($_W['uniacid']);
$condition = '';

if (intval($_GPC['uuid'])) {
    $uuid = intval($_GPC['uuid']);
    $condition = " AND x.uuid='{$uuid}'";
}
else {
    if($user){
        $condition = " AND x.uuid='{$_W['uid']}'";
    }

}
$permission = pdo_fetchall("SELECT u.uid,x.id as id FROM " . tablename('uni_account_users') . "as u left join " . tablename('xcommunity_users') . "as x on u.uid = x.uid WHERE x.uniacid = '{$uniacid}' and  x.type !=6 $condition", array(), 'uid');
if (!empty($permission)) {
    $member = pdo_fetchall("SELECT username, uid,status FROM " . tablename('users') . "WHERE uid IN (" . implode(',', array_keys($permission)) . ")", array(), 'uid');
}
if ($op == 'list') {
    if (checksubmit('submit')) {
        foreach ($_GPC['payapi'] as $key => $val) {
            $item = pdo_get('xcommunity_setting', array('xqkey' => $key), array('id'));
            $data = array(
                'xqkey'   => $key,
                'value'   => $val,
                'uniacid' => $_W['uniacid']
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
    $set = pdo_getall('xcommunity_setting', array('uniacid' => $_W['uniacid']), array(), 'xqkey', array());
    include $this->template('web/core/payapi/list');
}
elseif ($op == 'wechat') {
    if ($operation == 'add') {
        $regions = model_region::region_fetall();
        $id = intval($_GPC['id']);
        if ($id) {
            $item = pdo_get('xcommunity_wechat', array('id' => $id), array());

        }

        if (checksubmit('submit')) {
            $data = array(
                'uniacid'   => $_W['uniacid'],
                'appid'     => $_GPC['appid'],
                'appsecret' => $_GPC['appsecret'],
                'mchid'     => $_GPC['mchid'],
                'apikey'    => $_GPC['apikey'],
                'type'      => intval($_GPC['type']),
            );

            $data['userid'] = $data['type'] != 3 && $data['type'] == 2 ? $_GPC['userid'] : $_GPC['regionid'];
            if ($id) {
                pdo_update('xcommunity_wechat', $data, array('id' => $id));
                util::permlog('', '修改借用支付ID:' . $id);
            }
            else {
                $data['uid'] = $_W['uid'];
                pdo_insert('xcommunity_wechat', $data);
                $id = pdo_insertid();
                util::permlog('', '添加借用支付ID:' . $id);
            }
            itoast('提交成功', referer(), 'success', ture);
        }
        include $this->template('web/core/payapi/wechat/add');
    }
    elseif ($operation == 'list') {
        $pindex = max(1, intval($_GPC['page']));
        $psize = 20;
        $condition = '';
        if ($user[type] != 1) {
            $condition .= " and uid = {$_W['uid']}";
        }
        $sql = "SELECT * FROM" . tablename('xcommunity_wechat') . "WHERE uniacid=:uniacid LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
        $list = pdo_fetchall($sql, array(':uniacid' => $_W['uniacid']));
        foreach ($list as $key => $val) {
            if ($val['type'] == 1) {
                $region = model_region::region_check($val['userid']);
                $username = $region['title'];
            }
            elseif ($val['type'] == 2) {
                $user = pdo_get('users', array('uid' => $val['userid']), array('username'));
                $username = $user['username'];
            }
            $list[$key]['username'] = $username;
        }
        $total = pdo_fetchcolumn("SELECT COUNT(*) FROM" . tablename('xcommunity_wechat') . " WHERE uniacid='{$_W['uniacid']}'");
        $pager = pagination($total, $pindex, $psize);
        include $this->template('web/core/payapi/wechat/list');
    }
    elseif ($operation == 'del') {
        $id = intval($_GPC['id']);
        if ($id) {
            if (pdo_delete('xcommunity_wechat', array('id' => $id))) {
                util::permlog('', '删除借用支付ID:' . $id);
                itoast('删除成功', referer(), 'success', ture);
                exit();
            }
        }
    }
}
elseif ($op == 'alipay') {
    if ($operation == 'add') {
        $regions = model_region::region_fetall();
        $id = intval($_GPC['id']);
        if ($id) {
            $item = pdo_get('xcommunity_alipayment', array('id' => $id), array());
        }
        if (checksubmit('submit')) {
            $data = array(
                'uniacid' => $_W['uniacid'],
                'type'    => intval($_GPC['type']),
                'account' => $_GPC['account'],
                'partner' => $_GPC['partner'],
                'secret'  => $_GPC['secret']
            );
            $data['userid'] = $data['type'] != 3 && $data['type'] == 2 ? $_GPC['userid'] : $_GPC['regionid'];
            if ($id) {
                pdo_update('xcommunity_alipayment', $data, array('id' => $id));
                util::permlog('', '修改支付宝ID:' . $id);
            }
            else {
                $data['uid'] = $_W['uid'];
                pdo_insert('xcommunity_alipayment', $data);
                $id = pdo_insertid();
                util::permlog('', '添加支付宝ID:' . $id);
            }
            itoast('提交成功', referer(), 'success', ture);
        }
        include $this->template('web/core/payapi/alipay/add');
    }
    elseif ($operation == 'list') {
        $pindex = max(1, intval($_GPC['page']));
        $psize = 20;
        $condition = '';
        if ($user[type] != 1) {
            $condition .= " and uid = {$_W['uid']}";
        }

        $sql = "SELECT * FROM" . tablename('xcommunity_alipayment') . "WHERE uniacid=:uniacid LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
        $list = pdo_fetchall($sql, array(':uniacid' => $_W['uniacid']));
        foreach ($list as $key => $val) {
            if ($val['type'] == 1) {
                $region = model_region::region_check($val['userid']);
                $username = $region['title'];
            }
            elseif ($val['type'] == 2) {
                $user = pdo_get('users', array('uid' => $val['userid']), array('username'));
                $username = $user['username'];
            }
            $list[$key]['username'] = $username;
        }

        $total = pdo_fetchcolumn("SELECT COUNT(*) FROM" . tablename('xcommunity_alipayment') . "WHERE uniacid=:uniacid", array(':uniacid' => $_W['uniacid']));
        $pager = pagination($total, $pindex, $psize);
        include $this->template('web/core/payapi/alipay/list');
    }
    elseif ($operation == 'del') {
        $id = intval($_GPC['id']);
        if ($id) {
            if (pdo_delete('xcommunity_alipayment', array('id' => $id))) {
                itoast('删除成功', referer(), 'success', ture);
                exit();
            }
        }
    }
}
elseif ($op == 'sub') {
    if ($operation == 'add') {
        $regions = model_region::region_fetall();
        $id = intval($_GPC['id']);
        if ($id) {
            $item = pdo_get('xcommunity_service_data', array('id' => $id), array());
        }
        if (checksubmit('submit')) {
            $data = array(
                'uniacid'    => $_W['uniacid'],
                'sub_mch_id' => $_GPC['sub_mch_id'],
                'type'       => intval($_GPC['type']),
            );
            $data['userid'] = $data['type'] != 3 && $data['type'] == 2 ? $_GPC['userid'] : $_GPC['cid'];
            if ($id) {
                pdo_update('xcommunity_service_data', $data, array('id' => $id));
                util::permlog('', '修改子商户ID:' . $id);
            }
            else {
                $data['uid'] = $_W['uid'];
                pdo_insert('xcommunity_service_data', $data);
                $id = pdo_insertid();
                util::permlog('', '添加子商户ID:' . $id);
            }
            itoast('提交成功', referer(), 'success', ture);
        }
        include $this->template('web/core/payapi/sub/add');
    }
    elseif ($operation == 'list') {
        $pindex = max(1, intval($_GPC['page']));
        $psize = 20;
        $condition = '';
        if ($user[type] != 1) {
            $condition .= " and uid = {$_W['uid']}";
        }
        $sql = "SELECT * FROM" . tablename('xcommunity_service_data') . "WHERE uniacid=:uniacid LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
        $list = pdo_fetchall($sql, array(':uniacid' => $_W['uniacid']));
        foreach ($list as $key => $val) {
            if ($val['type'] == 1) {

                $region = model_region::region_check($val['userid']);
                $username = $region['title'];
            }
            elseif ($val['type'] == 2) {
                $user = pdo_get('users', array('uid' => $val['userid']), array('username'));
                $username = $user['username'];
            }
            $list[$key]['username'] = $username;
        }
        $total = pdo_fetchcolumn("SELECT COUNT(*) FROM" . tablename('xcommunity_service_data') . " WHERE uniacid='{$_W['uniacid']}'");
        $pager = pagination($total, $pindex, $psize);
        include $this->template('web/core/payapi/sub/list');
    }
    elseif ($operation == 'del') {
        $id = intval($_GPC['id']);
        if ($id) {
            if (pdo_delete('xcommunity_service_data', array('id' => $id))) {
                util::permlog('', '删除子商户ID:' . $id);
                itoast('删除成功', referer(), 'success');
                exit();
            }
        }
    }
}
elseif ($op == 'service') {
    if (checksubmit('submit')) {
        foreach ($_GPC['service'] as $key => $val) {
            $item = pdo_get('xcommunity_setting', array('xqkey' => $key, 'uniacid' => $_W['uniacid']), array('id'));
            $data = array(
                'xqkey'   => $key,
                'value'   => $val,
                'uniacid' => $_W['uniacid']
            );
            if ($item) {
                pdo_update('xcommunity_setting', $data, array('id' => $item['id'], 'uniacid' => $_W['uniacid']));
            }
            else {
                pdo_insert('xcommunity_setting', $data);
            }
        }
        util::permlog('', '修改服务商账号');
        itoast('操作成功', referer(), 'success', ture);
    }
    $pay = pdo_getall('xcommunity_setting', array('uniacid' => $_W['uniacid']), array(), 'xqkey', array());
    include $this->template('web/core/payapi/sub/service');
}
elseif ($op == 'swiftpass') {
    if ($operation == 'add') {
        $regions = model_region::region_fetall();
        $id = intval($_GPC['id']);
        if ($id) {
            $item = pdo_get('xcommunity_swiftpass', array('id' => $id), array());
        }
        if (checksubmit('submit')) {
            $data = array(
                'uniacid'   => $_W['uniacid'],
                'type'      => intval($_GPC['type']),
                'account'   => trim($_GPC['account']),
                'secret'    => trim($_GPC['secret']),
                'appid'     => trim($_GPC['appid']),
                'appsecret' => trim($_GPC['appsecret'])
            );
            $data['userid'] = $data['type'] != 3 && $data['type'] == 2 ? $_GPC['userid'] : $_GPC['regionid'];
            if ($id) {
                pdo_update('xcommunity_swiftpass', $data, array('id' => $id));
                util::permlog('', '修改威富通微信支付ID:' . $id);
            }
            else {
                $data['uid'] = $_W['uid'];
                pdo_insert('xcommunity_swiftpass', $data);
                $id = pdo_insertid();
                util::permlog('', '添加威富通微信支付ID:' . $id);
            }
            itoast('提交成功', referer(), 'success', ture);
        }
        include $this->template('web/core/payapi/swiftpass/add');
    }
    elseif ($operation == 'list') {
        $pindex = max(1, intval($_GPC['page']));
        $psize = 20;
        $condition = '';
        if ($user[type] != 1) {
            $condition .= " and uid = {$_W['uid']}";
        }

        $sql = "SELECT * FROM" . tablename('xcommunity_swiftpass') . "WHERE uniacid=:uniacid LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
        $list = pdo_fetchall($sql, array(':uniacid' => $_W['uniacid']));

        foreach ($list as $key => $val) {
            if ($val['type'] == 1) {
                $region = model_region::region_check($val['userid']);
                $username = $region['title'];
            }
            elseif ($val['type'] == 2) {
                $user = pdo_get('users', array('uid' => $val['userid']), array('username'));
                $username = $user['username'];
            }
            $list[$key]['username'] = $username;
        }

        $total = pdo_fetchcolumn("SELECT COUNT(*) FROM" . tablename('xcommunity_swiftpass') . "WHERE uniacid=:uniacid", array(':uniacid' => $_W['uniacid']));
        $pager = pagination($total, $pindex, $psize);

        include $this->template('web/core/payapi/swiftpass/list');
    }
    elseif ($operation == 'del') {
        $id = intval($_GPC['id']);
        if ($id) {
            if (pdo_delete('xcommunity_swiftpass', array('id' => $id))) {
                itoast('删除成功', referer(), 'success', ture);
                exit();
            }
        }
    }
}
elseif ($op == 'hsyunfu') {
    if ($operation == 'add') {
        $regions = model_region::region_fetall();
        $id = intval($_GPC['id']);
        if ($id) {
            $item = pdo_get('xcommunity_hsyunfu', array('id' => $id), array());
        }
        if (checksubmit('submit')) {
            $data = array(
                'uniacid'   => $_W['uniacid'],
                'type'      => intval($_GPC['type']),
                'account'   => trim($_GPC['account']),
                'secret'    => trim($_GPC['secret']),
                'appid'     => trim($_GPC['appid']),
                'appsecret' => trim($_GPC['appsecret'])
            );
            $data['userid'] = $data['type'] != 3 && $data['type'] == 2 ? $_GPC['userid'] : $_GPC['regionid'];
            if ($id) {
                pdo_update('xcommunity_hsyunfu', $data, array('id' => $id));
                util::permlog('', '修改华商云付微信支付ID:' . $id);
            }
            else {
                $data['uid'] = $_W['uid'];
                pdo_insert('xcommunity_hsyunfu', $data);
                $id = pdo_insertid();
                util::permlog('', '添加华商云付微信支付ID:' . $id);
            }
            itoast('提交成功', referer(), 'success', ture);
        }
        include $this->template('web/core/payapi/hsyunfu/add');
    }
    elseif ($operation == 'list') {
        $pindex = max(1, intval($_GPC['page']));
        $psize = 20;
        $condition = '';
        if ($user[type] != 1) {
            $condition .= " and uid = {$_W['uid']}";
        }

        $sql = "SELECT * FROM" . tablename('xcommunity_hsyunfu') . "WHERE uniacid=:uniacid LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
        $list = pdo_fetchall($sql, array(':uniacid' => $_W['uniacid']));

        foreach ($list as $key => $val) {
            if ($val['type'] == 1) {
                $region = model_region::region_check($val['userid']);
                $username = $region['title'];
            }
            elseif ($val['type'] == 2) {
                $user = pdo_get('users', array('uid' => $val['userid']), array('username'));
                $username = $user['username'];
            }
            $list[$key]['username'] = $username;
        }

        $total = pdo_fetchcolumn("SELECT COUNT(*) FROM" . tablename('xcommunity_hsyunfu') . "WHERE uniacid=:uniacid", array(':uniacid' => $_W['uniacid']));
        $pager = pagination($total, $pindex, $psize);

        include $this->template('web/core/payapi/hsyunfu/list');
    }
    elseif ($operation == 'del') {
        $id = intval($_GPC['id']);
        if ($id) {
            if (pdo_delete('xcommunity_hsyunfu', array('id' => $id))) {
                itoast('删除成功', referer(), 'success', ture);
                exit();
            }
        }
    }
}