<?php
/**
 * 小区秘书模块
 *
 * [蓝牛科技] Copyright (c) 2013 xqms.cn
 * 公告
 */
global $_GPC, $_W;
$member = model_user::mc_check('announcement');
$op = in_array(trim($_GPC['op']), array('list', 'detail')) ? trim($_GPC['op']) : 'list';
if ($op == 'list') {
    $_share = array(
        'title' => $_SESSION['community']['title'].'小区公告',
        'desc' => set('p71'),
        'imgUrl' => tomedia(set('p72'))
    );
    include $this->template($this->xqtpl('core/announcement/list'));
} elseif ($op == 'detail') {
    include $this->template($this->xqtpl('core/announcement/detail'));
}




