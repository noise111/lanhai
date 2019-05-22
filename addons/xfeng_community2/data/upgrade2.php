<?php
/**
 * Created by njlanniu.
 * User: njlanniu
 * Time: 2018/9/26 下午11:42
 */
//更新数据 适用更新9.2.8.3.0 出现问题后，解决方法
$member_sql = "select * from" . tablename('xcommunity_member_address') . "where 1";
$members = pdo_fetchall($member_sql);
foreach ($members as $key => $member) {
    if ($member['memberid']) {
        $user = pdo_get('xcommunity_member', array('id' => $member['memberid']), array('uniacid', 'regionid', 'uid'));
        pdo_update('xcommunity_member_address', array('regionid' => $user['regionid'], 'uniacid' => $user['uniacid'], 'uid' => $user['uid']), array('id' => $member['id']));
    }

    $bind = pdo_get('xcommunity_member_bind', array('memberid' => $member['memberid']), array('id', 'addressid'));
    if (empty($bind['addressid'])) {
        $data = array(
            'memberid'   => $member['memberid'],
            'createtime' => $member['createtime'],
            'status'     => $member['status'],
            'enable'     => $member['enable'],
            'uniacid'    => $user['uniacid'],
            'addressid'  => $member['id']
        );
        pdo_insert('xcommunity_member_bind', $data);
    }

    $item = pdo_get('xcommunity_member_room', array('id' => $member['id']), array('id'));
    if (empty($item)) {
        $dat = array(
            'id'         => $member['id'],
            'createtime' => $member['createtime'],
            'regionid'   => $user['regionid'],
            'area'       => $member['area'],
            'build'      => $member['build'],
            'unit'       => $member['unit'],
            'room'       => $member['room'],
            'address'    => $member['address'],
            'code'       => $member['code'],
            'square'     => $member['square'],
            'enable'     => $member['enable'],
            'uniacid'    => $user['uniacid'],
        );
        pdo_insert('xcommunity_member_room', $dat);
    }
    $log = pdo_get('xcommunity_member_log', array('regionid' => $user['regionid'], 'addressid' => $member['id']), array('id'));
    if (empty($log)) {
        $u = pdo_fetch("select t1.realname,t1.mobile from" . tablename('mc_members') . "t1 left join" . tablename('xcommunity_member') . "t2 on t2.uid = t1.uid where t2.uid=:uid", array(':uid' => $user['uid']));

        $d = array(
            'uniacid'   => $_W['uniacid'],
            'regionid'  => intval($user['regionid']),
            'realname'  => $u['realname'],
            'mobile'    => $u['mobile'],
            'addressid' => $member['id'],
            'status'    => 1
        );
        pdo_insert('xcommunity_member_log', $d);


    }


}