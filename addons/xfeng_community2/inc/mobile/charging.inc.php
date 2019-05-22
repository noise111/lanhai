<?php
/**
 * Created by njlanniu.com.
 * User: 蓝牛科技
 * Date: 2018/5/24 0024
 * Time: 上午 11:08
 */
global $_W, $_GPC;

if (empty($_W['fans']['follow'])) {
    model_user::alertWechatLogin();exit();
}
$op = in_array(trim($_GPC['op']), array('list','detail','record','p_up','log','down')) ? trim($_GPC['op']) : 'list';
$member = model_user::mc_check();
if ($op == 'list') {
    //是否开启强制绑定小区
    $status = set('p146');
    if($status){
        //未绑定过小区
        if(empty($_SESSION['community']['regionid'])){
            $url = util::murl('register', array('op' => 'region'));
            header('location:'.$url);
        }
    }
    $_share = array(
        'title' => $_SESSION['community']['title'].'智能充电',
        'desc' => set('p71'),
        'imgUrl' => tomedia(set('p72'))
    );
    include $this->template($this->xqtpl('plugin/charging/list'));
}

if ($op == 'detail') {

    include $this->template($this->xqtpl('plugin/charging/detail'));
}

