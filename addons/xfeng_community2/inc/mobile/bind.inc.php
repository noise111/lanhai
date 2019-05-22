<?php
/**
 * Created by we7xq.
 * User: zhoufeng
 * Time: 2017/7/27 上午10:55
 */
global $_GPC,$_W;
if (empty($_W['fans']['follow'])) {
    model_user::alertWechatLogin();exit();
}
if($_W['fans']['from_user']){
    model_user::checkauth();
    $id = intval($_GPC['id']);
    if(empty($id)){
        itoast('非法操作');exit();
    }

    $sql = "select id,addressid,regionid,realname,mobile,status from" . tablename('xcommunity_member_log') . "where id=:id";
    $item = pdo_fetch($sql,array(':id'=>$id));

    if (empty($item)) {
        itoast('房号信息不存在');
        exit();
    }
    $_uid = $_W['member']['uid'] ? $_W['member']['uid'] : mc_openid2uid($_W['openid']);
    if($_uid){
        pdo_update('mc_members', array('createtime' => TIMESTAMP, 'realname' => $item['realname'], 'mobile' => $item['mobile']), array('uid' => $_uid));
    }
    $user = pdo_get('xcommunity_member', array('uid' => $_W['member']['uid'], 'uniacid' => $_W['uniacid'], 'regionid' => $item['regionid']), array('id'));
    if($user){
        $memberid = $user['id'];
    }else{
        $memberid = model_user::mc_register_region($item['regionid'],'',$item['realname'],$item['mobile']);
    }
    if($memberid){
        $bind = pdo_get('xcommunity_member_bind', array('memberid' => $memberid, 'addressid' => $item['addressid']), array());
        if($bind){
            itoast('您已绑定该房屋',$this->createMobileUrl('home'),'success');exit();
        }
        $result = model_user::mc_register_address($item['regionid'],$area='',$build='',$unit='',$room='',$memberid,$mobile='',$item['addressid'],$item['status']);
    }
    if($result == 1){
        itoast('绑定成功',$this->createMobileUrl('home'),'success');exit();
    }elseif($result == 2){
        itoast('房号已存在,请重新输入',$this->createMobileUrl('home'),'success');exit();
    }elseif($result == 3){
        itoast('地址已绑定',$this->createMobileUrl('home'),'success');exit();
    }
}