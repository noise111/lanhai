<?php
/**
 * Created by 小区秘书.
 * User: 蓝牛科技
 * Date: 16/9/5
 * Time: 下午6:02
 * Function:
 */
global $_W, $_GPC;
$member = model_user::mc_check('opendoor');
$op = in_array(trim($_GPC['op']), array('list', 'share', 'key')) ? trim($_GPC['op']) : 'list';
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
    $_share = array(
        'title'  => $_SESSION['community']['title'] . '智能开门',
        'desc'   => set('p71'),
        'imgUrl' => tomedia(set('p72'))
    );
    include $this->template($this->xqtpl('core/opendoor/list'));
}
if ($op == 'key') {
    include $this->template($this->xqtpl('core/opendoor/key'));
}
if ($op == 'share') {
    $_W['page']['title']='分享临时钥匙';
    include $this->template($this->xqtpl('core/opendoor/share'));
}

