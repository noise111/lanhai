<?php
/**
 * 会员信息接口
 * Created by mp.admin9.com.
 * User: fengqiyue
 * Time: 2017/11/27 下午1:34
 */
global $_GPC, $_W;
$ops = array('userinfo', 'update');
$op = (!empty($_GPC['op']) && in_array($_GPC['op'], $ops)) ? $_GPC['op'] : 'userinfo';
if ($op == 'userinfo') {
    if (!$_W['member']['uid']) {
        util::send_error(1001, '请先登录');
    }
    $userinfo = mc_fetch($_W['member']['uid']);
    if (empty($userinfo)) {
        util::send_error(-1, '该会员已被删除或不存在!');
    }
    util::send_result($userinfo);
}

// 修改会员信息
if ($op == 'update') {
    util::send_result();
}