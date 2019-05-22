<?php
/**
 * 小区秘书模块
 *
 * [蓝牛科技] Copyright (c) 2013 xqms.cn
 */
/**
 * 微信端城乡直通车
 */

global $_GPC, $_W;
$op = in_array(trim($_GPC['op']), array('list', 'detail', 'mycart', 'confirm', 'myorder', 'grab')) ? trim($_GPC['op']) : 'list';
$operation = in_array(trim($_GPC['operation']), array('list', 'detail', 'add', 'remove', 'update')) ? trim($_GPC['operation']) : 'list';
if ($op == 'list') {
    $_share = array(
        'title' => $_SESSION['community']['title'].'城乡直通车',
        'desc' => set('p71'),
        'imgUrl' => tomedia(set('p72'))
    );
    include $this->template($this->xqtpl('plugin/direct/list'));
} elseif ($op == 'detail') {
    include $this->template($this->xqtpl('plugin/direct/detail'));
} elseif ($op == 'mycart') {
    include $this->template($this->xqtpl('plugin/direct/cart'));

} elseif ($op == 'confirm') {
    include $this->template($this->xqtpl('plugin/direct/confirm'));
} elseif ($op == 'myorder') {
    $status = intval($_GPC['status']);
    include $this->template($this->xqtpl('plugin/direct/myorder'));
} elseif ($op == 'grab') {
    include $this->template($this->xqtpl('plugin/direct/grab'));
}