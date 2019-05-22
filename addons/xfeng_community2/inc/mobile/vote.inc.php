<?php
/**
 * 小区秘书模块
 *
 * [蓝牛科技] Copyright (c) 2013 xqms.cn
 */
global $_GPC, $_W;
$op = !empty($_GPC['op']) ? $_GPC['op'] : 'list';
if (empty($_W['fans']['follow'])) {
    model_user::alertWechatLogin();exit();
}
//是否开启强制绑定小区
$status = set('p159');
if($status){
    $member = model_user::mc_check('vote');
}
if ($op == 'list') {
    $_share = array(
        'title' => $_SESSION['community']['title'].'小区投票',
        'desc' => set('p71'),
        'imgUrl' => tomedia(set('p72'))
    );
    include $this->template($this->xqtpl('plugin/vote/list'));
}elseif($op == 'add'){

    include $this->template($this->xqtpl('plugin/vote/add'));
}
