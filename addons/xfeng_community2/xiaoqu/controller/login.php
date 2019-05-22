<?php
/**
 * 蓝牛科技 Copyright (c) app.xqms.cn
 */

if (!defined('ES_PATH')) {
    exit('Access Denied');
}

class LoginController extends Controller
{
    public function index()
    {
        global $_W;
        global $_GPC;
        $title = '登录';
        include $this->template('login/index');
    }

    public function check()
    {
        global $_W, $_GPC;
        load()->model('user');
        $member = array();
        $username = trim($_GPC['username']);

        pdo_query('DELETE FROM' . tablename('users_failed_login') . ' WHERE lastupdate < :timestamp', array(':timestamp' => TIMESTAMP - 300));

        $failed = pdo_get('users_failed_login', array('username' => $username, 'ip' => CLIENT_IP));
        if (5 <= $failed['count']) {
            $data['msg'] = '输入密码错误次数超过5次，请在5分钟后再登录';
            $data['name'] = 'password';
            echo json_encode($data);
            exit();
        }
        if (empty($username)) {
            $data['msg'] = '请输入要登录的用户名';
            $data['name'] = 'password';
            echo json_encode($data);
            exit();
        }
        $member['username'] = $username;
        $member['password'] = $_GPC['password'];
        if (empty($member['password'])) {
            $data['msg'] = '请输入要登录的密码';
            $data['name'] = 'password';
            echo json_encode($data);
            exit();
        }
        $record = user_single($member);
        if (!empty($record)) {
            if ($record['status'] == USER_STATUS_CHECK || $record['status'] == USER_STATUS_BAN) {
                itoast('您的账号正在审核或是已经被系统禁止，请联系网站管理员解决！', '', '');
            }
            $_W['uid'] = $record['uid'];
            $_W['isfounder'] = user_is_founder($record['uid']);
            $_W['user'] = $record;

            if (empty($_W['isfounder'])) {
                if (!empty($record['endtime']) && $record['endtime'] < TIMESTAMP) {
                    $data['msg'] = '您的账号正在审核或是已经被系统禁止，请联系网站管理员解决！';
                    $data['name'] = 'system';
                    echo json_encode($data);
                    exit();
                }
            }
            if (!empty($_W['siteclose']) && empty($_W['isfounder'])) {
                $data['msg'] = '站点已关闭，关闭原因：' . $_W['setting']['copyright']['reason'];
                $data['name'] = 'system';
                echo json_encode($data);
                exit();
            }
            $cookie = array();
            $cookie['uid'] = $record['uid'];
            $cookie['lastvisit'] = $record['lastvisit'];
            $cookie['lastip'] = $record['lastip'];
            $cookie['hash'] = md5($record['password'] . $record['salt']);
            $session = authcode(json_encode($cookie), 'encode');
            isetcookie('__session', $session, !empty($_GPC['rember']) ? 7 * 86400 : 0, true);
            $status = array();
            $status['uid'] = $record['uid'];
            $status['lastvisit'] = TIMESTAMP;
            $status['lastip'] = CLIENT_IP;
            user_update($status);

            if ($record['uid'] != $_GPC['__uid']) {
                isetcookie('__uniacid', '', -7 * 86400);
                isetcookie('__uid', '', -7 * 86400);
            }

            pdo_delete('users_failed_login', array('id' => $failed['id']));

            //1. 当前用户登是否有 公众账号在使用小区秘书
            $users = pdo_get('xcommunity_users',array('uid'=>$record['uid']),array('uniacid'));
            $uniacid = $users['uniacid'];
            $url = url('site/entry/manage', array('m' => 'xfeng_community'));
            $url = htmlspecialchars($_W['sitescheme']) . $_SERVER['HTTP_HOST'] . '/web/' . substr($url, 2);

            //2. 写入该公众号默认缓存
            load()->model('account');
            uni_account_save_switch($uniacid);

            $data['msg'] = 'true';
            $data['url'] = $url;
        }
        else {
            if (empty($failed)) {
                pdo_insert('users_failed_login', array('ip' => CLIENT_IP, 'username' => $username, 'count' => '1', 'lastupdate' => TIMESTAMP));
            }
            else {
                pdo_update('users_failed_login', array('count' => $failed['count'] + 1, 'lastupdate' => TIMESTAMP), array('id' => $failed['id']));
            }
            $data['msg'] = '登录失败，请检查您输入的用户名和密码！';
            $data['name'] = 'system';
        }
        echo json_encode($data);
        exit();
    }
}