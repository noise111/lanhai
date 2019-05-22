<?php
/**
 * 小区秘书模块
 *
 * [蓝牛科技] Copyright (c) 2013 xqms.cn
 */
/**
 * 微信端小区超市
 */

global $_GPC, $_W;
$member = model_user::mc_check('shopping');

$op = in_array(trim($_GPC['op']), array('list', 'detail', 'mycart', 'confirm', 'myorder', 'grab')) ? trim($_GPC['op']) : 'list';
$operation = in_array(trim($_GPC['operation']), array('list', 'detail', 'add', 'remove', 'update')) ? trim($_GPC['operation']) : 'list';
if ($op == 'list') {
    $_share = array(
        'title' => $_SESSION['community']['title'].'小区超市',
        'desc' => set('p71'),
        'imgUrl' => tomedia(set('p72'))
    );
    include $this->template($this->xqtpl('plugin/shopping/list'));
} elseif ($op == 'detail') {
    include $this->template($this->xqtpl('plugin/shopping/detail'));
} elseif ($op == 'mycart') {
    include $this->template($this->xqtpl('plugin/shopping/cart'));

} elseif ($op == 'confirm') {
    include $this->template($this->xqtpl('plugin/shopping/confirm'));
} elseif ($op == 'myorder') {
    $status = intval($_GPC['status']);
    include $this->template($this->xqtpl('plugin/shopping/myorder'));
} elseif ($op == 'grab') {
    include $this->template($this->xqtpl('plugin/shopping/grab'));
}























