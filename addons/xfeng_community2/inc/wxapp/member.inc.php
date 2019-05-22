<?php
/**
 * Created by xiaoqu.
 * User: zhoufeng
 * Time: 2017/12/16 下午5:26
 */
global $_GPC, $_W;
$ops = array('list', 'detail', 'add', 'update', 'family', 'create', 'detail', 'home', 'userinfo', 'check');
$op = trim($_GPC['op']);
if (!in_array($op, $ops)) {
    util::send_error(-1, '参数错误');
}
if ($op == 'list') {
//    $visit = intval($_GPC['visit']);
//    if (empty($visit)) {
    $sql = 'select t1.address,t2.enable,t2.id,t1.id as roomid from' . tablename('xcommunity_member_room') . "t1 left join" . tablename('xcommunity_member_bind') . "t2 on t1.id = t2.addressid left join" . tablename('xcommunity_member') . "t3 on t3.id=t2.memberid where t3.regionid=:regionid and t3.uniacid=:uniacid and t3.id=:id";
    $params[':uniacid'] = $_W['uniacid'];
    $params[':id'] = $_SESSION['community']['id'];
    $params[':regionid'] = $_SESSION['community']['regionid'];
    $list = pdo_fetchall($sql, $params);
    foreach ($list as $k => $v) {
        $list[$k]['key'] = $v['roomid'];
        $list[$k]['value'] = $v['address'];
    }

    $data = array();
    $data['list'] = $list;
    $data['region'] = $_SESSION['community']['title'];
    $data['hstatus'] = set('p96') ? 1 : 0;
    $data['url'] = $this->createMobileUrl('room', array('op' => 'add'));
    util::send_result($data);

//    }
//    else {
////        $data = array();
////        $data['visit']=1;
////        $data['list'] = 0;
////        util::send_result($data);
//        util::send_error(-1, '暂未绑定房号');
//    }

}
elseif ($op == 'update') {
    //更新手机号和姓名
    $mobile = trim($_GPC['mobile']);
    $realname = trim($_GPC['realname']);
    $license = trim($_GPC['license']);
    $addressid = $_SESSION['community']['addressid'];
    $regionid = $_SESSION['community']['regionid'];
    $data = array();
    if (empty($_W['member']['uid'])) {
        util::send_error(-1, '非法操作');
        exit();
    }
    if ($mobile) {
        $data['mobile'] = $mobile;
    }
    if ($realname) {
        $data['realname'] = $realname;
    }
    if ($license) {
        $car = pdo_get('xcommunity_xqcars', array('addressid' => $addressid), array());
        if (empty($car)) {
            $card = array(
                'uniacid'   => $_W['uniacid'],
                'regionid'  => $regionid,
                'realname'  => $_W['member']['realname'],
                'mobile'    => $_W['member']['mobile'],
                'car_num'   => $license,
                'addressid' => $addressid
            );
            pdo_insert('xcommunity_xqcars', $card);
        }
        else {
            pdo_update('xcommunity_xqcars', array('car_num' => $license), array('addressid' => $addressid));
        }
        pdo_update('xcommunity_member', array('license' => $license), array('id' => $_SESSION['community']['id']));
        $data = array();
        $data['content'] = '修改成功';
        $_SESSION['coummunity']['license'] = $license;
        util::send_result($data);
        exit();
    }
    if ($mobile || $realname) {
        if (pdo_update('mc_members', $data, array('uid' => $_W['member']['uid']))) {
            $data = array();
            $data['content'] = '修改成功';
            util::send_result($data);
            exit();
        }
    }


}
elseif ($op == 'family') {
    $uid = $_W['member']['uid'];
    $sql = "select t2.realname,t2.mobile,t2.id,t2.status,t1.to_uid from" . tablename('xcommunity_member_family') . "t1 left join" . tablename('xcommunity_member_log') . "t2 on t1.logid = t2.id where t1.from_uid=:uid order by t1.id desc ";
    $list = pdo_fetchall($sql, array(':uid' => $uid));
    $data = array();
    $data['hstatus'] = set('p96') ? 1 : 0;
    $data['list'] = $list;
    util::send_result($data);
}
elseif ($op == 'create') {
    $d = array(
        'uniacid'   => $_W['uniacid'],
        'regionid'  => $_SESSION['community']['regionid'],
        'realname'  => trim($_GPC['realname']),
        'mobile'    => trim($_GPC['mobile']),
        'addressid' => intval($_GPC['roomid']),
        'status'    => intval($_GPC['status'])
    );
    if (pdo_insert('xcommunity_member_log', $d)) {
        $logid = pdo_insertid();
        if ($logid) {
            $dat = array(
                'from_uid'  => $_W['member']['uid'],
                'addressid' => $d['addressid'],
                'logid'     => $logid
            );
            if (pdo_insert('xcommunity_member_family', $dat)) {
                //$url = $this->createMobileUrl('member', array('op' => 'share', 'logid' => $logid));
                $url = $_W['siteroot'] . "app/index.php?i={$_W['uniacid']}&c=entry&logid={$logid}&do=share&m=" . $this->module['name'];//二维码内容

//                $temp = $d['realname'] . "-" . $d['mobile'] . ".png";
//                $tmpdir = "../addons/" . $this->module['name'] . "/data/qrcode/family/" . $_W['uniacid'] . "/" . $_SESSION['community']['title'] . "/";
//                $imgHtml = createQr($url, $temp, $tmpdir);

                $data = array();
                $data['url'] = $url;
                util::send_result($data);

            }
        }

    }

}
elseif ($op == 'detail') {
    $id = intval($_GPC['id']);
    if (empty($id)) {
        util::send_error(-1, '参数错误');
    }
    $sql = "select t1.*,t2.address from" . tablename('xcommunity_member_log') . "t1 left join" . tablename('xcommunity_member_room') . "t2 on t1.addressid = t2.id where t1.id=:id";
    $item = pdo_fetch($sql, array(':id' => $id));
    if ($item) {
        $url = $_W['siteroot'] . "app/index.php?i={$_W['uniacid']}&c=entry&logid={$id}&do=share&m=" . $this->module['name'];//二维码内容
        $item['img'] = $url;
    }
//    $img = $_W['siteroot'] . "/addons/" . $this->module['name'] . "/template/family_qr/qrcode_{$_W['uniacid']}_{$item['id']}.png";
    $item['hstatus'] = set('p96') ? 1 : 0;
    util::send_result($item);

}
elseif ($op == 'home') {

    $regionid = $_SESSION['community']['regionid'];
//    $regionid = 1;
    $sql = "select t1.id,t1.title from" . tablename('xcommunity_housecenter') . "as t1 left join " . tablename('xcommunity_housecenter_region') . "as t2 on t1.id=t2.nid where t1.uniacid=:uniacid and t2.regionid=:regionid and t1.status = 1 and t1.pcate = 0 order by t1.displayorder asc,t1.id asc ";
    $category = pdo_fetchall($sql, array(":uniacid" => $_W['uniacid'], ':regionid' => $regionid));
    $children = array();

    if (!empty($category)) {
        $children = '';
        foreach ($category as $cid => $cate) {
            $tsql = "select t1.id,t1.title,t1.url,t1.thumb,t1.show,t1.enable from" . tablename('xcommunity_housecenter') . "as t1 left join " . tablename('xcommunity_housecenter_region') . "as t2 on t1.id=t2.nid where t1.uniacid=:uniacid and t2.regionid=:regionid and t1.status = 1 and t1.pcate =:pcate order by t1.displayorder asc,t1.id asc ";
            $children = pdo_fetchall($tsql, array(":uniacid" => $_W['uniacid'], ':regionid' => $regionid, ':pcate' => $cate['id']));
            if ($children) {
                foreach ($children as $key => $val) {
                    $children[$key]['thumb'] = tomedia($val['thumb']);
                    $children[$key]['url'] = $val['url'];
                    if ($val['show'] == 1) {
                        if ($val['title'] == '待付款') {
                            $status = 0;
                        }
                        elseif ($val['title'] == '待发货') {
                            $status = 1;
                        }
                        elseif ($val['title'] == '待收货') {
                            $status = 2;
                        }
                        elseif ($val['title'] == '已完成') {
                            $status = 3;
                        }
                        if ($val['enable'] == 1) {
                            $type = 'shopping';
                        }
                        elseif ($val['enable'] == 2) {
                            $type = 'counter';
                        }
                        $children[$key]['total'] = model_shop::getOrderTotal($status, $regionid, $_W['member']['uid'], $type);
                    }

                }
                $category[$cid]['children'] = $children;
            }
        }
    }


    $data = array();
    $data['list'] = $category;
    util::send_result($data);

}
/**
 * 用户的信息
 */
