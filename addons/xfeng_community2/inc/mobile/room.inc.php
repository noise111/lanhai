<?php
/**
 * Created by xiaoqu.
 * User: zhoufeng
 * Time: 2017/12/25 下午9:53
 */
global $_GPC, $_W;
$op = in_array(trim($_GPC['op']), array('list', 'add')) ? trim($_GPC['op']) : 'list';
$member = model_user::mc_check();
if($op =='add'){

    include $this->template($this->xqtpl('core/room/add'));
}elseif($op =='list'){
    if(empty($_W['member']['mobile'])){
        $url = $this->createMobileUrl('register',array('op'=>'guide','p'=>1));
        header("Location:$url");exit();
    }
    include $this->template($this->xqtpl('core/room/list'));
}
