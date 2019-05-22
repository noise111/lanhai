<?php
/**
 * 小区秘书模块
 *
 * [蓝牛科技] Copyright (c) 2013 xqms.cn
 */
/**
 * 微信端房屋租赁
 */
global $_W, $_GPC;
$member = model_user::mc_check('houselease');
$op = in_array(trim($_GPC['op']), array('list', 'detail', 'add', 'post')) ? trim($_GPC['op']) : 'list';
if ($op == 'list') {
    $_share = array(
        'title' => $_SESSION['community']['title'].'房屋租赁',
        'desc' => set('p71'),
        'imgUrl' => tomedia(set('p72'))
    );
    include $this->template($this->xqtpl('plugin/houselease/list'));
} elseif ($op == 'detail') {

    include $this->template($this->xqtpl('plugin/houselease/detail'));
} elseif ($op == 'add') {

        include $this->template($this->xqtpl('plugin/houselease/add'));

} elseif ($op == 'post') {
    include $this->template($this->xqtpl('plugin/houselease/post'));
}





