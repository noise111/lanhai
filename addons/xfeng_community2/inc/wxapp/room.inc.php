<?php
/**
 * Created by xiaoqu.
 * User: zhoufeng
 * Time: 2018/1/7 上午1:17
 */
global $_W, $_GPC;
$ops = array('list', 'detail', 'add', 'one', 'del', 'change','member','appdetail');
$op = trim($_GPC['op']);
if (!in_array($op, $ops)) {
    util::send_error(-1, '参数错误');
}
if ($op == 'change') {
    if (empty($_W['member']['uid'])) {
        util::send_error(-1, '请先登录');
    }
    $id = intval($_GPC['id']);
    $enable = intval($_GPC['enable']);
    $memberid = $_SESSION['community']['id'];
    if (empty($id)) {
        util::send_error(-1, 'id null');
    }
    $total = pdo_fetchcolumn("select count(*) from".tablename('xcommunity_member_bind')."where memberid=:memberid",array(':memberid'=>$memberid));
    if($total ==1){
        pdo_update('xcommunity_member_bind', array('enable' => 1), array('memberid' => $memberid, 'id' => $id));
        return false;
    }
    //请求为 1 所有地址设置为0
    if ($enable == 1) {
        pdo_update('xcommunity_member_bind', array('enable' => 0), array('memberid' => $memberid));
    }
    //根据请求设置
    pdo_update('xcommunity_member_bind', array('enable' => $enable), array('memberid' => $memberid, 'id' => $id));
    util::send_result($data);
}
