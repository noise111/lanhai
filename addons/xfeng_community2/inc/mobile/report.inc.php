<?php
/**
 * 小区秘书模块
 *
 * [蓝牛科技] Copyright (c) 2013 xqms.cn
 */
/**
 * 微信端投诉页面
 */

global $_GPC, $_W;
$op = in_array(trim($_GPC['op']), array('list', 'add', 'rank', 'my', 'grab', 'detail')) ? trim($_GPC['op']) : 'list';

if ($op == 'list') {
    if ($_W['container'] == 'wechat') {
        if (empty($_W['member']['mobile'])) {
            $regionid = $_SESSION['community']['regionid'];
            if (empty($regionid)) {
                itoast('请选择小区并完善资料，再进行下一步操作', util::murl('register', array('op' => 'region')), 'success');
                exit();
            }
            else {
                $url = $this->createMobileUrl('register', array('regionid' => $regionid, 'op' => 'guide', 'p' => 1, 'memberid' => $_SESSION['community']['id']));
                itoast('请先完善资料，再进行下一步操作！', $url, 'error');
            }
        }
    }
    $member = model_user::mc_check('report');

    $_share = array(
        'title'  => $_SESSION['community']['title'] . '小区建议',
        'desc'   => set('p71'),
        'imgUrl' => tomedia(set('p72'))
    );
    include $this->template($this->xqtpl('core/report/list'));

}
elseif ($op == 'detail') {
    $member = model_user::mc_check('report');
    include $this->template($this->xqtpl('core/report/detail'));
}
elseif ($op == 'add') {
    $member = model_user::mc_check('report');
    include $this->template($this->xqtpl('core/report/add'));
}
elseif ($op == 'rank') {
    $member = model_user::mc_check('report');
    include $this->template($this->xqtpl('core/report/rank'));
}
elseif ($op == 'grab') {

    include $this->template($this->xqtpl('core/report/grab'));

}
elseif ($op == 'my') {
    $member = model_user::mc_check('report');
    include $this->template($this->xqtpl('core/report/my'));
}