<?php
global $_GPC, $_W;
$member = model_user::mc_check('activity');
$op = in_array(trim($_GPC['op']), array('list', 'detail','list2','dynamicpost','signupList')) ? trim($_GPC['op']) : 'list';

if ($op == 'list') {
    $_share = array(
        'title' => $_SESSION['community']['title'].'小区活动',
        'desc' => set('p71'),
        'imgUrl' => tomedia(set('p72')),
    );
    include $this->template($this->xqtpl('plugin/activity/list'));
} elseif ($op == 'list2') {
    $_share = array(
        'title' => $_SESSION['community']['title'].'小区活动',
        'desc' => set('p71'),
        'imgUrl' => tomedia(set('p72')),
    );
    include $this->template($this->xqtpl('plugin/activity/list2'));
} elseif ($op == 'dynamicpost') {
    $_share = array(
        'title' => $_SESSION['community']['title'].'小区活动',
        'desc' => set('p71'),
        'imgUrl' => tomedia(set('p72')),
    );
    include $this->template($this->xqtpl('plugin/activity/dynamicpost'));
} elseif ($op == 'signupList') {
    $_share = array(
        'title' => $_SESSION['community']['title'].'小区活动',
        'desc' => set('p71'),
        'imgUrl' => tomedia(set('p72')),
    );
    include $this->template($this->xqtpl('plugin/activity/signupList'));
}
elseif ($op == 'detail') {
    $_share = array(
        'title' => $_SESSION['community']['title'].'小区活动',
        'desc' => set('p71'),
        'imgUrl' => tomedia(set('p72')),
    );
    include $this->template($this->xqtpl('plugin/activity/detail'));
}














