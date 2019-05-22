<?php
/**
 * Created by xiaoqu.
 * User: zhoufeng
 * Time: 2017/12/25 下午11:36
 */
global $_GPC, $_W;
$ops = array('register', 'login');
$op = trim($_GPC['op']);
if (!in_array($op, $ops)) {
    util::send_error(-1, '参数错误');
}
if ($op == 'login') {

    $uid = model_user::member_check($_GPC['mobile']);
    if ($uid) {
        if (_mc_login(array('uid' => $uid))) {
            $data = array();
            $data['content'] = '登录成功';
            util::send_result($data);
        }

    }
    else {
        util::send_error(-1, '登录失败');
    }
}
elseif ($op == 'register') {

    $data = array(
        'uniacid'    => $_W['uniacid'],
        'mobile'     => $_GPC['mobile'],
        'realname'   => $_GPC['realname'],
        'createtime' => TIMESTAMP
    );
    if (model_user::member_check($data['mobile'])) {
        util::send_error(-1, '手机号已注册');
        exit();

    }
    if (pdo_insert('mc_members', $data)) {
        $uid = pdo_insertid();
        $_W['member']['uid'] = $uid;
//        $memberid = model_user::mc_register_region(intval($_GPC['regionid']), $uid);
        //
        $regionid = intval($_GPC['regionid']);
        $data = array(
            'uniacid'     => $_W['uniacid'],
            'regionid'    => $regionid,
            'uid'         => $uid,
            'createtime'  => TIMESTAMP,
            'open_status' => set('p32') || set('x14', $regionid) ? 1 : 0,
            'visit'       => 1
        );
        $status = (set('p18') || set('x1', $regionid)) && !set('p22') && !set('x4', $regionid) ? 0 : 1;
        $data['status'] = $status;
        $data['enable'] = $status == 1 ? 1 : 0;

        if (pdo_insert('xcommunity_member', $data)) {
            $memberid = pdo_insertid();

        }
        if ($memberid) {
//            $result = model_user::mc_register_address(intval($_GPC['regionid']), trim($_GPC['area']), trim($_GPC['build']), trim($_GPC['unit']), trim($_GPC['room']), $memberid, $_GPC['mobile']);



            if(_mc_login(array('uid' => $uid))){
                $_SESSION['community']['regionid'] = $regionid;
                $_SESSION['community']['id'] = $memberid;
                $data = array('content' => '注册成功');
                util::send_result($data);
//                echo json_encode(array('status' => 1, 'content' => '注册成功'));
                exit();
            }

        }
    }

}