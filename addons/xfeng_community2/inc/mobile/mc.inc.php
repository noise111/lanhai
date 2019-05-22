<?php
/**
 * Created by njlanniu.
 * User: njlanniu
 * Time: 2018/3/24 下午7:07
 */
global $_W,$_GPC;
if($_W['member']['mobile']){
    $url = url('mc');
    header("Location:$url");exit();
}
if($_W['isajax']){
    $mobile = trim($_GPC['mobile']);
    if(mc_update($_W['member']['uid'],array('mobile'=>$mobile))){
        $url = url('mc');
        echo json_encode(array('status'=>1,'url'=> $url));exit();
    }

}
include $this->template($this->xqtpl('mc'));