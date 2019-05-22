<?php
/**
 * 小区秘书模块
 *
 * [蓝牛科技] Copyright (c) 2013 xqms.cn
 */
/**
 * 微信端常用查询
 */

global $_W, $_GPC;
$member = model_user::mc_check('search');
$_share = array(
    'title' => $_SESSION['community']['title'].'便民查询',
    'desc' => set('p71'),
    'imgUrl' => tomedia(set('p72'))
);
include $this->template($this->xqtpl('plugin/search/list'));
