<?php
/**
 * 小区秘书模块
 *
 * [蓝牛科技] Copyright (c) 2013 xqms.cn
 */
/**
 * 微信端家政服务
 */

global $_GPC, $_W;
$member = model_user::mc_check('homemaking');
$op = in_array(trim($_GPC['op']), array('list', 'detail','grab')) ? trim($_GPC['op']) : 'list';
if ($op == 'list') {
    $_share = array(
        'title' => $_SESSION['community']['title'].'小区家政',
        'desc' => set('p71'),
        'imgUrl' => tomedia(set('p72'))
    );
    include $this->template($this->xqtpl('plugin/homemaking/list'));
}
elseif ($op == 'detail') {
    include $this->template($this->xqtpl('plugin/homemaking/detail'));
}
elseif ($op == 'grab') {
    include $this->template($this->xqtpl('plugin/homemaking/grab'));
}















