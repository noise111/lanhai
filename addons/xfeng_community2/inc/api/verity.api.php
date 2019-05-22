<?php
/**
 * Created by we7xq.
 * User: zhoufeng
 * Time: 2017/6/22 下午12:08
 */
global $_GPC,$_W;

$key = $_GPC['key'];
$mobile = $_GPC['mobile'];
$uniacid = intval($_POST['uniacid']) ? intval($_POST['uniacid']) : $_W['uniacid'];
$regionid = intval($_GPC['regionid']) ? intval($_GPC['regionid']) : $_SESSION['community']['regionid'];
load()->classs('wesession');
WeSession::start($_W['uniacid'], $_W['fans']['from_user'], 60);
if ($mobile == $_SESSION['mobile']) {
    $code = $_SESSION['code'];
} else {
    $code = random(6, 1);
    $_SESSION['mobile'] = $mobile;
    $_SESSION['code'] = $code;
}
if(set('p20') || set('x3',$regionid)){
    $condition = " t2.mobile=:mobile ";
    $params[':mobile'] = $mobile;
    if ($regionid){
        $condition .= " and t1.regionid=:regionid ";
        $params[':regionid'] = $regionid;
    }
    $sql ="select t1.code from".tablename('xcommunity_member_room')."t1 left join".tablename('xcommunity_member_log')."t2 on t1.id = t2.addressid where $condition";
    $item = pdo_fetch($sql,$params);

    $xqcode = $item['code'] ? $item['code'] : '';
}

if (set('s2') && set('s4')) {
    $type = set('s1');

    if($type ==1){
        $type ='wwt';
    }elseif($type ==2){
        $type = 'juhe';
        $tpl_id = set('s9');
    }elseif($type ==3){
        $type = 'aliyun_new';
        $tpl_id = set('s21');
    }else{
        $type ='qhyx';
    }

    if ($type == 'wwt' || $type =='qhyx') {
        if(set('p20') || set('x3',$regionid)){
            $smsg = "您的验证码是：" . $code . ",注册码是".$xqcode."请不要把验证码泄露给其他人。";
        }else{
            $smsg = "您的验证码是：" . $code . "。请不要把验证码泄露给其他人。";
        }

    }elseif($type=='juhe'){
        if(set('p20') || set('x3',$regionid)){
            $smsg = urlencode("#code#=$code&#xqcode#=$xqcode");
        }else{
            $smsg = urlencode("#code#=$code");
        }

    }else{
        if(set('p20') || set('x3',$regionid)){
            //$smsg = "code=$code&xqcode=$xqcode";
            if($xqcode){
                $arr = array('code'=>$code,'xqcode'=>$xqcode);
            }else{
                $arr = array('code'=>$code);
            }
            $smsg =json_encode($arr);
        }else{
            $smsg =json_encode(array('code'=>$code));
        }

    }
    $result = sms::send($mobile, $smsg, $type,'', 1, $tpl_id,$uniacid,set('s13'));
    $d = array(
        'uniacid' => $_W['uniacid'],
        'sendid' => '',
        'uid' => $_W['member']['uid'],
        'type' => 4,
        'cid' => 2,
        'status' => 1,
        'createtime' => TIMESTAMP,
        'regionid'  => $regionid
    );
    pdo_insert('xcommunity_send_log', $d);

}
else{
    $type = set('x21',$regionid) ;
    if($type ==1){
        $type ='wwt';
    }elseif($type ==2){
        $type = 'juhe';
        $tpl_id = set('x34',$regionid);
    }else{
        $type = 'aliyun_new';
        $tpl_id = set('x69',$regionid);
    }
    if ($type == 'wwt') {
        if(set('p20') || set('x3',$regionid)){
            $smsg = "您的验证码是：" . $code . ",注册码是".$xqcode."请不要把验证码泄露给其他人。";
        }else{
            $smsg = "您的验证码是：" . $code . "。请不要把验证码泄露给其他人。";
        }
    } elseif($type=='juhe') {
        if(set('p20') || set('x3',$regionid)){
            $smsg = urlencode("#code#=$code&#xqcode#=$xqcode");
        }else{
            $smsg = urlencode("#code#=$code");
        }
    }else{

        if(set('p20') || set('x3',$regionid)){
            //$smsg = "code=$code&xqcode=$xqcode";
            if($xqcode){
                $arr = array('code'=>$code,'xqcode'=>$xqcode);
            }else{
                $arr = array('code'=>$code);
            }
            $smsg =json_encode($arr);
        }else{
            $smsg =json_encode(array('code'=>$code));
        }
    }
    $result = sms::send($mobile, $smsg, $type,$regionid, 2, $tpl_id,'',set('x38',$regionid));
    $d = array(
        'uniacid' => $_W['uniacid'],
        'sendid' => '',
        'uid' => $_W['member']['uid'],
        'type' => 4,
        'cid' => 2,
        'status' => 1,
        'createtime' => TIMESTAMP,
        'regionid' => $regionid
    );
    pdo_insert('xcommunity_send_log', $d);

}
if ($result['status']) {
    //发送成功
    util::send_result(array('code'=>$code));
}else{
    util::send_error(-1,$result['message']);
}



