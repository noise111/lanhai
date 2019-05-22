<?php
/**
 * 小区秘书模块
 *
 * [蓝牛科技] Copyright (c) 2013 xqms.cn
 */
/**
 *
 */

global $_W, $_GPC;
//$member = model_user::mc_check('business');
$op = in_array(trim($_GPC['op']), array('list', 'pay', 'my','detail')) ? trim($_GPC['op']) : 'list';
if ($op == 'list') {
    $_share = array(
        'title'  => $_SESSION['community']['title'] . '周边商家',
        'desc'   => set('p71'),
        'imgUrl' => tomedia(set('p72'))
    );
    include $this->template($this->xqtpl('plugin/business/list'));
}
if ($op == 'my') {
    include $this->template($this->xqtpl('plugin/business/my'));
}
if ($op == 'pay') {
    include $this->template($this->xqtpl('plugin/business/pay'));
}

if ($op == 'detail') {
    include $this->template($this->xqtpl('plugin/business/detail'));
}









