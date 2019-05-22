<?php
/**
 * Created by njlanniu.
 * User: njlanniu
 * Time: 2018/2/11 下午10:52
 */
global $_GPC, $_W;
$media_id = trim($_GPC['serverId']);
if(empty($media_id)){
    util::send_error(-1, '参数错误');
}
$imgUrl = util::get_media($media_id);
if(is_error($imgUrl)){
    util::send_error($imgUrl['errno'], $imgUrl['message']);
}
if($_GPC['type'] =='upavatar'){
    pdo_update('mc_members',array('avatar'=>$imgUrl),array('uid'=> $_W['member']['uid']));
    $_W['member']['avatar'] = $imgUrl;
}
$data = array();
$data['imgUrl'] = $imgUrl;

util::send_result($data);
