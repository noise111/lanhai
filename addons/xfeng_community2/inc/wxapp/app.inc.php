<?php
/**
 * Created by njlanniu.
 * User: njlanniu
 * Time: 2018/2/28 下午9:58
 * 移动管理中心菜单接口
 */
global $_GPC, $_W;
$ops = array('home', 'login', 'userinfo', 'checklogin', 'register');
$op = trim($_GPC['op']);
if (!in_array($op, $ops)) {
    util::send_error(-1, '参数错误');
}

if ($op == 'home') {

    if (empty($_SESSION['appuid'])) {
        util::send_error(-1, '请先登录');
    }
    //主页菜单
    $memus = m($_SESSION['appuid'],$_SESSION['apptype'],$_SESSION['appstore']);
    if (empty($memus)) {
        util::send_error(-1, '未授权,请联系客服');
    }
    $data['list'] = $memus;
    $data['type'] = $_SESSION['apptype'];
    $user = util::xquser($_SESSION['appuid']);
    $_SESSION['appregionids'] = $user['regionid'];
    $_SESSION['apptxpay'] = $user['txpay'];
    $_SESSION['apptxcid'] = $user['txcid'];
    $_SESSION['appbalance'] = $user['balance'] ? $user['balance'] : '0.00';
    $_SESSION['appcredit'] = $user['credit'] ? $user['credit'] : '0.00';
    $_SESSION['apptype'] = $user['type'];
    $_SESSION['appstore'] = $user['store'];
    util::send_result($data);
}
elseif ($op == 'login') {
    //登录

    load()->model('user');
    $member = array();
    $username = trim($_GPC['username']);
    pdo_query('DELETE FROM' . tablename('users_failed_login') . ' WHERE lastupdate < :timestamp', array(':timestamp' => TIMESTAMP - 300));
    $failed = pdo_get('users_failed_login', array('username' => $username, 'ip' => CLIENT_IP));
    if ($failed['count'] >= 5) {
        util::send_error(-1, '输入密码错误次数超过5次，请在5分钟后再登录');
        exit();
    }

    $member['username'] = $username;
    $member['password'] = $_GPC['password'];

    $record = user_single($member);
    if (!empty($record)) {
        if ($record['status'] == 1) {
            util::send_error(-1, '您的账号正在审核或是已经被系统禁止，请联系网站管理员解决！');
            exit();
        }
        if (!empty($record['endtime']) && $record['endtime'] < TIMESTAMP) {
            util::send_error(-1, '您的账号有效期限已过，请联系网站管理员解决！');
            exit();
        }
        $founders = explode(',', $_W['config']['setting']['founder']);
        $_W['isfounder'] = in_array($record['uid'], $founders);
        if (!empty($_W['siteclose']) && empty($_W['isfounder'])) {
            util::send_error(-1, '站点已关闭，关闭原因：' . $_W['setting']['copyright']['reason']);
            exit();
        }
        $cookie = array();
        $cookie['uid'] = $record['uid'];
        $cookie['lastvisit'] = $record['lastvisit'];
        $cookie['lastip'] = $record['lastip'];
        $cookie['hash'] = md5($record['password'] . $record['salt']);
        isetcookie('_uid', $record['uid'], 7 * 86400);
        $_SESSION['appuid'] = $record['uid'];
        $user = util::xquser($_SESSION['appuid']);
        $_SESSION['appregionids'] = $user['regionid'];
        $_SESSION['username'] = $member['username'];
        $_SESSION['appuniacid'] = $user['uniacid'];
        $_SESSION['apptxpay'] = $user['txpay'];
        $_SESSION['apptxcid'] = $user['txcid'];
        $_SESSION['appbalance'] = $user['balance'] ? $user['balance'] : '0.00';
        $_SESSION['appcredit'] = $user['credit'] ? $user['credit'] : '0.00';
        $_SESSION['apptype'] = $user['type'];
        $_SESSION['appstore'] = $user['store'];
        $session = base64_encode(json_encode($cookie));
        isetcookie('__session', $session, !empty($_GPC['rember']) ? 7 * 86400 : 0, true);
        $status = array();
        $status['uid'] = $record['uid'];
        $status['lastvisit'] = TIMESTAMP;
        $status['lastip'] = CLIENT_IP;
        user_update($status);
        if ($record['type'] == ACCOUNT_OPERATE_CLERK) {
            $role = uni_permission($record['uid'], $record['uniacid']);
            isetcookie('__uniacid', $record['uniacid'], 7 * 86400);
            isetcookie('__uid', $record['uid'], 7 * 86400);
            if ($_W['role'] == 'clerk' || $role == 'clerk') {
                util::send_result();
                exit();

            }
        }
        if ($record['uid'] != $_GPC['__uid']) {
            isetcookie('__uniacid', '', -7 * 86400);
            isetcookie('__uid', '', -7 * 86400);
        }
        pdo_delete('users_failed_login', array('id' => $failed['id']));
        util::send_result(array('status'=>1));
        exit();
    } else {
        if (empty($failed)) {
            pdo_insert('users_failed_login', array('ip' => CLIENT_IP, 'username' => $username, 'count' => '1', 'lastupdate' => TIMESTAMP));
        } else {
            pdo_update('users_failed_login', array('count' => $failed['count'] + 1, 'lastupdate' => TIMESTAMP), array('id' => $failed['id']));
        }
        util::send_result(array('status'=>2) );
        exit();
    }


}
elseif ($op == 'userinfo') {
    $data = array();
//    $u = util::xquser($_SESSION['appuid']);
//
//    $_SESSION['appregionids'] = $u['regionid'];
    $user = util::xquser($_SESSION['appuid']);
    $data['username'] = $_SESSION['username'];
    $data['headimg'] = tomedia('headimg_' . $_W['account']['acid'] . '.jpg');
//    $user = pdo_get('xcommunity_users', array('uid' => $_SESSION['appuid']), array());
//    $_SESSION['appuniacid'] = $user['uniacid'];
//    $_SESSION['apptxpay'] = $user['txpay'];
//    $_SESSION['apptxcid'] = $user['txcid'];
    $data['apptxpay'] = $_SESSION['apptxpay'] ? $_SESSION['apptxpay'] : $user['txpay'];
    $data['apptxcid'] = $_SESSION['apptxcid'] ? $_SESSION['apptxcid'] : $user['txcid'];
    $data['balance'] = $_SESSION['appbalance'];
    $data['credit'] = $_SESSION['appcredit'];
    $data['type'] = $_SESSION['apptype'];
    $data['creditstatus'] = $_SESSION['creditstatus'] ? $_SESSION['creditstatus'] : $user['creditstatus'];
    $data['integral'] = $_SESSION['integral'] ? $_SESSION['integral'] : $user['integral'];
    if ($_SESSION['apptype'] == 1 || $_SESSION['apptype'] == 2 || $_SESSION['apptype'] == 3) {
        //小区
        $condition = 't2.uniacid=:uniacid';
        $con = 'uniacid=:uniacid';
        if ($_SESSION['appregionids'] && $_SESSION['apptype'] == 3) {
            $condition .= " and t2.regionid in({$_SESSION['appregionids']}) ";
            $con .= " and id in({$_SESSION['appregionids']}) ";
        }
        $roomsql = "select count(*) from" . tablename('xcommunity_member_room') . "t2 where $condition";
        $rooms = pdo_fetchcolumn($roomsql, array(':uniacid' => $_SESSION['appuniacid']));
        $_SESSION['room'] = $rooms;
//        $members = pdo_fetchcolumn("select count(*) from" . tablename('xcommunity_member') . "where $condition and visit=0", array(':uniacid' => $_SESSION['appuniacid']));
        $members = pdo_fetchcolumn("select count(*) from" . tablename('mc_members') . "as t1 left join" . tablename('xcommunity_member') . "as t2 on t1.uid=t2.uid left join" . tablename('xcommunity_region') . "as t3 on t2.regionid=t3.id left join" . tablename('xcommunity_member_bind') . "t5 on t5.memberid = t2.id where $condition and t1.mobile <> '' and t5.id <> '' and visit=0", array(':uniacid' => $_SESSION['appuniacid']));

        $_SESSION['member'] = $members;
        $_SESSION['region'] = pdo_fetchcolumn("select count(*) from" . tablename('xcommunity_region') . "where $con", array(':uniacid' => $_SESSION['appuniacid']));
        $sql = "select commission from" . tablename('xcommunity_region') . "where $con";
        $regions = pdo_fetchall($sql, array(':uniacid' => $_SESSION['appuniacid']));
        $total = '';
        foreach ($regions as $k => $v) {
            $total += $v['commission'];
        }
        $_SESSION['commission'] = round($total,2);
        $data['commission'] = $_SESSION['commission'];
    }
    $data['room'] = $_SESSION['room'];
    $data['member'] = $_SESSION['member'];
    $data['regions'] = $_SESSION['region'] ? $_SESSION['region'] : 0;
    if ($_SESSION['apptype'] == 1) {
        $_SESSION['appname'] = '超级管理员';
    } elseif ($_SESSION['apptype'] == 2) {
        $_SESSION['appname'] = '普通管理员';
    } elseif ($_SESSION['apptype'] == 3) {
        $_SESSION['appname'] = '小区管理员';
    } elseif ($_SESSION['apptype'] == 4) {
        $_SESSION['appname'] = '超市管理员';
    } elseif ($_SESSION['apptype'] == 5) {
        $_SESSION['appname'] = '商家管理员';
    } else {
        $_SESSION['appname'] = '超级管理员';
    }
    $data['appname'] = $_SESSION['appname'];
    util::send_result($data);
}
elseif ($op == 'checklogin') {
    //判断是否微信登录
//    $sql = "select t1.uid,t2.openid,t1.type,t2.mobile,t1.credit,t1.balance from" . tablename('xcommunity_users') . "t1 left join" . tablename('xcommunity_staff') . "t2 on t1.staffid=t2.id where t2.openid=:openid";
//    $user = pdo_fetch($sql, array(':openid' => $_W['fans']['from_user']));
////    print_r($user);exit();
//    if ($user) {
//        //账号启用状态
//        $_SESSION['appuid'] = $user['uid'];
//
//        $_SESSION['appopenid'] = $user['openid'];
//
//
//        $_SESSION['username'] = $user['mobile'];
//
//
//        util::send_result();
//    }

}
elseif ($op == 'register') {
    $pics = $_GPC['pics'];
    $pic = '';
    if ($pics) {
        $pics = explode(',', $pics);
        if (!empty($pics)) {
            foreach ($pics as $k => $v) {
                $pic .= $v . ',';//修改为H5上传图片
            }
        }
        $pic = ltrim(rtrim($pic, ','), ',');
    }
    $data = array(
        'uniacid' => $_W['uniacid'],
        'realname' => trim($_GPC['realname']),
        'tel' => trim($_GPC['tel']),
        'address' => trim($_GPC['address']),
        'category' => intval($_GPC['category']),
        'createtime' => TIMESTAMP,
        'company'   => trim($_GPC['company']),
        'license'   => $pic
    );
    if (pdo_insert('xcommunity_app_user', $data)) {
        $t17 = set('t17');
        if ($t17) {
            $content = array(
                'first' => array(
                    'value' => '客户入驻申请',
                ),
                'keyword1' => array(
                    'value' => $data['realname'],
                ),
                'keyword2' => array(
                    'value' => $data['tel'],
                ),
                'keyword3' => array(
                    'value' => date('Y-m-d H:i'),
                ),
                'keyword4' => array(
                    'value' => '地址为:' . $data['address'] . '的客户申请入驻',
                ),
                'remark' => array(
                    'value' => '单位：'.$data['company'],
                ),
            );
            $t18 = set('t18');
            $tplid = $t18;
            $notice = pdo_fetchall("select t1.type,t2.openid from" . tablename('xcommunity_notice') . "t1 left join" . tablename('xcommunity_staff') . "t2 on t1.staffid=t2.id where t1.uniacid=:uniacid and t1.enable=4", array(':uniacid' => $_W['uniacid']));
            foreach ($notice as $k => $v) {
                if ($v['type'] == 1 || $v['type'] == 3) {
                    util::sendTplNotice($v['openid'], $tplid, $content, $url = '', $topcolor = '#FF683F');
                }
            }
        }
        util::send_result();
    }
}
