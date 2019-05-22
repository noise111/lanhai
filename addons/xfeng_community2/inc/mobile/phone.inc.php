<?php
/**
 * 小区秘书模块
 *
 * [蓝牛科技] Copyright (c) 2013 xqms.cn
 */
/**
 * 微信端常用号码
 */
global $_GPC, $_W;
$member = model_user::mc_check('phone');
$op = in_array(trim($_GPC['op']), array('list', 'add')) ? trim($_GPC['op']) : 'list';
if ($op == 'list') {
    $_share = array(
        'title' => $_SESSION['community']['title'].'便民号码',
        'desc' => set('p71'),
        'imgUrl' => tomedia(set('p72'))
    );
    include $this->template($this->xqtpl('plugin/phone/list'));
} elseif ($op == 'add') {
    include $this->template($this->xqtpl('plugin/phone/add'));
}
