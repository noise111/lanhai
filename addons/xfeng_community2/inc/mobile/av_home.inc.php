<?php
/**
 * Created by mp.admin9.com.
 * User: fengqiyue
 * Time: 2017/11/28 下午2:27
 */
global $_GPC, $_W;
$ops = array('home');
$op = (!empty($_GPC['op']) && in_array($_GPC['op'], $ops)) ? $_GPC['op'] : 'home';
$userinfo = mc_oauth_userinfo();
include $this->template('default2/home/' . $op);