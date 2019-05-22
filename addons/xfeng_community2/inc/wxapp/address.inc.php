<?php
/**
 * Created by xiaoqu.
 * User: zhoufeng
 * Time: 2017/12/14 下午4:05
 */
global $_W, $_GPC;
$ops = array('list', 'detail', 'add', 'one', 'del', 'change','city');
$op = trim($_GPC['op']);
if (!in_array($op, $ops)) {
    util::send_error(-1, '参数错误');
}
if (empty($_W['member']['uid'])) {
    util::send_error(-1, '请先登录');
}
if ($op == 'list') {
    $sql = "select address,id,enable,realname,mobile,city from" . tablename('xcommunity_member_address') . "where uniacid=:uniacid and uid=:uid";
    $params[':uniacid'] = $_W['uniacid'];
    $params[':uid'] =$_W['member']['uid'];
    $list = pdo_fetchall($sql, $params);
    foreach ($list as $k => $v){
        $list[$k]['url'] = $this->createMobileUrl('address', array('op' => 'add', 'id' => $v['id']));
    }
    $data = array();
    $data['list'] = $list;
    $data['hstatus'] = set('p96') ? 1 : 0;
    util::send_result($data);

}
elseif ($op == 'one') {
    $id = intval($_GPC['id']);
    $condition = "uid=:uid";
    $params[':uid'] = $_W['member']['uid'];
    if ($id) {
        $condition .= " and id=:id";
        $params[':id'] = $id;
    }
    else {
        $condition .= " and enable=1";
    }
    $sql = "select realname,mobile,address,id,city from" . tablename('xcommunity_member_address') . "where $condition";
    $item = pdo_fetch($sql, $params);
    if($item['city']){
        $item['citys'] = explode(' ',$item['city']);
    }
//    else {
//        $item['city'] = '江苏省 南京市 浦口区';
//        $item['citys'] = array('江苏省', '南京市', '浦口区');
//    }
    $data = array();
    $data['item'] = $item;
    $data['hstatus'] = set('p96') ? 1 : 0;
    util::send_result($data);
}
elseif ($op == 'add') {
    $id = intval($_GPC['id']);
    $item = pdo_get('xcommunity_member_address', array('uid' => $_W['member']['uid'], 'enable' => 1), array('id'));
    $dat = array(
        'uniacid'  => $_W['uniacid'],
        'realname' => trim($_GPC['realname']),
        'mobile'   => trim($_GPC['mobile']),
        'address'  => trim($_GPC['address']),
        'city'     => trim($_GPC['city']),
    );
    $data = array();
    if ($id) {
        if (pdo_update('xcommunity_member_address', $dat, array('id' => $id, 'uid' => $_W['member']['uid']))) {
            $data['content'] = '修改成功';
        }
    }
    else {
        $dat['uid'] = $_W['member']['uid'];
        $dat['enable'] = $item ? 0 : 1;
        if (pdo_insert('xcommunity_member_address', $dat)) {
            $data['content'] = '添加成功';
        }
    }
    util::send_result($data);
}
elseif ($op == 'del') {
    $id = intval($_GPC['id']);
    if ($id) {
        $item = pdo_get('xcommunity_member_address', array('id' => $id, 'uid' => $_W['member']['uid']), array('id'));
        if ($item) {
            if (pdo_delete('xcommunity_member_address', array('id' => $id, 'uid' => $_W['member']['uid']))) {
                $data['content'] = '删除成功';
                util::send_result($data);
            }
        }
    }
}
elseif ($op == 'change') {
    $id = intval($_GPC['id']);
    $enable = intval($_GPC['enable']);
    if (empty($id)) {
        util::send_error(-1, 'id null');
    }
    //请求为 1 所有地址设置为0
    if ($enable == 1) {
        pdo_update('xcommunity_member_address', array('enable' => 0), array('uid' => $_W['member']['uid']));
    }
    //根据请求设置
    pdo_update('xcommunity_member_address', array('enable' => $enable), array('uid' => $_W['member']['uid'], 'id' => $id));
    util::send_result($data);
}
elseif ($op == 'city') {
    $condition = "uniacid=:uniacid and id=:id";
    $params['uniacid'] = $_W['uniacid'];
    $params[':id'] = $_SESSION['community']['regionid'] ? $_SESSION['community']['regionid'] : 1;
    $sql = "select province,city,dist from" . tablename('xcommunity_region') . "where $condition";
    $item = pdo_fetch($sql, $params);
    $data['city'] = $item['province'].' '.$item['city'].' '.$item['dist'];
    util::send_result($data);
}