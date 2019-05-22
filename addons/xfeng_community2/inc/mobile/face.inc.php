<?php
/**
 * Created by njlanniu.com
 * User: 蓝牛科技
 * Time: 2018/12/22 0022 下午 10:55
 */
global $_GPC, $_W;
$op = in_array(trim($_GPC['op']),array('list')) ? trim($_GPC['op']) : 'list';
$member = model_user::mc_check();
if($op =='list'){
    include $this->template($this->xqtpl('core/face'));
}