<?php
/**
 * Created by we7xq.
 * User: zhoufeng
 * Time: 2017/7/29 下午5:07
 */
global $_W, $_GPC;
if (empty($_W['fans']['follow'])) {
    model_user::alertWechatLogin();exit();
}
if($_W['fans']['from_user']){
    $logid = intval($_GPC['logid']);
    if(empty($logid)){
        itoast('非法操作');exit();
    }
    $sql ="select t1.realname,t1.mobile,t1.status,t2.id,t2.regionid,t4.from_uid from".tablename('xcommunity_member_log')."t1 left join".tablename('xcommunity_member_room')."t2 on t1.addressid=t2.id left join".tablename('xcommunity_region')."t3 on t3.id = t2.regionid left join".tablename('xcommunity_member_family')."t4 on t4.logid = t1.id where t1.id=:id";
    $item = pdo_fetch($sql,array(':id' => $logid));
    if(empty($item)){
        itoast('非法操作');exit();
    }
    if($item['from_uid'] == $_W['member']['uid']){
        itoast('您已是户主，无需点击');exit();
    }
//    $member = model_user::mc_check();
//    if(empty($member)){
        $memberid = model_user::mc_register_region($item['regionid'],$_W['member']['uid'],$item['realname'],$item['mobile']);
//    }else{
//        $memberid = $member['id'];
//    }

    $family = pdo_get('xcommunity_member_family', array('to_uid' => $_W['member']['uid'], 'logid' => $logid));
    if ($family) {
        itoast('您已是住户，无需点击');exit();
    }
    if($memberid){
        $result = model_user::mc_register_address($item['regionid'],$area='',$build='',$unit='',$room='',$memberid,$mobile='',$item['id'],$item['status']);
        if($result){
            pdo_update('xcommunity_member_family',array('to_uid' => $_W['member']['uid']),array('logid' => $logid));
            itoast('绑定成功',$this->createMobileUrl('home'),'success');
        }
    }

}