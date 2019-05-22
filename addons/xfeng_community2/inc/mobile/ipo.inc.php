<?php
/*
 * 小区秘书模块
 *
 * [蓝牛科技] Copyright (c) 2013 xqms.cn
 */
global $_GPC, $_W;

$regionid = intval($_GPC['regionid']);

$member = model_user::mc_check();
// 分享
$_share = array(
    'title' => set('p70'),
    'desc' => set('p71'),
    'imgUrl' => tomedia(set('p72')),
    'link' => set('p73') ? set('p73') : $_W['siteroot'] . 'app' . substr($this->createMobileUrl('home'), 1)
);
include $this->template($this->xqtpl('core/ipo'));















