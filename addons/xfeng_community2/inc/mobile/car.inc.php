<?php
/**
 * 小区秘书模块
 *
 * [蓝牛科技] Copyright (c) 2013 xqms.cn
 */
/**
 * 微信端小区拼车
 */

global $_W, $_GPC;
$member = model_user::mc_check('car');
$op = in_array(trim($_GPC['op']), array('list', 'add', 'detail', 'my')) ? trim($_GPC['op']) : 'list';
if ($op == 'list') {
    $_share = array(
        'title' => $_SESSION['community']['title'].'小区拼车',
        'desc' => set('p71'),
        'imgUrl' => tomedia(set('p72'))
    );
    include $this->template($this->xqtpl('plugin/car/list'));
} elseif ($op == 'add') {
    include $this->template($this->xqtpl('plugin/car/add'));
} elseif ($op == 'detail') {
    include $this->template($this->xqtpl('plugin/car/detail'));
}














