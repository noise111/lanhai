<?php
/**
 * 小区秘书模块
 *
 * [蓝牛科技] Copyright (c) 2013 xqms.cn
 */
/**
 *
 */
global $_GPC, $_W;

$member = model_user::mc_check('cost');
$op = in_array(trim($_GPC['op']), array('list', 'property', 'add', 'detail', 'bill')) ? trim($_GPC['op']) : 'list';
if ($op == 'list') {
    if ($_W['container'] == 'wechat') {
        if (empty($_W['member']['mobile'])) {
            $regionid= $_SESSION['community']['regionid'];
            if(empty($regionid)){
                itoast('请选择小区并完善资料，再进行下一步操作', util::murl('register', array('op' => 'region')), 'success');
                exit();
            }else{
                $url = $this->createMobileUrl('register', array('regionid' => $regionid, 'op' => 'guide', 'p' => 1, 'memberid' => $_SESSION['community']['id']));
                itoast('请先完善资料，再进行下一步操作！', $url, 'error');
            }
        }
    }
    include $this->template($this->xqtpl('core/cost/list'));
} elseif ($op == 'property') {
    include $this->template($this->xqtpl('core/cost/property'));
} elseif ($op == 'add') {
    include $this->template($this->xqtpl('core/cost/add'));
} elseif ($op == 'detail') {
    include $this->template($this->xqtpl('core/cost/detail'));
} elseif ($op == 'bill') {
    include $this->template($this->xqtpl('core/cost/bill'));
}