if ($op == 'userinfo') {
    $uid = $_W['member']['uid'];
    $member = pdo_get('xcommunity_member', array('uniacid' => $_W['uniacid'], 'uid' => $uid, 'enable' => 1));
    $addressid = pdo_getcolumn('xcommunity_member_bind', array('memberid' => $member['id']), 'addressid');
    $room = pdo_get('xcommunity_member_room', array('id' => $addressid), array());
    $data = array();
    $data['realname'] = $_W['member']['realname'];
    $data['mobile'] = $_W['member']['mobile'];
    $data['credit2'] = $_W['member']['credit2'];
    $data['credit1'] = $_W['member']['credit1'];
    if (empty($_W['member']['avatar'])) {
        if ($_W['container'] == 'wechat') {
            $info = mc_oauth_userinfo();
            if ($info) {
                $_W['member']['avatar'] = $info['headimgurl'];
                pdo_update('mc_members', array('avatar' => $info['headimgurl']), array('uid' => $_W['member']['uid']));
            }
            else {
                $userinfo = mc_oauth_account_userinfo();
                $_W['member']['avatar'] = $userinfo['headimgurl'];
                pdo_update('mc_members', array('avatar' => $userinfo['headimgurl']), array('uid' => $_W['member']['uid']));
            }
        }
        else {
            $member = pdo_get('mc_members', array('uid' => $_W['member']['uid']), array('avatar'));
            $_W['member']['avatar'] = $member['avatar'];
        }
    }
    $data['license'] = $_SESSION['community']['license'];
    $data['avatar'] = $_W['member']['avatar'] ? $_W['member']['avatar'] : MODULE_URL . 'template/mobile/default2/static/images/my/personal.png';
    $data['address'] = $room['address'] ? $room['address'] : $_SESSION['community']['address'];
    $data['region'] = $_SESSION['community']['title'];
    $condition = "uid=:uid and enable=1";
    $params[':uid'] = $_W['member']['uid'];
    $sql = "select realname,mobile,address,id,city from" . tablename('xcommunity_member_address') . "where $condition";
    $item = pdo_fetch($sql, $params);
    $data['oneaddr'] = $item;
    util::send_result($data);
}
elseif ($op == 'check') {
    $mobile = trim($_GPC['mobile']);
    $uid = model_user::member_check($mobile);
    if ($uid) {
        if ($_W['member']['uid'] != $uid) {
            util::send_result(array('status' => 2));
            exit();
        }
        else {
            util::send_result(array('status' => 1));
        }
    }
    else {
        util::send_result(array('status' => 1));
    }

}
