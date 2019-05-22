 <?php
/**
 * 小区秘书模块
 *
 * [蓝牛科技] Copyright (c) 2013 lanniu
 */
global $_GPC, $_W;
//判断是否从微信端进入
if (empty($_W['member']['uid'])) {
//    alertWechatLogin();exit();
    $info = mc_oauth_userinfo();
}
$op = !empty($_GPC['op']) ? $_GPC['op'] : 'region';
if ($op == 'member') {

    include $this->template($this->xqtpl('core/register/register'));
}
elseif ($op == 'region') {


    include $this->template($this->xqtpl('core/register/region'));
}
elseif ($op == 'getaround') {

}
elseif ($op == 'room') {


}
elseif ($op == 'add') {

}
elseif ($op == 'change') {

}
elseif ($op == 'xy') {

    include $this->template($this->xqtpl('core/register/xy'));
}
elseif ($op == 'guide') {
    //开启游客
    $regionid = intval($_GPC['regionid']) ? intval($_GPC['regionid']) : $_SESSION['community']['regionid'];
    $memberid = intval($_GPC['memberid']) ? intval($_GPC['memberid']) : $_SESSION['community']['id'];
//    $x61 = set('x61',$regionid);
//    $content = set('x63',$regionid) ? set('x63',$regionid) : '该小区暂未与本平台合作，请你建议物业尽快入驻本平台';
//    if ($x61){
//        itoast($content, util::murl('home', array('regionid' => $regionid)), 'error');
//    }
    $s1 = set('p22');
    $s2 = set('x4', $regionid);
    if(empty($_W['member']['uid'])){
        model_user::checkauth();
    }
    $_uid = $_W['member']['uid'] ? $_W['member']['uid'] : mc_openid2uid($_W['openid']);
    if($_uid){
        $condition .="t1.uniacid =:uniacid and  t1.uid=:uid and t1.regionid=:regionid";
        $params[':regionid'] = $regionid;
        //需要考虑游客情况
        $sql = "select t1.visit,t1.status,t1.open_status,t1.regionid,t1.id,t1.uid,t1.license,t2.title,t2.thumb,t2.pid,t2.qq,t2.linkway from" . tablename('xcommunity_member') . "t1 left join" . tablename('xcommunity_region') . "t2 on t2.id = t1.regionid where $condition";

        $params[':uniacid'] = $_W['uniacid'];
        $params[':uid'] = $_uid;
        $item = pdo_fetch($sql, $params);
    }

    if ($s1||$s2||$item) {

        $status = model_user::mc_register_region($regionid);
        if ($status&&empty($_GPC['p'])) {
            $url = util::murl('home');
            header("Location:" . $url);
            exit();
        }
    }


    include $this->template($this->xqtpl('core/register/guide'));
}
elseif ($op == 'm') {
    $regionid = intval($_GPC['regionid']);
    $memberid = intval($_GPC['memberid']);
    include $this->template($this->xqtpl('core/register/reg_house'));
}
elseif ($op == 'c') {
    $regionid = intval($_GPC['regionid']);
    $memberid = intval($_GPC['memberid']);
    include $this->template($this->xqtpl('core/register/reg_code'));
}
elseif ($op == 'login') {

    include $this->template($this->xqtpl('core/register/login'));
}
elseif ($op == 'register'){
    $regionid = intval($_GPC['regionid']);

    include $this->template($this->xqtpl('core/register/register'));
}
elseif ($op =='verity'){

    include $this->template($this->xqtpl('core/register/verity'));
}elseif($op =='f'){
    $regionid = intval($_GPC['regionid']);
    $memberid = intval($_GPC['memberid']);
    include $this->template($this->xqtpl('core/register/reg_mobile'));
}
