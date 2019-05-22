<?php
/**
 * Created by njxiaoqu.
 * User: zhoufeng
 * Time: 2017/5/15 下午3:44
 */
global $_W, $_GPC;
$community = 'community' . $_W['uniacid'];
if ($_W['setting'][$community]['styleid'] == 'default2') {
    $ops = array('login', 'home', 'region', 'build', 'repair', 'report', 'room', 'announcement', 'business', 'shop', 'activity', 'park', 'car', 'member', 'guard', 'cost', 'entery', 'market', 'carpool', 'houselease', 'homemaking', 'cash', 'recharge', 'register','user');
    $op = in_array(trim($_GPC['op']), $ops) ? trim($_GPC['op']) : 'login';
    $p = in_array(trim($_GPC['p']), array('add', 'detail', 'list', 'post', 'display', 'goods', 'order', 'setting', 'cz', 'tx', 'xf', 'gooddetail', 'payset', 'coupon','grab','orderdetail','home','account')) ? trim($_GPC['p']) : 'list';
    if ($op == 'login') {
        $_W['member']['title'] = '移动端管理中心';
//        isetcookie('__session', '', -10000);
        include $this->template('app2/login/login');
    }
    elseif ($op == 'home') {
        $_W['member']['title'] = '移动端管理中心';
        include $this->template('app2/home/home');
    }
    elseif ($op == 'region') {
        $_W['member']['title'] = '小区管理中心';
        if ($p == 'add') {
            include $this->template('app2/region/add');
        }
        elseif ($p == 'list') {
            include $this->template('app2/region/list');
        }
    }
    elseif ($op == 'build') {
        $_W['member']['title'] = '楼宇管理中心';
        if ($p == 'add') {
            include $this->template('app2/build/add');
        }
        elseif ($p == 'list') {
            include $this->template('app2/build/list');
        }
        elseif ($p == 'display') {
            include $this->template('app2/build/display');
        }
    }
    elseif ($op == 'repair') {
        $_W['member']['title'] = '报修管理中心';
        if ($p == 'detail') {
            include $this->template('app2/repair/detail');
        }
        elseif ($p == 'list') {
            include $this->template('app2/repair/list');
        }
    }
    elseif ($op == 'report') {
        $_W['member']['title'] = '建议管理中心';
        if ($p == 'detail') {
            include $this->template('app2/report/detail');
        }
        elseif ($p == 'list') {
            include $this->template('app2/report/list');
        }
    }
    elseif ($op == 'room') {
        $_W['member']['title'] = '房屋管理中心';
        if ($p == 'add') {
            include $this->template('app2/room/add');
        }
        elseif ($p == 'list') {
            include $this->template('app2/room/list');
        }
        elseif ($p == 'detail') {
            include $this->template('app2/room/detail');
        }
        elseif ($p == 'display') {
            include $this->template('app2/room/member');
        }
    }
    elseif ($op == 'announcement') {
        $_W['member']['title'] = '公告管理中心';
        if ($p == 'add') {
            include $this->template('app2/announcement/add');
        }
        elseif ($p == 'list') {
            include $this->template('app2/announcement/list');
        }
        elseif ($p == 'detail') {
            include $this->template('app2/announcement/detail');
        }
    }
    elseif ($op == 'business') {

        if ($p == 'add') {
            $_W['member']['title'] = '添加店铺';
            include $this->template('app2/business/add');
        }
        elseif ($p == 'list') {
            $_W['member']['title'] = '商家管理中心';
            include $this->template('app2/business/list');
        }
        elseif ($p == 'post') {
            $_W['member']['title'] = '添加商品';
            include $this->template('app2/business/post');
        }
        elseif ($p == 'goods') {
            $_W['member']['title'] = '商品管理中心';
            include $this->template('app2/business/good_list');
        }
        elseif ($p == 'order') {
            include $this->template('app2/business/order');
        }
        elseif ($p == 'orderdetail') {
            include $this->template('app2/business/orderdetail');
        }
        elseif ($p == 'setting') {
            include $this->template('app2/business/setting');
        }
        elseif ($p == 'detail') {
            include $this->template('app2/business/detail');
        }
        elseif ($p == 'cz') {
            include $this->template('app2/business/rechargelog');
        }
        elseif ($p == 'tx') {
            include $this->template('app2/business/cashlog');
        }
        elseif ($p == 'xf') {
            include $this->template('app2/business/record');
        }
        elseif ($p == 'gooddetail') {
            include $this->template('app2/business/gooddetail');
        }
        elseif ($p == 'payset') {
            echo '正在开发中';
            exit();
        }
        elseif ($p == 'coupon') {
            include $this->template('app2/business/hx');
        }

    }
    elseif ($op == 'shop') {

        if ($p == 'add') {
            $_W['member']['title'] = '商品发布';
            include $this->template('app2/shop/add');
        }
        elseif ($p == 'list') {
            $_W['member']['title'] = '商品管理中心';
            include $this->template('app2/shop/list');
        }
        elseif ($p == 'post') {
            include $this->template('app2/shop/post');
        }
        elseif ($p == 'order') {
            $_W['member']['title'] = '订单管理中心';
            include $this->template('app2/shop/order');
        }
        elseif ($p == 'tx') {
            $_W['member']['title'] = '提现管理';
            include $this->template('app2/shop/cashlog');
        }
        elseif ($p =='detail'){
            $_W['member']['title'] = '商品详情';
            include $this->template('app2/shop/detail');
        }
        elseif($p == 'orderdetail'){
            include $this->template('app2/shop/orderdetail');
        }
    }
    elseif ($op == 'activity') {
        $_W['member']['title'] = '活动管理中心';
        if ($p == 'add') {
            include $this->template('app2/activity/add');
        }
        elseif ($p == 'list') {
            include $this->template('app2/activity/list');
        }
        elseif ($p == 'post') {
            include $this->template('app2/activity/post');
        }
        elseif ($p == 'detail') {
            include $this->template('app2/activity/detail');
        }
    }
    elseif ($op == 'park') {
        $_W['member']['title'] = '车位管理中心';
        if ($p == 'add') {
            include $this->template('app2/park/add');
        }
        elseif ($p == 'list') {
            include $this->template('app2/park/list');
        }
        elseif ($p == 'display') {
            include $this->template('app2/park/display');
        }
        elseif($p == 'detail'){
            include $this->template('app2/park/detail');
        }
    }
    elseif ($op == 'carpool') {
        $_W['member']['title'] = '拼车管理中心';
        if ($p == 'add') {
            include $this->template('app2/carpool/add');
        }
        elseif ($p == 'list') {
            include $this->template('app2/carpool/list');
        }
        elseif($p == 'detail'){
            include $this->template('app2/carpool/detail');
        }
    }
    elseif ($op == 'member') {
        $_W['member']['title'] = '住户管理中心';
        if ($p == 'add') {
            include $this->template('app2/member/add');
        }
        elseif ($p == 'list') {
            include $this->template('app2/member/list');
        }
        elseif ($p == 'detail') {
            include $this->template('app2/member/detail');
        }
    }
    elseif ($op == 'guard') {
        $_W['member']['title'] = '门禁管理中心';
        if ($p == 'add') {
            include $this->template('app2/guard/add');
        }
        elseif ($p == 'list') {
            include $this->template('app2/guard/list');
        }
        elseif ($p == 'detail') {
            include $this->template('app2/guard/detail');
        }
    }
    elseif ($op == 'cost') {
        $_W['member']['title'] = '费用管理中心';
        if ($p == 'add') {
            include $this->template('app2/cost/add');
        }
        elseif ($p == 'list') {
            include $this->template('app2/cost/list');
        }
    }
    elseif ($op == 'entery') {
        $_W['member']['title'] = '抄表管理中心';
        if ($p == 'add') {
            include $this->template('app2/entery/add');
        }
        elseif($p == 'detail'){
            include $this->template('app2/entery/detail');
        }
        elseif ($p == 'list') {
            include $this->template('app2/entery/list');
        }

    }
    elseif ($op == 'market') {
        $_W['member']['title'] = '集市管理中心';
        if ($p == 'add') {
            include $this->template('app2/market/add');
        }
        elseif ($p == 'list') {
            include $this->template('app2/market/list');
        }
        elseif($p == 'detail'){
            include $this->template('app2/market/detail');
        }
    }
    elseif ($op == 'houselease') {
        $_W['member']['title'] = '租赁管理中心';
        if ($p == 'add') {
            include $this->template('app2/houselease/add');
        }
        elseif ($p == 'list') {
            include $this->template('app2/houselease/list');
        }
        elseif ($p == 'detail') {
            include $this->template('app2/houselease/detail');
        }
    }
    elseif ($op == 'homemaking') {
        $_W['member']['title'] = '家政管理中心';
        if ($p == 'list'){
            include $this->template('app2/homemaking/list');
        }
        elseif ($p == 'detail'){
            include $this->template('app2/homemaking/detail');
        }
        elseif ($p == 'grab'){
            include $this->template('app2/homemaking/grab');
        }
    }
    elseif ($op == 'register') {

        include $this->template('app2/register');
    }
    elseif ($op == 'car') {
        $_W['member']['title'] = '车辆管理中心';
        if ($p == 'add') {
            include $this->template('app2/car/add');
        }
        elseif ($p == 'list') {
            include $this->template('app2/car/list');
        }
        elseif ($p == 'display') {
            include $this->template('app2/car/display');
        }
        elseif ($p == 'detail') {
            include $this->template('app2/car/detail');
        }
    }
    elseif ($op == 'recharge') {
        if (checksubmit('submit')) {
            $fee = $_GPC['fee'];
            $orderid = 'LN' . date('YmdHi') . random(10, 1);
            $data = array(
                'uniacid'    => $_W['uniacid'],
                'fee'        => $fee,
                'orderid'    => $orderid,
                'createtime' => TIMESTAMP
            );
            $r = pdo_insert('xcommunity_recharge', $data);
            $orderid = pdo_insertid();
            if ($r) {
                $url = $this->createMobileUrl('apppay', array('orderid' => $orderid));
                @header("Location: " . $url);
                exit();
            }
        }
        include $this->template('app2/recharge');
    }
    elseif ($op == 'cash') {
        $type = intval($_GPC['type']);
        if ($_SESSION['apptype'] == 2) {
            $condition .= " and t1.uid=:uid";
            $params[':uid'] = $_SESSION['appuid'];
        }
        if ($_SESSION['apptype'] == 3) {
            $condition .= " and t2.regionid in (:regionid)";
            $params[':regionid'] = $_SESSION['appregionids'];
        }
        if ($_SESSION['apptype'] == 4 || $_SESSION['apptype'] == 5) {
            $data['list'] = array();
            util::send_result($data);
        }
        $regions = model_region::region_fetall('',$_SESSION['appuid']);
        if (checksubmit('submit')) {
            if ($_GPC['fee'] <= 0) {
                itoast('输入金额不正确,请重新输入', referer(), 'error', true);
                exit();
            }
            $data = array(
                'uniacid'    => $_W['uniacid'],
                'ordersn'    => 'LN' . date('YmdHi') . random(10, 1),
                'price'      => $_GPC['fee'],
                'type'       => 'cash',
                'pay'        => $_GPC['pay'],
                'createtime' => TIMESTAMP,
                'uid'        => $_SESSION['appuid'],
            );
            if($type ==1){
                $regionid = intval($_GPC['regionid']);
                $region = model_region::region_check($regionid);
                if ($_GPC['fee'] > $region['commission']) {
                    itoast('佣金不足，无法提现', referer(), 'error', true);
                }
                $data['regionid'] = $regionid;
            }else{
                $users = pdo_fetch("SELECT * FROM" . tablename('xcommunity_users') . "WHERE uid=:uid", array(':uid' => $_SESSION['appuid']));
                if ($_GPC['fee'] > $users['balance']) {
                    itoast('余额不足，无法提现', referer(), 'error', true);
                }
            }



            $r = pdo_insert('xcommunity_order', $data);
            if ($r) {
                if($type == 1){
                    pdo_update('xcommunity_region', array('commission -=' => $_GPC['fee']), array('id' => $regionid));
                }else{
                    pdo_update('xcommunity_users', array('balance -=' => $_GPC['fee']), array('id' => $users['id']));
                    $balance = $users['balance'] - $_GPC['fee'];
                    $_SESSION['balance'] = $balance;
                }

                itoast('提交成功', referer(), 'success', true);
            }
        }


            include $this->template('app2/cash');


    }elseif($op =='user'){
        if($p =='home'){
            include $this->template('app2/user/home');
        }elseif($p =='tx'){
            include $this->template('app2/user/cashlog');
        }elseif($p =='account'){
            include $this->template('app2/user/account');
        }
    }
}
else {
    $ops = array('login', 'index', 'list', 'detail', 'logout', 'add_notice', 'cost_order', 'shop_detail', 'homemaking_detail', 'houselease_detail', 'fled_detail', 'member_detail', 'add_guard', 'shop', 'business_detail', 'business', 'use', 'memo_detail');
    $op = in_array(trim($_GPC['op']), $ops) ? trim($_GPC['op']) : 'login';
    if ($op == 'login') {
        if ($_W['fans']['from_user']) {
            //判断是否微信登录
            $sql = "select t1.uid,t2.openid,t1.type from" . tablename('xcommunity_users') . "t1 left join" . tablename('xcommunity_staff') . "t2 on t1.staffid=t2.id where t2.openid=:openid";
            $user = pdo_fetch($sql, array(':openid' => $_W['fans']['from_user']));

            //账号启用状态
            $_SESSION['sysuid'] = $user['uid'];
            $_SESSION['sysopenid'] = $user['openid'];
            $url = $this->createMobileUrl('xqsys', array('op' => 'index'));
            header("location: " . $url);
            exit();

        }

        if ($_W['ispost']) {
            load()->model('user');
            $member = array();
            $username = trim($_GPC['username']);
            pdo_query('DELETE FROM' . tablename('users_failed_login') . ' WHERE lastupdate < :timestamp', array(':timestamp' => TIMESTAMP - 300));
            $failed = pdo_get('users_failed_login', array('username' => $username, 'ip' => CLIENT_IP));
            if ($failed['count'] >= 5) {
                itoast('输入密码错误次数超过5次，请在5分钟后再登录', referer(), 'info');
            }
            if (empty($username)) {
                itoast('请输入要登录的用户名');
            }
            $member['username'] = $username;
            $member['password'] = $_GPC['password'];
            if (empty($member['password'])) {
                itoast('请输入密码');
            }
            $record = user_single($member);
            if (!empty($record)) {
                if ($record['status'] == 1) {
                    itoast('您的账号正在审核或是已经被系统禁止，请联系网站管理员解决！');
                }
                if (!empty($record['endtime']) && $record['endtime'] < TIMESTAMP) {
                    itoast('您的账号有效期限已过，请联系网站管理员解决！');
                }
                $founders = explode(',', $_W['config']['setting']['founder']);
                $_W['isfounder'] = in_array($record['uid'], $founders);
                if (!empty($_W['siteclose']) && empty($_W['isfounder'])) {
                    itoast('站点已关闭，关闭原因：' . $_W['setting']['copyright']['reason']);
                }
                $cookie = array();
                $cookie['uid'] = $record['uid'];
                $cookie['lastvisit'] = $record['lastvisit'];
                $cookie['lastip'] = $record['lastip'];
                $cookie['hash'] = md5($record['password'] . $record['salt']);
//            isetcookie('_uid', $record['uid'], 7 * 86400);
                $_SESSION['sysuid'] = $record['uid'];
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
                        itoast('登陆成功', $this->createMobileUrl('xqsys', array('op' => 'index')), 'success');
                    }
                }
                if (empty($forward)) {
                    $forward = $_GPC['forward'];
                }
                if (empty($forward)) {
                    $forward = $this->createMobileUrl('xqsys', array('op' => 'index'));
                }
                if ($record['uid'] != $_GPC['__uid']) {
                    isetcookie('__uniacid', '', -7 * 86400);
                    isetcookie('__uid', '', -7 * 86400);
                }
                pdo_delete('users_failed_login', array('id' => $failed['id']));
                itoast("欢迎回来，{$record['username']}。", $forward);
            }
            else {
                if (empty($failed)) {
                    pdo_insert('users_failed_login', array('ip' => CLIENT_IP, 'username' => $username, 'count' => '1', 'lastupdate' => TIMESTAMP));
                }
                else {
                    pdo_update('users_failed_login', array('count' => $failed['count'] + 1, 'lastupdate' => TIMESTAMP), array('id' => $failed['id']));
                }
                itoast('登录失败，请检查您输入的用户名和密码！');
            }
        }
        include $this->template('app/login');
    }
    elseif ($op == 'logout') {
        isetcookie('__session', '', -10000);
        $forward = app_url('xqsys');
        header('Location:' . $forward);
    }
    elseif ($op == 'index') {
        if ($_SESSION['sysuid']) {
            $user = util::xquser($_SESSION['sysuid']);
            if (empty($user['xqmenus'])) {
                itoast('您还没有操作权限，请联系管理员', $this->createMobileUrl('home'), 'error');
                exit();
            }
            $data = array(
                '1'  => array('1' => '报修管理', 'icon' => $_W['siteroot'] . 'addons/' . $this->module['name'] . '/template/mobile/app/static/img/bx.png'),
                '2'  => array('2' => '建议管理', 'icon' => $_W['siteroot'] . 'addons/' . $this->module['name'] . '/template/mobile/app/static/img/jy.png'),
                '3'  => array('3' => '公告管理', 'icon' => $_W['siteroot'] . 'addons/' . $this->module['name'] . '/template/mobile/app/static/img/tz.png'),
                '4'  => array('4' => '超市管理', 'icon' => $_W['siteroot'] . 'addons/' . $this->module['name'] . '/template/mobile/app/static/img/cs.png'),
                '5'  => array('5' => '商家管理', 'icon' => $_W['siteroot'] . 'addons/' . $this->module['name'] . '/template/mobile/app/static/img/sj.png'),
                '6'  => array('6' => '费用查询', 'icon' => $_W['siteroot'] . 'addons/' . $this->module['name'] . '/template/mobile/app/static/img/fy.png'),
                '7'  => array('7' => '小区活动', 'icon' => $_W['siteroot'] . 'addons/' . $this->module['name'] . '/template/mobile/app/static/img/hd.png'),
                '8'  => array('8' => '家政管理', 'icon' => $_W['siteroot'] . 'addons/' . $this->module['name'] . '/template/mobile/app/static/img/jz.png'),
                '9'  => array('9' => '租赁管理', 'icon' => $_W['siteroot'] . 'addons/' . $this->module['name'] . '/template/mobile/app/static/img/zn.png'),
                '10' => array('10' => '二手管理', 'icon' => $_W['siteroot'] . 'addons/' . $this->module['name'] . '/template/mobile/app/static/img/es.png'),
                '11' => array('11' => '拼车管理', 'icon' => $_W['siteroot'] . 'addons/' . $this->module['name'] . '/template/mobile/app/static/img/pc.png'),
                '12' => array('12' => '住户管理', 'icon' => $_W['siteroot'] . 'addons/' . $this->module['name'] . '/template/mobile/app/static/img/zh.png'),
                '13' => array('13' => '门禁管理', 'icon' => $_W['siteroot'] . 'addons/' . $this->module['name'] . '/template/mobile/app/static/img/mj.png'),
                '14' => array('14' => '内部公告', 'icon' => $_W['siteroot'] . 'addons/' . $this->module['name'] . '/template/mobile/app/static/img/tz.png'),
            );
            $menus = '';
            if ($user['xqmenus']) {
                $xqmenus = $_SESSION['sysuid'] == 1 ? explode(',', '1,2,3,4,5,6,7,8,9,10,11,12,13,14') : explode(',', $user['xqmenus']);
                $menus = $xqmenus;
            }

        }
        else {
            itoast('请重新登录', $this->createMobileUrl('xqsys', array('op' => 'login')), 'error');
            exit();
        }

        include $this->template('app/index');
    }
    elseif ($op == 'list') {
        if (empty($_SESSION['sysuid'])) {
            itoast('请重新登录', $this->createMobileUrl('xqsys', array('op' => 'login')), 'error');
            exit();
        }
        $xqtype = intval($_GPC['type']);
        if ($xqtype) {
            $user = util::xquser($_SESSION['sysuid']);
            $pindex = max(1, intval($_GPC['page']));
            $psize = 10;
            if ($xqtype == 1 || $xqtype == 2) {
                $psize = 10;
                $condition = ' t1.uniacid=:uniacid';
                $params[':uniacid'] = $_W['uniacid'];
                if ($user['type'] == 3) {
                    $condition .= " and t1.regionid in({$user['regionid']})";
                }
                $type = $xqtype == 1 ? 1 : 2;
                $condition .= " and t1.type ={$type}";
                //1报修信息 2建议信息
                $sql = "select t1.*,t2.area,t2.build,t2.unit,t2.room,t4.realname,t4.mobile,t5.dealing,t5.content as report_content,t6.name as category,t7.content as rank_content,t5.createtime as rtime,t8.title from" . tablename('xcommunity_report') . "t1 left join" . tablename('xcommunity_member_room') . "t2 on t1.addressid=t2.id left join" . tablename('mc_members') . "t4 on t4.uid=t1.uid left join" . tablename('xcommunity_report_log') . "t5 on t1.id=t5.reportid left join" . tablename('xcommunity_category') . "t6 on t1.cid=t6.id left join" . tablename('xcommunity_rank') . "t7 on t7.rankid = t1.id left join" . tablename('xcommunity_region') . "t8 on t8.id = t1.regionid where $condition group by t1.id order by t1.id desc limit " . ($pindex - 1) * $psize . ',' . $psize;
                $list = pdo_fetchall($sql, $params);
                $tsql = "select count(*) from" . tablename('xcommunity_report') . "t1 left join" . tablename('xcommunity_member_room') . "t2 on t1.addressid=t2.id left join" . tablename('mc_members') . "t4 on t4.uid=t1.uid left join" . tablename('xcommunity_report_log') . "t5 on t1.id=t5.reportid left join" . tablename('xcommunity_category') . "t6 on t1.cid=t6.id left join" . tablename('xcommunity_rank') . "t7 on t7.rankid = t1.id where $condition order by t1.id desc ";
                $total = pdo_fetchcolumn($tsql, $params);

                $pager = pagination($total, $pindex, $psize);
                include $this->template('app/list_repair');
            }
            elseif ($xqtype == 3) {
                //公告管理
                $psize = 15;
                $condition = "uniacid=:uniacid and status = 2";
                $param[':uniacid'] = $_W['uniacid'];
                if ($user[type] == 2 || $user['type'] == 3) {
                    //普通管理员
                    $condition .= " AND t1.uid=:uid";
                    $param[':uid'] = $_SESSION['sysuid'];
                }
//            if($user['type'] == 3){
//                $condition .=" and t2.regionid in({$user['regionid']})";
//            }
//            $sql = "select * from " . tablename("xcommunity_announcement") . "where uniacid = {$_W['uniacid']} and status = 2 $condition order by createtime desc LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
                $sql = "select distinct t1.id,t1.* from" . tablename('xcommunity_announcement') . "t1 left join" . tablename('xcommunity_announcement_region') . "t2 on t1.id=t2.aid where $condition order by t1.createtime desc limit " . ($pindex - 1) * $psize . ',' . $psize;
                $list = pdo_fetchall($sql, $param);

                $tsql = "select count(distinct t1.id) from" . tablename('xcommunity_announcement') . "t1 left join" . tablename('xcommunity_announcement_region') . "t2 on t1.id=t2.aid where $condition order by t1.createtime desc ";
                $total = pdo_fetchcolumn($tsql, $param);
//            $total = pdo_fetchcolumn("SELECT COUNT(*) FROM" . tablename('xcommunity_announcement') . "where $condition and status = 2 order by createtime desc ",$param);
                $pager = pagination($total, $pindex, $psize);
                include $this->template('app/list_notice');
            }
            elseif ($xqtype == 4) {
                //超市
                $psize = 10;
                $condition = "t1.uniacid=:uniacid and t1.type='shopping'";
                $param[':uniacid'] = $_W['uniacid'];
                if ($user[type] == 2) {
                    //普通管理员
                    $condition .= " AND t6.uid=:uid";
                    $param[':uid'] = $_SESSION['sysuid'];
                }
                if ($user['type'] == 3) {
                    $condition .= " and t3.regionid in({$user['regionid']})";
                }
//            $sql = "SELECT o.* , m.realname,m.mobile,m.regionid FROM" . tablename('xcommunity_order') . "as o left join" . tablename('xcommunity_member') . "as m  on o.from_user = m.openid AND o.regionid = m.regionid WHERE $condition  AND o.type = 'shopping' AND m.enable = 1 ORDER BY o.status DESC, o.createtime DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
                $sql = "select t1.*,t4.realname,t4.mobile from" . tablename('xcommunity_order') . "t1 left join" . tablename('xcommunity_member_address') . "t2 on t1.addressid=t2.id left join" . tablename('mc_members') . "t4 on t4.uid= t1.uid left join" . tablename('xcommunity_order_goods') . "t5 on t5.orderid= t1.id left join" . tablename('xcommunity_goods') . "t6 on t6.id=t5.goodsid where $condition order by t1.status desc,t1.createtime LIMIT " . ($pindex - 1) * $psize . ',' . $psize;

                $list = pdo_fetchall($sql, $param);
                $paytype = array(
                    '0' => array('css' => 'default', 'name' => '未支付'),
                    '1' => array('css' => 'warning', 'name' => '余额支付'),
                    '2' => array('css' => 'info', 'name' => '在线支付'),
                    '3' => array('css' => 'warning', 'name' => '货到付款'),
                    '4' => array('css' => 'info', 'name' => '后台支付')
                );
                $orderstatus = array(
                    '-1' => array('css' => 'default', 'name' => '已关闭'),
                    '0'  => array('css' => 'danger', 'name' => '待付款'),
                    '1'  => array('css' => 'info', 'name' => '待发货'),
                    '2'  => array('css' => 'warning', 'name' => '待收货'),
                    '3'  => array('css' => 'success', 'name' => '已完成')
                );
                foreach ($list as $key => $value) {


                    $s = $value['status'];
                    $list[$key]['statuscss'] = $orderstatus[$value['status']]['css'];
                    $list[$key]['status'] = $orderstatus[$value['status']]['name'];
                    // $value['statuscss'] = $orderstatus[$value['status']]['css'];
                    // $value['status'] = $orderstatus[$value['status']]['name'];
                    if ($s < 1) {
                        $list[$key]['css'] = $paytype[$s]['css'];
                        $list[$key]['paytype'] = $paytype[$s]['name'];
                        // $value['css'] = $paytype[$s]['css'];
                        // $value['paytype'] = $paytype[$s]['name'];
                        continue;
                    }
                    $list[$key]['css'] = $paytype[$value['paytype']]['css'];
                    // $value['css'] = $paytype[$value['paytype']]['css'];
                    if ($value['paytype'] == 2) {
                        if (empty($value['transid'])) {
                            $list[$key]['paytype'] = '支付宝支付';
                            // $value['paytype'] = '支付宝支付';
                        }
                        else {
                            $list[$key]['paytype'] = '微信支付';
                            // $value['paytype'] = '微信支付';
                        }
                    }
                    else {
                        $list[$key]['paytype'] = $paytype[$value['paytype']]['name'];
                        // $value['paytype'] = $paytype[$value['paytype']]['name'];
                    }
                    $list[$key]['ordersn'] = chunk_split($value['ordersn']);
                }
//            $total = pdo_fetchcolumn("SELECT COUNT(*) FROM" . tablename('xcommunity_order') . "as o left join (" . tablename('xcommunity_region') . "as r left join" . tablename('xcommunity_member') . "as m on m.regionid = r.id ) on o.from_user = m.openid AND o.regionid = r.id WHERE $condition AND o.type = 'shopping' ", $param);
                $tsql = "select count(*) from" . tablename('xcommunity_order') . "t1 left join" . tablename('xcommunity_member_room') . "t2 on t1.addressid=t2.id left join" . tablename('mc_members') . "t4 on t4.uid= t1.uid left join" . tablename('xcommunity_order_goods') . "t5 on t5.orderid= t1.id left join" . tablename('xcommunity_goods') . "t6 on t6.id=t5.goodsid where $condition order by t1.status desc,t1.createtime ";
                $total = pdo_fetchcolumn($tsql, $param);

                $pager = pagination($total, $pindex, $psize);
                include $this->template('app/list_shop');
            }
            elseif ($xqtype == 5) {
                //商家
                $condition = " t1.uniacid=:uniacid and t1.type='business' ";
                $parms[':uniacid'] = $_W['uniacid'];
                if ($user[type] == 2) {
                    //普通管理员
                    $condition .= " AND t6.uid=:uid";
                    $parms[':uid'] = $_SESSION['sysuid'];
                }
                $psize = 10;
//            $sql = "SELECT o.*,m.realname as realname,m.mobile as mobile,m.address as address,m.regionid as regionid FROM" . tablename('xcommunity_order') . "as o left join (" . tablename('xcommunity_region') . "as r left join" . tablename('xcommunity_member') . "as m on m.regionid = r.id ) on o.from_user = m.openid AND o.regionid = r.id WHERE o.type = 'business' $condition  ORDER BY o.status DESC, o.createtime DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
                $sql = "select t1.id,t1.ordersn,t4.realname,t4.mobile,t1.price,t1.status,t1.createtime from" . tablename('xcommunity_order') . "t1 left join" . tablename('xcommunity_member_room') . "t2 on t1.addressid=t2.id left join" . tablename('mc_members') . "t4 on t1.uid=t4.uid left join" . tablename('xcommunity_order_goods') . "t5 on t5.orderid= t1.id left join" . tablename('xcommunity_goods') . "t6 on t6.id=t5.goodsid where $condition ORDER BY t1.status DESC, t1.createtime DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
                $list = pdo_fetchall($sql, $parms);
//            $total = pdo_fetchcolumn("SELECT COUNT(*) FROM" . tablename('xcommunity_order') . "as o left join (" . tablename('xcommunity_region') . "as r left join" . tablename('xcommunity_member') . "as m on m.regionid = r.id ) on o.from_user = m.openid AND o.regionid = r.id WHERE o.type = 'business' $condition ORDER BY o.status DESC, o.createtime DESC", $parms);
                $tsql = "select count(*) from" . tablename('xcommunity_order') . "t1 left join" . tablename('xcommunity_member_room') . "t2 on t1.addressid=t2.id left join" . tablename('mc_members') . "t4 on t1.uid=t4.uid left join" . tablename('xcommunity_order_goods') . "t5 on t5.orderid= t1.id left join" . tablename('xcommunity_goods') . "t6 on t6.id=t5.goodsid where $condition ORDER BY t1.status DESC, t1.createtime DESC ";
                $total = pdo_fetchcolumn($tsql, $parms);
                $pager = pagination($total, $pindex, $psize);
                include $this->template('app/list_business');
            }
            elseif ($xqtype == 6) {
                //费用
                $condition = "c.uniacid=:uniacid";
                $params[':uniacid'] = $_W['uniacid'];
                if ($user[type] == 3) {
                    //小区管理员
                    $condition .= " and r.id in({$user['regionid']})";
                }
                $list = pdo_fetchall("SELECT c.*,r.title as title FROM" . tablename('xcommunity_cost') . "as c left join" . tablename('xcommunity_region') . "as r on c.regionid = r.id WHERE $condition", $params);
                $total = pdo_fetchcolumn("SELECT COUNT(*) FROM" . tablename('xcommunity_cost') . "as c left join" . tablename('xcommunity_region') . "as r on c.regionid = r.id WHERE $condition", $params);
                $pager = pagination($total, $pindex, $psize);

                include $this->template('app/list_cost');
            }
            elseif ($xqtype == 7) {
                //小区活动
                $psize = 10;
                $condition = "t1.uniacid=:uniacid and t1.type='activity' and t4.id !=''";
                $param[':uniacid'] = $_W['uniacid'];
                if ($user[type] == 2) {
                    //普通管理员
                    $condition .= " AND t4.uid=:uid";
                    $param[':uid'] = $_SESSION['sysuid'];
                }
                if ($user['type'] == 3) {
                    $condition .= " and t6.regionid in({$user['regionid']})";
                }
                $sql = "select distinct t1.id,t3.id,t4.title,t5.realname,t5.mobile,t1.price,t1.status,t3.num,t3.createtime from" . tablename('xcommunity_order') . "t1 left join" . tablename('xcommunity_order_goods') . "t2 on t1.id=t2.orderid left join" . tablename('xcommunity_res') . "t3 on t3.id=t2.goodsid left join" . tablename('xcommunity_activity') . "t4 on t4.id=t3.aid left join " . tablename('mc_members') . "t5 on t3.uid = t5.uid left join" . tablename('xcommunity_activity_region') . "t6 on t6.activityid = t4.id where $condition LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
//            echo $sql;exit();
                $list = pdo_fetchall($sql, $param);

                $tsql = "select count(distinct t1.id) from" . tablename('xcommunity_order') . "t1 left join" . tablename('xcommunity_order_goods') . "t2 on t1.id=t2.orderid left join" . tablename('xcommunity_res') . "t3 on t3.id=t2.goodsid left join" . tablename('xcommunity_activity') . "t4 on t4.id=t3.aid left join " . tablename('mc_members') . "t5 on t3.uid = t5.uid left join" . tablename('xcommunity_activity_region') . "t6 on t6.activityid = t4.id where $condition";
                $total = pdo_fetchcolumn($tsql, $param);
                $pager = pagination($total, $pindex, $psize);
//            $sql = "select * from ".tablename('xcommunity_activity')."where $condition ";
//            $list = pdo_fetchall($sql,$param);
//            $data ='';
//            foreach ($list as $key => $item){
//                $res = pdo_get('xcommunity_res',array('aid' => $item['id']),array('mobile','truename','num','status','createtime'));
//                $data[] = array(
//                    'title' => $item['title'],
//                    'mobile' => $res['mobile'],
//                    'truename' => $res['truename'],
//                    'num' => $res['num'],
//                    'status' => $res['status'],
//                    'createtime' => $res['createtime'],
//                    'price' => $item['price']
//                );
//            }
//            $total  = pdo_fetchcolumn("SELECT COUNT(*) FROM".tablename('xcommunity_activity')."where $condition ",$param);
//            $pager = pagination($total, $pindex, $psize);
                include $this->template('app/list_activity');
            }
            elseif ($xqtype == 8) {
                //家政
                $condition = 't1.uniacid=:uniacid';
                $params[':uniacid'] = $_W['uniacid'];
                if ($user[type] == 3) {
                    $condition .= " and t5.id in({$user['regionid']})";
                }
                $pindex = max(1, intval($_GPC['page']));
                $psize = 10;
                $sql = "select t5.title,t1.*,t4.realname,t4.mobile,t2.area,t2.build,t2.unit,t2.room,t6.name from" . tablename('xcommunity_homemaking') . "t1 left join" . tablename('xcommunity_member_room') . "t2 on t1.addressid=t2.id left join" . tablename('mc_members') . "t4 on t4.uid=t1.uid left join " . tablename('xcommunity_region') . "t5 on t5.id = t1.regionid left join" . tablename('xcommunity_category') . "t6 on t6.id=t1.category where $condition order by t1.createtime desc limit " . ($pindex - 1) * $psize . ',' . $psize;
                $list = pdo_fetchall($sql, $params);
                $tsql = "select count(*) from" . tablename('xcommunity_homemaking') . "t1 left join" . tablename('xcommunity_member_room') . "t2 on t1.addressid=t2.id left join" . tablename('mc_members') . "t4 on t4.uid=t1.uid left join " . tablename('xcommunity_region') . "t5 on t5.id = t1.regionid left join" . tablename('xcommunity_category') . "t6 on t6.id=t1.category where $condition order by t1.createtime desc ";

                $total = pdo_fetchcolumn($tsql, $params);

                $pager = pagination($total, $pindex, $psize);
//            $list = pdo_fetchall("SELECT * FROM" . tablename('xcommunity_homemaking') . "WHERE $condition order by createtime desc LIMIT " . ($pindex - 1) * $psize . ',' . $psize, $params);
//            foreach ($list as $key => $item){
//                $category =  pdo_get('xcommunity_category',array('id' => $item['category']),array('name'));
//                $list[$key]['cname'] = $category['name'];
//            }
//            $total = pdo_fetchcolumn("SELECT COUNT(*) FROM" . tablename('xcommunity_homemaking') . "WHERE $condition", $params);
//            $pager = pagination($total, $pindex, $psize);
                include $this->template('app/list_homemaking');
            }
            elseif ($xqtype == 9) {
                //租赁
                $condition = 't1.uniacid=:uniacid';
                $params[':uniacid'] = $_W['uniacid'];
                if ($user[type] == 3) {
                    $condition .= " and t1.regionid in({$user['regionid']})";
                }
                $sql = "select t1.*,t2.realname,t2.mobile from" . tablename('xcommunity_houselease') . "t1 left join" . tablename('mc_members') . "t2 on t1.uid=t2.uid where $condition order by t1.createtime desc limit " . ($pindex - 1) * $psize . ',' . $psize;
                $list = pdo_fetchall($sql, $params);
//            $pindex = max(1, intval($_GPC['page']));
//            $psize = 10;
//            $list = pdo_fetchall("SELECT * FROM" . tablename('xcommunity_houselease') . "WHERE $condition order by createtime desc LIMIT " . ($pindex - 1) * $psize . ',' . $psize, $params);
//            $total = pdo_fetchcolumn("SELECT COUNT(*) FROM" . tablename('xcommunity_houselease') . "WHERE $condition", $params);
//            $pager = pagination($total, $pindex, $psize);
                include $this->template('app/list_houselease');
            }
            elseif ($xqtype == 10) {
                //二手
                $condition = 't1.uniacid=:uniacid';
                $params[':uniacid'] = $_W['uniacid'];
                if ($user[type] == 3) {
                    $condition .= " and t3.regionid in({$user['regionid']})";
                }
                $sql = "select t1.*,t4.realname,t4.mobile from" . tablename('xcommunity_fled') . "t1 left join" . tablename('xcommunity_member_room') . "t2 on t1.addressid=t2.id left join" . tablename('mc_members') . "t4 on t4.uid=t1.uid where $condition limit " . ($pindex - 1) * $psize . ',' . $psize;
                $list = pdo_fetchall($sql, $params);
                $tsql = "select count(*) from" . tablename('xcommunity_fled') . "t1 left join" . tablename('xcommunity_member_room') . "t2 on t1.addressid=t2.id left join" . tablename('mc_members') . "t4 on t4.uid=t1.uid where $condition";
                $total = pdo_fetchcolumn($tsql, $params);
//            $psize = 8;
//            $list = pdo_fetchall("SELECT * FROM" . tablename('xcommunity_fled') . "WHERE $condition order by createtime desc LIMIT " . ($pindex - 1) * $psize . ',' . $psize, $params);
//            $total = pdo_fetchcolumn("SELECT COUNT(*) FROM" . tablename('xcommunity_fled') . "WHERE $condition", $params);
                $pager = pagination($total, $pindex, $psize);
                include $this->template('app/list_fled');
            }
            elseif ($xqtype == 11) {
                //拼车
                $condition = 'uniacid=:uniacid';
                $params[':uniacid'] = $_W['uniacid'];
                if ($user[type] == 3) {
                    $condition .= " and id in({$user['regionid']})";
                }
                $pindex = max(1, intval($_GPC['page']));
                $psize = 10;
                $list = pdo_fetchall("SELECT * FROM" . tablename('xcommunity_carpool') . "WHERE $condition order by createtime desc LIMIT " . ($pindex - 1) * $psize . ',' . $psize, $params);
                $total = pdo_fetchcolumn("SELECT COUNT(*) FROM" . tablename('xcommunity_carpool') . "WHERE $condition", $params);
                $pager = pagination($total, $pindex, $psize);
                include $this->template('app/list_car');
            }
            elseif ($xqtype == 12) {
                //住户
                $condition = "t2.uniacid=:uniacid ";
                $params[':uniacid'] = $_W['uniacid'];
                if ($user[type] == 3) {
                    $condition .= " and t2.regionid in({$user['regionid']})";
                }

                $sql = "select t1.uid,t1.realname,t1.mobile,t1.createtime,t2.remark,t2.status,t2.enable,t2.open_status,t2.regionid,t2.id,t3.title,t4.openid from" . tablename('mc_members') . "as t1 left join" . tablename('xcommunity_member') . "as t2 on t1.uid=t2.uid left join" . tablename('xcommunity_region') . "as t3 on t2.regionid=t3.id left join" . tablename('mc_mapping_fans') . "t4 on t4.uid=t2.uid left join" . tablename('xcommunity_member_bind') . "t5 on t5.memberid = t2.id where $condition and t5.id <> '' order by t1.createtime desc LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
                $list = pdo_fetchall($sql, $params);
                foreach ($list as $key => $val) {

                    $con = 't1.memberid=:memberid';
                    $par[":memberid"] = $val['id'];

                    $bsql = "select t1.status as bstatus,t2.address,t1.id from" . tablename('xcommunity_member_bind') . "t1 left join" . tablename('xcommunity_member_room') . "t2 on t1.addressid = t2.id where $con";
                    $binds = pdo_fetchall($bsql, $par);
                    $list[$key]['bind'] = $binds;
                }
                $tsql = "select count(*) from" . tablename('mc_members') . "as t1 left join" . tablename('xcommunity_member') . "as t2 on t1.uid=t2.uid left join" . tablename('xcommunity_region') . "as t3 on t2.regionid=t3.id left join" . tablename('xcommunity_member_bind') . "t5 on t5.memberid = t2.id where $condition and t1.mobile <> '' and t5.id <> '' ";
                $total = pdo_fetchcolumn($tsql, $params);
                $pager = pagination($total, $pindex, $psize);
                include $this->template('app/list_member');
            }
            elseif ($xqtype == 13) {
                //门禁
                $condition = 't1.uniacid=:uniacid';
                $params[':uniacid'] = $_W['uniacid'];
                if ($user['type'] == 3) {
                    //普通管理员
                    $condition .= " AND t1.regionid in({$user['regionid']})";
                }
                $sql = "select t1.*,t2.title as xqtitle from" . tablename('xcommunity_building_device') . "t1 left join" . tablename('xcommunity_region') . "t2 on t1.regionid=t2.id where $condition limit " . ($pindex - 1) * $psize . ',' . $psize;
                $list = pdo_fetchall($sql, $params);
                $tsql = "select count(*) from" . tablename('xcommunity_building_device') . "t1 left join" . tablename('xcommunity_region') . "t2 on t1.regionid=t2.id where $condition ";
                $total = pdo_fetchcolumn($tsql, $params);
                $pager = pagination($total, $pindex, $psize);
                include $this->template('app/list_guard');
            }
            elseif ($xqtype == 14) {
                //内部通知
                $condition = 't1.uniacid=:uniacid ';
                $params[':uniacid'] = $_W['uniacid'];
//                $params[':companyid'] = $user['pid'];
                $sql = "select t1.* from" . tablename('xcommunity_memo') . "t1 left join" . tablename('xcommunity_memo_company') . "t2 on t1.id=t2.memoid where $condition";
                $list = pdo_fetchall($sql, $params);
                $tsql = "select count(*) from" . tablename('xcommunity_memo') . "t1 left join" . tablename('xcommunity_memo_company') . "t2 on t1.id=t2.memoid where $condition";
                $total = pdo_fetchcolumn($tsql, $params);
                $pager = pagination($total, $pindex, $psize);
                include $this->template('app/list_memo');
            }
        }
    }
    elseif ($op == 'detail') {
        //处理报修和建议
        if (empty($_SESSION['sysuid'])) {
            itoast('请重新登录', $this->createMobileUrl('xqsys', array('op' => 'login')), 'error');
            exit();
        }
        $id = intval($_GPC['id']);
        if (empty($id)) {
            itoast('缺少参数', referer(), 'error');
            exit();
        }
        $sql = "select t1.*,t2.area,t2.build,t2.unit,t2.room,t3.content as rank_content,t3.rank,t5.realname,t5.mobile,t5.uid,t6.name from" . tablename('xcommunity_report') . "t1 left join" . tablename('xcommunity_member_room') . "t2 on t1.addressid=t2.id left join" . tablename('xcommunity_rank') . "t3 on t1.id=t3.rankid left join " . tablename('mc_members') . "t5 on t1.uid= t5.uid left join" . tablename('xcommunity_category') . "t6 on t6.id = t1.cid where t1.id=:id";
        $item = pdo_fetch($sql, array(':id' => $id));
        $addr = '';
        if ($item['area']) {
            $addr .= $item['area'] . '区';
        }
        if ($item['build']) {
            $addr .= $item['build'] . '栋';
        }
        if ($item['unit']) {
            $addr .= $item['unit'] . '单元';
        }
        if ($item['room']) {
            $addr .= $item['room'] . '室';
        }
        $imgs = pdo_fetchall("select * from" . tablename('xcommunity_report_images') . "as t1 left join" . tablename('xcommunity_images') . "as t2 on t1.thumbid =t2.id where t1.reportid=:reportid", array(':reportid' => $item['id']));

        if ($_W['isajax']) {

            $data = array(
                'content'    => $_GPC['content'],
                'reportid'   => $_GPC['id'],
                'createtime' => TIMESTAMP,
                'uid'        => $_SESSION['sysuid'],
            );
            if (pdo_insert('xcommunity_report_log', $data)) {
                pdo_query("update " . tablename('xcommunity_report') . "set status = :status where id=:id", array(':status' => intval($_GPC['status']), ':id' => intval($_GPC['id'])));
                $user = util::xquser($_SESSION['sysuid']);
                if ($_GPC['type'] == 1) {
                    if (set('p53')) {
                        $content = $_GPC['status'] == 3 ? '您的报修正在处理中' : '您的报修已处理';
                        util::app_send($item['uid'], $content);
                    }
                    if (set('t7')) {
                        if ($_GPC['status'] == 1) {
                            //模板消息通知
                            $url = $_W['siteroot'] . "app/index.php?i={$_W['uniacid']}&c=entry&id={$item['id']}&op=detail&do=repair&m=" . $this->module['name'];

                            $content = array(
                                'first'    => array(
                                    'value' => '尊敬的业主，您的报修已经完成',
                                ),
                                'keyword1' => array(
                                    'value' => $addr,
                                ),
                                'keyword2' => array(
                                    'value' => $item['content'],
                                ),
                                'keyword3' => array(
                                    'value' => $user['realname'],
                                ),
                                'keyword4' => array(
                                    'value' => date('Y-m-d H:i', TIMESTAMP),
                                ),
                                'remark'   => array(
                                    'value' => '请到微信我的报修给我们评价，谢谢使用！',
                                ),
                            );
                            $tplid = set('t8');
                            $fans = mc_fansinfo($item['uid']);
                            util::sendTplNotice($fans['openid'], $tplid, $content, $url, $topcolor = '#FF683F');
                        }
                    }
                }
                else {
                    if (set('p53')) {
                        $content = $_GPC['status'] == 3 ? '您的建议正在处理中' : '您的报修已处理';
                        util::app_send($item['uid'], $content);
                    }
                    if (set('t9')) {
                        if ($_GPC['status'] == 1) {
                            //模板消息通知
                            $url = $_W['siteroot'] . "app/index.php?i={$_W['uniacid']}&c=entry&id={$item['id']}&op=detail&do=report&m=" . $this->module['name'];
                            $content = array(
                                'first'    => array(
                                    'value' => '尊敬的业主，您的提交的建议，我们已经处理',
                                ),
                                'keyword1' => array(
                                    'value' => $item['realname'],
                                ),
                                'keyword2' => array(
                                    'value' => $item['name'],
                                ),
                                'keyword3' => array(
                                    'value' => $item['content'],
                                ),
                                'keyword4' => array(
                                    'value' => date('Y-m-d H:i', TIMESTAMP),
                                ),
                                'keyword5' => array(
                                    'value' => $user['realname'],
                                ),
                                'remark'   => array(
                                    'value' => '请到微信我的意见给我们评价，谢谢使用！',
                                ),
                            );
                            $tplid = set('t10');
                            $fans = mc_fansinfo($item['uid']);
                            $result = util::sendTplNotice($fans['openid'], $tplid, $content, $url, $topcolor = '#FF683F');

                        }
                    }
                }

                echo json_encode(array('result' => 1));
                exit();
            }
        }
        include $this->template('app/detail');
    }
    elseif ($op == 'add_notice') {
        //发布通知
        $user = util::xquser($_SESSION['sysuid']);
        if ($user[type] == 2 || $user[type] == 3) {
            //普通管理员
            $condition .= " AND uid='{$_SESSION['sysuid']}'";
        }
        $regions = model_region::region_fetall('', $_SESSION['sysuid']);

        $id = intval($_GPC['id']);
        if ($id) {
            $item = pdo_get('xcommunity_announcement', array('id' => $id), array('id', 'title', 'reason'));
            $regionids = pdo_getall('xcommunity_announcement_region', array('aid' => $id), array('regionid'));
            $regs = '';
            foreach ($regionids as $key => $val) {
                $regs[] = $val['regionid'];
            }


        }

        if ($_W['isajax']) {
            $imgname = util::get_media($_GPC['pic']);
            $regionid = explode(',', $_GPC['regionid']);
            $data = array(
                'title'      => trim($_GPC['title']),
                'reason'     => trim($_GPC['reason']),
                'createtime' => TIMESTAMP,
                'uniacid'    => $_W['uniacid'],
                'status'     => 2,
                'enable'     => 1,//1显示2隐藏
                'pic'        => $imgname
            );
            if (empty($id)) {
                $data['uid'] = $_SESSION['sysuid'];
                pdo_insert("xcommunity_announcement", $data);
                $id = pdo_insertid();
            }
            else {
                pdo_update("xcommunity_announcement", $data, array('id' => $id));
                pdo_delete('xcommunity_announcement_region', array('aid' => $id));
            }
            foreach ($regionid as $key => $value) {
                $dat = array(
                    'aid'      => $id,
                    'regionid' => $value,
                );
                pdo_insert('xcommunity_announcement_region', $dat);
            }

            if (set('t1')) {
                $url = $_W['siteroot'] . "app/index.php?i={$_W['uniacid']}&c=entry&id={$id}&op=detail&do=announcement&m=" . $this->module['name'];
                $reason = htmlspecialchars_decode($data['reason']);
                $content = str_replace(array('<br>', '&nbsp;'), array("\n", ' '), $reason);
                $content = strip_tags($content, '<a>');
                $tplid = set('t2');
                $ddata = array(
                    'first'    => array(
                        'value' => '',
                    ),
                    'keyword1' => array(
                        'value' => $data['title'],
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


                //可发送业主
                $regionids = ltrim(rtrim($_GPC['regionid'], ","), ',');
                $sql = "select t1.uid,t2.openid from" . tablename('xcommunity_member') . "t1 left join" . tablename('mc_mapping_fans') . "t2 on t1.uid = t2.uid where t1.regionid in({$regionids}) group by t2.openid";
                $members = pdo_fetchall($sql);
                foreach ($members as $key => $val) {
                    $user = pdo_get('xcommunity_send_log', array('uid' => $val['uid'], 'sendid' => $id), array('status', 'id'));
                    if (empty($user) || $user['status'] == 2) {
                        if ($val['openid']) {
                            $resp = util::sendTplNotice($val['openid'], $tplid, $ddata, $url, $topcolor = '#FF683F');
                        }
                        $d = array(
                            'uniacid' => $_W['uniacid'],
                            'sendid'  => $id,
                            'uid'     => $val['uid'],
                            'type'    => 1,
                            'cid'     => 1,
                        );
                        if (empty($resp['errcode'])) {
                            $d['status'] = 1;
                            pdo_insert('xcommunity_send_log', $d);
                        }
                        else {
                            $d['status'] = 2;
                            pdo_insert('xcommunity_send_log', $d);
                        }
                    }
                }
            }

            echo json_encode(array('status' => 1));
            exit();

        }
        include $this->template('app/add_notice');
    }
    elseif ($op == 'cost_order') {
        //缴费订单
        $id = intval($_GPC['id']);
        if (empty($id)) {
            itoast('缺少参数', $this->createMobileUrl('xqsys', array('op' => 'index')), 'error');
            exit();
        }
        $pindex = max(1, intval($_GPC['page']));
        $psize = 20;
        $condition = 't1.uniacid=:uniacid';
        $params[':uniacid'] = $_W['uniacid'];
        if ($id) {
            $condition .= " and t3.cid=:id";
            $params[':id'] = $id;
        }
        $sql = "select t1.transid,t1.ordersn,t1.price,t2.price as goodsprice,t6.realname,t4.area,t4.build,t4.unit,t4.room,t3.credit1,t1.status,t1.createtime,t1.id,t3.homenumber from" . tablename('xcommunity_order') . "t1 left join"
            . tablename('xcommunity_order_goods') . "t2 on t1.id=t2.orderid left join" . tablename('xcommunity_cost_list') . "t3 on t2.goodsid = t3.id left join" . tablename('xcommunity_member_room') . "t4 on t4.id=t1.addressid left join" . tablename('mc_members') . "t6 on t6.uid=t1.uid where $condition order by t1.createtime desc limit " . ($pindex - 1) * $psize . ',' . $psize;

        $list = pdo_fetchall($sql, $params);

        $tsql = "select count(*) from" . tablename('xcommunity_order') . "t1 left join"
            . tablename('xcommunity_order_goods') . "t2 on t1.id=t2.orderid left join" . tablename('xcommunity_cost_list') . "t3 on t2.goodsid = t3.id left join" . tablename('xcommunity_member_room') . "t4 on t4.id=t1.addressid left join" . tablename('mc_members') . "t6 on t6.uid=t1.uid where $condition order by t1.createtime desc ";
        $total = pdo_fetchcolumn($tsql, $params);
        $pager = pagination($total, $pindex, $psize);
        include $this->template('app/cost_order');
    }
    elseif ($op == 'shop_detail') {
        //超市订单详情
        $id = intval($_GPC['id']);
        if (empty($id)) {
            itoast('缺少参数', $this->createMobileUrl('xqsys', array('op' => 'index')), 'error');
            exit();
        }

        if ($id) {
            $sql = "select t1.*,t4.realname,t4.mobile,t5.title,t2.address from" . tablename('xcommunity_order') . "t1 left join" . tablename('xcommunity_member_address') . "t2 on t1.addressid = t2.id left join" . tablename('mc_members') . "t4 on t4.uid=t1.uid left join" . tablename('xcommunity_region') . "t5 on t1.regionid=t5.id left join" . tablename('mc_mapping_fans') . "t6 on t6.uid = t4.uid where t1.id=:orderid";
            $params[':orderid'] = $id;

            $item = pdo_fetch($sql, $params);

        }
        //获取商品信息
        $goods = pdo_fetchall("SELECT g.title,g.marketprice,g.id,o.total as gtotal FROM " . tablename('xcommunity_order_goods') .
            " o left join " . tablename('xcommunity_goods') . " g on o.goodsid=g.id " . " WHERE o.orderid='{$id}'");
        $good = '';
        $total = '';
        foreach ($goods as $k => $v) {
            $goodcode = $val['goodcode'] ? '编码' . $val['goodcode'] . ',' : '';
            $good .= $v['title'] . ',' . $goodcode . '数量' . $v['gtotal'] . ',价格' . $v['marketprice'] . '。' . '<br/>';
            $total = $total + $v['marketprice'] * $v['gtotal'];
        }
        if (empty($item)) {
            itoast("抱歉，订单不存在!", referer(), "error");
        }
        if (checksubmit('confirmsend')) {
            //$item = pdo_fetch("SELECT * FROM " . tablename('xcommunity_order') . " WHERE id = :id", array(':id' => $id));
            if (!empty($item['transid'])) {
                $this->changeWechatSend($id, 1);
            }
            if (set('s2') && set('s6')) {
                $type = set('s1') == 1 ? 'wwt' : 'juhe';
                $expresscom = $_GPC['realname'];
                $expresssn = $_GPC['express'];
                $sdst = $item['mobile'];
                if ($type == 'wwt') {
                    $smsg = "您的快递是" . $expresscom . ",快递单号" . $expresssn . "。有任何问题请随时与我们联系，谢谢。";
                    $content = sms::send($sdst, '', $smsg, $type);
                }
                else {
                    $smsg = urlencode("#express_name#=$expresscom&#express_code#=$expresssn");
                    //$content = sms::send($sdst,set('s10'),$smsg,$type,array('regionid' => $item['regionid']));
                    $content = sms::send($sdst, $smsg, '', 1, set('s10'));
                }

            }
            if (set('t15')) {
                $createtime = date('Y-m-d H:i:s', $_W['timestamp']);
                $content = array(
                    'first'    => array(
                        'value' => '发货啦，小主人，我是您的商品呀，老板已经安排发货了，我和您即将团聚了，等我哟！',
                    ),
                    'keyword1' => array(
                        'value' => $item['price'] . '元',
                    ),
                    'keyword2' => array(
                        'value' => $title,
                    ),
                    'keyword3' => array(
                        'value' => $item['realname'] . ',' . $item['mobile'] . ',' . $item['address'],
                    ),
                    'keyword4' => array(
                        'value' => $item['ordersn'],
                    ),
                    'keyword5' => array(
                        'value' => $expresscom . ',' . $expresssn,
                    ),
                    'remark'   => array(
                        'value' => '有任何问题请随时与我们联系，谢谢。',
                    ),
                );
                $tplid = set('t16');
                util::sendTplNotice($item['openid'], $tplid, $content, $url = '');
            }


            pdo_update(
                'xcommunity_order',
                array(
                    'status' => 2,
                    'remark' => $_GPC['remark'],
                ),
                array('id' => $id)
            );
            itoast('发货操作成功！', referer(), 'success');
        }
        if (checksubmit('cancelsend')) {
            $item = pdo_fetch("SELECT transid FROM " . tablename('xcommunity_order') . " WHERE id = :id", array(':id' => $id));
            if (!empty($item['transid'])) {
                $this->changeWechatSend($id, 0, $_GPC['cancelreson']);
            }
            pdo_update(
                'xcommunity_order',
                array(
                    'status' => 1,
                    'remark' => $_GPC['remark'],
                ),
                array('id' => $id)
            );
            itoast('取消发货操作成功！', referer(), 'success');
        }
        if (checksubmit('finish')) {
            pdo_update('xcommunity_order', array('status' => 3, 'remark' => $_GPC['remark']), array('id' => $id));
            itoast('订单操作成功！', referer(), 'success');
        }
        if (checksubmit('cancel')) {
            pdo_update('xcommunity_order', array('status' => 1, 'remark' => $_GPC['remark']), array('id' => $id));
            itoast('取消完成订单操作成功！', referer(), 'success');
        }
        if (checksubmit('cancelpay')) {
            pdo_update('xcommunity_order', array('status' => 0, 'remark' => $_GPC['remark']), array('id' => $id));
            //设置库存
            $this->setOrderStock($id, false);
            //减少积分
            $this->setOrderCredit($id, false);

            itoast('取消订单付款操作成功！', referer(), 'success');
        }
        if (checksubmit('confrimpay')) {
            pdo_update('xcommunity_order', array('status' => 1, 'paytype' => 4, 'remark' => $_GPC['remark']), array('id' => $id));
            //设置库存
            $this->setOrderStock($id);
            //增加积分
            $this->setOrderCredit($id);
            itoast('确认订单付款操作成功！', referer(), 'success');
        }
        if (checksubmit('close')) {
            $item = pdo_fetch("SELECT transid FROM " . tablename('xcommunity_order') . " WHERE id = :id", array(':id' => $id));
            if (!empty($item['transid'])) {
                $this->changeWechatSend($id, 0, $_GPC['reson']);
            }
            pdo_update('xcommunity_order', array('status' => -1, 'remark' => $_GPC['remark']), array('id' => $id));
            itoast('订单关闭操作成功！', referer(), 'success');
        }
        if (checksubmit('open')) {
            $item = pdo_fetch("SELECT paytype FROM " . tablename('xcommunity_order') . " WHERE id = :id", array(':id' => $id));
            if (!empty($item['paytype']) && $item['paytype'] != 3) {
                $status = 1;
            }
            pdo_update('xcommunity_order', array('status' => $status, 'remark' => $_GPC['remark']), array('id' => $id));
            itoast('开启订单操作成功！', referer(), 'success');
        }

        include $this->template('app/shop_detail');
    }
    elseif ($op == 'homemaking_detail') {
        $id = intval($_GPC['id']);
        if (empty($id)) {
            itoast('缺少参数', referer(), 'error');
            exit();
        }
        //$item = pdo_get('xcommunity_homemaking',array('id'=> $id),array('content','realname','mobile','address','status','servicetime'));
        $sql = "select t5.title,t1.*,t4.realname,t4.mobile,t2.area,t2.build,t2.unit,t2.room from" . tablename('xcommunity_homemaking') . "t1 left join" . tablename('xcommunity_member_room') . "t2 on t1.addressid=t2.id left join" . tablename('mc_members') . "t4 on t4.uid=t1.uid left join" . tablename('xcommunity_region') . "t5 on t5.id=t1.regionid where t1.id=:id order by t1.createtime desc ";
        $item = pdo_fetch($sql, array(':id' => $id));
        $addr = '';
        if ($item['area']) {
            $addr .= $item['area'] . '区';
        }
        if ($item['build']) {
            $addr .= $item['build'] . '栋';
        }
        if ($item['unit']) {
            $addr .= $item['unit'] . '单元';
        }
        if ($item['room']) {
            $addr .= $item['room'] . '室';
        }
        $item['address'] = $addr;
        if (empty($item)) {
            itoast('信息不存在或已被删除', referer(), 'error');
            exit();
        }
        if ($_W['isajax']) {
            $status = intval($_GPC['status']);
            $id = intval($_GPC['id']);
            if (pdo_update('xcommunity_homemaking', array('status' => $status), array('id' => $id))) {
                echo json_encode(array('status' => 1));
                exit();
            }
        }
        include $this->template('app/homemaking_detail');
    }
    elseif ($op == 'houselease_detail') {
        $id = intval($_GPC['id']);
        if (empty($id)) {
            itoast('缺少参数', referer(), 'error');
            exit();
        }
//    $item = pdo_get('xcommunity_houselease',array('id'=> $id),array('title','status','realname','mobile','status','model_area','floor_layer','floor_number','price','model_room','model_hall','model_toilet','fitment','house','price_way'));
        $sql = "select t1.*,t2.realname,t2.mobile from" . tablename('xcommunity_houselease') . "t1 left join" . tablename('mc_members') . "t2 on t1.uid = t2.uid where t1.id=:id";
        $item = pdo_fetch($sql, array(':id' => $id));
        if (empty($item)) {
            itoast('信息不存在或已被删除', referer(), 'error');
            exit();
        }
        include $this->template('app/houselease_detail');
    }
    elseif ($op == 'fled_detail') {
        $id = intval($_GPC['id']);
        if (empty($id)) {
            itoast('缺少参数', referer(), 'error');
            exit();
        }
        //$item = pdo_get('xcommunity_fled',array('id'=> $id),array('title','realname','mobile','zprice','description','createtime'));
        $sql = "select t1.*,t4.realname,t4.mobile from" . tablename('xcommunity_fled') . "t1 left join" . tablename('xcommunity_member_room') . "t2 on t1.addressid=t2.id left join" . tablename('mc_members') . "t4 on t4.uid=t1.uid where t1.id=:id";

        $item = pdo_fetch($sql, array(':id' => $id));

        if (empty($item)) {
            itoast('信息不存在或已被删除', referer(), 'error');
            exit();
        }
        include $this->template('app/fled_detail');
    }
    elseif ($op == 'member_detail') {
        $id = intval($_GPC['id']);
        if (empty($id)) {
            itoast('缺少参数', referer(), 'error');
            exit();
        }
//    $item = pdo_get('xcommunity_member',array('id'=> $id),array('address','createtime','status','open_status'));
        $sql = "select * from" . tablename('xcommunity_member') . "t1 left join" . tablename('mc_members') . "t2 on t1.uid=t2.uid where t1.id=:id";
        $item = pdo_fetch($sql, array(':id' => $id));
        if (empty($item)) {
            itoast('信息不存在或已被删除', referer(), 'error');
            exit();
        }
        if ($_W['isajax']) {
            $status = intval($_GPC['status']);
            $open_status = intval($_GPC['open_status']);
            $id = intval($_GPC['id']);
            if (pdo_update('xcommunity_member', array('status' => $status, 'open_status' => $open_status), array('id' => $id))) {
                echo json_encode(array('status' => 1));
                exit();
            }
        }
        include $this->template('app/member_detail');
    }
    elseif ($op == 'add_guard') {
        //发布门禁
        $user = util::xquser($_SESSION['sysuid']);
        if ($user['type'] == 3) {
            $condition = " and id in({$user['regionid']})";
        }
        $regions = model_region::region_fetall($condition);
        $id = intval($_GPC['id']);
        if ($id) {
            $item = pdo_get('xcommunity_building_device', array('id' => $id), array());
        }
        if ($_W['isajax']) {
            $data = array(
                'uniacid'     => $_W['uniacid'],
                'title'       => $_GPC['title'],
                'api_key'     => $_GPC['api_key'],
                'device_code' => $_GPC['device_code'],
                'lock_code'   => $_GPC['lock_code'],
                'type'        => intval($_GPC['type']),
                'status'      => intval($_GPC['status']),
                'openurl'     => $_GPC['openurl'],
                'regionid'    => intval($_GPC['regionid'])
            );
            if ($data['type'] == 1) {
                $data['unit'] = $_GPC['unit'];
            }
            if ($id) {
                $result = pdo_update('xcommunity_building_device', $data, array('id' => $id));
            }
            else {
                $result = pdo_insert('xcommunity_building_device', $data);
            }
            if ($result) {
                echo json_encode(array('status' => 1));
                exit();
            }
        }
        include $this->template('app/add_guard');
    }
    elseif ($op == 'shop') {
        if ($_W['isajax']) {
            $type = intval($_GPC['type']);
            $orderid = intval($_GPC['orderid']);
            if ($type == 1 && $orderid) {
                $data = array(
                    'status' => 2,
                    'remark' => $_GPC['remark'],
                );
                $result = pdo_update('xcommunity_order', $data, array('id' => $orderid));
                if ($result) {
                    echo json_encode(array('status' => 1));
                    exit();
                }

            }
            if ($type == 2 && $orderid) {
                $data = array(
                    'status' => 1,
                    'remark' => $_GPC['remark'],
                );
                $result = pdo_update('xcommunity_order', $data, array('id' => $orderid));
                if ($result) {
                    echo json_encode(array('status' => 1));
                    exit();
                }

            }
            if ($type == 3 && $orderid) {
                $data = array(
                    'status' => 4,
                    'remark' => $_GPC['remark'],
                );
                $result = pdo_update('xcommunity_order', $data, array('id' => $orderid));
                if ($result) {
                    echo json_encode(array('status' => 1));
                    exit();
                }

            }
        }
    }
    elseif ($op == 'business_detail') {
        $orderid = intval($_GPC['id']);
        if (empty($orderid)) {
            itoast('缺少参数', referer(), 'error');
            exit();
        }

        $list = pdo_getall('xcommunity_coupon_order', array('orderid' => $orderid), array());
        include $this->template('app/business_detail');
    }
    elseif ($op == 'business') {

        $id = intval($_GPC['id']);
        if (empty($id)) {
            itoast('缺少参数', referer(), 'error');
            exit();
        }

        $sql = "select t1.couponsn,t7.realname,t7.mobile,t3.title,t1.createtime from" . tablename('xcommunity_coupon_order') . "t1 left join" . tablename('xcommunity_order_goods') . "t2 on t1.orderid=t2.orderid left join" . tablename('xcommunity_goods') . "t3 on t3.id=t2.goodsid left join" . tablename('xcommunity_order') . "t4 on t4.id=t1.orderid left join" . tablename('xcommunity_member_room') . "t5 on t5.id= t4.addressid left join" . tablename('mc_members') . "t7 on t7.uid=t1.uid where t1.id=:id";
        $item = pdo_fetch($sql, array(':id' => $id));

        include $this->template('app/business');
    }
    elseif ($op == 'use') {
        if ($_W['isajax']) {
            $couponid = intval($_GPC['couponid']);
            if ($couponid) {
                $result = pdo_update('xcommunity_order', array('enable' => 2, 'usetime' => TIMESTAMP), array('id' => $couponid));
                if ($result) {
                    echo json_encode(array('status' => 1));
                    exit();
                }
            }
        }
    }
    elseif ($op == 'memo_detail') {
        $id = intval($_GPC['id']);
        if (empty($id)) {
            itoast('缺少参数');
            exit();
        }

        $detail = pdo_get('xcommunity_memo', array('id' => $id), array());


        include $this->template('app/memo_detail');
    }
}
