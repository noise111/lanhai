<?php
/**
 * 小区秘书模块
 *
 * [蓝牛科技] Copyright (c) 2013 xqms.cn
 */
/**
 * 微信端个人页面
 */

global $_GPC, $_W;
$member = model_user::mc_check();
$op = in_array(trim($_GPC['op']), array('home', 'detail', 'region', 'room', 'js', 'detail', 'update_mobile', 'my', 'family', 'add', 'park')) ? trim($_GPC['op']) : 'home';
if ($op == 'home') {
    include $this->template($this->xqtpl('core/member/home'));
}
if ($op == 'region') {
    include $this->template($this->xqtpl('core/member/region'));
}
if ($op == 'room') {
    include $this->template($this->xqtpl('core/member/room'));
}
if ($op == 'js') {
    include $this->template($this->xqtpl('core/member/js'));
}
if ($op == 'detail') {
    include $this->template($this->xqtpl('core/member/detail'));
}
if ($op == 'update_mobile') {
    include $this->template($this->xqtpl('core/member/upmobile'));
}
if ($op == 'my') {
    include $this->template($this->xqtpl('core/member/my'));
}
if ($op == 'family') {
    include $this->template($this->xqtpl('core/member/family'));
}
if ($op == 'add') {
    include $this->template($this->xqtpl('core/member/add'));
}
if ($op == 'park') {
    include $this->template($this->xqtpl('core/member/park'));
}
