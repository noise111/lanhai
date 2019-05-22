<?php
/**
 * Created by we7xq.
 * User: zhoufeng
 * Time: 2017/6/29 下午8:28
 */
global $_W,$_GPC;
$op = in_array($_GPC['op'], array('register', 'login')) ? $_GPC['op'] : 'login';

if ($_W['container'] == 'wechat') {

    if (!empty($_GPC['forward'])) {
        if (is_base64($_GPC['forward'])) {
            $forward = './index.php?' . base64_decode($_GPC['forward']) . '#wechat_redirect';
        }
        if (strexists(urldecode($_GPC['forward']), '://')) {
            $query = parse_url($_GPC['forward'], PHP_URL_QUERY);
            parse_str($query, $query_arr);
            unset($query_arr['from'], $query_arr['isappinstalled']);
            $forward = http_build_query($query_arr);
            $forward = './index.php?' . $forward . '#wechat_redirect';
        }
    }
    if (!empty($_W['member']) && (!empty($_W['member']['mobile']) || !empty($_W['member']['email']))) {
        header('location: ' . $forward);
        exit;
    }
    $oauthinfo = mc_oauth_userinfo();
    if ($oauthinfo['errno'] < 0) {
        message($oauthinfo['message']);
    }
    if (!empty($oauthinfo)) {

        $oauthinfo['nickname'] = stripcslashes($oauthinfo['nickname']);
        if (!empty($userinfo['headimgurl'])) {
            $oauthinfo['headimgurl'] = rtrim($oauthinfo['headimgurl'], '0') . 132;
        }
        $oauthinfo['avatar'] = $oauthinfo['headimgurl'];

        // 根据openid生成一个邮箱
        $email = md5($oauthinfo['openid']) . '@we7.cc';
        // 是否注册过
        $uid = model_user::member_check($email);
        // 注册用户
        if (empty($uid)) {
            $salt = random(8);
            $default_groupid = pdo_fetchcolumn('SELECT groupid FROM ' . tablename('mc_groups') . ' WHERE uniacid = :uniacid AND isdefault = 1', array(':uniacid' => $_W['uniacid']));
            $data = array(
                'uniacid'        => $_W['uniacid'],
                'email'          => $email, // 服务号的openid生成的邮箱
                'salt'           => $salt,
                'groupid'        => $default_groupid,
                'createtime'     => TIMESTAMP,
                'password'       => md5($oauthinfo['openid'] . $salt . $_W['config']['setting']['authkey']),
                'nickname'       => $oauthinfo['nickname'],
                'avatar'         => $oauthinfo['avatar'],
                'gender'         => $oauthinfo['sex'],
                'nationality'    => $oauthinfo['country'],
                'resideprovince' => $oauthinfo['province'] . '省',
                'residecity'     => $oauthinfo['city'] . '市'
            );
            pdo_insert('mc_members', $data);
            $uid = pdo_insertid();
        }
        $_SESSION['uid'] = $uid;
        if (_mc_login(array('uid' => intval($_SESSION['uid'])))) {
            @header('location: ' . $forward);
            exit;
        }
    }

    message('微信授权获取用户信息失败,错误信息为: ' . $oauthinfo['message']);
}

if($op =='login'){
    include $this->template($this->xqtpl('core/auth/login'));
}elseif ($op == 'register'){
    include $this->template($this->xqtpl('core/auth/register'));
}